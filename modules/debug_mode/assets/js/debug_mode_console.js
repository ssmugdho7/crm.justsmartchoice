(function () {
  'use strict';

  var script = document.currentScript;
  var scopeName = script && script.getAttribute('data-scope');
  var pageRoute = script && script.getAttribute('data-route');
  var endpoint = script && script.getAttribute('data-endpoint');
  var visit = script && script.getAttribute('data-visit');
  var visitProof = script && script.getAttribute('data-visit-proof');
  var captureToken = script && script.getAttribute('data-token');
  var root = document.getElementById('scDebugConsole');
  if (!root || !scopeName || !pageRoute || !endpoint || !visit || !visitProof || !captureToken) return;

  window.scDebugIgnoredPaths = window.scDebugIgnoredPaths || [];
  try { window.scDebugIgnoredPaths.push(new URL(endpoint, location.href).pathname); } catch (_) {}

  var toggle = root.querySelector('[data-console-toggle]');
  var refresh = root.querySelector('[data-console-refresh]');
  var count = root.querySelector('[data-console-count]');
  var status = root.querySelector('[data-console-status]');
  var events = root.querySelector('[data-console-events]');
  var body = root.querySelector('[data-console-body]');
  var storageKey = 'scDebugConsole:' + pageRoute;
  var known = Object.create(null);
  var observed = Object.create(null);
  var busy = false;
  var loaded = document.readyState === 'complete';
  var checkAgain = false;
  var manuallyCollapsed = sessionStorage.getItem(storageKey) === 'collapsed';

  function setExpanded(expanded) {
    root.classList.toggle('is-open', expanded);
    body.hidden = !expanded;
    toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    toggle.querySelector('[data-console-toggle-label]').textContent = expanded ? 'Minimize' : 'Open';
    toggle.querySelector('.fa').className = expanded ? 'fa fa-chevron-down' : 'fa fa-chevron-up';
  }

  function node(tag, className, text) {
    var element = document.createElement(tag);
    if (className) element.className = className;
    if (text !== undefined) element.textContent = text;
    return element;
  }

  function renderLocation(location) {
    if (!location) return null;
    var block = node('div', 'sc-debug-console-location');
    block.appendChild(node('div', 'sc-debug-console-file', location.file + ':' + location.line));
    var source = node('pre', 'sc-debug-console-source');
    if (Array.isArray(location.excerpt)) {
      location.excerpt.forEach(function (line) {
        var row = node('span', line.current ? 'is-current' : '');
        row.appendChild(node('span', 'sc-debug-console-line', String(line.number)));
        row.appendChild(node('code', '', line.text));
        source.appendChild(row);
      });
      block.appendChild(source);
    }
    return block;
  }

  function formatSeen(timestamp) {
    if (!timestamp) return 'unknown';
    try { return new Date(timestamp * 1000).toLocaleString(); } catch (_) { return 'unknown'; }
  }

  function renderEvent(event) {
    var article = node('article', 'sc-debug-console-event severity-' + event.severity);
    var heading = node('div', 'sc-debug-console-event-heading');
    heading.appendChild(node('span', 'sc-debug-console-severity', event.severity));
    heading.appendChild(node('strong', '', event.summary));
    article.appendChild(heading);
    var occurrenceText = event.occurrences + ' occurrence' + (event.occurrences === 1 ? '' : 's');
    article.appendChild(node('div', 'sc-debug-console-meta', 'First seen ' + formatSeen(event.first_seen_unix) + ' · Last seen ' + formatSeen(event.last_seen_unix) + ' · ' + occurrenceText + ' · ' + event.source));
    var location = renderLocation(event.location);
    if (location) article.appendChild(location);
    if (event.trace && event.trace.length) {
      var details = node('details', 'sc-debug-console-trace');
      details.appendChild(node('summary', '', 'Stack trace (' + event.trace.length + ' lines)'));
      details.appendChild(node('pre', '', event.trace.join('\n')));
      article.appendChild(details);
    }
    return article;
  }

  function render(payload) {
    payload.events.forEach(function (event) { observed[event.fingerprint || event.id] = event; });
    payload.events = Object.keys(observed).map(function (key) { return observed[key]; }).slice(0, 50);
    events.textContent = '';
    var fresh = 0;
    payload.events.forEach(function (event) {
      var eventKey = event.fingerprint || event.id;
      if (!known[eventKey]) fresh++;
      known[eventKey] = true;
      events.appendChild(renderEvent(event));
    });
    var capture = window.scDebugCaptureStatus || {pending: 0, failed: true};
    if (!payload.events.length) events.appendChild(node('p', 'sc-debug-console-empty', capture.failed
      ? 'Some browser diagnostics could not be recorded. This page cannot yet be reported clean.'
      : 'No errors detected on this page load. Errors that no longer occur are removed automatically.'));
    count.textContent = String(payload.events.length);
    count.hidden = payload.events.length === 0;
    status.textContent = payload.events.length
      ? payload.events.length + ' issue' + (payload.events.length === 1 ? '' : 's') + ' on this page load' + (payload.limited ? ' · newest 50 shown' : '')
      : (capture.failed ? 'Browser capture incomplete' : 'No errors on this page load');
    if (capture.failed && payload.events.length) status.textContent += ' · browser capture incomplete';
    root.classList.toggle('has-errors', payload.events.length > 0 || capture.failed);
    if (payload.events.length && fresh && !manuallyCollapsed) setExpanded(true);
  }

  function requestBody() {
    var bodyData = new URLSearchParams({scope: scopeName, route: pageRoute, visit: visit, visit_proof: visitProof, debug_capture_token: captureToken});
    if (window.csrfData && csrfData.token_name && csrfData.hash) bodyData.set(csrfData.token_name, csrfData.hash);
    return bodyData;
  }

  function fetchEvents() {
    if (!loaded || document.hidden) return;
    if (busy) { checkAgain = true; return; }
    if (window.scDebugCaptureStatus && window.scDebugCaptureStatus.pending) {
      status.textContent = 'Recording page errors…';
      return;
    }
    busy = true;
    status.textContent = 'Checking ' + scopeName + '…';
    var bodyData = requestBody();
    fetch(endpoint, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: bodyData.toString()
    }).then(function (response) {
      if (!response.ok || response.redirected) throw new Error('HTTP ' + response.status);
      return response.json();
    }).then(function (payload) {
      if (!payload || payload.success !== true || payload.scope !== scopeName || payload.route !== pageRoute || payload.visit !== visit || !Array.isArray(payload.events)) throw new Error('Invalid response');
      if (!window.scDebugCaptureStatus || !window.scDebugCaptureStatus.pending) render(payload);
      else checkAgain = true;
    }).catch(function () {
      status.textContent = 'Page diagnostics are temporarily unavailable';
      root.classList.add('has-errors');
    }).finally(function () {
      busy = false;
      if (checkAgain) { checkAgain = false; fetchEvents(); }
    });
  }

  toggle.addEventListener('click', function () {
    var expanded = toggle.getAttribute('aria-expanded') !== 'true';
    manuallyCollapsed = !expanded;
    sessionStorage.setItem(storageKey, expanded ? 'open' : 'collapsed');
    setExpanded(expanded);
  });
  refresh.addEventListener('click', fetchEvents);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) fetchEvents(); });
  document.addEventListener('sc-debug-recorded', fetchEvents);
  window.addEventListener('load', function () { loaded = true; fetchEvents(); });

  setExpanded(sessionStorage.getItem(storageKey) === 'open');
  fetchEvents();
  window.setInterval(fetchEvents, 7000);
})();

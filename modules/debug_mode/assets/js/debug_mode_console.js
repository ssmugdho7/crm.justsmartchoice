(function () {
  'use strict';

  var script = document.currentScript;
  var scopeName = script && script.getAttribute('data-scope');
  var pageRoute = script && script.getAttribute('data-route');
  var endpoint = script && script.getAttribute('data-endpoint');
  var resolveEndpoint = script && script.getAttribute('data-resolve-endpoint');
  var captureToken = script && script.getAttribute('data-token');
  var root = document.getElementById('scDebugConsole');
  if (!root || !scopeName || !pageRoute || !endpoint || !resolveEndpoint || !captureToken) return;

  window.scDebugIgnoredPaths = window.scDebugIgnoredPaths || [];
  try { window.scDebugIgnoredPaths.push(new URL(endpoint, location.href).pathname); } catch (_) {}
  try { window.scDebugIgnoredPaths.push(new URL(resolveEndpoint, location.href).pathname); } catch (_) {}

  var toggle = root.querySelector('[data-console-toggle]');
  var refresh = root.querySelector('[data-console-refresh]');
  var count = root.querySelector('[data-console-count]');
  var status = root.querySelector('[data-console-status]');
  var events = root.querySelector('[data-console-events]');
  var body = root.querySelector('[data-console-body]');
  var storageKey = 'scDebugConsole:' + pageRoute;
  var known = Object.create(null);
  var busy = false;
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

  function resolveIssue(event, button) {
    if (button.disabled) return;
    button.disabled = true;
    status.textContent = 'Marking issue resolved…';
    var bodyData = requestBody({fingerprint: event.fingerprint});
    fetch(resolveEndpoint, {
      method: 'POST', credentials: 'same-origin',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: bodyData.toString()
    }).then(function (response) {
      if (!response.ok || response.redirected) throw new Error('HTTP ' + response.status);
      return response.json();
    }).then(function (payload) {
      if (!payload || payload.success !== true || payload.fingerprint !== event.fingerprint) throw new Error('Invalid response');
      delete known[event.fingerprint];
      fetchEvents();
    }).catch(function () {
      status.textContent = 'Could not resolve this issue';
      button.disabled = false;
    });
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
    var actions = node('div', 'sc-debug-console-actions');
    actions.appendChild(node('span', '', 'This issue stays open across visits.'));
    var resolveButton = node('button', 'sc-debug-console-resolve', 'Mark resolved after code fix');
    resolveButton.type = 'button';
    resolveButton.addEventListener('click', function () { resolveIssue(event, resolveButton); });
    actions.appendChild(resolveButton);
    article.appendChild(actions);
    return article;
  }

  function render(payload) {
    events.textContent = '';
    var fresh = 0;
    payload.events.forEach(function (event) {
      var eventKey = event.fingerprint || event.id;
      if (!known[eventKey]) fresh++;
      known[eventKey] = true;
      events.appendChild(renderEvent(event));
    });
    if (!payload.events.length) events.appendChild(node('p', 'sc-debug-console-empty', 'No unresolved issues are recorded for this page.'));
    count.textContent = String(payload.events.length);
    count.hidden = payload.events.length === 0;
    status.textContent = payload.events.length
      ? payload.events.length + ' unresolved issue' + (payload.events.length === 1 ? '' : 's') + ' for this page' + (payload.limited ? ' · newest 50 shown' : '')
      : 'No unresolved issues for this page';
    root.classList.toggle('has-errors', payload.events.length > 0);
    if (payload.events.length && fresh && !manuallyCollapsed) setExpanded(true);
  }

  function requestBody(extra) {
    var bodyData = new URLSearchParams({scope: scopeName, route: pageRoute, debug_capture_token: captureToken});
    Object.keys(extra || {}).forEach(function (key) { bodyData.set(key, extra[key]); });
    if (window.csrfData && csrfData.token_name && csrfData.hash) bodyData.set(csrfData.token_name, csrfData.hash);
    return bodyData;
  }

  function fetchEvents() {
    if (busy || document.hidden) return;
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
      if (!payload || payload.success !== true || payload.scope !== scopeName || payload.route !== pageRoute || !Array.isArray(payload.events)) throw new Error('Invalid response');
      render(payload);
    }).catch(function () {
      status.textContent = 'Page diagnostics are temporarily unavailable';
      root.classList.add('has-errors');
    }).finally(function () { busy = false; });
  }

  toggle.addEventListener('click', function () {
    var expanded = toggle.getAttribute('aria-expanded') !== 'true';
    manuallyCollapsed = !expanded;
    sessionStorage.setItem(storageKey, expanded ? 'open' : 'collapsed');
    setExpanded(expanded);
  });
  refresh.addEventListener('click', fetchEvents);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) fetchEvents(); });

  setExpanded(sessionStorage.getItem(storageKey) === 'open');
  fetchEvents();
  window.setInterval(fetchEvents, 7000);
})();

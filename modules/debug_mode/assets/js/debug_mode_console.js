(function () {
  'use strict';

  var script = document.currentScript;
  var scopeName = script && script.getAttribute('data-scope');
  var pageRoute = script && script.getAttribute('data-route');
  var endpoint = script && script.getAttribute('data-endpoint');
  var captureToken = script && script.getAttribute('data-token');
  var since = Number(script && script.getAttribute('data-since')) || Math.floor(Date.now() / 1000) - 10;
  var root = document.getElementById('scDebugConsole');
  if (!root || !scopeName || !pageRoute || !endpoint || !captureToken) return;

  window.scDebugIgnoredPaths = window.scDebugIgnoredPaths || [];
  try { window.scDebugIgnoredPaths.push(new URL(endpoint, location.href).pathname); } catch (_) {}

  var toggle = root.querySelector('[data-console-toggle]');
  var refresh = root.querySelector('[data-console-refresh]');
  var clear = root.querySelector('[data-console-clear]');
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
    location.excerpt.forEach(function (line) {
      var row = node('span', line.current ? 'is-current' : '');
      row.appendChild(node('span', 'sc-debug-console-line', String(line.number)));
      row.appendChild(node('code', '', line.text));
      source.appendChild(row);
    });
    block.appendChild(source);
    return block;
  }

  function renderEvent(event) {
    var article = node('article', 'sc-debug-console-event severity-' + event.severity);
    var heading = node('div', 'sc-debug-console-event-heading');
    heading.appendChild(node('span', 'sc-debug-console-severity', event.severity));
    heading.appendChild(node('strong', '', event.summary));
    article.appendChild(heading);
    article.appendChild(node('div', 'sc-debug-console-meta', event.occurred_at + ' · ' + event.source));
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
    events.textContent = '';
    var fresh = 0;
    payload.events.forEach(function (event) {
      if (!known[event.id]) fresh++;
      known[event.id] = true;
      events.appendChild(renderEvent(event));
    });
    if (!payload.events.length) events.appendChild(node('p', 'sc-debug-console-empty', 'No errors were recorded for this page after it started.'));
    count.textContent = String(payload.events.length);
    count.hidden = payload.events.length === 0;
    status.textContent = payload.events.length
      ? payload.events.length + ' event' + (payload.events.length === 1 ? '' : 's') + ' for this page' + (payload.limited ? ' · newest 50 shown' : '')
      : 'No errors for this page visit';
    root.classList.toggle('has-errors', payload.events.length > 0);
    if (payload.events.length && fresh && !manuallyCollapsed) setExpanded(true);
  }

  function fetchEvents() {
    if (busy || document.hidden) return;
    busy = true;
    status.textContent = 'Checking ' + scopeName + '…';
    var bodyData = new URLSearchParams({scope: scopeName, route: pageRoute, since: String(since), debug_capture_token: captureToken});
    if (window.csrfData && csrfData.token_name && csrfData.hash) bodyData.set(csrfData.token_name, csrfData.hash);
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
  clear.addEventListener('click', function () {
    since = Math.floor(Date.now() / 1000);
    known = Object.create(null);
    manuallyCollapsed = false;
    sessionStorage.removeItem(storageKey);
    fetchEvents();
  });
  document.addEventListener('visibilitychange', function () { if (!document.hidden) fetchEvents(); });

  setExpanded(sessionStorage.getItem(storageKey) === 'open');
  fetchEvents();
  window.setInterval(fetchEvents, 7000);
})();

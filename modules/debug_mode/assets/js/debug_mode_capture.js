(function () {
  'use strict';
  var script = document.currentScript;
  var endpoint = script && script.getAttribute('data-endpoint');
  var captureToken = script && script.getAttribute('data-token');
  if (!endpoint || !captureToken || window.scDebugCaptureInstalled) return;
  window.scDebugCaptureInstalled = true;
  var clientPortal = script.getAttribute('data-portal') === 'client';
  var visit = !clientPortal && script.getAttribute('data-visit');
  if (!/^[a-f0-9]{32}$/.test(visit || '')) visit = '';
  var captureStatus = window.scDebugCaptureStatus = {pending: 0, failed: false};
  var sent = 0, seen = Object.create(null), pending = [];
  function safePath(value) {
    try { var url = new URL(value, location.href); return url.origin === location.origin ? url.pathname : '[external resource]'; }
    catch (_) { return '[unknown resource]'; }
  }
  function ignored(value) {
    var path = safePath(value);
    return path === safePath(endpoint) || (window.scDebugIgnoredPaths || []).indexOf(path) !== -1;
  }
  function report(kind, message, file, line) {
    var entry = {kind: kind, message: String(message || '').slice(0, 700), file: safePath(file || location.href), line: Number(line) || 0, route: location.pathname};
    if (visit) entry.visit = visit;
    if (clientPortal) {
      // Client messages may contain personal data. Keep only the error category and asset path.
      entry.message = kind === 'http' ? String(message).replace(/[^A-Z0-9 ]/g, '').slice(0, 40) : 'Client portal ' + kind + ' error';
      entry.route = '/' + location.pathname.split('/').filter(Boolean).slice(0, 1).join('/');
      if (!/\.(js|css|png|jpg|jpeg|svg|webp|gif|woff2?)$/i.test(entry.file)) entry.file = entry.route;
    }
    var key = JSON.stringify(entry);
    if (seen[key]) return;
    if (sent >= 20) { captureStatus.failed = true; return; }
    seen[key] = true; sent++; pending.push(entry); captureStatus.pending++;
    flush();
  }
  // No form values, request/response bodies, headers or query strings are collected.
  function flush() {
    if (document.readyState === 'loading') return;
    while (pending.length) {
      var entry = pending.shift(), body = new URLSearchParams();
      Object.keys(entry).forEach(function (key) { body.set(key, entry[key]); });
      body.set('debug_capture_token', captureToken);
      if (window.csrfData && window.csrfData.token_name && window.csrfData.hash) body.set(window.csrfData.token_name, window.csrfData.hash);
      nativeFetch(endpoint, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: body.toString()})
        .then(function (response) {
          if (response.status < 200 || response.status >= 300 || response.redirected) captureStatus.failed = true;
        }, function () { captureStatus.failed = true; })
        .finally(function () {
          captureStatus.pending--;
          document.dispatchEvent(new Event('sc-debug-recorded'));
        });
    }
  }
  var nativeFetch = window.fetch.bind(window);
  window.addEventListener('error', function (event) {
    if (event.message) report('javascript', event.message, event.filename, event.lineno);
    else if (event.target && event.target !== window && /^(SCRIPT|LINK|IMG)$/.test(event.target.tagName)) {
      report('resource', 'Failed ' + event.target.tagName.toLowerCase() + ' resource', event.target.src || event.target.href, 0);
    }
  }, true);
  window.addEventListener('unhandledrejection', function (event) {
    // Arbitrary rejected objects can contain customer payloads; only report Error messages.
    report('promise', event.reason instanceof Error ? event.reason.message : 'Unhandled promise rejection', location.href, 0);
  });
  window.fetch = function () {
    var args = Array.prototype.slice.call(arguments), url = args[0] && args[0].url || args[0];
    if (visit && safePath(url).charAt(0) === '/') {
      var options = Object.assign({}, args[1] || {});
      var headers = new Headers(options.headers || (args[0] && args[0].headers) || {});
      headers.set('X-Sc-Debug-Visit', visit);
      options.headers = headers;
      args[1] = options;
    }
    return nativeFetch.apply(window, args).then(function (response) {
      if (response.status >= 400 && !ignored(url)) report('http', 'HTTP ' + response.status, url, 0);
      return response;
    }, function (error) { if (!ignored(url)) report('network', 'Fetch request failed', url, 0); throw error; });
  };
  var open = XMLHttpRequest.prototype.open;
  XMLHttpRequest.prototype.open = function (method, url) {
    this.addEventListener('loadend', function () {
      if ((this.status === 0 || this.status >= 400) && !ignored(url)) report('http', 'XHR HTTP ' + this.status, url, 0);
    }, {once: true});
    var result = open.apply(this, arguments);
    if (visit && safePath(url).charAt(0) === '/') this.setRequestHeader('X-Sc-Debug-Visit', visit);
    return result;
  };
  document.addEventListener('DOMContentLoaded', flush);
  window.addEventListener('load', flush);
})();

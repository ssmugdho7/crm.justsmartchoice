(function () {
  'use strict';
  var script = document.currentScript;
  var endpoint = script && script.getAttribute('data-endpoint');
  if (!endpoint || window.scDebugCaptureInstalled) return;
  window.scDebugCaptureInstalled = true;
  var sent = 0, seen = Object.create(null), pending = [];
  function safePath(value) {
    try { var url = new URL(value, location.href); return url.origin === location.origin ? url.pathname : '[external resource]'; }
    catch (_) { return '[unknown resource]'; }
  }
  function report(kind, message, file, line) {
    if (sent >= 20) return;
    var entry = {kind: kind, message: String(message || '').slice(0, 700), file: safePath(file || location.href), line: Number(line) || 0, route: location.pathname};
    var key = JSON.stringify(entry);
    if (seen[key]) return;
    seen[key] = true; sent++; pending.push(entry);
    flush();
  }
  // No form values, request/response bodies, headers or query strings are collected.
  function flush() {
    if (!window.csrfData || !window.csrfData.token_name || !window.csrfData.hash || document.readyState === 'loading') return;
    while (pending.length) {
      var entry = pending.shift(), body = new URLSearchParams();
      Object.keys(entry).forEach(function (key) { body.set(key, entry[key]); });
      body.set(window.csrfData.token_name, window.csrfData.hash);
      nativeFetch(endpoint, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: body.toString()}).catch(function () {});
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
    var args = arguments, url = args[0] && args[0].url || args[0];
    return nativeFetch.apply(window, args).then(function (response) {
      if (response.status >= 400 && safePath(url) !== safePath(endpoint)) report('http', 'HTTP ' + response.status, url, 0);
      return response;
    }, function (error) { report('network', 'Fetch request failed', url, 0); throw error; });
  };
  var open = XMLHttpRequest.prototype.open;
  XMLHttpRequest.prototype.open = function (method, url) {
    this.addEventListener('loadend', function () {
      if ((this.status === 0 || this.status >= 400) && safePath(url) !== safePath(endpoint)) report('http', 'XHR HTTP ' + this.status, url, 0);
    }, {once: true});
    return open.apply(this, arguments);
  };
  document.addEventListener('DOMContentLoaded', flush);
  window.addEventListener('load', flush);
})();

/* Admin navigation stability. Loaded immediately after the sidebar, before paint. */
(function (w, d) {
  'use strict';
  if (!d.body.classList.contains('admin')) return;
  var key = 'sc_open_menus:' + w.location.host;
  var menu = d.getElementById('side-menu');
  if (!menu) return;
  function id(list) {
    return Array.prototype.find.call(list.parentElement.classList, function (name) { return name.indexOf('menu-item-') === 0; }) || list.parentElement.id || '';
  }
  function lists() { return Array.prototype.slice.call(menu.querySelectorAll('ul.nav-second-level')); }
  function remember() {
    try { w.sessionStorage.setItem(key, JSON.stringify(lists().filter(function (el) {
      return el.classList.contains('in') || el.classList.contains('mm-show');
    }).map(id).filter(Boolean))); } catch (ignored) { /* Storage is optional. */ }
  }
  try {
    var saved = JSON.parse(w.sessionStorage.getItem(key) || '[]');
    if (Array.isArray(saved)) lists().forEach(function (list) {
      if (saved.indexOf(id(list)) === -1) return;
      list.classList.add('in');
      list.setAttribute('aria-expanded', 'true');
      list.parentElement.classList.add('active');
      var toggle = list.parentElement.querySelector('a');
      if (toggle) toggle.setAttribute('aria-expanded', 'true');
    });
  } catch (ignored) { /* Malformed or unavailable storage must not block navigation. */ }
  w.addEventListener('pagehide', remember);
  d.addEventListener('DOMContentLoaded', function () {
    var bar = d.createElement('div');
    bar.id = 'smartchoice-nav-progress';
    bar.setAttribute('aria-hidden', 'true');
    d.body.appendChild(bar);
    var timer;
    function reset() { w.clearTimeout(timer); bar.classList.remove('sc-navigating'); }
    w.addEventListener('pageshow', reset);
    menu.addEventListener('click', function (event) {
      var link = event.target.closest('a');
      if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.hasAttribute('download') || (link.target && link.target !== '_self') || link.hasAttribute('data-toggle')) return;
      var url;
      try { url = new URL(link.href, w.location.href); } catch (ignored) { return; }
      if (url.origin !== w.location.origin || url.pathname === w.location.pathname && url.search === w.location.search || !/^https?:$/.test(url.protocol)) return;
      remember();
      bar.classList.add('sc-navigating');
      // Reset if another handler cancels navigation or a download leaves this page open.
      timer = w.setTimeout(reset, 8000);
    });
  });
})(window, document);

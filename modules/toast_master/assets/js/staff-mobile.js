(function () {
  'use strict';
  function init() {
    var cfg = window.scStaffMobile;
    if (!cfg || !document.getElementById('header')) return;
    var mobile = matchMedia('(max-width:767px)');
    var body = document.body, menu = document.getElementById('menu');
    var setup = document.getElementById('setup-menu-wrapper');
    var labels = cfg.labels;
    var storage = 'sc-notices-' + cfg.staff + '-' + cfg.session;
    var dismissed = [];
    try { dismissed = JSON.parse(sessionStorage.getItem(storage)) || []; } catch (error) { /* Optional storage. */ }
    if (!Array.isArray(dismissed)) dismissed = [];
    function hideDrawer() {
      body.classList.remove('show-sidebar');
      if (setup && setup.classList.contains('display-block')) {
        var closer = setup.querySelector('.close-customizer');
        if (closer) closer.click();
      }
    }
    [menu, setup].filter(Boolean).forEach(function (drawer) {
      var close = document.createElement('button');
      close.type = 'button'; close.className = 'sc-staff-drawer-close';
      close.textContent = '× ' + labels.close;
      close.addEventListener('click', hideDrawer);
      drawer.prepend(close);
    });
    function syncDrawer() {
      if (menu) menu.inert = mobile.matches && !body.classList.contains('show-sidebar');
      if (setup) setup.inert = mobile.matches && !setup.classList.contains('display-block');
    }
    new MutationObserver(syncDrawer).observe(body, {attributes: true, attributeFilter: ['class']});
    if (setup) new MutationObserver(syncDrawer).observe(setup, {attributes: true, attributeFilter: ['class']});
    syncDrawer();
    document.addEventListener('keydown', function (event) {
      if (!mobile.matches) return;
      if (event.key === 'Escape') { hideDrawer(); return; }
      var drawer = body.classList.contains('show-sidebar') ? menu : (setup && setup.classList.contains('display-block') ? setup : null);
      if (!drawer || event.key !== 'Tab') return;
      var controls = Array.from(drawer.querySelectorAll('a[href],button,input,select')).filter(function (el) { return el.getClientRects().length && !el.disabled; });
      if (!controls.length) return;
      var first = controls[0], last = controls[controls.length - 1];
      if (!drawer.contains(document.activeElement) || (event.shiftKey && document.activeElement === first) || (!event.shiftKey && document.activeElement === last)) {
        event.preventDefault(); (event.shiftKey ? last : first).focus();
      }
    });
    document.querySelectorAll('.announcement').forEach(function (notice) {
      var close = notice.querySelector('a[href*="dismiss_announcement/"]');
      if (!close) return;
      var id = close.getAttribute('href');
      if (mobile.matches && dismissed.indexOf(id) !== -1) notice.classList.add('sc-session-dismissed');
      close.addEventListener('click', function (event) {
        if (!mobile.matches) return;
        event.preventDefault(); event.stopImmediatePropagation();
        notice.classList.add('sc-session-dismissed');
        if (dismissed.indexOf(id) === -1) dismissed.push(id);
        try { sessionStorage.setItem(storage, JSON.stringify(dismissed)); } catch (error) { /* Optional storage. */ }
      }, true);
    });
    body.classList.add('sc-staff-mobile-ready');
    var nav = document.createElement('nav');
    nav.className = 'sc-staff-mobile-nav'; nav.setAttribute('aria-label', labels.more);
    function item(label, icon, href, action) {
      var el = document.createElement(href ? 'a' : 'button');
      if (href) el.href = href; else el.type = 'button';
      var glyph = document.createElement('i'); glyph.className = 'fa-solid fa-' + icon; glyph.setAttribute('aria-hidden', 'true');
      var text = document.createElement('span'); text.textContent = label;
      el.append(glyph, text);
      if (href && location.pathname.replace(/\/$/, '') === new URL(href, location.href).pathname.replace(/\/$/, '')) el.setAttribute('aria-current', 'page');
      if (action) el.addEventListener('click', function (event) { event.stopPropagation(); action(event); });
      nav.appendChild(el);
    }
    item(labels.home, 'house', cfg.home);
    var tasks = document.querySelector('#side-menu a[href="' + cfg.home + 'tasks"]');
    if (tasks) item(labels.tasks, 'list-check', tasks.href);
    var quick = document.querySelector('#header .sc-admin-search-actions>.navbar-nav>li>a[data-toggle=dropdown]');
    if (quick) item(labels.create, 'plus', null, function () {
      hideDrawer();
      var dropdown = quick.parentElement.querySelector('.dropdown-menu');
      if (dropdown) dropdown.classList.add('sc-staff-mobile-quick');
      quick.click();
    });
    var settings = document.querySelector('#setup-menu-item .open-customizer');
    if (settings) item(labels.setup, 'gear', null, function () { body.classList.remove('show-sidebar'); settings.click(); });
    if (menu) item(labels.more, 'bars', null, function () {
      var open = body.classList.contains('show-sidebar'); hideDrawer();
      body.classList.toggle('show-sidebar', !open);
    });
    body.appendChild(nav);
    mobile.addEventListener('change', function () { hideDrawer(); syncDrawer(); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
}());

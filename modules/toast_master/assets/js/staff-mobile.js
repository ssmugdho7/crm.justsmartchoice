(function () {
  'use strict';
  function init() {
    var cfg = window.scStaffMobile;
    if (!cfg || !document.getElementById('header')) return;
    var mobile = matchMedia('(max-width:767px)');
    var body = document.body, menu = document.getElementById('menu');
    var setup = document.getElementById('setup-menu-wrapper');
    var labels = cfg.labels;
    var quickPanel, quickParent, quickNext, quickButton, quickTrigger, quickBackdrop, quickOpen = false;
    var search = document.getElementById('top_search');
    var searchInput = document.getElementById('search_input');
    var searchButton = document.querySelector('#top_search_button button');
    function closeSearch() {
      if (!search || !searchInput || !searchButton) return;
      search.classList.remove('sc-mobile-search-open');
      searchInput.value = ''; searchInput.blur();
      searchButton.classList.remove('search_remove');
      searchButton.innerHTML = '<i class="fa fa-search" aria-hidden="true"></i>';
      searchButton.setAttribute('aria-expanded', 'false');
      searchButton.setAttribute('aria-label', labels.search);
      var results = document.getElementById('search_results'); if (results) results.replaceChildren();
    }
    if (searchButton) searchButton.setAttribute('aria-label', labels.search);
    if (searchButton) searchButton.addEventListener('click', function (event) {
      if (!mobile.matches) return;
      event.preventDefault(); event.stopImmediatePropagation();
      if (search.classList.contains('sc-mobile-search-open')) { closeSearch(); return; }
      setQuick(false); hideDrawer();
      search.classList.add('sc-mobile-search-open');
      searchButton.classList.add('search_remove');
      searchButton.innerHTML = '<i class="fa fa-times" aria-hidden="true"></i>';
      searchButton.setAttribute('aria-expanded', 'true');
      searchButton.setAttribute('aria-label', labels.close);
      searchInput.focus();
    }, true);
    function setQuick(open, trigger) {
      if (!quickPanel || quickOpen === open) return;
      quickOpen = open;
      if (open) {
        hideDrawer(); closeSearch();
        quickTrigger = trigger;
        quickPanel.classList.add('sc-staff-mobile-quick');
        quickPanel.setAttribute('role', 'dialog'); quickPanel.setAttribute('aria-label', labels.create);
        quickBackdrop = document.createElement('button');
        quickBackdrop.type = 'button'; quickBackdrop.className = 'sc-staff-quick-backdrop';
        quickBackdrop.setAttribute('aria-label', labels.close);
        quickBackdrop.addEventListener('click', function () { setQuick(false); });
        body.append(quickBackdrop, quickPanel);
        var first = quickPanel.querySelector('a[href],button'); if (first) first.focus();
      } else {
        quickPanel.classList.remove('sc-staff-mobile-quick');
        quickPanel.removeAttribute('role'); quickPanel.removeAttribute('aria-label');
        quickParent.insertBefore(quickPanel, quickNext && quickNext.parentNode === quickParent ? quickNext : null);
        if (quickBackdrop) quickBackdrop.remove();
        if (quickTrigger) quickTrigger.focus();
      }
      if (quick) quick.setAttribute('aria-expanded', String(open));
      if (quickButton) quickButton.setAttribute('aria-expanded', String(open));
      if (nav) nav.classList.toggle('sc-action-open', open);
    }
    var storage = 'sc-notices-' + cfg.staff + '-' + cfg.session;
    var dismissed = [];
    try { dismissed = JSON.parse(sessionStorage.getItem(storage)) || []; } catch (error) { /* Optional storage. */ }
    if (!Array.isArray(dismissed)) dismissed = [];
    function hideDrawer() {
      body.classList.remove('show-sidebar');
      if (setup && setup.classList.contains('display-block')) {
        var closer = setup.querySelector('.close-customizer');
        if (closer) closer.click();
        // Native desktop closing relies on an exit animation, disabled on phones.
        setup.classList.remove('display-block', 'fadeInLeft', 'fadeInRight', 'fadeOutLeft', 'fadeOutRight');
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
      if (setupButton) setupButton.setAttribute('aria-expanded', String(mobile.matches && setup.classList.contains('display-block')));
      if (moreButton) moreButton.setAttribute('aria-expanded', String(mobile.matches && body.classList.contains('show-sidebar')));
      if (nav) nav.classList.toggle('sc-action-open', quickOpen || (mobile.matches && (body.classList.contains('show-sidebar') || (setup && setup.classList.contains('display-block')))));
    }
    new MutationObserver(syncDrawer).observe(body, {attributes: true, attributeFilter: ['class']});
    if (setup) new MutationObserver(syncDrawer).observe(setup, {attributes: true, attributeFilter: ['class']});
    syncDrawer();
    document.addEventListener('keydown', function (event) {
      if (!mobile.matches) return;
      if (event.key === 'Escape') { setQuick(false); closeSearch(); hideDrawer(); return; }
      var drawer = quickOpen ? quickPanel : (body.classList.contains('show-sidebar') ? menu : (setup && setup.classList.contains('display-block') ? setup : null));
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
      if (action) el.addEventListener('click', function (event) {
        event.stopPropagation();
        if (label !== labels.create) { setQuick(false); closeSearch(); }
        if (label !== labels.create) document.querySelectorAll('#header li.open').forEach(function (dropdown) { dropdown.classList.remove('open'); });
        action(event);
      });
      nav.appendChild(el);
      return el;
    }
    item(labels.home, 'house', cfg.home);
    var tasks = document.querySelector('#side-menu a[href="' + cfg.home + 'tasks"]');
    if (tasks) item(labels.tasks, 'list-check', tasks.href);
    var quick = document.querySelector('#header .sc-admin-search-actions>.navbar-nav>li>a[data-toggle=dropdown]');
    if (quick) {
      quickPanel = quick.parentElement.querySelector('.dropdown-menu');
      if (quickPanel) {
        quickPanel.addEventListener('click', function (event) { if (event.target.closest('a[href]')) setQuick(false); });
        quickParent = quickPanel.parentNode; quickNext = quickPanel.nextSibling;
        quickButton = item(labels.create, 'plus', null, function () { setQuick(!quickOpen, quickButton); });
        quickButton.setAttribute('aria-expanded', 'false');
        quick.addEventListener('click', function (event) {
          if (!mobile.matches) return;
          event.preventDefault(); event.stopImmediatePropagation(); setQuick(!quickOpen, quick);
        }, true);
      }
    }
    var settings = document.querySelector('#setup-menu-item .open-customizer');
    var setupButton;
    if (settings) setupButton = item(labels.setup, 'gear', null, function () {
      if (setup && setup.classList.contains('display-block')) hideDrawer();
      else { body.classList.remove('show-sidebar'); settings.click(); }
    });
    var moreButton;
    if (menu) moreButton = item(labels.more, 'bars', null, function () {
      var open = body.classList.contains('show-sidebar'); hideDrawer();
      body.classList.toggle('show-sidebar', !open);
    });
    body.appendChild(nav);
    syncDrawer();
    document.addEventListener('click', function (event) {
      if (mobile.matches && search && !search.contains(event.target) && !event.target.closest('#search_results')) closeSearch();
    });
    mobile.addEventListener('change', function () { setQuick(false); closeSearch(); hideDrawer(); syncDrawer(); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
}());

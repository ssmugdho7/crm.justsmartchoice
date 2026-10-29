// Presentation/navigation bridge only: no credentials, records or API calls.
(function () {
  'use strict';
  if (window.self === window.top && !window.flutter_inappwebview) return;
  const framed = window.self !== window.top;
  const allowed = new Set(['https://mobile.justsmartchoice.com', 'https://ssmugdho7.github.io']);
  let parentOrigin;
  function report() {
    if (parentOrigin) window.parent.postMessage({type:'sc-mobile-portal-location',url:location.href,overlay:!!document.querySelector('.modal.in,.modal.show,dialog[open]')}, parentOrigin);
  }
  const storage = 'sc-mobile-scroll:' + window.name;
  function pageKey() { return location.origin + location.pathname.replace(/\/$/, '') + location.search + location.hash; }
  function saveScroll() {
    if (!framed || !window.name.startsWith('sc-mobile-')) return;
    try {
      const saved = JSON.parse(sessionStorage.getItem(storage) || '{}');
      saved[pageKey()] = window.scrollY;
      const keys = Object.keys(saved);
      keys.slice(0, Math.max(0,keys.length - 30)).forEach(key => delete saved[key]);
      sessionStorage.setItem(storage, JSON.stringify(saved));
    } catch (_) { /* Private browsing may disable optional scroll storage. */ }
  }
  window.addEventListener('pagehide', saveScroll);
  window.addEventListener('message', function (event) {
    if (!framed || event.source !== window.parent || !allowed.has(event.origin) || !event.data) return;
    if (event.data.type === 'sc-mobile-portal-close-overlay') {
      const modal = document.querySelector('.modal.in,.modal.show,dialog[open]');
      if (modal && modal.tagName === 'DIALOG') modal.close();
      else if (modal && window.jQuery && window.jQuery.fn.modal) window.jQuery(modal).modal('hide');
      return;
    }
    if (event.data.type !== 'sc-mobile-portal-hello') return;
    parentOrigin = event.origin;
    report();
    try {
      const y = JSON.parse(sessionStorage.getItem(storage) || '{}')[pageKey()];
      if (Number.isFinite(y) && y >= 0) requestAnimationFrame(() => requestAnimationFrame(() => window.scrollTo(0,y)));
    } catch (_) { /* Navigation continues without stored scrolling. */ }
  });
  window.addEventListener('hashchange', report);
  window.addEventListener('popstate', report);
  document.addEventListener('sc-portal-overlay-change', report);
  function apply() {
    if (window.jQuery) window.jQuery(document).on('shown.bs.modal.scPortal hidden.bs.modal.scPortal',report);
    const chart = document.getElementById('commission-chart');
    if (chart) {
      const history = document.createElement('details');
      history.className = 'sc-commission-history';
      const phone = matchMedia('(max-width:767px)');
      history.open = !phone.matches;
      const summary = document.createElement('summary');
      summary.textContent = window.scPortalLabels && window.scPortalLabels.commissionChart || chart.closest('.widget').getAttribute('data-name');
      chart.before(history); history.append(summary,chart);
      history.addEventListener('toggle', function () {
        if (history.open && window.Highcharts) window.Highcharts.charts.filter(Boolean).forEach(function (item) {
          if (item.renderTo.id === 'commission_chart') item.reflow();
        });
      });
      phone.addEventListener('change', function () { history.open = !phone.matches; });
    }
    // Keep the original accessible Bootstrap control and collapse behavior.
    document.querySelectorAll('body:not(.admin) .navbar-toggle').forEach(function (button) {
      button.classList.add('sc-app-navigation-toggle');
      button.innerHTML = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>';
    });
    const style = document.createElement('style');
    style.textContent = '@media(max-width:767px){.sc-app-navigation-toggle{display:flex!important;align-items:center;justify-content:center;min-width:44px;min-height:44px;border-radius:12px!important}.sc-app-navigation-toggle[aria-expanded="true"]{background:#ffffff26!important}#widget-commission>.user-data,#widget-commission>.user-data>.panel-body{height:auto!important;min-height:0!important}#widget-commission .total-column{width:auto!important;min-width:0;padding:0!important}#widget-commission>.panel_s>.panel-body>.row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin:0!important}#widget-commission>.panel_s>.panel-body>.row::before,#widget-commission>.panel_s>.panel-body>.row::after{display:none}#widget-commission .row>.col-md-12:not(.total-column),#widget-commission #commission-chart{grid-column:1/-1}#widget-commission .total-column .panel_s{margin:0!important}#widget-commission .highcharts-figure{min-height:0!important;margin:0!important}#widget-commission #commission-chart:not(:has(svg,canvas)){display:none!important}}';
    document.head.appendChild(style);
    const chartStyle = document.createElement('style');
    chartStyle.textContent = '.sc-commission-history{grid-column:1/-1}.sc-commission-history>summary{display:none}@media(max-width:767px){.sc-commission-history>summary{display:list-item;min-height:44px;padding:12px;border:1px solid #dce5e2;border-radius:10px;color:#0e6f5b;background:#f4faf7;font-weight:600;cursor:pointer}.sc-commission-history>summary:focus-visible{outline:2px solid #238b76;outline-offset:2px}}';
    document.head.appendChild(chartStyle);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',apply,{once:true});
  else apply();
})();

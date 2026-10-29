(function () {
  'use strict';
  if (!/^\/admin\/(invoices|proposals|estimates|payments|credit_notes)(\/|$)/.test(location.pathname)) return;
  function organize() {
    const wrapper = document.getElementById('wrapper');
    if (!wrapper) return;
    let bar = wrapper.querySelector('._buttons,.sc-sales-action-bar');
    // Estimates use the same native actions, but historically lacked a wrapper.
    if (!bar && /^\/admin\/estimates\/?$/.test(location.pathname)) {
      const start = wrapper.querySelector('.new-estimate-btn,.switch-pipeline');
      if (start) {
        bar = document.createElement('div'); bar.className='sc-sales-action-bar';
        start.before(bar);
        let node = bar.nextElementSibling;
        while (node && !node.classList.contains('clearfix')) {
          const next = node.nextElementSibling; bar.appendChild(node); node = next;
        }
      }
    }
    const toolbar = wrapper.querySelector('.sales-center-generated-toolbar');
    if (!toolbar || toolbar.classList.contains('sc-sales-data-actions')) return;
    if (!bar) {
      const table = toolbar.closest('.panel-body,.panel-table-full');
      if (!table) return;
      bar = document.createElement('div'); bar.className='sc-sales-action-bar'; table.prepend(bar);
    }
    const holder = toolbar.parentElement;
    toolbar.classList.add('sc-sales-data-actions');
    // Move the existing nodes, keeping their permissions, URLs and event handlers.
    bar.appendChild(toolbar);
    if (holder.classList.contains('sales-center-toolbar-holder') && !holder.children.length) holder.remove();
  }
  function start() {
    organize();
    const wrapper = document.getElementById('wrapper');
    if (wrapper) new MutationObserver(organize).observe(wrapper,{childList:true,subtree:true});
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',start,{once:true});
  else start();
})();

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
    wrapper.querySelectorAll('a[href]').forEach(function (link) {
      let url;
      try { url = new URL(link.href); } catch (_) { return; }
      if (!/^\/proposal\/\d+\/[^/]+\/?$/.test(url.pathname) || url.origin !== location.origin || link.nextElementSibling?.classList.contains('sc-copy-proposal')) return;
      const copy = document.createElement('button'); copy.type = 'button'; copy.className = 'sc-copy-proposal sc-copy-transaction';
      copy.textContent = 'Copy link'; copy.setAttribute('aria-label', 'Copy public proposal link');
      copy.addEventListener('click', async function () {
        try {
          if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
          await navigator.clipboard.writeText(link.href); toast('Proposal link copied.', 'success');
        } catch (_) { toast('Unable to copy. Open the proposal link and copy its address manually.', 'error'); }
      });
      link.after(copy);
    });
    wrapper.querySelectorAll('.sales-transaction-id').forEach(function (transaction) {
      if (!transaction.textContent.trim() || transaction.nextElementSibling?.classList.contains('sc-copy-transaction')) return;
      const copy = document.createElement('button');
      copy.type = 'button'; copy.className = 'sc-copy-transaction';
      copy.textContent = 'Copy'; copy.setAttribute('aria-label', 'Copy transaction ID');
      copy.addEventListener('click', async function () {
        try {
          if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
          await navigator.clipboard.writeText(transaction.textContent);
          toast('Transaction ID copied.', 'success');
        } catch (_) { toast('Unable to copy. Select and copy the transaction ID manually.', 'error'); }
      });
      transaction.after(copy);
    });
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
  function toast(message, type) {
    let stack = document.querySelector('.sc-sales-toasts');
    if (!stack) {
      stack = document.createElement('div'); stack.className = 'sc-sales-toasts';
      stack.setAttribute('role', 'status'); stack.setAttribute('aria-live', 'polite'); document.body.appendChild(stack);
    }
    const item = document.createElement('div'); item.className = 'sc-sales-toast';
    item.dataset.type = type; item.textContent = message; stack.appendChild(item);
    window.setTimeout(function () { item.classList.add('sc-dismiss'); window.setTimeout(function () { item.remove(); }, 200); }, 4000);
  }
  function start() {
    organize();
    const wrapper = document.getElementById('wrapper');
    if (wrapper) new MutationObserver(organize).observe(wrapper,{childList:true,subtree:true});
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',start,{once:true});
  else start();
})();

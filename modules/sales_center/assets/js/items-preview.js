(function () {
  'use strict';
  const panel = document.getElementById('sc-item-preview');
  if (!panel) return;
  const layout = panel.closest('.sc-items-layout');
  function close() {
    panel.classList.add('hide');
    layout.classList.remove('sc-item-selected');
  }
  document.getElementById('sc-item-preview-close').addEventListener('click', close);
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !document.querySelector('.modal.in')) close();
  });
  document.addEventListener('click', function (event) {
    const link = event.target.closest('.table-invoice-items tbody td > a[data-target="#sales_item_modal"][data-id]');
    if (!link) return;
    event.preventDefault();
    event.stopPropagation();
    const row = link.closest('tr');
    document.getElementById('sc-item-preview-title').textContent = link.textContent.trim();
    const fields = document.getElementById('sc-item-preview-fields');
    fields.replaceChildren();
    const headings = row.closest('table').querySelectorAll('thead th');
    Array.from(row.cells).slice(2).forEach(function (cell, index) {
      const dt = document.createElement('dt'), dd = document.createElement('dd');
      dt.textContent = headings[index + 2]?.textContent.trim() || '';
      dd.textContent = cell.textContent.trim() || '—';
      fields.append(dt, dd);
    });
    const edit = document.getElementById('sc-item-preview-edit');
    if (edit) edit.setAttribute('data-id', link.getAttribute('data-id'));
    panel.classList.remove('hide');
    layout.classList.add('sc-item-selected');
    if (window.jQuery) window.jQuery('.table-invoice-items').DataTable().columns.adjust();
    panel.focus({preventScroll: true});
  }, true);
})();

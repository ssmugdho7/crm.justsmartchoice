(function () {
  'use strict';

  const label = document.currentScript.dataset.resizeLabel;
  const key = 'sc-invoice-list-width';
  const defaultWidth = 100 * 5 / 12;
  const desktop = window.matchMedia('(min-width: 992px)');

  window.jQuery(function () {
    const list = document.getElementById('small-table');
    const preview = document.getElementById('invoice');
    const right = preview && preview.closest('.small-table-right-col');
    if (!list || !right || list.parentElement !== right.parentElement) return;

    const row = list.parentElement;
    const handle = document.createElement('div');
    handle.className = 'sc-invoice-divider';
    handle.tabIndex = 0;
    handle.setAttribute('role', 'separator');
    handle.setAttribute('aria-orientation', 'vertical');
    handle.setAttribute('aria-controls', 'small-table invoice');
    handle.setAttribute('aria-label', label);
    handle.title = label;
    right.prepend(handle);

    let preferred = defaultWidth;
    try { preferred = Number(localStorage.getItem(key)) || defaultWidth; } catch (_) {}
    let current = preferred;
    let width = 0;
    let min = 0;
    let max = 100;
    let drag;

    function render(value) {
      current = Math.min(max, Math.max(min, value));
      row.style.setProperty('--sc-invoice-list-width', current + '%');
      handle.setAttribute('aria-valuemin', Math.round(min));
      handle.setAttribute('aria-valuemax', Math.round(max));
      handle.setAttribute('aria-valuenow', Math.round(current));
    }

    function save() {
      preferred = current;
      try { localStorage.setItem(key, String(preferred)); } catch (_) {}
      window.dispatchEvent(new Event('resize'));
    }

    function endDrag(event) {
      if (!drag || (event && event.pointerId !== drag.id)) return;
      const id = drag.id;
      drag = null;
      document.body.classList.remove('sc-invoice-resizing');
      if (handle.hasPointerCapture(id)) handle.releasePointerCapture(id);
      save();
    }

    function sync() {
      width = row.getBoundingClientRect().width;
      const active = desktop.matches && width >= 700 && list.classList.contains('col-md-5') &&
        !list.classList.contains('hide') && right.classList.contains('col-md-7') && !preview.classList.contains('hide');
      row.classList.toggle('sc-invoice-split', active);
      if (!active) {
        endDrag();
        return;
      }
      min = Math.max(28, 100 * 280 / width);
      max = Math.min(72, 100 - 100 * 360 / width);
      render(drag ? current : preferred);
    }

    function direction() { return getComputedStyle(row).direction === 'rtl' ? -1 : 1; }

    handle.addEventListener('pointerdown', function (event) {
      if (event.button !== 0 || !event.isPrimary || drag) return;
      drag = { id: event.pointerId, x: event.clientX, value: current };
      handle.setPointerCapture(event.pointerId);
      document.body.classList.add('sc-invoice-resizing');
    });
    handle.addEventListener('pointermove', function (event) {
      if (drag && event.pointerId === drag.id) {
        render(drag.value + direction() * (event.clientX - drag.x) * 100 / width);
      }
    });
    ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(name => handle.addEventListener(name, endDrag));
    handle.addEventListener('keydown', function (event) {
      const positions = { ArrowLeft: current - 3 * direction(), ArrowRight: current + 3 * direction(), Home: min, End: max };
      if (!Object.prototype.hasOwnProperty.call(positions, event.key)) return;
      event.preventDefault();
      render(positions[event.key]);
      save();
    });
    handle.addEventListener('dblclick', function () {
      render(defaultWidth);
      save();
    });

    const observer = new MutationObserver(sync);
    [list, right, preview].forEach(pane => observer.observe(pane, { attributes: true, attributeFilter: ['class'] }));
    new ResizeObserver(sync).observe(row);
    desktop.addEventListener('change', sync);
    sync();
  });
})();

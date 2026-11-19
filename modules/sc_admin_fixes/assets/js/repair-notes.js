(function () {
    'use strict';
    function init() {
        var table = document.querySelector('#development-repairs-table') || document.querySelector('#wrapper table.dt-table');
        if (!table || table.classList.contains('sc-repair-resizable')) return;
        var link = table.querySelector('tbody a[href*="/development/view/"]');
        if (!link || !table.tHead) return;
        var index = link.closest('td').cellIndex;
        var headers = Array.from(table.tHead.rows[0].cells);
        var header = headers[index];
        var widths = headers.map(function (cell) { return Math.max(35, Math.ceil(cell.getBoundingClientRect().width)); });
        var group = table.querySelector('colgroup');
        if (!group) { group = document.createElement('colgroup'); table.prepend(group); }
        while (group.children.length < headers.length) group.append(document.createElement('col'));
        var key = 'sc-repair-title-width-v1';
        var width = Math.max(220, widths[index]);
        try { var saved = Number(localStorage.getItem(key)); if (saved >= 220 && saved <= 2000) width = saved; } catch (_) {}
        table.classList.add('sc-repair-resizable');
        header.classList.add('sc-repair-title-cell');
        var handle = document.createElement('button');
        handle.type = 'button';
        handle.className = 'sc-repair-column-resizer';
        handle.setAttribute('role', 'separator');
        handle.setAttribute('aria-orientation', 'vertical');
        handle.setAttribute('aria-label', 'Resize Problem / Repair column');
        handle.setAttribute('aria-valuemin', '220');
        handle.setAttribute('aria-valuemax', '2000');
        handle.title = 'Drag to resize. Use arrow keys, or double-click to fit titles.';
        header.append(handle);
        function apply(value, persist) {
            width = Math.max(220, Math.min(2000, Math.round(value)));
            widths[index] = width;
            Array.from(group.children).forEach(function (col, i) { col.style.setProperty('width', widths[i] + 'px', 'important'); });
            table.style.setProperty('width', widths.reduce(function (a, b) { return a + b; }, 0) + 'px', 'important');
            headers.forEach(function (cell, i) { cell.style.setProperty('width', widths[i] + 'px', 'important'); });
            Array.from(table.tBodies).forEach(function (body) {
                Array.from(body.rows).forEach(function (row) { if (row.cells.length === headers.length) row.cells[index].classList.add('sc-repair-title-cell'); });
            });
            handle.setAttribute('aria-valuenow', width);
            if (persist) { try { localStorage.setItem(key, String(width)); } catch (_) {} }
        }
        function fit() {
            var context = document.createElement('canvas').getContext('2d');
            if (!context) return width;
            context.font = getComputedStyle(link).font;
            var links = Array.from(table.querySelectorAll('a[href*="/development/view/"]'));
            return Math.max.apply(null, [220].concat(links.map(function (a) { return context.measureText(a.textContent).width + 40; })));
        }
        handle.addEventListener('click', function (event) { event.preventDefault(); event.stopPropagation(); });
        handle.addEventListener('dblclick', function (event) { event.preventDefault(); event.stopPropagation(); apply(fit(), true); });
        handle.addEventListener('keydown', function (event) {
            var delta = event.shiftKey ? 80 : 20;
            var next = {ArrowLeft: width - delta, ArrowRight: width + delta, Home: 220, End: fit()}[event.key];
            if (next !== undefined) { event.preventDefault(); event.stopPropagation(); apply(next, true); }
        });
        handle.addEventListener('pointerdown', function (event) {
            if (event.button !== 0) return;
            event.preventDefault(); event.stopPropagation();
            var start = event.clientX, initial = width;
            handle.setPointerCapture(event.pointerId);
            function move(e) { apply(initial + e.clientX - start, false); }
            function end() { handle.removeEventListener('pointermove', move); handle.removeEventListener('pointerup', end); handle.removeEventListener('pointercancel', end); apply(width, true); }
            handle.addEventListener('pointermove', move);
            handle.addEventListener('pointerup', end);
            handle.addEventListener('pointercancel', end);
        });
        apply(width, false);
        if (window.jQuery) window.jQuery(table).on('draw.dt.scRepairTitles', function () { apply(width, false); });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();

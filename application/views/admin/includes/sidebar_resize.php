<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
#sc-admin-sidebar-resizer{position:fixed;width:6px;z-index:1002;cursor:col-resize;touch-action:none;outline:none}
#sc-admin-sidebar-resizer[hidden]{display:none!important}
#sc-admin-sidebar-resizer::after{content:"";position:absolute;inset:0 2px;background:transparent;transition:background .15s}
#sc-admin-sidebar-resizer:hover::after,#sc-admin-sidebar-resizer:focus-visible::after,body.sc-sidebar-dragging #sc-admin-sidebar-resizer::after{background:#11846e}
body.sc-sidebar-dragging,body.sc-sidebar-dragging *{cursor:col-resize!important;user-select:none!important}
body.sc-sidebar-dragging #menu,body.sc-sidebar-dragging #wrapper{transition:none!important}
@media(min-width:769px){
 body.admin.sc-sidebar-sized:not(.page-small) #menu,body.admin.sc-sidebar-sized:not(.page-small) #setup-menu-wrapper{width:var(--sc-admin-sidebar-width)!important}
 body.admin.sc-sidebar-sized:not(.page-small):not(.hide-sidebar):not(.rtl) #wrapper{margin-left:var(--sc-admin-sidebar-width)!important}
 body.admin.sc-sidebar-sized:not(.page-small).hide-sidebar:not(.show-sidebar):not(.rtl) #menu,
 body.admin.sc-sidebar-sized:not(.page-small).hide-sidebar:not(.show-sidebar):not(.rtl) #setup-menu-wrapper{left:0!important;margin-left:calc(-1 * var(--sc-admin-sidebar-width))!important}
 body.admin.sc-sidebar-sized:not(.page-small):not(.hide-sidebar).rtl #wrapper{margin-right:var(--sc-admin-sidebar-width)!important}
 body.admin.sc-sidebar-sized:not(.page-small).hide-sidebar:not(.show-sidebar).rtl #menu,
 body.admin.sc-sidebar-sized:not(.page-small).hide-sidebar:not(.show-sidebar).rtl #setup-menu-wrapper{right:0!important;margin-right:calc(-1 * var(--sc-admin-sidebar-width))!important}
}
</style>
<script>
(function () {
    function initSidebarResize() {
        var menu = document.getElementById('menu'), body = document.body;
        if (!menu || !body.classList.contains('admin') || document.getElementById('sc-admin-sidebar-resizer')) return;
        var min = 200, max = 380, initial = Math.round(menu.getBoundingClientRect().width) || 230;
        var user = body.className.match(/\buser-id-(\d+)\b/);
        var storageKey = 'sc.admin.sidebar.width.' + (user ? user[1] : 'default');
        var preferred = initial, drag = null;
        try {
            var saved = Number(window.localStorage.getItem(storageKey));
            if (Number.isFinite(saved) && saved >= min && saved <= max) preferred = saved;
        } catch (error) { /* Resizing still works when browser storage is disabled. */ }
        var handle = document.createElement('div');
        handle.id = 'sc-admin-sidebar-resizer';
        handle.tabIndex = 0;
        handle.setAttribute('role', 'separator');
        handle.setAttribute('aria-label', 'Resize sidebar');
        handle.setAttribute('aria-orientation', 'vertical');
        handle.setAttribute('aria-controls', 'menu');
        handle.setAttribute('aria-valuemin', String(min));
        handle.title = 'Drag to resize the sidebar. Use arrow keys, or double-click to reset.';
        body.appendChild(handle);

        function limit() { return Math.max(min, Math.min(max, Math.floor(window.innerWidth * 0.4))); }
        function width() { return Math.max(min, Math.min(limit(), preferred)); }
        function visible() {
            return window.innerWidth >= 769 && !body.classList.contains('page-small') &&
                !body.classList.contains('hide-sidebar') && !body.classList.contains('modal-open');
        }
        function position() {
            handle.hidden = !visible();
            if (handle.hidden) return;
            var rect = menu.getBoundingClientRect();
            handle.style.left = (body.classList.contains('rtl') ? rect.left - 4 : rect.right - 2) + 'px';
            handle.style.top = Math.max(0, rect.top) + 'px';
            handle.style.height = Math.max(0, Math.min(window.innerHeight, rect.bottom) - Math.max(0, rect.top)) + 'px';
        }
        function render() {
            body.style.setProperty('--sc-admin-sidebar-width', width() + 'px');
            if (!body.classList.contains('sc-sidebar-sized')) body.classList.add('sc-sidebar-sized');
            handle.setAttribute('aria-valuemax', String(limit()));
            handle.setAttribute('aria-valuenow', String(width()));
            position();
            if (!visible() && drag) finish(false);
        }
        function save() {
            try { window.localStorage.setItem(storageKey, String(preferred)); } catch (error) {}
        }
        function finish(persist) {
            if (!drag) return;
            var pointer = drag.id;
            if (!persist) preferred = drag.preferred;
            drag = null;
            body.classList.remove('sc-sidebar-dragging');
            if (handle.hasPointerCapture(pointer)) handle.releasePointerCapture(pointer);
            render();
            if (persist) save();
        }
        handle.addEventListener('pointerdown', function (event) {
            if (event.button !== 0 || !visible() || drag) return;
            event.preventDefault();
            handle.focus({preventScroll: true});
            drag = {id: event.pointerId, x: event.clientX, width: width(), preferred: preferred};
            try { handle.setPointerCapture(event.pointerId); }
            catch (error) { drag = null; return; }
            body.classList.add('sc-sidebar-dragging');
        });
        handle.addEventListener('pointermove', function (event) {
            if (!drag || drag.id !== event.pointerId) return;
            var direction = body.classList.contains('rtl') ? -1 : 1;
            preferred = Math.max(min, Math.min(limit(), drag.width + (event.clientX - drag.x) * direction));
            render();
        });
        handle.addEventListener('pointerup', function (event) { if (drag && drag.id === event.pointerId) finish(true); });
        handle.addEventListener('pointercancel', function () { finish(false); });
        handle.addEventListener('lostpointercapture', function () { finish(false); });
        window.addEventListener('blur', function () { finish(false); });
        handle.addEventListener('dblclick', function () { preferred = initial; render(); save(); });
        handle.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && drag) { finish(false); return; }
            if (!visible() || drag) return;
            var direction = body.classList.contains('rtl') ? -1 : 1;
            if (event.key === 'ArrowRight') preferred = width() + 10 * direction;
            else if (event.key === 'ArrowLeft') preferred = width() - 10 * direction;
            else if (event.key === 'Home') preferred = min;
            else if (event.key === 'End') preferred = limit();
            else if (event.key === 'Enter') preferred = initial;
            else return;
            event.preventDefault();
            preferred = Math.max(min, Math.min(limit(), preferred));
            render(); save();
        });
        window.addEventListener('resize', render);
        new MutationObserver(render).observe(body, {attributes: true, attributeFilter: ['class']});
        if (window.ResizeObserver) new ResizeObserver(position).observe(menu);
        render();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initSidebarResize);
    else initSidebarResize();
})();
</script>

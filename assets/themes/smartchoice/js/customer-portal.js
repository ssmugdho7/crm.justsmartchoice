(function () {
    'use strict';
    function init() {
        var layout = document.querySelector('#wrapper.sc-portal-layout');
        if (!layout) return;
        layout.classList.add('sc-portal-enhanced');
        var sidebar = layout.querySelector('.sc-portal-sidebar');
        var toggle = layout.querySelector('.sc-portal-menu-button');
        var close = layout.querySelector('.sc-portal-close');
        var collapse = layout.querySelector('.sc-portal-collapse');
        var backdrop = layout.querySelector('.sc-portal-backdrop');
        var mobile = window.matchMedia('(max-width: 991px)');
        var previousFocus;

        function setOpen(open) {
            layout.classList.toggle('sc-portal-open', open);
            toggle.setAttribute('aria-expanded', String(open));
            backdrop.hidden = !open;
            sidebar.inert = mobile.matches && !open;
            if (open) {
                previousFocus = document.activeElement;
                close.focus();
            } else if (previousFocus) {
                previousFocus.focus();
                previousFocus = null;
            }
        }
        toggle.addEventListener('click', function () { setOpen(!layout.classList.contains('sc-portal-open')); });
        close.addEventListener('click', function () { setOpen(false); });
        backdrop.addEventListener('click', function () { setOpen(false); });
        collapse.addEventListener('click', function () {
            var collapsed = layout.classList.toggle('sc-portal-collapsed');
            collapse.setAttribute('aria-expanded', String(!collapsed));
            collapse.title = collapse.ariaLabel = collapsed ? 'Expand customer navigation' : 'Collapse customer navigation';
            try { localStorage.setItem('sc-portal-collapsed', collapsed ? '1' : '0'); } catch (error) { /* Storage may be disabled. */ }
        });
        try {
            if (localStorage.getItem('sc-portal-collapsed') === '1') {
                layout.classList.add('sc-portal-collapsed');
                collapse.setAttribute('aria-expanded', 'false');
                collapse.title = collapse.ariaLabel = 'Expand customer navigation';
            }
        } catch (error) { /* Storage may be disabled. */ }
        document.addEventListener('keydown', function (event) {
            if (!mobile.matches || !layout.classList.contains('sc-portal-open')) return;
            if (event.key === 'Escape') { setOpen(false); return; }
            if (event.key !== 'Tab') return;
            var focusable = Array.from(sidebar.querySelectorAll('a, button')).filter(function (element) { return element.getClientRects().length; });
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });
        mobile.addEventListener('change', function () { setOpen(false); });
        sidebar.inert = mobile.matches;

        layout.querySelectorAll('.invoices-stats .progress-bar[data-percent]').forEach(function (bar) {
            var percent = Math.max(0, Math.min(100, Number(bar.dataset.percent) || 0));
            bar.style.width = percent + '%';
            bar.setAttribute('aria-valuenow', String(percent));
        });

        // Some modules append header links through hooks instead of the theme menu.
        // Copy only already-rendered, same-origin links; leave the header untouched.
        document.querySelectorAll('.navbar.header .navbar-nav > li:not(.dropdown) > a[href]').forEach(function (link) {
            var url;
            try { url = new URL(link.href); } catch (error) { return; }
            if (url.origin !== window.location.origin || url.hash || !link.textContent.trim()) return;
            if (url.pathname.replace(/\/$/, '') === new URL(sidebar.querySelector('a').href).pathname.replace(/\/$/, '')) return;
            var exists = Array.from(sidebar.querySelectorAll('a')).some(function (item) { return item.href === link.href; });
            if (exists) return;
            var group = sidebar.querySelector('[data-portal-group="Services & Booking"]');
            var item = document.createElement('a');
            var icon = document.createElement('i');
            var label = document.createElement('span');
            item.href = link.href;
            var headerTitle = link.querySelector('.gm-client-nav-title');
            item.title = label.textContent = (headerTitle || link).textContent.trim();
            icon.className = 'fa-solid fa-arrow-up-right-from-square';
            icon.setAttribute('aria-hidden', 'true');
            if (url.pathname.replace(/\/$/, '') === window.location.pathname.replace(/\/$/, '')) item.setAttribute('aria-current', 'page');
            item.append(icon, label);
            group.appendChild(item);
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
}());

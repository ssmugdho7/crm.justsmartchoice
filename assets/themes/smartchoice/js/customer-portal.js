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
        var more = layout.querySelector('.sc-portal-more');

        function setOpen(open) {
            layout.classList.toggle('sc-portal-open', open);
            toggle.setAttribute('aria-expanded', String(open));
            if (more) more.setAttribute('aria-expanded', String(open));
            document.body.classList.toggle('sc-portal-drawer-open', open);
            if (open && mobile.matches) { sidebar.setAttribute('role', 'dialog'); sidebar.setAttribute('aria-modal', 'true'); }
            else { sidebar.removeAttribute('role'); sidebar.removeAttribute('aria-modal'); }
            backdrop.hidden = !open;
            sidebar.inert = mobile.matches && !open;
            if (open) {
                previousFocus = document.activeElement;
                // Focus after the drawer visibility transition has started.
                requestAnimationFrame(function () {
                    if (layout.classList.contains('sc-portal-open')) close.focus();
                });
            } else if (previousFocus) {
                previousFocus.focus();
                previousFocus = null;
            }
        }
        toggle.addEventListener('click', function () { setOpen(!layout.classList.contains('sc-portal-open')); });
        if (more) more.addEventListener('click', function () { setOpen(!layout.classList.contains('sc-portal-open')); });
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
            if (!sidebar.contains(document.activeElement)) { event.preventDefault(); (event.shiftKey ? last : first).focus(); }
            else if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });
        mobile.addEventListener('change', function () { setOpen(false); });
        sidebar.inert = mobile.matches;

        layout.querySelectorAll('.invoices-stats .progress-bar[data-percent]').forEach(function (bar) {
            var percent = Math.max(0, Math.min(100, Number(bar.dataset.percent) || 0));
            bar.style.width = percent + '%';
            bar.setAttribute('aria-valuenow', String(percent));
        });

        // Keep Projects, Support and Meetings in the header; retain other granted destinations in the sidebar.
        function syncHeaderLinks() {
        document.querySelectorAll('.navbar.header .navbar-nav > li:not(.dropdown) > a[href]').forEach(function (link) {
            var url;
            try { url = new URL(link.href); } catch (error) { return; }
            if (url.origin !== window.location.origin || url.hash || !link.textContent.trim()) return;
            if (url.pathname.replace(/\/$/, '') === new URL(sidebar.querySelector('a').href).pathname.replace(/\/$/, '')) {
                link.parentElement.classList.add('sc-portal-nav-moved');
                return;
            }
            var exists = Array.from(sidebar.querySelectorAll('a')).some(function (item) { return item.href === link.href; });
            var keepHeader = /\/clients\/(?:projects|tickets)(?:\/|$)/.test(url.pathname) || /\/google_meet\/meeting_clients\/meetings(?:\/|$)/.test(url.pathname);
            if (exists) {
                link.parentElement.classList.toggle('sc-portal-nav-moved', !keepHeader);
                return;
            }
            var group = sidebar.querySelector('[data-portal-group="Services & Booking"]');
            if (!group) {
                group = document.createElement('div');
                group.className = 'sc-portal-nav-group';
                group.dataset.portalGroup = 'Services & Booking';
                var heading = document.createElement('h2');
                heading.textContent = 'Services & Booking';
                group.appendChild(heading);
                sidebar.querySelector('nav').appendChild(group);
            }
            var item = document.createElement('a');
            var icon = document.createElement('i');
            var label = document.createElement('span');
            item.href = link.href;
            var headerTitle = link.querySelector('.gm-client-nav-title');
            item.title = label.textContent = (headerTitle || link).textContent.trim();
            item.setAttribute('aria-label', item.title);
            icon.className = 'fa-solid fa-arrow-up-right-from-square';
            icon.setAttribute('aria-hidden', 'true');
            if (url.pathname.replace(/\/$/, '') === window.location.pathname.replace(/\/$/, '')) item.setAttribute('aria-current', 'page');
            item.append(icon, label);
            group.appendChild(item);
            link.parentElement.classList.toggle('sc-portal-nav-moved', !keepHeader);
        });
        }
        syncHeaderLinks();
        var headerList = document.querySelector('.navbar.header .navbar-nav');
        if (headerList) new MutationObserver(syncHeaderLinks).observe(headerList, {childList: true, subtree: true});
        // Hide only links that remain available in the sidebar.
        document.body.classList.add('sc-customer-enhanced');
        var current = sidebar.querySelector('a[aria-current="page"]');
        if (current) layout.querySelectorAll('.sc-portal-mobile-nav a').forEach(function (link) {
            if (link.href === current.href) link.setAttribute('aria-current', 'page');
        });

        function labelTables() {
            layout.querySelectorAll('table.dt-table').forEach(function (table) {
                var headers = Array.from(table.querySelectorAll('thead tr:first-child th'));
                if (!headers.length) return;
                table.classList.add('sc-mobile-cards');
                table.querySelectorAll('tbody tr').forEach(function (row) {
                    Array.from(row.cells).forEach(function (cell, index) {
                        if (cell.colSpan !== 1 || !headers[index]) return;
                        cell.dataset.label = headers[index].textContent.trim();
                    });
                });
                table.querySelectorAll('.dataTables_empty').forEach(function (cell) {
                    cell.setAttribute('role', 'status');
                    if (!cell.querySelector('.sc-filter-help')) {
                        var help = document.createElement('p');
                        help.className = 'sc-filter-help';
                        help.textContent = 'No matching records in this view. Try clearing your search or changing the status filter.';
                        cell.appendChild(help);
                    }
                });
            });
        }
        labelTables();
        if (window.jQuery) window.jQuery(document).on('draw.dt.scPortal', function (event) {
            if (layout.contains(event.target)) labelTables();
        });

    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
}());

(function () {
    'use strict';
    function initLanguages() {
        document.querySelectorAll('.customers-nav-item-languages').forEach(function (item) {
            var trigger = item.querySelector('.sc-language-toggle');
            var menu = item.querySelector('.sc-language-options');
            if (!trigger || !menu) return;
            function setOpen(open) {
                item.classList.toggle('sc-language-open', open);
                trigger.setAttribute('aria-expanded', String(open));
                menu.inert = !open;
            }
            setOpen(false);
            item.addEventListener('mouseenter', function () {
                if (window.matchMedia('(hover: hover)').matches) setOpen(true);
            });
            item.addEventListener('mouseleave', function () {
                if (!item.contains(document.activeElement)) setOpen(false);
            });
            trigger.addEventListener('click', function (event) {
                event.preventDefault(); event.stopPropagation();
                setOpen(!item.classList.contains('sc-language-open'));
            });
            item.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' || event.key === 'ArrowRight') {
                    if (item.classList.contains('sc-language-open')) {
                        event.preventDefault(); event.stopPropagation(); setOpen(false); trigger.focus();
                    }
                } else if (event.target === trigger && (event.key === 'ArrowLeft' || event.key === 'ArrowDown' || event.key === ' ')) {
                    event.preventDefault(); event.stopPropagation(); setOpen(true);
                    var first = menu.querySelector('a');
                    if (first) {
                        first.focus();
                        if (window.requestAnimationFrame) window.requestAnimationFrame(function () { if (item.classList.contains('sc-language-open')) first.focus(); });
                    }
                }
            });
            item.addEventListener('focusout', function (event) {
                if (!item.contains(event.relatedTarget)) setOpen(false);
            });
            if (window.jQuery) window.jQuery(item.closest('.customers-nav-item-profile')).on('hidden.bs.dropdown.scLanguages', function () { setOpen(false); });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initLanguages);
    else initLanguages();
}());

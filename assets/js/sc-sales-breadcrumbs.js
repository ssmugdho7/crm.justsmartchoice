(function (document, window) {
    'use strict';

    function init() {
        var configNode = document.getElementById('sc-sales-breadcrumbs-config');
        var content = document.querySelector('#wrapper > .content');
        if (!configNode || !content || document.getElementById('sc-sales-breadcrumbs')) return;
        var config;
        try { config = JSON.parse(configNode.textContent); } catch (error) { return; }

        var admin;
        try { admin = new URL(config.adminUrl, window.location.href); } catch (error) { return; }
        var root = admin.pathname.replace(/\/+$/, '') + '/';
        if (admin.origin !== window.location.origin || window.location.pathname.indexOf(root) !== 0) return;
        var currentPath = window.location.pathname.replace(/\/+$/, '');
        // Reuse the rendered, permission-filtered menu. No extra requests or permissions are introduced.
        var groups = document.querySelectorAll('#side-menu > .menu-item-sales, #side-menu > .menu-item-sales-center-main');
        var entries = [], seen = Object.create(null);
        groups.forEach(function (group) {
            group.querySelectorAll('.nav-second-level > li > a[href]').forEach(function (link) {
                var url;
                if (!link.getAttribute('href').trim() || link.getAttribute('href').trim().charAt(0) === '#') return;
                try { url = new URL(link.getAttribute('href'), window.location.href); } catch (error) { return; }
                if (url.origin !== admin.origin || url.pathname.indexOf(root) !== 0) return;
                var path = url.pathname.replace(/\/+$/, '');
                var nameNode = link.querySelector('.sub-menu-text');
                var label = (nameNode ? nameNode.textContent : link.textContent).trim();
                if (!label || path === root.slice(0, -1) || seen[path]) return;
                seen[path] = true;
                entries.push({ path: path, href: url.href, label: label });
            });
        });
        var matches = entries.filter(function (entry) {
            return currentPath === entry.path || currentPath.indexOf(entry.path + '/') === 0;
        }).sort(function (a, b) { return b.path.length - a.path.length; });
        if (!matches.length) return;
        var active = matches[0];
        var suffix = currentPath.slice(active.path.length).replace(/^\//, '');
        var isList = /^(?:index|manage|list_invoices|list_estimates|list_proposals)?$/.test(suffix);

        function element(tag, className, text) {
            var node = document.createElement(tag);
            if (className) node.className = className;
            if (text) node.textContent = text;
            return node;
        }
        function addLink(list, label, href) {
            var item = element('li');
            var link = element('a', '', label);
            link.href = href;
            item.appendChild(link);
            list.appendChild(item);
            return link;
        }
        var nav = element('nav', 'sc-sales-breadcrumbs');
        nav.id = 'sc-sales-breadcrumbs';
        nav.setAttribute('aria-label', config.sales + ' — ' + config.dashboard);
        var trail = element('ol', 'sc-sales-breadcrumbs__trail');
        addLink(trail, config.dashboard, admin.href);
        var salesItem = element('li', 'sc-sales-breadcrumbs__sales');
        var toggle = element('button', 'sc-sales-breadcrumbs__toggle', config.sales);
        toggle.type = 'button';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-controls', 'sc-sales-breadcrumbs-links');
        var links = element('ul', 'sc-sales-breadcrumbs__links');
        links.id = 'sc-sales-breadcrumbs-links';
        links.hidden = true;
        entries.forEach(function (entry) {
            var link = addLink(links, entry.label, entry.href);
            if (entry.path === active.path) link.setAttribute('aria-current', 'true');
        });
        salesItem.appendChild(toggle);
        salesItem.appendChild(links);
        trail.appendChild(salesItem);
        if (!isList) addLink(trail, active.label, active.href);
        var currentLabel = active.label;
        if (!isList) {
            currentLabel = config.title && config.title !== active.label ? config.title :
                (/\d+$/.test(suffix) ? active.label + ' #' + suffix.match(/\d+$/)[0] : config.view + ' — ' + active.label);
        }
        var current = element('li', 'sc-sales-breadcrumbs__current', currentLabel);
        current.setAttribute('aria-current', 'page');
        trail.appendChild(current);
        nav.appendChild(trail);
        content.insertBefore(nav, content.firstChild);

        function setOpen(open, returnFocus) {
            links.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (returnFocus) toggle.focus();
        }
        toggle.addEventListener('click', function () { setOpen(links.hidden); });
        nav.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !links.hidden) {
                event.preventDefault();
                setOpen(false, true);
            } else if (event.target === toggle && (event.key === 'ArrowDown' || event.key === 'ArrowUp')) {
                event.preventDefault();
                setOpen(true);
                var targets = links.querySelectorAll('a');
                targets[event.key === 'ArrowUp' ? targets.length - 1 : 0].focus();
            }
        });
        document.addEventListener('click', function (event) {
            if (!salesItem.contains(event.target)) setOpen(false);
        });
        salesItem.addEventListener('focusout', function () {
            window.setTimeout(function () {
                if (!salesItem.contains(document.activeElement)) setOpen(false);
            }, 0);
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})(document, window);

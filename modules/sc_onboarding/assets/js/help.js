/* admin/sc_onboarding/help — "Learn Smart Choice CRM": progress ring, flow strip, searchable tour cards grouped by area. */
(function ($, window, document) {
    'use strict';
    if (!$) return;

    $(function () {
        var $page = $('[data-sconb-help]');
        if (!$page.length) return;
        var T = window.SCTour;
        if (!T) {
            var cfgT = (window.SCOnb && window.SCOnb.t) || {};
            var msg = cfgT.help_load_error || 'Tours could not be loaded. Refresh the page to try again.';
            $page.find('[data-help-groups]').empty();
            $page.find('[data-help-error]').empty().append($('<div class="alert alert-warning" role="alert"></div>').text(msg));
            return;
        }
        var esc = T.esc, t = T.t, txt = T.txt, reduced = T.reducedMotion();
        var delay = function (ms) { return reduced ? '' : ' style="animation-delay:' + ms + 'ms"'; };
        var query = '';

        // Group names used by tour definitions -> translation keys (unknown groups are appended as-is).
        var GROUPS = [
            ['Basics', 'group_basics', 'fa-solid fa-compass'],
            ['Sell', 'group_sell', 'fa-solid fa-handshake'],
            ['Run jobs', 'group_run_jobs', 'fa-solid fa-helmet-safety'],
            ['Customers & leads', 'group_customers', 'fa-solid fa-users'],
            ['Support', 'group_support', 'fa-solid fa-life-ring'],
            ['Team & admin', 'group_team', 'fa-solid fa-user-gear']
        ];
        // Each station starts the tour for that stage; without access to it the station is shown muted, not clickable.
        var FLOW = [
            ['fa-solid fa-inbox', 'flow_lead', 'leads', 'flow_lead_d'],
            ['fa-solid fa-file-signature', 'flow_quote', 'estimates', 'flow_quote_d'],
            ['fa-solid fa-diagram-project', 'flow_project', 'projects', 'flow_project_d'],
            ['fa-solid fa-list-check', 'flow_tasks', 'tasks', 'flow_tasks_d'],
            ['fa-solid fa-file-invoice-dollar', 'flow_invoice', 'invoices', 'flow_invoice_d'],
            ['fa-solid fa-sack-dollar', 'flow_paid', 'payments', 'flow_paid_d']
        ];

        function tours() { return T.available(); }
        function byId(id) { return tours().filter(function (d) { return d.id === id; })[0] || null; }
        function iconHtml(icon) {
            icon = icon || 'fa-solid fa-route';
            return /^fa[a-z-]*\s|^fa-/.test(icon) ? '<i class="' + esc(icon) + '" aria-hidden="true"></i>' : '<span aria-hidden="true">' + esc(icon) + '</span>';
        }
        function groupKey(name) {
            var g = GROUPS.filter(function (x) { return x[0] === name; })[0];
            return g ? g : [name, null, 'fa-solid fa-layer-group'];
        }
        function matches(d) {
            if (!query) return true;
            var g = groupKey(d.group || 'Basics');
            var hay = [txt(d.title), txt(d.description), d.group, g[1] ? t(g[1]) : '']
                .concat((d.steps || []).map(function (s) { return txt(s.title); })).join(' ').toLowerCase();
            return query.split(/\s+/).every(function (w) { return hay.indexOf(w) !== -1; });
        }

        function ring(done, total) {
            var pct = total ? Math.round(done / total * 100) : 0, r = 34, c = 2 * Math.PI * r, off = c * (1 - pct / 100);
            return '<div class="sconb-bigring" role="img" aria-label="' + esc(t('help_ring_label', { done: done, total: total })) + '">' +
                '<svg viewBox="0 0 80 80" aria-hidden="true">' +
                '<circle cx="40" cy="40" r="' + r + '" class="sconb-bigring-bg"></circle>' +
                '<circle cx="40" cy="40" r="' + r + '" class="sconb-bigring-fg" data-help-ring stroke-dasharray="' + c.toFixed(1) +
                '" stroke-dashoffset="' + (reduced ? off.toFixed(1) : c.toFixed(1)) + '" data-offset="' + off.toFixed(1) + '"></circle></svg>' +
                '<span class="sconb-bigring-pct" aria-hidden="true">' + pct + '%</span></div>';
        }

        var checklistHidden = false;
        function renderProgress() {
            var all = tours(), done = all.filter(function (d) { return T.isDone(d.id); }).length;
            var msg = !all.length ? t('help_msg_none')
                : done === all.length ? t('help_msg_all')
                : done === 0 ? t('help_msg_start')
                : t('help_msg_progress', { n: all.length - done });
            $page.find('[data-help-progress]').html(ring(done, all.length) +
                '<div class="sconb-hero-count"><p class="sconb-hero-num">' + done + ' <span>' + esc(t('help_of_tours', { total: all.length })) + '</span></p>' +
                '<p class="sconb-hero-msg">' + esc(msg) + '</p></div>');
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    var $c = $page.find('[data-help-ring]');
                    $c.attr('stroke-dashoffset', $c.attr('data-offset'));
                });
            });
            var nextTour = all.filter(function (d) { return !T.isDone(d.id); })[0];
            var started = all.some(function (d) { return T.isDone(d.id); });
            $page.find('[data-help-actions]').html(
                (nextTour ? '<button type="button" class="sconb-hero-btn sconb-hero-btn-solid" data-start="' + esc(nextTour.id) + '">' +
                    esc(t(started ? 'help_continue' : 'help_start', { title: txt(nextTour.title) })) + ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>' : '') +
                (checklistHidden ? '<button type="button" class="sconb-hero-btn sconb-hero-btn-ghost" data-show-checklist><i class="fa-solid fa-list-check" aria-hidden="true"></i> ' + esc(t('help_show_checklist')) + '</button>' : '') +
                '<button type="button" class="sconb-hero-btn sconb-hero-btn-ghost" data-reset><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> ' + esc(t('help_reset')) + '</button>');
        }

        function renderFlow() {
            $page.find('[data-help-flow]').html(FLOW.map(function (f, i) {
                var d = byId(f[2]), done = d && T.isDone(d.id);
                var inner = '<span class="sconb-flow-icon' + (done ? ' is-done' : '') + '" aria-hidden="true"><i class="' + f[0] + '"></i></span>' +
                    '<span class="sconb-flow-text"><span class="sconb-flow-label">' + (i + 1) + '. ' + esc(t(f[1])) +
                    (done ? ' <i class="fa-solid fa-circle-check sconb-flow-check" aria-hidden="true"></i><span class="sr-only">(' + esc(t('help_done')) + ')</span>' : '') + '</span>' +
                    '<span class="sconb-flow-desc">' + esc(t(f[3])) + '</span></span>';
                return '<li class="sconb-flow-step sconb-fade-up"' + delay(120 + i * 90) + '>' + (d
                    ? '<button type="button" class="sconb-flow-card" data-start="' + esc(d.id) + '" aria-label="' + esc(t('help_start', { title: txt(d.title) })) + '">' + inner + '</button>'
                    : '<div class="sconb-flow-card is-static">' + inner + '</div>') +
                    (i < FLOW.length - 1 ? '<span class="sconb-flow-arrow" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>' : '') + '</li>';
            }).join(''));
        }

        function card(d, i) {
            var done = T.isDone(d.id), steps = T.steps(d.id).length;
            return '<li class="sconb-card sconb-fade-up' + (done ? ' is-done' : '') + '" data-tour-card="' + esc(d.id) + '"' + delay(160 + i * 60) + '>' +
                '<div class="sconb-card-top"><span class="sconb-card-icon">' + iconHtml(d.icon) + '</span>' +
                (done ? '<span class="sconb-badge sconb-badge-done"><i class="fa-solid fa-check" aria-hidden="true"></i> ' + esc(t('help_done')) + '</span>'
                    : '<span class="sconb-badge">' + esc(t('help_not_started')) + '</span>') + '</div>' +
                '<h3 class="sconb-card-title">' + esc(txt(d.title) || d.id) + '</h3>' +
                '<p class="sconb-card-desc">' + esc(txt(d.description)) + '</p>' +
                '<div class="sconb-card-foot"><span class="sconb-card-meta"><i class="fa-regular fa-clock" aria-hidden="true"></i> ' +
                esc(t('help_minutes', { n: d.minutes || 2 })) + ' · ' + esc(t('help_steps', { n: steps })) + '</span>' +
                '<button type="button" class="btn ' + (done ? 'btn-default' : 'btn-primary') + ' btn-sm sconb-card-btn" data-start="' + esc(d.id) + '" aria-label="' +
                esc((done ? t('help_replay') : t('help_start_btn')) + ': ' + (txt(d.title) || d.id)) + '">' +
                (done ? '<i class="fa-solid fa-rotate-right" aria-hidden="true"></i> ' + esc(t('help_replay')) : esc(t('help_start_btn')) + ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>') +
                '</button></div></li>';
        }

        function renderGroups() {
            var all = tours();
            var $g = $page.find('[data-help-groups]');
            if (!all.length) {
                $g.html('<div class="sconb-empty"><i class="fa-solid fa-route" aria-hidden="true"></i><h3>' + esc(t('help_no_tours')) + '</h3><p>' + esc(t('help_no_tours_hint')) + '</p></div>');
                return;
            }
            var names = GROUPS.map(function (g) { return g[0]; });
            all.forEach(function (d) { var g = d.group || 'Basics'; if (names.indexOf(g) === -1) names.push(g); });
            var n = 0, shown = 0;
            var html = names.map(function (g) {
                var inGroup = all.filter(function (d) { return (d.group || 'Basics') === g; });
                var list = inGroup.filter(matches);
                if (!list.length) return '';
                shown += list.length;
                var meta = groupKey(g), slug = 'sconb-g-' + g.toLowerCase().replace(/[^a-z0-9]+/g, '-');
                var done = inGroup.filter(function (d) { return T.isDone(d.id); }).length;
                return '<section class="sconb-group" aria-labelledby="' + slug + '">' +
                    '<div class="sconb-group-head"><div class="sconb-group-titles">' +
                    '<h2 id="' + slug + '" class="sconb-group-title"><i class="' + esc(meta[2]) + '" aria-hidden="true"></i> ' + esc(meta[1] ? t(meta[1]) : g) + '</h2>' +
                    (meta[1] ? '<p class="sconb-group-hint">' + esc(t(meta[1] + '_hint')) + '</p>' : '') + '</div>' +
                    '<span class="sconb-group-count">' + esc(t('help_group_done', { done: done, total: inGroup.length })) + '</span></div>' +
                    '<ul class="sconb-cards">' + list.map(function (d) { return card(d, n++); }).join('') + '</ul></section>';
            }).join('');
            $g.html(shown ? html : '<div class="sconb-empty"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><p>' + esc(t('help_search_empty')) + '</p></div>');
        }

        function render() {
            return T.state().then(function (s) { checklistHidden = !!s.checklist_hidden; renderProgress(); renderFlow(); renderGroups(); });
        }

        $page.on('click', '[data-start]', function () { T.start(String($(this).attr('data-start'))); });
        $page.on('click', '[data-show-checklist]', function () {
            T.save({ checklist_hidden: false }).then(function () {
                if (typeof window.alert_float === 'function') window.alert_float('success', esc(t('help_checklist_shown')));
                render().then(function () { $page.find('[data-reset]').trigger('focus'); });
            });
        });
        $page.on('click', '[data-reset]', function () {
            if (!window.confirm(t('help_reset_confirm'))) return;
            // reset:true is the only way to clear progress; normal saves are merged (unioned) on the server.
            T.save({ reset: true }).then(function () {
                if (typeof window.alert_float === 'function') window.alert_float('success', esc(t('help_reset_done')));
                render();
            });
        });
        var searchTimer = null;
        $page.on('input', '[data-help-search]', function () {
            var v = $(this).val();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () { query = String(v || '').trim().toLowerCase(); renderGroups(); }, 120);
        });
        $(document).on('sctour:saved', render);

        render();
    });
})(window.jQuery, window, document);

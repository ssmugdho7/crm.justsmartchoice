/*
 * First-run welcome (dashboard only) + floating "Getting started" checklist.
 * The welcome shows once per staff member while welcome_seen is false; both buttons, the X and Esc save
 * welcome_seen. A tap on the backdrop does nothing (easy to hit by accident on a phone).
 */
(function ($, window, document) {
    'use strict';
    if (!$ || !window.SCTour) return;

    var T = window.SCTour, CFG = window.SCOnb || {}, esc = T.esc, t = T.t, txt = T.txt;
    var reduced = T.reducedMotion();
    var COLLAPSE_KEY = 'sconb_checklist_collapsed:' + (CFG.staffId || 'anon');
    var delay = function (ms) { return reduced ? '' : ' style="animation-delay:' + ms + 'ms"'; };

    var FLOW = [
        ['fa-solid fa-inbox', 'flow_lead', 'flow_lead_d'],
        ['fa-solid fa-file-signature', 'flow_quote', 'flow_quote_d'],
        ['fa-solid fa-diagram-project', 'flow_project', 'flow_project_d'],
        ['fa-solid fa-list-check', 'flow_tasks', 'flow_tasks_d'],
        ['fa-solid fa-file-invoice-dollar', 'flow_invoice', 'flow_invoice_d'],
        ['fa-solid fa-sack-dollar', 'flow_paid', 'flow_paid_d']
    ];

    // Server-computed admin-relative path ('' = dashboard) is the most reliable signal; fall back to the URL.
    function onDashboard() { return typeof CFG.path === 'string' ? CFG.path === '' : T.path() === ''; }
    function currentPath() { return typeof CFG.path === 'string' ? CFG.path : T.path(); }
    // The Help page already lists every tour, so the floating checklist would only cover it.
    function onHelpPage() { return /^sc_onboarding(\/|$)/.test(currentPath()); }

    // ---------- welcome ----------
    function flowStrip() {
        return '<div class="sconb-wflow">' +
            '<div class="sconb-wflow-track" aria-hidden="true"><div class="sconb-wflow-line" data-sconb-line></div></div>' +
            '<ol class="sconb-wflow-list" aria-label="' + esc(t('help_flow_title')) + '">' + FLOW.map(function (f, i) {
                return '<li class="sconb-wflow-item sconb-pop-in"' + delay(100 + i * 50) + '>' +
                    '<span class="sconb-wflow-icon" aria-hidden="true"><i class="' + f[0] + '"></i></span>' +
                    '<span class="sconb-wflow-label">' + esc(t(f[1])) + '</span>' +
                    '<span class="sconb-wflow-desc">' + esc(t(f[2])) + '</span></li>';
            }).join('') + '</ol></div>';
    }

    function welcomeHtml() {
        var first = String(CFG.firstName || '').trim();
        var hi = first ? t('welcome_hi', { name: first }) : t('welcome_hi_anon');
        return '<div class="sconb-welcome" role="dialog" aria-modal="true" aria-labelledby="sconb-welcome-title" aria-describedby="sconb-welcome-intro">' +
            '<div class="sconb-welcome-backdrop"></div>' +
            '<div class="sconb-welcome-card" tabindex="-1">' +
            '<button type="button" class="sconb-welcome-close" data-sconb-explore aria-label="' + esc(t('welcome_explore')) + '"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>' +
            '<div class="sconb-welcome-badge sconb-fade-up" aria-hidden="true"' + delay(0) + '><span>👋</span></div>' +
            '<h2 id="sconb-welcome-title" class="sconb-welcome-title sconb-fade-up"' + delay(60) + '>' + esc(hi) + '</h2>' +
            '<p id="sconb-welcome-intro" class="sconb-welcome-intro sconb-fade-up"' + delay(110) + '>' + esc(t('welcome_intro')) + '</p>' +
            flowStrip() +
            '<p class="sconb-welcome-cta sconb-fade-up"' + delay(260) + '>' + esc(t('welcome_cta_text')) + '</p>' +
            '<div class="sconb-welcome-actions sconb-fade-up"' + delay(320) + '>' +
            '<button type="button" class="btn btn-default sconb-btn-lg" data-sconb-explore>' + esc(t('welcome_explore')) + '</button>' +
            '<button type="button" class="btn btn-primary sconb-btn-lg" data-sconb-tour>' + esc(t('welcome_tour')) + ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>' +
            '</div></div></div>';
    }

    function sparkle(fromEl) {
        if (reduced || !fromEl) return;
        var r = fromEl.getBoundingClientRect();
        var cx = r.left + r.width / 2, cy = r.top + r.height / 2;
        var colors = ['#059669', '#10b981', '#34d399', '#fbbf24', '#3598db'];
        var $s = $('<div class="sconb-sparkle" aria-hidden="true"></div>').css({ left: cx, top: cy });
        for (var i = 0; i < 18; i++) {
            var a = (Math.PI * 2 * i) / 18 + Math.random() * 0.3, d = 50 + Math.random() * 70;
            var dot = $('<i></i>').css({
                background: colors[i % colors.length], animationDelay: Math.round(Math.random() * 80) + 'ms'
            }).appendTo($s)[0];
            // jQuery 3.1's .css() drops custom properties, so set them on the style object directly.
            dot.style.setProperty('--dx', Math.round(Math.cos(a) * d) + 'px');
            dot.style.setProperty('--dy', Math.round(Math.sin(a) * d) + 'px');
        }
        $('body').append($s);
        setTimeout(function () { $s.remove(); }, 1000);
    }

    function showWelcome() {
        var $w = $(welcomeHtml()).appendTo('body');
        var prevFocus = document.activeElement, chosen = false;
        $('body').addClass('sconb-noscroll');
        requestAnimationFrame(function () {
            $w.addClass('is-open');
            // Draw the connecting line after the first stations pop in.
            requestAnimationFrame(function () { $w.find('[data-sconb-line]').addClass('is-drawn'); });
        });
        setTimeout(function () { $w.find('[data-sconb-tour]').trigger('focus'); }, reduced ? 0 : 60);

        function close(startTour, btn) {
            if (chosen) return;
            chosen = true;
            T.save({ welcome_seen: true });
            if (startTour) sparkle(btn);
            $(document).off('keydown.sconbwelcome');
            $w.removeClass('is-open').addClass('is-closing');
            $('body').removeClass('sconb-noscroll');
            setTimeout(function () {
                $w.remove();
                if (startTour) T.start('basics');
                else {
                    if (prevFocus && document.body.contains(prevFocus)) { try { prevFocus.focus(); } catch (e) { /* ignore */ } }
                    renderChecklist();
                }
            }, reduced ? 0 : 280);
        }
        $w.on('click', '[data-sconb-tour]', function () { close(true, this); });
        $w.on('click', '[data-sconb-explore]', function () { close(false); });
        $(document).on('keydown.sconbwelcome', function (e) {
            if (e.key === 'Escape' || e.key === 'Esc') { e.preventDefault(); close(false); return; }
            if (e.key === 'Tab') {
                var $f = $w.find('button:visible'), first = $f.first()[0], last = $f.last()[0];
                if (!$.contains($w[0], document.activeElement)) { e.preventDefault(); first.focus(); }
                else if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        });
    }

    // ---------- checklist ----------
    /**
     * One tour per area, in the order a new staff member needs them: Basics, Run jobs (projects, else tasks),
     * Sell (estimates first), Customers & leads (leads, else customers), Support. Only tours this person can open.
     */
    var PLAN = [
        ['Basics', ['basics']],
        ['Run jobs', ['projects', 'tasks']],
        ['Sell', ['estimates', 'proposals', 'invoices', 'payments']],
        ['Customers & leads', ['leads', 'customers']],
        ['Support', ['tickets']]
    ];
    function recommended() {
        var avail = T.available().filter(function (d) { return d.checklist !== false; }), picked = [];
        PLAN.forEach(function (p) {
            var inGroup = avail.filter(function (d) { return (d.group || 'Basics') === p[0]; });
            var pick = null;
            p[1].some(function (id) { pick = inGroup.filter(function (d) { return d.id === id; })[0] || null; return !!pick; });
            if (!pick) pick = inGroup[0] || null;
            if (pick) picked.push(pick);
        });
        return picked.slice(0, 5);
    }

    function ringSvg(done, total) {
        var r = 15, c = 2 * Math.PI * r, off = total ? c * (1 - done / total) : c;
        return '<svg class="sconb-ring" viewBox="0 0 36 36" aria-hidden="true">' +
            '<circle cx="18" cy="18" r="' + r + '" class="sconb-ring-bg"></circle>' +
            '<circle cx="18" cy="18" r="' + r + '" class="sconb-ring-fg" stroke-dasharray="' + c.toFixed(2) + '" stroke-dashoffset="' +
            (reduced ? off.toFixed(2) : c.toFixed(2)) + '" data-offset="' + off.toFixed(2) + '"></circle></svg>';
    }

    /**
     * Lift the checklist above other fixed bottom widgets (e.g. the team chat bar #pusherChat) on every screen size.
     * The checklist is ignored while probing, so it never "finds" itself.
     */
    var dockTimer = null;
    function dockChecklist() {
        var el = document.getElementById('sconb-checklist');
        if (!el) { clearInterval(dockTimer); dockTimer = null; return; }
        var vw = window.innerWidth, vh = window.innerHeight, lift = 0, phone = vw < 768;
        var prevPe = el.style.pointerEvents, prevVis = el.style.visibility;
        el.style.pointerEvents = 'none';
        el.style.visibility = 'hidden';
        var xs = phone ? [vw - 24, vw / 2, 24] : [vw - 30, vw - 160, vw - 290];
        var pts = [];
        xs.forEach(function (x) { [vh - 6, vh - 30].forEach(function (y) { pts.push([x, y]); }); });
        pts.forEach(function (pt) {
            var stack = document.elementsFromPoint ? document.elementsFromPoint(pt[0], pt[1]) : [];
            for (var i = 0; i < stack.length; i++) {
                var n = stack[i];
                if (n === el || el.contains(n) || n === document.body || n === document.documentElement) continue;
                for (var p = n; p && p !== document.body; p = p.parentElement) {
                    if (window.getComputedStyle(p).position === 'fixed') {
                        if ($(p).closest('.sconb-tour-root, .sconb-welcome, .sconb-toast, .sconb-confetti').length) break;
                        var r = p.getBoundingClientRect();
                        if (r.height < vh * (phone ? 0.5 : 0.75) && r.top > 0) lift = Math.max(lift, vh - r.top);
                        break;
                    }
                }
            }
        });
        el.style.pointerEvents = prevPe;
        el.style.visibility = prevVis;
        el.style.bottom = lift ? (lift + (phone ? 8 : 12)) + 'px' : '';
    }

    function renderChecklist() {
        $('#sconb-checklist').remove();
        if (T.isActive() || onHelpPage()) return;
        T.state().then(function (s) {
            if (T.isActive() || onHelpPage() || $('#sconb-checklist').length || s.checklist_hidden) return;
            var list = recommended();
            if (!list.length) return;
            var done = list.filter(function (d) { return T.isDone(d.id); }).length;
            if (done === list.length) return;
            // Phones start collapsed so the checklist never covers the page; a saved choice wins.
            var collapsed = window.innerWidth < 768, savedPref = null;
            try { savedPref = localStorage.getItem(COLLAPSE_KEY); } catch (e) { /* ignore */ }
            if (savedPref === '1' || savedPref === '0') collapsed = savedPref === '1';

            var items = list.map(function (d, i) {
                var isDone = T.isDone(d.id);
                return '<li class="sconb-check-item' + (isDone ? ' is-done' : '') + '"' + delay(80 + i * 60) + '>' +
                    '<button type="button" data-sconb-start="' + esc(d.id) + '">' +
                    '<span class="sconb-check-box" aria-hidden="true"><i class="fa-solid fa-check"></i></span>' +
                    '<span class="sconb-check-text">' + esc(txt(d.title) || d.id) +
                    '<span class="sr-only"> (' + esc(isDone ? t('help_done') : t('help_not_started')) + ')</span></span>' +
                    '<span class="sconb-check-min">' + esc(t('help_minutes', { n: d.minutes || 2 })) + '</span></button></li>';
            }).join('');

            var $c = $('<aside id="sconb-checklist" class="sconb-checklist' + (collapsed ? ' is-collapsed' : '') + '" aria-label="' + esc(t('checklist_title')) + '">' +
                '<div class="sconb-check-head">' +
                '<button type="button" class="sconb-check-toggle" data-sconb-toggle aria-expanded="' + (!collapsed) + '" aria-controls="sconb-check-body">' +
                ringSvg(done, list.length) +
                '<span class="sconb-check-titles"><span class="sconb-check-title">' + esc(t('checklist_title')) + '</span>' +
                '<span class="sconb-check-sub">' + esc(t('checklist_done_of', { done: done, total: list.length })) + '</span></span>' +
                '<i class="fa-solid fa-chevron-down sconb-check-chev" aria-hidden="true"></i></button>' +
                '<button type="button" class="sconb-check-dismiss" data-sconb-dismiss aria-label="' + esc(t('checklist_dismiss')) + '" title="' + esc(t('checklist_dismiss')) + '"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>' +
                '</div>' +
                '<div class="sconb-check-body" id="sconb-check-body"><ul class="sconb-check-list">' + items + '</ul>' +
                '<a class="sconb-check-all" href="' + esc(CFG.helpUrl || '#') + '">' + esc(t('checklist_all')) + ' <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>' +
                '</aside>');
            $('body').append($c);
            dockChecklist();
            if (!dockTimer) dockTimer = setInterval(dockChecklist, 1500);
            $(window).off('resize.sconbdock').on('resize.sconbdock', dockChecklist);
            requestAnimationFrame(function () {
                $c.addClass('is-in');
                requestAnimationFrame(function () { var $r = $c.find('.sconb-ring-fg'); $r.attr('stroke-dashoffset', $r.attr('data-offset')); });
            });
            $c.on('click', '[data-sconb-toggle]', function () {
                var now = !$c.hasClass('is-collapsed');
                $c.toggleClass('is-collapsed', now);
                $(this).attr('aria-expanded', String(!now));
                try { localStorage.setItem(COLLAPSE_KEY, now ? '1' : '0'); } catch (e) { /* ignore */ }
            });
            $c.on('click', '[data-sconb-dismiss]', function () {
                // Delta only; Help & Tours has a "Show the Getting started checklist" button that turns it back on.
                T.save({ checklist_hidden: true });
                $c.addClass('is-out');
                setTimeout(function () { $c.remove(); }, reduced ? 0 : 260);
            });
        });
    }

    // One render per tour end: completed tours re-render once progress is saved, skipped tours after the UI fades.
    var renderTimer = null;
    function scheduleChecklist(ms) {
        clearTimeout(renderTimer);
        renderTimer = setTimeout(renderChecklist, reduced ? 0 : ms);
    }
    $(document).on('sctour:start', function () { clearTimeout(renderTimer); $('#sconb-checklist').remove(); });
    $(document).on('sctour:end', function (e, id, completed) { if (!completed) scheduleChecklist(400); });
    $(document).on('sctour:saved', function () { scheduleChecklist(400); });

    $(function () {
        T.state().then(function (s) {
            // A tour resuming after navigation takes priority over welcome/checklist.
            setTimeout(function () {
                if (T.isActive()) return;
                if (!s.welcome_seen && onDashboard() && T.get('basics') && T.available().some(function (d) { return d.id === 'basics'; })) {
                    if ($('.modal.in').length) { renderChecklist(); return; }
                    showWelcome();
                    return;
                }
                renderChecklist();
            }, 450);
        });
    });

    window.SCOnboarding = { showWelcome: showWelcome, renderChecklist: renderChecklist };
})(window.jQuery, window, document);

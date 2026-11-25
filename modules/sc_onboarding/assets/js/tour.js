/*
 * Smart Choice CRM product tour engine (jQuery). Loaded on every admin page by the sc_onboarding module,
 * after window.SCOnb (config) and before the tour content files in assets/js/tours/*.js.
 *
 *   SCTour.register(id, {
 *     title, description, icon ('fa-solid fa-diagram-project' or an emoji), minutes, order,
 *     group: 'Basics' | 'Sell' | 'Run jobs' | 'Customers & leads' | 'Support' | 'Team & admin',
 *     perm: 'invoices' | ['estimates', 'proposals'] (any of; keys from SCOnb.perms, optional),
 *     steps: [{
 *       page: 'projects' | '' (dashboard) | function () { return url | Promise<url> },  // admin-relative; navigate here first
 *       match: 'projects' | /^projects\/view\/\d+/,  // tested against the admin-relative path (default: path === page)
 *       target: '#menu .menu-item-projects',          // element to spotlight; omit for a centered step
 *       title, body,                                   // trusted static copy (HTML allowed; escape user data with SCTour.esc)
 *                                                      // every text field may be a string or { en: '...', es: '...' }
 *       placement: 'auto'|'top'|'bottom'|'left'|'right',
 *       advanceOn: { selector, event, delay },         // "try it" step: advance when the user does this
 *       tryText, interactive: true, optional: true, timeout: 1500 (optional) / 3000 (max), padding: 8,
 *       perm: 'invoices',                              // step is left out when the staff member lacks it
 *       before: function () {}                         // runs before the step shows (may return a Promise)
 *     }]
 *   });
 *   SCTour.start(id, { step }), SCTour.end(), SCTour.available(), SCTour.isDone(id),
 *   SCTour.state() -> Promise<{completed, dismissed, welcome_seen, checklist_hidden}>, SCTour.save(patch),
 *   SCTour.beacon(selector, { tour, label, text }), SCTour.txt(stringOrLangObject).
 * Sidebar targets (#side-menu ...) open the mobile sidebar and expand their collapsed parent menu automatically.
 * Events on document: 'sctour:start' (id), 'sctour:step' (id, index), 'sctour:complete' (id),
 * 'sctour:end' (id, completed), 'sctour:saved' (id).
 */
(function ($, window, document) {
    'use strict';
    if (!$ || window.SCTour) return;

    var CFG = window.SCOnb || {};
    var STR = CFG.t || {};
    var tours = {};
    var ACTIVE_KEY = 'sconb_tour_active';
    var LOCAL_KEY = 'sconb_onboarding:' + (CFG.staffId || 'anon');
    // Deltas not yet confirmed by the server (offline, timeouts). Re-sent on the next page load.
    var PENDING_KEY = 'sconb_onboarding_pending:' + (CFG.staffId || 'anon');
    var ID_RE = /^[a-z0-9_-]{1,60}$/;
    var reduced = CFG.reducedMotion === true ||
        !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    var state = null;
    var statePromise = null;
    var run = null;
    var $ui = null;
    var rafId = null;
    var tickId = null;
    var lastDir = 1;
    var lastFocus = null;
    var sidebarOpened = false;
    var expandedMenus = [];
    var pageLocks = [];
    var ringTimer = null;
    var LANG = CFG.lang === 'spanish' ? 'es' : 'en';

    var DEFAULT_STR = {
        step_of: 'Step {n} of {total}', next: 'Next', back: 'Back', finish: 'Done', skip_tour: 'Skip tour',
        skip_step: 'Skip this step', close_tour: 'Close tour', try_it: 'Try it: use the highlighted control to continue.',
        tour_complete: '{title} complete — nice work!', quick_tour: 'Quick tour', loading: 'Loading…'
    };

    function noop() {}
    function t(key, vars) {
        var s = STR[key] && STR[key] !== 'sc_onb_js_' + key ? STR[key] : (DEFAULT_STR[key] || key);
        if (vars) Object.keys(vars).forEach(function (k) { s = s.split('{' + k + '}').join(vars[k]); });
        return s;
    }
    /** Tour copy may be a plain string or { en, es }; picks the staff member's language, then English. */
    function txt(v) {
        if (v === null || v === undefined) return '';
        if (typeof v !== 'object') return String(v);
        if (v[LANG]) return String(v[LANG]);
        if (v.en) return String(v.en);
        for (var k in v) { if (Object.prototype.hasOwnProperty.call(v, k) && v[k]) return String(v[k]); }
        return '';
    }
    function esc(s) {
        return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function idList(v) {
        var out = [];
        ($.isArray(v) ? v : []).forEach(function (id) { if (typeof id === 'string' && ID_RE.test(id) && out.indexOf(id) === -1) out.push(id); });
        return out;
    }
    /** Same shape as the server (sc_onb_normalize). A legacy 'checklist' entry in dismissed means checklist_hidden. */
    function normalize(s) {
        s = s || {};
        var dismissed = idList(s.dismissed), legacy = dismissed.indexOf('checklist');
        if (legacy !== -1) dismissed.splice(legacy, 1);
        return {
            completed: idList(s.completed).slice(-50),
            dismissed: dismissed.slice(-50),
            welcome_seen: !!s.welcome_seen,
            checklist_hidden: !!s.checklist_hidden || legacy !== -1
        };
    }
    /** Local mirror of the server merge (sc_onb_merge_state): union lists, reset clears, welcome only turns on. */
    function applyDelta(base, patch) {
        var out = patch.reset ? normalize({}) : normalize(base);
        ['completed', 'dismissed'].forEach(function (k) {
            ($.isArray(patch[k]) ? patch[k] : []).forEach(function (id) { if (out[k].indexOf(id) === -1) out[k].push(id); });
        });
        if (patch.welcome_seen) out.welcome_seen = true;
        if (typeof patch.checklist_hidden === 'boolean') out.checklist_hidden = patch.checklist_hidden;
        return normalize(out);
    }
    /** Keeps only the allowed delta keys (the server validates the same way). */
    function cleanDelta(patch) {
        patch = patch && typeof patch === 'object' ? patch : {};
        var delta = {};
        if (patch.reset === true) delta.reset = true;
        ['completed', 'dismissed'].forEach(function (k) { var l = idList(patch[k]).slice(0, 50); if (l.length) delta[k] = l; });
        if (patch.welcome_seen === true) delta.welcome_seen = true;
        if (typeof patch.checklist_hidden === 'boolean') delta.checklist_hidden = patch.checklist_hidden;
        return delta;
    }
    /** Combines two deltas in order: a later reset drops everything before it; lists union; booleans: latest wins. */
    function mergeDelta(a, b) {
        var out = b.reset ? { reset: true } : $.extend(true, {}, a);
        ['completed', 'dismissed'].forEach(function (k) {
            if (!b[k]) return;
            var l = out[k] || [];
            b[k].forEach(function (id) { if (l.indexOf(id) === -1) l.push(id); });
            out[k] = l.slice(-50);
        });
        if (b.welcome_seen) out.welcome_seen = true;
        if (typeof b.checklist_hidden === 'boolean') out.checklist_hidden = b.checklist_hidden;
        return out;
    }
    function readLocal() { try { return JSON.parse(localStorage.getItem(LOCAL_KEY) || 'null'); } catch (e) { return null; } }
    function writeLocal() { try { localStorage.setItem(LOCAL_KEY, JSON.stringify(state)); } catch (e) { /* storage full or blocked */ } }
    function readPending() { try { return cleanDelta(JSON.parse(localStorage.getItem(PENDING_KEY) || 'null')); } catch (e) { return {}; } }
    function writePending(d) {
        try { if ($.isEmptyObject(d)) localStorage.removeItem(PENDING_KEY); else localStorage.setItem(PENDING_KEY, JSON.stringify(d)); } catch (e) { /* ignore */ }
    }
    function ss(method, value) {
        try {
            if (method === 'get') return sessionStorage.getItem(ACTIVE_KEY);
            if (method === 'set') sessionStorage.setItem(ACTIVE_KEY, value);
            else sessionStorage.removeItem(ACTIVE_KEY);
        } catch (e) { /* private mode */ }
        return null;
    }

    // ---------- persistence (server first, localStorage fallback) ----------
    // Server state plus anything saved locally that the server has not confirmed yet; then re-send it.
    function adopt(server) {
        state = applyDelta(normalize(server), readPending());
        writeLocal();
        flush();
        return state;
    }
    function loadState() {
        if (state) return Promise.resolve(state);
        if (statePromise) return statePromise;
        if (CFG.state) {
            statePromise = Promise.resolve(adopt(CFG.state));
            return statePromise;
        }
        if (!CFG.stateUrl) {
            state = applyDelta(normalize(readLocal()), readPending());
            statePromise = Promise.resolve(state);
            return statePromise;
        }
        statePromise = new Promise(function (resolve) {
            $.ajax({ url: CFG.stateUrl, method: 'GET', dataType: 'json', cache: false })
                .done(function (s) { resolve(adopt(s)); })
                .fail(function () { state = applyDelta(normalize(readLocal()), readPending()); resolve(state); });
        });
        return statePromise;
    }
    var flushing = null;
    /** Sends the whole pending delta (server merges are idempotent); clears it once the server confirms. */
    function flush() {
        if (flushing) return flushing.then(function () { return $.isEmptyObject(readPending()) ? state : flush(); });
        var pending = readPending();
        if (!CFG.saveUrl || $.isEmptyObject(pending)) return Promise.resolve(state);
        var sent = JSON.stringify(pending);
        flushing = new Promise(function (resolve) {
            var data = { state: sent };
            var csrfName = (CFG.csrf && CFG.csrf.name) || (window.csrfData && window.csrfData.token_name);
            var csrfHash = (CFG.csrf && CFG.csrf.hash) || (window.csrfData && window.csrfData.hash);
            if (csrfName) data[csrfName] = csrfHash;
            $.ajax({ url: CFG.saveUrl, method: 'POST', data: data, dataType: 'json' })
                .done(function (s) {
                    if (!s || !$.isArray(s.completed)) return;
                    var now = readPending(), same = JSON.stringify(now) === sent;
                    if (same) writePending({});
                    state = applyDelta(normalize(s), same ? {} : now);
                    writeLocal();
                })
                .fail(function (xhr) {
                    // 422 = rejected by validation: retrying would never succeed. Anything else is kept for later.
                    if (xhr && xhr.status === 422 && JSON.stringify(readPending()) === sent) writePending({});
                })
                .always(function () { flushing = null; resolve(state); });
        });
        return flushing;
    }
    /**
     * Sends only a delta: { completed: [ids just finished], dismissed: [ids], welcome_seen: true } or { reset: true }.
     * The server unions it with what is stored, so two open tabs never overwrite each other.
     */
    function save(patch) {
        var delta = cleanDelta(patch);
        return loadState().then(function () {
            state = applyDelta(state, delta);
            writeLocal();
            if (!CFG.saveUrl || $.isEmptyObject(delta)) return state;
            writePending(mergeDelta(readPending(), delta));
            return flush();
        });
    }
    function markDone(id) {
        return loadState().then(function () {
            if (state.completed.indexOf(id) === -1) return save({ completed: [id] });
            return state;
        });
    }

    // ---------- registry ----------
    function can(perm) {
        if (!perm) return true;
        var perms = CFG.perms || {};
        if (CFG.isAdmin) return true;
        var list = $.isArray(perm) ? perm : [perm];
        return list.some(function (p) { return perms[p] === true; });
    }
    function sorted() {
        var order = CFG.tourOrder || [];
        return Object.keys(tours).map(function (id) { return tours[id]; }).sort(function (a, b) {
            var ao = a.order !== undefined ? a.order : (order.indexOf(a.id) === -1 ? 999 : order.indexOf(a.id) * 10);
            var bo = b.order !== undefined ? b.order : (order.indexOf(b.id) === -1 ? 999 : order.indexOf(b.id) * 10);
            return ao - bo || a.seq - b.seq;
        });
    }
    function available() {
        return sorted().filter(function (def) { return can(def.perm) && stepsFor(def).length > 0; });
    }
    function stepsFor(def) {
        return (def.steps || []).filter(function (s) { return can(s.perm); });
    }

    // ---------- paths ----------
    var adminBase = (function () {
        try { return new URL(CFG.adminUrl || '/admin/', window.location.href).pathname.replace(/\/+$/, ''); } catch (e) { return '/admin'; }
    })();
    function canon(p) {
        p = String(p || '').split('?')[0].split('#')[0].replace(/^\/+|\/+$/g, '');
        return p === 'dashboard' ? '' : p;
    }
    function relPath(pathname) {
        var p = String(pathname || '').replace(/\/+$/, '');
        p = p.replace(/\/index\.php(?=\/|$)/, '');
        if (adminBase && p.indexOf(adminBase) === 0) p = p.slice(adminBase.length);
        return canon(p);
    }
    function pathNow() { return relPath(window.location.pathname); }
    /**
     * Resolves a step destination to a same-origin admin URL, or null when it is not allowed:
     * admin-relative paths ('projects/view/5'), absolute paths under the admin path ('/admin/projects'),
     * or http(s) URLs on this origin under the admin path. Rejects '//host', other schemes and other hosts.
     */
    function urlFor(page) {
        if (typeof page !== 'string') return null;
        var raw = page.trim(), u;
        if (/[\u0000-\u001f\\]/.test(raw)) return null;
        var base = String(CFG.adminUrl || '/admin/').replace(/\/?$/, '/');
        try {
            if (raw.indexOf('//') === 0) return null;
            if (/^[a-z][a-z0-9+.-]*:/i.test(raw)) {
                if (!/^https?:/i.test(raw)) return null;
                u = new URL(raw);
            } else if (raw.charAt(0) === '/') {
                u = new URL(raw, window.location.origin);
            } else {
                u = new URL(base + raw, window.location.href);
            }
        } catch (e) { return null; }
        if (u.origin !== window.location.origin) return null;
        var p = u.pathname.replace(/\/index\.php(?=\/|$)/, '');
        if (adminBase && p !== adminBase && p.indexOf(adminBase + '/') !== 0) return null;
        return u.href;
    }
    function pathOfUrl(url) {
        try { return relPath(new URL(url, window.location.href).pathname); } catch (e) { return canon(url); }
    }
    // page: '' means the dashboard, so only undefined/null count as "no page".
    function hasPage(step) { return step.page !== undefined && step.page !== null; }
    function hasMatch(step) { return step.match !== undefined && step.match !== null; }
    function onPage(step) {
        if (!hasPage(step) && !hasMatch(step)) return true;
        var m = hasMatch(step) ? step.match : (typeof step.page === 'string' ? step.page : null);
        if (m === null) return false;
        return m instanceof RegExp ? m.test(pathNow()) : pathNow() === canon(m);
    }
    function stepTimeout(step) {
        var max = 3000;
        if (step.timeout) return Math.min(step.timeout, max);
        return step.optional ? 1500 : max;
    }
    function waitFor(selector, timeout) {
        return new Promise(function (resolve) {
            var started = Date.now();
            (function poll() {
                var $el = $(selector).filter(function () {
                    var r = this.getBoundingClientRect();
                    return $(this).is(':visible') && r.width > 0 && r.height > 0;
                }).first();
                if ($el.length) return resolve($el);
                var left = (timeout || 3000) - (Date.now() - started);
                if (left <= 0) return resolve(null);
                setTimeout(poll, Math.min(100, left));
            })();
        });
    }
    function persistActive(expect, navigated) {
        if (!run) return;
        ss('set', JSON.stringify({ id: run.id, index: run.index, staff: CFG.staffId || 0, expect: expect === undefined ? pathNow() : expect, nav: !!navigated, skip: skippedList() }));
    }
    function clearActive() { ss('remove'); }
    function headerOffset() {
        var h = document.getElementById('header');
        if (!h) return 0;
        var pos = window.getComputedStyle(h).position;
        if (pos !== 'fixed' && pos !== 'sticky') return 0;
        var r = h.getBoundingClientRect();
        return r.bottom > 0 && r.top <= 0 ? r.bottom : 0;
    }

    // ---------- sidebar targets ----------
    function isMobileNav() { return (window.innerWidth || document.documentElement.clientWidth) < 768; }
    function inSidebar(selector) {
        if (typeof selector !== 'string') return null;
        var $el;
        try { $el = $(selector).first(); } catch (e) { return null; }
        return $el.length && $el.closest('#side-menu, #menu').length ? $el : null;
    }
    // Perfex hides the sidebar with body classes (assets/js/main.js .hide-menu handler): phones need
    // 'show-sidebar'; desktop staff who collapsed it have 'hide-sidebar'. We undo exactly what we changed.
    function sidebarHidden() {
        var $b = $('body');
        if ($b.hasClass('show-sidebar')) return false;
        return isMobileNav() || $b.hasClass('page-small') || $b.hasClass('hide-sidebar');
    }
    function openSidebar() {
        if (!sidebarHidden()) return false;
        var $b = $('body');
        sidebarOpened = { hadHide: $b.hasClass('hide-sidebar') };
        $b.addClass('show-sidebar').removeClass('hide-sidebar');
        return true;
    }
    function closeSidebar() {
        if (!sidebarOpened) return;
        var prev = sidebarOpened;
        sidebarOpened = false;
        $('body').removeClass('show-sidebar').toggleClass('hide-sidebar', !!prev.hadHide);
        schedulePosition();
    }
    /** Opens a hidden sidebar and any collapsed parent menu so a sidebar target can be spotlighted. */
    function prepareTarget(step) {
        var $el = inSidebar(step.target);
        if (!$el) { collapseMenus(); closeSidebar(); return Promise.resolve(); }
        collapseMenus($el);
        var waits = [];
        if (openSidebar()) waits.push(new Promise(function (r) { setTimeout(r, reduced ? 0 : 320); }));
        $el.parents('ul.nav-second-level').each(function () {
            var $ul = $(this);
            if (!$ul.is(':visible') || !$ul.hasClass('in')) waits.push(expandLi($ul.parent('li')));
        });
        return Promise.all(waits);
    }

    // ---------- UI ----------
    function buildUi() {
        if ($ui) return $ui;
        $ui = $(
            '<div class="sconb-tour-root">' +
            // Four 1px dim rectangles around the target, sized and moved with transform only (see setRect).
            '<div class="sconb-tour-mask sconb-tour-mask-top"></div><div class="sconb-tour-mask sconb-tour-mask-left"></div>' +
            '<div class="sconb-tour-mask sconb-tour-mask-right"></div><div class="sconb-tour-mask sconb-tour-mask-bottom"></div>' +
            '<div class="sconb-tour-shield"></div><div class="sconb-tour-ring is-off"></div>' +
            '<div class="sconb-tour-pop" role="dialog" aria-modal="true" aria-labelledby="sconb-tour-title" aria-describedby="sconb-tour-body" tabindex="-1">' +
            '<div class="sconb-tour-arrow"></div>' +
            '<div class="sconb-tour-pop-inner">' +
            '<div class="sconb-tour-wait"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i><span class="sr-only">' + esc(t('loading')) + '</span></div>' +
            '<div class="sconb-tour-meta"><span class="sconb-tour-kicker"><span class="sconb-tour-kicker-title"></span>' +
            '<span class="sconb-tour-count" aria-live="polite" aria-atomic="true"></span></span>' +
            '<button type="button" class="sconb-tour-close" data-sconb-skip aria-label="' + esc(t('close_tour')) + '"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button></div>' +
            '<h2 id="sconb-tour-title" class="sconb-tour-title" tabindex="-1"></h2>' +
            '<div id="sconb-tour-body" class="sconb-tour-body"></div>' +
            '<div class="sconb-tour-try"></div>' +
            '<ol class="sconb-tour-dots" aria-hidden="true"></ol>' +
            '<div class="sconb-tour-actions"><button type="button" class="sconb-tour-btn sconb-tour-btn-ghost" data-sconb-skip>' + esc(t('skip_tour')) + '</button>' +
            '<span class="sconb-tour-spacer"></span>' +
            '<button type="button" class="sconb-tour-btn sconb-tour-btn-secondary" data-sconb-back>' + esc(t('back')) + '</button>' +
            '<button type="button" class="sconb-tour-btn sconb-tour-btn-primary" data-sconb-next>' + esc(t('next')) + '</button></div>' +
            '</div></div></div>'
        );
        if (reduced) $ui.addClass('sconb-reduced');
        $('body').append($ui);
        lockPage(false);
        $ui.on('click', '[data-sconb-next]', function () { next(); });
        $ui.on('click', '[data-sconb-back]', function () { back(); });
        $ui.on('click', '[data-sconb-skip]', function () { end(false); });
        $ui.on('click', '[data-sconb-dot]', function () {
            var i = parseInt($(this).attr('data-sconb-dot'), 10);
            if (run && !run.busy && !isNaN(i) && i !== run.index) { lastDir = i > run.index ? 1 : -1; show(i); }
        });
        $ui.on('click', '.sconb-tour-mask, .sconb-tour-shield', function () { nudge(); });
        $(document).on('keydown.sconbtour', function (e) {
            if (!run || !$ui) return;
            var typing = $(e.target).is('input,textarea,select,[contenteditable="true"]');
            var cur = run.steps[run.index] || {};
            if (e.key === 'Escape' || e.key === 'Esc') {
                // A Bootstrap modal opened during a "try it" step: Esc closes that modal, not the tour.
                if ((cur.interactive || cur.advanceOn) && $('body').hasClass('modal-open') && $('.modal.in, .modal.show').length) return;
                e.preventDefault(); e.stopPropagation(); end(false);
            }
            else if (e.key === 'ArrowRight' && !typing) { e.preventDefault(); next(); }
            else if (e.key === 'ArrowLeft' && !typing) { e.preventDefault(); back(); }
            else if (e.key === 'Tab') {
                // aria-modal dialog: keep keyboard focus inside the popover while a tour runs.
                var pop = $ui.find('.sconb-tour-pop')[0];
                var $f = $(pop).find('button, a[href], input, select, textarea, [tabindex]').not('[tabindex="-1"]').filter(':visible');
                e.preventDefault();
                if (!$f.length) { pop.focus(); return; }
                var list = $f.get(), at = list.indexOf(document.activeElement);
                var to = at === -1 ? (e.shiftKey ? list.length - 1 : 0) : (at + (e.shiftKey ? -1 : 1) + list.length) % list.length;
                list[to].focus();
            }
        });
        $(window).on('resize.sconbtour orientationchange.sconbtour', schedulePosition);
        // Capture phase catches scrolling inside nested containers (tables, modals, sidebar).
        document.addEventListener('scroll', schedulePosition, true);
        // Focus that escapes (e.g. a click on the page behind) is pulled back into the dialog.
        $(document).on('focusin.sconbtour', function (e) {
            if (!run || !$ui) return;
            var st = run.steps[run.index] || {};
            if (!$(e.target).closest('.sconb-tour-pop').length && !st.advanceOn && !st.interactive && !$(e.target).closest('.modal').length) focusTitle();
        });
        tickId = setInterval(schedulePosition, 500); // late layout shifts (DataTables, images)
        requestAnimationFrame(function () { if ($ui) $ui.addClass('sconb-tour-visible'); });
        return $ui;
    }
    // While a tour runs, the page behind is inert (not focusable/clickable, hidden from screen readers).
    // "Try it" steps need the spotlighted control, so there the page is only aria-hidden.
    var LOCK_SEL = '#wrapper, #header, #menu, #setup-menu-wrapper';
    function lockPage(interactive) {
        $(LOCK_SEL).each(function () {
            if (!this.parentNode || $(this).closest('.sconb-tour-root').length) return;
            var el = this, lock = pageLocks.filter(function (l) { return l.el === el; })[0];
            if (!lock) {
                lock = { el: el, inert: el.hasAttribute('inert'), aria: el.getAttribute('aria-hidden') };
                pageLocks.push(lock);
            }
            el.setAttribute('aria-hidden', 'true');
            if (!lock.inert) {
                if (interactive) { el.removeAttribute('inert'); el.inert = false; }
                else { el.setAttribute('inert', ''); el.inert = true; }
            }
        });
    }
    function unlockPage() {
        pageLocks.forEach(function (l) {
            if (!l.inert) { l.el.removeAttribute('inert'); l.el.inert = false; }
            if (l.aria === null) l.el.removeAttribute('aria-hidden'); else l.el.setAttribute('aria-hidden', l.aria);
        });
        pageLocks = [];
    }

    function destroyUi() {
        if (!$ui) return;
        unlockPage();
        if (ringTimer) { clearTimeout(ringTimer); ringTimer = null; }
        $(document).off('keydown.sconbtour focusin.sconbtour');
        $(window).off('resize.sconbtour orientationchange.sconbtour');
        document.removeEventListener('scroll', schedulePosition, true);
        if (tickId) { clearInterval(tickId); tickId = null; }
        var $old = $ui;
        $ui = null;
        $old.removeClass('sconb-tour-visible');
        if (reduced) $old.remove(); else setTimeout(function () { $old.remove(); }, 260);
    }
    function nudge() {
        if (!$ui || reduced) return;
        var $p = $ui.find('.sconb-tour-pop').removeClass('sconb-tour-nudge');
        void $p[0].offsetWidth;
        $p.addClass('sconb-tour-nudge');
        setTimeout(function () { if ($ui) $ui.find('.sconb-tour-pop').removeClass('sconb-tour-nudge'); }, 420);
    }

    function schedulePosition() {
        if (rafId) cancelAnimationFrame(rafId);
        rafId = requestAnimationFrame(position);
    }

    /** A mask piece is a 1x1px box; translate + scale paint it as any rectangle without layout work. */
    var ringState = { index: -1, el: null, at: 0 };
    function setRect(side, x, y, w, h) {
        $ui.find('.sconb-tour-mask-' + side)[0].style.transform =
            'translate3d(' + Math.round(x) + 'px,' + Math.round(y) + 'px,0) scale(' + Math.max(0, Math.round(w)) + ',' + Math.max(0, Math.round(h)) + ')';
    }

    function position() {
        rafId = null;
        if (!run || !$ui) return;
        var step = run.steps[run.index];
        if (!step) return;
        var vw = document.documentElement.clientWidth || window.innerWidth, vh = window.innerHeight;
        var pad = step.padding === undefined ? 8 : step.padding;
        var $pop = $ui.find('.sconb-tour-pop');
        var mobile = vw < 640;
        $pop.find('.sconb-tour-pop-inner').css({ maxHeight: Math.max(160, Math.round(mobile ? vh * 0.45 : vh - 24)), overflowY: 'auto' });
        var el = run.$target && run.$target.length ? run.$target[0] : null;
        var r = el && document.body.contains(el) && $(el).is(':visible') ? el.getBoundingClientRect() : null;
        if (r && (r.width === 0 && r.height === 0)) r = null;
        if (r && mobile && $(el).closest('#side-menu, #menu').length) {
            // Phone bottom sheet: point at the part of a tall sidebar target that stays visible above the sheet.
            var sheetTop = vh - Math.min(vh * 0.45 + 24, $pop.outerHeight() || vh * 0.45) - 8;
            if (r.bottom > sheetTop - 12 && sheetTop - 12 - r.top >= 40) {
                r = { top: r.top, left: r.left, right: r.right, width: r.width, bottom: sheetTop - 12, height: sheetTop - 12 - r.top };
            }
        }

        // Step change: the mask hole slides (transform transition) while the ring fades out, then the ring
        // fades back in at its final size once the hole has arrived, so the two never disagree mid-move.
        // Same-step updates (scroll, resize, late layout) track the target instantly.
        var $ring = $ui.find('.sconb-tour-ring'), now = Date.now();
        if (ringState.index !== run.index || ringState.el !== el) {
            ringState = { index: run.index, el: el, at: now };
            $ui.removeClass('sconb-tour-track');
            if (ringTimer) { clearTimeout(ringTimer); ringTimer = null; }
        }
        var settling = !reduced && now - ringState.at < 380;
        if (settling) {
            $ring.addClass('is-fading');
            if (!ringTimer) ringTimer = setTimeout(function () { ringTimer = null; schedulePosition(); }, 390 - (now - ringState.at));
        } else {
            // Size and place the ring with no transform transition, then fade it in (see the .sconb-tour-track CSS).
            $ui.addClass('sconb-tour-track');
        }
        if (r) {
            // Keep a few px of ring visible when the target touches a viewport edge (header icons, sidebar).
            var edge = 6;
            var top = Math.max(r.top - pad, Math.min(edge, r.top)), left = Math.max(r.left - pad, Math.min(edge, r.left));
            var right = Math.min(r.right + pad, Math.max(vw - edge, r.right)), bottom = Math.min(r.bottom + pad, Math.max(vh - edge, r.bottom));
            if (bottom < top) bottom = top;
            if (right < left) right = left;
            var ct = Math.max(0, top), cl = Math.max(0, left), cr = Math.min(vw, right), cb = Math.min(vh, bottom);
            setRect('top', 0, 0, vw, ct);
            setRect('bottom', 0, cb, vw, vh - cb);
            setRect('left', 0, ct, cl, cb - ct);
            setRect('right', cr, ct, vw - cr, cb - ct);
            // Ring moves with transform; its size is only changed while it is hidden or tracking the same target.
            if (!settling) $ring.css({ width: right - left, height: bottom - top, transform: 'translate3d(' + left + 'px,' + top + 'px,0)' });
            $ring.removeClass('is-off');
            $ui.find('.sconb-tour-shield').css({ top: ct, left: cl, width: cr - cl, height: Math.max(0, cb - ct) }).toggle(!(step.interactive || step.advanceOn));
        } else {
            setRect('top', 0, 0, vw, vh);
            setRect('bottom', 0, vh, vw, 0);
            setRect('left', 0, vh / 2, 0, 0);
            setRect('right', vw, vh / 2, 0, 0);
            $ring.addClass('is-off');
            $ui.find('.sconb-tour-shield').hide();
        }
        if (!settling) $ring.removeClass('is-fading');

        var mode = mobile ? 'sheet' : (!r ? 'center' : 'placed');
        if ($pop.data('mode') !== mode) {
            // Switching layout: jump instead of sliding from a stale transform.
            $pop.addClass('sconb-tour-snap').data('mode', mode);
            requestAnimationFrame(function () { requestAnimationFrame(function () { $pop.removeClass('sconb-tour-snap'); }); });
        }
        $pop.removeClass('sconb-tour-sheet sconb-tour-center sconb-tour-place-top sconb-tour-place-bottom sconb-tour-place-left sconb-tour-place-right');
        if (mode === 'sheet') { $pop.addClass('sconb-tour-sheet').css({ transform: '' }); return; }
        if (mode === 'center') { $pop.addClass('sconb-tour-center').css({ transform: '' }); return; }

        var pw = $pop.outerWidth(), ph = $pop.outerHeight(), gap = 14;
        var space = { top: r.top, bottom: vh - r.bottom, left: r.left, right: vw - r.right };
        var place = step.placement && step.placement !== 'auto' ? step.placement : null;
        var fits = function (p) { return (p === 'top' || p === 'bottom') ? space[p] >= ph + gap + pad : space[p] >= pw + gap + pad; };
        if (!place || !fits(place)) place = ['bottom', 'right', 'top', 'left'].filter(fits)[0] || 'bottom';
        var tp, lp;
        if (place === 'bottom') { tp = r.bottom + pad + gap; lp = r.left + r.width / 2 - pw / 2; }
        else if (place === 'top') { tp = r.top - pad - gap - ph; lp = r.left + r.width / 2 - pw / 2; }
        else if (place === 'right') { tp = r.top + r.height / 2 - ph / 2; lp = r.right + pad + gap; }
        else { tp = r.top + r.height / 2 - ph / 2; lp = r.left - pad - gap - pw; }
        tp = Math.round(Math.max(12, Math.min(tp, vh - ph - 12)));
        lp = Math.round(Math.max(12, Math.min(lp, vw - pw - 12)));
        $pop.addClass('sconb-tour-place-' + place).css({ transform: 'translate3d(' + lp + 'px,' + tp + 'px,0)' });
        var arrow = (place === 'top' || place === 'bottom')
            ? { left: Math.max(18, Math.min(pw - 18, r.left + r.width / 2 - lp)), top: '' }
            : { top: Math.max(18, Math.min(ph - 18, r.top + r.height / 2 - tp)), left: '' };
        $ui.find('.sconb-tour-arrow').css(arrow);
    }

    function skippedList() {
        return run && run.skipped ? Object.keys(run.skipped).map(Number).sort(function (a, b) { return a - b; }) : [];
    }
    function render(step) {
        var def = run.def, i = run.index;
        if (run.skipped) delete run.skipped[i];
        run.busy = false;
        lockPage(!!(step.interactive || step.advanceOn));
        // Numbering counts only the steps that are actually shown: skipped steps are left out of n and total.
        var skipped = skippedList();
        var shown = run.steps.map(function (s, k) { return k; }).filter(function (k) { return skipped.indexOf(k) === -1; });
        var total = shown.length, pos = shown.indexOf(i), isLast = i >= shown[shown.length - 1];
        var $pop = $ui.find('.sconb-tour-pop');
        $pop.removeClass('sconb-tour-enter sconb-tour-dir-back');
        void $pop[0].offsetWidth;
        $pop.addClass('sconb-tour-enter' + (lastDir < 0 ? ' sconb-tour-dir-back' : ''));
        var tourTitle = txt(def.title);
        $ui.find('.sconb-tour-kicker-title').text(tourTitle ? tourTitle + ' · ' : '');
        // Only the counter is a live region; the title gets focus, so each step is announced once.
        $ui.find('.sconb-tour-count').text(t('step_of', { n: pos + 1, total: total }));
        $ui.find('.sconb-tour-title').text(txt(step.title));
        $ui.find('.sconb-tour-body').html(txt(step.body));
        $ui.find('.sconb-tour-try').html(step.advanceOn
            ? '<span class="sconb-tour-try-dot" aria-hidden="true"></span><span>' + esc(txt(step.tryText) || t('try_it')) + '</span>'
            : '').toggle(!!step.advanceOn);
        var dots = '';
        shown.forEach(function (d) {
            dots += '<li><button type="button" tabindex="-1" data-sconb-dot="' + d + '" class="' +
                (d < i ? 'is-done' : (d === i ? 'is-current' : '')) + '"></button></li>';
        });
        $ui.find('.sconb-tour-dots').html(dots).toggleClass('sconb-tour-dots-many', total > 14);
        $ui.find('[data-sconb-back]').toggle(pos > 0);
        $ui.find('[data-sconb-next]').text(isLast ? t('finish') : (step.advanceOn ? t('skip_step') : t('next')));
        position();
        setTimeout(focusTitle, reduced ? 0 : 60);
    }
    function focusTitle() {
        if (!$ui) return;
        var title = $ui.find('.sconb-tour-title')[0];
        if (!title || document.activeElement === title) return;
        try { title.focus({ preventScroll: true }); } catch (e) { title.focus(); }
    }

    function scrollToTarget(el) {
        var rect = el.getBoundingClientRect();
        var vw = window.innerWidth, vh = window.innerHeight, mobile = vw < 640, head = headerOffset();
        var bottomSafe = mobile ? vh * 0.45 : vh - 60;
        if (mobile || rect.top < head + 16 || rect.bottom > bottomSafe || rect.left < 0 || rect.right > vw) {
            try { el.scrollIntoView({ block: mobile ? 'start' : 'center', inline: 'nearest', behavior: 'auto' }); } catch (e) { el.scrollIntoView(); }
            rect = el.getBoundingClientRect();
            if (rect.top < head + 12) window.scrollBy(0, rect.top - head - 12);
        }
    }

    /** Leaves step `index` out (missing target, no destination, error) and moves on in the current direction. */
    function skip(index, why) {
        if (!run) return Promise.resolve();
        (run.skipped = run.skipped || {})[index] = true;
        if (why && window.console && console.warn) console.warn('[SCTour] ' + run.id + ' step ' + (index + 1) + ' skipped: ' + why);
        var to = lastDir < 0 ? index - 1 : index + 1;
        // Going back from the first shown step: never go below 0, continue forward instead.
        while (lastDir < 0 && to >= 0 && run.skipped[to]) to--;
        if (to < 0) { lastDir = 1; to = index + 1; }
        return show(to);
    }

    function show(index, opts) {
        if (!run) return Promise.resolve();
        opts = opts || {};
        if (index < 0) index = 0;
        if (index >= run.steps.length) { complete(); return Promise.resolve(); }
        run.index = index;
        run.busy = true;
        persistActive();
        var step = run.steps[index];
        if (run.cleanup) { run.cleanup(); run.cleanup = null; }
        if ($ui) $ui.addClass('sconb-tour-busy');
        var mine = function () { return run && run.index === index; };

        if (!opts.noNav && hasPage(step) && !onPage(step)) {
            var dest;
            try { dest = typeof step.page === 'function' ? step.page() : step.page; } catch (e) { dest = Promise.reject(e); }
            return Promise.resolve(dest).then(function (url) {
                if (!mine()) return null;
                // '' is a real destination (the dashboard); only null/undefined/false mean "skip this step".
                if (url === null || url === undefined || url === false) return skip(index);
                var safe = urlFor(url);
                if (safe === null) {
                    if (window.console && console.warn) console.warn('[SCTour] blocked navigation to ' + String(url).slice(0, 200) + ' (not an admin URL on this site)');
                    return show(index, { noNav: true, center: true });
                }
                if (pathOfUrl(safe) !== pathNow()) {
                    persistActive(pathOfUrl(safe), true);
                    SCTour._navigate(safe);
                    return null;
                }
                return show(index, { noNav: true });
            }, function (err) {
                if (!mine()) return null;
                return skip(index, 'page() failed: ' + (err && err.message || err));
            });
        }

        var fresh = !$ui;
        buildUi();
        $ui.addClass('sconb-tour-busy');
        if (fresh) { run.$target = null; position(); }
        var target = opts.center ? null : step.target;
        return new Promise(function (resolve) { resolve(step.before ? step.before() : null); }).then(function () {
            if (!mine()) return null;
            return target ? prepareTarget(step) : (collapseMenus(), closeSidebar());
        }).then(function () {
            if (!mine()) return null;
            if (target) { $(target); } // throws now on an invalid selector instead of failing silently every poll
            return target ? waitFor(target, stepTimeout(step)) : null;
        }).then(function ($t) {
            if (!mine() || !$ui) return;
            $ui.removeClass('sconb-tour-busy');
            if (target && !$t && step.optional) return skip(index);
            run.$target = $t;
            if ($t) scrollToTarget($t[0]);
            if (step.advanceOn) {
                var sel = step.advanceOn.selector || target, ev = (step.advanceOn.event || 'click') + '.sconbstep';
                var handler = function () {
                    setTimeout(function () { if (run && run.index === index && !run.busy) { lastDir = 1; show(index + 1); } }, step.advanceOn.delay || 350);
                };
                if (sel) {
                    $(document).on(ev, sel, handler);
                    run.cleanup = function () { $(document).off(ev, sel, handler); };
                }
            }
            render(step);
            setTimeout(position, reduced ? 0 : 380);
            $(document).trigger('sctour:step', [run.id, index]);
        }).catch(function (err) {
            if (!mine()) return null;
            if ($ui) $ui.removeClass('sconb-tour-busy');
            return skip(index, (err && err.message) || String(err));
        });
    }

    // While a step is loading (navigation, waiting for its target) extra clicks/keys are ignored,
    // so a double-click on Next can't jump over a step.
    function next() { if (!run || run.busy) return; lastDir = 1; show(run.index + 1); }
    function back() {
        if (!run || run.busy) return;
        var to = run.index - 1;
        while (to >= 0 && run.skipped && run.skipped[to]) to--;
        if (to < 0) return;
        lastDir = -1;
        show(to);
    }

    function celebrate() {
        if (reduced) return;
        var colors = ['#059669', '#10b981', '#34d399', '#f59e0b', '#3598db', '#f28c28'];
        var $c = $('<div class="sconb-confetti" aria-hidden="true"></div>');
        for (var i = 0; i < 30; i++) {
            var piece = $('<i></i>').css({
                left: (50 + (Math.random() * 40 - 20)) + '%', background: colors[i % colors.length],
                animationDelay: Math.round(Math.random() * 120) + 'ms'
            }).appendTo($c)[0];
            // jQuery 3.1's .css() drops custom properties, so set them on the style object directly.
            piece.style.setProperty('--dx', Math.round(Math.random() * 360 - 180) + 'px');
            piece.style.setProperty('--dy', Math.round(-(Math.random() * 260 + 120)) + 'px');
            piece.style.setProperty('--rot', Math.round(Math.random() * 720 - 360) + 'deg');
        }
        $('body').append($c);
        setTimeout(function () { $c.remove(); }, 1600);
    }
    function toast(msg) {
        if (typeof window.alert_float === 'function') { window.alert_float('success', esc(msg)); return; }
        var $t = $('<div class="sconb-toast" role="status"></div>').text(msg).appendTo('body');
        setTimeout(function () { $t.addClass('is-in'); }, 10);
        setTimeout(function () { $t.remove(); }, 3200);
    }
    function restoreFocus() {
        var el = lastFocus;
        lastFocus = null;
        if (el && el !== document.body && document.body.contains(el) && !$(el).closest('.sconb-tour-root').length && typeof el.focus === 'function') {
            try { el.focus({ preventScroll: true }); } catch (e) { /* ignore */ }
        } else if (document.activeElement && $(document.activeElement).closest('.sconb-tour-root').length) {
            // Nothing to return to (tour resumed after a page load): don't leave focus on the fading popover.
            document.activeElement.blur();
        }
    }

    function finishRun() {
        if (run && run.cleanup) run.cleanup();
        run = null;
        collapseMenus();
        closeSidebar();
        clearActive();
        destroyUi();
        restoreFocus();
    }

    function complete() {
        if (!run) return;
        var id = run.id, def = run.def;
        finishRun();
        markDone(id).then(function () { $(document).trigger('sctour:saved', [id]); });
        if (def.celebrate !== false) celebrate();
        toast(t('tour_complete', { title: txt(def.title) || 'Tour' }));
        $(document).trigger('sctour:complete', [id]);
        $(document).trigger('sctour:end', [id, true]);
    }

    function end(completed) {
        if (!run) return;
        if (completed) { complete(); return; }
        var id = run.id;
        finishRun();
        $(document).trigger('sctour:end', [id, false]);
    }

    function start(id, opts) {
        var def = tours[id];
        if (!def) return false;
        var steps = stepsFor(def);
        if (!steps.length || !can(def.perm)) return false;
        if (run) end(false);
        if (!lastFocus) lastFocus = document.activeElement;
        // Close any open Bootstrap modal/dropdown so the spotlight is not hidden behind it.
        if (!(opts && opts.keepModals)) {
            try { $('.modal.in').modal('hide'); } catch (e) { /* no bootstrap */ }
        }
        run = { id: id, def: def, steps: steps, index: 0 };
        lastDir = 1;
        $(document).trigger('sctour:start', [id]);
        show((opts && opts.step) || 0);
        return true;
    }

    function resume() {
        if (run) return; // a tour was started before the delayed resume ran
        var saved;
        try { saved = JSON.parse(ss('get') || 'null'); } catch (e) { saved = null; }
        if (!saved || typeof saved !== 'object' || typeof saved.id !== 'string' || !Object.prototype.hasOwnProperty.call(tours, saved.id) ||
            (saved.staff || 0) !== (CFG.staffId || 0) || typeof saved.index !== 'number' || saved.index % 1 !== 0 || saved.index < 0 ||
            (saved.expect !== undefined && (typeof saved.expect !== 'string' || urlFor(saved.expect) === null))) {
            if (saved) clearActive();
            return;
        }
        var def = tours[saved.id], steps = stepsFor(def);
        var step = steps[saved.index];
        if (!step) { clearActive(); return; }
        // Only resume where the tour itself was taking the user; if they navigated elsewhere, let them go.
        var here = pathNow();
        if (saved.expect !== undefined && canon(saved.expect) !== here && !(hasPage(step) || hasMatch(step) ? onPage(step) : false)) { clearActive(); return; }
        var skipped = {};
        ($.isArray(saved.skip) ? saved.skip : []).forEach(function (k) { if (typeof k === 'number' && k >= 0 && k < steps.length) skipped[k] = true; });
        run = { id: saved.id, def: def, steps: steps, index: saved.index, skipped: skipped };
        // After a tour-driven navigation, never navigate again for the same step (avoids redirect loops).
        show(saved.index, { noNav: !!saved.nav });
    }

    /** Small pulsing hint that starts a tour. Returns the jQuery element (or null). */
    function beacon(selector, o) {
        o = o || {};
        var $host = $(selector).first();
        if (!$host.length || $host.find('[data-sconb-beacon]').length) return null;
        if (o.tour && (!tours[o.tour] || !can(tours[o.tour].perm))) return null;
        var label = o.label || t('quick_tour');
        var $b = $('<button type="button" class="sconb-beacon" data-sconb-beacon></button>')
            .attr('aria-label', label).attr('title', label)
            .html('<span class="sconb-beacon-dot" aria-hidden="true"></span><span class="sconb-beacon-label">' + esc(o.text || t('quick_tour')) + '</span>');
        $b.on('click', function (e) { e.preventDefault(); $b.remove(); if (o.tour) start(o.tour); if (o.onClick) o.onClick(); });
        $host.append($b);
        return $b;
    }

    /** Opens a collapsed sidebar parent (e.g. 'sales') so a child item can be spotlighted. */
    function expandMenu(slug) {
        var $li = $('#side-menu > li.menu-item-' + slug + ', #menu li.menu-item-' + slug).first();
        if (!$li.length) return Promise.resolve();
        openSidebar();
        return expandLi($li);
    }
    function expandLi($li) {
        var $ul = $li.children('ul.nav-second-level');
        if (!$ul.length || ($ul.hasClass('in') && $ul.is(':visible'))) return Promise.resolve();
        // Remember what we opened so collapseMenus() can put the sidebar back the way the user had it.
        if (run && !expandedMenus.some(function (m) { return m.li === $li[0]; })) {
            expandedMenus.push({ li: $li[0], hadActive: $li.hasClass('active'), hadIn: $ul.hasClass('in'), ulAria: $ul.attr('aria-expanded'), aAria: $li.children('a').attr('aria-expanded') });
        }
        // Same end state metisMenu leaves behind when a parent is clicked open.
        $li.addClass('active');
        $ul.addClass('in').removeClass('collapsing').attr('aria-expanded', 'true').css({ height: '', display: '' });
        $li.children('a').attr('aria-expanded', 'true');
        return new Promise(function (resolve) { setTimeout(resolve, reduced ? 0 : 200); });
    }

    /** Collapses the submenus the tour expanded, except ancestors of `$keep` (the next sidebar target). */
    function collapseMenus($keep) {
        expandedMenus = expandedMenus.filter(function (m) {
            if ($keep && $keep.length && $.contains(m.li, $keep[0])) return true;
            var $li = $(m.li), $ul = $li.children('ul.nav-second-level');
            if (!m.hadActive) $li.removeClass('active');
            $ul.toggleClass('in', !!m.hadIn);
            if (m.ulAria === undefined) $ul.removeAttr('aria-expanded'); else $ul.attr('aria-expanded', m.ulAria);
            if (m.aAria === undefined) $li.children('a').removeAttr('aria-expanded'); else $li.children('a').attr('aria-expanded', m.aAria);
            return false;
        });
    }

    var seq = 0;
    window.SCTour = {
        register: function (id, def) {
            if (!/^[a-z0-9_-]{1,60}$/.test(String(id)) || !def) return;
            def.id = id;
            def.seq = seq++;
            tours[id] = def;
        },
        get: function (id) { return tours[id]; },
        all: function () { return tours; },
        available: available,
        steps: function (id) { return tours[id] ? stepsFor(tours[id]) : []; },
        start: start, end: end, next: next, back: back,
        isActive: function () { return !!run; },
        current: function () { return run ? { id: run.id, index: run.index, total: run.steps.length, busy: !!run.busy } : null; },
        state: loadState, save: save, markDone: markDone,
        isDone: function (id) { return !!(state && state.completed.indexOf(id) !== -1); },
        beacon: beacon, expandMenu: expandMenu,
        can: can, esc: esc, t: t, txt: txt, lang: LANG, url: urlFor, path: pathNow,
        reducedMotion: function () { return reduced; },
        _navigate: function (url) { window.location.href = url; },
        _resume: resume
    };

    $(function () {
        loadState().then(function () { setTimeout(resume, 300); });
        $(document).on('click', '[data-sconb-start]', function (e) {
            var id = String($(this).attr('data-sconb-start'));
            if (tours[id]) { e.preventDefault(); start(id); }
        });
    });
})(window.jQuery, window, document);

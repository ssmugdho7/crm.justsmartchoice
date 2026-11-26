(function ($) {
  'use strict';
  if (!$ || !window.SCAO) return;
  var S = window.SCAO, T = S.t, cards = S.cards, modules = S.modules;
  var MOD_PAGE = 50;
  var state = { cat: '', status: '', q: '', mq: '', mst: '', msort: 'name', mlimit: MOD_PAGE };

  function esc(s) { return $('<div>').text(s == null ? '' : String(s)).html(); }
  function fmt(tpl, v) { return String(tpl || '').replace('%s', v); }
  function csrf() {
    var o = {};
    var h = (window.csrfData && window.csrfData.hash) || (document.cookie.match(/(?:^|; )csrf_cookie_name=([^;]*)/) || [])[1];
    o[S.csrfName] = h ? decodeURIComponent(h) : '';
    return o;
  }
  function toast(type, msg) {
    if (typeof alert_float === 'function') alert_float(type, esc(msg)); else window.alert(msg);
  }
  function post(path, data) {
    return $.ajax({ url: S.base + path, type: 'POST', data: $.extend({}, data || {}, csrf()), dataType: 'json', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  }
  function errMsg(xhr) { return (xhr && xhr.responseJSON && xhr.responseJSON.message) || T.error; }
  var ICON = function (cls) { return '<i class="' + cls + '" aria-hidden="true"></i>'; };

  /** Accessible confirm dialog: aria-labelledby/-describedby, autofocus on the type-to-confirm input. */
  var cmSeq = 0;
  function confirmBox(opts) {
    return new Promise(function (resolve) {
      var n = ++cmSeq, tid = 'scao-cm-t' + n, did = 'scao-cm-d' + n, iid = 'scao-cm-i' + n;
      var $m = $('<div class="modal fade" tabindex="-1" role="dialog" aria-modal="true"><div class="modal-dialog" role="document"><div class="modal-content">' +
        '<div class="modal-header"><h4 class="modal-title"></h4></div><div class="modal-body"><p class="scao-cm-msg"></p><ul class="scao-cm-list" hidden></ul>' +
        '<div class="scao-cm-typed form-group" hidden><label></label><input type="text" class="form-control" autocomplete="off" spellcheck="false"></div></div>' +
        '<div class="modal-footer"><button type="button" class="btn btn-default scao-cm-no"></button><button type="button" class="btn btn-danger scao-cm-yes"></button></div></div></div></div>');
      $m.attr({ 'aria-labelledby': tid, 'aria-describedby': did });
      var done = false, ans = false;
      $m.find('.modal-title').attr('id', tid).text(opts.title);
      $m.find('.scao-cm-msg').attr('id', did).text(opts.message);
      if (opts.list && opts.list.length) {
        $m.find('.scao-cm-list').prop('hidden', false).html(opts.list.map(function (l) { return '<li>' + esc(l) + '</li>'; }).join(''));
      }
      $m.find('.scao-cm-no').text(T.cancel);
      var $yes = $m.find('.scao-cm-yes').text(opts.action || T.confirm);
      var $inp = $m.find('input').attr('id', iid);
      if (opts.typed) {
        $m.find('.scao-cm-typed').prop('hidden', false).find('label').attr('for', iid).text(fmt(T.type_name, opts.typed));
        $yes.prop('disabled', true);
        $inp.on('input', function () { $yes.prop('disabled', $.trim(this.value) !== opts.typed); });
        $inp.on('keydown', function (e) { if (e.key === 'Enter' && !$yes.prop('disabled')) { e.preventDefault(); $yes.trigger('click'); } });
      }
      $m.on('shown.bs.modal', function () { (opts.typed ? $inp : $m.find('.scao-cm-no')).trigger('focus'); });
      $m.on('click', '.scao-cm-no', function () { $m.modal('hide'); });
      $m.on('click', '.scao-cm-yes', function () { ans = true; $m.modal('hide'); });
      $m.on('hidden.bs.modal', function () { if (done) return; done = true; $m.remove(); resolve(ans); });
      $('body').append($m); $m.modal({ backdrop: 'static' });
    });
  }

  // ---------- Integrations ----------
  var STATUS_LABEL = { connected: T.st_connected, needs_attention: T.st_needs_attention, not_configured: T.st_not_configured, not_installed: T.st_not_installed, disabled: T.st_disabled };

  function matches(c) {
    if (state.cat && c.category !== state.cat) return false;
    if (state.status && c.status !== state.status) return false;
    if (state.q) {
      var hay = (c.name + ' ' + c.category + ' ' + c.description + ' ' + c.reason).toLowerCase();
      if (hay.indexOf(state.q) === -1) return false;
    }
    return true;
  }

  /** Can this card be disconnected right now? Toggle entries when on; erase entries when credentials are saved. */
  function canDisconnect(c) {
    if (c.disconnect_mode === 'erase') return (c.erase_labels || []).length > 0;
    return c.toggleable && c.enabled !== false;
  }

  function moduleMenu(c) {
    var items = [];
    var li = function (act, label, extra) { return '<li><a href="#" role="menuitem" data-act="' + act + '" data-mod="' + esc(c.module.name) + '">' + esc(label) + (extra ? ' <small class="text-muted">' + esc(extra) + '</small>' : '') + '</a></li>'; };
    if (c.module.active && !isProtected(c.module.name)) {
      items.push(li('mod-deactivate', T.deactivate));
      items.push('<li class="disabled"><a href="#" role="menuitem" aria-disabled="true" tabindex="-1" data-noop="1">' + esc(T.uninstall) + ' <small>(' + esc(T.uninstall_needs_deactivate) + ')</small></a></li>');
      items.push(li('mod-deactivate-uninstall', T.deactivate_uninstall));
    } else if (!c.module.active && c.module.installed && !isProtected(c.module.name)) {
      items.push(li('mod-uninstall', T.uninstall));
    }
    if (!items.length) return '';
    return '<div class="btn-group"><button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="' + esc(T.menu) + '">' + ICON('fa-solid fa-ellipsis-vertical') + '</button>' +
      '<ul class="dropdown-menu dropdown-menu-right" role="menu">' + items.join('') + '</ul></div>';
  }

  function buttons(c) {
    var b = [];
    var btn = function (act, label, cls) { return '<button type="button" class="btn btn-sm ' + cls + '" data-act="' + act + '" data-id="' + esc(c.id) + '">' + esc(label) + '</button>'; };
    var disc = function () {
      if (c.disconnect_block) return '<button type="button" class="btn btn-sm btn-danger btn-outline" disabled title="' + esc(c.disconnect_block) + '" aria-label="' + esc(T.disconnect + ': ' + c.disconnect_block) + '">' + esc(T.disconnect) + '</button>';
      return btn('disconnect', T.disconnect, 'btn-danger btn-outline');
    };
    var cfg = function (cls) { return c.configurable ? btn('configure', T.configure, cls) : btn('open', T.open_settings, cls); };
    if (c.status === 'not_installed') {
      if (c.module_on_disk) b.push(btn('mod-install', T.install, 'btn-primary'));
      else b.push('<span class="text-muted small">' + esc(T.module_missing) + '</span>');
    } else if (c.status === 'connected') {
      b.push(cfg('btn-default'));
      b.push(btn('test', T.test, 'btn-default'));
      if (canDisconnect(c)) b.push(disc());
    } else if (c.status === 'needs_attention') {
      b.push(cfg('btn-primary'));
      b.push(btn('test', T.test, 'btn-default'));
      if (canDisconnect(c)) b.push(disc());
    } else if (c.status === 'disabled') {
      if (c.module && !c.module.active && !c.module.installed) b.push(btn('mod-install', T.install, 'btn-primary'));
      else if (c.module && !c.module.active) b.push(btn('mod-activate', T.activate, 'btn-primary'));
      else if (c.configurable) b.push(btn('connect', T.enable, 'btn-primary'));
      if (c.configurable) b.push(btn('configure', T.configure, 'btn-default'));
    } else {
      b.push(btn(c.configurable ? 'connect' : 'open', c.configurable ? T.connect : T.open_settings, 'btn-primary'));
      if (c.disconnect_mode === 'erase' && canDisconnect(c)) b.push(disc());
    }
    if (c.module) b.push(moduleMenu(c));
    return b.join(' ');
  }
  function isProtected(name) { return modules.some(function (m) { return m.name === name && m.protected; }); }

  function cardHtml(c) {
    var test = c.last_test ? '<div class="scao-test ' + (c.last_test.ok ? 'ok' : 'bad') + '">' + ICON('fa-solid ' + (c.last_test.ok ? 'fa-circle-check' : 'fa-circle-xmark')) + ' ' +
      esc(T.last_test) + ': ' + esc(c.last_test.message) + ' <span class="text-muted">(' + esc(c.last_test.at) + ')</span></div>' : '';
    var reason = c.reason ? '<div class="scao-reason scao-reason-' + esc(c.status) + '">' + esc(c.reason) + '</div>' : '';
    return '<div class="scao-card scao-s-' + esc(c.status) + '" data-id="' + esc(c.id) + '">' +
      '<div class="scao-card-top"><span class="scao-ico" aria-hidden="true"><i class="' + esc(c.icon) + '"></i></span>' +
      '<div class="scao-card-name"><strong>' + esc(c.name) + '</strong><small>' + esc(S.catLabels[c.category] || c.category) + (c.module ? ' · ' + esc(T.module_label) + ' ' + esc(c.module.name) : '') + '</small></div>' +
      '<span class="scao-badge scao-badge-' + esc(c.status) + '">' + esc(STATUS_LABEL[c.status] || c.status) + '</span></div>' +
      '<p class="scao-desc">' + esc(c.description) + '</p>' + reason + test +
      '<div class="scao-actions">' + buttons(c) + '</div></div>';
  }

  function render() {
    var counts = { connected: 0, needs_attention: 0, not_configured: 0, disabled: 0, not_installed: 0 };
    cards.forEach(function (c) { if (counts[c.status] != null) counts[c.status]++; });
    $('#scao-stats [data-count]').each(function () { $(this).text(counts[$(this).data('count')] || 0); });
    $('#scao-stats .scao-stat').each(function () {
      var on = $(this).data('filter-status') === state.status;
      $(this).toggleClass('active', on).attr('aria-pressed', on ? 'true' : 'false');
    });

    var groups = [
      ['connected', T.sec_connected, function (c) { return c.status === 'connected'; }],
      ['needs_attention', T.sec_attention, function (c) { return c.status === 'needs_attention'; }],
      ['available', T.sec_available, function (c) { return c.status === 'not_configured' || c.status === 'not_installed'; }],
      ['disabled', T.sec_disabled, function (c) { return c.status === 'disabled'; }]
    ];
    var html = '', total = 0;
    groups.forEach(function (g) {
      var list = cards.filter(function (c) { return g[2](c) && matches(c); });
      if (!list.length) return;
      total += list.length;
      html += '<section class="scao-section"><h3>' + esc(g[1]) + ' <span class="badge">' + list.length + '</span></h3><div class="scao-grid">' + list.map(cardHtml).join('') + '</div></section>';
    });
    $('#scao-sections').html(html);
    $('#scao-empty').prop('hidden', total > 0);
    renderModules();
  }

  // ---------- Modules tab ----------
  function modState(m) { return m.active ? 'active' : (m.installed ? 'inactive' : 'available'); }
  var MOD_ORDER = { active: 0, inactive: 1, available: 2 };

  function modRow(m) {
    var st = { active: T.mod_active, inactive: T.mod_inactive, available: T.mod_not_installed }[modState(m)];
    var a = [];
    var btn = function (act, label, cls) { return '<button type="button" class="btn btn-sm ' + cls + '" data-act="' + act + '" data-mod="' + esc(m.name) + '">' + esc(label) + '</button>'; };
    if (!m.installed) a.push(btn('mod-install', T.install, 'btn-primary'));
    else if (!m.active) a.push(btn('mod-activate', T.activate, 'btn-primary'));
    else if (!m.protected) a.push(btn('mod-deactivate', T.deactivate, 'btn-default'));
    if (!m.protected) {
      if (m.active) a.push(btn('mod-deactivate-uninstall', T.deactivate_uninstall, 'btn-danger btn-outline'));
      else a.push(btn('mod-uninstall', T.uninstall, 'btn-danger btn-outline'));
    }
    if (m.protected) a.push('<span class="label label-default">' + ICON('fa-solid fa-lock') + ' ' + esc(T.protected) + '</span>');
    return '<div class="scao-row"><div class="scao-row-main"><strong>' + esc(m.title) + '</strong> <code>' + esc(m.name) + '</code>' +
      '<small class="text-muted">' + (m.version ? esc(T.version) + ' ' + esc(m.version) : '') + (m.author ? ' · ' + esc(T.by) + ' ' + esc(m.author) : '') + '</small>' +
      '<div class="scao-row-desc" title="' + esc(m.description_full || m.description) + '">' + esc(m.description) + '</div></div>' +
      '<span class="scao-badge scao-badge-mod-' + modState(m) + '">' + esc(st) + '</span><div class="scao-row-actions">' + a.join(' ') + '</div></div>';
  }

  function renderModules() {
    $('#scao-mod-count').text(modules.length);
    var q = state.mq;
    var searched = modules.filter(function (m) { return !q || (m.name + ' ' + m.title + ' ' + (m.description_full || m.description)).toLowerCase().indexOf(q) !== -1; });
    var mc = { '': searched.length, active: 0, inactive: 0, available: 0 };
    searched.forEach(function (m) { mc[modState(m)]++; });
    $('#scao-mod-chips [data-mcount]').each(function () { $(this).text('(' + (mc[$(this).attr('data-mcount')] || 0) + ')'); });
    $('#scao-mod-chips .scao-chip').each(function () {
      var on = String($(this).attr('data-mst')) === state.mst;
      $(this).toggleClass('active', on).attr('aria-pressed', on ? 'true' : 'false');
    });
    var list = searched.filter(function (m) { return !state.mst || modState(m) === state.mst; });
    list.sort(function (a, b) {
      if (state.msort === 'status') {
        var d = MOD_ORDER[modState(a)] - MOD_ORDER[modState(b)];
        if (d) return d;
      }
      return String(a.title).toLowerCase().localeCompare(String(b.title).toLowerCase());
    });
    if (!list.length) {
      $('#scao-modlist').html('<p class="scao-empty">' + esc(T.no_modules) + '</p>');
      $('#scao-mod-more').prop('hidden', true);
      return;
    }
    // Render in pages so ~150 rows stay fast; "Show more" appends the next page.
    var shown = list.slice(0, state.mlimit);
    $('#scao-modlist').html(shown.map(modRow).join(''));
    var more = list.length > shown.length;
    $('#scao-mod-more').prop('hidden', !more);
    $('#scao-mod-showing').text(T.showing.replace('%1$s', shown.length).replace('%2$s', list.length));
  }

  function applyResponse(r) {
    if (r.cards) cards = r.cards;
    if (r.modules) modules = r.modules;
    if (r.card) cards = cards.map(function (c) { return c.id === r.card.id ? r.card : c; });
    render();
  }
  function reload() {
    return $.getJSON(S.base + 'data').done(applyResponse).fail(function (x) { toast('danger', (x && x.responseJSON && x.responseJSON.message) || T.refresh_failed); });
  }
  function busy($el, on) {
    $el.prop('disabled', on).toggleClass('is-loading', on).attr('aria-busy', on ? 'true' : null);
    $el.each(function () {
      var $b = $(this);
      if (on && !$b.find('.scao-spin').length) $b.prepend('<i class="fa-solid fa-spinner fa-spin scao-spin" aria-hidden="true"></i> ');
      if (!on) $b.find('.scao-spin').remove();
    });
  }

  // ---------- Configure modal ----------
  var current = null, opening = false;

  function renderGuide(g) {
    var $g = $('#scao-config-guide');
    if (!g || !(g.steps || []).length) { $g.prop('hidden', true); return; }
    $('#scao-guide-steps').html(g.steps.map(function (s) { return '<li>' + esc(s) + '</li>'; }).join('') +
      (g.generic ? '<li class="text-muted">' + esc(T.guide_generic) + '</li>' : ''));
    $('#scao-guide-urls').html((g.urls || []).map(function (u, i) {
      var id = 'scao-gu-' + i;
      return '<div class="scao-guide-url"><label for="' + id + '">' + esc(u.label) + '</label><div class="scao-copy">' +
        '<input type="text" class="form-control input-sm" id="' + id + '" readonly value="' + esc(u.url) + '">' +
        '<button type="button" class="btn btn-default btn-sm" data-copy="' + id + '">' + ICON('fa-regular fa-copy') + ' <span>' + esc(T.copy) + '</span></button></div></div>';
    }).join(''));
    var $d = $('#scao-guide-docs');
    if (g.docs && /^https:\/\//.test(g.docs.url)) {
      $d.attr('href', g.docs.url).html(ICON('fa-solid fa-arrow-up-right-from-square') + ' ' + esc(g.docs.label) + '<span class="sr-only"> (' + esc(T.opens_new_tab) + ')</span>').prop('hidden', false);
    } else $d.prop('hidden', true);
    $g.prop('hidden', false);
  }

  function fieldGroup(f, i) {
    var idn = 'scao-f-' + i, g = $('<div class="form-group"></div>').attr('data-option', f.option);
    var hint = f.required ? ' <span class="text-danger" aria-hidden="true">*</span><span class="sr-only">(' + esc(T.required) + ')</span>'
      : (f.recommended ? ' <span class="label label-info scao-rec">' + esc(T.recommended) + '</span>' : '');
    var label = '<label for="' + idn + '">' + esc(f.label) + hint + '</label>';
    var inp, helpId = idn + '-help';
    if (f.type === 'checkbox') {
      g.html('<div class="checkbox checkbox-primary"><input type="checkbox" id="' + idn + '" name="' + esc(f.option) + '"' + (f.value === '1' ? ' checked' : '') + '><label for="' + idn + '">' + esc(f.label) + '</label></div>');
    } else if (f.type === 'select') {
      inp = '<select class="form-control" id="' + idn + '" name="' + esc(f.option) + '">' + (f.options || []).map(function (o) { return '<option value="' + esc(o.value) + '"' + (o.value === f.value ? ' selected' : '') + '>' + esc(o.label) + '</option>'; }).join('') + '</select>';
      g.html(label + inp);
    } else {
      inp = (f.type === 'textarea' || f.type === 'secret_textarea')
        ? $('<textarea class="form-control" rows="4"></textarea>').attr({ id: idn, name: f.option, autocomplete: 'off', spellcheck: 'false' })
        : $('<input class="form-control">').attr({ id: idn, name: f.option, type: f.secret ? 'password' : 'text', autocomplete: f.secret ? 'new-password' : 'off', spellcheck: 'false' });
      if (f.secret) inp.attr('placeholder', f.has_value ? T.saved_keep : ''); else inp.val(f.value || '');
      if (f.required) inp.attr('aria-required', 'true');
      if (f.help) inp.attr('aria-describedby', helpId);
      g.html(label).append(inp);
      if (f.secret && f.has_value) {
        // A blank secret keeps the stored value, so clearing needs an explicit tick.
        var cid = idn + '-clear';
        var $clr = $('<div class="checkbox scao-clear"><input type="checkbox" class="scao-clear-cb"><label></label></div>');
        $clr.find('input').attr({ id: cid, 'data-clear': f.option });
        $clr.find('label').attr('for', cid).text(T.clear_saved);
        $clr.find('input').on('change', function () { inp.prop('disabled', this.checked); if (this.checked) inp.val(''); });
        g.append($clr);
      }
    }
    if (f.help) g.append($('<p class="help-block"></p>').attr('id', helpId).text(f.help));
    g.append('<p class="text-danger scao-err" role="alert" hidden></p>');
    return g;
  }

  function openConfig(id, connectMode, $btn) {
    if (opening) return;
    opening = true;
    if ($btn) busy($btn, true);
    $.getJSON(S.base + 'configure/' + encodeURIComponent(id)).done(function (r) {
      current = r; var $f = $('#scao-config-fields').empty();
      var connects = r.entry.toggleable;
      $('#scao-config-title').text(connectMode ? fmt(T.connect_title, r.entry.name) : T.configure + ' — ' + r.entry.name);
      $('#scao-config-desc').text(r.entry.description);
      var st = r.card;
      $('#scao-config-status').html('<span class="scao-badge scao-badge-' + esc(st.status) + '">' + esc(STATUS_LABEL[st.status]) + '</span> ' + (st.reason ? '<span class="text-muted">' + esc(st.reason) + '</span>' : ''));
      var ex = r.entry.exclusive_with || [];
      $('#scao-config-exclusive').prop('hidden', !ex.length).text(ex.length ? fmt(T.exclusive_warn, ex.join(', ')) : '');
      renderGuide(r.guide);
      r.fields.forEach(function (f, i) { $f.append(fieldGroup(f, i)); });
      var $link = $('#scao-config-settings');
      if (r.entry.settings_url) $link.attr('href', r.entry.settings_url).parent().show(); else $link.parent().hide();
      // OAuth integrations: one click saves the keys and continues to the provider's sign-in page.
      var oauth = !!r.entry.oauth_start_url;
      // Use the hidden attribute: theme CSS can override an inline display:none on buttons.
      $('#scao-config-oauth').prop('hidden', !oauth).text(oauth ? fmt(T.save_signin, r.entry.oauth_provider || r.entry.name) : '');
      $('#scao-config-connect').prop('hidden', !connects).text(T.connect).toggleClass('btn-primary', !oauth).toggleClass('btn-default', oauth);
      $('#scao-config-modal').modal('show');
    }).fail(function (x) { toast('danger', errMsg(x)); }).always(function () {
      opening = false;
      if ($btn) busy($btn, false);
    });
  }

  $('#scao-config-modal').on('shown.bs.modal', function () { $('#scao-config-fields').find('input:not([type=checkbox]),select,textarea').first().trigger('focus'); });

  $(document).on('click', '#scao-config-guide [data-copy]', function () {
    var $b = $(this), el = document.getElementById($b.attr('data-copy')), val = el ? el.value : '';
    var ok = function () { $b.find('span').text(T.copied); setTimeout(function () { $b.find('span').text(T.copy); }, 1600); };
    var fallback = function () {
      try { el.select(); if (document.execCommand('copy')) ok(); else toast('warning', T.copy_failed); } catch (e) { toast('warning', T.copy_failed); }
    };
    if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(val).then(ok, fallback); else fallback();
  });

  $('#scao-config-form').on('submit', function (e) {
    e.preventDefault();
    if (!current) return;
    var sub = e.originalEvent && e.originalEvent.submitter, mode = (sub && $(sub).data('mode')) || $(document.activeElement).data('mode') || 'save';
    var oauthUrl = mode === 'oauth' ? String(current.entry.oauth_start_url || '') : '';
    // Only follow same-origin sign-in routes of the CRM itself.
    if (oauthUrl && oauthUrl.indexOf(window.location.origin + '/') !== 0) oauthUrl = '';
    // Popup flows (QuickBooks reloads window.opener) need the window opened inside the click to dodge popup blockers.
    var popup = oauthUrl && current.entry.oauth_popup ? window.open('', 'scao_oauth', 'width=640,height=720') : null;
    var data = { connect: (mode === 'connect' || (mode === 'oauth' && current.entry.toggleable)) ? '1' : '0', fields: {}, clear: {} };
    $('#scao-config-fields').find('input[name],select[name],textarea[name]').each(function () {
      data.fields[this.name] = this.type === 'checkbox' ? (this.checked ? '1' : '0') : $(this).val();
    });
    $('#scao-config-fields .scao-clear-cb:checked').each(function () { data.clear[$(this).attr('data-clear')] = '1'; data.fields[$(this).attr('data-clear')] = ''; });
    var $btns = $(this).find('button[type=submit]'); busy($btns, true);
    $(this).find('.scao-err').prop('hidden', true).text('');
    $(this).find('[aria-invalid]').removeAttr('aria-invalid');
    post('configure/' + encodeURIComponent(current.entry.id), data).done(function (r) {
      if (oauthUrl) {
        if (popup && !popup.closed) { popup.location.href = oauthUrl; $('#scao-config-modal').modal('hide'); applyResponse(r); return; }
        toast('success', T.redirecting);
        window.location.href = oauthUrl;
        return;
      }
      $('#scao-config-modal').modal('hide');
      toast(r.test && !r.test.ok ? 'warning' : 'success', r.message + (r.test ? ' ' + r.test.message : ''));
      applyResponse(r);
    }).fail(function (x) {
      if (popup && !popup.closed) popup.close();
      var errs = (x.responseJSON && x.responseJSON.errors) || {}, first = null;
      Object.keys(errs).forEach(function (o) {
        var $g = $('#scao-config-fields .form-group').filter(function () { return $(this).data('option') === o; });
        $g.find('.scao-err').text(errs[o]).prop('hidden', false);
        $g.find('input,select,textarea').first().attr('aria-invalid', 'true');
        first = first || $g;
      });
      if (first) first.find('input,select,textarea').first().trigger('focus');
      toast('danger', errMsg(x));
    }).always(function () { busy($btns, false); });
  });

  // ---------- Actions ----------
  function moduleAction(act, name, $btn) {
    var run = function (path, extra) {
      if ($btn) busy($btn, true);
      return post(path + '/' + encodeURIComponent(name), extra).done(function (r) { toast('success', r.message); applyResponse(r); })
        .fail(function (x) { toast('danger', errMsg(x)); }).always(function () { if ($btn) busy($btn, false); });
    };
    if (act === 'deactivate') return confirmBox({ title: T.confirm_deactivate_title, message: fmt(T.confirm_deactivate_msg, name), action: T.deactivate }).then(function (ok) { if (ok) run('deactivate'); });
    if (act === 'uninstall') return confirmBox({ title: T.confirm_uninstall_title, message: fmt(T.confirm_uninstall_msg, name), typed: name, action: T.uninstall }).then(function (ok) { if (ok) run('uninstall', { confirm: name }); });
    if (act === 'deactivate-uninstall') {
      return confirmBox({ title: T.confirm_deact_uninstall_title, message: fmt(T.confirm_deact_uninstall_msg, name), typed: name, action: T.deactivate_uninstall })
        .then(function (ok) { if (ok) run('uninstall', { confirm: name, deactivate_first: '1' }); });
    }
    return run(act);
  }

  function disconnect(card, $b) {
    var erase = card.disconnect_mode === 'erase';
    var opts = erase
      ? { title: T.confirm_erase_title, message: fmt(T.confirm_erase_msg, card.name), list: card.erase_labels || [], action: T.disconnect }
      : { title: T.confirm_disconnect_title, message: fmt(T.confirm_disconnect_msg, card.name), action: T.disconnect };
    return confirmBox(opts).then(function (ok) {
      if (!ok) return; busy($b, true);
      post('disconnect/' + encodeURIComponent(card.id)).done(function (r) { toast('success', r.message); applyResponse(r); }).fail(function (x) { toast('danger', errMsg(x)); }).always(function () { busy($b, false); });
    });
  }

  $(document).on('click', '#scao-app [data-act]', function (e) {
    e.preventDefault();
    var $b = $(this), act = $b.data('act'), id = $b.data('id'), mod = $b.data('mod');
    var card = cards.filter(function (c) { return c.id === id; })[0];
    var $target = $b.is('a') ? $b.closest('.scao-actions').find('.dropdown-toggle') : $b;
    if (act === 'configure') return openConfig(id, false, $b);
    if (act === 'connect') return openConfig(id, true, $b);
    if (act === 'open') return card && card.settings_url && window.open(card.settings_url, '_blank', 'noopener');
    if (act === 'test') {
      busy($b, true);
      post('test/' + encodeURIComponent(id)).done(function (r) { toast(r.ok ? 'success' : 'warning', r.message); applyResponse(r); }).fail(function (x) { toast('danger', errMsg(x)); }).always(function () { busy($b, false); });
      return;
    }
    if (act === 'disconnect' && card) return disconnect(card, $b);
    if (act.indexOf('mod-') === 0) {
      return moduleAction(act.slice(4), mod || (card && card.module_name) || '', $target);
    }
  });
  $(document).on('click', '#scao-app [data-noop]', function (e) { e.preventDefault(); e.stopPropagation(); });

  function clearFilters() {
    state.q = ''; state.cat = ''; state.status = '';
    $('#scao-search').val('');
    $('#scao-chips .scao-chip').removeClass('active').attr('aria-pressed', 'false').filter('[data-cat=""]').addClass('active').attr('aria-pressed', 'true');
    render();
    $('#scao-search').trigger('focus');
  }

  $('#scao-search').on('input', function () { state.q = $.trim(this.value).toLowerCase(); render(); });
  $('#scao-clear-filters').on('click', clearFilters);
  $('#scao-mod-search').on('input', function () { state.mq = $.trim(this.value).toLowerCase(); state.mlimit = MOD_PAGE; renderModules(); });
  $('#scao-mod-chips').on('click', '.scao-chip', function () { state.mst = String($(this).attr('data-mst') || ''); state.mlimit = MOD_PAGE; renderModules(); });
  $('#scao-mod-sort').on('change', function () { state.msort = this.value; renderModules(); });
  $('#scao-mod-more-btn').on('click', function () { state.mlimit += MOD_PAGE; renderModules(); });
  $('#scao-chips .scao-chip').each(function () { $(this).attr('aria-pressed', $(this).hasClass('active') ? 'true' : 'false'); });
  $('#scao-chips').on('click', '.scao-chip', function () {
    state.cat = $(this).data('cat');
    $('#scao-chips .scao-chip').removeClass('active').attr('aria-pressed', 'false');
    $(this).addClass('active').attr('aria-pressed', 'true'); render();
  });
  $('#scao-stats').on('click', '.scao-stat', function () { var s = $(this).data('filter-status'); state.status = state.status === s ? '' : s; render(); });
  $('#scao-refresh').on('click', function () { var $b = $(this); busy($b, true); reload().always(function () { busy($b, false); }); });
  $('.scao-tabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    $(e.target).attr({ 'aria-selected': 'true', tabindex: null });
    if (e.relatedTarget) $(e.relatedTarget).attr({ 'aria-selected': 'false', tabindex: '-1' });
  });

  // Fixed widgets (team chat button/panel, onboarding checklist) sit over the bottom of the page. Pad the page so the
  // last row's actions can scroll clear of whichever one is docked at the bottom (CSS keeps a 96px minimum).
  var WIDGETS = '#pusherChat, .main-chat-panel, .sconb-check, #sconb-checklist';
  function fitBottom() {
    var app = document.getElementById('scao-app');
    if (!app) return;
    var vh = window.innerHeight, ar = app.getBoundingClientRect(), need = 96, rects = [];
    $(WIDGETS).each(function () {
      if (getComputedStyle(this).position !== 'fixed') return;
      var r = this.getBoundingClientRect();
      if (r.width && r.height && r.right > ar.left && r.left < ar.right) rects.push(r);
    });
    // Start from widgets docked at the bottom edge, then add any widget stacked right above them (checklist over chat).
    var top = null, grew = true;
    rects.forEach(function (r) { if (r.bottom >= vh * 0.6) top = top === null ? r.top : Math.min(top, r.top); });
    while (top !== null && grew) {
      grew = false;
      rects.forEach(function (r) { if (r.top < top && r.bottom >= top - 24) { top = r.top; grew = true; } });
    }
    if (top !== null) need = Math.max(need, Math.ceil(vh - top) + 16);
    need = Math.min(need, Math.round(vh * 0.85));
    if (app.style.paddingBottom !== need + 'px') app.style.paddingBottom = need + 'px';
  }
  $(window).on('resize', fitBottom);
  $('.scao-tabs a[data-toggle="tab"]').on('shown.bs.tab', fitBottom);
  setInterval(function () { if (!document.hidden) fitBottom(); }, 1500); // widgets open/close on their own
  fitBottom();

  render();
})(window.jQuery);

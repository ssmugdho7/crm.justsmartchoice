

/* Smart Choice 3.8.6 project upload/table and sales item numeric repair. */
(function($){
  'use strict';
  $(document).on('submit', 'form.invoice-form, form.estimate-form, form.proposal-form, form[data-type="invoice"], form[data-type="estimate"]', function(){
    $(this).find('input[name*="[rate]"],input[name*="[qty]"]').each(function(){
      var v=String($(this).val()||'').trim().replace(/[$\s]/g,'');
      if (v.indexOf(',')>-1 && v.indexOf('.')===-1) v=v.replace(',','.'); else v=v.replace(/,/g,'');
      if (v!=='' && !isNaN(Number(v))) $(this).val(Number(v));
    });
  });
  $(document).ajaxComplete(function(e,xhr,settings){
    if(settings && /projects\/upload_file\//.test(settings.url||'')){
      try { var r=typeof xhr.responseJSON==='object'?xhr.responseJSON:JSON.parse(xhr.responseText); if(r&&r.success){ setTimeout(function(){window.location.reload();},250); } } catch(ignore){}
    }
  });
})(jQuery);

/* Smart Choice Emerald admin theme. Preserve the custom sales/upload repairs above. */
(function ($) {
  'use strict';
  $(function () {
    var body = document.body;
    if (!body.classList.contains('admin') || body.dataset.scModernThemeReady) return;
    body.dataset.scModernThemeReady = 'true';

    // The current header already owns its clock and timer; reuse it when present.
    var clock = document.getElementById('sc-header-datetime');
    if (!clock) {
      var nav = document.querySelector('#header nav .tw-flex.tw-justify-between');
      if (nav) {
        clock = document.createElement('time');
        clock.id = 'sc-header-datetime';
        nav.insertBefore(clock, nav.lastElementChild);
        var formatter = new Intl.DateTimeFormat(undefined, {
          weekday: 'short', month: 'short', day: 'numeric', year: 'numeric',
          hour: 'numeric', minute: '2-digit'
        });
        var updateClock = function () {
          var now = new Date();
          clock.textContent = formatter.format(now);
          clock.dateTime = now.toISOString();
        };
        updateClock();
        window.setInterval(updateClock, 30000);
      }
    }
    if (clock) clock.classList.add('sc-live-clock');

    $(document).on('keydown.smartchoiceTheme', function (event) {
      if (!(event.ctrlKey || event.metaKey) || event.altKey || String(event.key).toLowerCase() !== 'k') return;
      var search = document.querySelector('#header #search_input, #header input[type="search"]');
      if (search && !search.disabled && search.getClientRects().length) {
        event.preventDefault();
        search.focus();
      }
    });

    function desktopSidebar() {
      return window.innerWidth >= 769 && !body.classList.contains('page-small') && !body.classList.contains('mobile');
    }
    function restoreSidebar() {
      if (!desktopSidebar()) return;
      try {
        var saved = localStorage.getItem('smartchoice_sidebar_collapsed');
        if (saved === 'true' || saved === 'false') {
          body.classList.toggle('hide-sidebar', saved === 'true');
          body.classList.remove('show-sidebar');
        }
      } catch (ignored) { /* Storage is optional. */ }
    }
    restoreSidebar();
    // Perfex handles toggling on the button; this delegated handler observes the result.
    $(document).on('click.smartchoiceTheme', '.hide-menu, .toggle-sidebar-button', function () {
      if (!desktopSidebar()) return;
      try {
        localStorage.setItem('smartchoice_sidebar_collapsed', String(body.classList.contains('hide-sidebar')));
      } catch (ignored) { /* Storage is optional. */ }
    });
    // Restore desktop preference after Perfex updates its responsive body classes.
    var desktop = window.innerWidth >= 769;
    $(window).on('resize.smartchoiceTheme', function () {
      var nextDesktop = window.innerWidth >= 769;
      if (nextDesktop && !desktop) window.setTimeout(restoreSidebar, 0);
      desktop = nextDesktop;
    });
  });
})(jQuery);

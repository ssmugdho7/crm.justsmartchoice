(function($){
    'use strict';

    var SETTINGS_URL = (typeof admin_url !== 'undefined' ? admin_url : '/admin/') + 'settings?group=payroll_hub';
    var PANEL_URL = (typeof admin_url !== 'undefined' ? admin_url : '/admin/') + 'hr_payroll/payroll_hub_settings_panel';
    var names = ['payroll hub','payroll hub settings','centro de nómina','centro de nomina'];
    var busy = false;

    function normalizedText(el){
        return $.trim($(el).text()).replace(/\s+/g,' ').toLowerCase();
    }

    function isPayrollLabel(el){
        var t = normalizedText(el);
        if (names.indexOf(t) !== -1) return true;
        return t === 'payroll hub' || t.indexOf('payroll hub') === 0;
    }

    function candidates(){
        return $('#settings-group-list a, #settings-group-list li, .settings-group-list a, .settings-group-list li, .settings-sidebar a, .settings-sidebar li, #settings-menu a, #settings-menu li, .settings-nav a, .settings-nav li, .settings-tabs a, .settings-tabs li, .list-group a, .list-group-item, .nav-tabs a, [data-settings-group], [data-group]');
    }

    function markNavigation(){
        candidates().each(function(){
            var $el=$(this);
            if (!isPayrollLabel(this)) return;

            var $a = $el.is('a') ? $el : $el.find('a').first();
            if ($a.length) {
                $a.attr('href', SETTINGS_URL)
                  .attr('data-hrp-settings-link','1')
                  .css({'cursor':'pointer','pointer-events':'auto'});
                // If a customized CRM has put a non-clickable overlay on the LI,
                // make the containing item identifiable as well.
                $a.closest('li,.list-group-item').attr('data-hrp-settings-item','1');
            } else {
                $el.attr('data-hrp-settings-link','1')
                   .attr('role','link')
                   .attr('tabindex','0')
                   .css({'cursor':'pointer','pointer-events':'auto'});
            }
        });
    }

    function findRightPanel(){
        var selectors=[
            '#settings-group-content', '.settings-group-content', '#settings-content', '.settings-content',
            '.settings-right-panel', '.settings-right-content', '.settings-main-content', '.settings-tab-content',
            '#settings .tab-content', '.settings-page .tab-content'
        ];
        for (var i=0;i<selectors.length;i++) {
            var $x=$(selectors[i]).filter(':visible').first();
            if ($x.length && !$x.is('#settings-group-list,.settings-group-list,.settings-sidebar,#settings-menu,.settings-nav,.settings-tabs')) return $x;
        }

        // Smart Choice CRM 4.x fallback: settings pages generally use a left
        // navigation column and a larger right column in the same row.
        var $nav = $('[data-hrp-settings-link="1"]').first();
        var $row = $nav.closest('.row');
        if ($row.length) {
            var $col = $row.children('.col-md-9,.col-md-10,.col-lg-9,.col-lg-10,.col-sm-9,.col-sm-10').filter(':visible').first();
            if ($col.length) return $col;
        }
        return $();
    }

    function activateNav(){
        var $link=$('[data-hrp-settings-link="1"]').first();
        $link.closest('li,.list-group-item').addClass('active');
    }

    function injectPanel(force){
        if (busy) return;
        if (!force && $('.hrp-native-settings-panel').length) {
            activateNav();
            return;
        }
        var $target=findRightPanel();
        if (!$target.length) return;
        busy=true;
        $target.css('min-height','320px').html('<div class="text-center ptop30 pbottom30"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mtop10">Loading Payroll Hub settings...</p></div>');
        $.get(PANEL_URL)
            .done(function(html){
                $target.html(html);
                activateNav();
                if (window.history && history.replaceState) history.replaceState({},'',SETTINGS_URL);
            })
            .fail(function(){
                window.location.href = SETTINGS_URL;
            })
            .always(function(){ busy=false; });
    }

    function openPayrollSettings(e){
        if (e) e.preventDefault();
        markNavigation();
        var $target=findRightPanel();
        if ($target.length) injectPanel(true);
        else window.location.href=SETTINGS_URL;
    }

    $(document).on('click','[data-hrp-settings-link="1"], [data-hrp-settings-item="1"]',openPayrollSettings);
    $(document).on('keydown','[data-hrp-settings-link="1"]',function(e){
        if (e.key==='Enter' || e.key===' ') openPayrollSettings(e);
    });

    $(function(){
        markNavigation();
        var onPayrollGroup = location.pathname.indexOf('/admin/settings') !== -1 && /(?:\?|&)group=payroll_hub(?:&|$)/.test(location.search);
        if (onPayrollGroup) {
            setTimeout(function(){
                markNavigation();
                if (!$('.hrp-native-settings-panel').length) injectPanel(true);
                else activateNav();
            },250);
        }

        // The custom CRM can rebuild Settings navigation after initial DOM ready.
        // Re-apply the bridge whenever that happens.
        if (window.MutationObserver) {
            var observer=new MutationObserver(function(){ markNavigation(); });
            observer.observe(document.body,{childList:true,subtree:true});
        }
        setTimeout(markNavigation,700);
        setTimeout(markNavigation,1500);
    });
})(jQuery);

(function($) {
    "use strict";

    $('body').addClass('perfex_office_theme_initiated smart-choice-office-theme-v149 smart-choice-safe-mode');
    $('.screen-options-area, #mobile-collapse').addClass('animated fadeIn');

    if ($('.quick-links').length && $('#header nav .navbar-nav').length) {
        $('.quick-links').prependTo('#header nav .navbar-nav').css('display', 'block');
    }

    if (typeof Waves !== 'undefined') {
        Waves.init();
        Waves.attach('.btn:not(.btn-default):not(.dt-button):not([data-toggle])', ['waves-effect', 'waves-light', 'waves-ripple']);
    }

    if (typeof Chart !== 'undefined' && Chart.defaults && Chart.defaults.global) {
        Chart.defaults.global.defaultFontColor = '#111111';
    }

    if (typeof Nanobar !== 'undefined') {
        var nanobar = new Nanobar({ target: document.getElementsByTagName('BODY')[0] });
        if (document.readyState === 'loading') {
            nanobar.go(30); nanobar.go(76); nanobar.go(100);
        }
    }

    $(document).on('click', 'a[href*="/admin/settings"], a[href*="/admin/setup"], a[href="#setup-menu"], .open-customizer, .settings-group a', function() {
        setTimeout(function() {
            $('#side-menu, #setup-menu, .admin-sidebar, .sidebar, .left-sidebar').each(function() {
                try { this.scrollTop = 0; } catch(e) {}
            });
        }, 80);
    });

    function compactTables() {
        $('table.table, table.dataTable').each(function() {
            var $table = $(this);
            $table.removeClass('smart-choice-resizable-table smart-choice-resizable-table-v148 sc-widths-locked')
                .addClass('smart-choice-compact-table');
            $table.find('.sc-col-resizer, .sc-col-resizer-v148').remove();
            $table.closest('.dataTables_wrapper,.table-responsive,.panel-body').find('> .sc-table-lockbar').remove();
        });
    }

    $(document).on('draw.dt ajaxComplete shown.bs.tab shown.bs.modal', function(){
        setTimeout(compactTables, 50);
        setTimeout(compactTables, 300);
    });
    $(compactTables);
    if (window.MutationObserver) {
        new MutationObserver(function(){ setTimeout(compactTables, 80); }).observe(document.documentElement, {childList:true, subtree:true});
    }
})(jQuery);

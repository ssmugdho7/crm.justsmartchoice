(function($) {
    "use strict";

    // Smart Choice Office Theme v1.3.8 safe mode.
    // This file must never remove normal CRM content, dashboard widgets, tables, panels, or landing page records.

    var slowFadeIn = 'animated fadeIn';
    $('body').addClass('perfex_office_theme_initiated smart-choice-office-theme-v138 smart-choice-safe-mode');
    $('.screen-options-area').addClass(slowFadeIn);
    $('#mobile-collapse').addClass(slowFadeIn);

    if ($('.quick-links').length && $('#header nav .navbar-nav').length) {
        $('.quick-links').prependTo('#header nav .navbar-nav').css('display', 'block');
    }

    $('li:has(a.close-customizer)').css({
        'background': '#169179',
        'height': '54px'
    });

    if (typeof Waves !== 'undefined') {
        Waves.init();
        Waves.attach('.btn:not(.btn-default):not(.dt-button):not([data-toggle])', ['waves-effect', 'waves-light', 'waves-ripple']);
    }

    if (typeof Chart !== 'undefined' && Chart.defaults && Chart.defaults.global) {
        Chart.defaults.global.defaultFontColor = '#263238';
    }

    if (typeof Nanobar !== 'undefined') {
        var options = { target: document.getElementsByTagName('BODY')[0] };
        var nanobar = new Nanobar(options);
        if (document.readyState === 'loading') {
            nanobar.go(30);
            nanobar.go(76);
            nanobar.go(100);
        }
    }

    // When switching from the long main menu to Setup, keep the menu readable by returning the sidebar scroll to the top.
    $(document).on('click', 'a[href*="/admin/settings"], a[href*="/admin/setup"], a[href="#setup-menu"], .open-customizer, .settings-group a', function() {
        setTimeout(function() {
            $('#side-menu, #setup-menu, .admin-sidebar, .sidebar, .left-sidebar').each(function() {
                try { this.scrollTop = 0; } catch(e) {}
            });
        }, 80);
    });

})(jQuery);

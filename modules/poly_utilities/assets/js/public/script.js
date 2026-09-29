(function ($) {
    "use strict";
    PolyOperationFunctions.Init();
    //Scroll to top
    if (typeof poly_utilities_settings !== 'undefined') {

        // Roles to top;
        if(poly_utilities_settings.is_scroll_to_top === 'true'){
            PolyOperationFunctions.ScrollToTop(['.poly-scroll-to-top']);
        }

        // Widget areas
        if (poly_utilities_settings.widgets) {
            PolyOperationFunctions.Widgets('user');
        }

        // AIO Supports
        if (poly_utilities_settings.aio_supports && poly_utilities_settings.aio_supports.is_clients === 'true') {
            let supports = JSON.parse(poly_utilities_settings.aio_supports.supports ?? '[]');
            PolyOperationFunctions.SupportLine(supports);
        }
    }

})(jQuery);
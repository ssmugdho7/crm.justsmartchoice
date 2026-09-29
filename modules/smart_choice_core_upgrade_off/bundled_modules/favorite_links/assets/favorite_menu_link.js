(function($) {
    "use strict";

    function placeFavoriteLinksMenu() {
        var content = $('#perfex_menu_link');
        if (!content.length) {
            return;
        }

        var html = content.html();
        content.remove();

        var search = $('#top_search');
        var header = $('#header').find('ul.nav.navbar-nav.visible-md.visible-lg').first();

        if (search.length) {
            search.before(html);
        } else if (header.length) {
            header.append(html);
        } else {
            $('body').prepend(html);
        }
    }

    $(document).ready(function() {
        placeFavoriteLinksMenu();
    });
})(jQuery);

function perfex_menu_link_menu_modal(menu_id) {
    if (typeof requestGet !== 'function') {
        alert_float('danger', 'Favorite Links cannot open because the CRM request helper is not loaded.');
        return;
    }

    requestGet('favorite_links/menu_detail/' + menu_id).done(function(response) {
        $('#perfex_menu_link_modal').html(response).promise().done(function() {
            if (typeof init_selectpicker === 'function') {
                init_selectpicker();
            }
        });

        $('#perfex_menu_link_modal').modal({
            show: true,
            backdrop: 'static',
            keyboard: false
        });
    }).fail(function(data) {
        alert_float('danger', data.responseText || 'Favorite Links could not open the link editor.');
    });
}

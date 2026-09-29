(function($) {
    "use strict";
    PolyOperationFunctions.DisableReload();
    //Toggle
    $('.toggle-menu-options').on('click', function(e) {
        "use strict";
        e.preventDefault();
        let menu_id = $(this).parents('li').data('id');
        if ($(this).hasClass('main-item-options')) {
            $(this).parents('li').find('.main-item-options[data-menu-options="' + menu_id + '"]')
                .slideToggle();
        } else {
            $(this).parents('li').find('.sub-item-options[data-menu-options="' + menu_id + '"]')
                .slideToggle();
        }
    });

    //Remove
    $('.poly-resource-delete').on('click', function() {
        "use strict";
        var data = {};
        data.id = $(this).data('id');
        data.resource = 'css';

        Swal.fire({
            title: poly_utilities_settings.popup_delete.header,
            text: poly_utilities_settings.popup_delete.message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: poly_utilities_settings.popup_delete.cancel,
            confirmButtonText: poly_utilities_settings.popup_delete.confirm,
            allowOutsideClick: false,
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(admin_url + 'poly_utilities/delete_resource', data).done(function(response) {
                    let dataResponse = JSON.parse(response);
                    dataResponse.title = "Alert";
                    PolyPopup.popup(dataResponse, true);
                });

            }
        });
    });
})(jQuery);
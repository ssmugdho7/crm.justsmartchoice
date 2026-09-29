(function ($) {
    "use strict";
    PolyOperationFunctions.DisableReload();

    PolyOperationFunctions.Icons($('.poly-utilities-aio-icon-select'));

    $('#add-field').on('click', function () {
        "use strict";
        var $template = $('.poly-fields-template .poly-field-template').clone().addClass('active');

        let itemID = PolyCommon.generateUniqueID();
        var iconSelect = $template.find('.poly-utilities-aio-icon-select');
        var deleteElement = $template.find('.poly-aio-handle-delete');

        $template.attr('id', itemID);
        deleteElement.attr('data-id', itemID);
        iconSelect.attr('data-id', itemID);

        $('#repeater-fields').append($template).promise().done(function () {
            appColorPicker();
            PolyOperationFunctions.Icons($('.poly-utilities-aio-icon-select'));
        });
    });

    $('#repeater-fields').on('click', '.poly-aio-handle-delete', function () {
        "use strict";
        Swal.fire({
            title: poly_utilities_settings.popup_delete.header,
            text: poly_utilities_settings.popup_delete.message,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: poly_utilities_settings.popup_delete.confirm_color,
            cancelButtonColor: poly_utilities_settings.popup_delete.cancel_color,
            cancelButtonText: poly_utilities_settings.popup_delete.cancel,
            confirmButtonText: poly_utilities_settings.popup_delete.confirm,
            allowOutsideClick: false,
        }).then((result) => {
            if (result.isConfirmed) {
                $(`#${$(this).data('id')}`).remove();
                updateDataFields();
            }
        });
    });

    Sortable.create($('#repeater-fields').get(0), {
        handle: '.poly-aio-handle-sortable',
        selectedClass: 'selected',
        animation: 150,
        onEnd: function (evt) { }
    });

    $('.poly_aio_is_supports').on('click', function () {
        "use strict";
        updateDataFields();
    });

    $('.btn-submit-poly-aio-supports').on('click', function () {
        "use strict";
        updateDataFields();
    });

    function updateDataFields() {
        var fields = [];
        $('.poly-field-template.active').each(function () {
            let iconContent = $(this).find('.poly_aio_supports_icon').val();
            var type = $(this).find('.poly_aio_supports_types').val();
            var icon = iconContent;
            var icon_color = $(this).find('.poly_aio_supports_icon_color').val();
            var title = $(this).find('.poly_aio_supports_title').val();
            var content = $(this).find('.poly_aio_supports_content').val();
            fields.push({ type: type, icon: icon, icon_color: icon_color, title: title, content: content });
        });

        var dataToSave = {
            is_admin: $('#poly_aio_support_is_admin').prop('checked'),
            is_clients: $('#poly_aio_support_is_clients').prop('checked'),
            is_messages: $('#poly_aio_is_supports_messages').prop('checked'),
            is_messages_mobile: $('#poly_aio_is_supports_messages_mobile').prop('checked'),
            icon_button: $('.poly_aio_supports_icon_button').val(),
            icon_button_color: $('.poly_aio_supports_icon_button_color').val(),
            messages: $('#poly_aio_supports_messages').val()
                .split('\n')
                .map(line => line.trim())
                .filter(Boolean),
            supports: JSON.stringify(fields),
        };

        var form = $('.poly_aio_supports-form');
        if (form.valid()) {
            $.post(admin_url + 'poly_utilities/save_aio_supports', {
                data: dataToSave,
            }).done(function (response) {
                let dataResponse = JSON.parse(response);
                dataResponse.title = "Alert";
                PolyMessage.displayNotification('success', dataResponse.message);
            });
        }
    }
})(jQuery);
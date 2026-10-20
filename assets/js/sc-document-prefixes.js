(function ($) {
    'use strict';
    function save(button, editor, input) {
        if (button.disabled) return;
        var prefix = input.val();
        var feedback = editor.find('.sc-prefix-feedback');
        button.disabled = true;
        var data = {prefix: prefix};
        if (window.csrfData) data[window.csrfData.token_name] = window.csrfData.hash;
        $.ajax({url: editor.data('url'), type: 'POST', data: data, dataType: 'json'})
            .done(function (response) {
                feedback.text(response.message || '').toggleClass('text-danger', !response.success);
                if (!response.success) return;
                // Plain text updates preserve the surrounding form, status badges and unsaved edits.
                var oldNumber = $('[data-sc-document-number]').first().text();
                $('#prefix').text(response.prefix);
                input.val(response.prefix);
                $('[data-sc-document-number]').text(response.formatted_number);
                $('[data-sc-document-heading]').text(response.heading);
                if (oldNumber) {
                    var current = $('.sc-sales-breadcrumbs__current');
                    current.text(current.text().replace(oldNumber, response.formatted_number));
                    document.title = document.title.replace(oldNumber, response.formatted_number);
                }
                editor.prop('open', false);
                $(document).trigger('sc:document-prefix-updated', [response]);
            })
            .fail(function (xhr) {
                feedback.text(xhr.status === 403 ? 'Access denied. Refresh the page and try again.' : 'Could not save the prefix. Please try again.').addClass('text-danger');
            })
            .always(function () { button.disabled = false; });
    }
    $(document).on('click.scPrefix', '.sc-save-prefix', function () {
        var editor = $(this).closest('.sc-prefix-editor');
        save(this, editor, editor.find('.sc-prefix-value'));
    });
    $(document).on('keydown.scPrefix', '.sc-prefix-value', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            $(this).closest('.sc-prefix-editor').find('.sc-save-prefix').trigger('click');
        }
    });
})(jQuery);

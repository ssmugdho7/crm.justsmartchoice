(function($) {
    "use strict";
    PolyOperationFunctions.DisableReload();
    const editor = CodeMirror.fromTextArea(document.getElementById('poly_utilities_resource_content'), {
        mode: 'javascript',
        theme: 'default',
        lineNumbers: true,
        autofocus: true,
        matchBrackets: true,
        lineWrapping: true,
        autoCloseBrackets: true,
        extraKeys: {
            "Ctrl-Space": "autocomplete"
        },
    });

    //Validation
    var vRules = {
        poly_utilities_resource_name: 'required',
        poly_utilities_file_name: 'required',
    }
    appValidateForm($('form'), vRules);

    //Add Scripts
    $('.btn-submit-poly-utilities-add-resource').on('click', function() {
        "use strict";
        if ($('form').valid()) {

            var content = editor.getValue();

            var is_admin_area = $('#poly_utilities_resource_is_admin').prop('checked');
            var is_customers_area = $('#poly_utilities_resource_is_customers').prop('checked');
            var is_embed = $('#poly_utilities_is_embed').prop('checked');
            var is_embed_position = $('#poly_utilities_is_embed_position').val();

            var data = {
                title: $('#poly_utilities_resource_name').val(),
                file: $('#poly_utilities_file_name').val(),
                mode: `${is_admin_area}${is_customers_area}`,
                is_embed: `${is_embed}`,
                is_embed_position: is_embed_position??'footer',
                content: content,
                state: $(this).data('state'),
                resource: 'js'
            };
            
            $.post(admin_url + 'poly_utilities/save_resource', data).done(function(response) {
                let dataResponse = JSON.parse(response);
                dataResponse.title = "Alert";
                if (!data.state) {
                    dataResponse.link_redirect = admin_url + 'poly_utilities/scripts_add?id=' + data.file;
                    PolyPopup.popup(dataResponse, false);
                }else{
                    PolyMessage.displayNotification(dataResponse.status, dataResponse.message);
                }
            });
        }
    });
})(jQuery);
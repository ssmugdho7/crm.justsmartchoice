
$(function(){
	"use strict"; // Start of use strict
    console.log("ok");
    $(document).on('click', '.btn_install_popup', function() {
        let code = $(this).data('code');
        if (!code) {
            alert('not found code');
            return false;
        }
    
        requestGet('perfex_popup/popups/install_script/' + code).done(function(response) {
            
            let res = JSON.parse(response);
            $('#popup_key_html').text(res.success);
            $('#modalInstallPopup').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
    
        }).fail(function(data) {
            alert_float('danger', data.responseText);
        }).always(function() {});
    
    });

});

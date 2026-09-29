$(function() {
    "use strict";
    
    var CustomersServerParams = {};
    $.each($('._hidden_inputs._filters input'), function() {
        CustomersServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
    });

    initDataTable('.table-popup-subscribers', window.location.href, [3], [3], CustomersServerParams);
    $('.btn-default-dt-options:first').remove();

   



    $(document).on('click', '.btn-convert-data-to-lead', function() {
        var id = $(this).attr('data-id');
        if (!id) {
            alert('not found id');
            return false;
        }
        requestGet('perfex_popup/popups/get_convert_data_to_lead/' + id).done(function(response) {
            $('#data_to_lead').html(response);
            $('#modal_data_to_lead').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
        }).fail(function(data) {
            alert_float('danger', data.responseText);
        }).always(function() {});

    });


    $(document).on('click', '.btn-convert-data-to-customer', function() {
        var id = $(this).attr('data-id');
        if (!id) {
            alert('not found id');
            return false;
        }
        requestGet('perfex_popup/popups/get_convert_data_to_customer/' + id).done(function(response) {
            $('#lead_to_customer').html(response);
            $('#convert_data_to_customer_modal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
        }).fail(function(data) {
            alert_float('danger', data.responseText);
        }).always(function() {});

    });
});
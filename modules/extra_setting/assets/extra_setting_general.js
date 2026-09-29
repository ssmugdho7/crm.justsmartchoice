
(function($) {
    "use strict";

    $(document).ready(function (){


        if ( typeof es_enable_web_whatsapp_link !== 'undefined' && es_enable_web_whatsapp_link == 1 )
        {

            es_search_phone_fields_for_redirect_whatsapp();

            // any table loaded
            $(document).on('init.dt', function (e, settings) {

                es_search_phone_fields_for_redirect_whatsapp();

            })


            // any table reload
            $(document).on('draw.dt', function () {

                es_search_phone_fields_for_redirect_whatsapp();

            });

            // model show action
            $("body").on('show.bs.modal', '.modal', function (event) {

                es_search_phone_fields_for_redirect_whatsapp();

            });

        }


    });


})(jQuery);



function es_search_phone_fields_for_redirect_whatsapp()
{

    var wa_me_icon_path = site_url+"modules/extra_setting/assets/whatsapp-icon.png";

    var wa_me_whatsapp_img = '<img class="wa_me_whatsapp_img" width="20" height="20" src="'+wa_me_icon_path+'" alt="WebWhatsApp" />';

    // Phones in tag <a> are checked

    $('a[href^="tel:"]').each(function() {

        var wa_me_phone_number = $(this).attr('href').replace(/\D/g, '');

        if ( wa_me_phone_number.length > 0 )
        {

            $(this).before( '<a href="https://wa.me/' + wa_me_phone_number +'" target="_blank">'+wa_me_whatsapp_img+'</a>' );

            $(this).attr('target', '_blank');

            $(this).attr('href', 'https://wa.me/' + wa_me_phone_number);

        }

    });


    // Checking the phone numbers in the input

    $('.wa_me_whatsapp_label_href').remove();

    $('label[for="phonenumber"]').each(function() {

        var wa_me_phone_number = $(this).parent('div').find('input[name="phonenumber"]').val().replace(/\D/g, '');

        if ( wa_me_phone_number.length > 0 )
        {

            $(this).before( '<a class="wa_me_whatsapp_label_href" href="https://wa.me/'+wa_me_phone_number+'" target="_blank">'+wa_me_whatsapp_img+'</a>' );

        }

    });



}



function es_calculate_target_currency_properties()
{

    var es_total_previous_value = parseFloat( $('input[name="total"]').val() );

    setInterval(function() {

        var es_total_current_value = parseFloat( $('input[name="total"]').val() );

        if ( es_total_previous_value !== es_total_current_value )
        {
            es_total_previous_value = es_total_current_value;

            es_calculate_target_currency();
        }

    }, 250 );


    $('#es_target_exchange_rate').on('change',function (){

        es_calculate_target_currency();

    })

    $('#es_target_exchange_rate').on('blur',function (){

        es_calculate_target_currency();

    })

    init_selectpicker();


}

function es_calculate_target_currency()
{

    if( $('#es_target_currency').val() > 0 )
    {

        var es_inv_total_amount = parseFloat( $('input[name="total"]').val() );

        var es_target_exchange_rate = parseFloat( $('#es_target_exchange_rate').val() );

        var es_target_currency = $('#es_target_currency option:selected').data('subtext');

        if( !isNaN( es_inv_total_amount ) && !isNaN( es_target_exchange_rate ) && es_target_exchange_rate !== 0 )
        {

            var es_target_currency_amount = es_inv_total_amount * es_target_exchange_rate ;

            $('#es_target_currency_amount').val(es_target_currency_amount);

            $('.es_target_currency_amount').text( format_money( es_target_currency_amount , ' ' ) + " "+es_target_currency );

        }
        else
        {

            $('#es_target_currency_amount').val(0);

            $('.es_target_currency_amount').text( "0 "+es_target_currency );

        }

    }

}

<?php

$lang_text = 'es_phone_number';
$lang_required_text = 'es_client_phone_number_required';
$lang_input_type = 'text';

$opt_val = extra_setting_get_option_value( 'es_enable_contact_login_using_otp_type' , 'phone' );

if ( $opt_val == 'email' )
{

    $lang_input_type = 'email';
    $lang_text = 'es_client_email';
    $lang_required_text = 'es_client_email_required';

}

?>


<div class="hide" id="es_login_content">

    <div class="form-group es_phone_field">

        <label for="es_phone_number"><?php echo _l($lang_text); ?></label>

        <input type="<?php echo $lang_input_type?>" autofocus="true" class="form-control" name="es_phone_number" id="es_phone_number">

    </div>

    <div class="form-group es_phone_field">

        <a class="btn btn-primary btn-block" onclick="check_phone_number()">

            <?php echo _l('es_send_otp'); ?>

        </a>

    </div>

    <div class="form-group es_phone_field">

        <p class="text-danger bold" id="es_phone_number_error_message"></p>

    </div>

    <div class="form-group es_otp_field hide">

        <label for="es_otp_code"><?php echo _l('es_enter_otp'); ?></label>

        <input type="text" autofocus="true" class="form-control" name="es_otp_code" id="es_otp_code">

    </div>

    <div class="form-group es_otp_field hide">

        <a class="btn btn-primary btn-block" onclick="check_otp_code()">

            <?php echo _l('es_btn_verify_otp'); ?>

        </a>

    </div>

    <div class="form-group es_otp_field hide">

        <a class="btn btn-primary btn-block" id="es_send_button_again" onclick="send_code_again()">

            <span id="es_countdown">60</span> <?php echo _l('es_btn_send_sms_again'); ?>

        </a>

    </div>

</div>


<script>

    document.addEventListener("DOMContentLoaded", function() {

        $(document).ready(function (){

            if ( $(".login-form").length > 0 )
            {

                var html_content = $('#es_login_content').html();

                $('#es_login_content').remove();

                 $(".login-form").find('.panel-body').html( html_content ).promise().done(function (){
                     $('#es_login_content').remove();
                     $('#es_phone_number').focus();

                 });

            }

        })

    })

    var es_countdown = 60;
    var es_timer;


    function check_phone_number()
    {

        $('#es_phone_number_error_message').text('');

        var phone_number = $('#es_phone_number').val();

        if ( phone_number )
        {
            $.post(site_url+"extra_setting/clientside/check_data" , { phone_number : phone_number }).done(function ( response ){

                response = JSON.parse(response);

                if ( response.success )
                {

                    es_countdown = 60;

                    es_start_countdown();

                    $('.es_phone_field').addClass('hide');

                    $('.es_otp_field').removeClass('hide');

                    alert_float('success',"<?php echo _l('es_code_send_success')?>");

                }
                else
                {

                    alert_float('danger',response.message);

                    $('#es_phone_number_error_message').text( response.message );

                }

            });
        }
        else
        {

            alert_float('danger',"<?php echo _l($lang_required_text)?>");

            $('#es_phone_number_error_message').text("<?php echo _l($lang_required_text)?>");
            $('#es_phone_number').focus();

        }


    }

    function es_start_countdown()
    {

        $('#es_send_button_again').addClass('disabled');

        $('#es_countdown').text(es_countdown);

        es_timer = setInterval(function() {

            es_countdown--;

            $('#es_countdown').text(es_countdown);

            if (es_countdown <= 0)
            {

                clearInterval(es_timer);

                $('#es_send_button_again').removeClass('disabled');

                $('#es_countdown').text('');

            }

        }, 1000);

    }


    function send_code_again()
    {

        var phone_number = $('#es_phone_number').val();

        if ( phone_number )
        {
            $.post(site_url+"extra_setting/clientside/send_code_again" , { phone_number : phone_number }).done(function ( response ){

                response = JSON.parse(response);

                if ( response.success )
                {

                    es_countdown = 60;

                    es_start_countdown();

                    alert_float('success',"<?php echo _l('es_code_send_success')?>");

                }
                else
                {

                    alert_float('danger',response.message);

                }

            });
        }

    }


    function check_otp_code()
    {

        var es_otp_code = $('#es_otp_code').val();

        if ( es_otp_code )
        {

            $.post(site_url+"extra_setting/clientside/check_otp_code" , { otp_code : es_otp_code }).done(function ( response ){

                response = JSON.parse(response);

                if ( response.success )
                {

                    window.location.href = site_url;

                }
                else
                {

                    alert_float('danger',response.message);

                }

            });

        }
        else
        {

            alert_float('danger',"Code empty");

            $('#es_otp_code').focus();

        }


    }

</script>

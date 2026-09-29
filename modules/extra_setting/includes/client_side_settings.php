<?php

hooks()->add_filter('clients_login_form_start', function (){

    $option = extra_setting_get_option_value( 'es_enable_contact_login_using_phone' );

    if ( !empty( $option ) )
        require_once __DIR__ . '/client_login.php';

});



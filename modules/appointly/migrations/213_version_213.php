<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_213 extends App_module_migration
{
    public function up()
    {
        if (get_option('appointly_public_booking_slug') === '') {
            add_option('appointly_public_booking_slug', 'booking-link');
        }
        if (get_option('appointly_public_form_hide_logo') === '') {
            add_option('appointly_public_form_hide_logo', '0');
        }
        if (get_option('appointly_public_form_logo_width') === '') {
            add_option('appointly_public_form_logo_width', '180');
        }
        if (get_option('appointly_public_form_logo_height') === '') {
            add_option('appointly_public_form_logo_height', 'auto');
        }
    }
}

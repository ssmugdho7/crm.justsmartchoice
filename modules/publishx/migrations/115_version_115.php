<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_115 extends App_module_migration
{
    public function up()
    {
        update_option('publishx_module_version', '1.1.5');
        update_option(
            'publishx_appointment_url',
            'https://crm.justsmartchoice.com/appointly/appointments_public/book?col=col-md-8+col-md-offset-2'
        );
    }

    public function down()
    {
        // Published pages and settings are preserved intentionally.
    }
}

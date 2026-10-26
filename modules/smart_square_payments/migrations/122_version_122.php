<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_122 extends App_module_migration
{
    public function up()
    {
        add_option('paymentmethod_smart_square_currencies', 'USD');
        if (trim((string) get_option('paymentmethod_smart_square_currencies')) === '') {
            update_option('paymentmethod_smart_square_currencies', 'USD');
        }

        update_option('smart_square_schema_version', '1.2.2');
    }

    public function down()
    {
        // Preserve payment eligibility, financial records, and credentials.
    }
}

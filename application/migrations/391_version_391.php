<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_391 extends App_migration
{
    public function up()
    {
        add_option('smart_choice_contract_initials_scale', '100');
        add_option('smart_choice_sales_default_validity_months', '3');
        update_option('smart_choice_crm_build', '3.9.1');
        update_option('smart_choice_crm_current_version', '3.9.1');
    }

    public function down()
    {
    }
}

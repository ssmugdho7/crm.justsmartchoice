<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_388 extends App_migration
{
    public function up()
    {
        update_option('smart_choice_crm_build', '3.8.8');
        update_option('smart_choice_core_upgrade_applied', '388');
        update_option('smart_choice_sales_isolated_input_fix', '1');
    }

    public function down()
    {
        update_option('smart_choice_crm_build', '3.8.7');
        delete_option('smart_choice_sales_isolated_input_fix');
    }
}

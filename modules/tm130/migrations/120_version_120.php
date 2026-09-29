<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_120 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (function_exists('add_option')) {
            add_option('training_manual_last_safe_upgrade', '1.2.0');
        }

        if (function_exists('update_option')) {
            update_option('training_manual_last_safe_upgrade', '1.2.0');
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}

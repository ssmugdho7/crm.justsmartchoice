<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_390 extends App_migration
{
    public function up()
    {
        if (function_exists('update_option')) {
            update_option('smart_choice_crm_build', '3.9.0');
            update_option('smart_choice_crm_current_version', '3.9.0');
        }
    }

    public function down()
    {
        // Upgrade-only migration. Data and working features are intentionally preserved.
    }
}

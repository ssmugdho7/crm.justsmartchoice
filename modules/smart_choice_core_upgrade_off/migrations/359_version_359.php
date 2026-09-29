<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_359 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_enterprise_current_version', '3.5.9');
        update_option('smart_choice_enterprise_latest_version', '3.5.9');
        update_option('smart_choice_core_latest_version', '3.5.9');
        update_option('smart_choice_latest_version', '3.5.9');
        update_option('latest_version', '3.5.9');
        update_option('smart_choice_core_upgrade_last_patch', '3.5.9');
        update_option('smart_choice_core_upgrade_settings_registration', 'app_add_settings_section');
    }

    public function down()
    {
        return true;
    }
}

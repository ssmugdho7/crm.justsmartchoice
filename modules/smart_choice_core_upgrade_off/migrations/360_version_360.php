<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_360 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_core_upgrade_version', '3.6.0');
        update_option('smart_choice_enterprise_current_version', '3.6.0');
        update_option('smart_choice_enterprise_latest_version', '3.6.0');
        update_option('smart_choice_core_latest_version', '3.6.0');
        update_option('smart_choice_latest_version', '3.6.0');
        update_option('smart_choice_core_upgrade_last_patch', '3.6.0');
        update_option('smart_choice_core_safe_mode', '1');
        update_option('smart_choice_core_table_tools', '0');
        update_option('smart_choice_office_theme_enabled', '0');
        update_option('smart_choice_core_dedupe_selects', '0');
        update_option('smart_choice_core_upgrade_settings_registration', 'app_add_settings_section');
    }

    public function down()
    {
        return true;
    }
}

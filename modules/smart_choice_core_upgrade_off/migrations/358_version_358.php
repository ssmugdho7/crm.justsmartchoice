<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_358 extends App_module_migration
{
    public function up()
    {
        update_option('smart_choice_core_upgrade_enabled', get_option('smart_choice_core_upgrade_enabled') === '' ? '1' : get_option('smart_choice_core_upgrade_enabled'));
        update_option('smart_choice_core_theme_name', get_option('smart_choice_core_theme_name') ?: 'Smart Choice');
        update_option('smart_choice_enterprise_current_version', '3.5.8');
        update_option('smart_choice_enterprise_latest_version', '3.5.8');
        update_option('smart_choice_core_latest_version', '3.5.8');
        update_option('smart_choice_latest_version', '3.5.8');
        update_option('latest_version', '3.5.8');
        update_option('smart_choice_core_upgrade_last_patch', '3.5.8');
    }

    public function down()
    {
        return true;
    }
}

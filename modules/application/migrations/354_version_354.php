<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_354 extends CI_Migration
{
    public function up()
    {
        $version = '3.5.4';
        update_option('smart_choice_enterprise_current_version', $version);
        update_option('smart_choice_enterprise_latest_version', $version);
        update_option('smart_choice_core_current_version', $version);
        update_option('smart_choice_core_latest_version', $version);
        update_option('smart_choice_latest_version', $version);
        update_option('latest_version', $version);
        update_option('smart_choice_modules_page_fast_mode', '1');
        update_option('smart_choice_disable_external_module_checks', '1');
        update_option('smart_choice_hide_broken_settings_sections', '1');
    }

    public function down() {}
}

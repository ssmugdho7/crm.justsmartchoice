<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_353 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_enterprise_current_version', '3.5.3');
        update_option('smart_choice_enterprise_latest_version', '3.5.3');
        update_option('smart_choice_core_current_version', '3.5.3');
        update_option('smart_choice_core_latest_version', '3.5.3');
        update_option('latest_version', '3.5.3');
        update_option('smart_choice_modules_page_fast_mode', '1');
    }

    public function down() {}
}

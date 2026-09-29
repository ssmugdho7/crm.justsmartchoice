<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_347 extends CI_Migration
{
    public function up()
    {
        if (function_exists('update_option')) {
            update_option('smart_choice_enterprise_current_version', '3.4.7');
            update_option('smart_choice_enterprise_latest_version', '3.4.7');
            update_option('smart_choice_core_upgrade_version', '3.4.7');
        }
    }

    public function down() {}
}

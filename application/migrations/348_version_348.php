<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_348 extends CI_Migration
{
    public function up()
    {
        $CI =& get_instance();

        if (function_exists('update_option')) {
            update_option('smart_choice_enterprise_current_version', '3.4.8');
            update_option('smart_choice_enterprise_latest_version', '3.4.8');
            update_option('smart_choice_crm_latest_version', '3.4.8');
        }
    }

    public function down()
    {
        return true;
    }
}

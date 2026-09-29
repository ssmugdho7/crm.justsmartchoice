<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_343 extends App_Migration
{
    public function up()
    {
        if (function_exists('update_option')) {
            update_option('smart_choice_core_display_version', '3.4.5');
            update_option('smart_choice_enterprise_current_version', '3.4.5');
        }
    }

    public function down() {}
}

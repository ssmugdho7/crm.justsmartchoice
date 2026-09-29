<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_256 extends App_module_migration
{
    public function up()
    {
        if (function_exists('update_option')) {
            update_option('smart_choice_core_upgrade_version', '3.1.2');
            update_option('smart_choice_enterprise_current_version', '3.1.2');
        }
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_103 extends App_module_migration
{
    public function up()
    {
        require_once(module_dir_path('smart_installer_tracking') . 'install.php');
        if (function_exists('update_option')) {
            update_option('smart_installer_tracking_module_version', '1.0.3');
        }
        return true;
    }

    public function down()
    {
        if (function_exists('update_option')) {
            update_option('smart_installer_tracking_enabled', '0');
        }
        return true;
    }
}

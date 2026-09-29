<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up(): void
    {
        require_once(module_dir_path('smart_installer_tracking') . 'install.php');
        update_option('smart_installer_tracking_module_version', '1.0.3');
    }

    public function down(): void
    {
        update_option('smart_installer_tracking_enabled', '0');
    }
}

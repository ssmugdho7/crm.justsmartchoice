<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_101 extends App_module_migration
{
    public function up(): void
    {
        require_once module_dir_path('smart_daily_productivity') . 'install.php';
        update_option('smart_daily_productivity_version', '1.0.2');
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}

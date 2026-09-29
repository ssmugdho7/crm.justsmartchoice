<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_110 extends App_module_migration
{
    public function up()
    {
        require_once module_dir_path('sales_center', 'install.php');

        if (function_exists('update_option')) {
            update_option('sales_center_version', '1.1.0');
        }
    }

    public function down()
    {
        // Safe rollback: no destructive changes. Data remains preserved.
    }
}

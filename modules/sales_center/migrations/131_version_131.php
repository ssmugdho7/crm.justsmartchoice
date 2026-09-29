<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_131 extends App_module_migration
{
    public function up()
    {

        if (!defined('SALES_CENTER_MODULE_NAME')) {
            define('SALES_CENTER_MODULE_NAME', 'sales_center');
        }
        if (!defined('SALES_CENTER_UPLOAD_FOLDER')) {
            define('SALES_CENTER_UPLOAD_FOLDER', FCPATH . 'uploads/sales_center/');
        }

        require_once module_dir_path('sales_center', 'install.php');

        update_option('sales_center_version', '1.3.1');
    }

    public function down()
    {
        // No destructive rollback. Sales Hub data remains preserved.
    }
}

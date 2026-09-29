<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_133 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        if (!defined('SALES_CENTER_MODULE_NAME')) {
            define('SALES_CENTER_MODULE_NAME', 'sales_center');
        }
        if (!defined('SALES_CENTER_UPLOAD_FOLDER')) {
            define('SALES_CENTER_UPLOAD_FOLDER', FCPATH . 'uploads/sales_center/');
        }

        require_once module_dir_path('sales_center', 'install.php');

        if (function_exists('sales_center_install')) {
            sales_center_install();
        }

        update_option('sales_center_version', '1.3.3');
        update_option('sales_center_native_pdf_email_fix', '1');
    }
}

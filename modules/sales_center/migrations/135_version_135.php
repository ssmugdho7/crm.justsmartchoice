<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_135 extends App_module_migration
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

        update_option('sales_center_version', '1.3.5');
        update_option('sales_center_native_pdf_email_fix', '1');
        update_option('sales_center_integrated_core_sales', '1');
        update_option('sales_center_upgrade_rescue_135', date('Y-m-d H:i:s'));
    }

    public function down()
    {
        // Safe rollback only. No CRM sales data is removed.
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_143 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        add_option('acc_purchase_hub_installed', acc_accounting_hub_module_installed('purchasing_hub') ? '1' : '0');
        add_option('acc_sales_hub_installed', acc_accounting_hub_module_installed('sales_center') ? '1' : '0');
        add_option('acc_quickbooks_desktop_enterprise_enabled', '1');

        if (!$CI->db->table_exists(db_prefix() . 'acc_hub_integration_logs')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "acc_hub_integration_logs` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `source_module` VARCHAR(100) NOT NULL,
                `source_table` VARCHAR(191) NULL,
                `source_id` INT(11) NULL,
                `target_type` VARCHAR(100) NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'queued',
                `message` TEXT NULL,
                `datecreated` DATETIME NULL,
                `addedfrom` INT(11) NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}

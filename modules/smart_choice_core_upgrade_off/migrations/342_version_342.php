<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_342 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        update_option('smart_choice_core_upgrade_version', '3.4.2');
        update_option('smart_choice_enterprise_current_version', '3.4.2');
        update_option('smart_choice_enterprise_latest_version', '3.4.2');
        update_option('smart_choice_enterprise_update_status', 'current');
        update_option('default_timezone', 'America/New_York');
        update_option('dateformat', 'm/d/Y|m/d/Y');
        update_option('time_format', '12');
        update_option('active_language', 'english');

        if (!$CI->db->table_exists(db_prefix() . 'smart_choice_core_logs')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_choice_core_logs` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `level` VARCHAR(30) NOT NULL DEFAULT "info",
                `event_type` VARCHAR(100) NOT NULL,
                `message` TEXT NULL,
                `data` LONGTEXT NULL,
                `staff_id` INT(11) NULL,
                `datecreated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `event_type` (`event_type`),
                KEY `level` (`level`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        if (!$CI->db->table_exists(db_prefix() . 'smart_choice_upgrade_history')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_choice_upgrade_history` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `from_version` VARCHAR(50) NULL,
                `to_version` VARCHAR(50) NULL,
                `package_name` VARCHAR(255) NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT "staged",
                `message` TEXT NULL,
                `staff_id` INT(11) NULL,
                `datecreated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `datefinished` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        if (!$CI->db->table_exists(db_prefix() . 'smart_choice_reports')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_choice_reports` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `report_key` VARCHAR(100) NOT NULL,
                `report_name` VARCHAR(191) NOT NULL,
                `report_group` VARCHAR(100) NOT NULL DEFAULT "General",
                `description` TEXT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `datecreated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `report_key` (`report_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        if ($CI->db->table_exists(db_prefix() . 'smart_choice_reports')) {
            $reports = [
                ['sales_summary', 'Sales Summary', 'Sales', 'Invoices, estimates, proposals, payments, and credit notes.'],
                ['income_per_job', 'Income Per Job', 'Projects', 'Income by project and customer.'],
                ['expenses_per_job', 'Expenses Per Job', 'Projects', 'Expenses and accounts payable by project.'],
                ['profit_loss_per_job', 'Profit And Loss Per Job', 'Projects', 'Income minus expenses per project.'],
                ['quickbooks_desktop_export', 'QuickBooks Desktop Export', 'QuickBooks', 'IIF export/import workflow for QuickBooks Desktop Enterprise.'],
                ['purchase_orders', 'Purchase Orders', 'Purchasing', 'Purchase orders from Purchasing Hub when installed.'],
                ['accounts_payable', 'Accounts Payable', 'Accounting', 'Bills and payables from Accounting Hub.'],
            ];
            foreach ($reports as $report) {
                $exists = $CI->db->where('report_key', $report[0])->get(db_prefix() . 'smart_choice_reports')->row();
                $row = ['report_key' => $report[0], 'report_name' => $report[1], 'report_group' => $report[2], 'description' => $report[3], 'is_active' => 1];
                if ($exists) {
                    $CI->db->where('id', $exists->id)->update(db_prefix() . 'smart_choice_reports', $row);
                } else {
                    $CI->db->insert(db_prefix() . 'smart_choice_reports', $row);
                }
            }
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}

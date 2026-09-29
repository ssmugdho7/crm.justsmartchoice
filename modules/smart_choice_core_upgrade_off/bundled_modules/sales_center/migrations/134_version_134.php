<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_134 extends App_module_migration
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

        if (!$CI->db->table_exists(db_prefix() . 'sales_center_document_links')) {
            $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . "sales_center_document_links` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `salesperson_id` int(11) NOT NULL DEFAULT 0,
                `manager_id` int(11) NULL,
                `department_id` int(11) NULL,
                `rel_type` varchar(50) NOT NULL DEFAULT 'invoice',
                `rel_id` int(11) NOT NULL DEFAULT 0,
                `document_total` decimal(15,2) NOT NULL DEFAULT 0.00,
                `amount_collected` decimal(15,2) NOT NULL DEFAULT 0.00,
                `commission_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
                `commission_earned` decimal(15,2) NOT NULL DEFAULT 0.00,
                `commission_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
                `commission_owed` decimal(15,2) NOT NULL DEFAULT 0.00,
                `status` varchar(50) NOT NULL DEFAULT 'pending',
                `issue_notes` text NULL,
                `created_by` int(11) NOT NULL DEFAULT 0,
                `datecreated` datetime NOT NULL,
                `dateupdated` datetime NULL,
                PRIMARY KEY (`id`),
                KEY `salesperson_id` (`salesperson_id`),
                KEY `rel_type` (`rel_type`),
                KEY `rel_id` (`rel_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        $commissions = db_prefix() . 'sales_center_commissions';
        if ($CI->db->table_exists($commissions)) {
            $columns = [
                'proposal_id' => 'int(11) NULL',
                'estimate_id' => 'int(11) NULL',
                'credit_note_id' => 'int(11) NULL',
                'rel_type' => "varchar(50) NOT NULL DEFAULT 'invoice'",
                'rel_id' => 'int(11) NULL',
                'document_total' => 'decimal(15,2) NOT NULL DEFAULT 0.00',
                'issue_notes' => 'text NULL',
                'manager_id' => 'int(11) NULL',
                'department_id' => 'int(11) NULL',
            ];
            foreach ($columns as $column => $definition) {
                if (!$CI->db->field_exists($column, $commissions)) {
                    $CI->db->query('ALTER TABLE `' . $commissions . '` ADD `' . $column . '` ' . $definition);
                }
            }
        }

        update_option('sales_center_version', '1.3.4');
        update_option('sales_center_native_pdf_email_fix', '1');
        update_option('sales_center_upgrade_rescue_134', date('Y-m-d H:i:s'));
    }

    public function down()
    {
        // Safe rollback: no destructive changes. Data remains preserved.
    }
}

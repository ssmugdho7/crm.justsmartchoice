<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_160 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Run the module installer safely so legacy installs get missing base tables/columns.
        // The install.php file has been patched to use db_prefix() checks correctly.
        $install = module_dir_path('sales_agent', 'install.php');
        if (file_exists($install)) {
            require_once($install);
        }

        // Construction commission tables: no foreign keys by design to avoid MySQL errno 150/170 issues.
        if (!$CI->db->table_exists(db_prefix() . 'sa_commissions')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "sa_commissions` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `invoice_id` int(11) NULL DEFAULT NULL,
              `agent_id` int(11) NULL DEFAULT NULL,
              `staff_id` int(11) NULL DEFAULT NULL,
              `project_id` int(11) NULL DEFAULT NULL,
              `customer_id` int(11) NULL DEFAULT NULL,
              `invoice_total` decimal(15,2) NOT NULL DEFAULT '0.00',
              `base_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
              `commission_type` varchar(20) NOT NULL DEFAULT 'percentage',
              `commission_rate` decimal(15,4) NOT NULL DEFAULT '0.0000',
              `commission_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
              `expense_deduction` decimal(15,2) NOT NULL DEFAULT '0.00',
              `net_commission` decimal(15,2) NOT NULL DEFAULT '0.00',
              `status` varchar(30) NOT NULL DEFAULT 'pending',
              `payment_date` date NULL DEFAULT NULL,
              `payment_reference` varchar(191) NULL DEFAULT NULL,
              `notes` text NULL,
              `created_by` int(11) NULL DEFAULT NULL,
              `created_at` datetime NULL DEFAULT NULL,
              `updated_at` datetime NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `invoice_id` (`invoice_id`),
              KEY `agent_id` (`agent_id`),
              KEY `staff_id` (`staff_id`),
              KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        if (!$CI->db->table_exists(db_prefix() . 'sa_commission_expenses')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . "sa_commission_expenses` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `commission_id` int(11) NULL DEFAULT NULL,
              `expense_date` date NULL DEFAULT NULL,
              `category` varchar(100) NULL DEFAULT NULL,
              `description` text NULL,
              `amount` decimal(15,2) NOT NULL DEFAULT '0.00',
              `created_by` int(11) NULL DEFAULT NULL,
              `created_at` datetime NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `commission_id` (`commission_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        add_option('sa_default_commission_rate', '5');
        add_option('sa_commission_base', 'paid_invoice');
        add_option('sa_commission_hold_until_paid', '1');
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}

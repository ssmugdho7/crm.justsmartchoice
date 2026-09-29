<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_369 extends CI_Migration
{
    public function up()
    {
        foreach (['estimates', 'proposals'] as $entity) {
            $table = db_prefix() . $entity;
            if (!$this->db->table_exists($table)) {
                continue;
            }
            $columns = [
                'sc_contract_total' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
                'sc_down_payment_percent' => "DECIMAL(7,2) NOT NULL DEFAULT 100.00",
                'sc_remaining_balance' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
                'sc_discount_reason' => "VARCHAR(191) NULL",
            ];
            foreach ($columns as $name => $definition) {
                if (!$this->db->field_exists($name, $table)) {
                    $this->db->query("ALTER TABLE `{$table}` ADD `{$name}` {$definition}");
                }
            }
        }

        $meta = db_prefix() . 'sc_sales_meta';
        if (!$this->db->table_exists($meta)) {
            $this->db->query("CREATE TABLE `{$meta}` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `rel_type` VARCHAR(30) NOT NULL,
              `rel_id` INT UNSIGNED NOT NULL,
              `deposit_percent` DECIMAL(7,2) NOT NULL DEFAULT 100.00,
              `payment_stage` VARCHAR(30) NOT NULL DEFAULT 'full',
              `discount_reason` VARCHAR(191) NULL,
              `discount_title` VARCHAR(191) NULL,
              `discount_description` TEXT NULL,
              `discount_mode` VARCHAR(20) NOT NULL DEFAULT 'percent',
              `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `contract_total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `due_now` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `remaining_balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
              `auto_create_balance` TINYINT(1) NOT NULL DEFAULT 1,
              `created_at` DATETIME NULL,
              `updated_at` DATETIME NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `rel_unique` (`rel_type`,`rel_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }

        update_option('delete_only_on_last_invoice', '0');
        update_option('delete_only_on_last_estimate', '0');
        update_option('smart_choice_crm_build', '3.6.9 SC');
        update_option('smart_choice_core_upgrade_applied', '369');
        update_option('smart_choice_sales_partial_payment_enabled', '1');
        update_option('smart_choice_sales_public_attachments_enabled', '1');
        update_option('smart_choice_sales_email_delete_repair', '1');
    }

    public function down() {}
}

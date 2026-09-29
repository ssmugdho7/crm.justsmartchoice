<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_370 extends CI_Migration
{
    public function up()
    {
        $itemable = db_prefix() . 'itemable';
        if ($this->db->table_exists($itemable)) {
            if (!$this->db->field_exists('is_optional', $itemable)) {
                $this->db->query("ALTER TABLE `{$itemable}` ADD `is_optional` TINYINT(1) NOT NULL DEFAULT 0 AFTER `unit`");
            }
            if (!$this->db->field_exists('is_selected', $itemable)) {
                $this->db->query("ALTER TABLE `{$itemable}` ADD `is_selected` TINYINT(1) NOT NULL DEFAULT 1 AFTER `is_optional`");
            }
            $this->db->query("UPDATE `{$itemable}` SET `is_selected`=1 WHERE `is_optional`=0");
        }

        foreach (['invoices','estimates','proposals'] as $entity) {
            $table = db_prefix() . $entity;
            if (!$this->db->table_exists($table)) { continue; }
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

        $ledger = db_prefix() . 'sc_payment_notification_ledger';
        if (!$this->db->table_exists($ledger)) {
            $this->db->query("CREATE TABLE `{$ledger}` (
              `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              `payment_id` INT UNSIGNED NOT NULL,
              `channel` VARCHAR(40) NOT NULL,
              `recipient` VARCHAR(191) NOT NULL DEFAULT '',
              `status` VARCHAR(20) NOT NULL DEFAULT 'sent',
              `created_at` DATETIME NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `payment_channel_recipient` (`payment_id`,`channel`,`recipient`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
        }

        update_option('delete_only_on_last_invoice', '0');
        update_option('delete_only_on_last_estimate', '0');
        update_option('smart_choice_crm_build', '3.7.0 SC');
        update_option('smart_choice_core_upgrade_applied', '370');
        update_option('smart_choice_sales_optional_items_enabled', '1');
        update_option('smart_choice_sales_partial_payment_enabled', '1');
        update_option('smart_choice_sales_public_attachments_enabled', '1');
        update_option('smart_choice_payment_notification_idempotency', '1');
    }

    public function down() {}
}

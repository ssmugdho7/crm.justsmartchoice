<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_389 extends App_migration
{
    public function up()
    {
        $tables = [db_prefix() . 'proposals', db_prefix() . 'estimates', db_prefix() . 'invoices'];
        $columns = [
            'sc_contract_total' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
            'sc_down_payment_percent' => "DECIMAL(7,2) NOT NULL DEFAULT 100.00",
            'sc_remaining_balance' => "DECIMAL(15,2) NOT NULL DEFAULT 0.00",
            'sc_discount_reason' => "VARCHAR(191) NULL",
        ];

        foreach ($tables as $table) {
            if (!$this->db->table_exists($table)) {
                continue;
            }
            foreach ($columns as $column => $definition) {
                if (!$this->db->field_exists($column, $table)) {
                    $this->db->query("ALTER TABLE `{$table}` ADD `{$column}` {$definition}");
                }
            }
        }

        update_option('smart_choice_crm_build', '3.8.9');
        update_option('smart_choice_core_upgrade_applied', '389');
        update_option('smart_choice_sales_payment_repair', '1');
        update_option('smart_choice_proposal_preview_items_repair', '1');
    }

    public function down()
    {
        update_option('smart_choice_crm_build', '3.8.8');
        delete_option('smart_choice_sales_payment_repair');
        delete_option('smart_choice_proposal_preview_items_repair');
    }
}

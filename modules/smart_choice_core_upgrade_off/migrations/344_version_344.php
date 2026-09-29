<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_344 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $CI->load->dbforge();

        if ($CI->db->table_exists(db_prefix() . 'options')) {
            $exists = $CI->db->where('name', 'smart_choice_enterprise_current_version')->get(db_prefix() . 'options')->row();
            if ($exists) {
                $CI->db->where('name', 'smart_choice_enterprise_current_version')->update(db_prefix() . 'options', ['value' => '3.4.4']);
            } else {
                $CI->db->insert(db_prefix() . 'options', ['name' => 'smart_choice_enterprise_current_version', 'value' => '3.4.4', 'autoload' => 1]);
            }
        }

        // Optional custom columns for Smart Choice down-payment/discount notes. Core math remains compatible if columns are unused.
        foreach (['invoices', 'estimates', 'proposals'] as $table) {
            $full = db_prefix() . $table;
            if ($CI->db->table_exists($full)) {
                if (!$CI->db->field_exists('smart_choice_down_payment_percent', $full)) {
                    $CI->dbforge->add_column($table, ['smart_choice_down_payment_percent' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00', 'null' => false]]);
                }
                if (!$CI->db->field_exists('smart_choice_down_payment_amount', $full)) {
                    $CI->dbforge->add_column($table, ['smart_choice_down_payment_amount' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00', 'null' => false]]);
                }
                if (!$CI->db->field_exists('smart_choice_discount_reason', $full)) {
                    $CI->dbforge->add_column($table, ['smart_choice_discount_reason' => ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true]]);
                }
            }
        }
    }


    public function down()
    {
        // Smart Choice safe no-op rollback. Keep CRM data intact.
    }
}

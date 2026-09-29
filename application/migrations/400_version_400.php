<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_400 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_crm_build', '4.0.0');
        update_option('smart_choice_crm_current_version', '4.0.0');

        foreach (['proposals', 'estimates', 'invoices'] as $tableName) {
            $table = db_prefix() . $tableName;
            if ($this->db->table_exists($table) && !$this->db->field_exists('sc_down_payment_amount', $table)) {
                $this->db->query("ALTER TABLE `{$table}` ADD `sc_down_payment_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `sc_down_payment_percent`");
            }
        }
    }

    public function down()
    {
    }
}

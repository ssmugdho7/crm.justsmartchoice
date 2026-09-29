<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_395 extends CI_Migration
{
    public function up()
    {
        update_option('smart_choice_crm_build', '3.9.5');
        update_option('smart_choice_crm_current_version', '3.9.5');
        foreach (['proposals','estimates','invoices'] as $tableName) {
            $table = db_prefix() . $tableName;
            if ($this->db->table_exists($table)) {
                if (!$this->db->field_exists('sc_custom_status_value', $table)) {
                    $this->db->query("ALTER TABLE `{$table}` ADD `sc_custom_status_value` VARCHAR(191) NULL DEFAULT NULL");
                }
                if (!$this->db->field_exists('sc_down_payment_mode', $table)) {
                    $this->db->query("ALTER TABLE `{$table}` ADD `sc_down_payment_mode` VARCHAR(20) NOT NULL DEFAULT 'percent'");
                }
            }
        }
    }
    public function down() {}
}

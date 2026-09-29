<?php defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_204 extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();
        $table = db_prefix() . 'reminders';
        if (!$CI->db->table_exists($table)) {
            return;
        }

        $columns = [
            'user_type' => "VARCHAR(50) NOT NULL DEFAULT 'self'",
            'assignment_type' => "VARCHAR(20) NOT NULL DEFAULT 'self'",
            'assigned_to' => "INT(11) NULL DEFAULT NULL",
            'created_by' => "INT(11) NULL DEFAULT NULL",
            'created_by_staff' => "INT(11) NULL DEFAULT NULL",
            'customer' => "INT(11) NULL DEFAULT 0",
            'contact' => "INT(11) NULL DEFAULT 0",
            'rel_id' => "INT(11) NULL DEFAULT 0",
            'rel_type' => "VARCHAR(40) NULL DEFAULT ''",
            'services' => "TEXT NULL",
            'total_amount' => "DECIMAL(15,2) NULL DEFAULT 0",
        ];

        foreach ($columns as $field => $definition) {
            if (!$CI->db->field_exists($field, $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `" . $field . "` " . $definition);
            }
        }

        foreach (['customer' => 'INT(11) NULL DEFAULT 0', 'contact' => 'INT(11) NULL DEFAULT 0', 'rel_id' => 'INT(11) NULL DEFAULT 0', 'rel_type' => "VARCHAR(40) NULL DEFAULT ''"] as $field => $definition) {
            if ($CI->db->field_exists($field, $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` MODIFY `" . $field . "` " . $definition);
            }
        }
    }
    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }

}

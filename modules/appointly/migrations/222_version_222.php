<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_222 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'appointly_google';
        if (!$CI->db->table_exists($table)) return;
        $types = [];
        foreach ($CI->db->field_data($table) as $field) $types[$field->name] = strtolower($field->type);
        foreach (['access_token', 'refresh_token'] as $column) {
            if (isset($types[$column]) && $types[$column] !== 'longtext') {
                $CI->db->query("ALTER TABLE `{$table}` MODIFY `{$column}` LONGTEXT NOT NULL");
            }
        }
    }
    public function down()
    {
        // Never truncate OAuth tokens or ciphertext during rollback.
    }
}

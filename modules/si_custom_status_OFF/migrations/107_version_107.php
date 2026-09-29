<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_107 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $tables = [
            db_prefix() . 'si_custom_status' => 'admin',
            db_prefix() . 'si_custom_status_default' => 'all',
        ];

        foreach ($tables as $table => $defaultScope) {
            if (!$CI->db->table_exists($table)) {
                continue;
            }

            if (!$CI->db->field_exists('visibility_scope', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD `visibility_scope` VARCHAR(20) NOT NULL DEFAULT '{$defaultScope}' AFTER `filter_default`");
            }
        }

        if ($CI->db->table_exists(db_prefix() . 'si_custom_status')) {
            $CI->db->where('visibility_scope', '');
            $CI->db->or_where('visibility_scope IS NULL', null, false);
            $CI->db->update(db_prefix() . 'si_custom_status', ['visibility_scope' => 'admin']);
        }

        if ($CI->db->table_exists(db_prefix() . 'si_custom_status_default')) {
            $CI->db->where('visibility_scope', '');
            $CI->db->or_where('visibility_scope IS NULL', null, false);
            $CI->db->update(db_prefix() . 'si_custom_status_default', ['visibility_scope' => 'all']);
        }
    }

    public function down()
    {
        // Safe rollback placeholder intentionally left non-destructive.
        return true;
    }
}

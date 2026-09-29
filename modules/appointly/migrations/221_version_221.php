<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_221 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'appointly_appointments';
        if ($CI->db->table_exists($table) && $CI->db->field_exists('status', $table)) {
            $CI->db->where('status', 'no_show')->update($table, ['status' => 'no-show']);
            $CI->db->where('status', 'pending_approval')->update($table, ['status' => 'pending']);
        }
    }

    public function down()
    {
        // Data normalization is intentionally retained for safe rollback.
    }
}

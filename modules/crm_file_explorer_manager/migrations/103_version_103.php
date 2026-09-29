<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_103 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'crm_file_explorer_index';
        if (!$CI->db->table_exists($table)) {
            return;
        }
        if (!$CI->db->field_exists('path_hash', $table)) {
            $CI->db->query('ALTER TABLE `' . $table . '` ADD `path_hash` CHAR(40) NULL AFTER `relative_path`');
        }
        // Deliberately avoid full-table UPDATE/DELETE/UNIQUE operations here.
        // Existing duplicates are consolidated incrementally during normal scans.
        update_option('crm_file_explorer_manager_version', '1.0.3');
    }

    public function down()
    {
        // Upgrade only. Existing indexed records are preserved.
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_355 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'staff';

        if (!$CI->db->table_exists($table)) {
            // Safety guard: never create tbltblstaff or continue against a missing core table.
            log_message('error', 'Smart Choice migration 355 skipped staff custom fields because table was not found: ' . $table);
            update_option('smart_choice_enterprise_current_version', '3.5.5');
            update_option('smart_choice_enterprise_latest_version', '3.5.5');
            update_option('smart_choice_core_latest_version', '3.5.5');
            update_option('update_info_message', '');
            return;
        }

        $columns = $CI->db->list_fields($table);

        if (!in_array('religion', $columns, true)) {
            $CI->db->query('ALTER TABLE `' . $table . '` ADD `religion` VARCHAR(191) NULL');
        }

        if (!in_array('marital_status', $columns, true)) {
            $CI->db->query('ALTER TABLE `' . $table . '` ADD `marital_status` VARCHAR(50) NULL');
        }

        if (!in_array('children_count', $columns, true)) {
            $CI->db->query('ALTER TABLE `' . $table . '` ADD `children_count` INT(11) NOT NULL DEFAULT 0');
        }

        if (!in_array('children_json', $columns, true)) {
            $CI->db->query('ALTER TABLE `' . $table . '` ADD `children_json` TEXT NULL');
        }

        update_option('smart_choice_enterprise_current_version', '3.5.5');
        update_option('smart_choice_enterprise_latest_version', '3.5.5');
        update_option('smart_choice_core_latest_version', '3.5.5');
        update_option('update_info_message', '');
    }

    public function down()
    {
        // Intentionally left blank. Core CRM upgrades are forward-only.
    }
}

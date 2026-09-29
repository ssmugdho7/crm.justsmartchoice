<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_357 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        if (function_exists('update_option')) {
            update_option('smart_choice_enterprise_current_version', '3.5.7');
            update_option('smart_choice_enterprise_latest_version', '3.5.7');
            update_option('smart_choice_core_current_version', '3.5.7');
            update_option('smart_choice_core_latest_version', '3.5.7');
            update_option('smart_choice_crm_current_version', '3.5.7');
            update_option('smart_choice_crm_latest_version', '3.5.7');
        }
        $staff = db_prefix() . 'staff';
        if ($CI->db->table_exists($staff)) {
            $fields = [
                'religion' => "VARCHAR(100) NULL",
                'marital_status' => "VARCHAR(50) NULL",
                'children_count' => "INT(11) NOT NULL DEFAULT 0",
                'children_info' => "TEXT NULL",
                'server_login' => "VARCHAR(191) NULL",
                'server_ip' => "VARCHAR(100) NULL",
                'server_password' => "VARCHAR(191) NULL",
                'whatsapp' => "VARCHAR(100) NULL",
            ];
            foreach ($fields as $field => $definition) {
                if (!$CI->db->field_exists($field, $staff)) {
                    $CI->db->query('ALTER TABLE `' . $staff . '` ADD `' . $field . '` ' . $definition);
                }
            }
        }
    }
    public function down() {}
}

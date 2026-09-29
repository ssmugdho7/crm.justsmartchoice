<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_351 extends CI_Migration
{
    public function up()
    {
        $CI = &get_instance();
        $CI->load->dbforge();

        // IMPORTANT:
        // dbforge->add_column() expects the un-prefixed table name in Perfex/CodeIgniter.
        // Passing db_prefix().'staff' makes CodeIgniter generate tbltblstaff on Bluehost.
        $staffTableRaw = 'staff';
        $staffTableFull = db_prefix() . 'staff';

        if ($CI->db->table_exists($staffTableFull) || $CI->db->table_exists($staffTableRaw)) {
            $fields = [];

            if (!$CI->db->field_exists('telegram', $staffTableFull) && !$CI->db->field_exists('telegram', $staffTableRaw)) {
                $fields['telegram'] = ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true];
            }

            if (!$CI->db->field_exists('server_login', $staffTableFull) && !$CI->db->field_exists('server_login', $staffTableRaw)) {
                $fields['server_login'] = ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true];
            }

            if (!$CI->db->field_exists('server_ip', $staffTableFull) && !$CI->db->field_exists('server_ip', $staffTableRaw)) {
                $fields['server_ip'] = ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true];
            }

            if (!$CI->db->field_exists('server_password', $staffTableFull) && !$CI->db->field_exists('server_password', $staffTableRaw)) {
                $fields['server_password'] = ['type' => 'VARCHAR', 'constraint' => 191, 'null' => true];
            }

            if (!empty($fields)) {
                $CI->dbforge->add_column($staffTableRaw, $fields);
            }
        }

        update_option('smart_choice_enterprise_current_version', '3.5.1');
        update_option('smart_choice_enterprise_latest_version', '3.5.1');
        update_option('smart_choice_core_current_version', '3.5.1');
        update_option('smart_choice_core_latest_version', '3.5.1');
        update_option('latest_version', '3.5.1');
    }

    public function down() {}
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Appointly_smartchoice_upgrade extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();

        add_option('appointly_smartchoice_public_logo_width', '110');
        add_option('appointly_smartchoice_public_logo_height', '38');

        $candidateTables = [
            db_prefix() . 'appointly_appointments',
            db_prefix() . 'appointments',
            db_prefix() . 'appointly',
        ];

        foreach ($candidateTables as $table) {
            if (!$CI->db->table_exists($table)) {
                continue;
            }

            if (!$CI->db->field_exists('created_by', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `created_by` INT(11) NULL DEFAULT NULL");
            }

            if (!$CI->db->field_exists('smartchoice_notes', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `smartchoice_notes` TEXT NULL");
            }
        }
    }
}

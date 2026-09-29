<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Smartchoice_favorite_links_upgrade extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();

        add_option('favorite_links_enabled', '1');
        add_option('favorite_links_open_new_tab', '1');
        add_option('favorite_links_show_in_menu', '1');

        $candidateTables = [
            db_prefix() . 'favorite_links',
            db_prefix() . 'favorite_link',
            db_prefix() . 'fav_links',
            db_prefix() . 'links',
        ];

        foreach ($candidateTables as $table) {
            if (!$CI->db->table_exists($table)) {
                continue;
            }

            if (!$CI->db->field_exists('created_by', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `created_by` INT(11) NULL DEFAULT NULL");
            }

            if (!$CI->db->field_exists('is_active', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `is_active` TINYINT(1) NOT NULL DEFAULT 1");
            }

            if (!$CI->db->field_exists('smartchoice_notes', $table)) {
                $CI->db->query("ALTER TABLE `" . $table . "` ADD `smartchoice_notes` TEXT NULL");
            }
        }
    }

    public function down()
    {
        return true;
    }
}

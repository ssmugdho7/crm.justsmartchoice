<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_435 extends CI_Migration
{
    public function up()
    {
        $CI =& get_instance();
        if (!$CI->db->field_exists('title', db_prefix() . 'todos')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'todos` ADD `title` VARCHAR(191) NULL DEFAULT NULL AFTER `todoid`');
        }

        if (!$CI->db->table_exists(db_prefix() . 'todoassignees')) {
            $CI->db->query('CREATE TABLE `' . db_prefix() . 'todoassignees` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `todoid` INT(11) NOT NULL,
                `staffid` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `todo_staff_unique` (`todoid`,`staffid`),
                KEY `staffid` (`staffid`),
                KEY `todoid` (`todoid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
        }
        update_option('sc_crm_build_version', '4.3.5');
        update_option('smart_choice_crm_build', '4.3.5');
        update_option('smart_choice_crm_current_version', '4.3.5');
    }

    public function down() {}
}

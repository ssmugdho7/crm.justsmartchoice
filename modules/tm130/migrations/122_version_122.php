<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_122 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $tbl = db_prefix() . 'wiki_articles';
        if ($CI->db->table_exists($tbl)) {
            if (!$CI->db->field_exists('created_by', $tbl)) {
                $CI->db->query("ALTER TABLE `{$tbl}` ADD `created_by` INT(11) NULL DEFAULT NULL AFTER `author_id`");
            }
            if (!$CI->db->field_exists('updated_by', $tbl)) {
                $CI->db->query("ALTER TABLE `{$tbl}` ADD `updated_by` INT(11) NULL DEFAULT NULL AFTER `created_by`");
            }
            if (!$CI->db->field_exists('last_creator_change_by', $tbl)) {
                $CI->db->query("ALTER TABLE `{$tbl}` ADD `last_creator_change_by` INT(11) NULL DEFAULT NULL AFTER `updated_by`");
            }
            if (!$CI->db->field_exists('last_creator_change_at', $tbl)) {
                $CI->db->query("ALTER TABLE `{$tbl}` ADD `last_creator_change_at` DATETIME NULL DEFAULT NULL AFTER `last_creator_change_by`");
            }
            $CI->db->query("UPDATE `{$tbl}` SET created_by = author_id WHERE (created_by IS NULL OR created_by = 0) AND author_id IS NOT NULL");
        }

        $tblStaffArticle = db_prefix() . 'wiki_staff_article';
        if ($CI->db->table_exists($tblStaffArticle)) {
            if (!$CI->db->field_exists('created_by', $tblStaffArticle)) {
                $CI->db->query("ALTER TABLE `{$tblStaffArticle}` ADD `created_by` INT(11) NULL DEFAULT NULL AFTER `article_id`");
            }
        }

        if (function_exists('get_option') && get_option('training_manual_last_safe_upgrade') === '') {
            add_option('training_manual_last_safe_upgrade', '1.2.2');
        } elseif (function_exists('update_option')) {
            update_option('training_manual_last_safe_upgrade', '1.2.2');
        }
    }

    public function down()
    {
        return true;
    }
}

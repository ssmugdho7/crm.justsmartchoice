<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_126 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table = db_prefix() . 'wiki_articles';
        if ($CI->db->table_exists($table)) {
            if (!$CI->db->field_exists('created_by', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD `created_by` INT(11) NULL DEFAULT NULL AFTER `author_id`");
            }
            if (!$CI->db->field_exists('updated_by', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD `updated_by` INT(11) NULL DEFAULT NULL AFTER `created_by`");
            }
            $CI->db->query("UPDATE `{$table}` SET `created_by` = `author_id` WHERE (`created_by` IS NULL OR `created_by` = 0) AND `author_id` IS NOT NULL");
        }
        update_option('training_manual_current_version', '1.2.6');
    }

    public function down()
    {
        // Upgrade-safe release. Existing training records and creator information are preserved.
    }
}

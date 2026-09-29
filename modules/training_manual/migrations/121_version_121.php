<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_121 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $tbl = db_prefix() . 'wiki_articles';

        if ($CI->db->table_exists($tbl)) {
            $fields = [
                'created_by' => "INT(11) NULL DEFAULT NULL AFTER `author_id`",
                'updated_by' => "INT(11) NULL DEFAULT NULL AFTER `created_by`",
                'last_creator_change_by' => "INT(11) NULL DEFAULT NULL AFTER `updated_by`",
                'last_creator_change_at' => "DATETIME NULL DEFAULT NULL AFTER `last_creator_change_by`",
                'audience' => "VARCHAR(30) NOT NULL DEFAULT 'internal' AFTER `style_preset`",
                'content_kind' => "VARCHAR(30) NOT NULL DEFAULT 'article' AFTER `audience`",
            ];

            foreach ($fields as $field => $definition) {
                if (!$CI->db->field_exists($field, $tbl)) {
                    $CI->db->query("ALTER TABLE `{$tbl}` ADD `{$field}` {$definition}");
                }
            }

            $CI->db->query("UPDATE `{$tbl}` SET created_by = author_id WHERE (created_by IS NULL OR created_by = 0) AND author_id IS NOT NULL");
        }

        if (function_exists('add_option')) {
            add_option('training_manual_enable_client_portal_videos', '1');
            add_option('training_manual_last_safe_upgrade', '1.2.1');
        }
        if (function_exists('update_option')) {
            update_option('training_manual_enable_client_portal_videos', '1');
            update_option('training_manual_last_safe_upgrade', '1.2.1');
        }
    }

    public function down()
    {
        $CI = &get_instance();
        if (function_exists('update_option')) {
            update_option('training_manual_last_safe_upgrade', '1.2.0');
        }
    }
}

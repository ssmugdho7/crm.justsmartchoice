<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_115 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $tbl_articles = db_prefix() . 'wiki_articles';

        if ($CI->db->table_exists($tbl_articles)) {
            $fields = [
                'language_code' => "VARCHAR(20) NULL DEFAULT 'en' AFTER `visibility`",
                'style_preset' => "VARCHAR(50) NULL DEFAULT 'smart_choice' AFTER `language_code`",
                'last_creator_change_by' => "INT(11) NULL DEFAULT NULL AFTER `updated_by`",
                'last_creator_change_at' => "DATETIME NULL DEFAULT NULL AFTER `last_creator_change_by`"
            ];

            foreach ($fields as $field => $definition) {
                if (!$CI->db->field_exists($field, $tbl_articles)) {
                    $CI->db->query("ALTER TABLE `{$tbl_articles}` ADD `{$field}` {$definition}");
                }
            }
        }

        add_option('training_manual_default_language', 'en');
        add_option('training_manual_default_style', 'smart_choice');
        add_option('training_manual_external_css_enabled', '1');
    }
}

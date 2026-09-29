<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration 202 - Version 2.0.2:
 * Widget chips, suggested order, and per-language Q&A translations.
 */
class Migration_Version_202 extends App_module_migration
{
    public function up()
    {
        $CI     = &get_instance();
        $prefix = db_prefix();

        $qaTbl = $prefix . 'chatbot_training_qa';
        $trTbl = $prefix . 'chatbot_training_qa_translations';

        if (!$CI->db->table_exists($qaTbl)) {
            return true;
        }

        if (!$CI->db->field_exists('is_suggested', $qaTbl)) {
            $CI->db->query("ALTER TABLE `{$qaTbl}` ADD `is_suggested` TINYINT(1) NOT NULL DEFAULT 0 AFTER `answer`");
        }

        if (!$CI->db->field_exists('suggested_order', $qaTbl)) {
            $CI->db->query("ALTER TABLE `{$qaTbl}` ADD `suggested_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `is_suggested`");
        }

        $idx = 'chatbot_suggested';

        $exists = $CI->db
            ->query("SHOW INDEX FROM `{$qaTbl}` WHERE Key_name = " . $CI->db->escape($idx))
            ->num_rows() > 0;

        if (!$exists && $CI->db->field_exists('is_suggested', $qaTbl)) {
            $CI->db->query("ALTER TABLE `{$qaTbl}` ADD INDEX `{$idx}` (`chatbot_id`, `is_suggested`, `suggested_order`)");
        }

        if (!$CI->db->table_exists($trTbl)) {
            $CI->db->query("
                CREATE TABLE `{$trTbl}` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `qa_id` INT(11) UNSIGNED NOT NULL,
                    `language` VARCHAR(32) NOT NULL,
                    `question` TEXT NOT NULL,
                    `answer` TEXT NOT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `qa_language` (`qa_id`, `language`),
                    KEY `qa_id` (`qa_id`),
                    CONSTRAINT `fk_qa_translations_qa`
                        FOREIGN KEY (`qa_id`)
                        REFERENCES `{$qaTbl}` (`id`)
                        ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
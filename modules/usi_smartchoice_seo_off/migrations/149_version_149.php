<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_149 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_core_context')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_core_context` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `context_title` VARCHAR(191) NOT NULL,
                `context_type` VARCHAR(100) NOT NULL DEFAULT 'general',
                `related_type` VARCHAR(100) NULL,
                `related_id` INT(11) NULL,
                `context_summary` MEDIUMTEXT NULL,
                `context_data` LONGTEXT NULL,
                `priority` VARCHAR(50) NOT NULL DEFAULT 'normal',
                `status` VARCHAR(50) NOT NULL DEFAULT 'active',
                `created_by` INT(11) NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_context_type` (`context_type`),
                KEY `idx_related` (`related_type`, `related_id`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_core_actions')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_core_actions` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `action_title` VARCHAR(191) NOT NULL,
                `action_type` VARCHAR(100) NOT NULL DEFAULT 'review',
                `source_area` VARCHAR(100) NOT NULL DEFAULT 'core',
                `related_type` VARCHAR(100) NULL,
                `related_id` INT(11) NULL,
                `requested_command` MEDIUMTEXT NULL,
                `recommended_action` MEDIUMTEXT NULL,
                `approval_required` TINYINT(1) NOT NULL DEFAULT 1,
                `assigned_staff_id` INT(11) NULL,
                `due_date` DATE NULL,
                `priority` VARCHAR(50) NOT NULL DEFAULT 'normal',
                `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
                `created_by` INT(11) NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_action_type` (`action_type`),
                KEY `idx_source_area` (`source_area`),
                KEY `idx_status` (`status`),
                KEY `idx_related` (`related_type`, `related_id`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_core_timeline')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_core_timeline` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `event_title` VARCHAR(191) NOT NULL,
                `event_type` VARCHAR(100) NOT NULL DEFAULT 'system',
                `source_area` VARCHAR(100) NOT NULL DEFAULT 'core',
                `related_type` VARCHAR(100) NULL,
                `related_id` INT(11) NULL,
                `event_summary` MEDIUMTEXT NULL,
                `event_data` LONGTEXT NULL,
                `created_by` INT(11) NULL,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_event_type` (`event_type`),
                KEY `idx_source_area` (`source_area`),
                KEY `idx_related` (`related_type`, `related_id`)
            ) " . $charset);
        }

        update_option('usi_smartchoice_seo_version', '1.4.9');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_core_timeline')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_core_timeline`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_core_actions')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_core_actions`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_core_context')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_core_context`'); }
    }
}

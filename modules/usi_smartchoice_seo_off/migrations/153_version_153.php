<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_153 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_advanced_vision_sessions')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_advanced_vision_sessions` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `session_title` VARCHAR(191) NOT NULL,
                `related_type` VARCHAR(100) NOT NULL DEFAULT 'project',
                `related_id` INT(11) NOT NULL DEFAULT 0,
                `service_type` VARCHAR(100) NOT NULL DEFAULT 'general',
                `room_area` VARCHAR(191) NULL,
                `photo_reference` VARCHAR(255) NULL,
                `analysis_prompt` MEDIUMTEXT NULL,
                `measurement_notes` MEDIUMTEXT NULL,
                `detected_summary` MEDIUMTEXT NULL,
                `confidence_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `status` VARCHAR(50) NOT NULL DEFAULT 'draft',
                `created_by` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_related` (`related_type`, `related_id`),
                KEY `idx_service` (`service_type`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_advanced_vision_detections')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_advanced_vision_detections` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `session_id` INT(11) NOT NULL DEFAULT 0,
                `object_name` VARCHAR(191) NOT NULL,
                `object_type` VARCHAR(100) NOT NULL DEFAULT 'general',
                `confidence_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `notes` MEDIUMTEXT NULL,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_session` (`session_id`),
                KEY `idx_type` (`object_type`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_advanced_vision_measurements')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_advanced_vision_measurements` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `session_id` INT(11) NOT NULL DEFAULT 0,
                `measurement_label` VARCHAR(191) NOT NULL,
                `measurement_type` VARCHAR(100) NOT NULL DEFAULT 'manual',
                `measurement_value` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
                `unit` VARCHAR(50) NULL,
                `confidence_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `notes` MEDIUMTEXT NULL,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_session` (`session_id`),
                KEY `idx_type` (`measurement_type`)
            ) " . $charset);
        }

        update_option('usi_smartchoice_seo_version', '1.5.3');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_advanced_vision_measurements')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_advanced_vision_measurements`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_advanced_vision_detections')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_advanced_vision_detections`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_advanced_vision_sessions')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_advanced_vision_sessions`'); }
    }
}

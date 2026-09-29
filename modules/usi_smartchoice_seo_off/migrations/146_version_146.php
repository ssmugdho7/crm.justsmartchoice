<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_146 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_api_providers')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_api_providers` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `provider_name` VARCHAR(191) NOT NULL,
                `provider_type` VARCHAR(100) NOT NULL DEFAULT 'ai',
                `base_url` VARCHAR(255) NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_provider_type` (`provider_type`),
                KEY `idx_is_active` (`is_active`)
            ) " . $charset);
        }
        if (!$CI->db->table_exists($prefix . 'usi_ai_api_keys')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_api_keys` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `provider_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `key_label` VARCHAR(191) NOT NULL,
                `api_key_encrypted` MEDIUMTEXT NULL,
                `key_status` VARCHAR(50) NOT NULL DEFAULT 'untested',
                `last_checked_at` DATETIME NULL,
                `created_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_provider_id` (`provider_id`),
                KEY `idx_key_status` (`key_status`)
            ) " . $charset);
        }
        if (!$CI->db->table_exists($prefix . 'usi_ai_api_health_checks')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_api_health_checks` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `provider_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `check_status` VARCHAR(50) NOT NULL DEFAULT 'queued',
                `message` MEDIUMTEXT NULL,
                `checked_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_provider_id` (`provider_id`),
                KEY `idx_check_status` (`check_status`)
            ) " . $charset);
        }

        update_option('usi_smartchoice_seo_version', '1.4.6');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_api_health_checks')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_api_health_checks`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_api_keys')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_api_keys`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_api_providers')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_api_providers`'); }

    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->dbforge();

$options = [
    'ga_enabled'                    => '0',
    'ga_track_admin'                => '0',
    'ga_track_client'               => '1',
    'ga_crm_measurement_id'         => '',
    'ga_default_property_id'        => '',
    'ga_service_account_json'       => '',
    'ga_report_cache_minutes'       => '30',
    'ga_measurement_protocol_secret'=> '',
    'ga_last_health_check'          => '',
    'ga_module_version'             => GOOGLE_ANALYTICS_MODULE_VERSION,
];
foreach ($options as $name => $value) {
    add_option($name, $value);
}

$prefix = db_prefix();
if (!$CI->db->table_exists($prefix . 'ga_websites')) {
    $CI->db->query("CREATE TABLE `{$prefix}ga_websites` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(191) NOT NULL,
        `website_url` VARCHAR(255) NOT NULL,
        `measurement_id` VARCHAR(32) NOT NULL,
        `property_id` VARCHAR(32) DEFAULT NULL,
        `stream_id` VARCHAR(64) DEFAULT NULL,
        `api_secret` TEXT NULL,
        `timezone` VARCHAR(64) DEFAULT 'America/New_York',
        `active` TINYINT(1) NOT NULL DEFAULT 1,
        `is_default` TINYINT(1) NOT NULL DEFAULT 0,
        `created_by` INT UNSIGNED DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        `date_updated` DATETIME DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `measurement_id` (`measurement_id`),
        KEY `property_id` (`property_id`),
        KEY `active` (`active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}
if (!$CI->db->table_exists($prefix . 'ga_report_cache')) {
    $CI->db->query("CREATE TABLE `{$prefix}ga_report_cache` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `website_id` INT UNSIGNED DEFAULT NULL,
        `cache_key` VARCHAR(191) NOT NULL,
        `report_type` VARCHAR(64) NOT NULL,
        `payload` LONGTEXT NOT NULL,
        `date_created` DATETIME NOT NULL,
        `expires_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `cache_key` (`cache_key`),
        KEY `website_id` (`website_id`),
        KEY `expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}
if (!$CI->db->table_exists($prefix . 'ga_audit_log')) {
    $CI->db->query("CREATE TABLE `{$prefix}ga_audit_log` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `staff_id` INT UNSIGNED DEFAULT NULL,
        `action` VARCHAR(100) NOT NULL,
        `details` TEXT NULL,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `date_created` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`),
        KEY `action` (`action`),
        KEY `date_created` (`date_created`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
}

// Preserve legacy snippets as a disabled backup instead of injecting arbitrary markup globally.
foreach (['google_analytics_admin_area','google_analytics_clients_area','google_analytics_clients_and_admin_area'] as $legacy) {
    $value = get_option($legacy);
    if ($value !== '' && get_option('ga_legacy_backup_' . $legacy) === '') {
        add_option('ga_legacy_backup_' . $legacy, $value);
    }
}
update_option('google_analytics', 'disable');
update_option('ga_module_version', GOOGLE_ANALYTICS_MODULE_VERSION);

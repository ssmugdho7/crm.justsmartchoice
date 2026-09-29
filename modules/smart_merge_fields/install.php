<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'smart_merge_mappings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_merge_mappings` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(191) NOT NULL,
        `source_table` VARCHAR(191) NOT NULL,
        `source_field` VARCHAR(191) NOT NULL,
        `target_table` VARCHAR(191) NOT NULL,
        `target_field` VARCHAR(191) NOT NULL,
        `relation_source_field` VARCHAR(191) NULL,
        `relation_target_field` VARCHAR(191) NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT "active",
        `direction` VARCHAR(50) NOT NULL DEFAULT "source_to_target",
        `last_run` DATETIME NULL,
        `created_by` INT NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `source_table` (`source_table`),
        KEY `target_table` (`target_table`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_merge_scan_cache')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_merge_scan_cache` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `table_name` VARCHAR(191) NOT NULL,
        `field_name` VARCHAR(191) NOT NULL,
        `field_type` VARCHAR(191) NULL,
        `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
        `is_email` TINYINT(1) NOT NULL DEFAULT 0,
        `is_phone` TINYINT(1) NOT NULL DEFAULT 0,
        `is_name` TINYINT(1) NOT NULL DEFAULT 0,
        `merge_tag` VARCHAR(255) NOT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `table_field` (`table_name`,`field_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_merge_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_merge_logs` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `mapping_id` INT UNSIGNED NULL,
        `action` VARCHAR(100) NOT NULL,
        `message` TEXT NULL,
        `records_affected` INT NOT NULL DEFAULT 0,
        `created_by` INT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `mapping_id` (`mapping_id`),
        KEY `action` (`action`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_merge_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_merge_settings` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `setting_key` VARCHAR(191) NOT NULL,
        `setting_value` TEXT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!is_dir(SMART_MERGE_FIELDS_UPLOADS_PATH)) {
    @mkdir(SMART_MERGE_FIELDS_UPLOADS_PATH, 0755, true);
}

$defaults = [
    'auto_scan_enabled' => '1',
    'allow_database_sync' => '1',
    'safe_mode' => '1',
    'last_installed_version' => SMART_MERGE_FIELDS_VERSION,
];
foreach ($defaults as $key => $value) {
    $exists = $CI->db->where('setting_key', $key)->get(db_prefix() . 'smart_merge_settings')->row();
    if (!$exists) {
        $CI->db->insert(db_prefix() . 'smart_merge_settings', [
            'setting_key' => $key,
            'setting_value' => $value,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

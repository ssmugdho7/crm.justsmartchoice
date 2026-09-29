<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'crm_file_explorer_scans')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "crm_file_explorer_scans` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `scan_type` VARCHAR(50) NOT NULL DEFAULT 'full',
        `base_path` TEXT NULL,
        `total_files` INT(11) NOT NULL DEFAULT 0,
        `total_size` BIGINT NOT NULL DEFAULT 0,
        `created_by` INT(11) NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'crm_file_explorer_index')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "crm_file_explorer_index` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `scan_id` INT(11) NULL,
        `relative_path` TEXT NOT NULL,
        `path_hash` CHAR(40) NOT NULL,
        `file_name` VARCHAR(255) NOT NULL,
        `extension` VARCHAR(25) NULL,
        `mime_type` VARCHAR(120) NULL,
        `file_type` VARCHAR(40) NULL,
        `file_size` BIGINT NOT NULL DEFAULT 0,
        `created_time` DATETIME NULL,
        `modified_time` DATETIME NULL,
        `module_guess` VARCHAR(120) NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `path_hash_unique` (`path_hash`),
        KEY `scan_id` (`scan_id`),
        KEY `file_type` (`file_type`),
        KEY `extension` (`extension`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'crm_file_explorer_backups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "crm_file_explorer_backups` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `backup_name` VARCHAR(255) NOT NULL,
        `backup_type` VARCHAR(80) NOT NULL,
        `relative_path` TEXT NOT NULL,
        `file_size` BIGINT NOT NULL DEFAULT 0,
        `created_by` INT(11) NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

$defaults = [
    'crm_file_explorer_manager_root_path' => FCPATH,
    'crm_file_explorer_manager_max_scan_files' => '5000',
    'crm_file_explorer_manager_allow_backup_zip' => '1',
    'crm_file_explorer_manager_public_preview' => '0',
    'crm_file_explorer_manager_author' => 'Smart Choice Contractors USA / Harold Cabrera',
    'crm_file_explorer_manager_version' => CRM_FILE_EXPLORER_MANAGER_VERSION,
];

foreach ($defaults as $name => $value) {
    if (get_option($name) === '') {
        add_option($name, $value);
    }
}

update_option('crm_file_explorer_manager_version', CRM_FILE_EXPLORER_MANAGER_VERSION);

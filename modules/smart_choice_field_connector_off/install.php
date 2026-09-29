<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'scfc_mappings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "scfc_mappings` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `source_module` VARCHAR(80) NOT NULL,
        `source_field` VARCHAR(150) NOT NULL,
        `destination_module` VARCHAR(80) NOT NULL,
        `destination_field` VARCHAR(150) NOT NULL,
        `merge_tag` VARCHAR(150) NULL,
        `notes` TEXT NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_by` INT(11) NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `source_module` (`source_module`),
        KEY `destination_module` (`destination_module`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

if (!$CI->db->table_exists(db_prefix() . 'scfc_custom_tokens')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "scfc_custom_tokens` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `token_name` VARCHAR(150) NOT NULL,
        `token_key` VARCHAR(150) NOT NULL,
        `source_module` VARCHAR(80) NOT NULL,
        `source_field` VARCHAR(150) NOT NULL,
        `description` TEXT NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_by` INT(11) NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `token_key` (`token_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

$options = [
    'scfc_enable_warnings' => '1',
    'scfc_enable_health_button' => '1',
    'scfc_default_preview_limit' => '25',
    'scfc_allow_delete_tokens' => '0',
];

foreach ($options as $name => $value) {
    if (get_option($name) === false) {
        add_option($name, $value);
    }
}

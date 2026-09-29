<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'scfc_groups')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "scfc_groups` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `group_name` VARCHAR(150) NOT NULL,
        `group_key` VARCHAR(150) NOT NULL,
        `description` TEXT NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_by` INT(11) NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `group_key` (`group_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

if (!$CI->db->table_exists(db_prefix() . 'scfc_mappings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "scfc_mappings` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `group_id` INT(11) NULL,
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
        KEY `group_id` (`group_id`),
        KEY `source_module` (`source_module`),
        KEY `destination_module` (`destination_module`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

if (!$CI->db->field_exists('group_id', db_prefix() . 'scfc_mappings')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'scfc_mappings` ADD `group_id` INT(11) NULL AFTER `id`');
}

if (!$CI->db->table_exists(db_prefix() . 'scfc_custom_tokens')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "scfc_custom_tokens` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `group_id` INT(11) NULL,
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
        UNIQUE KEY `token_key` (`token_key`),
        KEY `group_id` (`group_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

if (!$CI->db->field_exists('group_id', db_prefix() . 'scfc_custom_tokens')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'scfc_custom_tokens` ADD `group_id` INT(11) NULL AFTER `id`');
}

if (!$CI->db->table_exists(db_prefix() . 'scfc_combinations')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "scfc_combinations` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `group_id` INT(11) NULL,
        `combination_name` VARCHAR(150) NOT NULL,
        `combination_key` VARCHAR(150) NOT NULL,
        `template` TEXT NOT NULL,
        `description` TEXT NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_by` INT(11) NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `combination_key` (`combination_key`),
        KEY `group_id` (`group_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

$options = [
    'scfc_enable_warnings' => '1',
    'scfc_enable_health_button' => '1',
    'scfc_default_preview_limit' => '25',
    'scfc_allow_delete_tokens' => '0',
    'scfc_enable_visual_builder' => '1',
];

foreach ($options as $name => $value) {
    if (get_option($name) === false) {
        add_option($name, $value);
    }
}

$defaultGroups = [
    ['General Documents', 'general_documents', 'Default merge field group for contracts, proposals, estimates and invoices.'],
    ['Marketing', 'marketing', 'Merge fields used for email, campaign and customer follow-up content.'],
    ['Project Documents', 'project_documents', 'Merge fields used for project reports, work orders and field documentation.'],
];

foreach ($defaultGroups as $group) {
    $exists = $CI->db->where('group_key', $group[1])->count_all_results(db_prefix() . 'scfc_groups');
    if (!$exists) {
        $CI->db->insert(db_prefix() . 'scfc_groups', [
            'group_name' => $group[0],
            'group_key' => $group[1],
            'description' => $group[2],
            'is_active' => 1,
            'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

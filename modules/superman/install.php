<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'superman_mappings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "superman_mappings` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `source_module` VARCHAR(80) NOT NULL,
        `source_field` VARCHAR(150) NOT NULL,
        `destination_module` VARCHAR(80) NOT NULL,
        `destination_field` VARCHAR(150) NOT NULL,
        `merge_tag` VARCHAR(150) NULL,
        `map_group` VARCHAR(80) NULL DEFAULT 'Core Customer Flow',
        `priority` INT(11) NOT NULL DEFAULT 10,
        `overwrite_existing` TINYINT(1) NOT NULL DEFAULT 0,
        `is_locked_default` TINYINT(1) NOT NULL DEFAULT 0,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `source_module` (`source_module`),
        KEY `destination_module` (`destination_module`),
        KEY `map_group` (`map_group`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

if (!$CI->db->table_exists(db_prefix() . 'superman_tokens')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "superman_tokens` (
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

if (!$CI->db->table_exists(db_prefix() . 'superman_profiles')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "superman_profiles` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `profile_name` VARCHAR(150) NOT NULL,
        `profile_type` VARCHAR(80) NOT NULL DEFAULT 'settings',
        `payload` LONGTEXT NULL,
        `is_default` TINYINT(1) NOT NULL DEFAULT 0,
        `created_by` INT(11) NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `profile_type` (`profile_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

if (!$CI->db->table_exists(db_prefix() . 'superman_snapshots')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "superman_snapshots` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `source_module` VARCHAR(80) NOT NULL,
        `source_id` INT(11) NOT NULL,
        `payload` LONGTEXT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `source` (`source_module`,`source_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
}

$options = [
    'superman_enable_automation' => '1',
    'superman_enable_form_autofill' => '1',
    'superman_overwrite_existing' => '0',
    'superman_enable_visual_builder' => '1',
    'superman_enable_merge_search' => '1',
    'superman_enable_rollback_profiles' => '1',
    'superman_dropdown_single_scrollbar' => '1',
    'superman_gradient_white_text' => '1',
    'superman_sleek_mode' => '1',
    'superman_primary_color' => '#169179',
    'superman_secondary_color' => '#7fb79f',
    'superman_accent_color' => '#c7a56b',
    'superman_dark_color' => '#1f2933',
    'superman_surface_color' => '#f8faf8',
    'superman_sidebar_width' => '250',
    'superman_topbar_height' => '64',
    'superman_logo_max_height' => '54',
    'superman_menu_font_size' => '14',
    'superman_mobile_menu_font_size' => '16',
    'superman_table_name_width' => '180',
    'superman_table_email_width' => '160',
    'superman_client_login_button_size' => 'small',
];

foreach ($options as $name => $value) {
    if (get_option($name) === false) {
        add_option($name, $value);
    }
}

require_once(__DIR__ . '/helpers/superman_seed.php');
superman_seed_default_mappings($CI);

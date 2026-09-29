<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'smart_daily_productivity_entries')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . 'smart_daily_productivity_entries` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `staff_id` INT(11) NOT NULL,
        `entry_date` DATE NOT NULL,
        `source_type` VARCHAR(50) NOT NULL DEFAULT "manual",
        `source_id` INT(11) NULL,
        `related_type` VARCHAR(100) NULL,
        `related_id` INT(11) NULL,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `minutes_spent` INT(11) NOT NULL DEFAULT 0,
        `productivity_points` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `status` VARCHAR(50) NOT NULL DEFAULT "completed",
        `category` VARCHAR(100) NOT NULL DEFAULT "Other Process",
        `created_by` INT(11) NOT NULL DEFAULT 0,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`),
        KEY `entry_date` (`entry_date`),
        KEY `source_type` (`source_type`),
        KEY `related_type` (`related_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_daily_productivity_scores')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . 'smart_daily_productivity_scores` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `staff_id` INT(11) NOT NULL,
        `score_date` DATE NOT NULL,
        `completed_tasks` INT(11) NOT NULL DEFAULT 0,
        `manual_entries` INT(11) NOT NULL DEFAULT 0,
        `ticket_replies` INT(11) NOT NULL DEFAULT 0,
        `ticket_closed` INT(11) NOT NULL DEFAULT 0,
        `ticket_open` INT(11) NOT NULL DEFAULT 0,
        `project_tasks` INT(11) NOT NULL DEFAULT 0,
        `contract_tasks` INT(11) NOT NULL DEFAULT 0,
        `client_tasks` INT(11) NOT NULL DEFAULT 0,
        `research_tasks` INT(11) NOT NULL DEFAULT 0,
        `other_tasks` INT(11) NOT NULL DEFAULT 0,
        `minutes_total` INT(11) NOT NULL DEFAULT 0,
        `score_percent` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `rating_label` VARCHAR(50) NOT NULL DEFAULT "Not Rated",
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `staff_score_date` (`staff_id`, `score_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_daily_productivity_logs')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . 'smart_daily_productivity_logs` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `staff_id` INT(11) NOT NULL DEFAULT 0,
        `action` VARCHAR(100) NOT NULL,
        `message` TEXT NULL,
        `ip_address` VARCHAR(45) NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

$options = [
    'smart_daily_productivity_enabled' => '1',
    'smart_daily_productivity_version' => '1.0.2',
    'smart_daily_productivity_daily_target_minutes' => '480',
    'smart_daily_productivity_daily_target_tasks' => '8',
    'smart_daily_productivity_include_tickets' => '1',
    'smart_daily_productivity_allow_employee_manual_entries' => '1',
    'smart_daily_productivity_allow_employee_reports' => '0',
    'smart_daily_productivity_allow_department_reports' => '1',
    'smart_daily_productivity_score_high' => '85',
    'smart_daily_productivity_score_low' => '55',
];

foreach ($options as $name => $value) {
    if (get_option($name) === false) {
        add_option($name, $value);
    }
}

if (function_exists('smart_daily_productivity_write_log')) {
    smart_daily_productivity_write_log('module_installed', 'Module install or upgrade logic completed safely.', (int) get_staff_user_id());
}

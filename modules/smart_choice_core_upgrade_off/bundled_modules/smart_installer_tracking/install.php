<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->dbforge();

if (!$CI->db->table_exists(db_prefix() . 'smart_installer_trips')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . "smart_installer_trips` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `appointment_id` INT(11) NULL DEFAULT NULL,
        `project_id` INT(11) NULL DEFAULT NULL,
        `client_id` INT(11) NULL DEFAULT NULL,
        `staff_id` INT(11) NOT NULL,
        `department_id` INT(11) NULL DEFAULT NULL,
        `status` VARCHAR(40) NOT NULL DEFAULT 'scheduled',
        `client_name` VARCHAR(191) NULL DEFAULT NULL,
        `client_phone` VARCHAR(80) NULL DEFAULT NULL,
        `client_email` VARCHAR(191) NULL DEFAULT NULL,
        `destination_address` TEXT NULL,
        `destination_lat` DECIMAL(10,8) NULL DEFAULT NULL,
        `destination_lng` DECIMAL(11,8) NULL DEFAULT NULL,
        `started_at` DATETIME NULL DEFAULT NULL,
        `accepted_at` DATETIME NULL DEFAULT NULL,
        `arrived_at` DATETIME NULL DEFAULT NULL,
        `completed_at` DATETIME NULL DEFAULT NULL,
        `cancelled_at` DATETIME NULL DEFAULT NULL,
        `last_lat` DECIMAL(10,8) NULL DEFAULT NULL,
        `last_lng` DECIMAL(11,8) NULL DEFAULT NULL,
        `last_accuracy` DECIMAL(10,2) NULL DEFAULT NULL,
        `last_speed` DECIMAL(10,2) NULL DEFAULT NULL,
        `last_heading` DECIMAL(10,2) NULL DEFAULT NULL,
        `last_location_at` DATETIME NULL DEFAULT NULL,
        `eta_minutes` INT(11) NULL DEFAULT NULL,
        `eta_text` VARCHAR(100) NULL DEFAULT NULL,
        `distance_text` VARCHAR(100) NULL DEFAULT NULL,
        `tracking_token` VARCHAR(64) NOT NULL,
        `tracking_enabled` TINYINT(1) NOT NULL DEFAULT 1,
        `notification_sent_at` DATETIME NULL DEFAULT NULL,
        `created_by` INT(11) NULL DEFAULT NULL,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`),
        KEY `appointment_id` (`appointment_id`),
        KEY `project_id` (`project_id`),
        KEY `tracking_token` (`tracking_token`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_installer_locations')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . "smart_installer_locations` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `trip_id` INT(11) NOT NULL,
        `staff_id` INT(11) NOT NULL,
        `latitude` DECIMAL(10,8) NOT NULL,
        `longitude` DECIMAL(11,8) NOT NULL,
        `accuracy` DECIMAL(10,2) NULL DEFAULT NULL,
        `speed` DECIMAL(10,2) NULL DEFAULT NULL,
        `heading` DECIMAL(10,2) NULL DEFAULT NULL,
        `source` VARCHAR(40) NOT NULL DEFAULT 'browser',
        `ip_address` VARCHAR(80) NULL DEFAULT NULL,
        `user_agent` VARCHAR(255) NULL DEFAULT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `trip_id` (`trip_id`),
        KEY `staff_id` (`staff_id`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_installer_notifications')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . "smart_installer_notifications` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `trip_id` INT(11) NOT NULL,
        `staff_id` INT(11) NULL DEFAULT NULL,
        `client_id` INT(11) NULL DEFAULT NULL,
        `channel` VARCHAR(40) NOT NULL DEFAULT 'email',
        `recipient` VARCHAR(191) NULL DEFAULT NULL,
        `subject` VARCHAR(191) NULL DEFAULT NULL,
        `message` TEXT NULL,
        `status` VARCHAR(40) NOT NULL DEFAULT 'pending',
        `error_message` TEXT NULL,
        `sent_at` DATETIME NULL DEFAULT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `trip_id` (`trip_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'smart_installer_logs')) {
    $CI->db->query('CREATE TABLE IF NOT EXISTS `' . db_prefix() . "smart_installer_logs` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `staff_id` INT(11) NULL DEFAULT NULL,
        `action` VARCHAR(191) NOT NULL,
        `description` TEXT NULL,
        `ip_address` VARCHAR(80) NULL DEFAULT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

$options = [
    'smart_installer_tracking_enabled' => '1',
    'smart_installer_tracking_google_maps_api_key' => '',
    'smart_installer_tracking_google_distance_api_enabled' => '0',
    'smart_installer_tracking_default_eta_minutes' => '30',
    'smart_installer_tracking_location_refresh_seconds' => '30',
    'smart_installer_tracking_client_refresh_seconds' => '45',
    'smart_installer_tracking_allow_client_live_map' => '1',
    'smart_installer_tracking_notify_by_email' => '1',
    'smart_installer_tracking_notify_by_sms' => '0',
    'smart_installer_tracking_company_phone' => '727-755-3786',
    'smart_installer_tracking_public_link_hours' => '12',
    'smart_installer_tracking_ip_allowlist' => '',
    'smart_installer_tracking_module_version' => '1.0.3',
];

foreach ($options as $name => $value) {
    if (get_option($name) === false) {
        add_option($name, $value);
    }
}

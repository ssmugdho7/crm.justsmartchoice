<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'snm_devices')) {
    $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "snm_devices` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `owner_name` varchar(191) NULL,
        `device_name` varchar(191) NULL,
        `device_type` varchar(100) NULL,
        `ip_address` varchar(100) NULL,
        `mac_address` varchar(100) NOT NULL,
        `manufacturer` varchar(191) NULL,
        `group_name` varchar(100) NULL,
        `status` varchar(50) DEFAULT 'unknown',
        `is_blocked` tinyint(1) DEFAULT 0,
        `notes` text NULL,
        `last_seen` datetime NULL,
        `datecreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `mac_address` (`mac_address`),
        KEY `group_name` (`group_name`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . 'snm_schedules')) {
    $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "snm_schedules` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `device_id` int(11) NULL,
        `group_name` varchar(100) NULL,
        `schedule_name` varchar(191) NOT NULL,
        `days` varchar(191) NULL,
        `start_time` time NULL,
        `end_time` time NULL,
        `action` varchar(50) DEFAULT 'block',
        `active` tinyint(1) DEFAULT 1,
        `datecreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `device_id` (`device_id`),
        KEY `active` (`active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

if (!$CI->db->table_exists(db_prefix() . 'snm_logs')) {
    $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "snm_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `level` varchar(50) NOT NULL DEFAULT 'info',
        `event_type` varchar(100) NOT NULL,
        `message` text NOT NULL,
        `data` longtext NULL,
        `staff_id` int(11) NULL,
        `datecreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `level` (`level`),
        KEY `event_type` (`event_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
}

$columns = [
    'snm_devices' => [
        'owner_name' => "ALTER TABLE `" . db_prefix() . "snm_devices` ADD `owner_name` varchar(191) NULL AFTER `id`",
        'notes' => "ALTER TABLE `" . db_prefix() . "snm_devices` ADD `notes` text NULL AFTER `is_blocked`",
    ],
];

foreach ($columns as $table => $defs) {
    foreach ($defs as $column => $sql) {
        if (!$CI->db->field_exists($column, db_prefix() . $table)) {
            $CI->db->query($sql);
        }
    }
}

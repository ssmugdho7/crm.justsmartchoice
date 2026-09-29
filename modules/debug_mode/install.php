<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('debug_mode_enabled', '0');
add_option('debug_mode_client_visible', '0');
add_option('debug_mode_log_level', 'basic');
add_option('debug_mode_allowed_roles', '');
add_option('debug_mode_allowed_staff', '');
add_option('debug_mode_phpmyadmin_url', 'https://s111.bluehost.com:2083/');
add_option('debug_mode_client_portal_enabled', '1');
add_option('debug_mode_show_admin_banner', '1');
add_option('debug_mode_cache_last_cleared', '');
add_option('debug_mode_version', '2.1.6');
add_option('debug_mode_access_control_enabled', '0');

$CI =& get_instance();

if (!$CI->db->table_exists(db_prefix() . 'debug_mode_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'debug_mode_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `level` varchar(50) NULL,
        `message` text NULL,
        `context` mediumtext NULL,
        `staff_id` int(11) NULL,
        `ip_address` varchar(100) NULL,
        `created_at` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `level` (`level`),
        KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}


if (!$CI->db->table_exists(db_prefix() . 'debug_mode_monitors')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'debug_mode_monitors` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(191) NOT NULL,
        `url` text NOT NULL,
        `expected_code` int(11) NOT NULL DEFAULT 200,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `last_status` varchar(20) NULL,
        `last_code` int(11) NULL,
        `last_latency_ms` decimal(10,2) NULL,
        `last_checked_at` datetime NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'debug_mode_ip_rules')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'debug_mode_ip_rules` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `label` varchar(191) NOT NULL,
        `ip_address` varchar(100) NOT NULL,
        `access_from` time NULL,
        `access_until` time NULL,
        `days` varchar(50) NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `ip_address` (`ip_address`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

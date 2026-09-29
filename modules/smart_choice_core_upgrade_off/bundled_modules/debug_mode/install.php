<?php

defined('BASEPATH') or exit('No direct script access allowed');

add_option('debug_mode_enabled', '0');
add_option('debug_mode_client_visible', '0');
add_option('debug_mode_log_level', 'basic');
add_option('debug_mode_allowed_roles', '');
add_option('debug_mode_allowed_staff', '');
add_option('debug_mode_phpmyadmin_url', 'https://s111.bluehost.com:2083/cpsess2451663978/3rdparty/phpMyAdmin/index.php');
add_option('debug_mode_client_portal_enabled', '1');
add_option('debug_mode_show_admin_banner', '1');
add_option('debug_mode_cache_last_cleared', '');

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

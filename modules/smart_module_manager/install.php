<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'smart_module_manager_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'smart_module_manager_logs` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `staff_id` INT(11) NULL DEFAULT NULL,
        `module_name` VARCHAR(191) NOT NULL,
        `action` VARCHAR(60) NOT NULL,
        `message` TEXT NULL,
        `file_path` TEXT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `module_name` (`module_name`),
        KEY `action` (`action`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

add_option('smart_module_manager_enabled', '1');
add_option('smart_module_manager_allow_unload', '1');
add_option('smart_module_manager_backup_before_unload', '1');
add_option('smart_module_manager_allow_database_cleanup', '0');
add_option('smart_module_manager_export_path', 'uploads/smart_module_manager/exports');

$uploadPath = FCPATH . get_option('smart_module_manager_export_path');
if (!is_dir($uploadPath)) {
    @mkdir($uploadPath, 0755, true);
}

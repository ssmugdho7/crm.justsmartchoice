<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$options = [
    'toast_master_enable' => '1',
    'toaster_style' => '1',
    'toaster_position' => 'top-right',
    'toast_master_sound_enable' => '1',
    'toast_master_sound_name' => 'soft_click',
    'toast_master_volume' => '80',
    'toast_master_duration' => '4500',
    'toast_master_pause_on_hover' => '1',
    'toast_master_progress_bar' => '1',
    'toast_master_intercept_alert_float' => '1',
    'toast_master_intercept_browser_alert' => '1',
    'toast_master_intercept_unsaved_warning' => '0',
    'toast_master_intercept_ajax' => '1',
    'toast_master_intercept_validation' => '1',
    'toast_master_intercept_messages' => '1',
    'toast_master_intercept_announcements' => '1',
    'toast_master_intercept_module_updates' => '1',
    'toast_master_intercept_cron' => '1',
    'toast_master_history_enable' => '1',
    'toast_master_animation' => 'fade',
    'toast_master_version' => '2.0.2',
];

foreach ($options as $name => $value) {
    if (get_option($name) === '') {
        add_option($name, $value);
    } else {
        update_option($name, get_option($name) ?: $value);
    }
}

if (!$CI->db->table_exists(db_prefix() . 'toast_master_history')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'toast_master_history` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `staffid` INT(11) NULL DEFAULT NULL,
        `contact_id` INT(11) NULL DEFAULT NULL,
        `area` VARCHAR(50) NULL DEFAULT NULL,
        `source` VARCHAR(100) NULL DEFAULT NULL,
        `type` VARCHAR(30) NOT NULL DEFAULT "info",
        `title` VARCHAR(191) NULL DEFAULT NULL,
        `message` TEXT NULL,
        `url` TEXT NULL,
        `is_read` TINYINT(1) NOT NULL DEFAULT 0,
        `datecreated` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `staffid` (`staffid`),
        KEY `contact_id` (`contact_id`),
        KEY `type` (`type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

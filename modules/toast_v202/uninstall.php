<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$options = [
    'toast_master_enable',
    'toaster_style',
    'toaster_position',
    'toast_master_sound_enable',
    'toast_master_sound_name',
    'toast_master_volume',
    'toast_master_duration',
    'toast_master_pause_on_hover',
    'toast_master_progress_bar',
    'toast_master_animation',
    'toast_master_intercept_alert_float',
    'toast_master_intercept_browser_alert',
    'toast_master_intercept_unsaved_warning',
    'toast_master_intercept_ajax',
    'toast_master_intercept_validation',
    'toast_master_intercept_messages',
    'toast_master_intercept_announcements',
    'toast_master_intercept_module_updates',
    'toast_master_intercept_cron',
    'toast_master_history_enable',
    'toast_master_version',
    'toast_master_purchase_code',
    'toast_master_purchase_is_valid',
];

foreach ($options as $option) {
    delete_option($option);
}

if ($CI->db->table_exists(db_prefix() . 'toast_master_history')) {
    $CI->db->query('DROP TABLE `' . db_prefix() . 'toast_master_history`');
}

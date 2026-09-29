<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_210 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        $notifications = db_prefix() . 'appointly_smartchoice_notifications';
        if (!$CI->db->table_exists($notifications)) {
            $CI->db->query("CREATE TABLE `{$notifications}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `appointment_id` INT(11) NULL DEFAULT NULL,
                `staff_id` INT(11) NULL DEFAULT NULL,
                `channel` VARCHAR(50) NOT NULL DEFAULT 'crm',
                `event_type` VARCHAR(100) NOT NULL DEFAULT 'appointment_notice',
                `message` TEXT NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
                `created_at` DATETIME NOT NULL,
                `sent_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `appointment_id` (`appointment_id`),
                KEY `staff_id` (`staff_id`),
                KEY `channel` (`channel`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        $bridge = db_prefix() . 'appointly_installer_tracking_bridge';
        if (!$CI->db->table_exists($bridge)) {
            $CI->db->query("CREATE TABLE `{$bridge}` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `appointment_id` INT(11) NOT NULL,
                `staff_id` INT(11) NULL DEFAULT NULL,
                `project_id` INT(11) NULL DEFAULT NULL,
                `client_id` INT(11) NULL DEFAULT NULL,
                `tracking_token` VARCHAR(191) NULL DEFAULT NULL,
                `tracking_status` VARCHAR(50) NOT NULL DEFAULT 'not_started',
                `last_latitude` VARCHAR(50) NULL DEFAULT NULL,
                `last_longitude` VARCHAR(50) NULL DEFAULT NULL,
                `last_update` DATETIME NULL DEFAULT NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `appointment_id` (`appointment_id`),
                KEY `staff_id` (`staff_id`),
                KEY `project_id` (`project_id`),
                KEY `client_id` (`client_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        $options = [
            'appointly_sc_daily_popup_enabled' => '1',
            'appointly_sc_sound_enabled' => '1',
            'appointly_sc_daily_staff_notification_enabled' => '1',
            'appointly_sc_one_hour_reminder_enabled' => '1',
            'appointly_sc_email_enabled' => '1',
            'appointly_sc_telegram_enabled' => '0',
            'appointly_sc_telegram_bot_token' => '',
            'appointly_sc_telegram_default_chat_id' => '',
            'appointly_sc_popup_auto_close_seconds' => '10',
            'appointly_sc_reminder_minutes_before' => '60',
            'appointly_sc_dispatch_department' => 'Service',
            'appointly_sc_installer_tracking_enabled' => '1',
            'appointly_sc_customer_tracking_enabled' => '1',
            'appointly_sc_customer_tracking_portal_slug' => 'installer-tracking',
            'appointly_sc_tracking_eta_label' => 'Installer ETA',
            'appointly_smart_choice_version' => '2.1.2',
            'appointly_sc_show_public_logo' => '1',
            'appointly_sc_public_logo_width' => '110',
            'appointly_sc_public_logo_height' => '38',
            'appointly_sc_compact_ui_enabled' => '1',
            'appointly_sc_single_scroll_enabled' => '1',
        ];

        foreach ($options as $name => $value) {
            if (get_option($name) === false) {
                add_option($name, $value);
            } else {
                update_option($name, $value);
            }
        }
    }
}

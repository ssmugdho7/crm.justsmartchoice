<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'telegram_info')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'telegram_info` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT NOT NULL,
        `bot_token` VARCHAR(255) NOT NULL,
        `chat_id` VARCHAR(255) NOT NULL,
        `bot_username` VARCHAR(191) NULL,
        `created_at` DATETIME NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
} else {
    foreach ([
        'bot_username' => 'VARCHAR(191) NULL AFTER `chat_id`',
    ] as $field => $definition) {
        if (!$CI->db->field_exists($field, db_prefix() . 'telegram_info')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'telegram_info` ADD `' . $field . '` ' . $definition);
        }
    }
}

if (!$CI->db->table_exists(db_prefix() . 'telegram_chat_messages')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'telegram_chat_messages` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `staff_id` INT NULL,
        `chat_id` VARCHAR(255) NULL,
        `message_direction` VARCHAR(20) NOT NULL DEFAULT "outgoing",
        `module` VARCHAR(100) NULL,
        `action` VARCHAR(100) NULL,
        `record_id` VARCHAR(100) NULL,
        `related_id` VARCHAR(100) NULL,
        `telegram_message_id` VARCHAR(100) NULL,
        `sender_name` VARCHAR(191) NULL,
        `message_text` MEDIUMTEXT NULL,
        `raw_payload` MEDIUMTEXT NULL,
        `is_read` TINYINT(1) NOT NULL DEFAULT 0,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`),
        KEY `chat_id` (`chat_id`),
        KEY `module` (`module`),
        KEY `action` (`action`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

if (!$CI->db->table_exists(db_prefix() . 'telegram_appointment_tracking')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'telegram_appointment_tracking` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `appointment_id` INT NOT NULL,
        `payload_hash` VARCHAR(40) NOT NULL,
        `last_status` VARCHAR(100) NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `appointment_id` (`appointment_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

if (!$CI->db->table_exists(db_prefix() . 'telegram_scheduled_messages')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'telegram_scheduled_messages` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `chat_id` VARCHAR(255) NOT NULL,
        `message_text` MEDIUMTEXT NOT NULL,
        `scheduled_at` DATETIME NOT NULL,
        `sent_at` DATETIME NULL,
        `status` VARCHAR(20) NOT NULL DEFAULT "pending",
        `created_by` INT NULL,
        `created_at` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `scheduled_at` (`scheduled_at`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

$options = [
    'telegram_chat_enabled' => '1',
    'telegram_chat_enable_message_log' => '1',
    'telegram_chat_enable_client_login_notice' => '1',
    'telegram_chat_enable_ticket_notice' => '1',
    'telegram_chat_enable_proposal_notice' => '1',
    'telegram_chat_enable_file_notice' => '1',
    'telegram_chat_enable_portal_message_notice' => '1',
    'telegram_chat_enable_appointment_monitor' => '1',
    'telegram_chat_notify_new_appointments' => '1',
    'telegram_chat_notify_appointment_changes' => '1',
    'telegram_chat_default_chat_id' => '',
    'telegram_chat_getupdates_offset' => '0',
    'telegram_chat_last_appointment_scan' => '',
    'telegram_chat_store_url' => '',
    'telegram_chat_mini_app_url' => '',
    'telegram_chat_support_url' => '',
    'telegram_chat_privacy_url' => '',
    'telegram_chat_terms_url' => '',
    'telegram_chat_webhook_secret' => bin2hex(random_bytes(16)),
];

foreach ($options as $name => $value) {
    if (get_option($name) === '') {
        add_option($name, $value);
    }
}

update_option('telegram_chat_version', '2.0.2');

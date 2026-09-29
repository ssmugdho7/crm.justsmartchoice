<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'telegram_info')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'telegram_info` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `bot_token` varchar(225) NOT NULL,
        `chat_id` varchar(255) NOT NULL,
        `created_at` datetime NULL,
        `updated_at` datetime NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'telegram_chat_messages')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'telegram_chat_messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `staff_id` int(11) NULL,
        `chat_id` varchar(255) NULL,
        `message_direction` varchar(20) NOT NULL DEFAULT "outgoing",
        `module` varchar(100) NULL,
        `action` varchar(100) NULL,
        `record_id` varchar(100) NULL,
        `related_id` varchar(100) NULL,
        `telegram_message_id` varchar(100) NULL,
        `sender_name` varchar(191) NULL,
        `message_text` mediumtext NULL,
        `raw_payload` mediumtext NULL,
        `is_read` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`),
        KEY `chat_id` (`chat_id`),
        KEY `module` (`module`),
        KEY `action` (`action`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

$options = [
    'telegram_chat_enable_message_log' => '1',
    'telegram_chat_enable_client_login_notice' => '1',
    'telegram_chat_enable_ticket_notice' => '1',
    'telegram_chat_enable_proposal_notice' => '1',
    'telegram_chat_enable_file_notice' => '1',
    'telegram_chat_enable_portal_message_notice' => '1',
    'telegram_chat_getupdates_offset' => '0',
];

foreach ($options as $name => $value) {
    if (get_option($name) === '') {
        add_option($name, $value);
    }
}

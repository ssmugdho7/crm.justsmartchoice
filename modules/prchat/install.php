<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PRChat Module Installation
 * 
 * Handles fresh installations and creates all necessary tables and options.
 */

$CI = &get_instance();
$CI->load->helper('prchat/prchat_chatbot_v200_schema');

// ===========================================
// Create upload directories
// ===========================================
$upload_dirs = [
  PR_CHAT_MODULE_UPLOAD_FOLDER,
  PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER,
  PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER,
  FCPATH . 'uploads/chatbot_files',
  PR_CHAT_MEDIA_PROJECTS_FOLDER,
];

foreach ($upload_dirs as $dir) {
  if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
    $fp = fopen($dir . '/index.html', 'w');
    fclose($fp);
  }
}

// ===========================================
// Legacy Chat System Options
// ===========================================
$chat_options = [
  'pusher_chat_enabled' => 1,
  'chat_desktop_messages_notifications' => 1,
  'chat_floating_notifications_enabled' => '1',
  'chat_members_can_create_groups' => 1,
  'chat_client_enabled' => 1,
  'chat_allow_staff_to_create_tickets' => 1,
  'prchat_toggled_chat_disabled' => '1',

  'chat_client_widget_position' => 'right',
  'chat_client_widget_primary_color' => '#4f46e5',
  'chat_client_widget_show_logo' => '1',
  'chat_client_widget_show_staff_names' => '1',
  'chat_client_widget_show_staff_roles' => '1',
  'chat_client_staff_visibility' => 'assigned_and_responded',

  'chat_client_calls_enabled' => '0',
  'chat_staff_calls_enabled' => '1',
  'chat_calls_video_enabled' => '1',

  'chat_staff_can_access_clients' => '1',
  'prchat_max_upload_size_kb' => '204800',
  'prchat_copy_group_uploads_to_project_media' => '1',
  'prchat_smart_message_sound_enabled' => '1',
  'prchat_smart_center_toast_enabled' => '1',
  'prchat_smart_task_from_message_enabled' => '1',
  'prchat_smart_project_photo_actions_enabled' => '1',
  'prchat_twilio_enabled' => '0',
  'prchat_twilio_account_sid' => '',
  'prchat_twilio_auth_token' => '',
  'prchat_twilio_phone_number' => '',
  'prchat_twilio_voice_webhook' => '',
  'prchat_twilio_video_enabled' => '0',
];

foreach ($chat_options as $option_name => $default_value) {
  add_option($option_name, $default_value);
}

// ===========================================
// Legacy Chat Tables (Staff-to-Staff, Groups, Clients)
// ===========================================
if (!$CI->db->table_exists(TABLE_CHATMESSAGES)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATMESSAGES . "` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `sender_id` INT(11) NOT NULL,
            `reciever_id` INT(11) NOT NULL,
            `message` LONGTEXT NOT NULL,
            `reactions` TEXT NULL DEFAULT NULL,
            `viewed` INT(11) DEFAULT '0',
            `time_sent` DATETIME NULL DEFAULT NULL,
            `viewed_at` DATETIME NULL DEFAULT NULL,
            `edited_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATSETTINGS)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATSETTINGS . "` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `value` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATGROUPS)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATGROUPS . "` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `created_by_id` INT(11) NOT NULL,
            `group_name` VARCHAR(255) NOT NULL,
            `related_type` VARCHAR(50) NULL DEFAULT NULL,
            `related_id` INT(11) UNSIGNED NULL DEFAULT NULL,
            `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_related` (`related_type`, `related_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATGROUPMEMBERS)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATGROUPMEMBERS . "` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `group_id` INT(11) NOT NULL,
            `member_id` INT(11) NOT NULL,
            `group_name` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_unique_member` (`group_id`, `member_id`),
            KEY `group_id` (`group_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATGROUPMESSAGES)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATGROUPMESSAGES . "` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `group_id` INT(11) NOT NULL,
            `message` LONGTEXT NOT NULL,
            `reactions` TEXT NULL DEFAULT NULL,
            `sender_id` INT(11) NOT NULL,
            `time_sent` DATETIME NULL DEFAULT NULL,
            `edited_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATGROUPSHAREDFILES)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATGROUPSHAREDFILES . "` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `group_id` INT(11) NOT NULL,
            `sender_id` INT(11) NOT NULL,
            `file_name` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

if (!$CI->db->table_exists(TABLE_CHATCLIENTMESSAGES)) {
  $CI->db->query("
        CREATE TABLE `" . TABLE_CHATCLIENTMESSAGES . "` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `sender_id` VARCHAR(20) NOT NULL,
            `reciever_id` VARCHAR(20) NOT NULL,
            `message` LONGTEXT NOT NULL,
            `reactions` TEXT NULL DEFAULT NULL,
            `viewed` TINYINT(11) NOT NULL DEFAULT '0',
            `time_sent` DATETIME NULL DEFAULT NULL,
            `viewed_at` DATETIME NULL DEFAULT NULL,
            `edited_at` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . "
    ");
}

// ===========================================
// Smart Choice PRChat message visibility (scoped delete)
// ===========================================
$prchatVisibilityTable = db_prefix() . 'prchat_message_visibility';
if (!$CI->db->table_exists($prchatVisibilityTable)) {
  $CI->db->query("CREATE TABLE `{$prchatVisibilityTable}` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `message_type` VARCHAR(16) NOT NULL,
    `message_id` INT UNSIGNED NOT NULL,
    `viewer_type` VARCHAR(16) NOT NULL,
    `viewer_id` INT UNSIGNED NOT NULL,
    `created_by` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_visibility` (`message_type`,`message_id`,`viewer_type`,`viewer_id`),
    KEY `viewer_lookup` (`viewer_type`,`viewer_id`,`message_type`,`message_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// ===========================================
// New Chatbot System (tables + options + seed data)
// ===========================================
prchat_create_chatbot_tables();
prchat_ensure_options(prchat_chatbot_options());
prchat_seed_default_chatbot();


// ===========================================
// Smart Choice v207 safe upgrades
// ===========================================
if ($CI->db->table_exists(TABLE_CHATGROUPS) && !$CI->db->field_exists('group_image', TABLE_CHATGROUPS)) {
  $CI->db->query("ALTER TABLE `" . TABLE_CHATGROUPS . "` ADD `group_image` VARCHAR(255) NULL DEFAULT NULL AFTER `group_name`");
}

$smart_choice_chat_v207_options = [
  'prchat_smart_message_sound_enabled' => '1',
  'prchat_smart_center_toast_enabled' => '1',
  'prchat_smart_task_from_message_enabled' => '1',
  'prchat_smart_project_photo_actions_enabled' => '1',
  'prchat_twilio_enabled' => '0',
  'prchat_twilio_account_sid' => '',
  'prchat_twilio_auth_token' => '',
  'prchat_twilio_phone_number' => '',
  'prchat_twilio_voice_webhook' => '',
  'prchat_twilio_video_enabled' => '0',
];
foreach ($smart_choice_chat_v207_options as $option_name => $default_value) {
  add_option($option_name, $default_value);
}


// Smart Choice PRChat 2.2.0: SMS audit and core-AI integration defaults.
foreach ([
  'prchat_sms_enabled' => '0',
  'prchat_sms_provider' => 'twilio',
  'prchat_sms_status_callback' => '',
  'prchat_ai_use_core_settings' => '1',
] as $optionName => $optionValue) {
  if (get_option($optionName) === false) { add_option($optionName, $optionValue); }
}
$prchatSmsTable = db_prefix() . 'prchat_sms_log';
if (!$CI->db->table_exists($prchatSmsTable)) {
  $CI->db->query("CREATE TABLE `{$prchatSmsTable}` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `staff_id` INT UNSIGNED NOT NULL,
    `recipient_staff_id` INT UNSIGNED NOT NULL,
    `to_number` VARCHAR(40) NOT NULL,
    `from_number` VARCHAR(40) NULL,
    `message` TEXT NOT NULL,
    `provider` VARCHAR(30) NOT NULL DEFAULT 'twilio',
    `provider_message_id` VARCHAR(191) NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'queued',
    `error_message` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`), KEY `recipient_staff_id` (`recipient_staff_id`), KEY `status` (`status`), KEY `created_at` (`created_at`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

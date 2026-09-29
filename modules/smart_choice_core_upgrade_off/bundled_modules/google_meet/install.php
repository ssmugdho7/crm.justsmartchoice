<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI =& get_instance();

add_option('google_meet_enabled', '1');
add_option('google_meet_google_api_key', '');
add_option('google_meet_google_access_token', '');
add_option('google_meet_calendar_id', 'primary');
add_option('google_meet_timezone', 'America/New_York');
add_option('google_meet_default_duration', '30');
add_option('google_meet_notify_staff_default', '1');
add_option('google_meet_notify_customers_default', '1');
add_option('google_meet_send_invitations_default', '1');
add_option('google_meet_auto_create_link', '1');
add_option('google_meet_use_google_calendar_api', '0');
add_option('google_meet_allow_placeholder_links', '1');

$table = db_prefix() . 'google_meet_meetings';
if (!$CI->db->table_exists($table)) {
    $CI->db->query('CREATE TABLE `' . $table . '` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(191) NULL,
        `subject` varchar(191) NULL,
        `description` text NULL,
        `notes` text NULL,
        `meeting_type` varchar(50) NOT NULL DEFAULT "scheduled",
        `meet_link` varchar(500) NULL,
        `google_event_id` varchar(191) NULL,
        `google_api_status` varchar(50) NULL,
        `project_id` int(11) NULL,
        `appointment_id` int(11) NULL,
        `start_time` datetime NULL,
        `end_time` datetime NULL,
        `actual_start` datetime NULL,
        `actual_end` datetime NULL,
        `duration_minutes` int(11) NULL DEFAULT 30,
        `created_by` int(11) NULL,
        `assigned_staff_id` int(11) NULL,
        `notify_staff` tinyint(1) NOT NULL DEFAULT 0,
        `notify_customer` tinyint(1) NOT NULL DEFAULT 0,
        `notify_customers` tinyint(1) NOT NULL DEFAULT 0,
        `send_invitations_now` tinyint(1) NOT NULL DEFAULT 0,
        `status` varchar(50) NOT NULL DEFAULT "scheduled",
        `created_at` datetime NULL,
        `updated_at` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `created_by` (`created_by`),
        KEY `assigned_staff_id` (`assigned_staff_id`),
        KEY `status` (`status`),
        KEY `start_time` (`start_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

$columns = [
    'title' => "ALTER TABLE `$table` ADD COLUMN `title` varchar(191) NULL AFTER `id`",
    'subject' => "ALTER TABLE `$table` ADD COLUMN `subject` varchar(191) NULL AFTER `title`",
    'description' => "ALTER TABLE `$table` ADD COLUMN `description` text NULL AFTER `subject`",
    'notes' => "ALTER TABLE `$table` ADD COLUMN `notes` text NULL AFTER `description`",
    'meeting_type' => "ALTER TABLE `$table` ADD COLUMN `meeting_type` varchar(50) NOT NULL DEFAULT 'scheduled' AFTER `notes`",
    'google_event_id' => "ALTER TABLE `$table` ADD COLUMN `google_event_id` varchar(191) NULL AFTER `meet_link`",
    'google_api_status' => "ALTER TABLE `$table` ADD COLUMN `google_api_status` varchar(50) NULL AFTER `google_event_id`",
    'project_id' => "ALTER TABLE `$table` ADD COLUMN `project_id` int(11) NULL AFTER `google_api_status`",
    'appointment_id' => "ALTER TABLE `$table` ADD COLUMN `appointment_id` int(11) NULL AFTER `project_id`",
    'actual_start' => "ALTER TABLE `$table` ADD COLUMN `actual_start` datetime NULL AFTER `end_time`",
    'actual_end' => "ALTER TABLE `$table` ADD COLUMN `actual_end` datetime NULL AFTER `actual_start`",
    'assigned_staff_id' => "ALTER TABLE `$table` ADD COLUMN `assigned_staff_id` int(11) NULL AFTER `created_by`",
    'notify_customer' => "ALTER TABLE `$table` ADD COLUMN `notify_customer` tinyint(1) NOT NULL DEFAULT 0 AFTER `notify_staff`",
    'notify_customers' => "ALTER TABLE `$table` ADD COLUMN `notify_customers` tinyint(1) NOT NULL DEFAULT 0 AFTER `notify_customer`",
    'send_invitations_now' => "ALTER TABLE `$table` ADD COLUMN `send_invitations_now` tinyint(1) NOT NULL DEFAULT 0 AFTER `notify_customers`",
];
foreach ($columns as $column => $sql) {
    if (!$CI->db->field_exists($column, $table)) {
        $CI->db->query($sql);
    }
}

if ($CI->db->field_exists('title', $table) && $CI->db->field_exists('subject', $table)) {
    $CI->db->query("UPDATE `$table` SET `subject` = `title` WHERE (`subject` IS NULL OR `subject` = '') AND `title` IS NOT NULL AND `title` != ''");
    $CI->db->query("UPDATE `$table` SET `title` = `subject` WHERE (`title` IS NULL OR `title` = '') AND `subject` IS NOT NULL AND `subject` != ''");
}

$attendees = db_prefix() . 'google_meet_attendees';
if (!$CI->db->table_exists($attendees)) {
    $CI->db->query('CREATE TABLE `' . $attendees . '` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `meeting_id` int(11) NOT NULL,
        `attendee_type` varchar(50) NOT NULL DEFAULT "staff",
        `staff_id` int(11) NULL,
        `contact_id` int(11) NULL,
        `email` varchar(191) NULL,
        `name` varchar(191) NULL,
        `notified` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `meeting_id` (`meeting_id`),
        KEY `staff_id` (`staff_id`),
        KEY `contact_id` (`contact_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

$logs = db_prefix() . 'google_meet_logs';
if (!$CI->db->table_exists($logs)) {
    $CI->db->query('CREATE TABLE `' . $logs . '` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `meeting_id` int(11) NULL,
        `action` varchar(100) NULL,
        `message` text NULL,
        `created_by` int(11) NULL,
        `created_at` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `meeting_id` (`meeting_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

$comments = db_prefix() . 'google_meet_comments';
if (!$CI->db->table_exists($comments)) {
    $CI->db->query('CREATE TABLE `' . $comments . '` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `meeting_id` int(11) NOT NULL,
        `comment` text NOT NULL,
        `created_by` int(11) NULL,
        `created_at` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `meeting_id` (`meeting_id`),
        KEY `created_by` (`created_by`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

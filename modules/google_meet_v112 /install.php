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
add_option('google_meet_allow_placeholder_links', '0');

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


/* Smart Choice Google Meet v1.0.7 settings and schema */
add_option('google_meet_access_mode', 'trusted');
add_option('google_meet_guest_access_note', 'Google Meet access is controlled by Google account/calendar settings when the real API is used.');
add_option('google_meet_quick_access', '1');
add_option('google_meet_waiting_room', '0');
add_option('google_meet_allow_screen_sharing', '1');
add_option('google_meet_allow_chat', '1');
add_option('google_meet_allow_recording', '0');
add_option('google_meet_twilio_enabled', '0');
add_option('google_meet_telegram_enabled', '0');
add_option('google_meet_push_enabled', '1');
add_option('google_meet_email_enabled', '1');
add_option('google_meet_client_portal_enabled', '1');
add_option('google_meet_client_portal_title', 'My Video Meetings');

if ($CI->db->table_exists($table)) {
    $smartChoiceColumns = [
        'access_mode' => "ALTER TABLE `$table` ADD COLUMN `access_mode` varchar(50) NULL DEFAULT 'trusted' AFTER `meeting_type`",
        'quick_access' => "ALTER TABLE `$table` ADD COLUMN `quick_access` tinyint(1) NOT NULL DEFAULT 1 AFTER `access_mode`",
        'waiting_room' => "ALTER TABLE `$table` ADD COLUMN `waiting_room` tinyint(1) NOT NULL DEFAULT 0 AFTER `quick_access`",
        'allow_chat' => "ALTER TABLE `$table` ADD COLUMN `allow_chat` tinyint(1) NOT NULL DEFAULT 1 AFTER `waiting_room`",
        'allow_screen_sharing' => "ALTER TABLE `$table` ADD COLUMN `allow_screen_sharing` tinyint(1) NOT NULL DEFAULT 1 AFTER `allow_chat`",
        'allow_recording' => "ALTER TABLE `$table` ADD COLUMN `allow_recording` tinyint(1) NOT NULL DEFAULT 0 AFTER `allow_screen_sharing`",
        'notification_status' => "ALTER TABLE `$table` ADD COLUMN `notification_status` text NULL AFTER `google_api_status`"
    ];
    foreach ($smartChoiceColumns as $column => $sql) {
        if (!$CI->db->field_exists($column, $table)) {
            $CI->db->query($sql);
        }
    }
}


/* Smart Choice Google Meet v1.0.9 notification and AI-ready settings */
add_option('google_meet_sms_enabled', '0');
add_option('google_meet_browser_sound_enabled', '1');
add_option('google_meet_sound_volume', '0.85');
add_option('google_meet_popup_enabled', '1');
add_option('google_meet_recording_instruction', 'Google Meet recording is controlled by Google Workspace/Meet permissions. Enable recording in Google Workspace and paste recording links into meeting notes after the call.');
add_option('google_meet_ai_notes_enabled', '0');
add_option('google_meet_ai_summary_prompt', 'Summarize this meeting with action items, customer decisions, deadlines, and follow-up tasks for Smart Choice Contractors USA.');
add_option('google_meet_default_notification_message', 'You have been invited to a Smart Choice Contractors USA Google Meet meeting.');

if ($CI->db->table_exists($table)) {
    $v109Columns = [
        'recording_url' => "ALTER TABLE `$table` ADD COLUMN `recording_url` varchar(500) NULL AFTER `meet_link`",
        'ai_summary' => "ALTER TABLE `$table` ADD COLUMN `ai_summary` text NULL AFTER `notes`",
        'last_notified_at' => "ALTER TABLE `$table` ADD COLUMN `last_notified_at` datetime NULL AFTER `notification_status`"
    ];
    foreach ($v109Columns as $column => $sql) {
        if (!$CI->db->field_exists($column, $table)) {
            $CI->db->query($sql);
        }
    }
}

$notifications = db_prefix() . 'google_meet_notifications';
if (!$CI->db->table_exists($notifications)) {
    $CI->db->query('CREATE TABLE `' . $notifications . '` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `meeting_id` int(11) NOT NULL,
        `recipient_type` varchar(30) NOT NULL DEFAULT "staff",
        `recipient_id` int(11) NULL,
        `channel` varchar(30) NOT NULL DEFAULT "email",
        `destination` varchar(191) NULL,
        `status` varchar(30) NOT NULL DEFAULT "pending",
        `message` text NULL,
        `created_at` datetime NULL,
        PRIMARY KEY (`id`),
        KEY `meeting_id` (`meeting_id`),
        KEY `recipient_id` (`recipient_id`),
        KEY `channel` (`channel`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}


/* Smart Choice Google Meet v1.1.2 shared-room and native template repair */
update_option('google_meet_allow_placeholder_links', '0');
add_option('google_meet_reminder_minutes', '15');
if ($CI->db->table_exists($table)) {
    $v112Columns = [
        'public_token' => "ALTER TABLE `$table` ADD COLUMN `public_token` varchar(64) NULL AFTER `meet_link`",
        'join_count' => "ALTER TABLE `$table` ADD COLUMN `join_count` int(11) NOT NULL DEFAULT 0 AFTER `duration_minutes`",
    ];
    foreach ($v112Columns as $column => $sql) {
        if (!$CI->db->field_exists($column, $table)) { $CI->db->query($sql); }
    }
    $CI->db->query("UPDATE `$table` SET meet_link='' WHERE TRIM(TRAILING '/' FROM meet_link)='https://meet.google.com/new'");
}
$emailTable = db_prefix() . 'emailtemplates';
if ($CI->db->table_exists($emailTable)) {
    $templates = [
        'english' => [
            'subject' => 'Google Meet Invitation: {meeting_name}',
            'message' => '<div style="font-family:Arial,sans-serif;color:#333;max-width:640px;margin:auto"><p>Dear {recipient_name},</p><p>You have been invited to a Smart Choice Contractors USA video meeting.</p><div style="border-left:4px solid #f97316;background:#f8fafc;padding:15px;border-radius:8px"><p><strong>Meeting:</strong> {meeting_name}</p><p><strong>Start:</strong> {meeting_start}</p></div><p style="text-align:center"><a href="{meeting_link}" style="display:inline-block;background:#169179;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold">Join Google Meet</a></p>{email_signature}</div>',
        ],
        'spanish' => [
            'subject' => 'Invitación de Google Meet: {meeting_name}',
            'message' => '<div style="font-family:Arial,sans-serif;color:#333;max-width:640px;margin:auto"><p>Estimado/a {recipient_name},</p><p>Ha sido invitado/a a una videoconferencia de Smart Choice Contractors USA.</p><div style="border-left:4px solid #f97316;background:#f8fafc;padding:15px;border-radius:8px"><p><strong>Reunión:</strong> {meeting_name}</p><p><strong>Inicio:</strong> {meeting_start}</p></div><p style="text-align:center"><a href="{meeting_link}" style="display:inline-block;background:#169179;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold">Entrar a Google Meet</a></p>{email_signature}</div>',
        ],
    ];
    foreach ($templates as $language => $template) {
        $CI->db->where('slug', 'google-meet-invitation');
        if ($CI->db->field_exists('language', $emailTable)) { $CI->db->where('language', $language); }
        $exists = $CI->db->get($emailTable)->row();
        if (!$exists) {
            $row = ['type'=>'staff','slug'=>'google-meet-invitation','name'=>'Google Meet Invitation','subject'=>$template['subject'],'message'=>$template['message'],'active'=>1];
            if ($CI->db->field_exists('language',$emailTable)) { $row['language']=$language; }
            if ($CI->db->field_exists('fromname',$emailTable)) { $row['fromname']=''; }
            if ($CI->db->field_exists('fromemail',$emailTable)) { $row['fromemail']=''; }
            $CI->db->insert($emailTable,$row);
        }
    }
}

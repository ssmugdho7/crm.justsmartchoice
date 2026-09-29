<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'slf_opportunities')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "slf_opportunities` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `source` VARCHAR(50) NOT NULL DEFAULT 'manual',
        `external_id` VARCHAR(191) NULL,
        `author_name` VARCHAR(191) NULL,
        `author_profile_url` TEXT NULL,
        `contact_email` VARCHAR(191) NULL,
        `contact_phone` VARCHAR(80) NULL,
        `title` VARCHAR(255) NULL,
        `message` LONGTEXT NULL,
        `keyword_matched` VARCHAR(255) NULL,
        `service_type` VARCHAR(120) NULL,
        `city` VARCHAR(120) NULL,
        `state` VARCHAR(50) NULL,
        `post_url` TEXT NULL,
        `ai_score` INT(11) NOT NULL DEFAULT 0,
        `ai_reply_draft` LONGTEXT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'new',
        `lead_id` INT(11) NULL,
        `assigned_staff_id` INT(11) NULL,
        `department_id` INT(11) NULL,
        `created_by` INT(11) NULL,
        `datecreated` DATETIME NOT NULL,
        `updated_at` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `source` (`source`),
        KEY `status` (`status`),
        KEY `lead_id` (`lead_id`),
        KEY `assigned_staff_id` (`assigned_staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'slf_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "slf_logs` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `level` VARCHAR(30) NOT NULL DEFAULT 'info',
        `event_type` VARCHAR(80) NULL,
        `message` TEXT NULL,
        `data` LONGTEXT NULL,
        `staff_id` INT(11) NULL,
        `datecreated` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `level` (`level`),
        KEY `event_type` (`event_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'slf_templates')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "slf_templates` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(191) NOT NULL,
        `source` VARCHAR(80) NOT NULL DEFAULT 'all',
        `language` VARCHAR(40) NOT NULL DEFAULT 'english',
        `body` LONGTEXT NOT NULL,
        `active` TINYINT(1) NOT NULL DEFAULT 1,
        `datecreated` DATETIME NOT NULL,
        PRIMARY KEY (`id`),
        KEY `source` (`source`),
        KEY `active` (`active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

$options = [
    'social_lead_funnel_version' => '1.0.1',
    'social_lead_funnel_enable_facebook' => '0',
    'social_lead_funnel_enable_nextdoor' => '0',
    'social_lead_funnel_enable_ai_drafts' => '1',
    'social_lead_funnel_auto_create_task' => '1',
    'social_lead_funnel_auto_notify_staff' => '1',
    'social_lead_funnel_default_source' => 'Facebook / Nextdoor',
    'social_lead_funnel_keywords' => 'plumber, plumbing, electrician, electrical, remodel, estimate, handyman, contractor, bathroom, kitchen, drywall, painting, stucco, flooring',
    'social_lead_funnel_default_status' => 'new',
    'social_lead_funnel_default_staff_id' => '',
    'social_lead_funnel_default_department_id' => '',
    'social_lead_funnel_facebook_page_id' => '',
    'social_lead_funnel_facebook_access_token' => '',
    'social_lead_funnel_nextdoor_api_key' => '',
    'social_lead_funnel_ai_provider' => 'manual',
    'social_lead_funnel_ai_api_key' => '',
];

foreach ($options as $name => $value) {
    if (get_option($name) === false) {
        add_option($name, $value, 1);
    }
}

$CI->db->where('name', 'General Contractor Reply');
$exists = $CI->db->count_all_results(db_prefix() . 'slf_templates');
if (!$exists) {
    $CI->db->insert(db_prefix() . 'slf_templates', [
        'name' => 'General Contractor Reply',
        'source' => 'all',
        'language' => 'english',
        'body' => "Hi {name}, Smart Choice Contractors USA can help with your project. We can review the work, ask the right questions, and help you avoid costly mistakes. Please call us at 86 or send project details so we can guide you with the next step.",
        'active' => 1,
        'datecreated' => date('Y-m-d H:i:s'),
    ]);
}

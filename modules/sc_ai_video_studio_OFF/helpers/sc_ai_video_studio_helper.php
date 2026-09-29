<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('sc_ai_video_studio_table_exists')) {
    function sc_ai_video_studio_table_exists(string $table): bool
    {
        $CI = &get_instance();
        return $CI->db->table_exists(db_prefix() . $table);
    }
}

if (!function_exists('sc_ai_video_studio_ensure_schema')) {
    function sc_ai_video_studio_ensure_schema(): void
    {
        $CI = &get_instance();
        $charset = $CI->db->char_set ?: 'utf8';
        $collation = $CI->db->dbcollat ?: 'utf8_general_ci';

        $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "sc_video_voices` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `voice_name` VARCHAR(191) NOT NULL,
            `provider_voice_id` VARCHAR(191) NULL DEFAULT NULL,
            `language` VARCHAR(30) NOT NULL DEFAULT 'English',
            `gender` VARCHAR(30) NOT NULL DEFAULT 'Neutral',
            `tone` VARCHAR(80) NOT NULL DEFAULT 'Professional',
            `sample_url` TEXT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_by` INT(11) NOT NULL DEFAULT 0,
            `datecreated` DATETIME NULL DEFAULT NULL,
            `dateupdated` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `language` (`language`),
            KEY `is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "sc_video_avatars` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `avatar_name` VARCHAR(191) NOT NULL,
            `avatar_type` VARCHAR(50) NOT NULL DEFAULT 'Preset',
            `position_name` VARCHAR(80) NOT NULL DEFAULT 'Front',
            `image_url` TEXT NULL,
            `provider_avatar_id` VARCHAR(191) NULL DEFAULT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_by` INT(11) NOT NULL DEFAULT 0,
            `datecreated` DATETIME NULL DEFAULT NULL,
            `dateupdated` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `avatar_type` (`avatar_type`),
            KEY `is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "sc_video_jobs` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(191) NOT NULL,
            `script_text` LONGTEXT NULL,
            `language` VARCHAR(30) NOT NULL DEFAULT 'English',
            `voice_id` INT(11) NOT NULL DEFAULT 0,
            `avatar_id` INT(11) NOT NULL DEFAULT 0,
            `status` VARCHAR(40) NOT NULL DEFAULT 'Draft',
            `logo_enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `logo_position` VARCHAR(50) NOT NULL DEFAULT 'Top Right',
            `intro_thumbnail_url` TEXT NULL,
            `outro_thumbnail_url` TEXT NULL,
            `video_url` TEXT NULL,
            `embed_code` TEXT NULL,
            `provider_job_id` VARCHAR(191) NULL DEFAULT NULL,
            `provider_response` LONGTEXT NULL,
            `created_by` INT(11) NOT NULL DEFAULT 0,
            `datecreated` DATETIME NULL DEFAULT NULL,
            `dateupdated` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `status` (`status`),
            KEY `voice_id` (`voice_id`),
            KEY `avatar_id` (`avatar_id`),
            KEY `created_by` (`created_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "sc_video_text_layers` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `video_id` INT(11) NOT NULL DEFAULT 0,
            `layer_order` INT(11) NOT NULL DEFAULT 1,
            `text_value` TEXT NULL,
            `start_second` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
            `duration_second` DECIMAL(10,2) NOT NULL DEFAULT '3.00',
            `animation` VARCHAR(50) NOT NULL DEFAULT 'Fade In',
            `datecreated` DATETIME NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `video_id` (`video_id`),
            KEY `layer_order` (`layer_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $charset . " COLLATE=" . $collation . ";");

        add_option('sc_ai_video_studio_enabled', '1');
        add_option('sc_ai_video_studio_provider', 'Manual API');
        add_option('sc_ai_video_studio_api_key', '');
        add_option('sc_ai_video_studio_logo_enabled', '1');
        add_option('sc_ai_video_studio_logo_url', 'https://justsmartchoice.com');
        add_option('sc_ai_video_studio_default_language', 'English');
        add_option('sc_ai_video_studio_default_resolution', '1080p');
    }
}

if (!function_exists('sc_ai_video_studio_seed_defaults')) {
    function sc_ai_video_studio_seed_defaults(): void
    {
        $CI = &get_instance();
        sc_ai_video_studio_ensure_schema();
        if (!$CI->db->table_exists(db_prefix() . 'sc_video_voices') || !$CI->db->table_exists(db_prefix() . 'sc_video_avatars')) {
            return;
        }
        if ((int) $CI->db->count_all_results(db_prefix() . 'sc_video_voices') === 0) {
            $voices = [
                ['voice_name' => 'Smart Choice Male', 'language' => 'English', 'gender' => 'Male', 'tone' => 'Professional'],
                ['voice_name' => 'Smart Choice Female', 'language' => 'English', 'gender' => 'Female', 'tone' => 'Friendly'],
                ['voice_name' => 'Spanish Contractor Voice', 'language' => 'Spanish', 'gender' => 'Male', 'tone' => 'Confident'],
            ];
            foreach ($voices as $voice) {
                $voice['created_by'] = get_staff_user_id();
                $voice['datecreated'] = date('Y-m-d H:i:s');
                $CI->db->insert(db_prefix() . 'sc_video_voices', $voice);
            }
        }
        if ((int) $CI->db->count_all_results(db_prefix() . 'sc_video_avatars') === 0) {
            $positions = ['Front', 'Left Angle', 'Right Angle', 'Half Body', 'Close Up', 'Presenter Desk', 'Construction Site', 'Training Room', 'Customer Portal', 'Marketing'];
            foreach ($positions as $position) {
                $CI->db->insert(db_prefix() . 'sc_video_avatars', [
                    'avatar_name' => 'Smart Choice Avatar ' . $position,
                    'avatar_type' => 'Preset',
                    'position_name' => $position,
                    'is_active' => 1,
                    'created_by' => get_staff_user_id(),
                    'datecreated' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}

if (!function_exists('sc_ai_video_studio_nav')) {
    function sc_ai_video_studio_nav(): string
    {
        $links = [
            ['Dashboard', admin_url('sc_ai_video_studio')],
            ['Videos', admin_url('sc_ai_video_studio/videos')],
            ['Voices', admin_url('sc_ai_video_studio/voices')],
            ['Avatars', admin_url('sc_ai_video_studio/avatars')],
            ['Reports', admin_url('sc_ai_video_studio/reports')],
            ['Settings', admin_url('sc_ai_video_studio/settings')],
            ['Health', admin_url('sc_ai_video_studio/health')],
            ['Help', admin_url('sc_ai_video_studio/help')],
        ];
        $html = '<div class="scv-nav">';
        foreach ($links as $link) {
            $html .= '<a class="btn btn-default btn-sm" href="' . $link[1] . '">' . html_escape($link[0]) . '</a>';
        }
        $html .= '</div>';
        return $html;
    }
}

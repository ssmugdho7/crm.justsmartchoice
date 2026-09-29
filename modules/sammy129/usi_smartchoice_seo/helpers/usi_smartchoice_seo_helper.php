<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('usi_smartchoice_seo_table_exists')) {
    function usi_smartchoice_seo_table_exists(string $table): bool
    {
        $CI = &get_instance();
        $tableName = db_prefix() . $table;
        $query = $CI->db->query('SHOW TABLES LIKE ' . $CI->db->escape($tableName));
        return $query !== false && $query->num_rows() > 0;
    }
}

if (!function_exists('usi_smartchoice_seo_column_exists')) {
    function usi_smartchoice_seo_column_exists(string $table, string $column): bool
    {
        $CI = &get_instance();
        $tableName = db_prefix() . $table;
        $query = $CI->db->query('SHOW COLUMNS FROM `' . $CI->db->escape_str($tableName) . '` LIKE ' . $CI->db->escape($column));
        return $query !== false && $query->num_rows() > 0;
    }
}

if (!function_exists('usi_smartchoice_seo_add_column_if_missing')) {
    function usi_smartchoice_seo_add_column_if_missing(string $table, string $column, string $definition): void
    {
        $CI = &get_instance();
        if (!usi_smartchoice_seo_column_exists($table, $column)) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table . '` ADD `' . $CI->db->escape_str($column) . '` ' . $definition);
        }
    }
}

if (!function_exists('usi_smartchoice_seo_default_options')) {
    function usi_smartchoice_seo_default_options(): void
    {
        $defaults = [
            'usi_smartchoice_seo_enabled' => '1',
            'usi_smartchoice_seo_default_status' => 'draft',
            'usi_smartchoice_seo_primary_domain' => 'https://justsmartchoice.com',
            'usi_smartchoice_seo_brand_name' => 'Smart Choice Contractors USA',
            'usi_smartchoice_ai_enabled' => '1',
            'usi_smartchoice_ai_voice_enabled' => '1',
            'usi_smartchoice_ai_camera_enabled' => '1',
            'usi_smartchoice_ai_default_estimate_status' => 'draft',
            'usi_smartchoice_ai_default_currency' => 'USD',
            'usi_smartchoice_ai_require_estimate_approval' => '1',
            'usi_smartchoice_ai_default_tax_percent' => '0',
            'usi_smartchoice_ai_default_overhead_profit_percent' => '10',
            'usi_smartchoice_ai_command_mode' => 'review_first',
            'usi_smartchoice_ai_api_provider' => 'openai',
            'usi_smartchoice_ai_api_key' => '',
            'usi_smartchoice_ai_voice_continuous' => '1',
            'usi_smartchoice_ai_voice_language' => 'en-US',
            'usi_smartchoice_ai_listen_seconds' => '0',
            'usi_smartchoice_ai_mobile_grid_columns' => '4',
            'usi_smartchoice_ai_video_enabled' => '1',
            'usi_smartchoice_ai_video_provider' => 'manual_api',
            'usi_smartchoice_ai_video_api_key' => '',
            'usi_smartchoice_ai_video_default_voice' => 'Smart Choice Trainer',
            'usi_smartchoice_ai_video_logo_enabled' => '1',
            'usi_smartchoice_ai_video_logo_url' => 'https://justsmartchoice.com',
        ];
        foreach ($defaults as $name => $value) {
            if (function_exists('add_option')) {
                add_option($name, $value);
            }
        }
    }
}

if (!function_exists('usi_smartchoice_seo_ensure_schema')) {
    function usi_smartchoice_seo_ensure_schema(): bool
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_seo_pages` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(191) NOT NULL,
            `slug` varchar(191) NOT NULL,
            `page_type` varchar(50) NOT NULL DEFAULT 'service',
            `meta_title` varchar(191) DEFAULT NULL,
            `meta_description` text DEFAULT NULL,
            `primary_keyword` varchar(191) DEFAULT NULL,
            `city` varchar(100) DEFAULT NULL,
            `status` varchar(30) NOT NULL DEFAULT 'draft',
            `assigned_staff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `content` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_slug` (`slug`),
            KEY `idx_status` (`status`),
            KEY `idx_assigned_staff_id` (`assigned_staff_id`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_seo_keywords` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `keyword` varchar(191) NOT NULL,
            `intent` varchar(50) NOT NULL DEFAULT 'local_service',
            `city` varchar(100) DEFAULT NULL,
            `priority` varchar(30) NOT NULL DEFAULT 'medium',
            `status` varchar(30) NOT NULL DEFAULT 'planned',
            `assigned_staff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `notes` text DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_keyword` (`keyword`),
            KEY `idx_priority` (`priority`),
            KEY `idx_status` (`status`),
            KEY `idx_assigned_staff_id` (`assigned_staff_id`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_seo_reports` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `report_date` date NOT NULL,
            `summary` text DEFAULT NULL,
            `pages_created` int(11) unsigned NOT NULL DEFAULT 0,
            `keywords_targeted` int(11) unsigned NOT NULL DEFAULT 0,
            `issues_found` int(11) unsigned NOT NULL DEFAULT 0,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_report_date` (`report_date`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_commands` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `command_text` text NOT NULL,
            `command_source` varchar(30) NOT NULL DEFAULT 'typed',
            `intent` varchar(100) NOT NULL DEFAULT 'crm_assistant',
            `target_module` varchar(100) DEFAULT NULL,
            `target_record_type` varchar(100) DEFAULT NULL,
            `target_record_id` int(11) unsigned NOT NULL DEFAULT 0,
            `action_status` varchar(30) NOT NULL DEFAULT 'draft',
            `ai_response` mediumtext DEFAULT NULL,
            `review_notes` text DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_action_status` (`action_status`),
            KEY `idx_intent` (`intent`),
            KEY `idx_created_by` (`created_by`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_estimates` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(191) NOT NULL,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `lead_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `location` varchar(191) DEFAULT NULL,
            `scope_summary` mediumtext DEFAULT NULL,
            `measurement_notes` mediumtext DEFAULT NULL,
            `materials_summary` mediumtext DEFAULT NULL,
            `labor_summary` mediumtext DEFAULT NULL,
            `ai_observations` mediumtext DEFAULT NULL,
            `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
            `tax_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `status` varchar(30) NOT NULL DEFAULT 'draft',
            `converted_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_lead_id` (`lead_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_status` (`status`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_photos` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `file_name` varchar(191) NOT NULL,
            `original_name` varchar(191) DEFAULT NULL,
            `file_path` varchar(255) NOT NULL,
            `mime_type` varchar(100) DEFAULT NULL,
            `file_size` int(11) unsigned NOT NULL DEFAULT 0,
            `photo_type` varchar(50) NOT NULL DEFAULT 'jobsite',
            `ai_caption` text DEFAULT NULL,
            `measurement_note` text DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_photo_type` (`photo_type`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_actions` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ai_command_id` int(11) unsigned NOT NULL DEFAULT 0,
            `action_name` varchar(100) NOT NULL,
            `crm_module` varchar(100) DEFAULT NULL,
            `payload_json` mediumtext DEFAULT NULL,
            `execution_status` varchar(30) NOT NULL DEFAULT 'pending_review',
            `result_message` text DEFAULT NULL,
            `executed_by` int(11) unsigned NOT NULL DEFAULT 0,
            `executed_at` datetime DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_command_id` (`ai_command_id`),
            KEY `idx_execution_status` (`execution_status`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_training` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(191) NOT NULL,
            `category` varchar(100) NOT NULL DEFAULT 'crm_workflow',
            `prompt_text` mediumtext NOT NULL,
            `expected_behavior` mediumtext DEFAULT NULL,
            `status` varchar(30) NOT NULL DEFAULT 'active',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_category` (`category`),
            KEY `idx_status` (`status`)
        ) {$charset}");



        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_price_index` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `source_type` varchar(50) NOT NULL DEFAULT 'crm_estimate',
            `source_id` int(11) unsigned NOT NULL DEFAULT 0,
            `service_keyword` varchar(191) NOT NULL,
            `description` mediumtext DEFAULT NULL,
            `quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
            `unit` varchar(50) DEFAULT NULL,
            `unit_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
            `line_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `city` varchar(100) DEFAULT NULL,
            `confidence` decimal(5,2) NOT NULL DEFAULT 0.00,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_service_keyword` (`service_keyword`),
            KEY `idx_source` (`source_type`, `source_id`),
            KEY `idx_unit` (`unit`)
        ) {$charset}");


        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_estimate_lines` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `line_order` int(11) unsigned NOT NULL DEFAULT 0,
            `description` varchar(191) NOT NULL,
            `long_description` mediumtext DEFAULT NULL,
            `quantity` decimal(15,4) NOT NULL DEFAULT 1.0000,
            `unit` varchar(50) DEFAULT NULL,
            `unit_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
            `material_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `labor_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `overhead_profit_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `line_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `source_type` varchar(50) NOT NULL DEFAULT 'crm_history',
            `source_id` int(11) unsigned NOT NULL DEFAULT 0,
            `confidence` decimal(5,2) NOT NULL DEFAULT 0.00,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_source` (`source_type`, `source_id`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_estimate_sources` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `price_index_id` int(11) unsigned NOT NULL DEFAULT 0,
            `source_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `service_keyword` varchar(191) DEFAULT NULL,
            `source_description` mediumtext DEFAULT NULL,
            `source_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
            `source_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `confidence` decimal(5,2) NOT NULL DEFAULT 0.00,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_price_index_id` (`price_index_id`)
        ) {$charset}");

        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'pricing_confidence', "decimal(5,2) NOT NULL DEFAULT 0.00 AFTER `total`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'pricing_basis', "varchar(191) DEFAULT NULL AFTER `pricing_confidence`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'line_items_json', "mediumtext DEFAULT NULL AFTER `pricing_basis`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'source_matches_json', "mediumtext DEFAULT NULL AFTER `line_items_json`");

        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'review_status', "varchar(30) NOT NULL DEFAULT 'draft' AFTER `source_matches_json`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'customer_scope', "mediumtext DEFAULT NULL AFTER `review_status`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'exclusions', "mediumtext DEFAULT NULL AFTER `customer_scope`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'payment_schedule', "mediumtext DEFAULT NULL AFTER `exclusions`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'estimator_review_notes', "mediumtext DEFAULT NULL AFTER `payment_schedule`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'field_verified', "tinyint(1) NOT NULL DEFAULT 0 AFTER `estimator_review_notes`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'approved_by', "int(11) unsigned NOT NULL DEFAULT 0 AFTER `field_verified`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'approved_at', "datetime DEFAULT NULL AFTER `approved_by`");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_estimate_approvals` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `action` varchar(50) NOT NULL DEFAULT 'review',
            `notes` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_action` (`action`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_customer_packages` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `package_title` varchar(191) NOT NULL,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `crm_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `package_status` varchar(40) NOT NULL DEFAULT 'draft',
            `customer_scope` mediumtext DEFAULT NULL,
            `exclusions` mediumtext DEFAULT NULL,
            `payment_schedule` mediumtext DEFAULT NULL,
            `internal_handoff` mediumtext DEFAULT NULL,
            `embed_notes` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_crm_estimate_id` (`crm_estimate_id`),
            KEY `idx_package_status` (`package_status`)
        ) {$charset}");

        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'customer_package_id', "int(11) unsigned NOT NULL DEFAULT 0 AFTER `converted_estimate_id`");
        usi_smartchoice_seo_add_column_if_missing('usi_ai_estimates', 'last_package_at', "datetime DEFAULT NULL AFTER `customer_package_id`");




        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_field_verifications` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `verification_title` varchar(191) NOT NULL,
            `jobsite_address` varchar(191) DEFAULT NULL,
            `measurement_summary` mediumtext DEFAULT NULL,
            `inspection_checklist` mediumtext DEFAULT NULL,
            `photo_notes` mediumtext DEFAULT NULL,
            `field_status` varchar(40) NOT NULL DEFAULT 'pending',
            `ready_for_estimate` tinyint(1) NOT NULL DEFAULT 0,
            `verified_by` int(11) unsigned NOT NULL DEFAULT 0,
            `verified_at` datetime DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_field_status` (`field_status`),
            KEY `idx_ready_for_estimate` (`ready_for_estimate`)
        ) {$charset}");



        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_project_handoffs` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_package_id` int(11) unsigned NOT NULL DEFAULT 0,
            `field_verification_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `handoff_title` varchar(191) NOT NULL,
            `project_name` varchar(191) DEFAULT NULL,
            `handoff_status` varchar(40) NOT NULL DEFAULT 'draft',
            `assigned_staff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `start_date` date DEFAULT NULL,
            `due_date` date DEFAULT NULL,
            `production_notes` mediumtext DEFAULT NULL,
            `material_notes` mediumtext DEFAULT NULL,
            `permit_notes` mediumtext DEFAULT NULL,
            `task_plan` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_handoff_status` (`handoff_status`),
            KEY `idx_assigned_staff_id` (`assigned_staff_id`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_project_tasks` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `handoff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `crm_task_id` int(11) unsigned NOT NULL DEFAULT 0,
            `task_name` varchar(191) NOT NULL,
            `task_type` varchar(60) NOT NULL DEFAULT 'production',
            `task_status` varchar(40) NOT NULL DEFAULT 'pending',
            `assigned_staff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `due_date` date DEFAULT NULL,
            `task_notes` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_handoff_id` (`handoff_id`),
            KEY `idx_crm_task_id` (`crm_task_id`),
            KEY `idx_task_status` (`task_status`),
            KEY `idx_assigned_staff_id` (`assigned_staff_id`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_voices` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `voice_name` varchar(191) NOT NULL,
            `language` varchar(30) NOT NULL DEFAULT 'en-US',
            `gender` varchar(30) NOT NULL DEFAULT 'neutral',
            `provider_voice_id` varchar(191) DEFAULT NULL,
            `sample_text` text DEFAULT NULL,
            `status` varchar(30) NOT NULL DEFAULT 'active',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_language` (`language`),
            KEY `idx_status` (`status`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_avatars` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `avatar_name` varchar(191) NOT NULL,
            `avatar_type` varchar(50) NOT NULL DEFAULT 'stock',
            `position_name` varchar(100) NOT NULL DEFAULT 'front_facing',
            `source_photo` varchar(255) DEFAULT NULL,
            `provider_avatar_id` varchar(191) DEFAULT NULL,
            `status` varchar(30) NOT NULL DEFAULT 'active',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_avatar_type` (`avatar_type`),
            KEY `idx_status` (`status`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_videos` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(191) NOT NULL,
            `script_text` mediumtext NOT NULL,
            `language` varchar(30) NOT NULL DEFAULT 'en-US',
            `voice_id` int(11) unsigned NOT NULL DEFAULT 0,
            `avatar_id` int(11) unsigned NOT NULL DEFAULT 0,
            `video_purpose` varchar(50) NOT NULL DEFAULT 'training',
            `status` varchar(30) NOT NULL DEFAULT 'draft',
            `logo_enabled` tinyint(1) NOT NULL DEFAULT 1,
            `logo_position` varchar(30) NOT NULL DEFAULT 'top_right',
            `intro_thumbnail` varchar(255) DEFAULT NULL,
            `outro_thumbnail` varchar(255) DEFAULT NULL,
            `audio_file` varchar(255) DEFAULT NULL,
            `video_file` varchar(255) DEFAULT NULL,
            `embed_code` mediumtext DEFAULT NULL,
            `ai_notes` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_status` (`status`),
            KEY `idx_video_purpose` (`video_purpose`),
            KEY `idx_voice_id` (`voice_id`),
            KEY `idx_avatar_id` (`avatar_id`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_video_text_layers` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `video_id` int(11) unsigned NOT NULL DEFAULT 0,
            `layer_text` varchar(255) NOT NULL,
            `start_second` decimal(10,2) NOT NULL DEFAULT 0.00,
            `duration_second` decimal(10,2) NOT NULL DEFAULT 5.00,
            `animation_type` varchar(50) NOT NULL DEFAULT 'fade',
            `position_name` varchar(50) NOT NULL DEFAULT 'lower_third',
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_video_id` (`video_id`)
        ) {$charset}");

        usi_smartchoice_seo_default_options();

        return usi_smartchoice_seo_table_exists('usi_seo_pages')
            && usi_smartchoice_seo_table_exists('usi_seo_keywords')
            && usi_smartchoice_seo_table_exists('usi_seo_reports')
            && usi_smartchoice_seo_table_exists('usi_ai_commands')
            && usi_smartchoice_seo_table_exists('usi_ai_estimates')
            && usi_smartchoice_seo_table_exists('usi_ai_photos')
            && usi_smartchoice_seo_table_exists('usi_ai_actions')
            && usi_smartchoice_seo_table_exists('usi_ai_training')
            && usi_smartchoice_seo_table_exists('usi_ai_price_index')
            && usi_smartchoice_seo_table_exists('usi_ai_estimate_approvals')
            && usi_smartchoice_seo_table_exists('usi_ai_customer_packages')
            && usi_smartchoice_seo_table_exists('usi_ai_field_verifications')
            && usi_smartchoice_seo_table_exists('usi_ai_project_handoffs')
            && usi_smartchoice_seo_table_exists('usi_ai_project_tasks')
            && usi_smartchoice_seo_table_exists('usi_ai_voices')
            && usi_smartchoice_seo_table_exists('usi_ai_avatars')
            && usi_smartchoice_seo_table_exists('usi_ai_videos')
            && usi_smartchoice_seo_table_exists('usi_ai_video_text_layers');
    }
}

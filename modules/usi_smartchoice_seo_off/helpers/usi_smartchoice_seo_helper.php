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





        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_communications` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_package_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_handoff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `lead_id` int(11) unsigned NOT NULL DEFAULT 0,
            `communication_type` varchar(50) NOT NULL DEFAULT 'follow_up',
            `language` varchar(20) NOT NULL DEFAULT 'english',
            `subject` varchar(191) DEFAULT NULL,
            `message_body` mediumtext DEFAULT NULL,
            `send_status` varchar(40) NOT NULL DEFAULT 'draft',
            `sent_at` datetime DEFAULT NULL,
            `sent_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_customer_package_id` (`customer_package_id`),
            KEY `idx_project_handoff_id` (`project_handoff_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_lead_id` (`lead_id`),
            KEY `idx_type_status` (`communication_type`, `send_status`)
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


        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_memory_runs` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `run_type` varchar(60) NOT NULL DEFAULT 'manual_rebuild',
            `run_status` varchar(40) NOT NULL DEFAULT 'completed',
            `source_summary` mediumtext DEFAULT NULL,
            `items_indexed` int(11) unsigned NOT NULL DEFAULT 0,
            `records_scanned` int(11) unsigned NOT NULL DEFAULT 0,
            `started_at` datetime DEFAULT NULL,
            `finished_at` datetime DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_run_status` (`run_status`),
            KEY `idx_run_type` (`run_type`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_memory_items` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `source_type` varchar(60) NOT NULL,
            `source_id` int(11) unsigned NOT NULL DEFAULT 0,
            `source_number` varchar(100) DEFAULT NULL,
            `source_title` varchar(191) NOT NULL,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `lead_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `related_module` varchar(80) DEFAULT NULL,
            `city` varchar(100) DEFAULT NULL,
            `service_category` varchar(100) DEFAULT NULL,
            `memory_text` mediumtext NOT NULL,
            `summary` mediumtext DEFAULT NULL,
            `keywords` mediumtext DEFAULT NULL,
            `amount_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `record_date` date DEFAULT NULL,
            `memory_status` varchar(40) NOT NULL DEFAULT 'active',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_source` (`source_type`, `source_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_lead_id` (`lead_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_service_category` (`service_category`),
            KEY `idx_memory_status` (`memory_status`),
            FULLTEXT KEY `ft_memory_search` (`source_title`, `memory_text`, `summary`, `keywords`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_memory_links` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `memory_item_id` int(11) unsigned NOT NULL DEFAULT 0,
            `linked_type` varchar(60) NOT NULL,
            `linked_id` int(11) unsigned NOT NULL DEFAULT 0,
            `link_reason` varchar(191) DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_memory_item_id` (`memory_item_id`),
            KEY `idx_linked` (`linked_type`, `linked_id`)
        ) {$charset}");


        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_document_runs` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `run_type` varchar(60) NOT NULL DEFAULT 'manual_scan',
            `run_status` varchar(40) NOT NULL DEFAULT 'completed',
            `source_summary` mediumtext DEFAULT NULL,
            `documents_indexed` int(11) unsigned NOT NULL DEFAULT 0,
            `chunks_created` int(11) unsigned NOT NULL DEFAULT 0,
            `started_at` datetime DEFAULT NULL,
            `finished_at` datetime DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_run_type` (`run_type`),
            KEY `idx_run_status` (`run_status`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_documents` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `document_title` varchar(191) NOT NULL,
            `document_type` varchar(80) NOT NULL DEFAULT 'general',
            `source_module` varchar(80) DEFAULT NULL,
            `source_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `lead_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `file_name` varchar(191) DEFAULT NULL,
            `file_path` varchar(255) DEFAULT NULL,
            `mime_type` varchar(100) DEFAULT NULL,
            `document_text` mediumtext DEFAULT NULL,
            `ai_summary` mediumtext DEFAULT NULL,
            `ai_keywords` mediumtext DEFAULT NULL,
            `document_status` varchar(40) NOT NULL DEFAULT 'indexed',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_document_type` (`document_type`),
            KEY `idx_source` (`source_module`, `source_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_document_status` (`document_status`),
            FULLTEXT KEY `ft_document_search` (`document_title`, `document_text`, `ai_summary`, `ai_keywords`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_document_chunks` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `document_id` int(11) unsigned NOT NULL DEFAULT 0,
            `chunk_order` int(11) unsigned NOT NULL DEFAULT 0,
            `chunk_title` varchar(191) DEFAULT NULL,
            `chunk_text` mediumtext NOT NULL,
            `ai_summary` mediumtext DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_document_id` (`document_id`),
            FULLTEXT KEY `ft_document_chunk_search` (`chunk_title`, `chunk_text`, `ai_summary`)
        ) {$charset}");


        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_takeoffs` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(191) NOT NULL,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `service_category` varchar(100) NOT NULL DEFAULT 'general',
            `room_area` varchar(191) DEFAULT NULL,
            `measurement_notes` mediumtext DEFAULT NULL,
            `takeoff_summary` mediumtext DEFAULT NULL,
            `material_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `waste_percent` decimal(8,2) NOT NULL DEFAULT 10.00,
            `status` varchar(40) NOT NULL DEFAULT 'draft',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_service_category` (`service_category`),
            KEY `idx_status` (`status`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_takeoff_lines` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `takeoff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `item_name` varchar(191) NOT NULL,
            `trade` varchar(80) NOT NULL DEFAULT 'general',
            `unit` varchar(40) NOT NULL DEFAULT 'each',
            `quantity` decimal(15,2) NOT NULL DEFAULT 0.00,
            `waste_quantity` decimal(15,2) NOT NULL DEFAULT 0.00,
            `total_quantity` decimal(15,2) NOT NULL DEFAULT 0.00,
            `unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
            `line_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `source_note` text DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_takeoff_id` (`takeoff_id`),
            KEY `idx_trade` (`trade`)
        ) {$charset}");



        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_vendors` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `vendor_name` varchar(191) NOT NULL,
            `contact_name` varchar(191) DEFAULT NULL,
            `email` varchar(191) DEFAULT NULL,
            `phone` varchar(60) DEFAULT NULL,
            `website` varchar(191) DEFAULT NULL,
            `category` varchar(100) NOT NULL DEFAULT 'general',
            `rating` decimal(4,2) NOT NULL DEFAULT 0.00,
            `preferred` tinyint(1) NOT NULL DEFAULT 0,
            `status` varchar(40) NOT NULL DEFAULT 'active',
            `notes` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_vendor_name` (`vendor_name`),
            KEY `idx_category` (`category`),
            KEY `idx_status` (`status`),
            KEY `idx_preferred` (`preferred`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_vendor_prices` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `vendor_id` int(11) unsigned NOT NULL DEFAULT 0,
            `item_name` varchar(191) NOT NULL,
            `category` varchar(100) NOT NULL DEFAULT 'general',
            `unit` varchar(40) NOT NULL DEFAULT 'each',
            `unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
            `source` varchar(100) DEFAULT NULL,
            `effective_date` date DEFAULT NULL,
            `notes` text DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_vendor_id` (`vendor_id`),
            KEY `idx_item_name` (`item_name`),
            KEY `idx_category` (`category`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_purchase_orders` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `po_number` varchar(80) NOT NULL,
            `title` varchar(191) NOT NULL,
            `takeoff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `vendor_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `status` varchar(40) NOT NULL DEFAULT 'draft',
            `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
            `tax_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `delivery_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
            `total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `requested_date` date DEFAULT NULL,
            `ordered_date` date DEFAULT NULL,
            `received_date` date DEFAULT NULL,
            `delivery_address` varchar(255) DEFAULT NULL,
            `vendor_notes` mediumtext DEFAULT NULL,
            `internal_notes` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_po_number` (`po_number`),
            KEY `idx_takeoff_id` (`takeoff_id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_vendor_id` (`vendor_id`),
            KEY `idx_status` (`status`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_purchase_order_lines` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `purchase_order_id` int(11) unsigned NOT NULL DEFAULT 0,
            `takeoff_line_id` int(11) unsigned NOT NULL DEFAULT 0,
            `item_name` varchar(191) NOT NULL,
            `category` varchar(100) NOT NULL DEFAULT 'general',
            `unit` varchar(40) NOT NULL DEFAULT 'each',
            `quantity` decimal(15,2) NOT NULL DEFAULT 0.00,
            `unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
            `line_total` decimal(15,2) NOT NULL DEFAULT 0.00,
            `vendor_source_note` text DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_purchase_order_id` (`purchase_order_id`),
            KEY `idx_takeoff_line_id` (`takeoff_line_id`),
            KEY `idx_category` (`category`)
        ) {$charset}");


        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_schedules` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(191) NOT NULL,
            `project_handoff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `schedule_type` varchar(80) NOT NULL DEFAULT 'production',
            `status` varchar(40) NOT NULL DEFAULT 'draft',
            `start_date` date DEFAULT NULL,
            `end_date` date DEFAULT NULL,
            `crew_lead_id` int(11) unsigned NOT NULL DEFAULT 0,
            `department_id` int(11) unsigned NOT NULL DEFAULT 0,
            `duration_days` int(11) unsigned NOT NULL DEFAULT 0,
            `conflict_notes` mediumtext DEFAULT NULL,
            `schedule_summary` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_project_handoff_id` (`project_handoff_id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_status` (`status`),
            KEY `idx_start_date` (`start_date`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_schedule_items` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `schedule_id` int(11) unsigned NOT NULL DEFAULT 0,
            `item_order` int(11) unsigned NOT NULL DEFAULT 0,
            `item_title` varchar(191) NOT NULL,
            `item_type` varchar(80) NOT NULL DEFAULT 'task',
            `assigned_staff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `planned_start` date DEFAULT NULL,
            `planned_end` date DEFAULT NULL,
            `duration_days` int(11) unsigned NOT NULL DEFAULT 1,
            `dependency_note` varchar(191) DEFAULT NULL,
            `status` varchar(40) NOT NULL DEFAULT 'pending',
            `notes` mediumtext DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_schedule_id` (`schedule_id`),
            KEY `idx_assigned_staff_id` (`assigned_staff_id`),
            KEY `idx_status` (`status`),
            KEY `idx_planned_start` (`planned_start`)
        ) {$charset}");


        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_jobsite_logs` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `schedule_id` int(11) unsigned NOT NULL DEFAULT 0,
            `log_date` date DEFAULT NULL,
            `title` varchar(191) NOT NULL,
            `weather_note` varchar(191) DEFAULT NULL,
            `crew_note` text DEFAULT NULL,
            `work_completed` mediumtext DEFAULT NULL,
            `materials_used` mediumtext DEFAULT NULL,
            `safety_checklist` mediumtext DEFAULT NULL,
            `customer_note` mediumtext DEFAULT NULL,
            `photo_summary` mediumtext DEFAULT NULL,
            `status` varchar(30) NOT NULL DEFAULT 'draft',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_schedule_id` (`schedule_id`),
            KEY `idx_status` (`status`),
            KEY `idx_log_date` (`log_date`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_jobsite_punch_items` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `jobsite_log_id` int(11) unsigned NOT NULL DEFAULT 0,
            `item_title` varchar(191) NOT NULL,
            `item_type` varchar(50) NOT NULL DEFAULT 'punch',
            `priority` varchar(30) NOT NULL DEFAULT 'normal',
            `assigned_staff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `due_date` date DEFAULT NULL,
            `description` mediumtext DEFAULT NULL,
            `photo_note` mediumtext DEFAULT NULL,
            `status` varchar(30) NOT NULL DEFAULT 'open',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_jobsite_log_id` (`jobsite_log_id`),
            KEY `idx_item_type` (`item_type`),
            KEY `idx_priority` (`priority`),
            KEY `idx_status` (`status`),
            KEY `idx_assigned_staff_id` (`assigned_staff_id`)
        ) {$charset}");


        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_closeouts` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `jobsite_log_id` int(11) unsigned NOT NULL DEFAULT 0,
            `ai_estimate_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `title` varchar(191) NOT NULL,
            `closeout_date` date DEFAULT NULL,
            `status` varchar(40) NOT NULL DEFAULT 'draft',
            `final_photo_checklist` mediumtext DEFAULT NULL,
            `punch_summary` mediumtext DEFAULT NULL,
            `customer_walkthrough_notes` mediumtext DEFAULT NULL,
            `warranty_summary` mediumtext DEFAULT NULL,
            `closeout_package_notes` mediumtext DEFAULT NULL,
            `ready_for_customer` tinyint(1) NOT NULL DEFAULT 0,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_jobsite_log_id` (`jobsite_log_id`),
            KEY `idx_ai_estimate_id` (`ai_estimate_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_status` (`status`),
            KEY `idx_closeout_date` (`closeout_date`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_warranties` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `closeout_id` int(11) unsigned NOT NULL DEFAULT 0,
            `project_id` int(11) unsigned NOT NULL DEFAULT 0,
            `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
            `warranty_title` varchar(191) NOT NULL,
            `trade` varchar(80) NOT NULL DEFAULT 'general',
            `warranty_type` varchar(80) NOT NULL DEFAULT 'workmanship',
            `start_date` date DEFAULT NULL,
            `end_date` date DEFAULT NULL,
            `coverage_notes` mediumtext DEFAULT NULL,
            `exclusions` mediumtext DEFAULT NULL,
            `status` varchar(40) NOT NULL DEFAULT 'active',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_closeout_id` (`closeout_id`),
            KEY `idx_project_id` (`project_id`),
            KEY `idx_customer_id` (`customer_id`),
            KEY `idx_trade` (`trade`),
            KEY `idx_status` (`status`),
            KEY `idx_end_date` (`end_date`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_closeout_followups` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `closeout_id` int(11) unsigned NOT NULL DEFAULT 0,
            `followup_type` varchar(80) NOT NULL DEFAULT 'customer_check_in',
            `due_date` date DEFAULT NULL,
            `assigned_staff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `message_subject` varchar(191) DEFAULT NULL,
            `message_body` mediumtext DEFAULT NULL,
            `status` varchar(40) NOT NULL DEFAULT 'pending',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_closeout_id` (`closeout_id`),
            KEY `idx_followup_type` (`followup_type`),
            KEY `idx_due_date` (`due_date`),
            KEY `idx_status` (`status`)
        ) {$charset}");



        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_executive_snapshots` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `snapshot_date` date NOT NULL,
            `pipeline_value` decimal(15,2) NOT NULL DEFAULT 0.00,
            `approved_estimate_value` decimal(15,2) NOT NULL DEFAULT 0.00,
            `open_project_count` int(11) unsigned NOT NULL DEFAULT 0,
            `open_purchase_order_value` decimal(15,2) NOT NULL DEFAULT 0.00,
            `open_schedule_count` int(11) unsigned NOT NULL DEFAULT 0,
            `open_jobsite_log_count` int(11) unsigned NOT NULL DEFAULT 0,
            `open_closeout_count` int(11) unsigned NOT NULL DEFAULT 0,
            `active_warranty_count` int(11) unsigned NOT NULL DEFAULT 0,
            `risk_summary` mediumtext DEFAULT NULL,
            `recommended_actions` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_snapshot_date` (`snapshot_date`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_executive_actions` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `snapshot_id` int(11) unsigned NOT NULL DEFAULT 0,
            `action_title` varchar(191) NOT NULL,
            `action_type` varchar(80) NOT NULL DEFAULT 'management_review',
            `priority` varchar(30) NOT NULL DEFAULT 'normal',
            `related_area` varchar(80) DEFAULT NULL,
            `related_id` int(11) unsigned NOT NULL DEFAULT 0,
            `assigned_staff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `due_date` date DEFAULT NULL,
            `status` varchar(40) NOT NULL DEFAULT 'open',
            `notes` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_snapshot_id` (`snapshot_id`),
            KEY `idx_action_type` (`action_type`),
            KEY `idx_priority` (`priority`),
            KEY `idx_status` (`status`),
            KEY `idx_assigned_staff_id` (`assigned_staff_id`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_business_snapshots` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `snapshot_date` date NOT NULL,
            `gross_pipeline_value` decimal(15,2) NOT NULL DEFAULT 0.00,
            `approved_pipeline_value` decimal(15,2) NOT NULL DEFAULT 0.00,
            `open_purchase_value` decimal(15,2) NOT NULL DEFAULT 0.00,
            `ready_message_count` int(11) unsigned NOT NULL DEFAULT 0,
            `draft_estimate_count` int(11) unsigned NOT NULL DEFAULT 0,
            `open_schedule_count` int(11) unsigned NOT NULL DEFAULT 0,
            `open_closeout_count` int(11) unsigned NOT NULL DEFAULT 0,
            `active_warranty_count` int(11) unsigned NOT NULL DEFAULT 0,
            `risk_level` varchar(30) NOT NULL DEFAULT 'normal',
            `business_summary` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_snapshot_date` (`snapshot_date`),
            KEY `idx_risk_level` (`risk_level`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_business_recommendations` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `snapshot_id` int(11) unsigned NOT NULL DEFAULT 0,
            `recommendation_title` varchar(191) NOT NULL,
            `recommendation_type` varchar(80) NOT NULL DEFAULT 'operations',
            `priority` varchar(30) NOT NULL DEFAULT 'normal',
            `impact_area` varchar(80) DEFAULT NULL,
            `recommended_action` mediumtext DEFAULT NULL,
            `expected_impact` mediumtext DEFAULT NULL,
            `assigned_staff_id` int(11) unsigned NOT NULL DEFAULT 0,
            `due_date` date DEFAULT NULL,
            `status` varchar(40) NOT NULL DEFAULT 'open',
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_snapshot_id` (`snapshot_id`),
            KEY `idx_type` (`recommendation_type`),
            KEY `idx_priority` (`priority`),
            KEY `idx_status` (`status`),
            KEY `idx_assigned_staff_id` (`assigned_staff_id`)
        ) {$charset}");




        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_workflows` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `workflow_name` varchar(191) NOT NULL,
            `workflow_type` varchar(100) NOT NULL DEFAULT 'manual',
            `trigger_area` varchar(100) NOT NULL DEFAULT 'manual',
            `trigger_event` varchar(100) NOT NULL DEFAULT 'manual_run',
            `conditions_json` mediumtext DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `description` text DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_workflow_type` (`workflow_type`),
            KEY `idx_trigger_area` (`trigger_area`),
            KEY `idx_is_active` (`is_active`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_workflow_steps` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `workflow_id` int(11) unsigned NOT NULL DEFAULT 0,
            `step_order` int(11) unsigned NOT NULL DEFAULT 1,
            `step_name` varchar(191) NOT NULL,
            `step_type` varchar(100) NOT NULL DEFAULT 'action',
            `action_type` varchar(100) NOT NULL DEFAULT 'create_task',
            `target_area` varchar(100) DEFAULT NULL,
            `delay_minutes` int(11) unsigned NOT NULL DEFAULT 0,
            `requires_approval` tinyint(1) NOT NULL DEFAULT 0,
            `step_payload` mediumtext DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_workflow_id` (`workflow_id`),
            KEY `idx_step_type` (`step_type`),
            KEY `idx_action_type` (`action_type`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_workflow_runs` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `workflow_id` int(11) unsigned NOT NULL DEFAULT 0,
            `source_area` varchar(100) NOT NULL DEFAULT 'manual',
            `source_id` int(11) unsigned NOT NULL DEFAULT 0,
            `run_status` varchar(50) NOT NULL DEFAULT 'pending',
            `input_payload` mediumtext DEFAULT NULL,
            `result_message` text DEFAULT NULL,
            `started_by` int(11) unsigned NOT NULL DEFAULT 0,
            `started_at` datetime DEFAULT NULL,
            `completed_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_workflow_id` (`workflow_id`),
            KEY `idx_run_status` (`run_status`),
            KEY `idx_source` (`source_area`, `source_id`)
        ) {$charset}");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_workflow_logs` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `workflow_run_id` int(11) unsigned NOT NULL DEFAULT 0,
            `workflow_id` int(11) unsigned NOT NULL DEFAULT 0,
            `step_id` int(11) unsigned NOT NULL DEFAULT 0,
            `log_type` varchar(100) NOT NULL DEFAULT 'info',
            `log_message` text DEFAULT NULL,
            `created_by` int(11) unsigned NOT NULL DEFAULT 0,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_workflow_run_id` (`workflow_run_id`),
            KEY `idx_workflow_id` (`workflow_id`),
            KEY `idx_log_type` (`log_type`)
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
            && usi_smartchoice_seo_table_exists('usi_ai_communications')
            && usi_smartchoice_seo_table_exists('usi_ai_project_handoffs')
            && usi_smartchoice_seo_table_exists('usi_ai_project_tasks')
            && usi_smartchoice_seo_table_exists('usi_ai_memory_runs')
            && usi_smartchoice_seo_table_exists('usi_ai_memory_items')
            && usi_smartchoice_seo_table_exists('usi_ai_memory_links')
            && usi_smartchoice_seo_table_exists('usi_ai_voices')
            && usi_smartchoice_seo_table_exists('usi_ai_avatars')
            && usi_smartchoice_seo_table_exists('usi_ai_videos')
            && usi_smartchoice_seo_table_exists('usi_ai_video_text_layers')
            && usi_smartchoice_seo_table_exists('usi_ai_document_runs')
            && usi_smartchoice_seo_table_exists('usi_ai_documents')
            && usi_smartchoice_seo_table_exists('usi_ai_document_chunks')
            && usi_smartchoice_seo_table_exists('usi_ai_takeoffs')
            && usi_smartchoice_seo_table_exists('usi_ai_takeoff_lines')
            && usi_smartchoice_seo_table_exists('usi_ai_vendors')
            && usi_smartchoice_seo_table_exists('usi_ai_vendor_prices')
            && usi_smartchoice_seo_table_exists('usi_ai_purchase_orders')
            && usi_smartchoice_seo_table_exists('usi_ai_purchase_order_lines')
            && usi_smartchoice_seo_table_exists('usi_ai_schedules')
            && usi_smartchoice_seo_table_exists('usi_ai_schedule_items')
            && usi_smartchoice_seo_table_exists('usi_ai_jobsite_logs')
            && usi_smartchoice_seo_table_exists('usi_ai_jobsite_punch_items')
            && usi_smartchoice_seo_table_exists('usi_ai_closeouts')
            && usi_smartchoice_seo_table_exists('usi_ai_warranties')
            && usi_smartchoice_seo_table_exists('usi_ai_closeout_followups')
            && usi_smartchoice_seo_table_exists('usi_ai_executive_snapshots')
            && usi_smartchoice_seo_table_exists('usi_ai_executive_actions')
            && usi_smartchoice_seo_table_exists('usi_ai_business_snapshots')
            && usi_smartchoice_seo_table_exists('usi_ai_business_recommendations');
    }
}

if (!function_exists('usi_smartchoice_ai_ensure_core_schema')) {
    function usi_smartchoice_ai_ensure_core_schema(): void
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_core_context` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `context_title` varchar(191) NOT NULL,
            `context_type` varchar(100) NOT NULL DEFAULT 'general',
            `related_type` varchar(100) DEFAULT NULL,
            `related_id` int(11) DEFAULT NULL,
            `context_summary` mediumtext DEFAULT NULL,
            `context_data` longtext DEFAULT NULL,
            `priority` varchar(50) NOT NULL DEFAULT 'normal',
            `status` varchar(50) NOT NULL DEFAULT 'active',
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`), KEY `idx_context_type` (`context_type`), KEY `idx_status` (`status`)
        ) {$charset}");
        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_core_actions` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `action_title` varchar(191) NOT NULL,
            `action_type` varchar(100) NOT NULL DEFAULT 'review',
            `source_area` varchar(100) NOT NULL DEFAULT 'core',
            `related_type` varchar(100) DEFAULT NULL,
            `related_id` int(11) DEFAULT NULL,
            `requested_command` mediumtext DEFAULT NULL,
            `recommended_action` mediumtext DEFAULT NULL,
            `approval_required` tinyint(1) NOT NULL DEFAULT 1,
            `assigned_staff_id` int(11) DEFAULT NULL,
            `due_date` date DEFAULT NULL,
            `priority` varchar(50) NOT NULL DEFAULT 'normal',
            `status` varchar(50) NOT NULL DEFAULT 'pending',
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`), KEY `idx_action_type` (`action_type`), KEY `idx_source_area` (`source_area`), KEY `idx_status` (`status`)
        ) {$charset}");
        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_core_timeline` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `event_title` varchar(191) NOT NULL,
            `event_type` varchar(100) NOT NULL DEFAULT 'system',
            `source_area` varchar(100) NOT NULL DEFAULT 'core',
            `related_type` varchar(100) DEFAULT NULL,
            `related_id` int(11) DEFAULT NULL,
            `event_summary` mediumtext DEFAULT NULL,
            `event_data` longtext DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`), KEY `idx_event_type` (`event_type`), KEY `idx_source_area` (`source_area`)
        ) {$charset}");
        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_core_search_index` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `source_table` varchar(100) NOT NULL,
            `source_id` int(11) NOT NULL DEFAULT 0,
            `source_area` varchar(100) NOT NULL DEFAULT 'core',
            `record_title` varchar(191) NOT NULL,
            `record_summary` mediumtext DEFAULT NULL,
            `search_text` longtext DEFAULT NULL,
            `record_url` varchar(500) DEFAULT NULL,
            `status` varchar(50) NOT NULL DEFAULT 'active',
            `last_indexed_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`), KEY `idx_source` (`source_table`,`source_id`), KEY `idx_source_area` (`source_area`), KEY `idx_status` (`status`)
        ) {$charset}");
        $CI->db->query("CREATE TABLE IF NOT EXISTS `{$prefix}usi_ai_core_diagnostics` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `diagnostic_area` varchar(100) NOT NULL,
            `diagnostic_title` varchar(191) NOT NULL,
            `diagnostic_status` varchar(50) NOT NULL DEFAULT 'ok',
            `diagnostic_message` mediumtext DEFAULT NULL,
            `checked_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`), KEY `idx_area` (`diagnostic_area`), KEY `idx_status` (`diagnostic_status`)
        ) {$charset}");


        if (function_exists('usi_smartchoice_seo_production_repair_schema')) {
            usi_smartchoice_seo_production_repair_schema();
        }
    }
}

if (!function_exists('usi_smartchoice_seo_production_repair_schema')) {
    function usi_smartchoice_seo_production_repair_schema(): void
    {
        $columnRepairs = [
            'usi_ai_estimates' => [
                'status' => "varchar(30) NOT NULL DEFAULT 'draft'",
                'total' => "decimal(15,2) NOT NULL DEFAULT 0.00",
                'subtotal' => "decimal(15,2) NOT NULL DEFAULT 0.00",
                'tax_total' => "decimal(15,2) NOT NULL DEFAULT 0.00",
                'review_status' => "varchar(30) NOT NULL DEFAULT 'draft'",
                'pricing_confidence' => "decimal(5,2) NOT NULL DEFAULT 0.00",
                'pricing_basis' => "varchar(191) DEFAULT NULL",
                'line_items_json' => "mediumtext DEFAULT NULL",
                'source_matches_json' => "mediumtext DEFAULT NULL",
                'customer_scope' => "mediumtext DEFAULT NULL",
                'field_verified' => "tinyint(1) NOT NULL DEFAULT 0",
            ],
            'usi_ai_field_verifications' => [
                'status' => "varchar(30) NOT NULL DEFAULT 'draft'",
                'verification_status' => "varchar(30) NOT NULL DEFAULT 'draft'",
            ],
            'usi_ai_purchase_orders' => [
                'status' => "varchar(30) NOT NULL DEFAULT 'draft'",
                'grand_total' => "decimal(15,2) NOT NULL DEFAULT 0.00",
            ],
            'usi_ai_schedules' => [
                'status' => "varchar(30) NOT NULL DEFAULT 'draft'",
                'schedule_status' => "varchar(30) NOT NULL DEFAULT 'draft'",
            ],
            'usi_ai_jobsite_logs' => [
                'status' => "varchar(30) NOT NULL DEFAULT 'draft'",
                'log_status' => "varchar(30) NOT NULL DEFAULT 'draft'",
            ],
            'usi_ai_closeouts' => [
                'status' => "varchar(30) NOT NULL DEFAULT 'draft'",
                'closeout_status' => "varchar(30) NOT NULL DEFAULT 'draft'",
            ],
            'usi_ai_warranties' => [
                'status' => "varchar(30) NOT NULL DEFAULT 'active'",
                'warranty_status' => "varchar(30) NOT NULL DEFAULT 'active'",
            ],
            'usi_ai_communications' => [
                'send_status' => "varchar(30) NOT NULL DEFAULT 'draft'",
                'status' => "varchar(30) NOT NULL DEFAULT 'draft'",
            ],
            'usi_ai_business_recommendations' => [
                'status' => "varchar(30) NOT NULL DEFAULT 'open'",
            ],
            'usi_ai_core_actions' => [
                'status' => "varchar(50) NOT NULL DEFAULT 'pending'",
            ],
            'usi_ai_core_context' => [
                'status' => "varchar(50) NOT NULL DEFAULT 'active'",
            ],
            'usi_ai_voices' => [
                'sample_text' => "text DEFAULT NULL",
                'status' => "varchar(30) NOT NULL DEFAULT 'active'",
            ],
            'usi_ai_avatars' => [
                'status' => "varchar(30) NOT NULL DEFAULT 'active'",
            ],
            'usi_ai_project_handoffs' => [
                'handoff_status' => "varchar(30) NOT NULL DEFAULT 'draft'",
            ],
        ];
        foreach ($columnRepairs as $table => $columns) {
            if (!usi_smartchoice_seo_table_exists($table)) {
                continue;
            }
            foreach ($columns as $column => $definition) {
                usi_smartchoice_seo_add_column_if_missing($table, $column, $definition);
            }
        }
        usi_smartchoice_seo_default_options();
    }
}

if (!function_exists('sammy_ai_nav')) {
    function sammy_ai_nav(): string
    {
        $links = [
            ['AI Dashboard', 'usi_smartchoice_seo/ai_dashboard'],
            ['Production AI Core', 'usi_smartchoice_seo/ai_core'],
            ['Command Center', 'usi_smartchoice_seo/command_center'],
            ['Voice Assistant', 'usi_smartchoice_seo/voice_assistant'],
            ['Vision Engine', 'usi_smartchoice_seo/vision'],
            ['AI Estimates', 'usi_smartchoice_seo/ai_estimates'],
            ['Pricing Engine', 'usi_smartchoice_seo/pricing_engine'],
            ['Material Takeoff', 'usi_smartchoice_seo/material_takeoffs'],
            ['AI Purchasing', 'usi_smartchoice_seo/purchasing'],
            ['AI Scheduling', 'usi_smartchoice_seo/scheduling'],
            ['Jobsite Assistant', 'usi_smartchoice_seo/jobsite_assistant'],
            ['Closeout & Warranty', 'usi_smartchoice_seo/closeout_warranty'],
            ['Reports', 'usi_smartchoice_seo/reports'],
            ['Settings', 'usi_smartchoice_seo/settings'],
            ['Health', 'usi_smartchoice_seo/health'],
            ['Help', 'usi_smartchoice_seo/help'],
        ];
        $html = '<div class="sc-ai-nav m-b-15">';
        foreach ($links as $link) {
            $html .= '<a class="btn btn-default btn-sm" href="' . admin_url($link[1]) . '">' . html_escape($link[0]) . '</a> ';
        }
        $html .= '</div>';
        return $html;
    }
}

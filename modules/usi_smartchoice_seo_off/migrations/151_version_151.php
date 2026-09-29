<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_151 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_prompt_templates')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_prompt_templates` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `template_name` VARCHAR(191) NOT NULL,
                `template_area` VARCHAR(100) NOT NULL DEFAULT 'general',
                `system_prompt` LONGTEXT NULL,
                `user_prompt` LONGTEXT NULL,
                `default_model` VARCHAR(100) NULL,
                `temperature` DECIMAL(4,2) NOT NULL DEFAULT 0.20,
                `max_tokens` INT(11) NOT NULL DEFAULT 1200,
                `status` VARCHAR(50) NOT NULL DEFAULT 'active',
                `created_by` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_area` (`template_area`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_context_profiles')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_context_profiles` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `profile_name` VARCHAR(191) NOT NULL,
                `related_type` VARCHAR(100) NOT NULL DEFAULT 'general',
                `related_id` INT(11) NOT NULL DEFAULT 0,
                `context_summary` MEDIUMTEXT NULL,
                `context_payload` LONGTEXT NULL,
                `priority` VARCHAR(50) NOT NULL DEFAULT 'normal',
                `status` VARCHAR(50) NOT NULL DEFAULT 'active',
                `created_by` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_related` (`related_type`, `related_id`),
                KEY `idx_priority` (`priority`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_requests')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_requests` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `request_area` VARCHAR(100) NOT NULL DEFAULT 'general',
                `related_type` VARCHAR(100) NOT NULL DEFAULT 'general',
                `related_id` INT(11) NOT NULL DEFAULT 0,
                `prompt_hash` VARCHAR(64) NULL,
                `prompt_text` LONGTEXT NULL,
                `response_text` LONGTEXT NULL,
                `model_name` VARCHAR(100) NULL,
                `provider_name` VARCHAR(100) NULL,
                `request_status` VARCHAR(50) NOT NULL DEFAULT 'logged',
                `token_input` INT(11) NOT NULL DEFAULT 0,
                `token_output` INT(11) NOT NULL DEFAULT 0,
                `cost_estimate` DECIMAL(15,6) NOT NULL DEFAULT 0.000000,
                `staff_id` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_area` (`request_area`),
                KEY `idx_related` (`related_type`, `related_id`),
                KEY `idx_status` (`request_status`),
                KEY `idx_staff` (`staff_id`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_response_cache')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_response_cache` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `cache_key` VARCHAR(191) NOT NULL,
                `request_area` VARCHAR(100) NOT NULL DEFAULT 'general',
                `prompt_hash` VARCHAR(64) NULL,
                `response_text` LONGTEXT NULL,
                `model_name` VARCHAR(100) NULL,
                `provider_name` VARCHAR(100) NULL,
                `expires_at` DATETIME NULL,
                `last_used_at` DATETIME NULL,
                `use_count` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_cache_key` (`cache_key`),
                KEY `idx_area` (`request_area`),
                KEY `idx_expires` (`expires_at`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_provider_logs')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_provider_logs` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `provider_name` VARCHAR(100) NOT NULL DEFAULT 'Manual Provider',
                `request_id` INT(11) NOT NULL DEFAULT 0,
                `log_status` VARCHAR(50) NOT NULL DEFAULT 'logged',
                `message` MEDIUMTEXT NULL,
                `latency_ms` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_provider` (`provider_name`),
                KEY `idx_request` (`request_id`),
                KEY `idx_status` (`log_status`)
            ) " . $charset);
        }

        $defaultPromptCount = (int)$CI->db->where('template_area', 'estimating')->count_all_results($prefix . 'usi_ai_prompt_templates');
        if ($defaultPromptCount === 0) {
            $CI->db->insert($prefix . 'usi_ai_prompt_templates', [
                'template_name' => 'Estimate Context Builder',
                'template_area' => 'estimating',
                'system_prompt' => 'You are Sammy AI for Smart Choice Contractors USA. Use CRM context, prior estimates, field verification, and pricing history to prepare internal draft guidance only.',
                'user_prompt' => 'Build estimate context from the selected customer, AI estimate, pricing rows, material takeoff, and project notes. Return scope, assumptions, risks, and missing information.',
                'default_model' => '',
                'temperature' => 0.20,
                'max_tokens' => 1200,
                'status' => 'active',
                'created_by' => function_exists('get_staff_user_id') ? get_staff_user_id() : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        update_option('usi_smartchoice_seo_version', '1.5.1');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_provider_logs')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_provider_logs`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_response_cache')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_response_cache`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_requests')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_requests`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_context_profiles')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_context_profiles`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_prompt_templates')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_prompt_templates`'); }
    }
}

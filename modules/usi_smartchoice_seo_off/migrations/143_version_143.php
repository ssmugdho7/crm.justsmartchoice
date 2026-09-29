<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_143 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_voice_sessions')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_voice_sessions` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `session_title` VARCHAR(191) NOT NULL,
                `session_mode` VARCHAR(75) NOT NULL DEFAULT 'push_to_talk',
                `language_code` VARCHAR(20) NOT NULL DEFAULT 'en-US',
                `listening_status` VARCHAR(50) NOT NULL DEFAULT 'stopped',
                `source_area` VARCHAR(100) NOT NULL DEFAULT 'voice_orchestrator',
                `source_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `customer_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `lead_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `project_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `estimate_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `command_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `last_command_text` MEDIUMTEXT NULL,
                `last_intent` VARCHAR(100) NULL,
                `created_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_session_mode` (`session_mode`),
                KEY `idx_listening_status` (`listening_status`),
                KEY `idx_source` (`source_area`, `source_id`),
                KEY `idx_customer_id` (`customer_id`),
                KEY `idx_lead_id` (`lead_id`),
                KEY `idx_project_id` (`project_id`),
                KEY `idx_estimate_id` (`estimate_id`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_voice_transcripts')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_voice_transcripts` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `session_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `transcript_text` MEDIUMTEXT NOT NULL,
                `clean_command_text` MEDIUMTEXT NULL,
                `detected_intent` VARCHAR(100) NOT NULL DEFAULT 'unknown',
                `confidence_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `route_status` VARCHAR(50) NOT NULL DEFAULT 'draft',
                `route_target` VARCHAR(100) NULL,
                `route_result` MEDIUMTEXT NULL,
                `created_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_session_id` (`session_id`),
                KEY `idx_detected_intent` (`detected_intent`),
                KEY `idx_route_status` (`route_status`),
                KEY `idx_route_target` (`route_target`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_voice_command_routes')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_voice_command_routes` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `route_name` VARCHAR(191) NOT NULL,
                `trigger_phrase` VARCHAR(191) NOT NULL,
                `intent_key` VARCHAR(100) NOT NULL,
                `target_action` VARCHAR(100) NOT NULL,
                `requires_confirmation` TINYINT(1) NOT NULL DEFAULT 1,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_intent_key` (`intent_key`),
                KEY `idx_target_action` (`target_action`),
                KEY `idx_is_active` (`is_active`)
            ) " . $charset);
        }

        $now = date('Y-m-d H:i:s');
        $defaults = [
            ['Create Lead', 'create a lead', 'create_lead', 'open_lead_form', 1, 10],
            ['Create Estimate', 'create an estimate', 'create_estimate', 'run_ai_estimate', 1, 20],
            ['Run Estimate', 'run estimate', 'run_estimate', 'run_ai_estimate', 1, 30],
            ['Open Customer', 'open customer', 'open_customer', 'crm_search_customer', 0, 40],
            ['Create Task', 'create task', 'create_task', 'open_task_form', 1, 50],
            ['Search Memory', 'search memory', 'search_memory', 'memory_engine_search', 0, 60],
        ];
        foreach ($defaults as $route) {
            $exists = (int)$CI->db->where('intent_key', $route[2])->count_all_results($prefix . 'usi_ai_voice_command_routes');
            if ($exists === 0) {
                $CI->db->insert($prefix . 'usi_ai_voice_command_routes', [
                    'route_name' => $route[0],
                    'trigger_phrase' => $route[1],
                    'intent_key' => $route[2],
                    'target_action' => $route[3],
                    'requires_confirmation' => $route[4],
                    'is_active' => 1,
                    'sort_order' => $route[5],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        foreach (['usi_ai_voice_command_routes', 'usi_ai_voice_transcripts', 'usi_ai_voice_sessions'] as $table) {
            if ($CI->db->table_exists($prefix . $table)) {
                $CI->db->query('DROP TABLE `' . $prefix . $table . '`');
            }
        }
    }
}

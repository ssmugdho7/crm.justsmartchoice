<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_142 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_conversations')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_conversations` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `conversation_title` VARCHAR(191) NOT NULL,
                `conversation_type` VARCHAR(100) NOT NULL DEFAULT 'general',
                `source_area` VARCHAR(100) NOT NULL DEFAULT 'manual',
                `source_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `customer_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `lead_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `project_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `estimate_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `status` VARCHAR(50) NOT NULL DEFAULT 'open',
                `summary` MEDIUMTEXT NULL,
                `created_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_conversation_type` (`conversation_type`),
                KEY `idx_source` (`source_area`, `source_id`),
                KEY `idx_customer_id` (`customer_id`),
                KEY `idx_lead_id` (`lead_id`),
                KEY `idx_project_id` (`project_id`),
                KEY `idx_estimate_id` (`estimate_id`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_conversation_messages')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_conversation_messages` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `conversation_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `message_role` VARCHAR(50) NOT NULL DEFAULT 'user',
                `message_source` VARCHAR(50) NOT NULL DEFAULT 'typed',
                `message_text` MEDIUMTEXT NOT NULL,
                `intent` VARCHAR(100) NOT NULL DEFAULT 'crm_assistant',
                `action_status` VARCHAR(50) NOT NULL DEFAULT 'draft',
                `ai_response_json` MEDIUMTEXT NULL,
                `created_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_conversation_id` (`conversation_id`),
                KEY `idx_message_role` (`message_role`),
                KEY `idx_message_source` (`message_source`),
                KEY `idx_intent` (`intent`),
                KEY `idx_action_status` (`action_status`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_conversation_context')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_conversation_context` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `conversation_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `context_type` VARCHAR(100) NOT NULL DEFAULT 'manual',
                `source_area` VARCHAR(100) NOT NULL DEFAULT 'crm',
                `source_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `context_title` VARCHAR(191) NULL,
                `context_summary` MEDIUMTEXT NULL,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_conversation_id` (`conversation_id`),
                KEY `idx_context_type` (`context_type`),
                KEY `idx_source` (`source_area`, `source_id`)
            ) " . $charset);
        }

        $now = date('Y-m-d H:i:s');
        $staffId = function_exists('get_staff_user_id') ? (int) get_staff_user_id() : 0;
        $existing = (int) $CI->db->where('conversation_type', 'system_training')->count_all_results($prefix . 'usi_ai_conversations');
        if ($existing === 0) {
            $CI->db->insert($prefix . 'usi_ai_conversations', [
                'conversation_title' => 'Sammy AI System Training Conversation',
                'conversation_type' => 'system_training',
                'source_area' => 'sammy_ai',
                'source_id' => 0,
                'status' => 'open',
                'summary' => 'Default conversation used to train staff on command history, CRM context, and AI memory search.',
                'created_by' => $staffId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $conversationId = (int) $CI->db->insert_id();
            $CI->db->insert($prefix . 'usi_ai_conversation_messages', [
                'conversation_id' => $conversationId,
                'message_role' => 'assistant',
                'message_source' => 'system_seed',
                'message_text' => 'Sammy AI Conversation Engine is ready. Use it to keep chat history, CRM context, command notes, and memory search conversations inside the CRM.',
                'intent' => 'training',
                'action_status' => 'ready',
                'created_by' => $staffId,
                'created_at' => $now,
            ]);
        }
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        foreach (['usi_ai_conversation_context', 'usi_ai_conversation_messages', 'usi_ai_conversations'] as $table) {
            if ($CI->db->table_exists($prefix . $table)) {
                $CI->db->query('DROP TABLE `' . $prefix . $table . '`');
            }
        }
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_141 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_workflows')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_workflows` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `workflow_name` VARCHAR(191) NOT NULL,
                `workflow_type` VARCHAR(100) NOT NULL DEFAULT 'manual',
                `trigger_area` VARCHAR(100) NOT NULL DEFAULT 'manual',
                `trigger_event` VARCHAR(100) NOT NULL DEFAULT 'manual_run',
                `conditions_json` MEDIUMTEXT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `description` TEXT NULL,
                `created_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_workflow_type` (`workflow_type`),
                KEY `idx_trigger_area` (`trigger_area`),
                KEY `idx_is_active` (`is_active`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_workflow_steps')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_workflow_steps` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `workflow_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `step_order` INT(11) UNSIGNED NOT NULL DEFAULT 1,
                `step_name` VARCHAR(191) NOT NULL,
                `step_type` VARCHAR(100) NOT NULL DEFAULT 'action',
                `action_type` VARCHAR(100) NOT NULL DEFAULT 'create_task',
                `target_area` VARCHAR(100) NULL,
                `delay_minutes` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `requires_approval` TINYINT(1) NOT NULL DEFAULT 0,
                `step_payload` MEDIUMTEXT NULL,
                `created_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_workflow_id` (`workflow_id`),
                KEY `idx_step_type` (`step_type`),
                KEY `idx_action_type` (`action_type`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_workflow_runs')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_workflow_runs` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `workflow_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `source_area` VARCHAR(100) NOT NULL DEFAULT 'manual',
                `source_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `run_status` VARCHAR(50) NOT NULL DEFAULT 'pending',
                `input_payload` MEDIUMTEXT NULL,
                `result_message` TEXT NULL,
                `started_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `started_at` DATETIME NULL,
                `completed_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_workflow_id` (`workflow_id`),
                KEY `idx_run_status` (`run_status`),
                KEY `idx_source` (`source_area`, `source_id`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_workflow_logs')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_workflow_logs` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `workflow_run_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `workflow_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `step_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `log_type` VARCHAR(100) NOT NULL DEFAULT 'info',
                `log_message` TEXT NULL,
                `created_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_workflow_run_id` (`workflow_run_id`),
                KEY `idx_workflow_id` (`workflow_id`),
                KEY `idx_log_type` (`log_type`)
            ) " . $charset);
        }

        $existing = (int) $CI->db->where('workflow_type', 'default_estimate_follow_up')->count_all_results($prefix . 'usi_ai_workflows');
        if ($existing === 0) {
            $now = date('Y-m-d H:i:s');
            $staffId = function_exists('get_staff_user_id') ? (int) get_staff_user_id() : 0;
            $defaults = [
                ['Estimate Follow Up Workflow', 'default_estimate_follow_up', 'ai_estimates', 'status_ready', 'Creates review-first follow-up actions after an estimate becomes ready.'],
                ['Project Handoff Workflow', 'default_project_handoff', 'customer_packages', 'ready_for_project', 'Creates project handoff actions after customer package approval.'],
                ['Purchasing Workflow', 'default_purchasing', 'material_takeoff', 'ready_for_purchasing', 'Creates purchasing action queue from material takeoff records.'],
            ];
            foreach ($defaults as $row) {
                $CI->db->insert($prefix . 'usi_ai_workflows', [
                    'workflow_name' => $row[0],
                    'workflow_type' => $row[1],
                    'trigger_area' => $row[2],
                    'trigger_event' => $row[3],
                    'conditions_json' => json_encode(['mode' => 'review_first', 'requires_permission' => true]),
                    'is_active' => 1,
                    'description' => $row[4],
                    'created_by' => $staffId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $workflowId = (int) $CI->db->insert_id();
                $CI->db->insert($prefix . 'usi_ai_workflow_steps', [
                    'workflow_id' => $workflowId,
                    'step_order' => 1,
                    'step_name' => 'Create review alert',
                    'step_type' => 'action',
                    'action_type' => 'create_alert',
                    'target_area' => 'alerts_automation',
                    'delay_minutes' => 0,
                    'requires_approval' => 1,
                    'step_payload' => json_encode(['priority' => 'medium', 'status' => 'open']),
                    'created_by' => $staffId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $CI->db->insert($prefix . 'usi_ai_workflow_steps', [
                    'workflow_id' => $workflowId,
                    'step_order' => 2,
                    'step_name' => 'Log workflow result',
                    'step_type' => 'log',
                    'action_type' => 'write_log',
                    'target_area' => 'workflow_engine',
                    'delay_minutes' => 0,
                    'requires_approval' => 0,
                    'step_payload' => json_encode(['message' => 'Default workflow executed in test mode.']),
                    'created_by' => $staffId,
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
        foreach (['usi_ai_workflow_logs', 'usi_ai_workflow_runs', 'usi_ai_workflow_steps', 'usi_ai_workflows'] as $table) {
            if ($CI->db->table_exists($prefix . $table)) {
                $CI->db->query('DROP TABLE `' . $prefix . $table . '`');
            }
        }
    }
}

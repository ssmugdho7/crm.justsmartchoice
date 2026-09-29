<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_154 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_agents')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_agents` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `agent_name` VARCHAR(191) NOT NULL,
                `agent_role` VARCHAR(100) NOT NULL DEFAULT 'general',
                `department` VARCHAR(100) NULL,
                `system_prompt` MEDIUMTEXT NULL,
                `allowed_actions` MEDIUMTEXT NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'active',
                `priority` VARCHAR(50) NOT NULL DEFAULT 'normal',
                `created_by` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_role` (`agent_role`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_agent_tasks')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_agent_tasks` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `agent_id` INT(11) NOT NULL DEFAULT 0,
                `task_title` VARCHAR(191) NOT NULL,
                `related_type` VARCHAR(100) NOT NULL DEFAULT 'general',
                `related_id` INT(11) NOT NULL DEFAULT 0,
                `task_prompt` MEDIUMTEXT NULL,
                `task_result` MEDIUMTEXT NULL,
                `confidence_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `status` VARCHAR(50) NOT NULL DEFAULT 'queued',
                `created_by` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_agent` (`agent_id`),
                KEY `idx_related` (`related_type`, `related_id`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_agent_logs')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_agent_logs` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `agent_id` INT(11) NOT NULL DEFAULT 0,
                `task_id` INT(11) NOT NULL DEFAULT 0,
                `log_type` VARCHAR(100) NOT NULL DEFAULT 'activity',
                `message` MEDIUMTEXT NULL,
                `created_by` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_agent` (`agent_id`),
                KEY `idx_task` (`task_id`),
                KEY `idx_type` (`log_type`)
            ) " . $charset);
        }

        $defaults = [
            ['Sammy Estimator Agent', 'estimator', 'Estimating', 'Builds estimate scope, pricing review notes, and customer-ready proposal guidance.'],
            ['Sammy Scheduler Agent', 'scheduler', 'Production', 'Builds schedules, reminders, phase plans, and inspection-ready timing notes.'],
            ['Sammy Purchasing Agent', 'purchasing', 'Purchasing', 'Builds vendor and material purchasing actions from takeoffs and project needs.'],
            ['Sammy Project Manager Agent', 'project_manager', 'Operations', 'Builds production handoffs, project risks, task plans, and closeout steps.'],
            ['Sammy Sales Agent', 'sales', 'Sales', 'Builds follow-ups, customer communications, lead summaries, and next-step actions.'],
            ['Sammy Support Agent', 'support', 'Customer Service', 'Builds customer service notes, warranty support, and issue-resolution guidance.'],
        ];
        foreach ($defaults as $agent) {
            $exists = (int)$CI->db->where('agent_name', $agent[0])->count_all_results($prefix . 'usi_ai_agents');
            if ($exists === 0) {
                $CI->db->insert($prefix . 'usi_ai_agents', [
                    'agent_name' => $agent[0],
                    'agent_role' => $agent[1],
                    'department' => $agent[2],
                    'system_prompt' => $agent[3],
                    'allowed_actions' => 'Create draft only; human approval required before customer-facing, financial, or destructive action.',
                    'status' => 'active',
                    'priority' => 'normal',
                    'created_by' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        update_option('usi_smartchoice_seo_version', '1.5.4');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_agent_logs')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_agent_logs`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_agent_tasks')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_agent_tasks`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_agents')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_agents`'); }
    }
}

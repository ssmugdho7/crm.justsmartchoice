<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_140 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();

        if (!$CI->db->table_exists($prefix . 'usi_ai_alert_rules')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_alert_rules` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `rule_name` VARCHAR(191) NOT NULL,
                `rule_type` VARCHAR(100) NOT NULL,
                `source_area` VARCHAR(100) NOT NULL,
                `severity` VARCHAR(50) NOT NULL DEFAULT 'medium',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `description` TEXT NULL,
                `created_by` INT(11) NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `rule_type` (`rule_type`),
                KEY `source_area` (`source_area`),
                KEY `is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_alerts')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_alerts` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `alert_title` VARCHAR(191) NOT NULL,
                `alert_type` VARCHAR(100) NOT NULL,
                `source_area` VARCHAR(100) NOT NULL,
                `source_id` INT(11) NULL,
                `priority` VARCHAR(50) NOT NULL DEFAULT 'medium',
                `status` VARCHAR(50) NOT NULL DEFAULT 'open',
                `assigned_staff_id` INT(11) NULL,
                `due_date` DATE NULL,
                `message` TEXT NULL,
                `recommended_action` TEXT NULL,
                `created_by` INT(11) NULL,
                `completed_by` INT(11) NULL,
                `created_at` DATETIME NOT NULL,
                `completed_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `alert_type` (`alert_type`),
                KEY `source_area` (`source_area`),
                KEY `priority` (`priority`),
                KEY `status` (`status`),
                KEY `assigned_staff_id` (`assigned_staff_id`),
                KEY `due_date` (`due_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        if (!$CI->db->table_exists($prefix . 'usi_ai_automation_queue')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_automation_queue` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `automation_name` VARCHAR(191) NOT NULL,
                `automation_type` VARCHAR(100) NOT NULL,
                `source_area` VARCHAR(100) NOT NULL,
                `source_id` INT(11) NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
                `payload` MEDIUMTEXT NULL,
                `result_message` TEXT NULL,
                `run_after` DATETIME NULL,
                `created_by` INT(11) NULL,
                `created_at` DATETIME NOT NULL,
                `processed_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `automation_type` (`automation_type`),
                KEY `source_area` (`source_area`),
                KEY `status` (`status`),
                KEY `run_after` (`run_after`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
        }

        $exists = $CI->db->where('rule_type', 'default_estimate_follow_up')->count_all_results($prefix . 'usi_ai_alert_rules');
        if ((int) $exists === 0) {
            $now = date('Y-m-d H:i:s');
            $rules = [
                ['rule_name' => 'Estimate Follow Up Needed', 'rule_type' => 'default_estimate_follow_up', 'source_area' => 'ai_estimates', 'severity' => 'high', 'description' => 'Creates an alert when an AI estimate needs customer or estimator follow up.', 'created_by' => get_staff_user_id(), 'created_at' => $now],
                ['rule_name' => 'Project Schedule Risk', 'rule_type' => 'default_schedule_risk', 'source_area' => 'ai_scheduling', 'severity' => 'medium', 'description' => 'Creates an alert when a schedule item may need attention.', 'created_by' => get_staff_user_id(), 'created_at' => $now],
                ['rule_name' => 'Purchase Order Follow Up', 'rule_type' => 'default_purchase_follow_up', 'source_area' => 'ai_purchasing', 'severity' => 'medium', 'description' => 'Creates an alert when purchase orders require ordering or receiving follow up.', 'created_by' => get_staff_user_id(), 'created_at' => $now],
            ];
            $CI->db->insert_batch($prefix . 'usi_ai_alert_rules', $rules);
        }
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_automation_queue')) {
            $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_automation_queue`');
        }
        if ($CI->db->table_exists($prefix . 'usi_ai_alerts')) {
            $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_alerts`');
        }
        if ($CI->db->table_exists($prefix . 'usi_ai_alert_rules')) {
            $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_alert_rules`');
        }
    }
}

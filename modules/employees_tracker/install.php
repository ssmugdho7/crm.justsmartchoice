<?php
defined('BASEPATH') or exit('No direct script access allowed');

function employees_tracker_add_option_safe($name, $value = '')
{
    if (!option_exists($name)) {
        add_option($name, $value, 1);
    }
}

function employees_tracker_column_exists($table, $column)
{
    $CI = &get_instance();
    return $CI->db->field_exists($column, $table);
}

function employees_tracker_module_install()
{
    $CI = &get_instance();
    $charset = $CI->db->char_set ? $CI->db->char_set : 'utf8mb4';
    $collation = $CI->db->dbcollat ? ' COLLATE ' . $CI->db->dbcollat : '';

    if (!$CI->db->table_exists(db_prefix().'employees_tracker_locations')) {
        $CI->db->query('CREATE TABLE `'.db_prefix().'employees_tracker_locations` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `staff_id` INT(11) UNSIGNED NOT NULL,
            `lat` DECIMAL(10,7) NOT NULL,
            `lng` DECIMAL(10,7) NOT NULL,
            `accuracy` DECIMAL(10,2) NULL,
            `battery` TINYINT(3) UNSIGNED NULL,
            `source` VARCHAR(50) NULL DEFAULT "browser",
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `staff_id` (`staff_id`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET='.$charset.$collation);
    } else {
        $table = db_prefix().'employees_tracker_locations';
        $cols = [
            'battery' => 'ALTER TABLE `'.$table.'` ADD `battery` TINYINT(3) UNSIGNED NULL AFTER `accuracy`',
            'source' => 'ALTER TABLE `'.$table.'` ADD `source` VARCHAR(50) NULL DEFAULT "browser" AFTER `battery`',
        ];
        foreach ($cols as $col => $sql) {
            if (!employees_tracker_column_exists($table, $col)) {
                $CI->db->query($sql);
            }
        }
    }

    if (!$CI->db->table_exists(db_prefix().'employees_tracker_projects')) {
        $CI->db->query('CREATE TABLE `'.db_prefix().'employees_tracker_projects` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `project_id` INT(11) UNSIGNED NOT NULL,
            `address` VARCHAR(255) NULL,
            `lat` DECIMAL(10,7) NULL,
            `lng` DECIMAL(10,7) NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `project_id_unique` (`project_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET='.$charset.$collation);
    }

    if (!$CI->db->table_exists(db_prefix().'employees_tracker_assignments')) {
        $CI->db->query('CREATE TABLE `'.db_prefix().'employees_tracker_assignments` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `project_id` INT(11) UNSIGNED NOT NULL,
            `staff_id` INT(11) UNSIGNED NOT NULL,
            `appointment_id` INT(11) UNSIGNED NULL,
            `service_type` VARCHAR(191) NULL,
            `job_notes` TEXT NULL,
            `scheduled_start` DATETIME NULL,
            `enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `notify_client` TINYINT(1) NOT NULL DEFAULT 1,
            `last_eta_minutes` INT(11) NULL,
            `last_distance_text` VARCHAR(50) NULL,
            `last_eta_text` VARCHAR(100) NULL,
            `last_notified_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `project_staff_unique` (`project_id`,`staff_id`),
            KEY `appointment_id` (`appointment_id`),
            KEY `enabled` (`enabled`)
        ) ENGINE=InnoDB DEFAULT CHARSET='.$charset.$collation);
    } else {
        $table = db_prefix().'employees_tracker_assignments';
        $cols = [
            'appointment_id' => 'ALTER TABLE `'.$table.'` ADD `appointment_id` INT(11) UNSIGNED NULL AFTER `staff_id`',
            'service_type' => 'ALTER TABLE `'.$table.'` ADD `service_type` VARCHAR(191) NULL AFTER `appointment_id`',
            'job_notes' => 'ALTER TABLE `'.$table.'` ADD `job_notes` TEXT NULL AFTER `service_type`',
            'scheduled_start' => 'ALTER TABLE `'.$table.'` ADD `scheduled_start` DATETIME NULL AFTER `job_notes`',
            'notify_client' => 'ALTER TABLE `'.$table.'` ADD `notify_client` TINYINT(1) NOT NULL DEFAULT 1 AFTER `enabled`',
            'last_eta_minutes' => 'ALTER TABLE `'.$table.'` ADD `last_eta_minutes` INT(11) NULL AFTER `notify_client`',
            'last_distance_text' => 'ALTER TABLE `'.$table.'` ADD `last_distance_text` VARCHAR(50) NULL AFTER `last_eta_minutes`',
            'last_eta_text' => 'ALTER TABLE `'.$table.'` ADD `last_eta_text` VARCHAR(100) NULL AFTER `last_distance_text`',
            'last_notified_at' => 'ALTER TABLE `'.$table.'` ADD `last_notified_at` DATETIME NULL AFTER `last_eta_text`',
            'updated_at' => 'ALTER TABLE `'.$table.'` ADD `updated_at` DATETIME NULL AFTER `created_at`',
        ];
        foreach ($cols as $col => $sql) {
            if (!employees_tracker_column_exists($table, $col)) {
                $CI->db->query($sql);
            }
        }
    }

    if (!$CI->db->table_exists(db_prefix().'employees_tracker_notifications')) {
        $CI->db->query('CREATE TABLE `'.db_prefix().'employees_tracker_notifications` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `assignment_id` INT(11) UNSIGNED NOT NULL,
            `project_id` INT(11) UNSIGNED NOT NULL,
            `staff_id` INT(11) UNSIGNED NOT NULL,
            `clientid` INT(11) UNSIGNED NULL,
            `contact_id` INT(11) UNSIGNED NULL,
            `eta_minutes` INT(11) NULL,
            `distance_text` VARCHAR(50) NULL,
            `message` TEXT NULL,
            `sent_by` VARCHAR(50) NULL DEFAULT "email",
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `assignment_id` (`assignment_id`),
            KEY `project_id` (`project_id`),
            KEY `staff_id` (`staff_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET='.$charset.$collation);
    }

    employees_tracker_add_option_safe('employees_tracker_poll_interval', '30');
    employees_tracker_add_option_safe('employees_tracker_allow_staff_self_share', '1');
    employees_tracker_add_option_safe('employees_tracker_google_api_key', '');
    employees_tracker_add_option_safe('employees_tracker_eta_notify_minutes', '30');
    employees_tracker_add_option_safe('employees_tracker_enable_email_notifications', '1');
    employees_tracker_add_option_safe('employees_tracker_distance_provider', 'google');
    employees_tracker_add_option_safe('employees_tracker_default_service_type', 'Scheduled construction service');
    employees_tracker_add_option_safe('employees_tracker_use_test_location_button', '1');
}

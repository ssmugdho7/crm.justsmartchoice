<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_132 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // Create separate table for reschedule requests
        if (! $CI->db->table_exists(db_prefix() . 'appointly_reschedule_requests')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_reschedule_requests` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `appointment_id` INT UNSIGNED NOT NULL,
            `requested_date` DATE NOT NULL,
            `requested_time` TIME NOT NULL,
            `reason` TEXT NULL,
            `status` ENUM('pending', 'approved', 'denied') DEFAULT 'pending',
            `requested_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `processed_by` INT NULL,
            `processed_at` DATETIME NULL,
            `denial_reason` TEXT NULL,
            INDEX `idx_appointment_id` (`appointment_id`),
            INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }
    }
}

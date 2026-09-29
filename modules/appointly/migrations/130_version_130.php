<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_130 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        log_message('info', 'Appointly Migration 130: Starting comprehensive cleanup for v1.3.0');

        // First, ensure all appointments have proper status values before dropping legacy fields
        $this->migrateStatusData($CI);

        // Clean up appointments table - remove legacy status columns
        $this->cleanupAppointmentsTable($CI);

        // Clean up services table - remove legacy availability columns  
        $this->cleanupServicesTable($CI);

        // Remove legacy appointment types table if it exists
        $this->removeLegacyTables($CI);

        // Fix database helper syntax errors
        $this->fixDatabaseHelperSyntax($CI);

        log_message('info', 'Appointly Migration 130: Cleanup completed successfully');
    }

    private function migrateStatusData($CI)
    {
        // Migrate existing status data to new enum field
        if (
            $CI->db->field_exists('approved', db_prefix() . 'appointly_appointments') &&
            $CI->db->field_exists('finished', db_prefix() . 'appointly_appointments') &&
            $CI->db->field_exists('cancelled', db_prefix() . 'appointly_appointments')
        ) {

            $CI->db->query("UPDATE " . db_prefix() . "appointly_appointments 
                SET status = CASE 
                    WHEN finished = 1 THEN 'completed'
                    WHEN cancelled = 1 THEN 'cancelled'
                    WHEN approved = 1 THEN 'in-progress' 
                    ELSE 'pending' 
                END 
                WHERE (cancelled IS NOT NULL OR finished IS NOT NULL OR approved IS NOT NULL)");

            log_message('info', 'Appointly Migration 130: Status data migrated successfully for all appointments');
        }
    }

    private function cleanupAppointmentsTable($CI)
    {
        $table = db_prefix() . 'appointly_appointments';

        // Remove legacy status columns
        $legacyColumns = ['approved', 'finished', 'cancelled'];

        foreach ($legacyColumns as $column) {
            if ($CI->db->field_exists($column, $table)) {
                $CI->db->query("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
                log_message('info', "Appointly Migration 130: Removed column '{$column}' from appointments table");
            }
        }

        // Remove legacy type_id column if it exists
        if ($CI->db->field_exists('type_id', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` DROP COLUMN `type_id`");
            log_message('info', 'Appointly Migration 130: Removed type_id column from appointments table');
        }

        // Remove buffer columns from appointments (they belong in services table only)
        $bufferColumns = ['buffer_before', 'buffer_after'];
        foreach ($bufferColumns as $column) {
            if ($CI->db->field_exists($column, $table)) {
                $CI->db->query("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
                log_message('info', "Appointly Migration 130: Removed column '{$column}' from appointments table");
            }
        }
    }

    private function cleanupServicesTable($CI)
    {
        $table = db_prefix() . 'appointly_services';

        // Remove legacy availability columns from services table
        $legacyColumns = [
            'availability_days',
            'availability_hours_start',
            'availability_hours_end',
            'working_hours',
            'staff_members'
        ];

        foreach ($legacyColumns as $column) {
            if ($CI->db->field_exists($column, $table)) {
                $CI->db->query("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
                log_message('info', "Appointly Migration 130: Removed column '{$column}' from services table");
            }
        }

        $appointments_booking_services_availability = get_option('appointments_booking_services_availability');

        if (!$appointments_booking_services_availability) {
            update_option('appointments_booking_services_availability', json_encode([1, 2]));
        }
    }

    private function removeLegacyTables($CI)
    {
        // Remove appointment types table if it exists
        $legacyTable = db_prefix() . 'appointly_appointment_types';
        if ($CI->db->table_exists($legacyTable)) {
            $CI->db->query("DROP TABLE `{$legacyTable}`");
            log_message('info', 'Appointly Migration 130: Removed legacy appointment_types table');
        }
    }

    private function fixDatabaseHelperSyntax($CI)
    {
        // Fix any database inconsistencies that might have been created by syntax errors
        $table = db_prefix() . 'appointly_appointments';

        // Ensure provider_id column exists and is properly positioned
        if (!$CI->db->field_exists('provider_id', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `provider_id` int(11) DEFAULT NULL AFTER `service_id`");
            log_message('info', 'Appointly Migration 130: Added provider_id column to appointments table');
        }

        // Ensure status column exists with proper enum values
        if (!$CI->db->field_exists('status', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `status` ENUM('pending', 'cancelled', 'completed', 'no-show', 'in-progress') NOT NULL DEFAULT 'in-progress' AFTER `hash`");
            log_message('info', 'Appointly Migration 130: Added status column to appointments table');
        }

        // Ensure end_hour column exists
        if (!$CI->db->field_exists('end_hour', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `end_hour` varchar(191) NOT NULL AFTER `start_hour`");
            log_message('info', 'Appointly Migration 130: Added end_hour column to appointments table');
        }

        // Ensure duration column exists
        if (!$CI->db->field_exists('duration', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `duration` varchar(100) DEFAULT NULL AFTER `start_hour`");
            log_message('info', 'Appointly Migration 130: Added duration column to appointments table');
        }
    }
}

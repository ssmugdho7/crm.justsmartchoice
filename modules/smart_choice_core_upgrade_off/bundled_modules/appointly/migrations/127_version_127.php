<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_127 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $appointments_show_past_dates = get_option('appointments_show_past_dates');
        if (!$appointments_show_past_dates) {
            update_option('appointments_show_past_dates', 0);
        }

        // Add new option for booking services availability
        $appointments_enable_terms_conditions = get_option('appointments_enable_terms_conditions');
        if (!$appointments_enable_terms_conditions) {
            update_option('appointments_enable_terms_conditions', 0);
        }
        
        $appointments_booking_services_availability = get_option('appointments_booking_services_availability');
        
        if (!$appointments_booking_services_availability) {
            update_option('appointments_booking_services_availability', json_encode([1,2]));
        }

        // Create company schedule table
        $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "appointly_company_schedule` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `weekday` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
            `start_time` TIME NOT NULL DEFAULT '09:00:00',
            `end_time` TIME NOT NULL DEFAULT '17:00:00',
            `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");

        // Populate company schedule with default values
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $insertData = [];

        foreach ($weekdays as $index => $day) {
            $isWeekend = ($index > 4); // Saturday and Sunday
            $insertData[] = [
                'weekday' => $day,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_enabled' => $isWeekend ? 0 : 1
            ];
        }

        $CI->db->insert_batch(db_prefix() . 'appointly_company_schedule', $insertData);

        // Create staff working hours table
        $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "appointly_staff_working_hours` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `staff_id` int(11) NOT NULL,
            `weekday` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
            `start_time` TIME NOT NULL,
            `end_time` TIME NOT NULL,
            `is_available` TINYINT(1) NOT NULL DEFAULT 1,
            `use_company_schedule` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            KEY `staff_id` (`staff_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");

        // Update services table - remove working_hours, staff_members fields
        if ($CI->db->field_exists('working_hours', db_prefix() . 'appointly_services')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_services` DROP COLUMN `working_hours`;");
        }

        if ($CI->db->field_exists('staff_members', db_prefix() . 'appointly_services')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_services` DROP COLUMN `staff_members`;");
        }

        // Add buffer times to services
        if (!$CI->db->field_exists('buffer_before', db_prefix() . 'appointly_services')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_services` 
                ADD COLUMN `buffer_before` int(11) DEFAULT 0 AFTER `duration`,
                ADD COLUMN `buffer_after` int(11) DEFAULT 0 AFTER `buffer_before`;");
        }

        // Update service_staff table - add is_primary flag, remove working_hours
        if (!$CI->db->field_exists('is_primary', db_prefix() . 'appointly_service_staff')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_service_staff` 
                ADD COLUMN `is_primary` TINYINT(1) DEFAULT 0 AFTER `is_provider`;");

            // Mark the first provider for each service as primary
            $CI->db->query("
                UPDATE " . db_prefix() . "appointly_service_staff ss1
                JOIN (
                    SELECT service_id, MIN(id) as min_id
                    FROM " . db_prefix() . "appointly_service_staff
                    WHERE is_provider = 1
                    GROUP BY service_id
                ) ss2 ON ss1.service_id = ss2.service_id AND ss1.id = ss2.min_id
                SET ss1.is_primary = 1
            ");
        }

        if ($CI->db->field_exists('working_hours', db_prefix() . 'appointly_service_staff')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_service_staff` DROP COLUMN `working_hours`;");
        }
        // Check if the columns exist before trying to remove them
        if ($CI->db->field_exists('availability_days', db_prefix() . 'appointly_services')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'appointly_services` DROP COLUMN `availability_days`');
        }

        if ($CI->db->field_exists('availability_hours_start', db_prefix() . 'appointly_services')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'appointly_services` DROP COLUMN `availability_hours_start`');
        }

        if ($CI->db->field_exists('availability_hours_end', db_prefix() . 'appointly_services')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'appointly_services` DROP COLUMN `availability_hours_end`');
        }

        // Update appointments table - add status field, buffer fields
        if (!$CI->db->field_exists('status', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_appointments` 
                ADD COLUMN `status` ENUM('pending', 'cancelled', 'completed', 'no-show', 'in-progress') 
                NOT NULL DEFAULT 'in-progress' AFTER `hash`");

            // Log the migration
            log_message('info', 'Appointly Migration 127: Migrated legacy status fields to new status enum');

            // Drop legacy status fields only if we can safely remove them
            $legacy_fields = ['approved', 'cancelled', 'finished'];

            // First verify all appointments have a valid status value
            $statuses = $CI->db->query("SELECT DISTINCT status FROM " . db_prefix() . "appointly_appointments")->result_array();
            $status_values = array_column($statuses, 'status');
            $valid_statuses = ['pending', 'cancelled', 'completed', 'no-show', 'in-progress'];
            $has_invalid = false;

            foreach ($status_values as $status) {
                if (!in_array($status, $valid_statuses)) {
                    $has_invalid = true;
                    log_message('error', "Appointly Migration 127: Found invalid status value: $status");
                    break;
                }
            }

            if (!$has_invalid) {
                // Safe to drop the legacy fields
                foreach ($legacy_fields as $field) {
                    if ($CI->db->field_exists($field, db_prefix() . 'appointly_appointments')) {
                        $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_appointments` DROP COLUMN `$field`");
                        log_message('info', "Appointly Migration 127: Dropped legacy status field: $field");
                    }
                }
            } else {
                log_message('error', "Appointly Migration 127: Found invalid status values, keeping legacy fields for safety");
            }
        }

        // Ensure we have appointment-service connections for all appointments
        $CI->db->query("INSERT IGNORE INTO `" . db_prefix() . "appointly_appointment_services`
            (appointment_id, service_id)
            SELECT id, service_id
            FROM `" . db_prefix() . "appointly_appointments`
            WHERE service_id IS NOT NULL 
            AND id NOT IN (SELECT appointment_id FROM `" . db_prefix() . "appointly_appointment_services`)");

        // Email templates
        create_email_template(
            'Your appointment has been updated!',
            '<span style=\"font-size: 12pt;\"> Hello {appointment_client_name}.</span><br /><br /><span style=\"font-size: 12pt;\"> Your appointment has been updated.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Updated Appointment Details:</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description: Appointly helps Smart Choice Contractors USA manage appointment booking, public scheduling forms, staff calendars, customer booking requests, appointment notifications, and CRM scheduling workflows inside Perfex CRM. The module is updated for PHP 8.5 target compatibility, Perfex CRM 3.4.1 usage, safe database upgrades, clean labels, and a polished Smart Choice experience for public booking pages and internal appointment management.
            'appointly',
            'Appointment updated (Sent to Contact)',
            'appointment-updated-to-contact'
        );

        create_email_template(
            'Your appointment has been updated!',
            '<span style=\"font-size: 12pt;\"> Hello {staff_firstname} {staff_lastname}.</span><br /><br /><span style=\"font-size: 12pt;\"> An appointment that you are attending has been updated.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Updated Appointment Details:</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description: Appointly helps Smart Choice Contractors USA manage appointment booking, public scheduling forms, staff calendars, customer booking requests, appointment notifications, and CRM scheduling workflows inside Perfex CRM. The module is updated for PHP 8.5 target compatibility, Perfex CRM 3.4.1 usage, safe database upgrades, clean labels, and a polished Smart Choice experience for public booking pages and internal appointment management.
            'appointly',
            'Appointment updated (Sent to Staff)',
            'appointment-updated-to-staff'
        );
    }
}

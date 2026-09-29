<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_131 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        log_message('info', 'Appointly Migration 131: Starting critical fixes for v1.3.1');

        try {
            // Start transaction for data integrity
            $CI->db->trans_start();

            // Fix database helper syntax errors first
            $this->fixDatabaseHelperSyntax($CI);

            // Ensure all required tables exist
            $this->ensureRequiredTables($CI);

            // Fix missing columns and data integrity
            $this->fixMissingColumns($CI);

            // Ensure proper service-staff assignments
            $this->ensureServiceStaffAssignments($CI);

            // Fix menu registration
            $this->fixMenuRegistration($CI);

            // Set default options
            $this->setDefaultOptions($CI);

            // Commit transaction
            $CI->db->trans_complete();

            if ($CI->db->trans_status() === FALSE) {
                throw new Exception('Database transaction failed');
            }

            log_message('info', 'Appointly Migration 131: All fixes completed successfully');
            return true;
        } catch (Exception $e) {
            $CI->db->trans_rollback();
            log_message('error', 'Appointly Migration 131 failed: ' . $e->getMessage());
            // Don't re-throw to prevent migration system from breaking
            return false;
        }
    }

    private function fixDatabaseHelperSyntax($CI)
    {
        $table = db_prefix() . 'appointly_appointments';

        // Check if table exists first
        if (!$CI->db->table_exists($table)) {
            log_message('info', 'Appointly Migration 131: Appointments table does not exist, skipping column fixes');
            return;
        }

        // Fix the syntax error in database helper - ensure end_hour column exists
        if (!$CI->db->field_exists('end_hour', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `end_hour` varchar(191) NOT NULL DEFAULT '' AFTER `start_hour`");
            log_message('info', 'Appointly Migration 131: Added end_hour column');
        }

        // Ensure duration column exists
        if (!$CI->db->field_exists('duration', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `duration` varchar(100) DEFAULT NULL AFTER `start_hour`");
            log_message('info', 'Appointly Migration 131: Added duration column');
        }

        // Ensure status column exists with proper enum values
        if (!$CI->db->field_exists('status', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `status` ENUM('pending', 'cancelled', 'completed', 'no-show', 'in-progress') NOT NULL DEFAULT 'in-progress' AFTER `hash`");
            log_message('info', 'Appointly Migration 131: Added status column');
        }

        // Ensure provider_id column exists
        if (!$CI->db->field_exists('provider_id', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `provider_id` int(11) DEFAULT NULL AFTER `service_id`");
            log_message('info', 'Appointly Migration 131: Added provider_id column');
        }

        // Ensure service_id column exists
        if (!$CI->db->field_exists('service_id', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `service_id` int(11) DEFAULT NULL AFTER `id`");
            log_message('info', 'Appointly Migration 131: Added service_id column');
        }

        // Ensure date_created column exists
        if (!$CI->db->field_exists('date_created', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `date_created` datetime DEFAULT CURRENT_TIMESTAMP AFTER `id`");
            log_message('info', 'Appointly Migration 131: Added date_created column');
        }

        // Fix missing critical columns that exist in production
        $missing_columns = [
            'files' => 'text DEFAULT NULL',
            'timezone' => 'varchar(100) DEFAULT NULL',
            'invoice_id' => 'int(11) DEFAULT NULL',
            'invoice_date' => 'datetime DEFAULT NULL'
        ];

        foreach ($missing_columns as $column => $definition) {
            if (!$CI->db->field_exists($column, $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
                log_message('info', "Appointly Migration 131: Added missing column {$column}");
            }
        }

        // Handle legacy status columns migration
        $this->migrateLegacyStatusColumns($CI, $table);
    }

    private function ensureRequiredTables($CI)
    {
        // Ensure services table exists
        if (!$CI->db->table_exists(db_prefix() . 'appointly_services')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_services` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(191) NOT NULL,
                `description` text DEFAULT NULL,
                `duration` int(11) DEFAULT 60,
                `price` decimal(15,2) DEFAULT 0.00,
                `color` varchar(10) DEFAULT '#28B8DA',
                `active` tinyint(1) DEFAULT 1,
                `buffer_before` int(11) DEFAULT 0,
                `buffer_after` int(11) DEFAULT 0,
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
            log_message('info', 'Appointly Migration 131: Created services table');
        }

        // Ensure service_staff table exists
        if (!$CI->db->table_exists(db_prefix() . 'appointly_service_staff')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_service_staff` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `service_id` int(11) NOT NULL,
                `staff_id` int(11) NOT NULL,
                `is_provider` tinyint(1) DEFAULT 1,
                `is_primary` tinyint(1) DEFAULT 0,
                `working_hours` text DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `service_id` (`service_id`),
                KEY `staff_id` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
            log_message('info', 'Appointly Migration 131: Created service_staff table');
        }

        // Ensure company_schedule table exists
        if (!$CI->db->table_exists(db_prefix() . 'appointly_company_schedule')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_company_schedule` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `weekday` varchar(20) NOT NULL,
                `start_time` time DEFAULT '09:00:00',
                `end_time` time DEFAULT '17:00:00',
                `is_enabled` tinyint(1) DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `weekday` (`weekday`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");

            // Insert default company schedule
            $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($weekdays as $day) {
                $is_enabled = in_array($day, ['Saturday', 'Sunday']) ? 0 : 1;
                $CI->db->query("INSERT IGNORE INTO `" . db_prefix() . "appointly_company_schedule` 
                    (`weekday`, `start_time`, `end_time`, `is_enabled`) 
                    VALUES ('{$day}', '09:00:00', '17:00:00', {$is_enabled})");
            }
            log_message('info', 'Appointly Migration 131: Created company_schedule table');
        }

        // Ensure staff_working_hours table exists
        if (!$CI->db->table_exists(db_prefix() . 'appointly_staff_working_hours')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_staff_working_hours` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `staff_id` int(11) NOT NULL,
                `weekday` varchar(20) NOT NULL,
                `start_time` time DEFAULT NULL,
                `end_time` time DEFAULT NULL,
                `is_enabled` tinyint(1) DEFAULT 1,
                `use_company_schedule` tinyint(1) DEFAULT 1,
                PRIMARY KEY (`id`),
                KEY `staff_id` (`staff_id`),
                KEY `weekday` (`weekday`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
            log_message('info', 'Appointly Migration 131: Created staff_working_hours table');
        }
    }

    private function fixMissingColumns($CI)
    {
        $services_table = db_prefix() . 'appointly_services';

        // Check if services table exists first
        if (!$CI->db->table_exists($services_table)) {
            log_message('info', 'Appointly Migration 131: Services table does not exist, skipping column fixes');
            return;
        }

        // Add buffer columns to services table if missing
        if (!$CI->db->field_exists('buffer_before', $services_table)) {
            $CI->db->query("ALTER TABLE `{$services_table}` ADD COLUMN `buffer_before` int(11) DEFAULT 0 AFTER `active`");
            log_message('info', 'Appointly Migration 131: Added buffer_before to services');
        }

        if (!$CI->db->field_exists('buffer_after', $services_table)) {
            $CI->db->query("ALTER TABLE `{$services_table}` ADD COLUMN `buffer_after` int(11) DEFAULT 0 AFTER `buffer_before`");
            log_message('info', 'Appointly Migration 131: Added buffer_after to services');
        }

        // Add is_primary column to service_staff table if missing
        $service_staff_table = db_prefix() . 'appointly_service_staff';
        if ($CI->db->table_exists($service_staff_table) && !$CI->db->field_exists('is_primary', $service_staff_table)) {
            $CI->db->query("ALTER TABLE `{$service_staff_table}` ADD COLUMN `is_primary` tinyint(1) DEFAULT 0 AFTER `is_provider`");

            // Set first provider per service as primary
            $CI->db->query("
                UPDATE {$service_staff_table} ss1
                JOIN (
                    SELECT service_id, MIN(id) as min_id
                    FROM {$service_staff_table}
                    WHERE is_provider = 1
                    GROUP BY service_id
                ) ss2 ON ss1.service_id = ss2.service_id AND ss1.id = ss2.min_id
                SET ss1.is_primary = 1
            ");

            log_message('info', 'Appointly Migration 131: Added is_primary to service_staff');
        }

        // Fix missing end_hour values by calculating from start_hour + duration
        $appointments_table = db_prefix() . 'appointly_appointments';

        if ($CI->db->table_exists($appointments_table) && $CI->db->table_exists($services_table)) {
            $CI->db->query("UPDATE `{$appointments_table}` a
                JOIN `{$services_table}` s ON a.service_id = s.id
                SET a.end_hour = DATE_FORMAT(
                    DATE_ADD(
                        STR_TO_DATE(CONCAT(a.date, ' ', a.start_hour), '%Y-%m-%d %H:%i'), 
                        INTERVAL s.duration MINUTE
                    ), '%H:%i'
                )
                WHERE (a.end_hour = '' OR a.end_hour IS NULL) AND a.service_id IS NOT NULL");

            log_message('info', 'Appointly Migration 131: Fixed missing end_hour values');
        }
    }

    private function ensureServiceStaffAssignments($CI)
    {
        // Check if there are any service staff assignments
        $staff_assignments_count = 0;
        if ($CI->db->table_exists(db_prefix() . 'appointly_service_staff')) {
            $staff_assignments_count = $CI->db->count_all(db_prefix() . 'appointly_service_staff');
        }

        if ($staff_assignments_count == 0) {
            // Create default service if none exist
            $services_count = 0;
            if ($CI->db->table_exists(db_prefix() . 'appointly_services')) {
                $services_count = $CI->db->count_all(db_prefix() . 'appointly_services');
            }

            if ($services_count == 0) {
                // Insert default service
                $CI->db->insert(db_prefix() . 'appointly_services', [
                    'name' => 'General Consultation',
                    'description' => 'General consultation service',
                    'duration' => 60,
                    'price' => 0.00,
                    'color' => '#28B8DA',
                    'active' => 1,
                    'buffer_before' => 0,
                    'buffer_after' => 0
                ]);
                $service_id = $CI->db->insert_id();
                log_message('info', 'Appointly Migration 131: Created default service');
            } else {
                // Get first service
                $service = $CI->db->get(db_prefix() . 'appointly_services', 1)->row();
                $service_id = $service->id;
            }

            // Assign service to admin (staff_id = 1) using INSERT IGNORE to prevent duplicates
            $CI->db->query("INSERT IGNORE INTO `" . db_prefix() . "appointly_service_staff` 
                (service_id, staff_id, is_provider, is_primary) 
                VALUES ({$service_id}, 1, 1, 1)");
            log_message('info', 'Appointly Migration 131: Assigned default service to admin');
        }
    }

    private function migrateLegacyStatusColumns($CI, $table)
    {
        // Check if old status columns exist and migrate them
        $legacy_columns = ['approved', 'finished', 'cancelled'];
        $has_legacy = false;

        foreach ($legacy_columns as $col) {
            if ($CI->db->field_exists($col, $table)) {
                $has_legacy = true;
                break;
            }
        }

        if ($has_legacy) {
            log_message('info', 'Appointly Migration 131: Migrating legacy status columns');

            // This ensures ALL appointments are properly migrated, not just pending/in-progress ones
            $CI->db->query("UPDATE `{$table}` SET 
                status = CASE 
                    WHEN cancelled = 1 THEN 'cancelled'
                    WHEN finished = 1 THEN 'completed'
                    WHEN approved = 1 THEN 'in-progress'
                    ELSE 'pending'
                END 
                WHERE (cancelled IS NOT NULL OR finished IS NOT NULL OR approved IS NOT NULL)");

            log_message('info', 'Appointly Migration 131: Successfully migrated all appointment statuses from legacy columns');

            // Drop legacy columns
            foreach ($legacy_columns as $col) {
                if ($CI->db->field_exists($col, $table)) {
                    $CI->db->query("ALTER TABLE `{$table}` DROP COLUMN `{$col}`");
                    log_message('info', "Appointly Migration 131: Dropped legacy column {$col}");
                }
            }
        }
    }

    private function fixMenuRegistration($CI)
    {
        // Reset menu to ensure Services menu appears
        if (function_exists('update_option')) {
            // Reset aside menu to force regeneration
            update_option('aside_menu_active', '[]');
            log_message('info', 'Appointly Migration 131: Reset aside menu for regeneration');
        }
    }

    private function setDefaultOptions($CI)
    {
        // Set default booking services availability if not set
        $appointments_booking_services_availability = get_option('appointments_booking_services_availability');
        if (!$appointments_booking_services_availability) {
            update_option('appointments_booking_services_availability', json_encode([1]));
        }

        // Set other default options
        if (!get_option('appointly_show_clients_schedule_button')) {
            add_option('appointly_show_clients_schedule_button', 0);
        }

        if (!get_option('appointly_tab_on_clients_page')) {
            add_option('appointly_tab_on_clients_page', 0);
        }
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_125 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // If type_id exists in appointments table, remove it
        if ($CI->db->field_exists('type_id', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments DROP COLUMN `type_id`");
        }

        if ($CI->db->field_exists('invoice_id', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments DROP COLUMN `invoice_id`");
        }

        if ($CI->db->field_exists('invoice_date', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments DROP COLUMN `invoice_date`");
        }

        add_option('appointly_show_summary', '1');
        add_option('external_form_heading', 'Schedule a Meeting');
        add_option(
            'external_form_description',
            'Complete the form below to arrange your session with our team'
        );

        // Remove callbacks tables
        $CI->db->query("DROP TABLE IF EXISTS " . db_prefix() . "appointly_callbacks");
        $CI->db->query("DROP TABLE IF EXISTS " . db_prefix() . "appointly_callbacks_assignees");

        // Remove options
        delete_option('appointly_callbacks_enabled');
        delete_option('appointly_callbacks_form_enabled');

        // Create services table if it doesn't exist
        if (!$CI->db->table_exists(db_prefix() . 'appointly_services')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_services` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(191) NOT NULL,
                `description` text DEFAULT NULL,
                `duration` int(11) DEFAULT 60,
                `price` decimal(15,2) DEFAULT 0.00,
                `color` varchar(10) DEFAULT '#28B8DA',
                `active` tinyint(1) DEFAULT 1,
                `availability_days` varchar(191) DEFAULT '1,2,3,4,5',
                `availability_hours_start` time DEFAULT '09:00:00',
                `availability_hours_end` time DEFAULT '17:00:00',
                `working_hours` text DEFAULT NULL,
                `staff_members` text DEFAULT NULL COMMENT 'JSON array of staff IDs',
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        // Create service assignments table for staff BEFORE trying to insert data
        if (!$CI->db->table_exists(db_prefix() . 'appointly_service_staff')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_service_staff` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `service_id` int(11) NOT NULL,
                `staff_id` int(11) NOT NULL,
                `is_provider` tinyint(1) DEFAULT 1,
                `working_hours` text DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `service_id` (`service_id`),
                KEY `staff_id` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        //  required columns to appointments table
        //  service_id to appointments table
        if (!$CI->db->field_exists('service_id', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments 
                ADD COLUMN `service_id` int(11) DEFAULT NULL AFTER `id`");
        }

        //  provider_id to appointments table
        if (!$CI->db->field_exists('provider_id', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments 
                ADD COLUMN `provider_id` int(11) DEFAULT NULL AFTER `service_id`");
        }

        //  date_created to appointments table
        if (!$CI->db->field_exists('date_created', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments 
                ADD COLUMN `date_created` datetime DEFAULT CURRENT_TIMESTAMP AFTER `id`");
        }

        //  end_hour to appointments table if missing
        if (!$CI->db->field_exists('end_hour', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments
                ADD COLUMN `end_hour` varchar(191) NOT NULL AFTER `start_hour`");
        }

        $services_count = $CI->db->count_all(db_prefix() . 'appointly_services');

        if ($services_count == 0) {
            // Check if admin staff exists before assigning services
            $admin_exists = $CI->db->get_where(db_prefix() . 'staff', ['staffid' => 1])->num_rows() > 0;
            $default_staff_id = $admin_exists ? 1 : null;

            // Get first available staff member if admin doesn't exist
            if (!$admin_exists) {
                $first_staff = $CI->db->select('staffid')->from(db_prefix() . 'staff')->limit(1)->get()->row();
                $default_staff_id = $first_staff ? $first_staff->staffid : null;
            }

            $working_hours = [
                'monday' => ['enabled' => 'on', 'start_time' => '09:00', 'end_time' => '17:00'],
                'tuesday' => ['enabled' => 'on', 'start_time' => '09:00', 'end_time' => '17:00'],
                'wednesday' => ['enabled' => 'on', 'start_time' => '09:00', 'end_time' => '17:00'],
                'thursday' => ['enabled' => 'on', 'start_time' => '09:00', 'end_time' => '17:00'],
                'friday' => ['enabled' => 'on', 'start_time' => '09:00', 'end_time' => '17:00'],
                'saturday' => ['enabled' => 'off', 'start_time' => '09:00', 'end_time' => '17:00'],
                'sunday' => ['enabled' => 'off', 'start_time' => '09:00', 'end_time' => '17:00']
            ];

            $default_services = [
                [
                    'name' => 'Initial Consultation',
                    'description' => 'First meeting to discuss your needs and requirements',
                    'duration' => 30,
                    'price' => 0,
                    'color' => '#3B82F6', // Tailwind blue-500
                    'active' => 1,
                    'availability_days' => '1,2,3,4,5',
                    'availability_hours_start' => '09:00:00',
                    'availability_hours_end' => '17:00:00',
                    'staff_members' => $default_staff_id ? json_encode([$default_staff_id]) : json_encode([]),
                    'working_hours' => json_encode($working_hours)
                ],
                [
                    'name' => 'Business Strategy Session',
                    'description' => 'In-depth discussion about business strategy and planning',
                    'duration' => 60,
                    'price' => 150,
                    'color' => '#10B981', // Tailwind emerald-500
                    'active' => 1,
                    'availability_days' => '1,2,3,4,5',
                    'availability_hours_start' => '09:00:00',
                    'availability_hours_end' => '17:00:00',
                    'staff_members' => $default_staff_id ? json_encode([$default_staff_id]) : json_encode([]),
                    'working_hours' => json_encode($working_hours)
                ],
                [
                    'name' => 'Project Review Meeting',
                    'description' => 'Regular project status review and updates',
                    'duration' => 45,
                    'price' => 100,
                    'color' => '#8B5CF6', // Tailwind violet-500
                    'active' => 1,
                    'availability_days' => '1,2,3,4,5',
                    'availability_hours_start' => '09:00:00',
                    'availability_hours_end' => '17:00:00',
                    'staff_members' => $default_staff_id ? json_encode([$default_staff_id]) : json_encode([]),
                    'working_hours' => json_encode($working_hours)
                ]
            ];

            // Insert services and assign to staff if available
            foreach ($default_services as $service) {
                $CI->db->insert(db_prefix() . 'appointly_services', $service);
                $service_id = $CI->db->insert_id();

                // Only assign to staff if staff exists
                if ($default_staff_id) {
                    $CI->db->insert(db_prefix() . 'appointly_service_staff', [
                        'service_id' => $service_id,
                        'staff_id' => $default_staff_id,
                        'is_provider' => 1,
                        'working_hours' => $service['working_hours']
                    ]);
                }
            }
        }

        // Check if there are any service staff assignments
        $staff_assignments_count = $CI->db->count_all(db_prefix() . 'appointly_service_staff');

        if ($staff_assignments_count == 0) {
            // Get all services
            $services = $CI->db->get(db_prefix() . 'appointly_services')->result_array();

            // Get first available staff member
            $first_staff = $CI->db->select('staffid')->from(db_prefix() . 'staff')->limit(1)->get()->row();

            if ($first_staff) {
                // Assign each service to first available staff
                foreach ($services as $service) {
                    $CI->db->insert(db_prefix() . 'appointly_service_staff', [
                        'service_id' => $service['id'],
                        'staff_id' => $first_staff->staffid,
                        'is_provider' => 1,
                        'working_hours' => $service['working_hours'] ?? null
                    ]);
                }
            }
        }

        // Clean up old option
        delete_option('appointly_default_working_hours');
    }
}

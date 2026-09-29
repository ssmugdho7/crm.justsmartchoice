<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (! function_exists('init_appointly_database_tables')) {
    /**
     * Init installation tables creation in a database
     */
    function init_appointly_database_tables()
    {
        $CI = &get_instance();

        add_option('appointly_show_clients_schedule_button', 0); // Show clients schedule button
        add_option('appointly_tab_on_clients_page', 0); // Show tab on clients page
        add_option('appointly_also_delete_in_google_calendar', 1); // Also delete in google calendar
        add_option('appointly_google_client_secret'); // Google client secretq
        add_option('appointly_outlook_client_id'); // Outlook client id
        add_option('appointly_view_all_in_calendar', 0); // View all in calendar
        add_option('appointments_googlesync_show_in_table', 0); // Show in table
        add_option('appointments_googlesync_show_from', 'last_3_months'); // Show from
        add_option('appointments_enable_terms_conditions', 1); // Enable terms and conditions
        add_option('appointly_blocked_days'); // Blocked days
        add_option('appointly_appointments_recaptcha', '0'); // Appointments recaptcha
        add_option('external_form_heading', 'Schedule a Meeting'); // External form heading
        add_option('appointly_show_summary', '1'); // Show summary
        add_option('appointly_today_widget_enabled', '1'); // Today widget enabled
        add_option('appointly_upcoming_widget_enabled', '1'); // Upcoming widget enabled
        add_option('appointly_upcoming_widget_range', '7_days'); // Upcoming widget range
        add_option('appointments_booking_services_availability', json_encode([]));
        add_option('appointly_invoice_tax_type', 'none'); // none, custom, system
        add_option('appointly_invoice_system_tax', ''); // Selected tax ID for system taxes
        add_option('appointly_invoice_default_vat', '0'); // Custom tax percentage (backward compatibility)
        add_option('appointly_create_invoice_when_completed', 0); // Create invoice when appointment is completed
        add_option('appointly_auto_enable_google_meet', 0); // Auto enable google meet
        add_option('appointly_google_meet_recording', 0); // Google meet recording
        add_option('appointly_google_meet_waiting_room', 0); // Google meet waiting room
        add_option('appointly_google_meet_reminder_minutes', 30); // Google meet reminder minutes
        add_option('appointly_disable_google_meeting_emails', 0); // Disable google meeting emails
        add_option('appointly_show_staff_email', 0); // Show staff email in booking form
        add_option('appointly_staff_respect_availability', 0); // Staff appointments respect availability restrictions
        
        add_option(
            'external_form_description',
            'Complete the form below to arrange your session with our team'
        ); // External form description

        add_option(
            'appointly_default_feedbacks',
            '["0","1","2","3","4","5","6"]'
        ); // Default feedbacks

        // Create the company schedule table if it doesn't exist
        if (!$CI->db->table_exists(db_prefix() . "appointly_company_schedule")) {
            $CI->db->query("CREATE TABLE IF NOT EXISTS `" . db_prefix() . "appointly_company_schedule` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `weekday` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
                `start_time` TIME NOT NULL DEFAULT '09:00:00',
                `end_time` TIME NOT NULL DEFAULT '17:00:00',
                `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");

            // Populate with default data
            $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $companySchedule = [];

            foreach ($weekdays as $index => $day) {
                $isWeekend = ($index > 4); // Saturday and Sunday
                $companySchedule[] = [
                    'weekday' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'is_enabled' => $isWeekend ? 0 : 1
                ];
            }

            if (!empty($companySchedule)) {
                $CI->db->insert_batch(db_prefix() . 'appointly_company_schedule', $companySchedule);
            }
        }

        // Create the staff working hours table if it doesn't exist
        if (!$CI->db->table_exists(db_prefix() . "appointly_staff_working_hours")) {
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
        }

        // Updated appointments table: added provider_id column after created_by.
        if (! $CI->db->table_exists(db_prefix() . "appointly_appointments")) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS " . db_prefix() . "appointly_appointments (
                `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `service_id` int(11) DEFAULT NULL,
                `provider_id` int(11) DEFAULT NULL,
                `created_by` int(11) DEFAULT NULL,     
                `source` varchar(191) DEFAULT NULL,
                `google_event_id` varchar(191) DEFAULT NULL,
                `google_calendar_link` varchar(191) DEFAULT NULL,
                `google_meet_link` varchar(191) DEFAULT NULL,
                `google_added_by_id` int(11) DEFAULT NULL,
                `outlook_event_id` VARCHAR(191) DEFAULT NULL,
                `outlook_calendar_link` VARCHAR(255) DEFAULT NULL,
                `outlook_added_by_id` INT(11) DEFAULT NULL,
                `subject` varchar(191) NOT NULL,
                `description` text,
                `email` varchar(191) DEFAULT NULL,
                `name` varchar(191) DEFAULT NULL,
                `phone` varchar(191) DEFAULT NULL,
                `address` varchar(191) DEFAULT NULL,
                `notes` longtext DEFAULT NULL,
                `contact_id` int(11) DEFAULT NULL,
                `hash` varchar(191) DEFAULT NULL,
                `status` ENUM('pending', 'cancelled', 'completed', 'no-show', 'in-progress') NOT NULL DEFAULT 'in-progress',
                `notification_date` datetime DEFAULT NULL,
                `external_notification_date` datetime DEFAULT NULL,
                `date` date NOT NULL,
                `start_hour` varchar(191) NOT NULL,
                `duration` varchar(100) DEFAULT NULL,
                `end_hour` varchar(191) NOT NULL,
                `approved` tinyint(1) NOT NULL DEFAULT '0',                        
                `cancel_notes` text,
                `feedback` SMALLINT NULL DEFAULT NULL,
                `feedback_comment` TEXT NULL DEFAULT NULL,
                `reminder_before` int(11) DEFAULT NULL,
                `reminder_before_type` varchar(10) DEFAULT NULL,
                `by_sms` tinyint(1) DEFAULT NULL,
                `by_email` tinyint(1) DEFAULT NULL,
                `recurring` int NOT NULL DEFAULT '0',
                `recurring_type` varchar(10) DEFAULT NULL,
                `repeat_every` INT NULL DEFAULT NULL,
                `custom_recurring` tinyint NULL DEFAULT '0',
                `cycles` int NOT NULL DEFAULT '0',
                `total_cycles` int NOT NULL DEFAULT '0',
                `last_recurring_date` date DEFAULT NULL,
                `date_created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                `files` text DEFAULT NULL,
                `timezone` varchar(100) DEFAULT NULL,
                `invoice_id` int(11) NULL DEFAULT NULL,
                `invoice_date` datetime NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }

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
                INDEX `idx_status` (`status`),
                FOREIGN KEY (`appointment_id`) REFERENCES `" . db_prefix() . "appointly_appointments`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }

        if (! $CI->db->table_exists(db_prefix() . 'appointly_service_staff')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_service_staff` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `service_id` int(11) NOT NULL,
                `staff_id` int(11) NOT NULL,
                `is_provider` tinyint(1) DEFAULT 1,
                `is_primary` tinyint(1) DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `service_id` (`service_id`),
                KEY `staff_id` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }

        if (! $CI->db->table_exists(db_prefix()
            . 'appointly_appointment_services')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_appointment_services` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `appointment_id` int unsigned NOT NULL,
                `service_id` int NOT NULL,
                PRIMARY KEY (`id`),
                KEY `appointment_id` (`appointment_id`),
                KEY `service_id` (`service_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");

            // Migrate existing appointments data
            $CI->db->query("INSERT INTO `" . db_prefix() . "appointly_appointment_services`
                (appointment_id, service_id)
                SELECT id, service_id
                FROM `" . db_prefix() . "appointly_appointments`
                WHERE service_id IS NOT NULL");
        }

        if (! $CI->db->table_exists(db_prefix() . "appointly_attendees")) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS " . db_prefix() . "appointly_attendees (
                `staff_id` int(11) NOT NULL,
                `appointment_id` int(11) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }

        if (! $CI->db->table_exists(db_prefix() . "appointly_google")) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS " . db_prefix() . "appointly_google (
               `id` int(11) NOT NULL AUTO_INCREMENT,
               `staff_id` int(11) NOT NULL,
               `access_token` varchar(191) NOT NULL,
               `refresh_token` varchar(191) NOT NULL,
               `expires_in` varchar(191) NOT NULL,
               PRIMARY KEY (`id`)
               ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";"
            );
        }
        // Create services table if it doesn't exist
        if (! $CI->db->table_exists(db_prefix() . 'appointly_services')) {
            $CI->db->query("CREATE TABLE `" . db_prefix() . "appointly_services` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(191) NOT NULL,
            `description` text DEFAULT NULL,
            `duration` int(11) DEFAULT 60,
            `buffer_before` int(11) DEFAULT 0,
            `buffer_after` int(11) DEFAULT 0,
            `price` decimal(15,2) DEFAULT 0.00,
            `color` varchar(10) DEFAULT '#28B8DA',
            `active` tinyint(1) DEFAULT 1,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";");
        }

        // Check if services table exists before counting (safety check)
        $services_count = 0;
        if ($CI->db->table_exists(db_prefix() . 'appointly_services')) {
            $services_count = $CI->db->count_all(db_prefix() . 'appointly_services');
        }

        if ($services_count == 0) {

            $default_services = [
                [
                    'name' => 'Initial Consultation',
                    'description' => 'First meeting to discuss your needs and requirements',
                    'duration' => 30,
                    'price' => 0,
                    'color' => '#3B82F6', // Tailwind blue-500
                    'active' => 1,
                ],
                [
                    'name' => 'Business Strategy Session',
                    'description' => 'In-depth discussion about business strategy and planning',
                    'duration' => 60,
                    'price' => 150,
                    'color' => '#10B981', // Tailwind emerald-500
                    'active' => 1,
                ],
                [
                    'name' => 'Project Review Meeting',
                    'description' => 'Regular project status review and updates',
                    'duration' => 45,
                    'price' => 100,
                    'color' => '#8B5CF6', // Tailwind violet-500
                    'active' => 1,
                ]
            ];

            // Insert services and assign to default admin
            foreach ($default_services as $service) {
                $CI->db->insert(db_prefix() . 'appointly_services', $service);
                $service_id = $CI->db->insert_id();

                // Assign to default admin (staff_id = 1)
                $CI->db->insert(db_prefix() . 'appointly_service_staff', [
                    'service_id' => $service_id,
                    'staff_id' => 1,
                    'is_provider' => 1,
                    'is_primary' => 1
                ]);
            }
            log_message('info', 'Appointly Module: Assigned default services to admin');
        }

        // Check if there are any service staff assignments
        $staff_assignments_count = 0;
        if ($CI->db->table_exists(db_prefix() . 'appointly_service_staff')) {
            $staff_assignments_count = $CI->db->count_all(db_prefix() . 'appointly_service_staff');
        }

        if ($staff_assignments_count == 0) {
            // Get all services - only if table exists
            if ($CI->db->table_exists(db_prefix() . 'appointly_services')) {
                $services = $CI->db->get(db_prefix() . 'appointly_services')->result_array();

                // Assign each service to admin
                foreach ($services as $service) {
                    $CI->db->insert(db_prefix() . 'appointly_service_staff', [
                        'service_id' => $service['id'],
                        'staff_id' => 1,
                        'is_provider' => 1,
                        'is_primary' => 1
                    ]);
                }

                log_message('info', 'Appointly Module: Assigned default services to admin');
            }
        }

        // Check for missing columns after all tables are created
        checkForMissingColumns($CI);

        checkForModuleReinstallation();
    }

    /**
     * @param $CI
     *
     * @return void
     */
    function checkForMissingColumns($CI): void
    {
        // Check if end_hour exists, if not add it
        if (!$CI->db->field_exists('end_hour', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_appointments` ADD `end_hour` varchar(191) NOT NULL DEFAULT '' AFTER `start_hour`;");
        }

        // Check if duration exists, if not add it
        if (!$CI->db->field_exists('duration', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_appointments` ADD `duration` varchar(100) DEFAULT NULL AFTER `start_hour`;");
        }

        if (! $CI->db->field_exists(
            'service_id',
            db_prefix() . 'appointly_appointments'
        )) {
            $CI->db->query("ALTER TABLE " . db_prefix()
                . "appointly_appointments ADD COLUMN `service_id` int(11) DEFAULT NULL AFTER `id`");
        }

        if (! $CI->db->field_exists(
            'date_created',
            db_prefix() . 'appointly_appointments'
        )) {
            $CI->db->query("ALTER TABLE " . db_prefix()
                . "appointly_appointments ADD COLUMN `date_created` datetime DEFAULT CURRENT_TIMESTAMP AFTER `id`");
        }

        // Check for invoice integration columns
        if (! $CI->db->field_exists(
            'invoice_id',
            db_prefix() . 'appointly_appointments'
        )) {
            $CI->db->query("ALTER TABLE " . db_prefix()
                . "appointly_appointments ADD COLUMN `invoice_id` int(11) NULL DEFAULT NULL AFTER `feedback_comment`");
        }

        if (! $CI->db->field_exists(
            'invoice_date',
            db_prefix() . 'appointly_appointments'
        )) {
            $CI->db->query("ALTER TABLE " . db_prefix()
                . "appointly_appointments ADD COLUMN `invoice_date` datetime NULL DEFAULT NULL AFTER `invoice_id`");
        }

        // Check for provider_id in appointments table
        if (! $CI->db->field_exists('provider_id', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_appointments ADD COLUMN `provider_id` int(11) DEFAULT NULL AFTER `created_by`;");
        }

        // Check for is_primary in service_staff table - only if table exists
        if (
            $CI->db->table_exists(db_prefix() . 'appointly_service_staff') &&
            !$CI->db->field_exists('is_primary', db_prefix() . 'appointly_service_staff')
        ) {
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

        // Check for status field in appointments table
        if (!$CI->db->field_exists('status', db_prefix() . 'appointly_appointments')) {
            $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_appointments` 
                ADD COLUMN `status` ENUM('pending', 'cancelled', 'completed', 'no-show', 'in-progress') NOT NULL DEFAULT 'in-progress' AFTER `hash`;");

            // Migrate existing statuses to the new field
            $CI->db->query("UPDATE " . db_prefix() . "appointly_appointments 
                SET status = CASE 
                    WHEN finished = 1 THEN 'completed'
                    WHEN cancelled = 1 THEN 'cancelled'
                    WHEN approved = 1 THEN 'in-progress'
                    ELSE 'pending' 
                END");
        }

        // Check for buffer times in services table - CRITICAL for time slot generation
        if ($CI->db->table_exists(db_prefix() . 'appointly_services')) {
            if (!$CI->db->field_exists('buffer_before', db_prefix() . 'appointly_services')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_services` 
                    ADD COLUMN `buffer_before` int(11) DEFAULT 0 AFTER `duration`;");
            }

            if (!$CI->db->field_exists('buffer_after', db_prefix() . 'appointly_services')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_services` 
                    ADD COLUMN `buffer_after` int(11) DEFAULT 0 AFTER `buffer_before`;");
            }
        }
    }
}


if (! function_exists('init_appointly_template_tables')) {
    /**
     * Insert email templates into database
     */
    function init_appointly_template_tables()
    {
        create_email_template(
            'You have an upcoming appointment!',
            '<span style=\"font-size: 12pt;\"> Hello {staff_firstname} {staff_lastname} </span><br /><br /><span style=\"font-size: 12pt;\"> You have an upcoming appointment that is need to be held date {appointment_date} and location {appointment_location}</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Additional info for your appointment:</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span>',
            'appointly',
            'Appointment reminder (Sent to Staff and Attendees)',
            'appointment-cron-reminder-to-staff'
        );

        create_email_template(
            'Your appointment has been updated!',
            '<span style=\"font-size: 12pt;\"> Hello {appointment_client_name}.</span><br /><br /><span style=\"font-size: 12pt;\"> Your appointment has been updated.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Updated Appointment Details:</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span>',
            'appointly',
            'Appointment updated (Sent to Contact)',
            'appointment-updated-to-contact'
        );

        create_email_template(
            'Appointment has been updated!',
            '<span style=\"font-size: 12pt;\"> Hello {staff_firstname} {staff_lastname}.</span><br /><br /><span style=\"font-size: 12pt;\"> An appointment that you are attending has been updated.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Updated Appointment Details:</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span>',
            'appointly',
            'Appointment updated (Sent to Staff)',
            'appointment-updated-to-staff'
        );

        create_email_template(
            'Recurring appointment was re-created!',
            '<span style=\"font-size: 12pt;\"> Hello {staff_firstname} {staff_lastname} </span><br /><br /><span style=\"font-size: 12pt;\"> Your recurring appointment was recreated with date {appointment_date} and location {appointment_location}</span><br /><br /><span style=\"font-size: 12pt;\"><strong> Additional info for your appointment:</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span>',
            'appointly',
            'Appointment recurring (Sent to Staff and Attendees)',
            'appointment-recurring-to-staff'
        );

        create_email_template(
            'Appointment has been cancelled!',
            '<span style=\"font-size: 12pt;\"> Hello {staff_firstname} {staff_lastname}. </span><br /><br /><span style=\"font-size: 12pt;\"> The appointment that needed to be held on date {appointment_date} and location {appointment_location} with contact {appointment_client_name} is cancelled.</span><br /><br /><span style=\"font-size: 12pt;\"><br />Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>',
            'appointly',
            'Appointment cancelled (Sent to Staff and Attendees)',
            'appointment-cancelled-to-staff'
        );

        create_email_template(
            'Your appointment has been cancelled!',
            '<span style=\"font-size: 12pt;\"> Hello {appointment_client_name}. </span><br /><br /><span style=\"font-size: 12pt;\"> The appointment that needed to be held on date {appointment_date} and location {appointment_location} is now cancelled.</span><br /><br /><span style=\"font-size:12pt;\"><br />Kind Regards</span><br /><br /><span style=\"font-size: 12pt;\">{email_signature}</span>',
            'appointly',
            'Appointment cancelled (Sent to Contact)',
            'appointment-cancelled-to-contact'
        );

        create_email_template(
            'You have an upcoming appointment!',
            '<span style=\"font-size: 12pt;\"> Hello {appointment_client_name}. </span><br /><br /><span style=\"font-size: 12pt;\"> You have an upcoming appointment that is need to be held date {appointment_date} {appointment_location}.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Additional info for your appointment</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span>',
            'appointly',
            'Appointment reminder (Sent to Contact)',
            'appointment-cron-reminder-to-contact'
        );

        create_email_template(
            'Recurring appointment was re-created!',
            '<span style=\"font-size: 12pt;\"> Hello {appointment_client_name}. </span><br /><br /><span style=\"font-size: 12pt;\"> Your recurring appointment was recreated with date {appointment_date} {appointment_location}.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Additional info for your appointment</strong></span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span>',
            'appointly',
            'Appointment recurring (Sent to Contact)',
            'appointment-recurring-to-contacts'
        );

        create_email_template(
            'You are added as a appointment attendee!',
            '<span style=\"font-size: 12pt;\"> Hello {staff_firstname} {staff_lastname}.</span><br /><br /><span style=\"font-size: 12pt;\"> You are added as a appointment attendee.</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span>',
            'appointly',
            'Appointment approved (Sent to Staff and Attendees)',
            'appointment-approved-to-staff'
        );

        create_email_template(
            'Your appointment has been approved!',
            '<span style=\"font-size: 12pt;\"> Hello {appointment_client_name}.</span><br /><br /><span style=\"font-size: 12pt;\"> You appointment has been approved!</span><br /><br /><span style=\"font-size: 12pt;\"><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><span style=\"font-size: 12pt;\"><strong>Appointment Description:</strong> {appointment_description}</span>',
            'appointly',
            'Appointment approved (Sent to Contact)',
            'appointment-approved-to-contact'
        );

        create_email_template(
            'New appointment request via external form!',
            '<span 12pt=""><span 12pt="">Hello {staff_firstname} {staff_lastname}<br /><br />New appointment request submitted via external form</span>.<br /><br /><span 12pt=""><strong>Appointment Subject:</strong> {appointment_subject}</span><br /><br /><span 12pt=""><strong>Appointment Description:</strong> {appointment_description}</span>',
            'appointly',
            'New appointment request (Sent to Staff)',
            'appointment-submitted-to-staff'
        );

        create_email_template(
            'Thank you for your appointment request!',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">Thank you for submitting your appointment request! We have received your request and our team will review it shortly.</span><br /><br /><span style="font-size: 12pt;"><strong>Your Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Requested Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Description:</strong> {appointment_description}</span>',
            'appointly',
            'Appointment request confirmation (Sent to Contact)',
            'appointment-submitted-to-contact'
        );

        create_email_template(
            'Thank you for your appointment request!',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">Thank you for submitting your appointment request! We have received your request and our team will review it shortly.</span><br /><br /><span style="font-size: 12pt;"><strong>Your Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Requested Date & Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Description:</strong> {appointment_description}</span>',
            'appointly',
            'Appointment request confirmation (Sent to Contact)',
            'appointment-submitted-to-contact'
        );

        create_email_template(
            'Feedback request for appointment!',
            '<span 12pt=""><span 12pt="">Hello {appointment_client_name} <br /><br />A new feedback request has just been submitted, please leave your comments and thoughts about this past appointment, fast navigate to the appointment to add a feedback:</strong> <a href="{appointment_public_url}">{appointment_public_url}</a></span><br /><br /><br />{companyname}<br />{crm_url}<br /><span 12pt=""></span></span>',
            'appointly',
            'Request Appointment Feedback (Sent to Client)',
            'appointly-appointment-request-feedback'
        );

        create_email_template(
            'New appointment feedback rating received!',
            '<span 12pt=""><span 12pt="">Hello {staff_firstname} {staff_lastname} <br /><br />A new feedback rating has been received from client {appointment_client_name}. View the new feedback rating submitted at the following link:</strong> <a href="{appointment_admin_url}">{appointment_admin_url}</a></span><br /><br /><br />{companyname}<br />{crm_url}<br /><span 12pt=""></span></span>',
            'appointly',
            'New Feedback Received (Sent to Staff)',
            'appointly-appointment-feedback-received'
        );

        create_email_template(
            'Appointment feedback rating updated!',
            '<span 12pt=""><span 12pt="">Hello {staff_firstname} {staff_lastname} <br /><br />An existing feedback was just updated from client {appointment_client_name}. View the new rating submitted at the following link:</strong> <a href="{appointment_admin_url}">{appointment_admin_url}</a></span><br /><br /><br />{companyname}<br />{crm_url}<br /><span 12pt=""></span></span>',
            'appointly',
            'Feedback Updated (Sent to Staff)',
            'appointly-appointment-feedback-updated'
        );

        // Copy English templates to all other languages
        appointly_copy_templates_to_all_languages();
    }
}

if (! function_exists('appointly_copy_templates_to_all_languages')) {
    /**
     * Copy English appointment templates to all other languages
     */
    function appointly_copy_templates_to_all_languages()
    {
        $CI = &get_instance();
        $CI->load->model('emails_model');

        // Get all languages
        $languages = $CI->db->query("SELECT DISTINCT language FROM " . db_prefix() . "emailtemplates WHERE language != 'english' ORDER BY language")->result_array();
        $languages = array_column($languages, 'language');

        // Get all English appointly templates
        $english_templates = $CI->emails_model->get(['type' => 'appointly', 'language' => 'english']);

        foreach ($english_templates as $template) {
            foreach ($languages as $language) {
                // Check if template exists for this language
                $existing = $CI->emails_model->get(['slug' => $template['slug'], 'language' => $language, 'type' => 'appointly'], 'row');

                if (!$existing) {
                    // Create template for this language
                    $CI->emails_model->add_template([
                        'subject' => $template['subject'],
                        'message' => $template['message'],
                        'type' => $template['type'],
                        'name' => $template['name'],
                        'slug' => $template['slug'],
                        'language' => $language,
                        'active' => $template['active'],
                        'plaintext' => $template['plaintext'],
                        'fromname' => $template['fromname'],
                        'fromemail' => $template['fromemail']
                    ]);
                }
            }
        }
    }
}


if (! function_exists('init_appointly_install_sequence')) {
    /**
     * Initialize tables content example data for email templates and sms in database
     */
    function init_appointly_install_sequence()
    {
        init_appointly_database_tables();
        init_appointly_template_tables();

        // Clear browser cache to ensure new routes are recognized immediately
        appointly_clear_browser_cache();

        log_message('info', 'Appointly: Installation sequence completed with browser cache clearing');
    }
}

if (! function_exists('appointly_clear_browser_cache')) {
    /**
     * Clear browser cache by setting cache-busting parameters and forcing reload
     * Note: Headers cannot be sent from helper functions as output may already be started
     */
    function appointly_clear_browser_cache()
    {
        // Add cache-busting parameter to current URL
        $cache_buster = time();

        // Use JavaScript to reload with cache-busting parameter
        echo '<script>
        (function() {
            var currentUrl = window.location.href;
            var separator = currentUrl.indexOf("?") !== -1 ? "&" : "?";
            var newUrl = currentUrl + separator + "cache_bust=' . $cache_buster . '";
            
            setTimeout(function(){
                window.location.href = newUrl;
            }, 1000);
        })();
        </script>';

        log_message('info', 'Appointly: Browser cache clearing initiated with JavaScript reload');
    }
}

if (! function_exists('checkForModuleReinstallation')) {
    /**
     * Percussion database checks
     */
    function checkForModuleReinstallation()
    {
        $CI = &get_instance();

        $table_name = db_prefix() . 'appointly_appointments';

        if ($CI->db->table_exists($table_name)) {
            $CI->db->query("DROP TABLE IF EXISTS " . db_prefix()
                . "appointly_appointment_types");

            if (! $CI->db->field_exists('notes', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `notes` LONGTEXT NULL AFTER `address`;"
                );
            }

            if (! $CI->db->field_exists('google_event_id', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `google_event_id` VARCHAR(191) NULL DEFAULT NULL AFTER `id`;"
                );
            }

            if (! $CI->db->field_exists('google_calendar_link', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `google_calendar_link` VARCHAR(191) NULL DEFAULT NULL AFTER `google_event_id`;"
                );
            }

            if (! $CI->db->field_exists('google_added_by_id', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `google_added_by_id` int(11) NULL DEFAULT NULL AFTER `google_calendar_link`;"
                );
            }
            if (! $CI->db->field_exists('google_meet_link', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `google_meet_link` VARCHAR(191) NULL DEFAULT NULL AFTER `google_calendar_link`;"
                );
            }
            // New check for provider_id
            if (! $CI->db->field_exists('provider_id', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `provider_id` INT(11) NULL AFTER `created_by`;"
                );
            }

            // Check for timezone field
            if (! $CI->db->field_exists('timezone', $table_name)) {
                $CI->db->query(
                    "ALTER TABLE " . db_prefix()
                        . "appointly_appointments ADD `timezone` VARCHAR(100) DEFAULT NULL AFTER `date`;"
                );
            }
        }

        // If type_id exists in appointments table, remove it - only if table exists
        if ($CI->db->table_exists($table_name) && $CI->db->field_exists(
            'type_id',
            db_prefix() . 'appointly_appointments'
        )) {
            $CI->db->query("ALTER TABLE " . db_prefix()
                . "appointly_appointments DROP COLUMN `type_id`");
        }

        // Check for working_hours field in service_staff table - only if table exists
        if (
            $CI->db->table_exists(db_prefix() . 'appointly_service_staff') &&
            !$CI->db->field_exists('working_hours', db_prefix() . 'appointly_service_staff')
        ) {
            $CI->db->query("ALTER TABLE " . db_prefix() . "appointly_service_staff 
                ADD COLUMN `working_hours` text DEFAULT NULL AFTER `is_provider`");
        }
    }
}

if (!function_exists('appointly_get_available_languages')) {
    /**
     * Get all available languages in the system
     * 
     * @return array List of language codes
     */
    function appointly_get_available_languages()
    {
        $CI = &get_instance();

        // Get languages from database
        $query = $CI->db->query("SELECT DISTINCT language FROM " . db_prefix() . "emailtemplates ORDER BY language");
        $db_languages = array_column($query->result_array(), 'language');

        // Get languages from filesystem
        $file_languages = [];
        if (is_dir(APPPATH . 'language')) {
            $dirs = scandir(APPPATH . 'language');
            foreach ($dirs as $dir) {
                if ($dir !== '.' && $dir !== '..' && is_dir(APPPATH . 'language/' . $dir)) {
                    $file_languages[] = $dir;
                }
            }
        }

        // Merge and deduplicate
        $all_languages = array_unique(array_merge($db_languages, $file_languages));

        // Ensure English is always first
        $all_languages = array_diff($all_languages, ['english']);
        array_unshift($all_languages, 'english');

        return $all_languages;
    }
}

if (!function_exists('appointly_create_missing_templates')) {
    /**
     * Create missing templates based on English
     * 
     * @return array Results of the creation process
     */
    function appointly_create_missing_templates()
    {
        $CI = &get_instance();
        $CI->load->model('emails_model');

        $created_count = 0;

        // Get all English Appointly templates
        $english_templates = $CI->emails_model->get([
            'type' => 'appointly',
            'language' => 'english'
        ]);

        // Get all available languages
        $available_languages = appointly_get_available_languages();

        foreach ($english_templates as $english_template) {
            foreach ($available_languages as $language) {
                if ($language === 'english') continue;

                // Check if template exists for this language
                $existing = $CI->emails_model->get([
                    'slug' => $english_template['slug'],
                    'language' => $language,
                    'type' => 'appointly'
                ], 'row');

                // If doesn't exist, create it
                if (!$existing) {
                    $template_data = [
                        'subject'   => $english_template['subject'],
                        'message'   => $english_template['message'],
                        'type'      => $english_template['type'],
                        'name'      => $english_template['name'],
                        'slug'      => $english_template['slug'],
                        'language'  => $language,
                        'active'    => $english_template['active'],
                        'plaintext' => $english_template['plaintext'],
                        'fromname'  => $english_template['fromname'],
                        'fromemail' => $english_template['fromemail']
                    ];

                    if ($CI->emails_model->add_template($template_data)) {
                        $created_count++;
                    }
                }
            }
        }

        log_message('info', "Appointly: Created {$created_count} missing templates");
        return ['created' => $created_count];
    }
}

if (!function_exists('appointly_fix_empty_template_messages')) {
    /**
     * Fix empty template messages by copying from English version
     * 
     * @param string|null $slug Specific template slug to fix (null = fix all)
     * @return array Results of the fix operation
     */
    function appointly_fix_empty_template_messages($slug = null)
    {
        $CI = &get_instance();
        $CI->load->model('emails_model');

        $results = [];

        // Build WHERE clause
        $where_sql = "WHERE type = 'appointly' AND language != 'english' AND (TRIM(message) = '' OR message IS NULL)";
        if ($slug) {
            $where_sql .= " AND slug = " . $CI->db->escape($slug);
        }

        // Get templates with empty messages
        $empty_templates = $CI->db->query("
            SELECT * FROM " . db_prefix() . "emailtemplates 
            {$where_sql}
        ")->result();

        log_message('info', "Appointly: Found " . count($empty_templates) . " templates with empty messages");

        foreach ($empty_templates as $template) {
            try {
                // Get English version as source
                $english_template = $CI->emails_model->get([
                    'slug' => $template->slug,
                    'language' => 'english'
                ], 'row');

                if (!$english_template || trim($english_template->message) === '') {
                    $results[$template->language . '_' . $template->slug] = [
                        'status' => 'error',
                        'message' => 'No English template found or English template is also empty'
                    ];
                    continue;
                }

                // Update the empty template with English content
                $CI->db->where('emailtemplateid', $template->emailtemplateid);
                $CI->db->update(db_prefix() . 'emailtemplates', [
                    'message' => $english_template->message,
                    'subject' => $english_template->subject ?: $template->subject
                ]);

                $results[$template->language . '_' . $template->slug] = [
                    'status' => 'fixed',
                    'message' => 'Template message copied from English version',
                    'id' => $template->emailtemplateid
                ];
            } catch (Exception $e) {
                $results[$template->language . '_' . $template->slug] = [
                    'status' => 'error',
                    'message' => 'Exception: ' . $e->getMessage()
                ];
                log_message('error', "Appointly: Error fixing template {$template->slug} for {$template->language}: " . $e->getMessage());
            }
        }

        $fixed_count = count(array_filter($results, function ($r) {
            return $r['status'] === 'fixed';
        }));

        log_message('info', "Appointly: Fixed {$fixed_count} empty template messages");

        return $results;
    }
}

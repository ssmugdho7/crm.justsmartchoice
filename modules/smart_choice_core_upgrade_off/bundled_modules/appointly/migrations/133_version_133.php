<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_133 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        // fallback for services table some users might not have it
        if ($CI->db->table_exists(db_prefix() . 'appointly_services')) {
            // Add buffer_before column if it doesn't exist
            if (!$CI->db->field_exists('buffer_before', db_prefix() . 'appointly_services')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_services` 
                    ADD COLUMN `buffer_before` int(11) DEFAULT 0 AFTER `duration`;");
            }

            // Add buffer_after column if it doesn't exist
            if (!$CI->db->field_exists('buffer_after', db_prefix() . 'appointly_services')) {
                $CI->db->query("ALTER TABLE `" . db_prefix() . "appointly_services` 
                    ADD COLUMN `buffer_after` int(11) DEFAULT 0 AFTER `buffer_before`;");
            }
        }

        // Fix missing columns in appointments table that cause database errors
        $appointments_table = db_prefix() . 'appointly_appointments';
        if ($CI->db->table_exists($appointments_table)) {

            // Add timezone column
            if (!$CI->db->field_exists('timezone', $appointments_table)) {
                $CI->db->query("ALTER TABLE `{$appointments_table}`
                    ADD COLUMN `timezone` varchar(100) DEFAULT 'UTC' AFTER `google_event_id`;");
            }

            // Add other missing columns that cause errors
            if (!$CI->db->field_exists('created_from', $appointments_table)) {
                $CI->db->query("ALTER TABLE `{$appointments_table}`
                    ADD COLUMN `created_from` varchar(50) DEFAULT 'internal' AFTER `timezone`;");
            }

            if (!$CI->db->field_exists('reminder_sent_staff', $appointments_table)) {
                $CI->db->query("ALTER TABLE `{$appointments_table}`
                    ADD COLUMN `reminder_sent_staff` tinyint(1) DEFAULT 0 AFTER `created_from`;");
            }

            if (!$CI->db->field_exists('reminder_sent_contact', $appointments_table)) {
                $CI->db->query("ALTER TABLE `{$appointments_table}`
                    ADD COLUMN `reminder_sent_contact` tinyint(1) DEFAULT 0 AFTER `reminder_sent_staff`;");
            }

            if (!$CI->db->field_exists('outlook_event_id', $appointments_table)) {
                $CI->db->query("ALTER TABLE `{$appointments_table}`
                    ADD COLUMN `outlook_event_id` varchar(255) DEFAULT NULL AFTER `google_calendar_link`;");
            }

            if (!$CI->db->field_exists('outlook_calendar_link', $appointments_table)) {
                $CI->db->query("ALTER TABLE `{$appointments_table}`
                    ADD COLUMN `outlook_calendar_link` text DEFAULT NULL AFTER `outlook_event_id`;");
            }
        }

        // Add client confirmation email template for external appointment submissions
        create_email_template(
            'Thank you for your appointment request!',
            '<span style="font-size: 12pt;">Hello {appointment_client_name},</span><br /><br /><span style="font-size: 12pt;">Thank you for submitting your appointment request. We have received your request and our team will review it shortly.</span><br /><br /><span style="font-size: 12pt;"><strong>Your Appointment Details:</strong></span><br /><span style="font-size: 12pt;"><strong>Subject:</strong> {appointment_subject}</span><br /><span style="font-size: 12pt;"><strong>Requested Date And Time:</strong> {appointment_date}</span><br /><span style="font-size: 12pt;"><strong>Description:</strong> {appointment_description}</span><br /><br /><span style="font-size: 12pt;">We will contact you if more information is needed.</span>',
            'appointly',
            'Appointment request confirmation (Sent to Contact)',
            'appointment-submitted-to-contact'
        );

        // Add new options to the database
        add_option('appointly_upcoming_widget_enabled', '1');
        add_option('appointly_upcoming_widget_range', '7_days');
        add_option('appointly_today_widget_enabled', '1');
        add_option('appointly_invoice_default_vat', '13');

        // Fix missing templates and empty template messages on update
        $CI->load->helper('appointly' . '/appointly_database');
        appointly_create_missing_templates();
        appointly_fix_empty_template_messages();
        appointly_clear_browser_cache();
    }
}

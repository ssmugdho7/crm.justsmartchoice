<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_134 extends App_module_migration
{
    public function up()
    {
        // Tax configuration
        add_option('appointly_invoice_tax_type', 'none'); // none, custom, system
        add_option('appointly_invoice_system_tax', ''); // Selected tax ID for system taxes
        add_option('appointly_invoice_default_vat', '0'); // Custom tax percentage (backward compatibility)

        // Add new invoice toggle setting
        add_option('appointly_show_invoice_option', 0);

        // Google Meet settings
        add_option('appointly_auto_enable_google_meet', 0); // Enable Google Meet for all appointments
        add_option('appointly_google_meet_recording', 0); // Enable Google Meet recording
        add_option('appointly_google_meet_waiting_room', 0); // Enable Google Meet waiting room
        add_option('appointly_google_meet_reminder_minutes', 30); // Google Meet reminder minutes
        add_option('appointly_disable_google_meeting_emails', 0); // Disable Google Meet emails

        // Show staff email in booking form
        add_option('appointly_show_staff_email', 0);

        add_option('appointly_staff_respect_availability', 0); // 0 = disabled (current behavior), 1 = enabled (use same logic as other appointment types)

    }
}

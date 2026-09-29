<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Version_219 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        update_option('appointly_smart_choice_version', '2.1.9');
        foreach ([
            'appointly_sc_create_customer_on_booking' => '0',
            'appointly_sc_create_estimate_on_booking' => '0',
            'appointly_sc_create_proposal_on_booking' => '0',
        ] as $name => $value) {
            if (get_option($name) === false) { add_option($name, $value); }
        }
        update_option('appointly_sc_public_logo_width', '88');
        update_option('appointly_sc_public_logo_height', '30');

        $table = db_prefix().'appointly_smartchoice_notifications';
        if ($CI->db->table_exists($table)) {
            if (!$CI->db->field_exists('customer_id', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD `customer_id` INT(11) NULL DEFAULT NULL AFTER `staff_id`, ADD KEY `customer_id` (`customer_id`)");
            }
            if (!$CI->db->field_exists('customer_email', $table)) {
                $CI->db->query("ALTER TABLE `{$table}` ADD `customer_email` VARCHAR(191) NULL DEFAULT NULL AFTER `customer_id`");
            }
        }
        $templates = db_prefix().'emailtemplates';
        if ($CI->db->table_exists($templates) && !$CI->db->where('slug','appointly-dispatch-customer-status')->get($templates)->row()) {
            create_email_template(
                'Smart Choice appointment status update',
                '<p>Hello,</p><p>{appointment_dispatch_message}</p><p><strong>Appointment:</strong> {appointment_subject}<br><strong>Status:</strong> {appointment_status}<br><strong>Representative:</strong> {appointment_staff_name}</p><p><img src="{appointment_staff_photo}" alt="Assigned representative" style="max-width:72px;border-radius:8px;"></p><p><a href="{appointment_map_link}">Open directions</a></p><p>{email_signature}</p>',
                'appointly',
                'Appointment Dispatch Status (Sent to Contact)',
                'appointly-dispatch-customer-status'
            );
        }
    }
}

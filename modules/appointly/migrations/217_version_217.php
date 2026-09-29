<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_217 extends App_module_migration
{
    public function up()
    {
        if (get_option('appointly_smart_choice_version') === false) {
            add_option('appointly_smart_choice_version', '2.1.7');
        } else {
            update_option('appointly_smart_choice_version', '2.1.7');
        }

        $CI = &get_instance();
        $templates = db_prefix() . 'emailtemplates';
        if (!$CI->db->table_exists($templates)) {
            return;
        }

        $slug = 'appointly-dispatch-test-notification';
        $exists = $CI->db->where('slug', $slug)->get($templates)->row();
        if (!$exists) {
            create_email_template(
                'Smart Choice Appointments test notification',
                '<p>Hi {staff_firstname} {staff_lastname},</p><p>{appointly_test_message}</p><p>This message was sent from the Appointly Dispatcher Board notification test.</p><p><a href="{appointly_dispatch_url}">Open Dispatcher Board</a></p><p>{email_signature}</p>',
                'appointly',
                'Dispatcher Test Notification (Sent to Staff)',
                $slug
            );
        }
    }
}

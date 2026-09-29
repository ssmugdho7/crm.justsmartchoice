<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_117 extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();
        $attendees = db_prefix() . 'google_meet_attendees';

        // Upgrade-only schema repair. Existing meetings, attendees, links,
        // settings, API credentials, templates, and notifications are preserved.
        if (!$CI->db->table_exists($attendees)) {
            $CI->db->query('CREATE TABLE `' . $attendees . '` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `meeting_id` int(11) NOT NULL,
                `attendee_type` varchar(50) NOT NULL DEFAULT "staff",
                `staff_id` int(11) NULL,
                `contact_id` int(11) NULL,
                `email` varchar(191) NULL,
                `name` varchar(191) NULL,
                `notified` tinyint(1) NOT NULL DEFAULT 0,
                `created_at` datetime NULL,
                PRIMARY KEY (`id`),
                KEY `meeting_id` (`meeting_id`),
                KEY `staff_id` (`staff_id`),
                KEY `contact_id` (`contact_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        } else {
            $columns = [
                'meeting_id'    => "ALTER TABLE `$attendees` ADD COLUMN `meeting_id` int(11) NOT NULL DEFAULT 0 AFTER `id`",
                'attendee_type' => "ALTER TABLE `$attendees` ADD COLUMN `attendee_type` varchar(50) NOT NULL DEFAULT 'staff' AFTER `meeting_id`",
                'staff_id'      => "ALTER TABLE `$attendees` ADD COLUMN `staff_id` int(11) NULL AFTER `attendee_type`",
                'contact_id'    => "ALTER TABLE `$attendees` ADD COLUMN `contact_id` int(11) NULL AFTER `staff_id`",
                'email'         => "ALTER TABLE `$attendees` ADD COLUMN `email` varchar(191) NULL AFTER `contact_id`",
                'name'          => "ALTER TABLE `$attendees` ADD COLUMN `name` varchar(191) NULL AFTER `email`",
                'notified'      => "ALTER TABLE `$attendees` ADD COLUMN `notified` tinyint(1) NOT NULL DEFAULT 0 AFTER `name`",
                'created_at'    => "ALTER TABLE `$attendees` ADD COLUMN `created_at` datetime NULL AFTER `notified`",
            ];

            foreach ($columns as $column => $sql) {
                if (!$CI->db->field_exists($column, $attendees)) {
                    $CI->db->query($sql);
                }
            }
        }

        update_option('google_meet_version', '1.1.7');
        update_option('google_meet_client_login_required', '1');
        update_option('google_meet_client_portal_enabled', '1');
        update_option('google_meet_client_route', 'google_meet/client');
    }

    public function down()
    {
        // Upgrade only. Never remove or roll back production data.
    }
}

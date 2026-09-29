<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_122 extends App_module_migration
{
    public function up()
    {
        $CI =& get_instance();

        update_option('google_meet_client_route', 'google_meet/client');
        update_option('google_meet_client_portal_enabled', '1');

        $attendees = db_prefix() . 'google_meet_attendees';
        if ($CI->db->table_exists($attendees)) {
            if (!$CI->db->field_exists('contact_id', $attendees)) {
                $CI->db->query("ALTER TABLE `{$attendees}` ADD `contact_id` INT(11) NULL AFTER `staff_id`");
            }
            if (!$CI->db->field_exists('email', $attendees)) {
                $CI->db->query("ALTER TABLE `{$attendees}` ADD `email` VARCHAR(191) NULL AFTER `contact_id`");
            }

            $indexes = $CI->db->query("SHOW INDEX FROM `{$attendees}` WHERE Column_name = 'contact_id'")->result_array();
            if (empty($indexes)) {
                $CI->db->query("ALTER TABLE `{$attendees}` ADD INDEX `contact_id` (`contact_id`)");
            }
        }
    }

    public function down()
    {
        // Upgrade-only repair. Existing meeting assignments and settings are preserved.
    }
}

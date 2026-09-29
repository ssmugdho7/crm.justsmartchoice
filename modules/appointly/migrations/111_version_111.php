<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        add_option('appointly_google_client_secret', '');
        add_option('appointly_also_delete_in_google_calendar', 1);

        $table = db_prefix() . "appointly_appointments";

        // Add Google-related columns with existence checks
        if (!$CI->db->field_exists('google_event_id', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `google_event_id` VARCHAR(191) NULL DEFAULT NULL AFTER `id`;");
        }

        if (!$CI->db->field_exists('google_calendar_link', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `google_calendar_link` VARCHAR(191) NULL DEFAULT NULL AFTER `google_event_id`;");
        }

        if (!$CI->db->field_exists('google_added_by_id', $table)) {
            $CI->db->query("ALTER TABLE `{$table}` ADD `google_added_by_id` int(11) NULL DEFAULT NULL AFTER `google_calendar_link`;");
        }

        // Create Google table with proper error handling
        if (!$CI->db->table_exists(db_prefix() . "appointly_google")) {
            $CI->db->query(
                "CREATE TABLE IF NOT EXISTS " . db_prefix() . "appointly_google (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `staff_id` int(11) NOT NULL,
                    `access_token` varchar(191) NOT NULL,
                    `refresh_token` varchar(191) NOT NULL,
                    `expires_in` varchar(191) NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
            );
        }
    }
}

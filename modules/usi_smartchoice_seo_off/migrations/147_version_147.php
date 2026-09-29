<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_147 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_security_policies')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_security_policies` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `policy_name` VARCHAR(191) NOT NULL,
                `policy_area` VARCHAR(100) NOT NULL DEFAULT 'general',
                `policy_description` MEDIUMTEXT NULL,
                `is_required` TINYINT(1) NOT NULL DEFAULT 1,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_policy_area` (`policy_area`),
                KEY `idx_is_active` (`is_active`)
            ) " . $charset);
        }
        if (!$CI->db->table_exists($prefix . 'usi_ai_security_audit_events')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_security_audit_events` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `event_type` VARCHAR(100) NOT NULL,
                `event_area` VARCHAR(100) NOT NULL DEFAULT 'sammy_ai',
                `event_summary` MEDIUMTEXT NULL,
                `severity` VARCHAR(50) NOT NULL DEFAULT 'info',
                `staff_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_event_type` (`event_type`),
                KEY `idx_event_area` (`event_area`),
                KEY `idx_severity` (`severity`)
            ) " . $charset);
        }

        update_option('usi_smartchoice_seo_version', '1.4.7');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_security_audit_events')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_security_audit_events`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_security_policies')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_security_policies`'); }

    }
}

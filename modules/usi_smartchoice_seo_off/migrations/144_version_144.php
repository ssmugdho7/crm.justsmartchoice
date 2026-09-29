<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_144 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $CI->load->dbforge();

        $sessions = db_prefix() . 'usi_ai_vision_sessions';
        if (!$CI->db->table_exists($sessions)) {
            $CI->db->query('CREATE TABLE `' . $sessions . '` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(191) NOT NULL,
                `related_type` VARCHAR(50) NULL,
                `related_id` INT(11) NULL,
                `service_type` VARCHAR(100) NULL,
                `photo_notes` TEXT NULL,
                `measurement_notes` TEXT NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT "draft",
                `confidence` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                `created_by` INT(11) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `related_type_id` (`related_type`, `related_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        $findings = db_prefix() . 'usi_ai_vision_findings';
        if (!$CI->db->table_exists($findings)) {
            $CI->db->query('CREATE TABLE `' . $findings . '` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `vision_session_id` INT(11) NOT NULL,
                `finding_type` VARCHAR(100) NOT NULL,
                `description` TEXT NULL,
                `quantity` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `unit` VARCHAR(50) NULL,
                `estimate_impact` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `status` VARCHAR(50) NOT NULL DEFAULT "new",
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `vision_session_id` (`vision_session_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
        }

        update_option('usi_smartchoice_seo_version', '1.4.4');
    }

    public function down()
    {
        $CI = &get_instance();
        $CI->load->dbforge();
        if ($CI->db->table_exists(db_prefix() . 'usi_ai_vision_findings')) {
            $CI->dbforge->drop_table(db_prefix() . 'usi_ai_vision_findings', true);
        }
        if ($CI->db->table_exists(db_prefix() . 'usi_ai_vision_sessions')) {
            $CI->dbforge->drop_table(db_prefix() . 'usi_ai_vision_sessions', true);
        }
    }
}

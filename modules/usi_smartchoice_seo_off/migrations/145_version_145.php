<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_145 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        $charset = 'ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';';

        if (!$CI->db->table_exists($prefix . 'usi_ai_learning_courses')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_learning_courses` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `course_title` VARCHAR(191) NOT NULL,
                `course_type` VARCHAR(100) NOT NULL DEFAULT 'crm_training',
                `description` MEDIUMTEXT NULL,
                `target_role` VARCHAR(100) NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'draft',
                `created_by` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_course_type` (`course_type`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }
        if (!$CI->db->table_exists($prefix . 'usi_ai_learning_lessons')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_learning_lessons` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `course_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `lesson_title` VARCHAR(191) NOT NULL,
                `lesson_content` MEDIUMTEXT NULL,
                `sort_order` INT(11) NOT NULL DEFAULT 0,
                `status` VARCHAR(50) NOT NULL DEFAULT 'active',
                `created_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_course_id` (`course_id`),
                KEY `idx_status` (`status`)
            ) " . $charset);
        }
        if (!$CI->db->table_exists($prefix . 'usi_ai_learning_assignments')) {
            $CI->db->query('CREATE TABLE `' . $prefix . "usi_ai_learning_assignments` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `course_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `staff_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `assignment_status` VARCHAR(50) NOT NULL DEFAULT 'assigned',
                `assigned_at` DATETIME NULL,
                `completed_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_course_id` (`course_id`),
                KEY `idx_staff_id` (`staff_id`),
                KEY `idx_assignment_status` (`assignment_status`)
            ) " . $charset);
        }

        update_option('usi_smartchoice_seo_version', '1.4.5');
    }

    public function down()
    {
        $CI = &get_instance();
        $prefix = db_prefix();
        if ($CI->db->table_exists($prefix . 'usi_ai_learning_assignments')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_learning_assignments`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_learning_lessons')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_learning_lessons`'); }
        if ($CI->db->table_exists($prefix . 'usi_ai_learning_courses')) { $CI->db->query('DROP TABLE `' . $prefix . 'usi_ai_learning_courses`'); }

    }
}

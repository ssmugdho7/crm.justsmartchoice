<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// ✅ Create `tblticket_feedback` Table if it doesn't exist
if (!$CI->db->table_exists(db_prefix() . 'ticket_feedback')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "ticket_feedback` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `ticket_id` INT NOT NULL,
        `customer_id` INT NOT NULL,
        `rating` INT NULL DEFAULT NULL CHECK (rating BETWEEN 1 AND 5), -- ✅ Allow NULL & Default NULL
        `resolved` TINYINT(1) NOT NULL DEFAULT 0, -- ✅ Strict Mode Compatibility
        `response_time_satisfactory` TINYINT(1) NOT NULL DEFAULT 1, -- ✅ Ensures default values for strict mode
        `comments` TEXT NULL,
        `email_sent` TINYINT(1) NOT NULL DEFAULT 0, 
        `feedback_received` TINYINT(1) NOT NULL DEFAULT 0, 
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- ✅ Strict Mode Compatible
    );");
}

// ✅ Add missing columns to `tblticket_feedback`
if ($CI->db->table_exists(db_prefix() . 'ticket_feedback')) {
    if (!$CI->db->field_exists('email_sent', db_prefix() . 'ticket_feedback')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "ticket_feedback` ADD COLUMN `email_sent` TINYINT(1) NOT NULL DEFAULT 0;");
    }
    if (!$CI->db->field_exists('feedback_received', db_prefix() . 'ticket_feedback')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "ticket_feedback` ADD COLUMN `feedback_received` TINYINT(1) NOT NULL DEFAULT 0;");
    }
}

// ✅ Create `tblproject_feedback` Table if it doesn't exist
if (!$CI->db->table_exists(db_prefix() . 'project_feedback')) {
    $CI->db->query("CREATE TABLE `" . db_prefix() . "project_feedback` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `project_id` INT NOT NULL,
        `customer_id` INT NOT NULL,
        `rating` INT NULL DEFAULT NULL CHECK (rating BETWEEN 1 AND 5), -- ✅ Allow NULL & Default NULL for strict mode
        `satisfaction_level` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Satisfied, 0: Not Satisfied', -- ✅ Default Value
        `communication_quality` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Good, 0: Poor', -- ✅ Default Value
        `comments` TEXT NULL,
        `email_sent` TINYINT(1) NOT NULL DEFAULT 0,
        `feedback_received` TINYINT(1) NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- ✅ Strict Mode Compatible
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- ✅ Auto-update timestamps for strict mode
    );");
}

// ✅ Add missing columns to `tblproject_feedback`
if ($CI->db->table_exists(db_prefix() . 'project_feedback')) {
    if (!$CI->db->field_exists('email_sent', db_prefix() . 'project_feedback')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "project_feedback` ADD COLUMN `email_sent` TINYINT(1) NOT NULL DEFAULT 0;");
    }
    if (!$CI->db->field_exists('feedback_received', db_prefix() . 'project_feedback')) {
        $CI->db->query("ALTER TABLE `" . db_prefix() . "project_feedback` ADD COLUMN `feedback_received` TINYINT(1) NOT NULL DEFAULT 0;");
    }

    // ✅ Modify existing columns to comply with MySQL Strict Mode
    $CI->db->query("ALTER TABLE `" . db_prefix() . "project_feedback` 
        MODIFY COLUMN `rating` INT NULL DEFAULT NULL CHECK (rating BETWEEN 1 AND 5),
        MODIFY COLUMN `satisfaction_level` TINYINT(1) NOT NULL DEFAULT 0,
        MODIFY COLUMN `communication_quality` TINYINT(1) NOT NULL DEFAULT 0,
        MODIFY COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        MODIFY COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
}

?>

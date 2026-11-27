<?php
defined('BASEPATH') or exit('No direct script access allowed');
function feedbacks_upgrade_schema() {
    $CI = &get_instance();
        $p = db_prefix();
        $definitions = [
            'feedbacks_question_meta' => "`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `feedbackid` INT NOT NULL, `questionid` INT NOT NULL, `field_type` VARCHAR(40) NOT NULL DEFAULT 'input', `options_json` LONGTEXT NULL, `settings_json` LONGTEXT NULL, `datecreated` DATETIME NOT NULL, KEY `idx_feedbackid` (`feedbackid`)",
            'feedbacks_design' => "`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `feedbackid` INT NOT NULL, `settings_json` LONGTEXT NULL, `header_html` LONGTEXT NULL, `footer_html` LONGTEXT NULL, `dateupdated` DATETIME NOT NULL, KEY `idx_feedbackid` (`feedbackid`)",
            'feedbacks_audiences' => "`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(191) NOT NULL, `description` TEXT NULL, `criteria_json` LONGTEXT NULL, `created_by` INT NULL, `datecreated` DATETIME NOT NULL, `dateupdated` DATETIME NULL",
            'feedbacks_audience_members' => "`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `audience_id` INT NOT NULL, `name` VARCHAR(191) NULL, `email` VARCHAR(191) NOT NULL, `source` VARCHAR(60) NOT NULL DEFAULT 'manual', `source_id` VARCHAR(100) NULL, `datecreated` DATETIME NOT NULL, KEY `idx_audience` (`audience_id`)",
            'feedbacks_import_log' => "`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `staff_id` INT NULL, `filename` VARCHAR(255) NULL, `status` VARCHAR(30) NOT NULL, `details` LONGTEXT NULL, `datecreated` DATETIME NOT NULL",
            'feedbacks_participants' => "`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `resultsetid` INT NOT NULL, `feedbackid` INT NOT NULL, `participant_type` VARCHAR(20) NOT NULL, `participant_id` INT NULL, `datecreated` DATETIME NOT NULL, KEY `idx_feedback` (`feedbackid`)"
        ];
        foreach ($definitions as $suffix => $columns) {
            $table = $p . $suffix;
            if ($CI->db->table_exists($table)) { continue; }
            $sql = 'CREATE TABLE `'.str_replace('`', '``', $table).'` ('.$columns.') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
            if (!$CI->db->query($sql)) {
                log_message('error', 'Questionnaire migration 254 failed creating '.$table.': '.json_encode($CI->db->error()));
                return false;
            }
        }
        $base = $p . 'feedbacks';
        if ($CI->db->table_exists($base) && !$CI->db->field_exists('created_by', $base)) {
            if (!$CI->db->query('ALTER TABLE `'.str_replace('`', '``', $base).'` ADD COLUMN `created_by` INT NULL DEFAULT NULL')) {
                log_message('error', 'Questionnaire migration 254 failed adding created_by: '.json_encode($CI->db->error()));
                return false;
            }
        }
    return true;
}

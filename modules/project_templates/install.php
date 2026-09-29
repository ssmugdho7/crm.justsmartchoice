<?php
defined('BASEPATH') or exit('No direct script access allowed');

project_templates_db_up();

function project_templates_db_up(){
    $CI       = & get_instance();

    if (!$CI->db->table_exists(PROJECT_TEMPLATES_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' . PROJECT_TEMPLATES_TABLE_NAME.  "` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` mediumtext DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `send_created_email` tinyint(1) NOT NULL DEFAULT 0,
  `billing_type` int(11) NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 0,
  `progress` int(11) DEFAULT 0,
  `progress_from_tasks` int(11) NOT NULL DEFAULT 1,
  `project_cost` decimal(15,2) DEFAULT NULL,
  `project_rate_per_hour` decimal(15,2) DEFAULT NULL,
  `estimated_hours` decimal(15,2) DEFAULT NULL,
  `contact_notification` int(11) DEFAULT 1,
  `notify_contacts` text DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `dateadded` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(PROJECT_TEMPLATES_SETTINGS_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' . PROJECT_TEMPLATES_SETTINGS_TABLE_NAME.  "` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_template_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_template_id` (`project_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(PROJECT_TEMPLATES_MEMBERS_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' . PROJECT_TEMPLATES_MEMBERS_TABLE_NAME.  "` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_template_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_template_id` (`project_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES)) {
        $CI->db->query('CREATE TABLE `' .PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES. '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `relid` int(11) NOT NULL,
  `fieldid` int(11) NOT NULL,
  `fieldto` varchar(15) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(PROJECT_TEMPLATES_NOTES_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' .PROJECT_TEMPLATES_NOTES_TABLE_NAME. '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_template_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(PROJECT_TEMPLATES_FILES_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' .PROJECT_TEMPLATES_FILES_TABLE_NAME. '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(191) NOT NULL,
  `original_file_name` mediumtext DEFAULT NULL,
  `subject` varchar(191) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `filetype` varchar(50) DEFAULT NULL,
  `dateadded` datetime NOT NULL,
  `project_template_id` int(11) NOT NULL,
  `visible_to_customer` tinyint(1) DEFAULT 0,
  `external` varchar(40) DEFAULT NULL,
  `external_link` text DEFAULT NULL,
  `thumbnail_link` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }

    if (!$CI->db->table_exists(PROJECT_TEMPLATES_MILESTONE_TABLE_NAME)) {
        $CI->db->query('CREATE TABLE `' .PROJECT_TEMPLATES_MILESTONE_TABLE_NAME. '` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text,
  `description_visible_to_customer` tinyint(1) DEFAULT 0,
  `start_date` varchar(191) DEFAULT NULL,
  `due_date` varchar(191) NOT NULL,
  `project_template_id` int(11) NOT NULL,
  `color` varchar(10) DEFAULT NULL,
  `milestone_order` int(11) NOT NULL DEFAULT 0,
  `datecreated` date NOT NULL,
  `hide_from_customer` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
}
<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$charset = $CI->db->char_set ?: 'utf8';
$collate = $CI->db->dbcollat ?: 'utf8_general_ci';

function engproj_table($name) { return db_prefix() . $name; }
function engproj_add_column($table, $field, $sql) {
    $CI = &get_instance();
    if ($CI->db->table_exists($table) && !$CI->db->field_exists($field, $table)) {
        $CI->db->query("ALTER TABLE `{$table}` ADD {$sql}");
    }
}
function engproj_add_unique_index($table, $index, $column) {
    $CI = &get_instance();
    if (!$CI->db->table_exists($table)) { return; }
    $exists = $CI->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = " . $CI->db->escape($index))->row();
    if (!$exists) { @ $CI->db->query("ALTER TABLE `{$table}` ADD UNIQUE KEY `{$index}` ({$column})"); }
}

function engproj_add_index($table, $index, $column) {
    $CI = &get_instance();
    if (!$CI->db->table_exists($table)) { return; }
    $exists = $CI->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = " . $CI->db->escape($index))->row();
    if (!$exists) { $CI->db->query("ALTER TABLE `{$table}` ADD INDEX `{$index}` ({$column})"); }
}

$projectsTable = engproj_table('eng_engineering_projects');
$contractorsTable = engproj_table('eng_contractors');
$drawingsTable = engproj_table('eng_drawings');
$documentsTable = engproj_table('eng_documents');

if (!$CI->db->table_exists($projectsTable)) {
    $CI->db->query("CREATE TABLE `{$projectsTable}` (
      `engg_proj_id` INT(11) NOT NULL AUTO_INCREMENT,
      `project_id` INT(11) NULL DEFAULT NULL,
      `customer_id` INT(11) NULL DEFAULT NULL,
      `name` VARCHAR(255) NULL DEFAULT NULL,
      `start_date` DATE NULL DEFAULT NULL,
      `end_date` DATE NULL DEFAULT NULL,
      `site_survey_schedule` VARCHAR(195) NULL DEFAULT NULL,
      `site_survey_schedule_date` DATE NULL DEFAULT NULL,
      `contractor_id` INT(11) NOT NULL DEFAULT 0,
      `install_schedule` VARCHAR(195) NULL DEFAULT NULL,
      `install_schedule_date` DATE NULL DEFAULT NULL,
      `drawings_ids` LONGTEXT NULL,
      `final_inspection` VARCHAR(195) NULL DEFAULT NULL,
      `final_inspection_date` DATE NULL DEFAULT NULL,
      `document_ids` LONGTEXT NULL,
      `shared_file_ids` LONGTEXT NULL,
      `folder_path` VARCHAR(500) NULL DEFAULT NULL,
      `tpo_status` INT(11) NOT NULL DEFAULT 0,
      `created_by` INT(11) NULL DEFAULT NULL,
      `extra_meta` LONGTEXT NULL,
      `datecreated` DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`engg_proj_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate};");
}

if (!$CI->db->table_exists($contractorsTable)) {
    $CI->db->query("CREATE TABLE `{$contractorsTable}` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `contractor` VARCHAR(100) DEFAULT NULL,
      `email` VARCHAR(195) DEFAULT NULL,
      `phone` VARCHAR(195) DEFAULT NULL,
      `address` VARCHAR(195) DEFAULT NULL,
      `active` INT(11) NOT NULL DEFAULT 1,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate};");
}

if (!$CI->db->table_exists($drawingsTable)) {
    $CI->db->query("CREATE TABLE `{$drawingsTable}` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `engineering_project_id` INT(11) NULL DEFAULT NULL,
      `project_id` INT(11) NULL DEFAULT NULL,
      `customer_id` INT(11) NULL DEFAULT NULL,
      `name` VARCHAR(195) DEFAULT NULL,
      `type` VARCHAR(195) DEFAULT NULL,
      `draf` VARCHAR(255) DEFAULT NULL,
      `final_doc` VARCHAR(255) DEFAULT NULL,
      `folder_path` VARCHAR(500) DEFAULT NULL,
      `datecreated` DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate};");
}

if (!$CI->db->table_exists($documentsTable)) {
    $CI->db->query("CREATE TABLE `{$documentsTable}` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `engineering_project_id` INT(11) NULL DEFAULT NULL,
      `project_id` INT(11) NULL DEFAULT NULL,
      `customer_id` INT(11) NULL DEFAULT NULL,
      `name` VARCHAR(195) DEFAULT NULL,
      `noc` VARCHAR(255) DEFAULT NULL,
      `eng_letter` VARCHAR(255) DEFAULT NULL,
      `site_insp` VARCHAR(255) DEFAULT NULL,
      `permit` VARCHAR(255) DEFAULT NULL,
      `folder_path` VARCHAR(500) DEFAULT NULL,
      `datecreated` DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$charset} COLLATE={$collate};");
}

// Idempotent upgrade columns for older installs. No foreign keys: prevents MySQL 107/150 on mixed engines/collations.
foreach ([
    ['project_id', '`project_id` INT(11) NULL DEFAULT NULL AFTER `engg_proj_id`'],
    ['customer_id', '`customer_id` INT(11) NULL DEFAULT NULL AFTER `project_id`'],
    ['name', '`name` VARCHAR(255) NULL DEFAULT NULL AFTER `customer_id`'],
    ['start_date', '`start_date` DATE NULL DEFAULT NULL AFTER `name`'],
    ['end_date', '`end_date` DATE NULL DEFAULT NULL AFTER `start_date`'],
    ['shared_file_ids', '`shared_file_ids` LONGTEXT NULL AFTER `document_ids`'],
    ['folder_path', '`folder_path` VARCHAR(500) NULL DEFAULT NULL AFTER `shared_file_ids`'],
    ['created_by', '`created_by` INT(11) NULL DEFAULT NULL AFTER `tpo_status`'],
    ['extra_meta', '`extra_meta` LONGTEXT NULL AFTER `created_by`'],
] as $col) { engproj_add_column($projectsTable, $col[0], $col[1]); }

foreach ([$drawingsTable, $documentsTable] as $table) {
    engproj_add_column($table, 'engineering_project_id', '`engineering_project_id` INT(11) NULL DEFAULT NULL AFTER `id`');
    engproj_add_column($table, 'project_id', '`project_id` INT(11) NULL DEFAULT NULL AFTER `engineering_project_id`');
    engproj_add_column($table, 'customer_id', '`customer_id` INT(11) NULL DEFAULT NULL AFTER `project_id`');
    engproj_add_column($table, 'folder_path', '`folder_path` VARCHAR(500) NULL DEFAULT NULL');
    engproj_add_column($table, 'datecreated', '`datecreated` DATETIME DEFAULT CURRENT_TIMESTAMP');
}

engproj_add_index($projectsTable, 'idx_eng_project_id', '`project_id`');
engproj_add_index($projectsTable, 'idx_eng_customer_id', '`customer_id`');
engproj_add_index($drawingsTable, 'idx_eng_drawings_project', '`project_id`');
engproj_add_index($drawingsTable, 'idx_eng_drawings_customer', '`customer_id`');
engproj_add_index($documentsTable, 'idx_eng_documents_project', '`project_id`');
engproj_add_index($documentsTable, 'idx_eng_documents_customer', '`customer_id`');

add_option('engproj_required_fields', json_encode(['projects'=>['project_id','customer_id'], 'documents'=>['name'], 'drawings'=>['name','type']]));
add_option('engproj_option_fields', json_encode(['permit_number','scope','jurisdiction','engineer','review_status']));
add_option('engproj_version_safe_migrated', '2.5.0');

// Contractor duplicate prevention. Unique indexes are best-effort to preserve legacy dirty data.
engproj_add_index($contractorsTable, 'idx_eng_contractor_email', '`email`');
engproj_add_index($contractorsTable, 'idx_eng_contractor_phone', '`phone`');
update_option('engproj_version_safe_migrated', '2.5.3');

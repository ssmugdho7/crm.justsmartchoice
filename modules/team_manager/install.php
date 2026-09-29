<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// ----------------------------------------------------------------
// Create team_manager_teams table if it does not exist
// ----------------------------------------------------------------
$teams_table = db_prefix() . 'team_manager_teams';
if (!$CI->db->table_exists($teams_table)) {
    $sql = "CREATE TABLE `{$teams_table}` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `leader` INT(11) NOT NULL,           -- Should reference a staff ID
        `subleader` INT(11) DEFAULT NULL,      -- Optional reference to a staff ID
        `description` TEXT DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_leader` (`leader`),
        KEY `idx_subleader` (`subleader`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $CI->db->query($sql);
}

// ----------------------------------------------------------------
// Create team_manager_members table if it does not exist
// ----------------------------------------------------------------
$members_table = db_prefix() . 'team_manager_members';
if (!$CI->db->table_exists($members_table)) {
    $sql = "CREATE TABLE `{$members_table}` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `team_id` INT(11) NOT NULL,          -- References team_manager_teams.id
        `staff_id` INT(11) NOT NULL,           -- Should reference a staff ID
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_team_id` (`team_id`),
        KEY `idx_staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $CI->db->query($sql);
}

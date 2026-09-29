<?php

defined('BASEPATH') or exit('No direct script access allowed');

//create table flexagileprojects
add_option('flexagile_auto_add_new_task_to_active_sprint', 1);
add_option('flexagile_add_billable_tasks_only_to_sprint_invoice', 1);
if (!$CI->db->table_exists(db_prefix() .'flexagilesprints' )) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexagilesprints` (
    `id` int(11) NOT NULL,
    `project_id` int(11) NOT NULL,
    `name` mediumtext NOT NULL,
    `description` LONGTEXT NOT NULL,
    `start_date` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `date_added` datetime NOT NULL,
    `status` int(10) NOT NULL DEFAULT 0,
    `date_updated` datetime NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexagilesprints`
    ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexagilesprints`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

}

//create table flexagiletasks with task_id, sprint_id, status, date_added, date_updated
if(!$CI->db->table_exists(db_prefix() . 'flexagiletasks')){
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexagiletasks` (
    `id` int(11) NOT NULL,
    `task_id` int(11) NOT NULL,
    `sprint_id` int(11) NOT NULL,
    `status` int(10) NOT NULL DEFAULT 0,
    `date_added` datetime NOT NULL,
    `date_updated` datetime NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexagiletasks`
    ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexagiletasks`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
}
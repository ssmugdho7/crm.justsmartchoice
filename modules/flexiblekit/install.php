<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'flexiblekits')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexiblekits` (
    `id` int(11) NOT NULL,
    `flexiblekit_userid` int(11),
    `flexiblekit_contact_id` int(11),
    `flexiblekit_contact_name` mediumtext NOT NULL,
    `flexiblekit_contact_type` mediumtext NOT NULL,
    `flexiblekit_name` mediumtext NOT NULL,
    `flexiblekit_type` mediumtext NOT NULL,
    `flexiblekit_interval_type` mediumtext NOT NULL,
    `flexiblekit_interval` mediumtext NOT NULL,
    `flexiblekit_interval_label` mediumtext NOT NULL,
    `flexiblekit_active` tinyint(1) NOT NULL DEFAULT 0,
    `flexiblekit_skip_weekends` tinyint(1) NOT NULL DEFAULT 0,
    `flexiblekit_start_datetime` datetime NOT NULL,
    `flexiblekit_end_datetime` datetime NOT NULL,
    `flexiblekit_tags` text,
    `flexiblekit_description` text
  ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexiblekits`
    ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexiblekits`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
}

if (!$CI->db->table_exists(db_prefix() . 'flexibleschedules')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexibleschedules` (
    `id` int(11) NOT NULL,
    `flexiblekit_id` int(11) NOT NULL,
    `flexibleschedule_userid` int(11),
    `flexibleschedule_contact_id` int(11),
    `flexibleschedule_contact_name` mediumtext NOT NULL,
    `flexibleschedule_contact_type` mediumtext NOT NULL,
    `flexibleschedule_name` mediumtext NOT NULL,
    `flexibleschedule_subject` mediumtext NOT NULL,
    `flexibleschedule_type` mediumtext NOT NULL,
    `flexibleschedule_interval_label` mediumtext NOT NULL,
    `flexibleschedule_status` mediumtext NOT NULL,
    `flexibleschedule_start_datetime` datetime NOT NULL,
    `flexibleschedule_end_datetime` datetime NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexibleschedules`
    ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexibleschedules`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');
}

if(empty(get_option('flexiblekit_color'))){
  add_option('flexiblekit_color', FLEXIBLEKIT_COLOR);
}
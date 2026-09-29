<?php
defined('BASEPATH') or exit('No direct script access allowed');

project_templates_db_migration_down();

function project_templates_db_migration_down(){
    $CI       = & get_instance();

    if ($CI->db->table_exists(db_prefix().'project_templates')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'project_templates`;');
    }
    if ($CI->db->table_exists(db_prefix().'project_templates_settings')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'project_templates_settings`;');
    }
    if ($CI->db->table_exists(db_prefix().'project_templates_members')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'project_templates_members`;');
    }
    if ($CI->db->table_exists(db_prefix().'project_templates_custom_fields_values')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'project_templates_custom_fields_values`;');
    }
    if ($CI->db->table_exists(db_prefix().'project_templates_notes')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'project_templates_notes`;');
    }
    if ($CI->db->table_exists(db_prefix().'project_templates_files')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'project_templates_files`;');
    }
    if ($CI->db->table_exists(db_prefix().'project_templates_milestone')) {
        $CI->db->query('DROP TABLE `' . db_prefix().'project_templates_milestone`;');
    }

}
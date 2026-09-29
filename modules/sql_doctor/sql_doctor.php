<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: SQL Doctor
Description: Smart Choice SQL Doctor provides safe administrator-only CRM maintenance tools including cache cleanup, log review, module diagnostics, folder checks, safe database backup creation, and health reporting. It is portable and uses Perfex native paths and helpers.
Version: 1.0.1
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
*/

define('SQL_DOCTOR_MODULE_NAME', 'sql_doctor');

hooks()->add_action('admin_init', 'sql_doctor_admin_init');
register_activation_hook(SQL_DOCTOR_MODULE_NAME, 'sql_doctor_activation_hook');
register_deactivation_hook(SQL_DOCTOR_MODULE_NAME, 'sql_doctor_deactivation_hook');
register_language_files(SQL_DOCTOR_MODULE_NAME, [SQL_DOCTOR_MODULE_NAME]);

function sql_doctor_admin_init()
{
    if (!is_admin()) {
        return;
    }

    $CI = &get_instance();

    if (function_exists('admin_url')) {
        $CI->app_menu->add_sidebar_menu_item('sql-doctor', [
            'name'     => _l('sql_doctor'),
            'href'     => admin_url('sql_doctor'),
            'position' => 5,
            'icon'     => 'fa fa-database',
        ]);
    }

    $CI->app->add_settings_section('sql_doctor', [
        'name'     => _l('sql_doctor_settings'),
        'view'     => 'sql_doctor/settings',
        'position' => 65,
    ]);
}

function sql_doctor_activation_hook()
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(db_prefix().'sql_doctor_backups')) {
        $CI->db->query('CREATE TABLE `'.db_prefix().'sql_doctor_backups` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `file_name` VARCHAR(191) NOT NULL,
            `file_path` TEXT NOT NULL,
            `file_size` BIGINT NULL,
            `created_by` INT(11) NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET='.$CI->db->char_set.';');
    }
}

function sql_doctor_deactivation_hook()
{
    // Safe by design: does not delete backups, tables, or logs.
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

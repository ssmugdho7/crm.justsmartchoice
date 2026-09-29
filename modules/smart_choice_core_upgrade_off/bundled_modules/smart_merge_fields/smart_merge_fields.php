<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Merge Fields Automation
Description: Quality ★★★★★.  Smart Choice CRM Merge Fields Automation scans CRM tables, discovers merge fields, maps fields between modules, supports drag and drop mapping, database health checks, lead custom field creation, export, sync, and automation workflows. Quality: ★★★★★. Last Modified: 2026-06-30. Help Guide: detailed visual workflow guide included.
Version: 1.0.1
Requires at least: 3.4.1
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

define('SMART_MERGE_FIELDS_MODULE_NAME', 'smart_merge_fields');
define('SMART_MERGE_FIELDS_VERSION', '1.0.1');

define('SMART_MERGE_FIELDS_UPLOADS_PATH', module_dir_path(SMART_MERGE_FIELDS_MODULE_NAME, 'uploads'));

hooks()->add_action('admin_init', 'smart_merge_fields_admin_init');
hooks()->add_action('app_admin_head', 'smart_merge_fields_load_css');
hooks()->add_action('app_admin_footer', 'smart_merge_fields_load_js');

register_activation_hook(SMART_MERGE_FIELDS_MODULE_NAME, 'smart_merge_fields_activation_hook');
register_deactivation_hook(SMART_MERGE_FIELDS_MODULE_NAME, 'smart_merge_fields_deactivation_hook');
register_uninstall_hook(SMART_MERGE_FIELDS_MODULE_NAME, 'smart_merge_fields_uninstall_hook');

function smart_merge_fields_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function smart_merge_fields_deactivation_hook()
{
    // Non destructive by design.
}

function smart_merge_fields_uninstall_hook()
{
    require_once(__DIR__ . '/uninstall.php');
}

function smart_merge_fields_admin_init()
{
    $CI = &get_instance();

    if (function_exists('register_language_files')) {
        register_language_files(SMART_MERGE_FIELDS_MODULE_NAME, [SMART_MERGE_FIELDS_MODULE_NAME]);
    }

    $capabilities = [
        'capabilities' => [
            'view' => _l('permission_view') ?: 'View',
            'create' => _l('permission_create') ?: 'Create',
            'edit' => _l('permission_edit') ?: 'Edit',
            'delete' => _l('permission_delete') ?: 'Delete',
        ],
    ];
    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities('smart_merge_fields', $capabilities, 'Smart Merge Fields');
    }

    if (has_permission('smart_merge_fields', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('smart-merge-fields', [
            'name' => 'Merge Fields Automation',
            'href' => admin_url('smart_merge_fields'),
            'icon' => 'fa fa-random',
            'position' => 29,
        ]);
    }

    if (has_permission('smart_merge_fields', '', 'view')) {
        if (method_exists($CI->app_menu, 'add_setup_menu_item')) {
            $CI->app_menu->add_setup_menu_item('smart-merge-fields-setup', [
                'name' => 'Merge Fields Settings',
                'href' => admin_url('smart_merge_fields/settings'),
                'position' => 64,
            ]);
        }
    }
}

function smart_merge_fields_load_css()
{
    echo '<link href="' . module_dir_url(SMART_MERGE_FIELDS_MODULE_NAME, 'assets/css/smart_merge_fields.css') . '?v=' . SMART_MERGE_FIELDS_VERSION . '" rel="stylesheet" type="text/css" />';
}

function smart_merge_fields_load_js()
{
    echo '<script src="' . module_dir_url(SMART_MERGE_FIELDS_MODULE_NAME, 'assets/js/smart_merge_fields.js') . '?v=' . SMART_MERGE_FIELDS_VERSION . '"></script>';
}

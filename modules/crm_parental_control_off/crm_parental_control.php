<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: CRM Parental Control
Description: Smart Choice CRM administration control center for departments, role templates, permission governance, database/CRM backups, health checks, preview/apply, and rollback support.
Version: 1.0.3
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
*/

define('CRM_PARENTAL_CONTROL_MODULE_NAME', 'crm_parental_control');
define('CRM_PARENTAL_CONTROL_VERSION', '1.0.3');

register_activation_hook(CRM_PARENTAL_CONTROL_MODULE_NAME, 'crm_parental_control_activation_hook');
register_deactivation_hook(CRM_PARENTAL_CONTROL_MODULE_NAME, 'crm_parental_control_deactivation_hook');

function crm_parental_control_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

function crm_parental_control_deactivation_hook()
{
    // Non-destructive by design. No tables or backups are removed on deactivation.
}

hooks()->add_action('admin_init', 'crm_parental_control_admin_init');
hooks()->add_action('app_admin_head', 'crm_parental_control_head_assets');
hooks()->add_action('app_admin_footer', 'crm_parental_control_footer_assets');

function crm_parental_control_admin_init()
{
    $CI = &get_instance();

    if (function_exists('register_language_files')) {
        register_language_files(CRM_PARENTAL_CONTROL_MODULE_NAME, [CRM_PARENTAL_CONTROL_MODULE_NAME]);
    }

    if (function_exists('register_staff_capabilities')) {
        $capabilities = [
            'capabilities' => [
                'view'     => _l('permission_view') ?: 'View',
                'create'   => _l('permission_create') ?: 'Create',
                'edit'     => _l('permission_edit') ?: 'Edit',
                'delete'   => _l('permission_delete') ?: 'Delete',
                'settings' => _l('settings') ?: 'Settings',
                'backup'   => _l('crm_parental_control_backup') ?: 'Backup',
                'rollback' => _l('crm_parental_control_rollback') ?: 'Rollback',
            ],
        ];
        register_staff_capabilities(CRM_PARENTAL_CONTROL_MODULE_NAME, $capabilities, _l('crm_parental_control'));
    }

    if (has_permission(CRM_PARENTAL_CONTROL_MODULE_NAME, '', 'view') || is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('crm-parental-control', [
            'name'     => _l('crm_parental_control'),
            'href'     => admin_url('crm_parental_control'),
            'position' => 4,
            'icon'     => 'fa fa-shield-alt',
        ]);
    }

    // Settings page registration is intentionally disabled in this version.
    // Some shared hosting installs still surface deprecated settings-tab notices from cached module code.
    // The module dashboard is the single control center.

}

function crm_parental_control_head_assets()
{
    echo '<link href="' . module_dir_url(CRM_PARENTAL_CONTROL_MODULE_NAME, 'assets/css/crm_parental_control.css') . '?v=' . CRM_PARENTAL_CONTROL_VERSION . '" rel="stylesheet" type="text/css" />';
}

function crm_parental_control_footer_assets()
{
    echo '<script src="' . module_dir_url(CRM_PARENTAL_CONTROL_MODULE_NAME, 'assets/js/crm_parental_control.js') . '?v=' . CRM_PARENTAL_CONTROL_VERSION . '"></script>';
}

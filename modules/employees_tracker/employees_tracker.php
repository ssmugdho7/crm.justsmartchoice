<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Installer ETA
Description: Installer ETA connects Perfex CRM projects, appointments, staff location sharing, Google Maps, and the client portal so customers can see who is coming, what service is scheduled, live installer distance, estimated arrival time, staff photo, and arrival notifications.
Version: 1.1.3
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
Requires at least: 3.4.*
*/

define('EMPLOYEES_TRACKER_MODULE_NAME', 'employees_tracker');
define('EMPLOYEES_TRACKER_MODULE_VERSION', '1.1.3');

hooks()->add_action('admin_init', 'employees_tracker_init_menu_items');
hooks()->add_action('admin_init', 'employees_tracker_register_permissions');
hooks()->add_action('clients_init', 'employees_tracker_clients_area_menu_items');
hooks()->add_action('after_cron_run', 'employees_tracker_cron_eta_notifications');

register_language_files(EMPLOYEES_TRACKER_MODULE_NAME, ['employees_tracker']);

function employees_tracker_register_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'      => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'view_own'  => _l('permission_view_own'),
        'create'    => _l('permission_create'),
        'edit'      => _l('permission_edit'),
        'delete'    => _l('permission_delete'),
        'manage'    => _l('permission_manage'),
    ];

    register_staff_capabilities(EMPLOYEES_TRACKER_MODULE_NAME, $capabilities, _l('employees_tracker'));
}

function employees_tracker_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('employees_tracker', '', 'view') || has_permission('employees_tracker', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('employees-tracker', [
            'name'     => _l('employees_tracker_menu'),
            'icon'     => 'fa fa-location-arrow',
            'position' => 25,
            'collapse' => true,
        ]);

        $CI->app_menu->add_sidebar_children_item('employees-tracker', [
            'slug'     => 'employees-tracker-dashboard',
            'name'     => _l('employees_tracker_dashboard'),
            'icon'     => 'fa fa-map',
            'href'     => admin_url('employees_tracker'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('employees-tracker', [
            'slug'     => 'employees-tracker-assignments',
            'name'     => _l('employees_tracker_assignments'),
            'icon'     => 'fa fa-truck',
            'href'     => admin_url('employees_tracker/assignments'),
            'position' => 10,
        ]);

        if (has_permission('employees_tracker', '', 'manage')) {
            $CI->app_menu->add_sidebar_children_item('employees-tracker', [
                'slug'     => 'employees-tracker-settings',
                'name'     => _l('settings'),
                'icon'     => 'fa fa-cog',
                'href'     => admin_url('employees_tracker/settings'),
                'position' => 15,
            ]);
        }
    }
}

function employees_tracker_clients_area_menu_items()
{
    add_theme_menu_item('employees-tracker-client', [
        'name'     => _l('employees_tracker_client_menu'),
        'href'     => site_url('employees_tracker_client'),
        'position' => 20,
    ]);
}

register_activation_hook(EMPLOYEES_TRACKER_MODULE_NAME, 'employees_tracker_install');
function employees_tracker_install()
{
    require_once(__DIR__ . '/install.php');
    employees_tracker_module_install();
}

register_uninstall_hook(EMPLOYEES_TRACKER_MODULE_NAME, 'employees_tracker_uninstall');
function employees_tracker_uninstall()
{
    require_once(__DIR__ . '/uninstall.php');
    employees_tracker_module_uninstall();
}

function employees_tracker_cron_eta_notifications()
{
    $CI = &get_instance();
    if (!isset($CI->employees_tracker_model)) {
        $CI->load->model('employees_tracker/employees_tracker_model');
    }
    if (method_exists($CI->employees_tracker_model, 'process_eta_notifications')) {
        $CI->employees_tracker_model->process_eta_notifications();
    }
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

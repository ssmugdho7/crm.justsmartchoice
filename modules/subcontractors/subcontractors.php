<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Subcontractors
Description: Quality ★★★★★.  Complete Subcontractors system for Smart Choice CRM / Perfex CRM. Includes subcontractor profiles, public portal registration, project integration, subcontractor contracts, templates, signatures, initials, PDF/print views, attachments, settings, notifications, application alerts, task automation, email/SMS auto-replies, health check, and custom field support.
Version: 3.3.4
Requires at least: 3.4.0
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

define('SMARTSOURCE_SUBCONTRACTORS_MODULE_NAME', 'subcontractors');
define('SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER', FCPATH . 'uploads/smartsource_subcontractors/');

if (function_exists('register_language_files')) {
    register_language_files(SMARTSOURCE_SUBCONTRACTORS_MODULE_NAME, ['smartsource_subcontractors']);
}


hooks()->add_action('admin_init', 'smartsource_subcontractors_admin_init');
hooks()->add_action('admin_init', 'smartsource_subcontractors_register_settings_tab');
hooks()->add_action('app_admin_head', 'smartsource_subcontractors_admin_head');
hooks()->add_action('app_admin_footer', 'smartsource_subcontractors_admin_footer');
hooks()->add_filter('module_subcontractors_action_links', 'smartsource_subcontractors_action_links');
hooks()->add_action('after_custom_fields_select_options', 'smartsource_subcontractors_custom_field_options');
hooks()->add_filter('before_create_staff_member', 'smartsource_subcontractors_before_save_staff');
hooks()->add_filter('before_update_staff_member', 'smartsource_subcontractors_before_save_staff');

register_activation_hook(SMARTSOURCE_SUBCONTRACTORS_MODULE_NAME, 'smartsource_subcontractors_activation_hook');
register_uninstall_hook(SMARTSOURCE_SUBCONTRACTORS_MODULE_NAME, 'smartsource_subcontractors_uninstall_hook');

function smartsource_subcontractors_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';
}

function smartsource_subcontractors_uninstall_hook()
{
    $CI = &get_instance();
    $deleteData = get_option('smartsource_subcontractors_delete_data_on_uninstall');

    if ((string) $deleteData === '1') {
        $tables = [
            'smartsource_subcontractor_files',
            'smartsource_subcontractor_project_links',
            'smartsource_subcontractor_contracts',
            'smartsource_subcontractors',
            'smartsource_subcontractor_categories',
            'smartsource_subcontractor_statuses',
            'smartsource_subcontractor_contract_statuses',
            'smartsource_subcontractor_templates',
        ];

        foreach ($tables as $table) {
            if ($CI->db->table_exists(db_prefix() . $table)) {
                $CI->db->query('DROP TABLE `' . db_prefix() . $table . '`');
            }
        }

        smartsource_subcontractors_delete_directory(SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER);
    }

    delete_option('smartsource_subcontractors_enabled');
    delete_option('smartsource_subcontractors_delete_data_on_uninstall');
    delete_option('smartsource_help_sections_json');
}

function smartsource_subcontractors_delete_directory($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            smartsource_subcontractors_delete_directory($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

function smartsource_subcontractors_action_links($actions)
{
    
    $actions[] = '<a href="' . admin_url('settings?group=subcontractors') . '">' . _l('settings') . '</a>';
    return $actions;
}


function smartsource_subcontractors_repair_database_if_needed()
{
    $CI = &get_instance();
    $requiredTables = [
        'smartsource_subcontractors',
        'smartsource_subcontractor_contracts',
        'smartsource_subcontractor_files',
        'smartsource_subcontractor_project_links',
        'smartsource_subcontractor_categories',
        'smartsource_subcontractor_statuses',
        'smartsource_subcontractor_contract_statuses',
        'smartsource_subcontractor_templates',
    ];

    foreach ($requiredTables as $table) {
        if (!$CI->db->table_exists(db_prefix() . $table)) {
            require_once __DIR__ . '/install.php';
            return;
        }
    }
}

function smartsource_subcontractors_admin_init()
{
    $CI = &get_instance();

    smartsource_subcontractors_repair_database_if_needed();

    if (!is_dir(SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER)) {
        @mkdir(SMARTSOURCE_SUBCONTRACTORS_UPLOAD_FOLDER, 0755, true);
    }

    register_staff_capabilities('smartsource_subcontractors', [
        'capabilities' => [
            'view'     => _l('permission_view'),
            'view_own' => _l('permission_view_own'),
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
        ],
    ], _l('smartsource_subcontractors'));

    register_staff_capabilities('smartsource_subcontractor_contracts', [
        'capabilities' => [
            'view'     => _l('permission_view'),
            'view_own' => _l('permission_view_own'),
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
        ],
    ], _l('smartsource_subcontractor_contracts'));

    if (has_permission('smartsource_subcontractors', '', 'view') || has_permission('smartsource_subcontractors', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('smartsource-subcontractors', [
            'name'     => 'Subcontractors',
            'href'     => admin_url('subcontractors'),
            'position' => 36,
            'icon'     => 'fa fa-users',
        ]);

        $CI->app_menu->add_sidebar_children_item('smartsource-subcontractors', [
            'slug'     => 'smartsource-subcontractors-list',
            'name'     => 'Subcontractors',
            'href'     => admin_url('subcontractors'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('smartsource-subcontractors', [
            'slug'     => 'smartsource-subcontractor-contracts-list',
            'name'     => 'Subcontractor Contracts',
            'href'     => admin_url('subcontractors/contracts'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('smartsource-subcontractors', [
            'slug'     => 'smartsource-subcontractor-templates',
            'name'     => 'Subcontractor Templates',
            'href'     => admin_url('subcontractors/templates'),
            'position' => 3,
        ]);

        $CI->app_menu->add_sidebar_children_item('smartsource-subcontractors', [
            'slug'     => 'smartsource-subcontractor-portal',
            'name'     => 'Subcontractor Portal',
            'href'     => admin_url('subcontractors/portal_links'),
            'position' => 4,
        ]);

        $CI->app_menu->add_sidebar_children_item('smartsource-subcontractors', [
            'slug'     => 'smartsource-subcontractor-settings',
            'name'     => 'Settings',
            'href'     => admin_url('settings?group=subcontractors'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('smartsource-subcontractors', [
            'slug'     => 'smartsource-subcontractor-help',
            'name'     => 'Help',
            'href'     => admin_url('subcontractors/help'),
            'position' => 6,
        ]);
    }
}


function smartsource_subcontractors_register_settings_tab()
{
    $CI = &get_instance();
    if (!is_admin() || !isset($CI->app_tabs)) {
        return;
    }
    $CI->app_tabs->add_settings_tab('subcontractors', [
        'name'     => _l('smartsource_subcontractor_settings'),
        'view'     => 'subcontractors/admin/settings/setup',
        'position' => 48,
        'icon'     => 'fa fa-hard-hat',
    ]);
}

function smartsource_subcontractors_before_save_staff($data, $staffid = null)
{
    $CI = &get_instance();
    $data['is_subcontractor'] = $CI->input->post('is_subcontractor') ? 1 : 0;
    $data['smartsource_subcontractor_id'] = (int) $CI->input->post('smartsource_subcontractor_id');
    return $data;
}

function smartsource_subcontractors_custom_field_options($custom_field = null)
{
    $selected1 = isset($custom_field) && isset($custom_field->fieldto) && $custom_field->fieldto === 'smartsource_subcontractors' ? ' selected' : '';
    $selected2 = isset($custom_field) && isset($custom_field->fieldto) && $custom_field->fieldto === 'smartsource_subcontractor_contracts' ? ' selected' : '';
    echo '<option value="smartsource_subcontractors"' . $selected1 . '>Subcontractors</option>';
    echo '<option value="smartsource_subcontractor_contracts"' . $selected2 . '>Subcontractor Contracts</option>';
}

function smartsource_subcontractors_admin_head()
{
    echo '<link href="' . module_dir_url(SMARTSOURCE_SUBCONTRACTORS_MODULE_NAME, 'assets/css/smartsource_subcontractors.css') . '" rel="stylesheet" type="text/css" />';
}

function smartsource_subcontractors_admin_footer()
{
    $enabled = get_option('smartsource_application_screen_alerts') === '1'
        && function_exists('is_staff_logged_in') && is_staff_logged_in()
        && (has_permission('smartsource_subcontractors', '', 'view') || has_permission('smartsource_subcontractors', '', 'view_own'));

    $config = [
        'enabled'     => $enabled,
        'url'         => admin_url('subcontractors/application_alerts_json'),
        'ackUrl'      => admin_url('subcontractors/acknowledge_application_alert'),
        'sound'       => get_option('smartsource_application_alert_sound') === '1',
        'pollSeconds' => max(10, (int) (get_option('smartsource_application_alert_poll_seconds') ?: 20)),
    ];

    echo '<script>window.smartsourceApplicationAlerts = ' . json_encode($config) . ';</script>';
    echo '<script src="' . module_dir_url(SMARTSOURCE_SUBCONTRACTORS_MODULE_NAME, 'assets/js/smartsource_subcontractors.js') . '"></script>';
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

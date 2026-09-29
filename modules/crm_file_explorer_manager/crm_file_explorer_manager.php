<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: CRM File Explorer Manager
Description: Scan, review, preview, and safely back up CRM files, images, videos, documents, and module storage from one organized file explorer interface.
Version: 1.0.6
Requires at least: 3.0.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
*/

define('CRM_FILE_EXPLORER_MANAGER_MODULE_NAME', 'crm_file_explorer_manager');
define('CRM_FILE_EXPLORER_MANAGER_VERSION', '1.0.6');

hooks()->add_action('admin_init', 'crm_file_explorer_manager_admin_init');
hooks()->add_action('admin_init', 'crm_file_explorer_manager_permissions');

register_activation_hook(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, 'crm_file_explorer_manager_activation_hook');
register_deactivation_hook(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, 'crm_file_explorer_manager_deactivation_hook');
register_language_files(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, [CRM_FILE_EXPLORER_MANAGER_MODULE_NAME]);

function crm_file_explorer_manager_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

function crm_file_explorer_manager_deactivation_hook()
{
    // Data is intentionally preserved.
}

function crm_file_explorer_manager_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view_own' => _l('permission_view_own'),
        'view'     => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ];
    register_staff_capabilities(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, $capabilities, _l('crm_file_explorer_manager'));
}

function crm_file_explorer_manager_admin_init()
{
    $CI = &get_instance();

    if (has_permission(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, '', 'view') || has_permission(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, '', 'view_own') || is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('crm-file-explorer-manager', [
            'name'     => _l('crm_file_explorer_manager'),
            'href'     => admin_url('crm_file_explorer_manager'),
            'position' => 62,
            'icon'     => 'fa fa-folder-open',
        ]);

        $CI->app_menu->add_sidebar_children_item('crm-file-explorer-manager', [
            'slug'     => 'crm-file-explorer-dashboard',
            'name'     => _l('file_dashboard'),
            'href'     => admin_url('crm_file_explorer_manager'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('crm-file-explorer-manager', [
            'slug'     => 'crm-file-explorer-media',
            'name'     => _l('media_viewer'),
            'href'     => admin_url('crm_file_explorer_manager/media'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('crm-file-explorer-manager', [
            'slug'     => 'crm-file-explorer-backups',
            'name'     => _l('safe_backups'),
            'href'     => admin_url('crm_file_explorer_manager/backups'),
            'position' => 3,
        ]);
    }

    if (is_admin()) {
        if (version_compare(get_app_version(), '3.2.0', '<')) {
            $CI->app->add_settings_section_child('other', 'crm_file_explorer_manager_settings', [
                'id'                    => 'crm_file_explorer_manager_settings',
                'name'                  => _l('crm_file_explorer_manager_settings'),
                'view'                  => 'crm_file_explorer_manager/settings/settings',
                'icon'                  => 'fa fa-folder-open fa-fw',
                'position'              => 86,
                'without_submit_button' => true,
            ]);
        } else {
            $CI->app->add_settings_section('crm-file-explorer-manager-settings', [
                'title'    => _l('crm_file_explorer_manager'),
                'position' => 86,
                'children' => [
                    [
                        'id'                    => 'crm_file_explorer_manager_settings',
                        'name'                  => _l('crm_file_explorer_manager_settings'),
                        'view'                  => 'crm_file_explorer_manager/settings/settings',
                        'icon'                  => 'fa fa-folder-open fa-fw',
                        'position'              => 10,
                        'without_submit_button' => true,
                    ],
                ],
            ]);
        }
    }
}

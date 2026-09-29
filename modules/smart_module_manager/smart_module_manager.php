<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Module Manager
Description: Smart Choice module backup, download, health check, and clean unload manager for Perfex CRM.
Version: 1.0.5
Requires at least: 3.4.1
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
*/

define('SMART_MODULE_MANAGER_MODULE_NAME', 'smart_module_manager');
define('SMART_MODULE_MANAGER_VERSION', '1.0.5');

register_activation_hook(SMART_MODULE_MANAGER_MODULE_NAME, 'smart_module_manager_activation_hook');
register_deactivation_hook(SMART_MODULE_MANAGER_MODULE_NAME, 'smart_module_manager_deactivation_hook');
register_uninstall_hook(SMART_MODULE_MANAGER_MODULE_NAME, 'smart_module_manager_uninstall_hook');

hooks()->add_action('admin_init', 'smart_module_manager_admin_init_hook');
hooks()->add_action('app_admin_footer', 'smart_module_manager_admin_footer_hook');
hooks()->add_action('app_admin_head', 'smart_module_manager_admin_head_hook');

function smart_module_manager_activation_hook()
{
    require_once __DIR__ . '/install.php';
}

function smart_module_manager_deactivation_hook()
{
    update_option('smart_module_manager_enabled', '0');
}

function smart_module_manager_uninstall_hook()
{
    require_once __DIR__ . '/uninstall.php';
}

function smart_module_manager_admin_init_hook()
{
    $CI = &get_instance();

    register_language_files(SMART_MODULE_MANAGER_MODULE_NAME, [SMART_MODULE_MANAGER_MODULE_NAME]);

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'        => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'view_own'    => _l('permission_view_own'),
        'create'      => _l('permission_create'),
        'edit'        => _l('permission_edit'),
        'delete'      => _l('permission_delete'),
        'download'    => _l('smart_module_manager_permission_download'),
        'unload'      => _l('smart_module_manager_permission_unload'),
        'settings'    => _l('smart_module_manager_permission_settings'),
        'health_check'=> _l('smart_module_manager_permission_health_check'),
    ];
    register_staff_capabilities(SMART_MODULE_MANAGER_MODULE_NAME, $capabilities, _l('smart_module_manager'));

    if (is_admin() || has_permission(SMART_MODULE_MANAGER_MODULE_NAME, '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('smart-module-manager', [
            'name'     => _l('smart_module_manager'),
            'href'     => admin_url('smart_module_manager'),
            'position' => 97,
            'icon'     => 'fa fa-archive',
        ]);

        $CI->app_menu->add_sidebar_children_item('smart-module-manager', [
            'slug'     => 'smart-module-manager-dashboard',
            'name'     => _l('smart_module_manager_dashboard'),
            'href'     => admin_url('smart_module_manager'),
            'position' => 1,
            'icon'     => 'fa fa-dashboard',
        ]);

        $CI->app_menu->add_sidebar_children_item('smart-module-manager', [
            'slug'     => 'smart-module-manager-health',
            'name'     => _l('smart_module_manager_health_check'),
            'href'     => admin_url('smart_module_manager/health'),
            'position' => 2,
            'icon'     => 'fa fa-heartbeat',
        ]);

        $CI->app_menu->add_sidebar_children_item('smart-module-manager', [
            'slug'     => 'smart-module-manager-settings',
            'name'     => _l('smart_module_manager_settings'),
            'href'     => admin_url('smart_module_manager/settings'),
            'position' => 3,
            'icon'     => 'fa fa-cogs',
        ]);

        $CI->app_menu->add_setup_menu_item('smart-module-manager-setup', [
            'name'     => _l('smart_module_manager'),
            'href'     => admin_url('smart_module_manager/settings'),
            'position' => 67,
            'icon'     => 'fa fa-archive',
        ]);
    }
}

function smart_module_manager_admin_head_hook()
{
    echo '<link href="' . module_dir_url(SMART_MODULE_MANAGER_MODULE_NAME, 'assets/css/smart_module_manager.css') . '?v=' . SMART_MODULE_MANAGER_VERSION . '" rel="stylesheet" type="text/css" />';
}

function smart_module_manager_admin_footer_hook()
{
    echo '<script>window.smartModuleManagerAdminUrl="' . admin_url('smart_module_manager') . '";</script>';
    echo '<script src="' . module_dir_url(SMART_MODULE_MANAGER_MODULE_NAME, 'assets/js/smart_module_manager.js') . '?v=' . SMART_MODULE_MANAGER_VERSION . '"></script>';
}

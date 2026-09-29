<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Procurement Manager
Description: Procurement tracker and supplier management for any Perfex project.
Version: 1.0.0
Author: ChatGPT Assistant
*/

define('PROCUREMENT_MANAGER_MODULE_NAME', 'procurement_manager');

hooks()->add_action('admin_init', 'procurement_manager_init_menu_items');
hooks()->add_action('admin_init', 'procurement_manager_permissions');

register_activation_hook(PROCUREMENT_MANAGER_MODULE_NAME, 'procurement_manager_module_activation_hook');
register_deactivation_hook(PROCUREMENT_MANAGER_MODULE_NAME, 'procurement_manager_module_deactivation_hook');
register_uninstall_hook(PROCUREMENT_MANAGER_MODULE_NAME, 'procurement_manager_uninstall');

/**
 * Add sidebar menu item in Admin
 */
function procurement_manager_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('procurement_manager', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('procurement-manager', [
            'name'     => _l('procurement_manager'),
            'href'     => admin_url('procurement_manager'),
            'icon'     => 'fa fa-shopping-cart',
            'position' => 38,
        ]);
    }
}

/**
 * Register staff permissions
 */
function procurement_manager_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('procurement_manager', $capabilities, _l('procurement_manager'));
}

/**
 * On module activation
 */
function procurement_manager_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * On module deactivation
 */
function procurement_manager_module_deactivation_hook()
{
    // You can place logic here if needed when module is deactivated.
}

/**
 * On module uninstall
 */
function procurement_manager_uninstall()
{
    require_once(__DIR__ . '/uninstall.php');
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Network Manager
Description: Secure home and office network dashboard for Perfex CRM. Includes local Windows agent integration, device inventory, schedules, safe tools, health checker, database checker, logs, settings, and help guide.
Version: 1.1.0
Requires at least: 3.0.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
*/

define('SMART_NETWORK_MANAGER_MODULE_NAME', 'smart_network_manager');
define('SMART_NETWORK_MANAGER_VERSION', '1.1.0');

hooks()->add_action('admin_init', 'smart_network_manager_admin_init');
hooks()->add_action('admin_init', 'smart_network_manager_permissions');
hooks()->add_action('app_admin_head', 'smart_network_manager_head_assets');
hooks()->add_action('app_admin_footer', 'smart_network_manager_footer_assets');

register_activation_hook(SMART_NETWORK_MANAGER_MODULE_NAME, 'smart_network_manager_activation_hook');
register_deactivation_hook(SMART_NETWORK_MANAGER_MODULE_NAME, 'smart_network_manager_deactivation_hook');
register_language_files(SMART_NETWORK_MANAGER_MODULE_NAME, [SMART_NETWORK_MANAGER_MODULE_NAME]);

function smart_network_manager_activation_hook()
{
    require_once(__DIR__ . '/install.php');
    update_option('smart_network_manager_version', SMART_NETWORK_MANAGER_VERSION);
    add_option('smart_network_manager_agent_url', '');
    add_option('smart_network_manager_agent_token', bin2hex(random_bytes(24)));
    add_option('smart_network_manager_agent_ip_whitelist', '');
    add_option('smart_network_manager_router_type', 'manual');
    add_option('smart_network_manager_router_url', '');
    add_option('smart_network_manager_phpmyadmin_url', 'https://s111.bluehost.com:2083/3rdparty/phpMyAdmin/index.php');
    add_option('smart_network_manager_enable_controls', '0');
    add_option('smart_network_manager_theme_color', '#169179');
    add_option('smart_network_manager_allow_cache_cleanup', '1');
    add_option('smart_network_manager_allow_database_tools', '1');
}

function smart_network_manager_deactivation_hook() {}

function smart_network_manager_admin_init()
{
    $CI = &get_instance();

    if (has_permission('smart_network_manager', '', 'view') || is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('smart-network-manager', [
            'name'     => _l('smart_network_manager'),
            'href'     => admin_url('smart_network_manager'),
            'position' => 58,
            'icon'     => 'fa fa-network-wired',
        ]);
    }

    $CI->app->add_settings_section('smart_network_manager', [
        'name'     => _l('smart_network_manager_settings'),
        'view'     => 'smart_network_manager/settings',
        'position' => 64,
    ]);
}

function smart_network_manager_permissions()
{
    register_staff_capabilities('smart_network_manager', [
        'capabilities' => [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ], _l('smart_network_manager'));
}

function smart_network_manager_head_assets()
{
    echo '<link href="' . module_dir_url(SMART_NETWORK_MANAGER_MODULE_NAME, 'assets/css/smart_network_manager.css') . '?v=' . SMART_NETWORK_MANAGER_VERSION . '" rel="stylesheet" type="text/css" />';
}

function smart_network_manager_footer_assets()
{
    echo '<script src="' . module_dir_url(SMART_NETWORK_MANAGER_MODULE_NAME, 'assets/js/smart_network_manager.js') . '?v=' . SMART_NETWORK_MANAGER_VERSION . '"></script>';
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

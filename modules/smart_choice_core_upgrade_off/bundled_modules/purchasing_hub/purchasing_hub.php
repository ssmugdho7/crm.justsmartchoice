<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Purchasing Hub
Description: Purchasing, vendor, item, purchase order, accounts payable, contract editor, PDF/email workflow, attachments, and CRM-linked procurement reports for Perfex CRM. Quality: ★★★★★
Version: 2.1.8
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
Quality: ★★★★★
Last Modified: 2026-06-30
*/

define('PURCHASING_HUB_MODULE_NAME', 'purchasing_hub');
define('PURCHASING_HUB_VERSION', '2.1.8');

hooks()->add_action('admin_init', 'purchasing_hub_admin_init');
hooks()->add_action('app_admin_head', 'purchasing_hub_load_css');

register_activation_hook(PURCHASING_HUB_MODULE_NAME, 'purchasing_hub_activation_hook');
register_language_files(PURCHASING_HUB_MODULE_NAME, [PURCHASING_HUB_MODULE_NAME]);

function purchasing_hub_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

function purchasing_hub_load_css()
{
    echo '<link href="' . module_dir_url(PURCHASING_HUB_MODULE_NAME, 'assets/css/purchasing_hub.css') . '?v=' . PURCHASING_HUB_VERSION . '" rel="stylesheet" type="text/css" />';
}

function purchasing_hub_admin_init()
{
    $CI = &get_instance();

    if (is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('purchasing-hub', [
            'name'     => 'Purchasing Hub',
            'href'     => admin_url('purchasing_hub'),
            'position' => 41,
            'icon'     => 'fa fa-shopping-cart',
        ]);

        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-dashboard',
            'name'     => 'Dashboard',
            'href'     => admin_url('purchasing_hub'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-items',
            'name'     => 'Items',
            'href'     => admin_url('purchasing_hub/items'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-vendors',
            'name'     => 'Vendors',
            'href'     => admin_url('purchasing_hub/vendors'),
            'position' => 3,
        ]);

        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-purchase-orders',
            'name'     => 'Purchase Orders',
            'href'     => admin_url('purchasing_hub/purchase_orders'),
            'position' => 4,
        ]);

        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-accounts-payable',
            'name'     => 'Accounts Payable',
            'href'     => admin_url('purchasing_hub/accounts_payable'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-vendor-quotes',
            'name'     => 'Vendor Quotes',
            'href'     => admin_url('purchasing_hub/vendor_quotes'),
            'position' => 6,
        ]);


        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-reports-child',
            'name'     => 'Purchasing Reports',
            'href'     => admin_url('purchasing_hub/reports'),
            'position' => 7,
        ]);

        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-settings-child',
            'name'     => 'Settings',
            'href'     => admin_url('purchasing_hub/settings'),
            'position' => 8,
        ]);

        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-health-child',
            'name'     => 'Health Check',
            'href'     => admin_url('purchasing_hub/health_check'),
            'position' => 9,
        ]);

        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-help-child',
            'name'     => 'Help Guide',
            'href'     => admin_url('purchasing_hub/help_training'),
            'position' => 10,
        ]);

        $CI->app_menu->add_sidebar_children_item('purchasing-hub', [
            'slug'     => 'purchasing-hub-contracts',
            'name'     => 'Contracts',
            'href'     => admin_url('purchasing_hub/contracts'),
            'position' => 7,
        ]);
    }

    // Add Purchasing Reports inside the native Perfex Reports menu when that menu exists.
    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'purchasing-hub-native-reports',
            'name'     => 'Purchasing Reports',
            'href'     => admin_url('purchasing_hub/reports'),
            'position' => 60,
        ]);

        // Fallback direct menu item for installations where the native Reports menu slug is not registered yet.
        $CI->app_menu->add_sidebar_menu_item('purchasing-hub-reports', [
            'name'     => 'Purchasing Reports',
            'href'     => admin_url('purchasing_hub/reports'),
            'position' => 46,
            'icon'     => 'fa fa-bar-chart',
        ]);
    }

    $CI->app->add_settings_section('purchasing_hub_settings', [
        'name'     => 'Purchasing Hub Settings',
        'view'     => 'purchasing_hub/settings/general',
        'position' => 65,
    ]);

    $CI->app->add_settings_section('purchasing_hub_health', [
        'name'     => 'Purchasing Hub Health Check',
        'view'     => 'purchasing_hub/settings/health',
        'position' => 66,
    ]);

    $CI->app->add_settings_section('purchasing_hub_help', [
        'name'     => 'Purchasing Hub Help Guide',
        'view'     => 'purchasing_hub/settings/help',
        'position' => 67,
    ]);
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Module Name: IP Address Management (IPAM)
 * Description: Manage IP addresses, networks, VLANs, and related infrastructure within Perfex CRM.
 * Author: Smart Choice Contractors USA / Harold Cabrera
 * Author URI: https://justsmartchoice.com
 * Version: 1.0.1
 */
define('IPAM_MODULE_NAME', 'ipam');

hooks()->add_action('admin_init', 'ipam_module_init_menu_items');
hooks()->add_action('admin_init', 'ipam_permissions');
register_activation_hook(IPAM_MODULE_NAME, function () {
    require_once __DIR__ . '/install.php';
});

function ipam_load_assets()
{
    // Change “ipam” to your actual module folder name if different
    echo '<link 
      href="' . module_dir_url('ipam', 'assets/css/styles.css') . '" 
      rel="stylesheet" 
      type="text/css" 
    />';
}

function ipam_module_init_menu_items()
{
    $CI = &get_instance();
    $CI->app_menu->add_sidebar_menu_item('ipam', [
        'name'     => 'IPAM',
        'href'     => admin_url('ipam/locations'),
        'icon'     => 'fa fa-network-wired', // Icon for the main menu item
        'position' => 10,
    ]);

    $CI->app_menu->add_sidebar_children_item('ipam', [
        'slug'     => 'ipam-locations',
        'name'     => 'Locations',
        'href'     => admin_url('ipam/locations'),
        'icon'     => 'fa fa-map-marker-alt', // Icon for Locations
        'position' => 11,
    ]);

   

    $CI->app_menu->add_sidebar_children_item('ipam', [
        'slug'     => 'ipam-vlans',
        'name'     => 'VLANs',
        'href'     => admin_url('ipam/vlans'),
        'icon'     => 'fa fa-exchange-alt', // Icon for VLANs
        'position' => 13,
    ]);
    
 $CI->app_menu->add_sidebar_children_item('ipam', [
        'slug'     => 'ipam-networks',
        'name'     => 'Networks',
        'href'     => admin_url('ipam/networks'),
        'icon'     => 'fa fa-sitemap', // Icon for Networks
        'position' => 12,
    ]);
    
    $CI->app_menu->add_sidebar_children_item('ipam', [
        'slug'     => 'ipam-interfaces',
        'name'     => 'Interfaces',
        'href'     => admin_url('ipam/interfaces'),
        'icon'     => 'fa fa-plug', // Icon for Interfaces
        'position' => 14,
    ]);

    $CI->app_menu->add_sidebar_children_item('ipam', [
        'slug'     => 'ipam-ipaddresses',
        'name'     => 'IP Addresses',
        'href'     => admin_url('ipam/ipaddresses'),
        'icon'     => 'fa fa-address-card', // Icon for IP Addresses
        'position' => 15,
    ]);
}

function ipam_permissions()
{
    $capabilities = [
        'view',
        'create',
        'edit',
        'delete'
    ];

    register_staff_capabilities('ipam', $capabilities, 'IPAM');
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */


// Smart Choice structural standard assets and permissions.
if (function_exists('hooks')) {
    hooks()->add_action('app_admin_head', 'ipam_smart_choice_standard_head');
    hooks()->add_action('app_admin_footer', 'ipam_smart_choice_standard_footer');
}
if (!function_exists('ipam_smart_choice_standard_head')) {
    function ipam_smart_choice_standard_head() {
        echo '<link href="' . module_dir_url('ipam', 'assets/css/smart_choice_module_standard.css') . '?v=100" rel="stylesheet" type="text/css" />';
    }
}
if (!function_exists('ipam_smart_choice_standard_footer')) {
    function ipam_smart_choice_standard_footer() {
        echo '<script src="' . module_dir_url('ipam', 'assets/js/smart_choice_module_standard.js') . '?v=100"></script>';
    }
}
if (function_exists('register_staff_capabilities')) {
    register_staff_capabilities('ipam', [
        'view_own' => _l('view_own'),
        'view' => _l('view_global'),
        'create' => _l('create'),
        'edit' => _l('edit'),
        'delete' => _l('delete'),
    ], _l('ipam'));
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: QuickShare
Description: Effortless online file transfer and sharing. Securely upload, store, and share on files. Simplify workflows, boost productivity, and ensure data privacy. Start maximizing productivity today.
Version: 1.1.0
Author: LenzCreative
Author URI: https://codecanyon.net/user/lenzcreativee
Requires at least: 1.0.*
*/

define('QUICK_SHARE_MODULE_NAME', 'quick_share');

hooks()->add_action('admin_init', 'quick_share_module_init_menu_items');
hooks()->add_action('admin_init', 'quick_share_permissions');

include( __DIR__ . '/vendor/autoload.php');

/**
 * Load the module helper
 */
$CI = & get_instance();
$CI->load->helper(QUICK_SHARE_MODULE_NAME . '/quick_share'); //on module main file

function quick_share_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
        'view_downloads' => _l('qs_permission_view_downloads'),
    ];

    register_staff_capabilities('quick_share', $capabilities, _l('quick_share'));
}

/**
 * Register activation module hook
 */
register_activation_hook(QUICK_SHARE_MODULE_NAME, 'quick_share_module_activation_hook');

function quick_share_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(QUICK_SHARE_MODULE_NAME, [QUICK_SHARE_MODULE_NAME]);

/**
 * Init quick share module menu items in setup in admin_init hook
 * @return null
 */
function quick_share_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
        'name'       => _l('quick_share'),
        'url'        => 'quick_share/upload',
        'permission' => 'quick_share',
        'position'   => 53,
        'icon'       => 'fa-solid fa-upload',
    ]);

    if (has_permission('quick_share', '', 'view')) {

        $CI->app_menu->add_sidebar_menu_item('quick_share', [
            'slug'     => 'quick_share',
            'name'     => _l('quick_share'),
            'position' => 6,
            'icon'     => 'fa-solid fa-upload',
            'href'     => admin_url('quick_share')
        ]);

    }

    if (has_permission('quick_share', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('quick_share', [
            'slug' => 'quickshare-view',
            'name' => _l('qs_menu_view_list'),
            'href' => admin_url('quick_share'),
            'position' => 11,
        ]);
    }

    if (has_permission('quick_share', '', 'create')) {
        $CI->app_menu->add_sidebar_children_item('quick_share', [
            'slug' => 'quickshare-upload',
            'name' => _l('qs_menu_upload_file'),
            'href' => admin_url('quick_share/upload'),
            'position' => 11,
        ]);
    }

    if (has_permission('quick_share', '', 'view_downloads')) {
        $CI->app_menu->add_sidebar_children_item('quick_share', [
            'slug' => 'quickshare-downloads-view',
            'name' => _l('qs_permission_manage_downloads'),
            'href' => admin_url('quick_share/manage_downloads'),
            'position' => 11,
        ]);
    }

    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('quick_share', [
            'slug' => 'quickshare-settings',
            'name' => _l('qs_menu_settings'),
            'href' => admin_url('quick_share/settings'),
            'position' => 11,
        ]);
    }
}
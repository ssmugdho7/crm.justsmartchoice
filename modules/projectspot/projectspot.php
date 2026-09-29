<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: ProjectSpot
Description: ProjectSpot is a powerful module that allows you to exhibit your completed projects in an impressive showcase. With this module, you can highlight your work and enable clients to easily browse and explore your portfolio. Display captivating visuals, provide project details, and leave a lasting impression. ProjectSpot is your go-to tool for showcasing your accomplishments and attracting new clients to your business.
Version: 1.0.0
Author: LenzCreative
Author URI: https://codecanyon.net/user/lenzcreativee/portfolio
Requires at least: 1.0.*
*/

define('PROJECTSPOT_MODULE_NAME', 'projectspot');

hooks()->add_action('admin_init', 'projectspot_module_init_menu_items');
hooks()->add_action('admin_init', 'projectspot_permissions');
hooks()->add_action('clients_init', 'projectspot_module_clients_init_menu_items');

/**
 * Load the module helper
 */
$CI = & get_instance();
$CI->load->helper(PROJECTSPOT_MODULE_NAME . '/projectspot'); //on module main file

function projectspot_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('projectspot', $capabilities, _l('projectspot'));
}

/**
 * Register activation module hook
 */
register_activation_hook(PROJECTSPOT_MODULE_NAME, 'projectspot_module_activation_hook');

function projectspot_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(PROJECTSPOT_MODULE_NAME, [PROJECTSPOT_MODULE_NAME]);

/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
function projectspot_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('projectspot', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('projectspot', [
            'slug' => 'projectspot',
            'name' => _l('projectspot'),
            'position' => 6,
            'icon' => 'fa fa-camera-retro'
        ]);
    }

    if (has_permission('projectspot', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('projectspot', [
            'slug' => 'projectspot-projects',
            'name' => _l('projectspot_projects'),
            'position' => 6,
            'icon' => 'fas fa-clipboard',
            'href'     => admin_url('projectspot/projects')
        ]);
    }

    if (has_permission('projectspot', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('projectspot', [
            'slug' => 'projectspot-categories',
            'name' => _l('projectspot_categories'),
            'position' => 6,
            'icon' => 'fas fa-tags',
            'href'     => admin_url('projectspot/project_categories')
        ]);
    }

    if (is_admin()) {

        $CI->app_menu->add_sidebar_children_item('projectspot', [
            'slug'     => 'projectspot-settings',
            'name'     => _l('settings'),
            'position' => 6,
            'icon'     => 'fa-solid fa-cog',
            'href'     => admin_url('projectspot/settings')
        ]);

    }

}

function projectspot_module_clients_init_menu_items() {

    $CI = &get_instance();

    $accessMenu = false;

    if (get_option('projectspot_show_menu_client_side') == '1') {
        $accessMenu = true;
    }
    if (get_option('projectspot_should_client_be_logged_in') == '0' && !is_client_logged_in()) {
        $accessMenu = false;
    }

    if ($accessMenu) {
        $CI->app_menu->add_theme_item('projectspot', [
            'name' => _l('projectspot_projects_showcase'),
            'href' => site_url('projectspot/projectspot_client/projects_showcase'),
            'position' => 10,
        ]);
    }

}
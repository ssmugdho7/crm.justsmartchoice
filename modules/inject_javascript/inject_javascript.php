<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Custom JavaScript
Description: Custom JavaScript module for Perfex CRM
Version: 1.0
Requires at least: 2.3.*
*/

define('inject_javascript_MODULE_NAME', 'inject_javascript');

$CI = &get_instance();

/**
 * Load the module helper
 */
$CI->load->helper(inject_javascript_MODULE_NAME . '/inject_javascript');

/**
 * Register activation module hook
 */
register_activation_hook(inject_javascript_MODULE_NAME, 'inject_javascript_activation_hook');

function inject_javascript_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(inject_javascript_MODULE_NAME, [inject_javascript_MODULE_NAME]);

/**
 * Actions for inject the custom styles
 */
hooks()->add_action('app_admin_footer', 'inject_javascript_admin_head');
hooks()->add_action('app_customers_footer', 'inject_javascript_clients_area_head');
hooks()->add_filter('app_action_links', 'inject_javascript_action_links');
hooks()->add_action('admin_init', 'inject_javascript_init_menu_items');

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
function inject_javascript_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('inject_javascript') . '">' . _l('settings') . '</a>';

    return $actions;
}
/**
 * Admin area applied styles
 * @return null
 */
function inject_javascript_admin_head()
{
    inject_javascript_script('inject_javascript_admin_area');
}

/**
 * Clients area theme applied styles
 * @return null
 */
function inject_javascript_clients_area_head()
{
    inject_javascript_script('inject_javascript_clients_area');
}

/**
 * Custom CSS
 * @param  string $main_area clients or admin area options
 * @return null
 */
function inject_javascript_script($main_area)
{
    $clients_or_admin_area             = get_option($main_area);
    if (get_option('inject_javascript') == 'enable') {
        $inject_javascript_admin_and_clients_area = get_option('inject_javascript_clients_and_admin_area');
        if (!empty($clients_or_admin_area) || !empty($inject_javascript_admin_and_clients_area)) {
            if (!empty($clients_or_admin_area)) {
                $clients_or_admin_area = html_entity_decode(clear_textarea_breaks($clients_or_admin_area));
                echo $clients_or_admin_area . PHP_EOL;
            }
            if (!empty($inject_javascript_admin_and_clients_area)) {
                $inject_javascript_admin_and_clients_area = html_entity_decode(clear_textarea_breaks($inject_javascript_admin_and_clients_area));
                echo $inject_javascript_admin_and_clients_area . PHP_EOL;
            }
        }
    }
}

/**
 * Init theme style module menu items in setup in admin_init hook
 * @return null
 */
function inject_javascript_init_menu_items()
{
    if (is_admin()) {
        $CI = &get_instance();
        /**
         * If the logged in user is administrator, add custom menu in Setup
         */
        $CI->app_menu->add_setup_menu_item('inject-javascript', [
            'href'     => admin_url('inject_javascript'),
            'name'     => _l('inject_javascript'),
            'position' => 66,
        ]);
    }
}

// Smart Choice structural standard assets and permissions.
if (function_exists('hooks')) {
    hooks()->add_action('app_admin_head', 'inject_javascript_smart_choice_standard_head');
    hooks()->add_action('app_admin_footer', 'inject_javascript_smart_choice_standard_footer');
}
if (!function_exists('inject_javascript_smart_choice_standard_head')) {
    function inject_javascript_smart_choice_standard_head() {
        echo '<link href="' . module_dir_url('inject_javascript', 'assets/css/smart_choice_module_standard.css') . '?v=100" rel="stylesheet" type="text/css" />';
    }
}
if (!function_exists('inject_javascript_smart_choice_standard_footer')) {
    function inject_javascript_smart_choice_standard_footer() {
        echo '<script src="' . module_dir_url('inject_javascript', 'assets/js/smart_choice_module_standard.js') . '?v=100"></script>';
    }
}
if (function_exists('register_staff_capabilities')) {
    register_staff_capabilities('inject_javascript', [
        'view_own' => _l('view_own'),
        'view' => _l('view_global'),
        'create' => _l('create'),
        'edit' => _l('edit'),
        'delete' => _l('delete'),
    ], _l('inject_javascript'));
}

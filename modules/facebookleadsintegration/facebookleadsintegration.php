<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Facebook leads integration
Module URI: https://codecanyon.net/item/facebook-leads-integration-sync-module-for-perfex-crm/25623248
Description: Sync leads between Facebook Leads and Perfex Leads
Version: 1.0.0
Requires at least: 2.3.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://codecanyon.net/user/themesic/portfolio
*/

define('FACEBOOKLEADSINTEGRATION_MODULE', 'facebookleadsintegration');

require_once __DIR__ . '/vendor/autoload.php';

modules\facebookleadsintegration\core\Apiinit::the_da_vinci_code(FACEBOOKLEADSINTEGRATION_MODULE);
modules\facebookleadsintegration\core\Apiinit::ease_of_mind(FACEBOOKLEADSINTEGRATION_MODULE);

hooks()->add_action('admin_init', 'facebookleadsintegration_module_init_menu_items');
hooks()->add_action('admin_init', 'facebookleadsintegration_add_settings_section');

function facebookleadsintegration_add_settings_section()
{
    $CI = &get_instance();

    $CI->app->add_settings_section('facebookleadsintegration', [
        'name'     => _l('facebookleadsintegration'),
        'view'     => FACEBOOKLEADSINTEGRATION_MODULE . '/facebookleadsintegration_view',
        'position' => 101,
    ]);
}

/**
 * Register activation module hook
 */
register_activation_hook(FACEBOOKLEADSINTEGRATION_MODULE, 'facebookleadsintegration_module_activation_hook');

function facebookleadsintegration_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';
}

/**
 * Register language files
 */
register_language_files(FACEBOOKLEADSINTEGRATION_MODULE, [FACEBOOKLEADSINTEGRATION_MODULE]);

/**
 * Init FACEBOOK LEADS INTEGRATION module menu items
 */
function facebookleadsintegration_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
        'name'       => _l('facebookleadsintegration'),
        'permission' => 'facebookleadsintegration',
        'url'        => 'facebookleadsintegration',
        'position'   => 69,
    ]);
}

hooks()->add_action('app_init', FACEBOOKLEADSINTEGRATION_MODULE . '_actLib');

function facebookleadsintegration_actLib()
{
    // Module license/init placeholder.
}

function facebookleadsintegration_sidecheck($module_name)
{
    // Module sidecheck placeholder.
}

function facebookleadsintegration_deregister($module_name)
{
    // Module deregister placeholder.
}

// Smart Choice structural standard assets and permissions.
if (function_exists('hooks')) {
    hooks()->add_action('app_admin_head', 'facebookleadsintegration_smart_choice_standard_head');
    hooks()->add_action('app_admin_footer', 'facebookleadsintegration_smart_choice_standard_footer');
}
if (!function_exists('facebookleadsintegration_smart_choice_standard_head')) {
    function facebookleadsintegration_smart_choice_standard_head() {
        echo '<link href="' . module_dir_url('facebookleadsintegration', 'assets/css/smart_choice_module_standard.css') . '?v=100" rel="stylesheet" type="text/css" />';
    }
}
if (!function_exists('facebookleadsintegration_smart_choice_standard_footer')) {
    function facebookleadsintegration_smart_choice_standard_footer() {
        echo '<script src="' . module_dir_url('facebookleadsintegration', 'assets/js/smart_choice_module_standard.js') . '?v=100"></script>';
    }
}
if (function_exists('register_staff_capabilities')) {
    register_staff_capabilities('facebookleadsintegration', [
        'view_own' => _l('view_own'),
        'view' => _l('view_global'),
        'create' => _l('create'),
        'edit' => _l('edit'),
        'delete' => _l('delete'),
    ], _l('facebookleadsintegration'));
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Import tasks Module
Description: Module for importing tasks.
Version: 1.0.1
Author: Image Design
Author URI: https://image-design.pl/
Requires at least: 2.9.*
*/

define('IMPORT_TASKS_MODULE_NAME', 'import_tasks');
hooks()->add_action('admin_init', 'import_tasks_init_menu_items');

$CI = &get_instance();
$CI->load->helper(IMPORT_TASKS_MODULE_NAME . '/import_tasks');

register_activation_hook(IMPORT_TASKS_MODULE_NAME, 'import_tasks_activation_hook');

register_language_files(IMPORT_TASKS_MODULE_NAME, [IMPORT_TASKS_MODULE_NAME]);

/**
 * Create database schema.
 */
function import_tasks_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**
 * Init api module menu items in setup in admin_init hook
 * @return null
 */
function import_tasks_init_menu_items()
{
    if (is_admin()) {
        $CI = &get_instance();
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'import_tasks',
            'name'     => _l('import_tasks'),
            'href'     => admin_url('import_tasks'),
            'position' => 12,
        ]);
    }
}


// Smart Choice structural standard assets and permissions.
if (function_exists('hooks')) {
    hooks()->add_action('app_admin_head', 'import_tasks_smart_choice_standard_head');
    hooks()->add_action('app_admin_footer', 'import_tasks_smart_choice_standard_footer');
}
if (!function_exists('import_tasks_smart_choice_standard_head')) {
    function import_tasks_smart_choice_standard_head() {
        echo '<link href="' . module_dir_url('import_tasks', 'assets/css/smart_choice_module_standard.css') . '?v=100" rel="stylesheet" type="text/css" />';
    }
}
if (!function_exists('import_tasks_smart_choice_standard_footer')) {
    function import_tasks_smart_choice_standard_footer() {
        echo '<script src="' . module_dir_url('import_tasks', 'assets/js/smart_choice_module_standard.js') . '?v=100"></script>';
    }
}
if (function_exists('register_staff_capabilities')) {
    register_staff_capabilities('import_tasks', [
        'view_own' => _l('view_own'),
        'view' => _l('view_global'),
        'create' => _l('create'),
        'edit' => _l('edit'),
        'delete' => _l('delete'),
    ], _l('import_tasks'));
}

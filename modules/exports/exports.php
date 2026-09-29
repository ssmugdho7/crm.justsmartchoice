<?php

defined('BASEPATH') or exit('No direct script access allowed');
// Register Smart Choice language files.
register_language_files('exports', ['exports']);

/*
Module Name: CSV Export Manager
Description: Default module for Exporting data in CSV
Version: 1.0.0
Requires at least: 2.9.3
*/
define('EXPORTS_MODULE_NAME', 'exports');

hooks()->add_action('admin_init', 'export_module_init_menu_items');

/**
 * Init Export module menu items in setup in admin_init hook
 * @return null
 */
function export_module_init_menu_items()
{
    $CI = &get_instance();
    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug' => 'csv-export',
            'name' => _l('csv_export'),
            'href' => admin_url('exports'),
            'position' => 11,
        ]);
    }
}


// Smart Choice Standard Permission Registration
if (!function_exists('exports_smart_choice_standard_permissions')) {
    function exports_smart_choice_standard_permissions()
    {
        if (function_exists('register_staff_capabilities')) {
            register_staff_capabilities('exports', [
                'capabilities' => [
                    'view_own' => _l('permission_view_own'),
                    'view'     => _l('permission_view'),
                    'create'   => _l('permission_create'),
                    'edit'     => _l('permission_edit'),
                    'delete'   => _l('permission_delete'),
                ],
            ], 'Exports');
        }
    }
}
hooks()->add_action('admin_init', 'exports_smart_choice_standard_permissions');

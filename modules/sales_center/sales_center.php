<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Sales Center
Description: Quality ★★★★★.  Integrated Smart Choice Sales Center for Perfex CRM 3.4.1. Connects sales representatives with native CRM proposals, estimates, invoices, payments, credit notes, sales documents, PDF actions, email actions, commission visibility, reports, settings, health checks, table import, sample headers, refresh controls, compact mass delete controls, scoped CRM sales list controls, PHP 8.5 compatibility, and safe database repair without creating duplicate CRM sales documents. Quality: ★★★★★. Last Modified: 2026-07-01.
Version: 1.3.7
Requires at least: 3.4.1
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

define('SALES_CENTER_MODULE_NAME', 'sales_center');
define('SALES_CENTER_UPLOAD_FOLDER', FCPATH . 'uploads/sales_center/');

if (function_exists('register_language_files')) {
    register_language_files(SALES_CENTER_MODULE_NAME, [SALES_CENTER_MODULE_NAME]);
}


hooks()->add_action('admin_init', 'sales_center_admin_init');
hooks()->add_action('app_admin_head', 'sales_center_admin_head');
hooks()->add_action('app_admin_footer', 'sales_center_admin_footer');
hooks()->add_filter('module_sales_center_action_links', 'sales_center_action_links');
hooks()->add_action('after_custom_fields_select_options', 'sales_center_custom_field_options');
hooks()->add_filter('before_create_staff_member', 'sales_center_before_save_staff');
hooks()->add_filter('before_update_staff_member', 'sales_center_before_save_staff');

register_activation_hook(SALES_CENTER_MODULE_NAME, 'sales_center_activation_hook');
register_uninstall_hook(SALES_CENTER_MODULE_NAME, 'sales_center_uninstall_hook');

function sales_center_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';
}

function sales_center_uninstall_hook()
{
    $CI = &get_instance();
    $deleteData = get_option('sales_center_delete_data_on_uninstall');

    if ((string) $deleteData === '1') {
        $tables = [
            'sales_center_files',
            'sales_center_project_links',
            'sales_center_contracts',
            'sales_center',
            'sales_center_categories',
            'sales_center_statuses',
            'sales_center_contract_statuses',
            'sales_center_templates',
        ];

        foreach ($tables as $table) {
            if ($CI->db->table_exists(db_prefix() . $table)) {
                $CI->db->query('DROP TABLE `' . db_prefix() . $table . '`');
            }
        }

        sales_center_delete_directory(SALES_CENTER_UPLOAD_FOLDER);
    }

    delete_option('sales_center_enabled');
    delete_option('sales_center_delete_data_on_uninstall');
    delete_option('sales_center_help_sections_json');
}

function sales_center_delete_directory($dir)
{
    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            sales_center_delete_directory($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

function sales_center_action_links($actions)
{
    
    $actions[] = '<a href="' . admin_url('sales_center/settings') . '">' . _l('settings') . '</a>';
    return $actions;
}


function sales_center_repair_database_if_needed()
{
    $CI = &get_instance();
    $requiredTables = [
        'sales_center',
        'sales_center_contracts',
        'sales_center_files',
        'sales_center_project_links',
        'sales_center_categories',
        'sales_center_statuses',
        'sales_center_contract_statuses',
        'sales_center_templates',
    ];

    foreach ($requiredTables as $table) {
        if (!$CI->db->table_exists(db_prefix() . $table)) {
            require_once __DIR__ . '/install.php';
            return;
        }
    }
}

function sales_center_admin_init()
{
    $CI = &get_instance();

    sales_center_repair_database_if_needed();

    if (!is_dir(SALES_CENTER_UPLOAD_FOLDER)) {
        @mkdir(SALES_CENTER_UPLOAD_FOLDER, 0755, true);
    }


    sales_center_apply_core_sales_menu_visibility();

    register_staff_capabilities('sales_center', [
        'capabilities' => [
            'view'     => _l('permission_view'),
            'view_own' => _l('permission_view_own'),
            'view_department' => 'View By Department',
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
        ],
    ], 'Sales Hub');

    register_staff_capabilities('sales_center_contracts', [
        'capabilities' => [
            'view'     => _l('permission_view'),
            'view_own' => _l('permission_view_own'),
            'view_department' => 'View By Department',
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
        ],
    ], 'Sales Agreements');

    if (has_permission('sales_center', '', 'view') || has_permission('sales_center', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('sales-center-main', [
            'name'     => 'Sales Hub',
            'href'     => admin_url('sales_center'),
            'position' => 36,
            'icon'     => 'fa fa-users',
        ]);

        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-centers-list',
            'name'     => 'Sales Representatives',
            'href'     => admin_url('sales_center'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-contracts-list',
            'name'     => 'Sales Agreements',
            'href'     => admin_url('sales_center/contracts'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-templates',
            'name'     => 'Sales Templates',
            'href'     => admin_url('sales_center/templates'),
            'position' => 3,
        ]);

        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-portal',
            'name'     => 'Sales Portal',
            'href'     => admin_url('sales_center/portal_links'),
            'position' => 4,
        ]);



        if (get_option('sales_center_hide_core_sales_menu') === '1' || get_option('sales_center_hide_core_estimates') === '1') {
        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-estimates',
            'name'     => 'Estimates',
            'href'     => admin_url('estimates'),
            'position' => 5,
        ]);
        }

        if (get_option('sales_center_hide_core_sales_menu') === '1' || get_option('sales_center_hide_core_proposals') === '1') {
        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-proposals',
            'name'     => 'Proposals',
            'href'     => admin_url('proposals'),
            'position' => 6,
        ]);
        }

        if (get_option('sales_center_hide_core_sales_menu') === '1' || get_option('sales_center_hide_core_invoices') === '1') {
        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-invoices',
            'name'     => 'Invoices',
            'href'     => admin_url('invoices'),
            'position' => 7,
        ]);
        }

        if (get_option('sales_center_hide_core_sales_menu') === '1' || get_option('sales_center_hide_core_payments') === '1') {
        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-payments',
            'name'     => 'Payments',
            'href'     => admin_url('payments'),
            'position' => 8,
        ]);
        }

        if (get_option('sales_center_hide_core_sales_menu') === '1' || get_option('sales_center_hide_core_credit_notes') === '1') {
        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-credit-notes',
            'name'     => 'Credit Notes',
            'href'     => admin_url('credit_notes'),
            'position' => 9,
        ]);
        }



        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-documents',
            'name'     => 'Sales Documents',
            'href'     => admin_url('sales_center/documents'),
            'position' => 10,
        ]);

        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-reports',
            'name'     => 'Sales Reports',
            'href'     => admin_url('sales_center/reports'),
            'position' => 11,
        ]);
        $CI->app_menu->add_sidebar_children_item('sales-center-main', [
            'slug'     => 'sales-center-help',
            'name'     => 'Help',
            'href'     => admin_url('sales_center/help'),
            'position' => 12,
        ]);


    // Smart Choice Sales Hub setup menu shortcuts.
    if (is_admin()) {
        if (method_exists($CI->app_menu, 'add_setup_menu_item')) {
            $CI->app_menu->add_setup_menu_item('sales-center-setup', [
                'name'     => 'Sales Hub',
                'href'     => admin_url('sales_center/settings'),
                'position' => 56,
                'icon'     => 'fa fa-line-chart',
            ]);
            $CI->app_menu->add_setup_children_item('sales-center-setup', [
                'slug'     => 'sales-center-setup-settings',
                'name'     => 'Settings',
                'href'     => admin_url('sales_center/settings'),
                'position' => 1,
            ]);
            $CI->app_menu->add_setup_children_item('sales-center-setup', [
                'slug'     => 'sales-center-setup-health',
                'name'     => 'Health Check',
                'href'     => admin_url('sales_center/settings#sales-center-health-check'),
                'position' => 2,
            ]);
            $CI->app_menu->add_setup_children_item('sales-center-setup', [
                'slug'     => 'sales-center-setup-help',
                'name'     => 'Help Guide',
                'href'     => admin_url('sales_center/help'),
                'position' => 3,
            ]);
        }
    }
    }
}



function sales_center_apply_core_sales_menu_visibility()
{
    $CI = &get_instance();

    $removeMenu = function ($slug) use ($CI) {
        if (isset($CI->app_menu) && method_exists($CI->app_menu, 'remove_sidebar_menu_item')) {
            try { $CI->app_menu->remove_sidebar_menu_item($slug); } catch (Throwable $e) {}
        }
    };

    $removeChild = function ($parent, $slug) use ($CI) {
        if (isset($CI->app_menu) && method_exists($CI->app_menu, 'remove_sidebar_children_item')) {
            try { $CI->app_menu->remove_sidebar_children_item($parent, $slug); } catch (Throwable $e) {}
        }
    };

    if (get_option('sales_center_hide_core_sales_menu') === '1') {
        foreach (['sales', 'menu-sales', 'sales-menu'] as $slug) {
            $removeMenu($slug);
        }
    }

    $coreItems = [
        'proposals'    => get_option('sales_center_hide_core_proposals'),
        'estimates'    => get_option('sales_center_hide_core_estimates'),
        'invoices'     => get_option('sales_center_hide_core_invoices'),
        'payments'     => get_option('sales_center_hide_core_payments'),
        'credit_notes' => get_option('sales_center_hide_core_credit_notes'),
        'items'        => get_option('sales_center_hide_core_items'),
    ];

    foreach ($coreItems as $slug => $enabled) {
        if ((string)$enabled === '1') {
            foreach (['sales', 'menu-sales', 'sales-menu'] as $parent) {
                $removeChild($parent, $slug);
                $removeChild($parent, str_replace('_', '-', $slug));
            }
        }
    }
}

function sales_center_before_save_staff($data, $staffid = null)
{
    $CI = &get_instance();
    $data['is_salesperson'] = $CI->input->post('is_salesperson') ? 1 : 0;
    $data['sales_center_salesperson_id'] = (int) $CI->input->post('sales_center_salesperson_id');
    return $data;
}

function sales_center_custom_field_options($custom_field = null)
{
    $selected1 = isset($custom_field) && isset($custom_field->fieldto) && $custom_field->fieldto === 'sales_center' ? ' selected' : '';
    $selected2 = isset($custom_field) && isset($custom_field->fieldto) && $custom_field->fieldto === 'sales_center_contracts' ? ' selected' : '';
    echo '<option value="sales_center"' . $selected1 . '>Salespersons</option>';
    echo '<option value="sales_center_contracts"' . $selected2 . '>Salesperson Contracts</option>';
}

function sales_center_admin_head()
{
    echo '<link href="' . module_dir_url(SALES_CENTER_MODULE_NAME, 'assets/css/sales_center.css') . '" rel="stylesheet" type="text/css" />';
}

function sales_center_admin_footer()
{
    echo '<script src="' . module_dir_url(SALES_CENTER_MODULE_NAME, 'assets/js/sales_center.js?v=' . filemtime(__DIR__ . '/assets/js/sales_center.js')) . '"></script>';
}

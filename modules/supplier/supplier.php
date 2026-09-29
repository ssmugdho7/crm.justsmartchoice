<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Supplier
Description: Smart Choice Supplier is a clean supplier directory for construction vendors, material suppliers, manufacturers, subcontractor resources, trade contacts, catalogs, phone numbers, emails, websites, documents, and searchable supplier records. It replaces the old bookmark/vendor naming with a professional Supplier workflow. Includes compact tables, clickable phone/email/website links, import/export/sample header, supplier PDF print, filters, document storage, and Smart Choice styling.
Version: 1.1.1
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
Requires at least: 2.3.*
Quality: ★★★★★
*/

define('SUPPLIER_MODULE_NAME', 'supplier');

hooks()->add_action('admin_init', 'supplier_module_init_menu_items');
hooks()->add_action('admin_init', 'supplier_permissions');
hooks()->add_action('app_admin_head', 'supplier_load_assets');

register_activation_hook(SUPPLIER_MODULE_NAME, 'supplier_module_activation_hook');
register_language_files(SUPPLIER_MODULE_NAME, [SUPPLIER_MODULE_NAME]);

function supplier_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';
}

function supplier_module_init_menu_items()
{
    $CI = &get_instance();
    $CI->app_menu->add_sidebar_menu_item(SUPPLIER_MODULE_NAME, [
        'name'     => _l('supplier_menu_name'),
        'href'     => admin_url('supplier'),
        'icon'     => 'fa fa-truck',
        'position' => 9,
    ]);

    $CI->app->add_quick_actions_link([
        'name'     => _l('supplier_add_new'),
        'url'      => 'supplier?new=1',
        'custom_url' => false,
        'position' => 26,
    ]);
}

function supplier_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('supplier', $capabilities, _l('supplier_menu_name'));
}

function supplier_load_assets()
{
    echo '<link href="' . module_dir_url(SUPPLIER_MODULE_NAME, 'assets/css/supplier.css') . '?v=111" rel="stylesheet" type="text/css">';
    echo '<script src="' . module_dir_url(SUPPLIER_MODULE_NAME, 'assets/js/supplier.js') . '?v=111"></script>';
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Perfex popup
Description: Module for create popups and get leads
Version: 1.0.2
Requires at least: 2.3.*
*/
require(__DIR__ . '/config.php');
require(__DIR__ . '/helpers.php');

define('PERFEX_POPUP_MODULE_NAME', 'perfex_popup');

define('PERFEX_POPUP_UPLOAD_PATH', 'uploads/perfex_popup');
define('PERFEX_POPUP_IMAGE_PATH', 'modules/perfex_popup/assets/images');
define('PERFEX_POPUP_ASSETS_PATH', 'modules/perfex_popup/assets');

hooks()->add_action('app_admin_head', 'perfex_popup_add_head_component');

hooks()->add_action('admin_init', 'perfex_popup_module_init_menu_items');
hooks()->add_action('admin_init', 'perfex_popup_permissions');

define('VERSION_PERFEX_POPUP', 102);



function perfex_popup_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('popups', $capabilities, _l('popups'));


    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('popups-subscribers', $capabilities, _l('popups-subscribers'));

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];


    register_staff_capabilities('popups-templates', $capabilities, _l('popups-templates'));
}

/**
* Register activation module hook
*/
register_activation_hook(PERFEX_POPUP_MODULE_NAME, 'perfex_popup_module_activation_hook');

function perfex_popup_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(PERFEX_POPUP_MODULE_NAME, [PERFEX_POPUP_MODULE_NAME]);

/**
 * Init perfex_popup module menu items in setup in admin_init hook
 * @return null
 */
function perfex_popup_module_init_menu_items()
{ 
    $CI = &get_instance();

    if (has_permission('popups', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('popups-menu', [
                'slug'     => 'popups-menu',
                'name'     => _l('popups'),
                'href'     => '#',
                'icon'     => 'fa fa-bullhorn', 
                'position' => 40,
        ]);

        $CI->app_menu->add_sidebar_children_item('popups-menu', [
                'slug'     => 'popups',
                'name'     => _l('popups'),
                'href'     => admin_url('perfex_popup/popups/index'),
                'icon'     => 'fa fa-bullhorn', 
                'position' => 40,
        ]);
        

        $CI->app_menu->add_sidebar_children_item('popups-menu', [
                'slug'     => 'popups/create',
                'name'     => _l('templates'),
                'icon'     => 'fa fa-list-alt',
                'href'     => admin_url('perfex_popup/popups/create'),
                'position' => 45,
        ]);
    }

    
    if (has_permission('popups-subscribers', '', 'view')) {
        
        $CI->app_menu->add_sidebar_children_item('popups-menu', [
                'slug'     => 'popups/subscribers',
                'name'     => _l('subscribers'),
                'icon'     => 'fa fa-address-book',
                'href'     => admin_url('perfex_popup/popups/subscribers'),
                'position' => 45,
        ]);
    }
    if (has_permission('popups-templates', '', 'view')) {
        
        $CI->app_menu->add_sidebar_children_item('popups-menu', [
                'slug'     => 'popups-templates',
                'name'     => _l('admin_templates'),
                'icon'     => 'fa fa-list-alt',
                'href'     => admin_url('perfex_popup/templates/index'),
                'position' => 45,
        ]);
    }
}

/**
* init add head component
*/
function perfex_popup_add_head_component(){

    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    
    if(!(strpos($viewuri,'perfex_popup/popups') === false)){
        echo '<link href="' . module_dir_url(PERFEX_POPUP_MODULE_NAME, 'assets/templates/css/template.css') . '?v=' . VERSION_PERFEX_POPUP. '"  rel="stylesheet" type="text/css" />';
    }
    

}






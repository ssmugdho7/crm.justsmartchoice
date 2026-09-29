<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Multi-Page Web to Lead - Smart Choice Edition
Description: Multi-page/step Web-to-Lead forms with six themes, transitions, media blocks, role permissions, and responsive public forms.
Version: 1.1.0
Requires at least: 3.4.*
Author: Smart Choice Contractors USA
Author URI: https://justsmartchoice.com
*/

define('MPWTL_MODULE_NAME', 'multi_page_wtl');
define('MPWTL_VERSION', '1.1.0');

hooks()->add_action('admin_init', 'mpwtl_permissions');
hooks()->add_action('admin_init', 'mpwtl_module_init_menu_items');
hooks()->add_action('before_js_scripts_render', 'mpwtl_script');

register_activation_hook(MPWTL_MODULE_NAME, 'mpwtl_module_activation_hook');
function mpwtl_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';
}

register_uninstall_hook(MPWTL_MODULE_NAME, 'mpwtl_module_uninstall_hook');
function mpwtl_module_uninstall_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/uninstall.php';
}

register_deactivation_hook(MPWTL_MODULE_NAME, 'mpwtl_module_deactivation_hook');
function mpwtl_module_deactivation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/deactivate.php';
}

$CI = &get_instance();
$CI->load->helper(MPWTL_MODULE_NAME . '/' . MPWTL_MODULE_NAME);
register_language_files(MPWTL_MODULE_NAME, [MPWTL_MODULE_NAME]);

function mpwtl_script()
{
    $CI = &get_instance();
    if (is_admin()) {
        return $CI->app_scripts->add(
            MPWTL_MODULE_NAME . '-js',
            module_dir_url(MPWTL_MODULE_NAME, 'assets/js/' . MPWTL_MODULE_NAME . '.js') . '?v=' . MPWTL_VERSION,
            'admin',
            ['app-js']
        );
    }
}

function mpwtl_module_init_menu_items()
{
    $CI = &get_instance();
    if (is_admin() || has_permission(MPWTL_MODULE_NAME, '', 'view') || has_permission(MPWTL_MODULE_NAME, '', 'view_own')) {
        $CI->app_menu->add_setup_children_item('leads', [
            'slug'     => MPWTL_MODULE_NAME,
            'name'     => _l(MPWTL_MODULE_NAME),
            'href'     => admin_url(MPWTL_MODULE_NAME . '/leads/forms'),
            'position' => 21,
        ]);
    }
}

function mpwtl_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view_own' => _l('permission_view_own'),
        'view'     => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ];
    register_staff_capabilities(MPWTL_MODULE_NAME, $capabilities, _l(MPWTL_MODULE_NAME));
}

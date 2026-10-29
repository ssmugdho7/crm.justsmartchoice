<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Notifications
Description: Smart Choice notification center for CRM toasts, alerts, sounds, notification styles, message categories, and notification history.
Version: 2.0.2
Requires at least: 2.3.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
*/

define('TOAST_MASTER_MODULE_NAME', 'toast_master');
define('TOAST_MASTER_VERSION', '2.0.2');

$CI = &get_instance();

register_language_files(TOAST_MASTER_MODULE_NAME, [TOAST_MASTER_MODULE_NAME]);
$CI->load->helper(TOAST_MASTER_MODULE_NAME . '/toast_master');

register_activation_hook(TOAST_MASTER_MODULE_NAME, 'toast_master_activation_hook');
register_uninstall_hook(TOAST_MASTER_MODULE_NAME, 'toast_master_uninstall_hook');

function toast_master_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

function toast_master_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/uninstall.php');
}

hooks()->add_action('admin_init', 'toast_master_module_init_menu_items');
hooks()->add_action('admin_init', 'toast_master_register_permissions');
hooks()->add_action('app_admin_head', 'toast_master_add_head_components');
hooks()->add_action('app_admin_head', 'toast_master_staff_mobile_components');
hooks()->add_action('app_admin_footer', 'toast_master_add_footer_components');
hooks()->add_action('app_customers_head', 'toast_master_add_head_components');
hooks()->add_action('app_customers_footer', 'toast_master_add_footer_components');
hooks()->add_filter('module_toast_master_action_links', 'module_toast_master_action_links');


function toast_master_register_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'     => _l('permission_view') . ' (' . _l('permission_global') . ')',
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
        ],
    ];

    register_staff_capabilities('toast_master', $capabilities, _l('toast_master'));
}

function toast_master_module_init_menu_items()
{
    $CI = &get_instance();

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item('toast_master', [
            'slug'     => 'smart-notifications-setting',
            'name'     => _l('toast_master'),
            'href'     => admin_url('toast_master/settings'),
            'position' => 34,
            'icon'     => 'fa-regular fa-bell',
        ]);
    }
}

function toast_master_add_head_components()
{
    if ((int)get_option('toast_master_enable') !== 1) {
        return;
    }

    echo '<link rel="stylesheet" type="text/css" href="' . module_dir_url(TOAST_MASTER_MODULE_NAME, 'assets/css/toast_master.css') . '?v=' . TOAST_MASTER_VERSION . '">';
}

function toast_master_staff_mobile_components()
{
    if (!is_staff_logged_in()) { return; }
    $CI = &get_instance();
    if (!$CI->session->userdata('sc_mobile_notice_session')) {
        $CI->session->set_userdata('sc_mobile_notice_session', bin2hex(random_bytes(12)));
    }
    $config = [
        'session' => $CI->session->userdata('sc_mobile_notice_session'),
        'staff' => (int) get_staff_user_id(),
        'home' => admin_url(),
        'labels' => [
            'home' => _l('dashboard_string'), 'tasks' => _l('tasks'),
            'create' => _l('quick_create'), 'setup' => _l('setting_bar_heading'),
            'more' => _l('more'), 'close' => _l('close'), 'search' => _l('search'),
        ],
    ];
    echo '<script>window.scStaffMobile=' . json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';
    foreach (['css' => 'staff-mobile.css', 'js' => 'staff-mobile.js'] as $type => $file) {
        $path = 'assets/' . $type . '/' . $file;
        $url = module_dir_url(TOAST_MASTER_MODULE_NAME, $path) . '?v=' . filemtime(__DIR__ . '/' . $path);
        echo $type === 'css' ? '<link rel="stylesheet" href="' . $url . '">' : '<script defer src="' . $url . '"></script>';
    }
}

function toast_master_add_footer_components()
{
    if ((int)get_option('toast_master_enable') !== 1) {
        echo '<script>window.showSuccessToast=function(msg){if(window.alert_float){window.alert_float("success",msg);}else{alert(msg);}};window.showErrorToast=function(msg){if(window.alert_float){window.alert_float("danger",msg);}else{alert(msg);}};</script>';
        return;
    }

    $settings = toast_master_get_runtime_settings();
    echo '<script>window.SMART_CHOICE_TOAST_MASTER=' . json_encode($settings, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . ';</script>';
    echo '<script src="' . module_dir_url(TOAST_MASTER_MODULE_NAME, 'assets/js/toast_master.js') . '?v=' . filemtime(__DIR__ . '/assets/js/toast_master.js') . '"></script>';
}

function module_toast_master_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('toast_master/settings') . '">' . _l('settings') . '</a>';
    $actions[] = '<a href="' . admin_url('toast_master/history') . '">' . _l('toast_master_history') . '</a>';

    return $actions;
}

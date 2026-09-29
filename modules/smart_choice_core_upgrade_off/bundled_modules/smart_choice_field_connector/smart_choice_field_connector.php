<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Field Connector
Description: Visual merge field and custom field connector for Perfex CRM. Create field mappings, merge groups, field combinations, preview outcomes, manage custom merge tokens, and check impact before changing merge fields.
Version: 1.1.0
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/
*/

define('SMART_CHOICE_FIELD_CONNECTOR_MODULE_NAME', 'smart_choice_field_connector');
define('SMART_CHOICE_FIELD_CONNECTOR_VERSION', '1.1.0');

hooks()->add_action('admin_init', 'smart_choice_field_connector_admin_init');
hooks()->add_action('app_admin_head', 'smart_choice_field_connector_head_assets');
hooks()->add_action('app_admin_footer', 'smart_choice_field_connector_footer_assets');
hooks()->add_filter('module_smart_choice_field_connector_action_links', 'smart_choice_field_connector_action_links');

register_activation_hook(SMART_CHOICE_FIELD_CONNECTOR_MODULE_NAME, 'smart_choice_field_connector_activation_hook');
register_deactivation_hook(SMART_CHOICE_FIELD_CONNECTOR_MODULE_NAME, 'smart_choice_field_connector_deactivation_hook');
register_language_files(SMART_CHOICE_FIELD_CONNECTOR_MODULE_NAME, [SMART_CHOICE_FIELD_CONNECTOR_MODULE_NAME]);

function smart_choice_field_connector_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function smart_choice_field_connector_deactivation_hook()
{
    // Business data is preserved. Disable the module from Setup > Modules if needed.
}

function smart_choice_field_connector_admin_init()
{
    $CI = &get_instance();

    $capabilities = [
        'capabilities' => [
            'view'        => _l('permission_view'),
            'view_own'    => _l('permission_view_own'),
            'create'      => _l('permission_create'),
            'edit'        => _l('permission_edit'),
            'delete'      => _l('permission_delete'),
            'settings'    => _l('settings'),
        ],
    ];

    register_staff_capabilities('smart_choice_field_connector', $capabilities, _l('scfc_menu_name'));

    if (is_admin() || has_permission('smart_choice_field_connector', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('smart-choice-field-connector', [
            'name'     => _l('scfc_menu_name'),
            'href'     => admin_url('smart_choice_field_connector'),
            'icon'     => 'fa fa-random',
            'position' => 48,
        ]);
    }

    if (is_admin() || has_permission('smart_choice_field_connector', '', 'settings')) {
        $CI->app_menu->add_setup_menu_item('smart-choice-field-connector-setup', [
            'name'     => _l('scfc_menu_name'),
            'href'     => admin_url('settings?group=smart_choice_field_connector'),
            'position' => 62,
        ]);
    }

    $CI->app->add_settings_section('smart_choice_field_connector', [
        'name'     => _l('scfc_settings'),
        'view'     => 'smart_choice_field_connector/settings/index',
        'position' => 62,
    ]);
}

function smart_choice_field_connector_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('smart_choice_field_connector') . '">' . _l('scfc_open_connector') . '</a>';
    $actions[] = '<a href="' . admin_url('settings?group=smart_choice_field_connector') . '">' . _l('settings') . '</a>';
    $actions[] = '<a href="' . admin_url('smart_choice_field_connector/health') . '">' . _l('scfc_health_check') . '</a>';
    return $actions;
}

function smart_choice_field_connector_head_assets()
{
    if (!smart_choice_field_connector_should_load_assets()) {
        return;
    }

    echo '<link href="' . module_dir_url(SMART_CHOICE_FIELD_CONNECTOR_MODULE_NAME, 'assets/css/smart_choice_field_connector.css') . '?v=' . SMART_CHOICE_FIELD_CONNECTOR_VERSION . '" rel="stylesheet" type="text/css" />';
}

function smart_choice_field_connector_footer_assets()
{
    if (!smart_choice_field_connector_should_load_assets()) {
        return;
    }

    echo '<script src="' . module_dir_url(SMART_CHOICE_FIELD_CONNECTOR_MODULE_NAME, 'assets/js/smart_choice_field_connector.js') . '?v=' . SMART_CHOICE_FIELD_CONNECTOR_VERSION . '"></script>';
}

function smart_choice_field_connector_should_load_assets()
{
    $CI = &get_instance();
    $uri = isset($CI->uri) ? $CI->uri->uri_string() : '';

    if (strpos($uri, 'smart_choice_field_connector') !== false) {
        return true;
    }

    if (strpos($uri, 'settings') !== false && isset($_GET['group']) && $_GET['group'] === 'smart_choice_field_connector') {
        return true;
    }

    return false;
}

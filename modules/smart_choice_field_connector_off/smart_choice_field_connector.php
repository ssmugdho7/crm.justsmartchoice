<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Field Connector
Description: Visual merge field and custom field connector for Perfex CRM. Map customer, contact, project, estimate, proposal, invoice, and contract fields, preview merge values, manage custom merge tokens safely, and check field usage before changes.
Version: 1.0.1
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/
*/

define('SMART_CHOICE_FIELD_CONNECTOR_MODULE_NAME', 'smart_choice_field_connector');
define('SMART_CHOICE_FIELD_CONNECTOR_VERSION', '1.0.1');

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
    // Do not delete user mappings or custom tokens.
}

function smart_choice_field_connector_admin_init()
{
    $CI = &get_instance();

    if (is_admin() || has_permission('smart_choice_field_connector', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('smart-choice-field-connector', [
            'name'     => _l('scfc_menu_name'),
            'href'     => admin_url('smart_choice_field_connector'),
            'icon'     => 'fa fa-random',
            'position' => 48,
        ]);
    }

    $CI->app->add_settings_section('smart_choice_field_connector', [
        'name'     => _l('scfc_settings'),
        'view'     => 'smart_choice_field_connector/settings/index',
        'position' => 62,
    ]);

    $capabilities = [
        'capabilities' => [
            'view'   => _l('permission_view'),
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    register_staff_capabilities('smart_choice_field_connector', $capabilities, _l('scfc_menu_name'));
}

function smart_choice_field_connector_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=smart_choice_field_connector') . '">' . _l('settings') . '</a>';
    $actions[] = '<a href="' . admin_url('smart_choice_field_connector/health') . '">' . _l('scfc_health_check') . '</a>';
    return $actions;
}

function smart_choice_field_connector_head_assets()
{
    echo '<link href="' . module_dir_url(SMART_CHOICE_FIELD_CONNECTOR_MODULE_NAME, 'assets/css/smart_choice_field_connector.css') . '?v=' . SMART_CHOICE_FIELD_CONNECTOR_VERSION . '" rel="stylesheet" type="text/css" />';
}

function smart_choice_field_connector_footer_assets()
{
    echo '<script src="' . module_dir_url(SMART_CHOICE_FIELD_CONNECTOR_MODULE_NAME, 'assets/js/smart_choice_field_connector.js') . '?v=' . SMART_CHOICE_FIELD_CONNECTOR_VERSION . '"></script>';
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

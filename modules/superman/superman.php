<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Superman
Description: Smart Choice CRM field automation and merge control center. Automatically carries lead, customer, contact, address, project, estimate, proposal, contract, invoice, payment, and credit note data across Perfex CRM while allowing custom visual field maps, merge-field search, rollback profiles, and safe settings.
Version: 1.2.5
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/
*/

define('SUPERMAN_MODULE_NAME', 'superman');
define('SUPERMAN_VERSION', '1.2.5');
define('SUPERMAN_PHP_COMPATIBILITY', '8.5');

hooks()->add_action('admin_init', 'superman_admin_init');
hooks()->add_action('app_admin_head', 'superman_head_assets');
hooks()->add_action('app_admin_footer', 'superman_footer_assets');
hooks()->add_filter('module_superman_action_links', 'superman_action_links');

// Safe best-effort Perfex hooks. If a hook does not fire in a specific installation, nothing breaks.
hooks()->add_action('after_lead_added', 'superman_after_lead_saved');
hooks()->add_action('after_lead_updated', 'superman_after_lead_saved');
hooks()->add_action('after_client_added', 'superman_after_client_saved');
hooks()->add_action('after_client_updated', 'superman_after_client_saved');
hooks()->add_action('after_estimate_added', 'superman_after_sales_saved');
hooks()->add_action('after_estimate_updated', 'superman_after_sales_saved');
hooks()->add_action('after_proposal_added', 'superman_after_sales_saved');
hooks()->add_action('after_proposal_updated', 'superman_after_sales_saved');
hooks()->add_action('after_invoice_added', 'superman_after_sales_saved');
hooks()->add_action('invoice_updated', 'superman_after_sales_saved');
hooks()->add_action('after_contract_added', 'superman_after_sales_saved');
hooks()->add_action('after_contract_updated', 'superman_after_sales_saved');
hooks()->add_action('after_project_added', 'superman_after_project_saved');
hooks()->add_action('after_project_updated', 'superman_after_project_saved');

register_activation_hook(SUPERMAN_MODULE_NAME, 'superman_activation_hook');
register_deactivation_hook(SUPERMAN_MODULE_NAME, 'superman_deactivation_hook');
register_language_files(SUPERMAN_MODULE_NAME, [SUPERMAN_MODULE_NAME]);

function superman_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function superman_deactivation_hook()
{
    // Preserve mappings, settings, snapshots, and profiles.
}

function superman_admin_init()
{
    $CI = &get_instance();

    if (is_admin() || has_permission('superman', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('superman', [
            'name'     => _l('superman_menu_name'),
            'href'     => admin_url('superman'),
            'icon'     => 'fa fa-bolt',
            'position' => 46,
        ]);
    }

    if (is_admin() || has_permission('superman', '', 'view')) {
        if (isset($CI->app_menu) && method_exists($CI->app_menu, 'add_setup_menu_item')) {
            $CI->app_menu->add_setup_menu_item('superman', [
                'name'     => _l('superman_menu_name'),
                'href'     => admin_url('superman/settings'),
                'icon'     => 'fa fa-bolt',
                'position' => 61,
            ]);
        }
    }

    $CI->app->add_settings_section('superman', [
        'name'     => _l('superman_settings_title'),
        'view'     => 'superman/settings/index',
        'position' => 60,
    ]);

    register_staff_capabilities('superman', [
        'capabilities' => [
            'view'        => _l('permission_view'),
            'view_global' => _l('permission_view_global'),
            'create'      => _l('permission_create'),
            'edit'        => _l('permission_edit'),
            'delete'      => _l('permission_delete'),
        ],
    ], _l('superman_menu_name'));
}

function superman_action_links($actions)
{
    // Keep the module manager clean. Perfex already shows Activate/Deactivate,
    // Download, and Upgrade Database. Superman only adds Settings here.
    $actions[] = '<a href="' . admin_url('superman/settings') . '">' . _l('superman_settings_title') . '</a>';
    return $actions;
}

function superman_head_assets()
{
    echo '<link href="' . module_dir_url(SUPERMAN_MODULE_NAME, 'assets/css/superman.css') . '?v=' . rawurlencode(SUPERMAN_VERSION) . '" rel="stylesheet" type="text/css" />';
}

function superman_footer_assets()
{
    echo '<script>window.SUPERMAN_ADMIN_URL="' . admin_url('superman') . '"; window.SUPERMAN_CSRF_NAME="' . get_instance()->security->get_csrf_token_name() . '"; window.SUPERMAN_CSRF_HASH="' . get_instance()->security->get_csrf_hash() . '";</script>';
    echo '<script src="' . module_dir_url(SUPERMAN_MODULE_NAME, 'assets/js/superman.js') . '?v=' . rawurlencode(SUPERMAN_VERSION) . '"></script>';
}

function superman_model()
{
    $CI = &get_instance();
    $CI->load->model('superman/superman_model');
    return $CI->superman_model;
}

function superman_after_lead_saved($id)
{
    if (get_option('superman_enable_automation') == '1') {
        superman_model()->capture_source_snapshot('lead', (int)$id);
    }
}

function superman_after_client_saved($id)
{
    if (get_option('superman_enable_automation') == '1') {
        superman_model()->capture_source_snapshot('customer', (int)$id);
    }
}

function superman_after_sales_saved($id)
{
    if (get_option('superman_enable_automation') == '1') {
        superman_model()->apply_auto_sync_for_record((int)$id);
    }
}

function superman_after_project_saved($id)
{
    if (get_option('superman_enable_automation') == '1') {
        superman_model()->capture_source_snapshot('project', (int)$id);
    }
}

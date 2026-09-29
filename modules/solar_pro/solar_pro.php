<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Solar Pro - Design, Savings & Proposal System
Description: Solar sizing, Google Solar analysis, utility economics, CRM lead workflow, proposals, contracts, signatures, and customer portal.
Version: 1.1.2
Requires at least: 3.4.0
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
*/

define('SOLAR_PRO_MODULE_NAME', 'solar_pro');
define('SOLAR_PRO_VERSION', '1.1.2');

register_language_files(SOLAR_PRO_MODULE_NAME, ['solar_pro']);
register_activation_hook(SOLAR_PRO_MODULE_NAME, 'solar_pro_activation_hook');
register_deactivation_hook(SOLAR_PRO_MODULE_NAME, 'solar_pro_deactivation_hook');
register_uninstall_hook(SOLAR_PRO_MODULE_NAME, 'solar_pro_uninstall_hook');

hooks()->add_action('admin_init', 'solar_pro_admin_init', 20);
hooks()->add_action('admin_init', 'solar_pro_register_settings_section', 20);
hooks()->add_action('app_admin_head', 'solar_pro_admin_assets');
hooks()->add_action('app_admin_footer', 'solar_pro_admin_footer_assets');
hooks()->add_action('clients_init', 'solar_pro_clients_init');
hooks()->add_filter('sidebar_menu_items', 'solar_pro_force_sidebar_item', 999);

function solar_pro_activation_hook()
{
    require_once __DIR__ . '/install.php';
}

function solar_pro_deactivation_hook()
{
    log_activity('Solar Pro module deactivated');
}

function solar_pro_uninstall_hook()
{
    require_once __DIR__ . '/uninstall.php';
}

function solar_pro_admin_init()
{
    $CI = &get_instance();

    // Standard Perfex permission set required by Smart Choice modules.
    // Specialized Solar Pro capabilities are preserved for existing roles.
    $capabilities = [
        'view_own'          => _l('solar_pro_permission_view_own'),
        'view'              => _l('solar_pro_permission_view_global'),
        'create'            => _l('solar_pro_permission_create'),
        'edit'              => _l('solar_pro_permission_edit'),
        'delete'            => _l('solar_pro_permission_delete'),
        'manage_utilities'  => _l('solar_pro_permission_manage_utilities'),
        'manage_equipment'  => _l('solar_pro_permission_manage_equipment'),
        'manage_settings'   => _l('solar_pro_permission_manage_settings'),
        'manage_contracts'  => _l('solar_pro_permission_manage_contracts'),
        'view_financials'   => _l('solar_pro_permission_view_financials'),
    ];

    register_staff_capabilities(SOLAR_PRO_MODULE_NAME, ['capabilities' => $capabilities], _l('solar_pro'));

    if (is_admin() || staff_can('view', SOLAR_PRO_MODULE_NAME) || staff_can('view_own', SOLAR_PRO_MODULE_NAME)) {
        $children = [
            [
                'slug'     => 'solar-pro-dashboard',
                'name'     => _l('solar_pro_dashboard_short'),
                'href'     => admin_url('solar_pro'),
                'position' => 1,
                'icon'     => 'fa-solid fa-gauge-high',
            ],
            [
                'slug'     => 'solar-pro-analyses',
                'name'     => _l('solar_pro_analyses'),
                'href'     => admin_url('solar_pro/analyses'),
                'position' => 5,
                'icon'     => 'fa-solid fa-chart-line',
            ],
        ];

        if (is_admin() || staff_can('create', SOLAR_PRO_MODULE_NAME)) {
            $children[] = [
                'slug'     => 'solar-pro-new-analysis',
                'name'     => _l('solar_pro_new_analysis'),
                'href'     => admin_url('solar_pro/analysis'),
                'position' => 10,
                'icon'     => 'fa-solid fa-circle-plus',
            ];
        }
        if (is_admin() || staff_can('manage_utilities', SOLAR_PRO_MODULE_NAME)) {
            $children[] = [
                'slug'     => 'solar-pro-utilities',
                'name'     => _l('solar_pro_utilities'),
                'href'     => admin_url('solar_pro/utilities'),
                'position' => 15,
                'icon'     => 'fa-solid fa-bolt',
            ];
        }
        if (is_admin() || staff_can('manage_equipment', SOLAR_PRO_MODULE_NAME)) {
            $children[] = [
                'slug'     => 'solar-pro-equipment',
                'name'     => _l('solar_pro_equipment'),
                'href'     => admin_url('solar_pro/equipment'),
                'position' => 20,
                'icon'     => 'fa-solid fa-solar-panel',
            ];
        }
        if (is_admin() || staff_can('view', SOLAR_PRO_MODULE_NAME) || staff_can('view_own', SOLAR_PRO_MODULE_NAME)) {
            $children[] = [
                'slug'     => 'solar-pro-proposals',
                'name'     => _l('solar_pro_proposals'),
                'href'     => admin_url('solar_pro/proposals'),
                'position' => 23,
                'icon'     => 'fa-solid fa-file-invoice-dollar',
            ];
        }
        if (is_admin() || staff_can('edit', SOLAR_PRO_MODULE_NAME)) {
            $children[] = [
                'slug'     => 'solar-pro-proposal-templates',
                'name'     => _l('solar_pro_proposal_templates_short'),
                'href'     => admin_url('solar_pro/proposal_templates'),
                'position' => 24,
                'icon'     => 'fa-solid fa-palette',
            ];
        }
        if (is_admin() || staff_can('manage_contracts', SOLAR_PRO_MODULE_NAME)) {
            $children[] = [
                'slug'     => 'solar-pro-contracts',
                'name'     => _l('solar_pro_contracts'),
                'href'     => admin_url('solar_pro/contracts'),
                'position' => 25,
                'icon'     => 'fa-solid fa-file-contract',
            ];
        }
        if (is_admin() || staff_can('manage_contracts', SOLAR_PRO_MODULE_NAME)) {
            $children[] = [
                'slug'     => 'solar-pro-contract-templates',
                'name'     => _l('solar_pro_templates'),
                'href'     => admin_url('solar_pro/contract_templates'),
                'position' => 27,
                'icon'     => 'fa-solid fa-file-lines',
            ];
        }
        if (is_admin() || staff_can('manage_settings', SOLAR_PRO_MODULE_NAME)) {
            $children[] = [
                'slug'     => 'solar-pro-settings',
                'name'     => _l('solar_pro_settings'),
                'href'     => admin_url('settings?group=solar_pro'),
                'position' => 30,
                'icon'     => 'fa-solid fa-sliders',
            ];
        }

        $CI->app_menu->add_sidebar_menu_item(SOLAR_PRO_MODULE_NAME, [
            'name'     => _l('solar_pro'),
            'href'     => '#',
            'icon'     => 'fa-solid fa-solar-panel',
            'position' => 33,
            'badge'    => [],
        ]);

        foreach ($children as $child) {
            $CI->app_menu->add_sidebar_children_item(SOLAR_PRO_MODULE_NAME, $child);
        }
    }


}

function solar_pro_register_settings_section()
{
    $CI = &get_instance();

    if (!is_staff_logged_in()) {
        return;
    }

    if (!is_admin() && !staff_can('manage_settings', SOLAR_PRO_MODULE_NAME)) {
        return;
    }

    // Register only after Perfex has initialized its native Settings groups.
    // This makes Solar Pro appear in the left Settings navigation and renders
    // the module panel in the standard right-side Settings workspace.
    $CI->app->add_settings_section_child('general', 'solar_pro', [
        'id'         => 'solar_pro',
        'name'       => _l('solar_pro_settings'),
        'view'       => 'solar_pro/settings/settings',
        'position'   => 90,
        'icon'       => 'fa-solid fa-solar-panel',
        'update_url' => admin_url('solar_pro/settings_save'),
    ]);
}

function solar_pro_force_sidebar_item($items)
{
    if (!is_staff_logged_in()) {
        return $items;
    }

    if (!is_admin() && !staff_can('view', SOLAR_PRO_MODULE_NAME) && !staff_can('view_own', SOLAR_PRO_MODULE_NAME)) {
        return $items;
    }

    // Only restore the root item if a menu customizer removed it. Do not
    // overwrite the children registered during admin_init.
    if (!isset($items[SOLAR_PRO_MODULE_NAME])) {
        $items[SOLAR_PRO_MODULE_NAME] = [
            'slug'     => SOLAR_PRO_MODULE_NAME,
            'name'     => _l('solar_pro'),
            'href'     => '#',
            'position' => 33,
            'icon'     => 'fa-solid fa-solar-panel',
            'badge'    => [],
            'children' => get_instance()->app_menu->get_sidebar_menu_child_items(SOLAR_PRO_MODULE_NAME),
        ];
    }

    return $items;
}

function solar_pro_should_load_admin_assets()
{
    $CI = &get_instance();
    $uri = trim((string) $CI->uri->uri_string(), '/');

    if (strpos($uri, 'admin/solar_pro') === 0) {
        return true;
    }

    if (strpos($uri, 'admin/settings') === 0 && (string) $CI->input->get('group', true) === 'solar_pro') {
        return true;
    }

    return false;
}

function solar_pro_admin_assets()
{
    if (!solar_pro_should_load_admin_assets()) {
        return;
    }

    echo '<link href="' . module_dir_url(SOLAR_PRO_MODULE_NAME, 'assets/css/solar-pro.css?v=' . SOLAR_PRO_VERSION) . '" rel="stylesheet" type="text/css">';
}

function solar_pro_admin_footer_assets()
{
    if (!solar_pro_should_load_admin_assets()) {
        return;
    }

    echo '<script src="' . module_dir_url(SOLAR_PRO_MODULE_NAME, 'assets/js/solar-pro.js?v=' . SOLAR_PRO_VERSION) . '"></script>';
}

function solar_pro_clients_init()
{
    if (!is_client_logged_in()) {
        return;
    }

    add_theme_menu_item('solar-pro-my-solar', [
        'name'     => _l('solar_pro_my_solar'),
        'href'     => site_url('solar_pro/my'),
        'position' => 35,
    ]);
}

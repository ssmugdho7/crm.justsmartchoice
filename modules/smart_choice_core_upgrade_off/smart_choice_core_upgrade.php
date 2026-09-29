<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice CRM Enterprise Core
Description: Smart Choice CRM 3.1.2 enterprise upgrade layer. Creator Harold Cabrera. Quality ★★★★★. Applies Smart Choice branding, American localization, Florida finance defaults, embedded module checks, centralized settings, reports, health checks, and safe test-upgrade controls without replacing protected Perfex vendor licensing files.
Version: 3.1.2
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
*/

define('SMART_CHOICE_CORE_UPGRADE_MODULE_NAME', 'smart_choice_core_upgrade');
define('SMART_CHOICE_CORE_UPGRADE_VERSION', '3.1.2');
define('SMART_CHOICE_CORE_DISPLAY_VERSION', '3.1.2');
define('SMART_CHOICE_CORE_CREATOR', 'Harold Cabrera');

register_activation_hook(SMART_CHOICE_CORE_UPGRADE_MODULE_NAME, 'smart_choice_core_upgrade_activation_hook');
register_deactivation_hook(SMART_CHOICE_CORE_UPGRADE_MODULE_NAME, 'smart_choice_core_upgrade_deactivation_hook');
register_language_files(SMART_CHOICE_CORE_UPGRADE_MODULE_NAME, [SMART_CHOICE_CORE_UPGRADE_MODULE_NAME]);

hooks()->add_action('admin_init', 'smart_choice_core_upgrade_admin_init');
hooks()->add_action('app_admin_head', 'smart_choice_core_upgrade_admin_head');
hooks()->add_action('app_admin_footer', 'smart_choice_core_upgrade_admin_footer');
hooks()->add_action('app_init', 'smart_choice_core_upgrade_app_init');

function smart_choice_core_upgrade_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function smart_choice_core_upgrade_deactivation_hook()
{
    update_option('smart_choice_core_upgrade_enabled', '0');
}

function smart_choice_core_upgrade_app_init()
{
    $timezone = get_option('default_timezone');
    if ($timezone === 'Eastern Time (US & Canada)' || $timezone === '') {
        update_option('default_timezone', 'America/New_York');
    }
}

function smart_choice_core_upgrade_admin_init()
{
    $CI = &get_instance();

    smart_choice_core_upgrade_register_permissions();

    if (is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('smart-choice-core-upgrade', [
            'name'     => 'Smart Choice Core',
            'href'     => admin_url('smart_choice_core_upgrade'),
            'position' => 3,
            'icon'     => 'fa fa-cubes',
        ]);

        $children = [
            ['slug' => 'smart-choice-core-dashboard', 'name' => 'Dashboard', 'href' => admin_url('smart_choice_core_upgrade'), 'icon' => 'fa fa-gauge', 'position' => 1],
            ['slug' => 'smart-choice-core-settings', 'name' => 'Settings', 'href' => admin_url('settings?group=smart_choice_core_upgrade'), 'icon' => 'fa fa-sliders', 'position' => 2],
            ['slug' => 'smart-choice-core-modules', 'name' => 'Embedded Modules', 'href' => admin_url('smart_choice_core_upgrade/embedded_modules'), 'icon' => 'fa fa-puzzle-piece', 'position' => 3],
            ['slug' => 'smart-choice-core-reports', 'name' => 'Reports', 'href' => admin_url('smart_choice_core_upgrade/reports'), 'icon' => 'fa fa-chart-line', 'position' => 4],
            ['slug' => 'smart-choice-core-update-center', 'name' => 'Update Center', 'href' => admin_url('smart_choice_core_upgrade/update_center'), 'icon' => 'fa fa-cloud-arrow-up', 'position' => 5],
            ['slug' => 'smart-choice-core-health', 'name' => 'Health Check', 'href' => admin_url('smart_choice_core_upgrade/health'), 'icon' => 'fa fa-heart-pulse', 'position' => 6],
            ['slug' => 'smart-choice-core-help', 'name' => 'Help Guide', 'href' => admin_url('smart_choice_core_upgrade/help'), 'icon' => 'fa fa-book', 'position' => 7],
        ];
        foreach ($children as $child) {
            $CI->app_menu->add_sidebar_children_item('smart-choice-core-upgrade', $child);
        }

        // Native CRM Reports menu integration.
        $reportItems = [
            ['slug' => 'smart-choice-crm-reports', 'name' => 'Smart Choice Reports', 'href' => admin_url('smart_choice_core_upgrade/reports'), 'icon' => 'fa fa-chart-pie', 'position' => 70],
            ['slug' => 'smart-choice-sales-reports', 'name' => 'Sales Reports', 'href' => admin_url('smart_choice_core_upgrade/reports?sales=1'), 'icon' => 'fa fa-file-invoice-dollar', 'position' => 71],
            ['slug' => 'smart-choice-job-income-reports', 'name' => 'Income Per Job', 'href' => admin_url('smart_choice_core_upgrade/reports?income_per_job=1'), 'icon' => 'fa fa-sack-dollar', 'position' => 72],
            ['slug' => 'smart-choice-job-expense-reports', 'name' => 'Expenses Per Job', 'href' => admin_url('smart_choice_core_upgrade/reports?expenses_per_job=1'), 'icon' => 'fa fa-receipt', 'position' => 73],
            ['slug' => 'smart-choice-quickbooks-reports', 'name' => 'QuickBooks Desktop Reports', 'href' => admin_url('smart_choice_core_upgrade/reports?quickbooks=1'), 'icon' => 'fa fa-file-export', 'position' => 74],
        ];
        foreach ($reportItems as $item) {
            $CI->app_menu->add_sidebar_children_item('reports', $item);
        }
    }

    if (method_exists($CI->app, 'add_settings_section')) {
        $CI->app->add_settings_section('smart_choice_core_upgrade', [
            'name'     => 'Smart Choice Admin Settings',
            'view'     => 'smart_choice_core_upgrade/settings',
            'position' => 5,
        ]);
        $CI->app->add_settings_section('smart_choice_embedded_modules', [
            'name'     => 'Smart Choice Embedded Modules',
            'view'     => 'smart_choice_core_upgrade/settings_embedded_modules',
            'position' => 6,
        ]);
        $CI->app->add_settings_section('smart_choice_update_center', [
            'name'     => 'Smart Choice Update Center',
            'view'     => 'smart_choice_core_upgrade/settings_update_center',
            'position' => 7,
        ]);
    }
}

function smart_choice_core_upgrade_register_permissions()
{
    if (!function_exists('register_staff_capabilities')) {
        return;
    }

    $standard = [
        'capabilities' => [
            'view_own' => 'View Own',
            'view'     => 'View Global',
            'create'   => 'Create',
            'edit'     => 'Edit',
            'delete'   => 'Delete',
        ],
    ];

    $modules = [
        'smart_choice_core_upgrade' => 'Smart Choice Core',
        'accounting' => 'Accounting Hub',
        'purchasing_hub' => 'Purchasing Hub',
        'sales_center' => 'Sales Hub',
        'prchat' => 'Smart Choice Office Stream',
        'training_manual' => 'Training Manual',
        'custom_pdf' => 'Custom PDF',
        'google_meet' => 'Google Meet',
        'favorite_links' => 'Favorite Links',
        'video_library' => 'Video Library',
        'smart_choice_field_connector' => 'Smart Choice Field Connector',
        'si_todo' => 'Smart Choice To Do',
    ];

    foreach ($modules as $feature => $name) {
        register_staff_capabilities($feature, $standard, $name);
    }
}

function smart_choice_core_upgrade_admin_head()
{
    echo '<link href="' . module_dir_url(SMART_CHOICE_CORE_UPGRADE_MODULE_NAME, 'assets/css/smart_choice_core_upgrade.css') . '?v=' . SMART_CHOICE_CORE_UPGRADE_VERSION . '" rel="stylesheet" type="text/css" />';
}

function smart_choice_core_upgrade_admin_footer()
{
    echo '<script>window.smartChoiceCoreUpgradeSettings = ' . json_encode([
        'enabled'             => get_option('smart_choice_core_upgrade_enabled') === '1',
        'mainMenuSearch'      => get_option('smart_choice_core_main_menu_search') === '1',
        'setupMenuSearch'     => get_option('smart_choice_core_setup_menu_search') === '1',
        'tableTools'          => get_option('smart_choice_core_table_tools') === '1',
        'themeEnabled'        => get_option('smart_choice_core_theme_enabled') === '1',
        'themeName'           => get_option('smart_choice_core_theme_name') ?: 'Smart Choice',
        'displayVersion'      => SMART_CHOICE_CORE_DISPLAY_VERSION,
        'windowsTimezoneName' => 'Eastern Time (US & Canada)',
        'phpTimezoneName'     => 'America/New_York',
    ]) . ';</script>';
    echo '<script src="' . module_dir_url(SMART_CHOICE_CORE_UPGRADE_MODULE_NAME, 'assets/js/smart_choice_core_upgrade.js') . '?v=' . SMART_CHOICE_CORE_UPGRADE_VERSION . '"></script>';
}

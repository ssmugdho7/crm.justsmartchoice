<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Installer Tracking
Description: Quality ★★★★★.  Live installer route tracking, appointment notifications, project visibility, client ETA links, Google Maps integration, and Smart Choice field operations reporting for Perfex CRM.
Version: 1.0.3
Requires at least: 3.4.0
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

define('SMART_INSTALLER_TRACKING_MODULE_NAME', 'smart_installer_tracking');
define('SMART_INSTALLER_TRACKING_VERSION', '1.0.3');

$CI = &get_instance();

register_activation_hook(SMART_INSTALLER_TRACKING_MODULE_NAME, 'smart_installer_tracking_activation_hook');
register_deactivation_hook(SMART_INSTALLER_TRACKING_MODULE_NAME, 'smart_installer_tracking_deactivation_hook');
register_uninstall_hook(SMART_INSTALLER_TRACKING_MODULE_NAME, 'smart_installer_tracking_uninstall_hook');

hooks()->add_action('admin_init', 'smart_installer_tracking_admin_init');
hooks()->add_action('app_admin_head', 'smart_installer_tracking_load_assets');
hooks()->add_action('app_admin_footer', 'smart_installer_tracking_load_footer_assets');
hooks()->add_action('after_cron_run', 'smart_installer_tracking_cron');
hooks()->add_action('after_staff_login', 'smart_installer_tracking_after_staff_login');

$CI->load->helper(SMART_INSTALLER_TRACKING_MODULE_NAME . '/smart_installer_tracking');
register_language_files(SMART_INSTALLER_TRACKING_MODULE_NAME, [SMART_INSTALLER_TRACKING_MODULE_NAME]);

function smart_installer_tracking_activation_hook(): void
{
    require_once(__DIR__ . '/install.php');
}

function smart_installer_tracking_deactivation_hook(): void
{
    update_option('smart_installer_tracking_enabled', '0');
}

function smart_installer_tracking_uninstall_hook(): void
{
    require_once(__DIR__ . '/uninstall.php');
}

function smart_installer_tracking_admin_init(): void
{
    $CI = &get_instance();

    $capabilities = [
        'capabilities' => [
            'view'   => _l('smart_installer_tracking_permission_view'),
            'view_own' => _l('smart_installer_tracking_permission_view_own'),
            'create' => _l('smart_installer_tracking_permission_create'),
            'edit'   => _l('smart_installer_tracking_permission_edit'),
            'delete' => _l('smart_installer_tracking_permission_delete'),
            'reports' => _l('smart_installer_tracking_permission_reports'),
            'settings' => _l('smart_installer_tracking_permission_settings'),
        ],
    ];
    register_staff_capabilities('smart_installer_tracking', $capabilities, _l('smart_installer_tracking'));

    if (has_permission('smart_installer_tracking', '', 'view') || has_permission('smart_installer_tracking', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('smart-installer-tracking', [
            'name'     => _l('smart_installer_tracking'),
            'href'     => admin_url('smart_installer_tracking'),
            'icon'     => 'fa fa-map-marker',
            'position' => 36,
        ]);

        $CI->app_menu->add_sidebar_children_item('smart-installer-tracking', [
            'slug'     => 'smart-installer-tracking-dashboard',
            'name'     => _l('smart_installer_tracking_dashboard'),
            'href'     => admin_url('smart_installer_tracking'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('smart-installer-tracking', [
            'slug'     => 'smart-installer-tracking-live-map',
            'name'     => _l('smart_installer_tracking_live_map'),
            'href'     => admin_url('smart_installer_tracking/live_map'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('smart-installer-tracking', [
            'slug'     => 'smart-installer-tracking-trips',
            'name'     => _l('smart_installer_tracking_trips'),
            'href'     => admin_url('smart_installer_tracking/trips'),
            'position' => 3,
        ]);

        if (has_permission('smart_installer_tracking', '', 'reports')) {
            $CI->app_menu->add_sidebar_children_item('smart-installer-tracking', [
                'slug'     => 'smart-installer-tracking-reports',
                'name'     => _l('smart_installer_tracking_reports'),
                'href'     => admin_url('smart_installer_tracking/reports'),
                'position' => 4,
            ]);
        }

        $CI->app_menu->add_sidebar_children_item('smart-installer-tracking', [
            'slug'     => 'smart-installer-tracking-health',
            'name'     => _l('smart_installer_tracking_health_checker'),
            'href'     => admin_url('smart_installer_tracking/health'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('smart-installer-tracking', [
            'slug'     => 'smart-installer-tracking-help',
            'name'     => _l('smart_installer_tracking_help_guide'),
            'href'     => admin_url('smart_installer_tracking/help'),
            'position' => 6,
        ]);
    }

    if (has_permission('smart_installer_tracking', '', 'settings')) {
        $CI->app->add_settings_section('smart-installer-tracking-settings', [
            'name'     => _l('smart_installer_tracking_settings'),
            'view'     => SMART_INSTALLER_TRACKING_MODULE_NAME . '/settings',
            'position' => 56,
        ]);
    }
}

function smart_installer_tracking_load_assets(): void
{
    if (!is_admin()) {
        return;
    }

    $CI = &get_instance();
    $uri = (string) $CI->uri->uri_string();
    if (strpos($uri, 'admin/smart_installer_tracking') === false && strpos($uri, 'settings') === false) {
        return;
    }

    echo '<link href="' . module_dir_url(SMART_INSTALLER_TRACKING_MODULE_NAME, 'assets/css/smart_installer_tracking.css') . '?v=' . SMART_INSTALLER_TRACKING_VERSION . '" rel="stylesheet" type="text/css" />';
}

function smart_installer_tracking_load_footer_assets(): void
{
    if (!is_admin()) {
        return;
    }

    $CI = &get_instance();
    $uri = (string) $CI->uri->uri_string();
    if (strpos($uri, 'admin/smart_installer_tracking') === false && strpos($uri, 'settings') === false) {
        return;
    }

    echo '<script src="' . module_dir_url(SMART_INSTALLER_TRACKING_MODULE_NAME, 'assets/js/smart_installer_tracking.js') . '?v=' . SMART_INSTALLER_TRACKING_VERSION . '"></script>';
}

function smart_installer_tracking_cron(): void
{
    if (get_option('smart_installer_tracking_enabled') !== '1') {
        return;
    }

    $CI = &get_instance();
    $CI->load->model('smart_installer_tracking/smart_installer_tracking_model');
    $CI->smart_installer_tracking_model->send_due_eta_notifications();
}

function smart_installer_tracking_after_staff_login($staff_id): void
{
    $CI = &get_instance();
    $CI->load->model('smart_installer_tracking/smart_installer_tracking_model');
    $CI->smart_installer_tracking_model->log_action((int) $staff_id, 'Staff Login', 'Installer session became active.');
}

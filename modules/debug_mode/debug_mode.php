<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: CRM Utilities & Debug Tools
Description: Quality ★★★★★.  CRM Utilities & Debug Tools gives administrators a safer control center for troubleshooting Perfex CRM. It includes debug mode controls, cache cleanup, error log reports, client portal toggle, phpMyAdmin quick link, safe database check/repair tools, and a help guide. Designed for PHP 8.5, MySQL 5.7, and Perfex CRM 3.4.x.
Version: 2.1.6
Requires at least: 3.0.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

defined('DEBUG_MODE_MODULE') or define('DEBUG_MODE_MODULE', 'debug_mode');

register_activation_hook(DEBUG_MODE_MODULE, 'debug_mode_smartchoice_activation_hook');
register_deactivation_hook(DEBUG_MODE_MODULE, 'debug_mode_smartchoice_deactivation_hook');
register_language_files(DEBUG_MODE_MODULE, [DEBUG_MODE_MODULE]);

hooks()->add_action('admin_init', 'debug_mode_smartchoice_admin_init');
hooks()->add_action('app_admin_head', 'debug_mode_smartchoice_assets');
hooks()->add_action('app_admin_footer', 'debug_mode_smartchoice_footer_notice');

function debug_mode_smartchoice_activation_hook()
{
    require_once(__DIR__ . '/install.php');
    debug_mode_smartchoice_add_default_options();
}

function debug_mode_smartchoice_deactivation_hook()
{
    // Do not delete logs or settings. Only place the CRM back in production mode.
    debug_mode_enable_environment('production');
    update_option('debug_mode_enabled', '0');
}

function debug_mode_smartchoice_add_default_options()
{
    add_option('debug_mode_enabled', '0');
    add_option('debug_mode_client_visible', '0');
    add_option('debug_mode_log_level', 'basic');
    add_option('debug_mode_allowed_roles', '');
    add_option('debug_mode_allowed_staff', '');
    add_option('debug_mode_phpmyadmin_url', 'https://s111.bluehost.com:2083/');
    add_option('debug_mode_client_portal_enabled', '1');
    add_option('debug_mode_show_admin_banner', '1');
    add_option('debug_mode_cache_last_cleared', '');
    add_option('debug_mode_version', '2.1.6');
    add_option('debug_mode_access_control_enabled', '0');
}

function debug_mode_smartchoice_admin_init()
{
    $CI =& get_instance();
    debug_mode_smartchoice_add_default_options();

    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities('debug_mode', [
            'capabilities' => [
                'view_own' => 'View (Own)',
                'view'     => 'View (Global)',
                'create' => 'Create',
                'edit'   => 'Edit',
                'delete' => 'Delete',
            ],
        ], 'CRM Utilities & Debug Tools');
    }

    if (isset($CI->app) && method_exists($CI->app, 'add_settings_section')) {
        $CI->app->add_settings_section('debug_mode', [
            'name'     => 'CRM Utilities & Debug Tools',
            'view'     => 'debug_mode/settings',
            'position' => 65,
        ]);
    } elseif (isset($CI->app_tabs) && method_exists($CI->app_tabs, 'add_settings_tab')) {
        $CI->app->add_settings_section('debug_mode', [
            'name'     => 'CRM Utilities & Debug Tools',
            'view'     => 'debug_mode/settings',
            'position' => 65,
        ]);
    }

    // Add a direct admin menu link when the menu object exists.
    if (isset($CI->app_menu) && method_exists($CI->app_menu, 'add_sidebar_menu_item')) {
        $CI->app_menu->add_sidebar_menu_item('debug-mode-utilities', [
            'name'     => 'CRM Utilities',
            'href'     => admin_url('debug_mode'),
            'position' => 58,
            'icon'     => 'fa fa-wrench',
        ]);
    }
}

function debug_mode_smartchoice_assets()
{
    $CI =& get_instance();
    $uri = isset($CI->uri) ? (string) $CI->uri->uri_string() : '';
    $isModulePage = strpos($uri, 'admin/debug_mode') === 0;
    $isSettingsPage = strpos($uri, 'admin/settings') === 0 && isset($_GET['group']) && $_GET['group'] === 'debug_mode';
    if (!$isModulePage && !$isSettingsPage) {
        return;
    }
    echo '<link href="' . module_dir_url('debug_mode', 'assets/css/debug_mode_smartchoice.css') . '?v=216" rel="stylesheet" type="text/css" />';
    echo '<script src="' . module_dir_url('debug_mode', 'assets/js/debug_mode_smartchoice.js') . '?v=216" defer></script>';
}

function debug_mode_smartchoice_footer_notice()
{
    if (get_option('debug_mode_enabled') !== '1' || get_option('debug_mode_show_admin_banner') !== '1') {
        return;
    }

    echo '<script>
    (function(){
        if (window.localStorage && !localStorage.getItem("debug_mode_activated_seen")) {
            localStorage.setItem("debug_mode_activated_seen", "1");
            setTimeout(function(){
                if (typeof alert_float === "function") {
                    alert_float("info", "Debug Mode is active. Use CRM Utilities carefully.");
                }
            }, 800);
        }
    })();
    </script>';
}

function debug_mode_enable_environment($desired_env)
{
    $desired_env = $desired_env === 'development' ? 'development' : 'production';
    $indexFile = FCPATH . 'index.php';

    if (!is_writable($indexFile)) {
        return false;
    }

    $content = file_get_contents($indexFile);
    if ($content === false) {
        return false;
    }

    $newContent = preg_replace(
        "/define\(['\"]ENVIRONMENT['\"],\s*['\"][^'\"]+['\"]\);/",
        "define('ENVIRONMENT', '" . $desired_env . "');",
        $content,
        1
    );

    if (!$newContent || $newContent === $content) {
        return false;
    }

    return file_put_contents($indexFile, $newContent) !== false;
}

function debug_mode_is_enabled()
{
    return get_option('debug_mode_enabled') === '1';
}

function debug_mode_user_allowed()
{
    if (function_exists('is_admin') && is_admin()) {
        return true;
    }

    if (function_exists('has_permission') && has_permission('debug_mode', '', 'view')) {
        return true;
    }

    $allowedStaff = get_option('debug_mode_allowed_staff');
    if (function_exists('get_staff_user_id') && $allowedStaff) {
        $ids = array_map('trim', explode(',', (string) $allowedStaff));
        return in_array((string) get_staff_user_id(), $ids, true);
    }

    return false;
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

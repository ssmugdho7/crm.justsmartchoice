<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Add-ons & Integrations
Description: One page to see every integration and module: connect, disconnect, test, install and uninstall.
Version: 1.0.0
Requires at least: 3.0.0
Author: Smart Choice Contractors USA
*/

define('SC_ADDONS_MODULE', 'sc_addons');
define('SC_ADDONS_VERSION', '1.0.0');

// Modules that can never be deactivated or uninstalled from this page (in addition to uninstallable_modules()).
const SC_ADDONS_PROTECTED_MODULES = ['sc_addons', 'sc_admin_fixes', 'sc_onboarding', 'prchat', 'menu_setup', 'theme_style', 'master_crm_fix', 'sc_module_licensing', 'smart_choice_enterprise_core'];
const SC_ADDONS_PROTECTED_PREFIXES = ['smart_choice_core_'];

register_language_files(SC_ADDONS_MODULE, [SC_ADDONS_MODULE]);

hooks()->add_action('admin_init', 'sc_addons_admin_init');

function sc_addons_admin_init()
{
    $CI = &get_instance();
    if (!is_staff_logged_in() || !is_admin() || $CI->input->is_ajax_request()) {
        return;
    }
    $CI->app_menu->add_sidebar_menu_item('sc-addons', [
        'name'     => sc_addons_t('sc_addons_menu'),
        'href'     => admin_url('sc_addons'),
        'icon'     => 'fa-solid fa-puzzle-piece',
        'position' => 900,
    ]);
}

function sc_addons_is_protected_module($name)
{
    $name = strtolower((string) $name);
    if (in_array($name, SC_ADDONS_PROTECTED_MODULES, true) || in_array($name, array_map('strtolower', uninstallable_modules()), true)) {
        return true;
    }
    foreach (SC_ADDONS_PROTECTED_PREFIXES as $prefix) {
        if (strpos($name, $prefix) === 0) {
            return true;
        }
    }

    return false;
}

require_once __DIR__ . '/helpers/sc_addons_registry.php';
require_once __DIR__ . '/helpers/sc_addons_status.php';
require_once __DIR__ . '/helpers/sc_addons_guides.php';

/** UI strings handed to sc_addons.js (already translated for the current staff language). */
function sc_addons_js_strings()
{
    $keys = ['connect', 'disconnect', 'configure', 'test', 'enable', 'install', 'activate', 'deactivate', 'uninstall', 'menu', 'cancel', 'saved_keep',
        'st_connected', 'st_needs_attention', 'st_not_configured', 'st_not_installed', 'st_disabled',
        'sec_connected', 'sec_attention', 'sec_available', 'sec_disabled', 'confirm_disconnect_title', 'confirm_disconnect_msg',
        'confirm_deactivate_title', 'confirm_deactivate_msg', 'confirm_uninstall_title', 'confirm_uninstall_msg', 'type_name', 'confirm',
        'working', 'error', 'last_test', 'test_ok', 'test_failed', 'module_label', 'protected', 'mod_active', 'mod_inactive', 'mod_not_installed',
        'required', 'none_found', 'no_modules', 'version', 'by', 'issue_label', 'connected_ok', 'module_missing', 'open_settings',
        'confirm_erase_title', 'confirm_erase_msg', 'confirm_erase_none', 'clear_saved', 'recommended', 'guide_title', 'guide_generic', 'copy', 'copied',
        'copy_failed', 'refresh_failed', 'clear_filters', 'exclusive_warn', 'deactivate_uninstall', 'uninstall_needs_deactivate',
        'confirm_deact_uninstall_title', 'confirm_deact_uninstall_msg', 'st_disabled_count', 'mod_all', 'sort_name', 'sort_status', 'sort_label',
        'show_more', 'showing', 'opens_new_tab', 'save', 'connect_title', 'save_signin', 'redirecting'];
    $out = [];
    foreach ($keys as $k) {
        $out[$k] = sc_addons_t('sc_addons_js_' . $k);
    }

    return $out;
}

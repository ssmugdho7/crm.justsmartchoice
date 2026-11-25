<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Onboarding & Tours
Description: Help center, guided product tours and an animated first-run welcome for staff.
Version: 1.0.0
Requires at least: 3.0.0
Author: Smart Choice Contractors USA
*/

define('SC_ONB_MODULE', 'sc_onboarding');
define('SC_ONB_VERSION', '1.0.0');
// Tour content files (assets/js/tours/<name>.js). Only files that exist are loaded, in this order.
define('SC_ONB_TOUR_FILES', ['basics', 'sales', 'projects', 'customers', 'leads', 'support', 'team']);

require_once __DIR__ . '/helpers/sc_onboarding_helper.php';

register_language_files(SC_ONB_MODULE, [SC_ONB_MODULE]);
register_activation_hook(SC_ONB_MODULE, 'sc_onb_activate');

hooks()->add_action('admin_init', 'sc_onb_admin_init');
hooks()->add_action('app_admin_head', 'sc_onb_admin_head');
hooks()->add_action('app_admin_footer', 'sc_onb_admin_footer');
hooks()->add_action('admin_navbar_start', 'sc_onb_navbar_help');

/** Activation only creates the state table if it is missing; it never drops or alters data. */
function sc_onb_activate()
{
    sc_onb_ensure_table(true);
}

function sc_onb_admin_init()
{
    $CI = &get_instance();
    // AJAX requests (DataTables, modals) never render the sidebar, so skip the menu work there.
    if (!is_staff_logged_in() || $CI->input->is_ajax_request()) {
        return;
    }
    $CI->app_menu->add_sidebar_menu_item('sc-help', [
        'name'     => _l('sc_onb_menu'),
        'href'     => admin_url('sc_onboarding/help'),
        'icon'     => 'fa-regular fa-circle-question',
        'position' => 2, // right after Dashboard (1) so non-technical staff can find it
    ]);
}

function sc_onb_asset_url($file)
{
    $path = __DIR__ . '/' . $file;
    return module_dir_url(SC_ONB_MODULE, $file) . '?v=' . (is_file($path) ? filemtime($path) : SC_ONB_VERSION);
}

function sc_onb_admin_head()
{
    if (!is_staff_logged_in()) {
        return;
    }
    echo '<link rel="stylesheet" href="' . e(sc_onb_asset_url('assets/css/sc_onboarding.css')) . '">' . PHP_EOL;
}

/** Small "?" button in the admin top bar, next to the other header icons. */
function sc_onb_navbar_help()
{
    if (!is_staff_logged_in()) {
        return;
    }
    echo '<li class="icon sconb-header-help" data-toggle="tooltip" data-placement="bottom" title="' . e(_l('sc_onb_menu')) . '">'
        . '<a href="' . e(admin_url('sc_onboarding/help')) . '" aria-label="' . e(_l('sc_onb_menu')) . '" data-sconb-header-help>'
        . '<i class="fa-regular fa-circle-question fa-lg tw-shrink-0" aria-hidden="true"></i></a></li>';
}

function sc_onb_admin_footer()
{
    if (!is_staff_logged_in()) {
        return;
    }
    $CI    = &get_instance();
    $staff = get_staff(get_staff_user_id());
    $csrf  = get_csrf_for_ajax();
    // Admin-relative path, e.g. '' (dashboard), 'projects', 'projects/view/5'.
    $path = trim((string) $CI->uri->uri_string(), '/');
    $path = preg_replace('#^' . preg_quote(ADMIN_URI, '#') . '(/|$)#', '', $path);
    $path = $path === 'dashboard' ? '' : $path;
    $tours = [];
    foreach (SC_ONB_TOUR_FILES as $name) {
        if (is_file(__DIR__ . '/assets/js/tours/' . $name . '.js')) {
            $tours[] = $name;
        }
    }

    $config = [
        'staffId'   => (int) get_staff_user_id(),
        'firstName' => $staff ? (string) $staff->firstname : '',
        'isAdmin'   => is_admin(),
        'lang'      => sc_onb_language(),
        'adminUrl'  => admin_url(),
        'csrf'      => ['name' => $csrf['token_name'], 'hash' => $csrf['hash']],
        'stateUrl'  => admin_url('sc_onboarding/state'),
        'saveUrl'   => admin_url('sc_onboarding/save_state'),
        'helpUrl'   => admin_url('sc_onboarding/help'),
        'path'      => $path,
        'tourOrder' => SC_ONB_TOUR_FILES,
        'perms'     => sc_onb_perms(),
        'state'     => sc_onb_get_state(get_staff_user_id()),
        't'         => sc_onb_js_strings(),
    ];

    $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
    echo '<script>window.SCOnb = ' . json_encode($config, $flags) . ';</script>' . PHP_EOL;
    echo '<script src="' . e(sc_onb_asset_url('assets/js/tour.js')) . '"></script>' . PHP_EOL;
    foreach ($tours as $name) {
        echo '<script src="' . e(sc_onb_asset_url('assets/js/tours/' . $name . '.js')) . '"></script>' . PHP_EOL;
    }
    echo '<script src="' . e(sc_onb_asset_url('assets/js/onboarding.js')) . '"></script>' . PHP_EOL;
}

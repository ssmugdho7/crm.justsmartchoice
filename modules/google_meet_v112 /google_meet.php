<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Google Meet
Description: Google Meet connects Perfex CRM with meeting scheduling for staff and customers. Smart Choice Contractors USA can create instant or scheduled meetings, generate or save Meet links, invite employees/customers by CRM email, CRM notifications, and Twilio SMS when available, track attendees, comments, reports, health checks, and customer portal access without disrupting the existing CRM workflow.
Version: 1.1.2
Author URI: https://justsmartchoice.com/webdeveloper.php
Author: Smart Choice Contractors USA / Harold Cabrera
Requires at least: 3.4.1
*/

define('GOOGLE_MEET_MODULE_NAME', 'google_meet');
define('GOOGLE_MEET_VERSION', '1.1.2');

/**
 * Smart Choice migration cleanup.
 * Removes legacy non-standard migration files that break Perfex module upgrade scanning.
 * Required because older packages left files like 104_google_meet_stable_database.php beside
 * 104_version_104.php. Perfex expects one standard migration file per version.
 */
function google_meet_cleanup_legacy_migrations()
{
    $migration_path = __DIR__ . '/migrations/';
    if (!is_dir($migration_path) || !is_writable($migration_path)) {
        return;
    }

    $legacy_files = [
        '104_google_meet_stable_database.php',
        '105_google_meet_view_path_fix.php',
        '106_google_meet_auto_reports_notes_fix.php',
    ];

    foreach ($legacy_files as $legacy_file) {
        $full_path = $migration_path . $legacy_file;
        if (is_file($full_path)) {
            @unlink($full_path);
        }
    }
}

google_meet_cleanup_legacy_migrations();


register_activation_hook(GOOGLE_MEET_MODULE_NAME, 'google_meet_activation_hook');

hooks()->add_action('admin_init', 'google_meet_admin_init');
hooks()->add_action('app_admin_head', 'google_meet_admin_assets');
hooks()->add_action('app_admin_head', 'google_meet_smart_choice_standard_head');
hooks()->add_action('app_admin_footer', 'google_meet_smart_choice_standard_footer');
hooks()->add_action('customers_navigation_end', 'google_meet_client_navigation_item');
hooks()->add_action('app_customers_footer', 'google_meet_customer_footer');

function google_meet_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function google_meet_admin_init()
{
    $CI =& get_instance();
    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities(GOOGLE_MEET_MODULE_NAME, [
            'view_own'    => _l('view_own'),
            'view'        => _l('view_global'),
            'create'      => _l('create'),
            'edit'        => _l('edit'),
            'delete'      => _l('delete'),
        ], 'Google Meet');
    }

    if (isset($CI->app_menu)) {
        $CI->app_menu->add_sidebar_menu_item('google-meet', [
            'name'     => 'Google Meet',
            'href'     => admin_url('google_meet'),
            'icon'     => 'fa fa-video-camera',
            'position' => 57,
        ]);

        $items = [
            ['google-meet-dashboard', 'Dashboard', 'google_meet', 'fa fa-dashboard', 1],
            ['google-meet-new-meeting', 'New Meeting', 'google_meet/create', 'fa fa-plus-circle', 2],
            ['google-meet-reports', 'Reports', 'google_meet/reports', 'fa fa-bar-chart', 3],
            ['google-meet-test-notifications', 'Test Notifications', 'google_meet/test_notifications', 'fa fa-bell', 4],
            ['google-meet-settings', 'Settings', 'google_meet/settings', 'fa fa-cog', 5],
            ['google-meet-join', 'Join Meeting', 'google_meet/join', 'fa fa-sign-in', 6],
            ['google-meet-help', 'Help', 'google_meet/help', 'fa fa-question-circle', 7],
            ['google-meet-health', 'Health', 'google_meet/health', 'fa fa-heartbeat', 8],
        ];

        foreach ($items as $item) {
            $CI->app_menu->add_sidebar_children_item('google-meet', [
                'slug'     => $item[0],
                'name'     => $item[1],
                'href'     => admin_url($item[2]),
                'icon'     => $item[3],
                'position' => $item[4],
            ]);
        }
    }
}

function google_meet_admin_assets()
{
    echo '<link href="' . module_dir_url('google_meet', 'assets/css/google_meet_smartchoice.css') . '?v=112" rel="stylesheet" type="text/css" />';
}

function google_meet_smart_choice_standard_head()
{
    echo '<link href="' . module_dir_url('google_meet', 'assets/css/smart_choice_module_standard.css') . '?v=112" rel="stylesheet" type="text/css" />';
}

function google_meet_smart_choice_standard_footer()
{
    echo '<script src="' . module_dir_url('google_meet', 'assets/js/smart_choice_module_standard.js') . '?v=112"></script>';
}

function google_meet_client_navigation_item()
{
    if (get_option('google_meet_client_portal_enabled') !== '1') {
        return;
    }
    echo '<li class="customers-nav-item-google-meet"><a href="' . site_url('google-meet-access') . '"><i class="fa fa-video-camera"></i> ' . html_escape(get_option('google_meet_client_portal_title') ?: 'My Video Meetings') . '</a></li>';
}


function google_meet_customer_footer()
{
    if (get_option('google_meet_client_portal_enabled') !== '1') {
        return;
    }
    $url = site_url('google-meet-access');
    echo '<script>(function(){function addMeetButton(){var login=document.querySelector("form[action*=authentication] button[type=submit], .customers_login button[type=submit], #login-form button[type=submit]");if(!login||document.getElementById("smart-choice-meet-access"))return;var a=document.createElement("a");a.id="smart-choice-meet-access";a.href=' . json_encode($url) . ';a.className="btn btn-default btn-block";a.style.marginBottom="10px";a.innerHTML="<i class=\\"fa fa-video-camera\\"></i> My Video Meetings";login.parentNode.insertBefore(a,login);}document.addEventListener("DOMContentLoaded",addMeetButton);setTimeout(addMeetButton,600);})();</script>';
}

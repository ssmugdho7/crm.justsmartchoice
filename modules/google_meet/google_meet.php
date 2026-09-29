<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Google Meet
Description: Google Meet connects Perfex CRM with meeting scheduling for staff and customers. Smart Choice Contractors USA can create instant or scheduled meetings, generate or save Meet links, invite employees/customers by CRM email, CRM notifications, and Twilio SMS when available, track attendees, comments, reports, health checks, and customer portal access without disrupting the existing CRM workflow.
Version: 1.2.7
Author URI: https://justsmartchoice.com/webdeveloper.php
Author: Smart Choice Contractors USA / Harold Cabrera
Requires at least: 3.4.1
*/

define('GOOGLE_MEET_MODULE_NAME', 'google_meet');
define('GOOGLE_MEET_VERSION', '1.2.7');


/**
 * Return a translated module label, with a safe English fallback.
 * Perfex returns the language key unchanged when a key is unavailable, so this
 * helper prevents missing language keys from breaking customer portal pages.
 */
if (!function_exists('google_meet_lang')) {
    function google_meet_lang($key, $fallback = '')
    {
        $translated = function_exists('_l') ? _l($key) : '';
        if ($translated === '' || $translated === null || $translated === $key) {
            return $fallback !== '' ? $fallback : $key;
        }

        return $translated;
    }
}

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
hooks()->add_action('app_customers_head', 'google_meet_customer_head');
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
            'view_own'           => 'View Own',
            'view'               => 'View(Permission Global)',
            'create'             => 'Create',
            'edit'               => 'Edit',
            'delete'             => 'Delete',
            'view_all_templates' => 'View All Templates',
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
    echo '<link href="' . module_dir_url('google_meet', 'assets/css/google_meet_smartchoice.css') . '?v=127" rel="stylesheet" type="text/css" />';
}

function google_meet_smart_choice_standard_head()
{
    echo '<link href="' . module_dir_url('google_meet', 'assets/css/smart_choice_module_standard.css') . '?v=127" rel="stylesheet" type="text/css" />';
}

function google_meet_smart_choice_standard_footer()
{
    echo '<script src="' . module_dir_url('google_meet', 'assets/js/smart_choice_module_standard.js') . '?v=127"></script>';
}

function google_meet_client_navigation_item()
{
    if (get_option('google_meet_client_portal_enabled') !== '1' || !is_client_logged_in()) {
        return;
    }

    $label = trim((string) get_option('google_meet_client_portal_title'));
    if ($label === '' || $label === 'My Video Meetings') {
        $label = 'Meetings';
    }

    echo '<li class="customers-nav-item-google-meet">'
        . '<a href="' . site_url('google_meet/meeting_clients/meetings') . '" aria-label="Google Meet meetings">'
        . '<i class="fa fa-video-camera" aria-hidden="true"></i>'
        . '<span class="gm-client-nav-copy"><span class="gm-client-nav-title">' . html_escape($label) . '</span>'
        . '<small class="gm-client-nav-subtitle">Google Meet</small></span>'
        . '</a></li>';
}

function google_meet_customer_head()
{
    if (get_option('google_meet_client_portal_enabled') !== '1' || !is_client_logged_in()) {
        return;
    }

    echo '<link href="' . module_dir_url('google_meet', 'assets/css/google_meet.css') . '?v=127" rel="stylesheet" type="text/css" />';
    echo '<link href="' . module_dir_url('google_meet', 'assets/css/google_meet_smartchoice.css') . '?v=127" rel="stylesheet" type="text/css" />';
    echo '<style>
    .customers-nav-item-google-meet>a{display:flex!important;align-items:center!important;gap:7px!important}
    .customers-nav-item-google-meet>a>i{font-size:15px!important;flex:0 0 auto!important}
    .gm-client-nav-copy{display:inline-flex!important;flex-direction:column!important;line-height:1.05!important;text-align:left!important}
    .gm-client-nav-title{font-size:13px!important;font-weight:600!important;line-height:1.1!important}
    .gm-client-nav-subtitle{font-size:9px!important;font-weight:400!important;line-height:1.1!important;opacity:.72!important;margin-top:2px!important}
    </style>';
}

function google_meet_customer_footer()
{
    if (get_option('google_meet_client_portal_enabled') !== '1' || !is_client_logged_in()) {
        return;
    }

    echo '<script>(function(){
      function moveGoogleMeetNavigation(){
        var item=document.querySelector(".customers-nav-item-google-meet");
        if(!item){return;}
        var nav=item.parentNode;
        if(!nav){return;}
        var target=nav.querySelector(".customers-nav-item-logout, li a[href*=\\"authentication/logout\\"], li a[href*=\\"logout\\"], .customers-nav-item-profile");
        if(target){
          var targetItem=target.tagName&&target.tagName.toLowerCase()==="li"?target:target.closest("li");
          if(targetItem&&targetItem!==item){nav.insertBefore(item,targetItem);}
        }
      }
      if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",moveGoogleMeetNavigation);}else{moveGoogleMeetNavigation();}
      window.addEventListener("load",moveGoogleMeetNavigation);
    })();</script>';
}

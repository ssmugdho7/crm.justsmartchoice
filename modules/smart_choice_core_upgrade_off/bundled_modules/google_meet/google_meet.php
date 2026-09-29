<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Google Meet
Description: Google Meet connects Perfex CRM with meeting scheduling for staff and customers. Smart Choice Contractors USA can create instant or scheduled meetings, save or auto-generate Meet links, track employees, attendees, comments, notes, reports, and meeting activity without disrupting the existing CRM workflow.
Version: 1.0.6
Author: Smart Choice Contractors USA / Harold Cabrera
Requires at least: 3.4.1
*/

define('GOOGLE_MEET_MODULE_NAME', 'google_meet');

register_activation_hook(GOOGLE_MEET_MODULE_NAME, 'google_meet_activation_hook');

hooks()->add_action('admin_init', 'google_meet_admin_init');
hooks()->add_action('app_admin_head', 'google_meet_admin_assets');

function google_meet_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function google_meet_admin_init()
{
    require_once(__DIR__ . '/install.php');

    $CI =& get_instance();

    if (isset($CI->app_menu)) {
        $CI->app_menu->add_sidebar_menu_item('google-meet', [
            'name'     => 'Google Meet',
            'href'     => admin_url('google_meet'),
            'icon'     => 'fa fa-video-camera',
            'position' => 57,
        ]);

        $CI->app_menu->add_sidebar_children_item('google-meet', [
            'slug'     => 'google-meet-dashboard',
            'name'     => 'Dashboard',
            'href'     => admin_url('google_meet'),
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('google-meet', [
            'slug'     => 'google-meet-new-meeting',
            'name'     => 'New Meeting',
            'href'     => admin_url('google_meet/create'),
            'position' => 2,
        ]);

        $CI->app_menu->add_sidebar_children_item('google-meet', [
            'slug'     => 'google-meet-reports',
            'name'     => 'Reports',
            'href'     => admin_url('google_meet/reports'),
            'position' => 3,
        ]);

        $CI->app_menu->add_sidebar_children_item('google-meet', [
            'slug'     => 'google-meet-settings',
            'name'     => 'Settings',
            'href'     => admin_url('google_meet/settings'),
            'position' => 4,
        ]);
    }
}

function google_meet_admin_assets()
{
    echo '<link href="' . module_dir_url('google_meet', 'assets/css/google_meet_smartchoice.css') . '" rel="stylesheet" type="text/css" />';
}

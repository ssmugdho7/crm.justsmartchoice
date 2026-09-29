<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Mobile App API
Description: Smart Choice mobile app API for Perfex CRM with health check, logging, and secure optional Auth-Key support
Version: 1.5.1
Author: Smart Choice Contractors
Author URI: https://justsmartchoice.com
Requires at least: 3.4.1
*/

/*
 * Check if can have permissions then apply new tab in settings
 */
if (is_admin() && get_option(PERFEX_MOBILE_APP_API . '_enabled') == '1') {
    hooks()->add_action('admin_init', PERFEX_MOBILE_APP_API . '_settings_tab');
}

function perfex_mobile_app_api_settings_tab()
{
    $CI = &get_instance();
    $tab = [
        'name'     => _l(PERFEX_MOBILE_APP_API . '_settings_name'),
        'view'     => PERFEX_MOBILE_APP_API . '/settings',
        'position' => 37,
        'icon'     => 'fa-mobile',
    ];

    if (isset($CI->app) && method_exists($CI->app, 'add_settings_section')) {
        $CI->app->add_settings_section(PERFEX_MOBILE_APP_API . '-settings', $tab);
    } elseif (isset($CI->app_tabs) && method_exists($CI->app_tabs, 'add_settings_tab')) {
        $CI->app_tabs->add_settings_tab(PERFEX_MOBILE_APP_API . '-settings', $tab);
    }
}

if (!function_exists('prd')) {
    function prd(...$array)
    {
        echo '<pre>';
        foreach ($array as $data) {
            print_r($data) . "<br/>";;
        }
        die;
    }
}
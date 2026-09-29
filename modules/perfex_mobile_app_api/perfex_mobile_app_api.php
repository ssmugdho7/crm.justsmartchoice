<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Mobile App API
Description: Smart Choice mobile app API for Perfex CRM with health check, logging, and secure optional Auth-Key support
Version: 1.5.1
Author: Smart Choice Contractors
Author URI: https://justsmartchoice.com
Requires at least: 3.4.1
*/

const PERFEX_MOBILE_APP_API = 'perfex_mobile_app_api';

/*
    Defined required table names
*/
define('TABLE_API_LOGS', db_prefix() . 'api_logs');


$CI = &get_instance();

/**
 * Register the activation for module
 */
register_activation_hook(PERFEX_MOBILE_APP_API, 'perfex_mobile_api_activation_hook');
register_deactivation_hook(PERFEX_MOBILE_APP_API, 'perfex_mobile_api_deactivation_hook');
register_uninstall_hook(PERFEX_MOBILE_APP_API, 'perfex_mobile_api_uninstall_hook');
/**
 * The activation function
 */
function perfex_mobile_api_activation_hook()
{
    require(__DIR__ . '/install.php');
}

function perfex_mobile_api_deactivation_hook()
{
    // Keep data on deactivation. Full cleanup is handled during uninstall.
}

function perfex_mobile_api_uninstall_hook()
{
    require(__DIR__ . '/uninstall.php');
}

/**
 * Register PERFEX_MOBILE_APP_API language files
 */
register_language_files(PERFEX_MOBILE_APP_API, ['perfex_mobile_app_api']);

/**
 * Load the PERFEX_MOBILE_APP_API helper
 */
$CI->load->helper(PERFEX_MOBILE_APP_API . "/perfex_mobile_app_api");

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

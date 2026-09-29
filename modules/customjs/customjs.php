<?php
defined('BASEPATH') or exit('No direct script access allowed');
// Register Smart Choice language files.
register_language_files('customjs', ['customjs']);

/*
Module Name: Perfex CRM Custom JS Module
Description: Quality ★★★★★.  The easiest way to implement own javascript
Version: 1.0.0
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
Requires at least: 2.3.4
*/

define('CUSTOM_JS_MODULE', 'customjs');

$CI = &get_instance();

register_activation_hook(CUSTOM_JS_MODULE, 'customjs_activation_hook');

/**
 * The activation function
 */
function customjs_activation_hook()
{
    require(__DIR__ .'/init.php');
}
/**
 * Register helper init
 */
$CI->load->helper(CUSTOM_JS_MODULE.'/custom_js');


// Smart Choice Standard Permission Registration
if (!function_exists('customjs_smart_choice_standard_permissions')) {
    function customjs_smart_choice_standard_permissions()
    {
        if (function_exists('register_staff_capabilities')) {
            register_staff_capabilities('customjs', [
                'capabilities' => [
                    'view_own' => _l('permission_view_own'),
                    'view'     => _l('permission_view'),
                    'create'   => _l('permission_create'),
                    'edit'     => _l('permission_edit'),
                    'delete'   => _l('permission_delete'),
                ],
            ], 'Custom JavaScript');
        }
    }
}
hooks()->add_action('admin_init', 'customjs_smart_choice_standard_permissions');

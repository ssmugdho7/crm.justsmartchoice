<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Ultimate POS
Description: Module for managing Ultimate POS
Version: 1.0.0
Requires at least: 2.3.*
Author: Your Name
Author URI: https://clouderp360.com
*/

// Define module constants
define('ULTIMATEPOS_MODULE_NAME', 'ultimatepos');
define('ULTIMATEPOS_REVISION', 100);

$CI = &get_instance();
$CI->load->helper(ULTIMATEPOS_MODULE_NAME . '/ultimatepos');

// Load the language file
register_language_files(ULTIMATEPOS_MODULE_NAME, [ULTIMATEPOS_MODULE_NAME]);

// Register hooks
hooks()->add_action('admin_init', 'ultimatepos_module_init_menu_items');

/**
 * Initialize menu items in admin setup
 */
function ultimatepos_module_init_menu_items()
{
    // Get CodeIgniter instance
    $CI = &get_instance();

    // Add settings tab for the module
    $CI->app_tabs->add_settings_tab(ULTIMATEPOS_MODULE_NAME, [
        'name'     => _l('settings_group_' . ULTIMATEPOS_MODULE_NAME),
        'view'     => 'ultimatepos/admin/settings',
        'position' => 9,
        'icon'     => 'fa fa-users',
    ]);
}
?>

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

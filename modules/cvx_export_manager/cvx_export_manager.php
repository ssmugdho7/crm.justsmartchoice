<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: CSV Export Manager
Description: Smart Choice CSV export manager for CRM reports, table exports, sample headers, and structured exports. Creator Harold Cabrera. Quality ★★★★★.
Version: 1.0.1
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

register_activation_hook('cvx_export_manager', 'cvx_export_manager_activation_hook');
register_language_files('cvx_export_manager', ['cvx_export_manager']);
hooks()->add_action('admin_init', 'cvx_export_manager_admin_init');

function cvx_export_manager_activation_hook() { require_once(__DIR__ . '/install.php'); }
function cvx_export_manager_admin_init() {
    $CI = &get_instance();
    if (is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('cvx_export_manager', ['name'=>'CSV Export Manager','href'=>admin_url('cvx_export_manager'),'position'=>95,'icon'=>'fa fa-star']);
    }
    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities('cvx_export_manager', ['capabilities'=>['view_own'=>'View Own','view'=>'View Global','create'=>'Create','edit'=>'Edit','delete'=>'Delete']], 'CSV Export Manager');
    }
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

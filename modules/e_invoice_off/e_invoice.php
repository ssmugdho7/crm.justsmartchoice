<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: E-Invoice
Description: Default Smart Choice e-Invoice module with invoice templates, payment document readiness, and CRM invoice integration. Creator Harold Cabrera. Quality ★★★★★.
Version: 1.0.1
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

register_activation_hook('e_invoice', 'e_invoice_activation_hook');
register_language_files('e_invoice', ['e_invoice']);
hooks()->add_action('admin_init', 'e_invoice_admin_init');

function e_invoice_activation_hook() { require_once(__DIR__ . '/install.php'); }
function e_invoice_admin_init() {
    $CI = &get_instance();
    if (is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('e_invoice', ['name'=>'E-Invoice','href'=>admin_url('e_invoice'),'position'=>95,'icon'=>'fa fa-star']);
    }
    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities('e_invoice', ['capabilities'=>['view_own'=>'View Own','view'=>'View Global','create'=>'Create','edit'=>'Edit','delete'=>'Delete']], 'E-Invoice');
    }
}

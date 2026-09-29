<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice Custom UI Manager
Description: Combined Custom JavaScript and Elite Custom JS CSS manager with templates, preview support, visual options, and safe CRM UI customization. Creator Harold Cabrera. Quality ★★★★★.
Version: 1.0.1
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

register_activation_hook('smart_choice_custom_ui_manager', 'smart_choice_custom_ui_manager_activation_hook');
register_language_files('smart_choice_custom_ui_manager', ['smart_choice_custom_ui_manager']);
hooks()->add_action('admin_init', 'smart_choice_custom_ui_manager_admin_init');

function smart_choice_custom_ui_manager_activation_hook() { require_once(__DIR__ . '/install.php'); }
function smart_choice_custom_ui_manager_admin_init() {
    $CI = &get_instance();
    if (is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('smart_choice_custom_ui_manager', ['name'=>'Smart Choice Custom UI Manager','href'=>admin_url('smart_choice_custom_ui_manager'),'position'=>95,'icon'=>'fa fa-star']);
    }
    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities('smart_choice_custom_ui_manager', ['capabilities'=>['view_own'=>'View Own','view'=>'View Global','create'=>'Create','edit'=>'Edit','delete'=>'Delete']], 'Smart Choice Custom UI Manager');
    }
}

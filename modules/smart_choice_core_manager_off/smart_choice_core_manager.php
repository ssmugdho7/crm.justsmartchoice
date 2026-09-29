<?php
/**
 * Module Name: Smart Choice Core Manager
 * Description: Centralized Smart Choice CRM governance, appearance, localization, system tools, module downloads, and controlled core upgrade management.
 * Version: 1.0.0
 * Requires at least: 3.4.0
 * Author: Smart Choice Contractors USA
 */
defined('BASEPATH') or exit('No direct script access allowed');

define('SMART_CHOICE_CORE_MANAGER_MODULE_NAME', 'smart_choice_core_manager');
register_activation_hook(SMART_CHOICE_CORE_MANAGER_MODULE_NAME, 'smart_choice_core_manager_activate');
register_deactivation_hook(SMART_CHOICE_CORE_MANAGER_MODULE_NAME, 'smart_choice_core_manager_deactivate');
register_language_files(SMART_CHOICE_CORE_MANAGER_MODULE_NAME, [SMART_CHOICE_CORE_MANAGER_MODULE_NAME]);

hooks()->add_action('admin_init', 'smart_choice_core_manager_admin_init');
hooks()->add_action('app_admin_head', 'smart_choice_core_manager_head');
hooks()->add_action('app_admin_footer', 'smart_choice_core_manager_footer');
hooks()->add_action('pre_activate_module', 'smart_choice_core_manager_validate_module');
hooks()->add_filter('module_uninstallable', 'smart_choice_core_manager_protect_itself');

function smart_choice_core_manager_activate(){ require_once __DIR__.'/install.php'; }
function smart_choice_core_manager_deactivate(){}
function smart_choice_core_manager_protect_itself($modules){ $modules[] = SMART_CHOICE_CORE_MANAGER_MODULE_NAME; return array_unique($modules); }

function smart_choice_core_manager_admin_init(){
    $CI=&get_instance();
    if(is_admin()){
        $CI->app_menu->add_setup_menu_item('smart-choice-core-manager',[
            'name'=>_l('sc_core_manager'),'href'=>admin_url('smart_choice_core_manager'),'position'=>3,'icon'=>'fa-solid fa-shield-halved'
        ]);
    }
}
function smart_choice_core_manager_head(){
    echo '<link rel="stylesheet" href="'.module_dir_url(SMART_CHOICE_CORE_MANAGER_MODULE_NAME,'assets/css/core-manager.css?v=100').'">';
    $vars=[
      'logoHeight'=>(int)get_option('sc_nav_logo_height_percent'),
      'menuColor'=>get_option('sc_menu_background_color'),
      'menuText'=>get_option('sc_menu_text_color'),
      'currencyPrefix'=>get_option('sc_currency_prefix'),
      'currencySuffix'=>get_option('sc_currency_suffix'),
    ];
    echo '<script>window.SmartChoiceCore='.json_encode($vars).';</script>';
}
function smart_choice_core_manager_footer(){
    echo '<script src="'.module_dir_url(SMART_CHOICE_CORE_MANAGER_MODULE_NAME,'assets/js/core-manager.js?v=100').'"></script>';
}
function smart_choice_core_manager_validate_module($module){
    if(!get_option('sc_require_module_manifest')) return;
    $name=is_array($module)?($module['system_name']??''):(string)$module;
    if(!$name || $name===SMART_CHOICE_CORE_MANAGER_MODULE_NAME) return;
    $path=module_dir_path($name,'smart_choice_module_manifest.json');
    if(!file_exists($path)){
        set_alert('warning','This module does not include the Smart Choice impact manifest. Review its CSS, JavaScript, database tables, and core-file impact before activation.');
    }
}

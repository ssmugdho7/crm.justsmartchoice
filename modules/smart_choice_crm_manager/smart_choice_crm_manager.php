<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice CRM Manager
Description: Safely validates, backs up, and applies approved Smart Choice CRM core update packages.
Version: 1.0.0
Requires at least: 3.4.1
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
*/
define('SMART_CHOICE_CRM_MANAGER','smart_choice_crm_manager');
register_activation_hook(SMART_CHOICE_CRM_MANAGER,'smart_choice_crm_manager_activate');
register_language_files(SMART_CHOICE_CRM_MANAGER,[SMART_CHOICE_CRM_MANAGER]);
hooks()->add_action('admin_init','smart_choice_crm_manager_menu');
function smart_choice_crm_manager_activate(){require_once __DIR__.'/install.php';}
function smart_choice_crm_manager_menu(){
 $CI=&get_instance(); if(!is_admin())return;
 $CI->app_menu->add_setup_menu_item('smart-choice-crm-manager',['name'=>_l('smart_choice_crm_manager'),'href'=>admin_url('smart_choice_crm_manager'),'icon'=>'fa-solid fa-shield-halved','position'=>94]);
}

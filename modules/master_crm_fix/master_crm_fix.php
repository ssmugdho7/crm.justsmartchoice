<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Master CRM Fix
Description: Creates segmented full CRM backups, database exports, manifests, archive reports, and recovery instructions.
Version: 1.0.0
Requires at least: 3.4.1
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
*/
define('MASTER_CRM_FIX_MODULE_NAME','master_crm_fix');
define('MASTER_CRM_FIX_VERSION','1.0.0');
register_activation_hook(MASTER_CRM_FIX_MODULE_NAME,'master_crm_fix_activate');
hooks()->add_action('admin_init','master_crm_fix_permissions');
hooks()->add_action('admin_init','master_crm_fix_menu');
hooks()->add_action('app_admin_head','master_crm_fix_assets');
register_language_files(MASTER_CRM_FIX_MODULE_NAME,[MASTER_CRM_FIX_MODULE_NAME]);

function master_crm_fix_activate(){require_once __DIR__.'/install.php';}
function master_crm_fix_permissions(){
 register_staff_capabilities(MASTER_CRM_FIX_MODULE_NAME,['capabilities'=>[
  'view'=>_l('permission_view'),'create'=>_l('permission_create'),'delete'=>_l('permission_delete')
 ]],_l('master_crm_fix'));
}
function master_crm_fix_menu(){
 $CI=&get_instance();
 if(staff_can('view',MASTER_CRM_FIX_MODULE_NAME)){
  $CI->app_menu->add_setup_menu_item('master-crm-fix',[
   'name'=>_l('master_crm_fix'),'href'=>admin_url('master_crm_fix'),
   'position'=>95,'icon'=>'fa-solid fa-box-archive'
  ]);
 }
}
function master_crm_fix_assets(){
 $CI=&get_instance();
 if(is_admin() && strpos((string)$CI->uri->uri_string(),'master_crm_fix')!==false){
  echo '<link rel="stylesheet" href="'.module_dir_url(MASTER_CRM_FIX_MODULE_NAME,'assets/css/master_crm_fix.css').'?v='.MASTER_CRM_FIX_VERSION.'">';
 }
}

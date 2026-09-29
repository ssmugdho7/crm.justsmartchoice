<?php
/**
 * Module Name: Smart Choice Core Enhancements
 * Description: Managed settings, utilities, webhooks, custom JavaScript, PDF controls, module setting links, and CRM structure export.
 * Version: 1.4.0
 * Author: Smart Choice Contractors USA / Harold Cabrera
 */
defined('BASEPATH') or exit('No direct script access allowed');
define('SCCE_MODULE','smart_choice_core_enhancements');
register_activation_hook(SCCE_MODULE,'scce_activate');
register_language_files(SCCE_MODULE,[SCCE_MODULE]);
hooks()->add_action('admin_init','scce_admin_init');
hooks()->add_action('app_admin_head','scce_custom_js_admin');
hooks()->add_action('app_customers_head','scce_custom_js_client');
function scce_activate(){ require_once __DIR__.'/install.php'; scce_install(); }
function scce_admin_init(){
 $CI=&get_instance();
 register_staff_capabilities(SCCE_MODULE,['capabilities'=>['view_own'=>_l('permission_view_own'),'view'=>_l('permission_view').' ('._l('permission_global').')','create'=>_l('permission_create'),'edit'=>_l('permission_edit'),'delete'=>_l('permission_delete')]],'Smart Choice Core Enhancements');
 if(is_admin()){
  $CI->app->add_settings_section('smart_choice_core_enhancements',['name'=>'Smart Choice Core Enhancements','view'=>'smart_choice_core_enhancements/settings','position'=>95,'icon'=>'fa-solid fa-sliders']);
  $CI->app->add_settings_section('payroll_hub',['name'=>'Payroll Hub','view'=>'smart_choice_core_enhancements/module_settings_missing','position'=>96,'icon'=>'fa-solid fa-money-check-dollar']);
  $CI->app->add_settings_section('purchasing_hub',['name'=>'Purchasing Hub','view'=>'smart_choice_core_enhancements/module_settings_missing','position'=>97,'icon'=>'fa-solid fa-cart-shopping']);
  $CI->app->add_settings_section('blogging',['name'=>'Blogging','view'=>'smart_choice_core_enhancements/module_settings_missing','position'=>98,'icon'=>'fa-solid fa-blog']);
 }
 $CI->app_menu->add_setup_menu_item('scce-utilities',['name'=>'Utilities','href'=>admin_url('smart_choice_core_enhancements/calculator'),'position'=>98,'icon'=>'fa-solid fa-calculator']);
 $CI->app_menu->add_sidebar_menu_item('scce-contact-search',['name'=>'Contact Search','href'=>admin_url('smart_choice_core_enhancements/contacts'),'position'=>18,'icon'=>'fa-solid fa-address-book']);
 $CI->app_menu->add_sidebar_menu_item('scce-report-center',['name'=>'Reports','href'=>admin_url('smart_choice_core_enhancements/reports'),'position'=>57,'icon'=>'fa-solid fa-chart-column']);
}
function scce_custom_js_admin(){ $js=get_option('scce_custom_js_admin'); if($js!=='') echo '<script>'.$js.'</script>'; }
function scce_custom_js_client(){ $js=get_option('scce_custom_js_client'); if($js!=='') echo '<script>'.$js.'</script>'; }

function scce_dashboard_widgets($widgets){
 $widgets[]=['path'=>'smart_choice_core_enhancements/dashboard/clock','container'=>'right-4'];
 $widgets[]=['path'=>'smart_choice_core_enhancements/dashboard/calculator','container'=>'right-4'];
 return $widgets;
}

function scce_world_clocks_nav(){
 foreach([['in','India','Asia/Kolkata'],['pk','Pakistan','Asia/Karachi'],['ph','Philippines','Asia/Manila']] as $c){
  echo '<li class="icon scce-world-clock-item"><a href="#" onclick="return false;" data-toggle="tooltip" data-placement="bottom" title="'.html_escape($c[1]).' time"><i class="fa-solid fa-earth-asia"></i><span class="scce-clock-code">'.strtoupper($c[0]).'</span><span class="scce-clock-time" data-zone="'.html_escape($c[2]).'">--:--</span></a></li>';
 }
}
function scce_world_clocks_css(){ echo '<style>.scce-world-clock-item>a{display:flex!important;align-items:center;gap:3px;padding-left:6px!important;padding-right:6px!important}.scce-world-clock-item i{color:#3598db}.scce-clock-code{font-size:9px;font-weight:700;color:#169179}.scce-clock-time{font-size:11px;white-space:nowrap;color:#475569}@media(max-width:1100px){.scce-clock-code{display:none}.scce-world-clock-item>a{padding-left:4px!important;padding-right:4px!important}}@media(max-width:767px){.scce-world-clock-item{display:none!important}}</style>'; }
function scce_world_clocks_js(){ echo '<script>(function(){function u(){document.querySelectorAll(".scce-clock-time").forEach(function(e){try{e.textContent=new Intl.DateTimeFormat(undefined,{timeZone:e.dataset.zone,hour:"numeric",minute:"2-digit",hour12:true}).format(new Date())}catch(x){e.textContent="--:--"}})}u();setInterval(u,30000)})();</script>'; }

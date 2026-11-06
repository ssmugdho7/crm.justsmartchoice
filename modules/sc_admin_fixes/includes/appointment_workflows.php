<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once dirname(__DIR__,2).'/appointly/helpers/workflow_helper.php';
hooks()->add_filter('app_view_data','scaf_appointment_workflows_view',50);
hooks()->add_action('app_admin_head','scaf_appointment_places');
function scaf_appointment_places(){
 $CI=&get_instance();if($CI->uri->segment(2)!=='appointly')return;
 $key=get_option('google_api_key');if($key)echo '<script>window.scafPlacesKey='.json_encode($key,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';</script>';
}
function scaf_appointment_workflows_view($view){
 $CI=&get_instance();$path=preg_replace('/\.php$/','',$view['path']);
 if(strtolower($CI->router->fetch_class())==='estimates' && $path==='admin/estimates/estimate' && $CI->input->get('from_appointment') && empty($view['data']['estimate'])){
  $CI->load->helper('appointly/appointly');$prefill=appointly_estimate_prefill((string)$CI->input->get('from_appointment'));
  if(!$prefill || !(staff_can('view','customers') || is_customer_admin($prefill['customer_id'])))access_denied('appointments');
  $view['data']['customer_id']=$prefill['customer_id'];$view['data']['appointment_prefill']=$prefill;
  $view['path']='../../modules/sc_admin_fixes/views/appointments/estimate_handoff';
 }
 return $view;
}

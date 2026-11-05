<?php
defined('BASEPATH') or exit('No direct script access allowed');
hooks()->add_filter('app_view_data','scaf_appointments_views',45);
function scaf_appointments_views($view) {
    $CI=&get_instance();if(!is_staff_logged_in() || $CI->uri->segment(2)!=='appointly')return $view;
    $map=['appointly/index'=>'index','appointly/tables/appointment'=>'detail','appointly/pages/update'=>'update','appointly/services/manage'=>'services_manage','appointly/services/service'=>'service','appointly/pages/create'=>'create','appointly/users/index'=>'users','appointly/callbacks/index'=>'callbacks','appointly/history_view'=>'history'];
    foreach(array_keys($map) as $key)$map[substr($key,strlen('appointly/'))]=$map[$key];
    $path=preg_replace('/\.php$/','',$view['path']);if(isset($map[$path]))$view['path']='../../modules/sc_admin_fixes/views/appointments/'.$map[$path];
    return $view;
}

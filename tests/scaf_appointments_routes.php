<?php
define('BASEPATH',true);$section='appointly';
function hooks(){return new class{function add_filter(...$args){}function add_action(...$args){}};}function is_staff_logged_in(){return true;}
$ci=(object)['uri'=>new class{function segment($n){global $section;return $section;}}];function &get_instance(){global $ci;return $ci;}
require __DIR__.'/../modules/sc_admin_fixes/includes/appointments.php';
foreach(['index','tables/appointment','pages/create','pages/update','services/manage','services/service','users/index','callbacks/index','history_view'] as $path){$view=scaf_appointments_views(['path'=>$path,'data'=>['sentinel'=>'retained']]);if(strpos($view['path'],'sc_admin_fixes/views/appointments')===false||$view['data']['sentinel']!=='retained')throw new Exception('Native view whitelist failed '.$path);}
$section='clients';$view=scaf_appointments_views(['path'=>'index','data'=>[]]);if($view['path']!=='index')throw new Exception('Non-appointment route overridden');
$section='appointly';$view=scaf_appointments_views(['path'=>'client_appointments_dashboard','data'=>[]]);if($view['path']!=='client_appointments_dashboard')throw new Exception('Customer view overridden');
echo "PASS fixed admin view whitelist, native data retained, non-module/customer routes isolated\n";

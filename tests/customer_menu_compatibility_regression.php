<?php
define('BASEPATH',true);
require $argv[1] ?? dirname(__DIR__).'/application/libraries/App_menu.php';
class CustomerMenuHooksFixture {
 public $events=[];
 function add_action($event,$callback,$priority=10,$args=1){$this->events[$event][]=$callback;}
 function add_filter($event,$callback,$priority=10,$args=1){}
 function apply_filters($event,$value){return $value;}
}
$CI=(object)[];$hooks=new CustomerMenuHooksFixture();$customer=false;
function &get_instance(){return $GLOBALS['CI'];}
function hooks(){return $GLOBALS['hooks'];}
function app_fill_empty_common_attributes($item){return array_merge(['position'=>10,'icon'=>''],$item);}
function app_sort_by_position($items,$children=false){uasort($items,fn($a,$b)=>($a['position']??10)<=>($b['position']??10));return array_values($items);}
function is_client_logged_in(){return $GLOBALS['customer'];}
function register_language_files($module,$files){}
function register_activation_hook($module,$callback){}
function _l($key){return $key;}
function site_url($path=''){return 'https://crm.example.test/'.$path;}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS $message\n";}
// Optional installed-module fixture exercises the exact production clients_init callback.
if(isset($argv[2])){require $argv[2];$callback=$hooks->events['clients_init'][0];}
else{$callback=function(){if(is_client_logged_in())get_instance()->app_menu->add_theme_menu_item('task_signature',['name'=>'ts_my_requests','href'=>site_url('task_signature/portal')]);};}
$CI->app_menu=new App_menu();$callback();check($CI->app_menu->get_theme_items()===[],'Anonymous registration page has no customer-only menu');
$customer=true;$callback();$items=$CI->app_menu->get_theme_items();
check(count($items)===1&&$items[0]['slug']==='task_signature','Registered customer initialization does not crash');
check($items[0]['href']===site_url('task_signature/portal'),'Existing customer signature destination is retained');
$menu=new App_menu();$alias=$menu->add_theme_menu_item('alias',['name'=>'Alias','position'=>20]);
check($alias===$menu,'Compatibility method preserves fluent menu calls');
$menu->add_theme_item('native',['name'=>'Native','position'=>5]);
check(array_column($menu->get_theme_items(),'slug')===['native','alias'],'Native and legacy customer menus share sorting and storage');
$menu->add_sidebar_menu_item('staff',['name'=>'Staff']);$menu->add_setup_menu_item('settings',['name'=>'Settings']);
check(count($menu->get_sidebar_menu_items())===1&&count($menu->get_setup_menu_items())===1&&count($menu->get_theme_items())===2,'Staff and settings menus remain separate');

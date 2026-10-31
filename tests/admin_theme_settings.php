<?php
// Run without a live CRM, customer records, or settings database.
define('BASEPATH', __DIR__); define('FCPATH', dirname(__DIR__) . '/');
$options=[]; $writes=[]; $admin=true; $posted=[]; $method='post';
function get_option($key) {return $GLOBALS['options'][$key] ?? '';}
function update_option($key,$value) {$GLOBALS['writes'][]=$key;$GLOBALS['options'][$key]=$value;}
function is_admin(){return $GLOBALS['admin'];}
function _l($key){return $key;}
function admin_url($path){return '/admin/'.$path;}
function base_url($path){return '/'.$path;}
function html_escape($value){return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');}
function set_alert(...$args){}
function redirect($url){$GLOBALS['redirect']=$url;}
function access_denied($reason){throw new DomainException('denied');}
function show_404(){throw new DomainException('method');}
function show_error($reason,$code){throw new DomainException('invalid');}
class ThemeHooks {function add_action(...$args){} function add_filter(...$args){} function apply_filters($name,$value){return $name==='smartchoice_admin_themes'?array_merge($value,$GLOBALS['extraThemes']??[]):$value;}}
function hooks(){static $h;return $h??($h=new ThemeHooks());}
class AdminController {public $input;function __construct(){$this->input=new class {function method(){return $GLOBALS['method'];} function post($name){return $GLOBALS['posted'][$name]??null;}};}}
require FCPATH.'application/helpers/my_functions_helper.php';
require FCPATH.'modules/theme_style/controllers/Theme_style.php';
function check($ok,$message){if(!$ok)throw new RuntimeException($message);}
check(smartchoice_admin_theme()==='emerald','Existing installations retain Emerald');
ob_start();smartchoice_enqueue_modern_theme();$html=ob_get_clean();check(str_contains($html,'admin-themes/emerald.css'),'Default loads Emerald');
$c=new Theme_style();$posted=['admin_theme'=>'classic'];$c->select_admin_theme();
check($writes===['smartchoice_admin_theme'],'Does not overwrite color or customer settings');
check(smartchoice_admin_theme()==='classic'&&!smartchoice_admin_theme_is_modern(),'Reload returns saved Classic');
ob_start();smartchoice_enqueue_modern_theme();$html=ob_get_clean();check(!str_contains($html,'admin-themes/emerald.css')&&str_contains($html,'assets/css/custom.css'),'Classic retains shared repairs without modern CSS');
foreach(['../../x','https://evil.invalid/x',[],null] as $bad){$posted=['admin_theme'=>$bad];try{$c->select_admin_theme();throw new RuntimeException('Bad input accepted');}catch(DomainException $e){check($e->getMessage()==='invalid','Invalid input rejected');}}
check(count($writes)===1,'Invalid settings never persisted');
$posted=['admin_theme'=>'emerald'];$method='get';try{$c->select_admin_theme();throw new RuntimeException('GET changed settings');}catch(DomainException $e){check($e->getMessage()==='method','POST required');}
$admin=false;try{new Theme_style();throw new RuntimeException('Non-admin accepted');}catch(DomainException $e){check($e->getMessage()==='denied','Only admin may change theme');}
$options['smartchoice_admin_theme']='missing-theme';check(smartchoice_admin_theme()==='emerald','Removed themes fall back safely');
echo "PASS theme defaults, persistence/reload, Classic CSS isolation, unchanged color/customer settings, POST/admin authorization and invalid-key rejection\n";

// Trusted future themes appear through the existing registry, while settings stay keys only.
$extraThemes=['future'=>['label'=>'Future', 'stylesheet'=>'assets/css/admin-themes/emerald.css', 'modern'=>true]];
$admin=true;$method='post';$posted=['admin_theme'=>'future'];$c->select_admin_theme();
check(smartchoice_admin_theme()==='future'&&smartchoice_admin_theme_is_modern(),'Registered future theme can be selected');
ob_start();smartchoice_enqueue_modern_theme();$html=ob_get_clean();
check(str_contains($html,'id="sc-admin-theme-css"')&&str_contains($html,'admin-themes/emerald.css'),'Registered local stylesheet is loaded');
$extraThemes['future']['stylesheet']='https://evil.invalid/theme.css';
ob_start();smartchoice_enqueue_modern_theme();$html=ob_get_clean();
check(!str_contains($html,'evil.invalid'),'Remote stylesheet is rejected');
$extraThemes=[];
echo "PASS future theme registration and local stylesheet restriction\n";

// Changing presentation must never turn off expense authorization checks.
function &get_instance(){return $GLOBALS['fixtureCi'];}
function get_staff_user_id(){return 42;}
function staff_can($permission,$feature){return false;}
$fixtureCi=(object)[
 'router'=>new class{function fetch_class(){return 'expenses';}function fetch_method(){return 'delete';}},
 'uri'=>new class{function segment($n){return $n===4?'7':null;}},
 'db'=>new class{function where(...$a){return $this;}function get($name){return $this;}function row(){return (object)['addedfrom'=>99];}},
];
function db_prefix(){return 'test_';}
$options['smartchoice_admin_theme']='classic';
require FCPATH.'modules/sc_expenses_workspace/includes/expenses.php';
check(scct_expenses_context()&&!scct_expenses_presentation_context(),'Classic changes presentation context only');
check(scct_expenses_body([])===[],'Classic uses native expense body');
try{scct_expenses_admin_init();throw new RuntimeException('Classic skipped expense authorization');}catch(DomainException $e){check($e->getMessage()==='denied','Record access guard remains active');}
echo "PASS Classic retains expense record authorization and uses native views\n";

<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
require dirname(__DIR__).'/application/services/ValidatesContact.php';
class ClientsController { public $input,$misc_model,$clients_model; function __construct() {} }
function hooks() { return new class { function do_action($x,$data) {} }; }
function is_client_logged_in() { return $GLOBALS['loggedIn']; }
function get_contact_user_id() { return 9; }
function get_client_user_id() { return 7; }
function get_option($key) { return $GLOBALS['permission']; }
function show_error($message,$code) { throw new RuntimeException((string)$code); }
function show_404() { throw new RuntimeException('404'); }
function site_url($path) { return 'https://portal.example/'.$path; }
function redirect($url) { throw new RuntimeException($url); }
function set_alert($kind,$msg) {}
function _l($key,...$args) { return $key; }
require dirname(__DIR__).'/application/controllers/Clients.php';
$controller=new Clients();
$controller->input=new class { function method() { return $GLOBALS['method']; } };
$controller->misc_model=new class { function get_file($id) { return $GLOBALS['file']; } };
$controller->clients_model=new class { public $removed=[]; function delete_attachment($id) { $this->removed[]=$id; return true; } };
$own=(object)['rel_type'=>'customer','rel_id'=>7,'contact_id'=>9];
foreach ([
 ['get',true,1,44,$own,'405'],
 ['post',false,1,44,$own,'404'],
 ['post',true,0,44,$own,'404'],
 ['post',true,1,'44invalid',$own,'404'],
 ['post',true,1,0,$own,'404'],
 ['post',true,1,44,null,'404'],
 ['post',true,1,44,(object)['rel_type'=>'task','rel_id'=>7,'contact_id'=>9],'404'],
 ['post',true,1,44,(object)['rel_type'=>'customer','rel_id'=>8,'contact_id'=>9],'404'],
 ['post',true,1,44,(object)['rel_type'=>'customer','rel_id'=>7,'contact_id'=>10],'404'],
 ['post',true,1,44,$own,'https://portal.example/clients/files'],
] as [$method,$loggedIn,$permission,$id,$file,$expected]) {
 $controller->clients_model->removed=[];
 try { $controller->remove_uploaded_file($id); throw new RuntimeException('unexpected'); }
 catch (RuntimeException $e) { if ($e->getMessage()!==$expected) throw $e; }
 if ($controller->clients_model->removed!==($expected==='https://portal.example/clients/files'?[44]:[])) throw new RuntimeException('Unauthorized deletion');
}
echo "PASS ten customer removal authorization cases; no real files or records deleted\n";

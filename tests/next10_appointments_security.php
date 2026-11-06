<?php
define('BASEPATH',true);$method='GET';$token='';$canEdit=false;
function show_404(){throw new RuntimeException('METHOD');}function show_error($text,$code){throw new RuntimeException('TOKEN');}function access_denied($r){throw new RuntimeException('DENIED');}function staff_can($a,$r){global $canEdit;return $a==='edit'?$canEdit:false;}function get_staff_user_id(){return 8;}function is_admin(){return false;}function db_prefix(){return 'tbl';}
class AdminController{public $input,$session,$db,$workflow_model;}
class EndpointDB{public $reads=0;function where(...$a){return $this;}function get(...$a){$this->reads++;return $this;}function row_array(){return ['id'=>10,'created_by'=>9];}function row(){return null;}}
$ci=(object)['db'=>new EndpointDB];function &get_instance(){global $ci;return $ci;}
require __DIR__.'/../modules/appointly/helpers/workflow_helper.php';require __DIR__.'/../modules/appointly/controllers/Workflow.php';
$route=(new ReflectionClass('Workflow'))->newInstanceWithoutConstructor();$route->db=$ci->db;$route->session=new class{function userdata($key){return str_repeat('a',64);}};$route->input=new class{function method(){global $method;return $method;}function post($key){global $token;return $key==='workflow_token'?$token:'';}};
function reject($route,$id,$reason){try{$route->reschedule($id);throw new Exception('Unexpected mutation');}catch(RuntimeException $e){if($e->getMessage()!==$reason)throw $e;}}
reject($route,'10','METHOD');$method='POST';reject($route,'10','TOKEN');$token='wrong';reject($route,'10','TOKEN');if($route->db->reads)throw new Exception('Bad CSRF queried appointment');$token=str_repeat('a',64);reject($route,'x','METHOD');reject($route,'10','DENIED');
echo "PASS scheduling POST, per-session CSRF, numeric ID and unauthorized-record checks before mutation\n";

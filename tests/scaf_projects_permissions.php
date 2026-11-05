<?php
define('BASEPATH',true);$canView=false;$canCreate=false;$member=false;$method='GET';
function staff_can($action,$resource){global $canView,$canCreate;return $resource==='tasks'?$canCreate:$canView;}
function access_denied($resource){throw new RuntimeException('DENIED');}function show_404(){throw new RuntimeException('METHOD');}
class AdminController{public $input,$load,$projects_model;}
class FakeProjectInput{function method(){global $method;return $method;}}
class FakeProjectLoad{public $models=[];function model($name){$this->models[]=$name;}}
$instance=(object)['load'=>new FakeProjectLoad,'projects_model'=>new class{function get($id){return $id===72?(object)['id'=>72]:null;}function is_member($id){global $member;return $member;}}];
function &get_instance(){global $instance;return $instance;}
require __DIR__.'/../modules/sc_admin_fixes/controllers/Sc_admin_fixes.php';
$route=new Sc_admin_fixes;$route->input=new FakeProjectInput;$route->load=new FakeProjectLoad;
function expectDenied($route,$id,$reason){try{$route->project_scope($id);throw new Exception('Unexpected allow');}catch(RuntimeException $e){if($e->getMessage()!==$reason)throw $e;}}
expectDenied($route,'72','METHOD');$method='POST';expectDenied($route,'72','DENIED');
$canCreate=true;expectDenied($route,'72','DENIED');$member=true;expectDenied($route,'72 OR 1=1','DENIED');expectDenied($route,'999','DENIED');
if($route->load->models)throw new Exception('Denied requests reached mutation model');
echo "PASS scope endpoint rejects GET, missing task permission, unrelated project, invalid ID, and missing project before mutation model\n";

<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('BASEPATH',true);
#[AllowDynamicProperties] class AdminController {}
#[AllowDynamicProperties] class App_Model {}
$admin=false;$caps=[];$actor=7;$checks=0;
function is_admin(){return $GLOBALS['admin'];}
function staff_can($cap,$feature){return in_array($cap,$GLOBALS['caps'],true);}
function get_staff_user_id(){return $GLOBALS['actor'];}
function get_staff_full_name($id){return 'Fixture Staff';}
function _l($key){return $key;}
function db_prefix(){return 'tbl';}
function show_error($message,$status){throw new RuntimeException('HTTP '.$status);}
function show_404(){throw new RuntimeException('HTTP 404');}
function access_denied($feature){throw new RuntimeException('DENIED');}
function redirect($url){$GLOBALS['redirect']=$url;}
function admin_url($path=''){return '/admin/'.$path;}
function site_url($path=''){return '/'.$path;}
function module_dir_url($module,$path=''){return '/modules/'.$module.'/'.$path;}
function set_alert($type,$message){}
function log_message(...$args){}
function init_head(){} function init_tail(){}
function html_escape($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function form_open($url,$attributes=[]){return '<form method="post" action="'.html_escape($url).'" onsubmit="'.html_escape($attributes['onsubmit']??'').'"><input type="hidden" name="csrf" value="fixture">';}
function form_close(){return '</form>';}
function check($ok,$label){if(!$ok)throw new RuntimeException($label);$GLOBALS['checks']++;}
function denied($call,$message){try{$call();throw new RuntimeException('Expected '.$message);}catch(RuntimeException $e){check($e->getMessage()===$message,$message.' guard');}}
class FixtureInput {public $verb='POST';function method($upper=false){return $upper?$this->verb:strtolower($this->verb);}function post($name=null){return null;}}
class FixtureDB {
 public $meeting=['id'=>11,'host_type'=>'staff','host_id'=>7,'status'=>'ended'];public $reads=0,$deleted=[],$commits=0,$rollbacks=0,$begins=0,$fail=false;
 function where(...$args){return $this;}
 function get($table){$this->reads++;return $this;}
 function row_array(){return $this->meeting;}
 function trans_begin(){$this->begins++;}
 function table_exists($table){return true;}
 function delete($table){$this->deleted[]=$table;return !$this->fail;}
 function trans_status(){return !$this->fail;}
 function trans_commit(){$this->commits++;}
 function trans_rollback(){$this->rollbacks++;}
}
require __DIR__.'/../modules/video_meetings/controllers/Video_meetings.php';
require __DIR__.'/../modules/video_meetings/models/Video_meetings_model.php';
$c=(new ReflectionClass(Video_meetings::class))->newInstanceWithoutConstructor();$c->input=new FixtureInput;$c->db=new FixtureDB;
$c->video_meetings_model=new class {public $deleted=0,$allowed=false,$joined=0;function delete_meeting($id){$this->deleted++;return true;}function staff_allowed($id,$staff){return $this->allowed;}function get($id){return ['id'=>$id,'host_type'=>'staff','host_id'=>7,'title'=>'Fixture','status'=>'ended'];}function enforce_expiry($m){return $m;}function guest_url($m){return '/guest/fixture';}};
$c->input->verb='GET';denied(fn()=>$c->delete(11),'HTTP 405');check($c->db->reads===0,'GET must not read or mutate');$c->input->verb='POST';
denied(fn()=>$c->delete(11),'DENIED');check($c->video_meetings_model->deleted===0,'Missing manage denies deletion');
$caps=['manage'];$actor=8;denied(fn()=>$c->delete(11),'DENIED');check($c->video_meetings_model->deleted===0,'Other owner denied');
$actor=7;$c->delete(11);check($c->video_meetings_model->deleted===1,'Permitted owner can delete ended meeting');
$actor=8;$admin=true;$c->delete(11);check($c->video_meetings_model->deleted===2,'Administrator can delete any meeting');
$admin=false;$caps=[];denied(fn()=>$c->room(11),'DENIED');
$c->video_meetings_model->allowed=true;$c->security=new class {function get_csrf_token_name(){return 'csrf';}function get_csrf_hash(){return 'hash';}};
$c->load=new class {public $view;function view($name,$data){$this->view=$name;}};
$c->room(11);check($c->load->view==='video_meetings/room','Invited employee can use staff join link without module-wide view');
$m=(new ReflectionClass(Video_meetings_model::class))->newInstanceWithoutConstructor();$m->db=new FixtureDB;
check($m->delete_meeting(11),'Atomic cleanup success');check(count($m->db->deleted)===7&&$m->db->commits===1&&$m->db->rollbacks===0,'Delete cleans all six related tables and meeting');
$m->db=new FixtureDB;$m->db->fail=true;check(!$m->delete_meeting(11)&&$m->db->rollbacks===1&&$m->db->commits===0,'Cleanup failure rolls back');
$meetings=[['id'=>11,'host_type'=>'staff','host_id'=>7,'title'=>'Ended fixture','status'=>'ended','created_at'=>'2026-11-05']];
$admin=true;ob_start();include __DIR__.'/../modules/video_meetings/views/admin_list.php';$html=ob_get_clean();check(strpos($html,'method="post" action="/admin/video_meetings/delete/11"')!==false&&strpos($html,'name="csrf"')!==false,'Ended meeting has CSRF POST delete control');
$admin=false;$caps=[];ob_start();include __DIR__.'/../modules/video_meetings/views/admin_list.php';$html=ob_get_clean();check(strpos($html,'/delete/')===false,'Read-only staff receive no delete control');
$staff=[['staffid'=>7,'firstname'=>'Fixture','lastname'=>'<Admin>','email'=>'fixture@example.test']];$contacts=[];ob_start();include __DIR__.'/../modules/video_meetings/views/admin_create.php';$html=ob_get_clean();check(strpos($html,'name="staff_ids[]"')!==false&&strpos($html,'data-live-search="true"')!==false&&strpos($html,'Fixture &lt;Admin&gt;')!==false,'Employee list is searchable and escaped');
echo "PASS $checks legacy meeting POST, permission, owner/admin, invited staff, atomic cleanup, delete rendering and employee selector checks\n";

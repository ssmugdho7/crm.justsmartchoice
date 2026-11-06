<?php
define('BASEPATH',true);$canView=false;$canEdit=false;$staff=8;
function staff_can($a,$r){global $canView,$canEdit;return $a==='view'?$canView:$canEdit;}function is_admin(){return false;}function get_staff_user_id(){global $staff;return $staff;}function db_prefix(){return 'tbl';}function _dt($s){return $s;}function log_activity($s){}
class App_Model{public $db,$load,$appointly_model;}
class WorkflowFakeDB{
 public $fresh,$updates=[],$overlaps=0,$lock=1,$released=0,$whereCalls=[];
 function where(...$a){$this->whereCalls[]=$a;return $this;}function where_not_in(...$a){return $this;}function get(...$a){return $this;}function row_array(){return $this->fresh;}function row(){return null;}
 function query($sql,$args){if(strpos($sql,'RELEASE_LOCK')!==false){$this->released++;return $this;}return new class($this->lock){private $lock;function __construct($v){$this->lock=$v;}function row_array(){return ['acquired'=>$this->lock];}};}
 function count_all_results(...$a){return $a[0]==='tblappointly_service_staff'?1:$this->overlaps;}function update($t,$data){$this->updates[]=$data;$this->fresh=array_merge($this->fresh,$data);return $this;}function affected_rows(){return 1;}
}
$ci=(object)['db'=>new WorkflowFakeDB];function &get_instance(){global $ci;return $ci;}
require __DIR__.'/../modules/appointly/helpers/workflow_helper.php';require __DIR__.'/../modules/appointly/models/Workflow_model.php';
function check($ok,$why){if(!$ok)throw new RuntimeException($why);}
$a=['id'=>10,'created_by'=>8,'status'=>'pending','date'=>'2026-11-06','start_hour'=>'09:00','end_hour'=>'10:00','provider_id'=>8,'service_id'=>2,'timezone'=>'America/New_York'];
check(!appointly_valid_schedule('2026-02-31','09:00'),'Invalid realdate rejected');check(!appointly_valid_schedule('2026-11-06','25:01'),'Invalidtime rejected');
check(appointly_workflow_visible($a),'Owner visible');$a['created_by']=9;check(!appointly_workflow_visible($a),'Unrelated staff denied');$a['created_by']=8;
$model=new Workflow_model;$model->db=new WorkflowFakeDB;$model->db->fresh=$a;$model->load=new class{function model($n){}};$model->appointly_model=new class{public $slots=[];function get_available_time_slots(...$args){return $this->slots;}};
function denied($fn,$fragment){try{$fn();throw new Exception('Unexpected allow');}catch(RuntimeException $e){check(strpos($e->getMessage(),$fragment)!==false,'Wrong denial '.$e->getMessage());}}
$version=appointly_schedule_version($a);denied(function()use($model,$a){$model->reschedule($a,'2026-11-07','10:00','stale');},'changed');check(!$model->db->updates,'Stale version no mutation');
denied(function()use($model,$a,$version){$model->reschedule($a,'2026-11-07','10:00',$version);},'unavailable');check(!$model->db->updates,'Unavailable slot no mutation');
$model->appointly_model->slots=[['start'=>'10:00','end'=>'11:00','available'=>true]];$model->db->overlaps=1;denied(function()use($model,$a,$version){$model->reschedule($a,'2026-11-07','10:00',$version);},'conflicting');check(!$model->db->updates,'Conflict no mutation');
$model->db->overlaps=0;$result=$model->reschedule($a,'2026-11-07','10:00',$version);check($result['success']&&$model->db->updates[0]===['date'=>'2026-11-07','start_hour'=>'10:00','end_hour'=>'11:00'],'Only schedule columns updated');check($model->db->released===8,'All acquired locks released after all attempts');
$a['google_event_id']='foreign';$a['google_added_by_id']=9;denied(function()use($model,$a,$version){$model->reschedule($a,'2026-11-07','10:00',$version);},'owner');
$t=appointly_workflow_telemetry($a,[],[]);check($t['email']==='Not tracked'&&$t['sms']==='Not tracked','No fabricated telemetry');$t=appointly_workflow_telemetry($a,[['opened'=>1,'date_opened'=>'2026-11-06']], [['status'=>'failed']]);check(strpos($t['email'],'Opened')===0&&$t['sms']==='failed','Actual email/SMS statuses');
echo "PASS actual dates, record isolation, stale schedule version, availability/conflict denial, schedule-only update, lock release, foreign Google protection and empty/actual telemetry\n";

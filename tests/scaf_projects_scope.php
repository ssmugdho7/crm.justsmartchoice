<?php
define('BASEPATH',true);
require __DIR__.'/../modules/sc_admin_fixes/includes/project_data.php';
function assert_scope($ok,$message){if(!$ok)throw new RuntimeException($message);}
$tasks=scaf_scope_tasks('<p>Project scope</p><p>Day 1: Demolition</p><p>Remove old tile safely.</p><p>Day 3: Plumbing</p><p>Inspect rough-in.</p>','2026-02-27');
assert_scope(count($tasks)===2,'Two real headings');
assert_scope($tasks[1]['date']==='2026-03-01','Explicit Day 3 offset crosses month boundary');
assert_scope(strpos($tasks[0]['body'],'Remove old tile safely.')!==false,'Continuation lines retained');
assert_scope(scaf_scope_tasks('Plain text scope without a day heading','2026-01-01')===[],'No invented tasks from prose');
$repeat=scaf_scope_tasks("Day 1: Demolition\nDetails changed",'2026-02-27');
assert_scope($tasks[0]['marker']===$repeat[0]['marker'],'Stable identity across body edits');
try{scaf_scope_tasks('Day 0: Bad','2026-01-01');throw new Exception('Invalid day accepted');}catch(InvalidArgumentException $e){}
try{scaf_scope_tasks('Day 1: Bad','2026-02-31');throw new Exception('Invalid date accepted');}catch(InvalidArgumentException $e){}
// Exercise the actual model against in-memory storage; no native writes/emails/records.
function _d($date){return $date;} function html_escape($value){return htmlspecialchars($value,ENT_QUOTES);} function db_prefix(){return 'tbl';}
class App_Model{public $load,$db,$projects_model,$tasks_model;}
class ScopeDB{public $tasks=[];public $acquired=1;public $released=false;function select(...$a){return $this;}function where(...$a){return $this;}function get(...$a){return $this;}function result_array(){return $this->tasks;}function query($sql,$args){if(strpos($sql,'RELEASE_LOCK')!==false)$this->released=true;return $this;}function row_array(){return ['acquired'=>$this->acquired];}}
function &get_instance(){global $instance;return $instance;}
class ScopeFieldDB extends ScopeDB{function row_array(){return null;}}
$instance=(object)['db'=>new ScopeFieldDB];
require __DIR__.'/../modules/sc_admin_fixes/models/Sc_admin_fixes_model.php';
$model=new Sc_admin_fixes_model;$model->db=new ScopeDB;$model->load=new class{function model($name){}};
$model->projects_model=new class{function get($id){return(object)['id'=>$id,'start_date'=>'2026-02-27','description'=>"Day 1: Demolition\nDetails\nDay 3: Plumbing"];}};
$model->tasks_model=new class($model->db){private $db;function __construct($db){$this->db=$db;}function add($data){$this->db->tasks[]=$data;return count($this->db->tasks);}};
$result=$model->project_scope_convert(72);assert_scope($result===['created'=>2,'skipped'=>0],'Creates parsed native task payloads');
$result=$model->project_scope_convert(72);assert_scope($result===['created'=>0,'skipped'=>2],'Repeat conversion idempotent');
assert_scope($model->db->released,'Project lock released');assert_scope($model->db->tasks[0]['visible_to_client']===0,'No customer publication');
echo "PASS scope parse dates, identity, continuation, native payload, retry idempotency and lock release\n";

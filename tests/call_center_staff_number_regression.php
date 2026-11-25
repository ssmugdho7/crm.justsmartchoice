<?php
// Exercise incoming employee-number routing without placing calls or loading the app.
define('BASEPATH', __DIR__);
class App_Controller {public $db,$input,$output,$cc;}
function db_prefix(){return 'tbl';}
function sccc_phone($v){$d=preg_replace('/\D/','',(string)$v);return preg_match('/^1\d{10}$/',$d)?'+'.$d:'';}
function sccc_xml($v){return htmlspecialchars((string)$v,ENT_XML1|ENT_QUOTES,'UTF-8');}
function site_url($v){return 'https://crm.example.test/'.$v;}
function get_option($v){return $v==='sccc_record_calls'?'1':0;}
function _l($v){return $v;}
require dirname(__DIR__).'/modules/smart_choice_call_center/controllers/Webhook.php';
class StaffNumberDb {
 public $matches=[],$params;
 function query($sql,$params){$this->params=$params;return $this;}
 function result_array(){return $this->matches;}
}
class StaffNumberInput {public $data;function post($v){return $this->data[$v]??null;}}
class StaffNumberOutput {public $body;function set_content_type($v){return $this;}function set_output($v){$this->body=$v;return $this;}}
class StaffNumberCalls {public $created=[];function create_call($data){$this->created[]=$data;return 71;}}
function verify($ok,$message){if(!$ok)throw new RuntimeException($message);}
$w=(new ReflectionClass(Webhook::class))->newInstanceWithoutConstructor();
$w->db=new StaffNumberDb;$w->input=new StaffNumberInput;$w->output=new StaffNumberOutput;$w->cc=new StaffNumberCalls;
$w->input->data=['To'=>'+1 (715) 671-7259','From'=>'+17273792355','CallSid'=>'CAtest'];$w->db->matches=[['staff_id'=>45]];
$w->incoming();$xml=simplexml_load_string($w->output->body);
verify((string)$xml->Dial->Client==='staff_45','Assigned number must ring the assigned browser identity');
verify(!isset($xml->Gather),'Direct employee calls must bypass the shared IVR');
verify($w->db->params===['+17156717259'],'Lookup must normalize destination to E.164');
verify($w->cc->created[0]['staff_id']===45 && $w->cc->created[0]['direction']==='inbound','Call log must belong to receiving employee');
verify(strpos((string)$xml->Dial['action'],'browser_complete?call_id=71')!==false,'Direct call must persist completion status');
verify((string)$xml->Dial['record']==='record-from-answer-dual','Existing recording setting must be honored');
$lookup=new ReflectionMethod(Webhook::class,'assigned_staff_number');if(PHP_VERSION_ID<80100)$lookup->setAccessible(true);
$w->db->matches=[];verify($lookup->invoke($w,'+18559066053')===null,'Unassigned number must use the existing shared IVR');
$w->db->matches=[['staff_id'=>45],['staff_id'=>44]];verify($lookup->invoke($w,'+17156717259')===null,'Ambiguous assignment must not route to an arbitrary employee');
verify($lookup->invoke($w,'bad')===null,'Invalid destination must not resolve to an employee');
echo "PASS employee-number routing, call ownership, completion, recording, and ambiguous/unassigned numbers\n";

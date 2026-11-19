<?php
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__).'/application/');
require APPPATH.'vendor/autoload.php';
$root = dirname(__DIR__).'/modules/smart_choice_call_center/';
$options = ['sccc_twilio_account_sid'=>'ACfixture','sccc_twilio_auth_token'=>'fixture-token','sccc_main_number'=>'+15551234567','sccc_ai_enabled'=>'1','sccc_record_calls'=>'1'];
function get_option($key){global $options;return $options[$key]??'';}
function _l($key){return $key;}
function db_prefix(){return 'tbl';}
function site_url($path=''){return 'https://crm.example.test/'.$path;}
function get_staff_user_id(){return 7;}
$adminAllowed=true;function is_admin(){global $adminAllowed;return $adminAllowed;}
function staff_can($cap,$module,$id=null){global $adminAllowed;return $adminAllowed;}
function access_denied($module){throw new RuntimeException('403');}
function set_alert($type,$message){}
function admin_url($path){return site_url('admin/'.$path);}
function redirect($path){}
function show_404(){throw new RuntimeException('404');}
function show_error($message,$code=500){throw new RuntimeException((string)$code);}
function check($value,$message){if(!$value)throw new RuntimeException($message);echo "PASS $message\n";}
class App_Model {public $db;}
class App_Controller {public $db,$input,$output,$cc,$load; function __construct(){}}
class AdminController extends App_Controller {}
// Exercise the real helper validation and TwiML generation; substitute only the network boundary.
$helper=file_get_contents($root.'helpers/smart_choice_call_center_helper.php');
$helper=preg_replace('/function sccc_twilio_request\(/','function original_sccc_twilio_request(',$helper,1);
eval('?>'.$helper);
$requests=[];$providerOk=true;
function sccc_twilio_request($method,$path,$data=[]){global $requests,$providerOk;$requests[]=[$method,$path,$data];return $providerOk?['ok'=>true,'data'=>['sid'=>'CAfixture']]:['ok'=>false,'error'=>'Provider unavailable'];}
require $root.'models/Call_center_model.php';
require $root.'controllers/Webhook.php';
require $root.'controllers/Smart_choice_call_center.php';
class FixtureDb {
 public $tables=[],$filters=[],$lastId=0,$affected=0,$lockAvailable=true,$groups=[],$released=0;
 function predicate($k,$v){return function($r)use($k,$v){
  if(strpos($k,' IS NULL')!==false)return ($r[str_replace(' IS NULL','',$k)]??null)===null;
  if(preg_match('/^(.*) (<=|>=|<|>)$/',$k,$m)){$a=$r[$m[1]]??null;switch($m[2]){case '<':return $a<$v;case '>':return $a>$v;case '<=':return $a!==null&&$a<=$v;case '>=':return $a>=$v;}}
  return ($r[$k]??null)==$v;
 };}
 function where($k,$v=null,$escape=null){$this->filters[]=$this->predicate($k,$v);return $this;}
 function or_where($k,$v=null,$escape=null){$last=array_pop($this->filters);$next=$this->predicate($k,$v);$this->filters[]=fn($r)=>$last($r)||$next($r);return $this;}
 function where_in($k,$v){$this->filters[]=fn($r)=>in_array($r[$k]??null,$v,true);return $this;}
 function where_not_in($k,$v){$this->filters[]=fn($r)=>!in_array($r[$k]??null,$v,true);return $this;}
 function group_start(){$this->groups[]=$this->filters;$this->filters=[];return $this;}
 function group_end(){$group=$this->filters;$this->filters=array_pop($this->groups);$this->filters[]=function($r)use($group){foreach($group as $f)if(!$f($r))return false;return true;};return $this;}
 function order_by($k,$v){return $this;}
 function limit($n){return $this;}
 function matches($r){foreach($this->filters as $f)if(!$f($r))return false;return true;}
 function query($sql,$params){if(strpos($sql,'RELEASE_LOCK')!==false)$this->released++;return new class($this->lockAvailable){private $v;function __construct($v){$this->v=$v;}function row_array(){return ['acquired'=>$this->v?1:0];}};}
 function get($t){$rows=array_values(array_filter($this->tables[$t]??[],fn($r)=>$this->matches($r)));$this->filters=[];return new class($rows){private $rows;function __construct($r){$this->rows=$r;}function row_array(){return $this->rows[0]??null;}function result_array(){return $this->rows;}};}
 function count_all_results($t){return count($this->get($t)->result_array());}
 function insert($t,$d){$d['id']=++$this->lastId;$this->tables[$t][]=$d;return true;}
 function insert_id(){return $this->lastId;}
 function affected_rows(){return $this->affected;}
 function update($t,$d){$this->affected=0;foreach($this->tables[$t]??[] as $i=>$r){if($this->matches($r)){$this->tables[$t][$i]=array_merge($r,$d);$this->affected++;}}$this->filters=[];return true;}
}
class FixtureInput {public $get=[],$post=[];function get($k){return $this->get[$k]??null;}function post($k=null){return $k===null?$this->post:($this->post[$k]??null);}function method(){return 'post';}function get_request_header($k){return $_SERVER['HTTP_X_TWILIO_SIGNATURE']??'';}}
class FixtureOutput {public $body;function set_content_type($t){return $this;}function set_output($b){$this->body=$b;return $this;}}
$db=new FixtureDb;
$db->tables['tblsccc_ai_agents']=[['id'=>9,'active'=>1,'provider'=>'generic_stream','websocket_url'=>'wss://voice.example.test/stream','webhook_url'=>'','system_prompt'=>'Be helpful & concise.','voice_name'=>'alloy']];
$model=(new ReflectionClass(Call_center_model::class))->newInstanceWithoutConstructor();$model->db=$db;
$r=$model->place_call('+15557654321',7,null,null,9);
$request=end($requests)[2];
check(strpos($request['Url'],'/outgoing_twiml?')!==false,'Outbound call URL resolves to the existing TwiML action');
check($request['To']==='+15557654321','AI call connects the customer to the voice provider');
check($request['StatusCallbackEvent']===['initiated','ringing','answered','completed'],'Twilio status events are encoded as a repeated field');
$webhook=(new ReflectionClass(Webhook::class))->newInstanceWithoutConstructor();$webhook->db=$db;$webhook->input=new FixtureInput;$webhook->output=new FixtureOutput;
$webhook->input->get=['call_id'=>$r['call_id'],'ai_agent_id'=>9];$webhook->input->post=['CallSid'=>'CAfixture'];
$webhook->outgoing_twiml();$xml=simplexml_load_string($webhook->output->body);
check((string)$xml->Connect->Stream['url']==='wss://voice.example.test/stream','AI TwiML connects the configured WSS endpoint');
$params=[];foreach($xml->Connect->Stream->Parameter as $p)$params[(string)$p['name']]=(string)$p['value'];
check(($params['system_prompt']??null)==='Be helpful & concise.'&&($params['voice_name']??null)==='alloy','Stream forwards escaped instructions and voice configuration');
$db->tables['tblsccc_ai_agents'][0]['provider']='webhook';$db->tables['tblsccc_ai_agents'][0]['webhook_url']='https://voice.example.test/twiml?tenant=demo';
$webhook->outgoing_twiml();$xml=simplexml_load_string($webhook->output->body);
check(strpos((string)$xml->Redirect,'https://voice.example.test/twiml?tenant=demo&call_id=')===0,'Webhook AI provider receives a POST TwiML redirect with CRM context');
$options['sccc_ai_enabled']='0';$n=count($requests);$r=$model->place_call('+15557654321',7,null,null,9);
check(!$r['ok']&&count($requests)===$n,'Disabled AI cannot initiate a paid call');
$webhook->outgoing_twiml();$xml=simplexml_load_string($webhook->output->body);
check(isset($xml->Hangup)&&!isset($xml->Dial),'Unavailable AI hangs up rather than dialing the customer again');
$options['sccc_ai_enabled']='1';
check(sccc_ai_agent_error(['provider'=>'generic_stream','websocket_url'=>'https://wrong.example','system_prompt'=>'','voice_name'=>''])!==null,'Stream agents require WSS');
check(sccc_ai_agent_error(['provider'=>'webhook','webhook_url'=>'http://wrong.example'])!==null,'Webhook agents require HTTPS');
$n=count($requests);$r=$model->place_call('bad',7);check(!$r['ok']&&count($requests)===$n,'Invalid phone is rejected before provider or database side effects');
$model->place_call('+15557654321',7);check(end($requests)[2]['To']==='client:staff_7','Power calls ring the assigned staff browser before dialing the customer');
$options['sccc_twilio_auth_token']='';$n=count($requests);$r=$model->place_call('+15557654321',7);check(!$r['ok']&&count($requests)===$n,'Missing credentials fail before creating a call');$options['sccc_twilio_auth_token']='fixture-token';
$_SERVER['REQUEST_URI']='/smart_choice_call_center/webhook/status?call_id=1';
$post=['AccountSid'=>'ACfixture','CallSid'=>'CAfixture'];
$validator=new Twilio\Security\RequestValidator('fixture-token');
$sig=$validator->computeSignature('https://crm.example.test'.$_SERVER['REQUEST_URI'],$post);
check(sccc_valid_twilio_request($sig,$post),'Valid provider signature accepts the exact configured callback URL');
check(!sccc_valid_twilio_request($sig,$post+['CallStatus'=>'completed']),'Tampered callback payload is rejected');
check(!sccc_valid_twilio_request('',[]),'Unsigned webhook is rejected');
$providerOk=false;$r=$model->place_call('+15557654321',7);
check(!$r['ok']&&end($db->tables['tblsccc_calls'])['status']==='failed','Provider failure is persisted as a failed call');
echo "PASS call-center integration regressions without real phone calls\n";

// Campaign progression, retry limits, concurrency and duplicate provider deliveries.
$campaign=['id'=>12,'name'=>'Fixture','list_id'=>3,'mode'=>'ai','ai_agent_id'=>9,'status'=>'running','max_attempts'=>2,'retry_minutes'=>17,'daily_limit'=>2,'timezone'=>'UTC','business_start'=>'00:00','business_end'=>'00:00','staff_id'=>7,'created_by'=>7];
$contact=['id'=>50,'list_id'=>3,'phone'=>'+15557654321','attempts'=>0,'status'=>'ready','do_not_call'=>0,'next_attempt_at'=>null];
$db->tables['tblsccc_campaigns']=[$campaign];$db->tables['tblsccc_list_contacts']=[$contact];$db->tables['tblsccc_calls']=[];
$providerOk=false;$before=time();$model->process_campaign(12);
$saved=$db->tables['tblsccc_list_contacts'][0];
check($saved['status']==='ready'&&$saved['attempts']===1&&strtotime($saved['next_attempt_at'])>=$before+17*60,'Immediate provider failure releases contact and uses campaign retry interval');
$n=count($requests);$model->process_campaign(12);check(count($requests)===$n,'Contact is not retried before its due time');
$db->tables['tblsccc_list_contacts'][0]['next_attempt_at']=null;$model->process_campaign(12);
check($db->tables['tblsccc_list_contacts'][0]['status']==='failed','Failed contacts stop after max_attempts');
$db->tables['tblsccc_list_contacts']=[$contact];$n=count($requests);$model->process_campaign(12);check(count($requests)===$n,'Daily campaign limit blocks additional calls');
$db->tables['tblsccc_calls']=[];$db->lockAvailable=false;$model->process_campaign(12);check(count($requests)===$n,'A competing campaign worker cannot reserve or dial another contact');$db->lockAvailable=true;
$providerOk=true;$model->process_campaign(12);$n=count($requests);$model->process_campaign(12);check(count($requests)===$n,'An active call prevents overlapping campaign calls');
$id=end($db->tables['tblsccc_calls'])['id'];$webhook->cc=new class{public $finished=0,$advanced=0;function finish_campaign_contact($id,$c,$s){$this->finished++;}function process_campaign($id,$count){$this->advanced++;}};
$webhook->input->get=['call_id'=>$id];$webhook->input->post=['CallSid'=>'CAfixture','CallStatus'=>'completed','CallDuration'=>'12'];
ob_start();$webhook->status();$webhook->status();$webhook->input->post['CallStatus']='ringing';$webhook->status();ob_end_clean();
check($webhook->cc->finished===1&&$webhook->cc->advanced===1,'Duplicate terminal events advance a campaign only once');
check(end($db->tables['tblsccc_calls'])['status']==='completed'&&end($db->tables['tblsccc_calls'])['duration']===12,'Late ringing events cannot reopen or zero a completed call');
$options+=['sccc_api_key_sid'=>'SKfixture','sccc_api_key_secret'=>'fixture-secret','sccc_twiml_app_sid'=>'APfixture'];
$token=sccc_twilio_access_token('staff_7',false);$claims=json_decode(base64_decode(strtr(explode('.',$token)[1],'-_','+/')),true);
check(!isset($claims['grants']['voice']['outgoing'])&&$claims['grants']['voice']['incoming']['allow'],'Read-only staff token cannot make outgoing calls');
check(substr_count(sccc_form_encode(['StatusCallbackEvent'=>['initiated','completed']]),'StatusCallbackEvent=')===2,'HTTP form repeats callback event keys without bracket notation');
check(sccc_ai_agent_error(['provider'=>'generic_stream','websocket_url'=>'wss://voice.example.test?token=x'])!==null,'Twilio stream URLs cannot contain unsupported query parameters');
check($db->released>=1,'Campaign reservation lock is released after processing');

function expect_error(callable $action,string $code,string $message){try{$action();}catch(RuntimeException $e){check($e->getMessage()===$code,$message);return;}throw new RuntimeException($message);}
$webhook->input->post=['From'=>'client:staff_7','To'=>'invalid','CallSid'=>'CAfixture'];
expect_error(fn()=>$webhook->browser_outgoing(),'422','Browser webhook rejects invalid destinations before creating a call');
$webhook->input->post=['From'=>'staff_7-spoof','To'=>'+15557654321','CallSid'=>'CAfixture'];
expect_error(fn()=>$webhook->browser_outgoing(),'403','Browser webhook requires the exact authenticated staff identity');
class AdminInput extends FixtureInput {public $verb='post';function method(){return $this->verb;}}
$admin=(new ReflectionClass(Smart_choice_call_center::class))->newInstanceWithoutConstructor();$admin->input=new AdminInput;$admin->db=$db;$admin->cc=$model;
$admin->input->verb='get';expect_error(fn()=>$admin->campaign_status(12,'running'),'405','GET cannot start an autodial campaign');
$admin->input->verb='post';$adminAllowed=false;expect_error(fn()=>$admin->save_ai_agent(),'403','Staff without manage_ai cannot create agents');$adminAllowed=true;
$admin->input->post=['name'=>'Broken','provider'=>'generic_stream','websocket_url'=>'http://invalid'];$n=count($db->tables['tblsccc_ai_agents']);$admin->save_ai_agent();check(count($db->tables['tblsccc_ai_agents'])===$n,'Invalid AI endpoint is rejected by the save action');
$db->tables['tblsccc_lists']=[['id'=>3]];$admin->input->post=['name'=>'No agent','list_id'=>3,'mode'=>'ai'];$n=count($db->tables['tblsccc_campaigns']);$admin->save_campaign();check(count($db->tables['tblsccc_campaigns'])===$n,'AI campaign requires a usable agent before saving');

// Staff-leg completion does not prove the customer answered.
$db->tables['tblsccc_calls'][]=['id'=>999,'twilio_sid'=>'CAfixture','status'=>'in-progress','from_number'=>'+15551234567','to_number'=>'+15557654321'];
$webhook->input->get=['call_id'=>999];$webhook->input->post=['CallSid'=>'CAfixture','DialCallStatus'=>'busy','DialCallDuration'=>'0'];$webhook->browser_complete();
check(end($db->tables['tblsccc_calls'])['status']==='busy','Power/browser dial completion records the customer-leg outcome');
$webhook->input->post=['CallSid'=>'CAfixture','CallStatus'=>'completed','CallDuration'=>'5'];ob_start();$webhook->status();ob_end_clean();
check(end($db->tables['tblsccc_calls'])['status']==='busy','Completed staff leg cannot overwrite a busy customer-leg outcome');

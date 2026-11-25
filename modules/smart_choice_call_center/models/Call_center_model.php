<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Call_center_model extends App_Model {
 public function __construct(){parent::__construct();$this->load->helper('smart_choice_call_center/smart_choice_call_center');}
 public function get_employee_directory(){
  // These are the four staff accounts with provisioned direct employee lines.
  $p=db_prefix();$rows=$this->db->query("SELECT s.staffid AS staff_id,s.firstname,s.lastname,v.value AS phone FROM {$p}staff s JOIN {$p}customfieldsvalues v ON v.relid=s.staffid AND v.fieldto='staff' JOIN {$p}customfields f ON f.id=v.fieldid AND f.fieldto='staff' WHERE s.active=1 AND s.staffid IN (42,44,45,47) AND f.slug='staff_twilio_phone_number' ORDER BY s.firstname,s.lastname")->result_array();
  $directory=[];foreach($rows as $row){$phone=sccc_phone($row['phone']);if(!$phone)continue;$id=(int)$row['staff_id'];if(isset($directory[$id]))continue;$directory[$id]=['staff_id'=>$id,'name'=>trim(trim($row['firstname']).' '.trim($row['lastname'])),'phone'=>$phone];}
  return array_values($directory);
 }
 public function calls($limit=100,$own=false){if($own)$this->db->where('staff_id',get_staff_user_id());return $this->db->order_by('id','DESC')->limit($limit)->get(db_prefix().'sccc_calls')->result_array();}
 public function stats(){ $t=db_prefix().'sccc_calls'; $today=date('Y-m-d 00:00:00');$r=[];$r['total']=(int)$this->db->count_all($t);$r['today']=(int)$this->db->where('created_at >=',$today)->count_all_results($t);$r['answered']=(int)$this->db->where_in('status',['in-progress','completed'])->count_all_results($t);$r['successful']=(int)$this->db->join(db_prefix().'sccc_dispositions d','d.slug='.$t.'.disposition','left')->where('d.is_success',1)->count_all_results($t);$r['leads']=(int)$this->db->where('lead_id IS NOT NULL',null,false)->count_all_results($t);return $r; }
 public function get_lists(){return $this->db->order_by('id','DESC')->get(db_prefix().'sccc_lists')->result_array();}
 public function get_campaigns(){return $this->db->order_by('id','DESC')->get(db_prefix().'sccc_campaigns')->result_array();}
 public function get_extensions(){return $this->db->order_by('extension','ASC')->get(db_prefix().'sccc_extensions')->result_array();}
 public function get_greetings(){return $this->db->order_by('id','DESC')->get(db_prefix().'sccc_greetings')->result_array();}
 public function get_ai_agents(){return $this->db->order_by('id','DESC')->get(db_prefix().'sccc_ai_agents')->result_array();}
 public function create_call($d){$d['created_at']=date('Y-m-d H:i:s');$this->db->insert(db_prefix().'sccc_calls',$d);return $this->db->insert_id();}
 public function place_call($to,$staff_id=null,$campaign_id=null,$list_contact_id=null,$ai_agent_id=null){
  $to=sccc_phone($to);$from=sccc_phone(get_option('sccc_outbound_caller_id')?:get_option('sccc_main_number')); if(!$to||!$from)return ['ok'=>false,'error'=>_l('sccc_missing_phone')];
  if(!get_option('sccc_twilio_account_sid')||!get_option('sccc_twilio_auth_token'))return ['ok'=>false,'error'=>_l('sccc_twilio_not_configured')];
  if($ai_agent_id){$error=$this->ai_agent_error($ai_agent_id);if($error)return ['ok'=>false,'error'=>$error];}
  elseif(!(int)$staff_id)return ['ok'=>false,'error'=>_l('sccc_staff_required')];
  $id=$this->create_call(['staff_id'=>$staff_id,'campaign_id'=>$campaign_id,'list_contact_id'=>$list_contact_id,'direction'=>'outbound','from_number'=>$from,'to_number'=>$to,'status'=>'queued']);
  $url=site_url('smart_choice_call_center/webhook/outgoing_twiml?call_id='.$id.($ai_agent_id?'&ai_agent_id='.(int)$ai_agent_id:''));
  $status=site_url('smart_choice_call_center/webhook/status?call_id='.$id);
  $params=['To'=>$ai_agent_id?$to:'client:staff_'.(int)$staff_id,'From'=>$from,'Url'=>$url,'Method'=>'POST','StatusCallback'=>$status,'StatusCallbackMethod'=>'POST','StatusCallbackEvent'=>['initiated','ringing','answered','completed']];
  if($ai_agent_id && get_option('sccc_record_calls')==='1'){$params['Record']='true';$params['RecordingChannels']='dual';$params['RecordingStatusCallback']=site_url('smart_choice_call_center/webhook/recording?call_id='.$id);$params['RecordingStatusCallbackMethod']='POST';}
  $r=sccc_twilio_request('POST','Calls.json',$params);
  if($r['ok'] && !empty($r['data']['sid'])){$this->db->where('id',$id)->update(db_prefix().'sccc_calls',['twilio_sid'=>$r['data']['sid'],'updated_at'=>date('Y-m-d H:i:s')]);}
  else {$this->db->where('id',$id)->update(db_prefix().'sccc_calls',['status'=>'failed','notes'=>$r['error']??'']);}
  return $r+['call_id'=>$id];
 }
 public function ai_agent_error($id){
  if(get_option('sccc_ai_enabled')!=='1')return _l('sccc_ai_disabled');
  $agent=$this->db->where('id',(int)$id)->where('active',1)->get(db_prefix().'sccc_ai_agents')->row_array();
  return $agent?sccc_ai_agent_error($agent):_l('sccc_ai_agent_unavailable');
 }
 public function readiness(){
  $issues=[];
  foreach(['sccc_twilio_account_sid','sccc_twilio_auth_token','sccc_main_number','sccc_twiml_app_sid','sccc_api_key_sid','sccc_api_key_secret'] as $key){if(!get_option($key) && !($key==='sccc_main_number' && get_option('sccc_outbound_caller_id')))$issues[]=_l($key);}
  if(get_option('sccc_ai_enabled')!=='1')$issues[]=_l('sccc_ai_disabled');
  $usable=false;foreach($this->get_ai_agents() as $agent){if(!empty($agent['active'])&&!sccc_ai_agent_error($agent)){$usable=true;break;}}
  if(!$usable)$issues[]=_l('sccc_ai_agent_unavailable');
  return $issues;
 }
 public function process_active_campaigns($limit=10){$rows=$this->db->where('status','running')->limit($limit)->get(db_prefix().'sccc_campaigns')->result_array();foreach($rows as $c)$this->process_campaign((int)$c['id'],1);}
 public function process_campaign($id,$count=1){
  $id=(int)$id;
  // Cron and terminal callbacks may arrive together. Reserve only one call per campaign.
  $lock=db_prefix().'sccc_campaign_'.$id;
  $locked=$this->db->query('SELECT GET_LOCK(?, 0) AS acquired',[$lock])->row_array();
  if(empty($locked['acquired']))return;
  try {
   $c=$this->db->where('id',$id)->get(db_prefix().'sccc_campaigns')->row_array();
   if(!$c||$c['status']!=='running')return;
   if($c['mode']==='ai' && (empty($c['ai_agent_id'])||$this->ai_agent_error($c['ai_agent_id'])))return;
   if(!in_array($c['mode'],['ai','power'],true))return;
   if($this->db->where('campaign_id',$id)->where_in('status',['queued','initiated','ringing','in-progress'])->count_all_results(db_prefix().'sccc_calls'))return;
   try{$zone=new DateTimeZone($c['timezone']?:get_option('sccc_default_timezone')?:'UTC');}catch(Exception $e){return;}
   $now=new DateTimeImmutable('now',$zone);
   $start=$c['business_start']?:get_option('sccc_business_start');$end=$c['business_end']?:get_option('sccc_business_end');
   if($start&&$end){$time=$now->format('H:i:s');$start=substr($start,0,5).':00';$end=substr($end,0,5).':00';if($start<$end?($time<$start||$time>=$end):($start!==$end&&$time<$start&&$time>=$end))return;}
   $storageZone=new DateTimeZone(date_default_timezone_get());
   $day=$now->setTime(0,0);$next=$day->modify('+1 day');
   $today=$this->db->where('campaign_id',$id)->where('created_at >=',$day->setTimezone($storageZone)->format('Y-m-d H:i:s'))->where('created_at <',$next->setTimezone($storageZone)->format('Y-m-d H:i:s'))->count_all_results(db_prefix().'sccc_calls');
   if($today>=max(1,(int)$c['daily_limit']))return;
   $x=$this->db->where('list_id',$c['list_id'])->where('do_not_call',0)->where('status','ready')->where('attempts <',max(1,(int)$c['max_attempts']))->group_start()->where('next_attempt_at IS NULL',null,false)->or_where('next_attempt_at <=',date('Y-m-d H:i:s'))->group_end()->order_by('id','ASC')->limit(1)->get(db_prefix().'sccc_list_contacts')->row_array();
   if(!$x)return;
   $this->db->where('id',$x['id'])->where('status','ready')->update(db_prefix().'sccc_list_contacts',['status'=>'dialing','attempts'=>(int)$x['attempts']+1,'last_call_at'=>date('Y-m-d H:i:s')]);
   if($this->db->affected_rows()!==1)return;
   $result=$this->place_call($x['phone'],$c['staff_id']?:$c['created_by'],$id,$x['id'],$c['mode']==='ai'?$c['ai_agent_id']:null);
   if(empty($result['ok']))$this->finish_campaign_contact($x['id'],$c,'failed',(int)$x['attempts']+1);
  } finally {$this->db->query('SELECT RELEASE_LOCK(?)',[$lock]);}
 }
 public function finish_campaign_contact($contactId,array $campaign,string $status,?int $attempts=null){
  $contact=$this->db->where('id',(int)$contactId)->where('list_id',(int)$campaign['list_id'])->get(db_prefix().'sccc_list_contacts')->row_array();
  if(!$contact)return;
  $attempts=$attempts??(int)$contact['attempts'];
  $retry=$status!=='completed' && $attempts<max(1,(int)$campaign['max_attempts']);
  $this->db->where('id',$contact['id'])->update(db_prefix().'sccc_list_contacts',['status'=>$status==='completed'?'called':($retry?'ready':'failed'),'next_attempt_at'=>$retry?date('Y-m-d H:i:s',time()+max(1,(int)$campaign['retry_minutes'])*60):null]);
 }
 public function process_due_callbacks(){return;}
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Call_center_model extends App_Model {
 public function __construct(){parent::__construct();$this->load->helper('smart_choice_call_center/smart_choice_call_center');}
 public function calls($limit=100,$own=false){if($own)$this->db->where('staff_id',get_staff_user_id());return $this->db->order_by('id','DESC')->limit($limit)->get(db_prefix().'sccc_calls')->result_array();}
 public function stats(){ $t=db_prefix().'sccc_calls'; $today=date('Y-m-d 00:00:00');$r=[];$r['total']=(int)$this->db->count_all($t);$r['today']=(int)$this->db->where('created_at >=',$today)->count_all_results($t);$r['answered']=(int)$this->db->where_in('status',['in-progress','completed'])->count_all_results($t);$r['successful']=(int)$this->db->join(db_prefix().'sccc_dispositions d','d.slug='.$t.'.disposition','left')->where('d.is_success',1)->count_all_results($t);$r['leads']=(int)$this->db->where('lead_id IS NOT NULL',null,false)->count_all_results($t);return $r; }
 public function get_lists(){return $this->db->order_by('id','DESC')->get(db_prefix().'sccc_lists')->result_array();}
 public function get_campaigns(){return $this->db->order_by('id','DESC')->get(db_prefix().'sccc_campaigns')->result_array();}
 public function get_extensions(){return $this->db->order_by('extension','ASC')->get(db_prefix().'sccc_extensions')->result_array();}
 public function get_greetings(){return $this->db->order_by('id','DESC')->get(db_prefix().'sccc_greetings')->result_array();}
 public function get_ai_agents(){return $this->db->order_by('id','DESC')->get(db_prefix().'sccc_ai_agents')->result_array();}
 public function create_call($d){$d['created_at']=date('Y-m-d H:i:s');$this->db->insert(db_prefix().'sccc_calls',$d);return $this->db->insert_id();}
 public function place_call($to,$staff_id=null,$campaign_id=null,$list_contact_id=null,$ai_agent_id=null){
  $to=sccc_phone($to);$from=get_option('sccc_outbound_caller_id')?:get_option('sccc_main_number'); if(!$to||!$from)return ['ok'=>false,'error'=>_l('sccc_missing_phone')];
  $id=$this->create_call(['staff_id'=>$staff_id,'campaign_id'=>$campaign_id,'list_contact_id'=>$list_contact_id,'direction'=>'outbound','from_number'=>$from,'to_number'=>$to,'status'=>'queued']);
  $url=site_url('smart_choice_call_center/webhook/outbound_twiml?call_id='.$id.($ai_agent_id?'&ai_agent_id='.(int)$ai_agent_id:''));
  $status=site_url('smart_choice_call_center/webhook/status?call_id='.$id);
  $r=sccc_twilio_request('POST','Calls.json',['To'=>$to,'From'=>sccc_phone($from),'Url'=>$url,'StatusCallback'=>$status,'StatusCallbackEvent'=>'initiated ringing answered completed']);
  if($r['ok'] && !empty($r['data']['sid'])){$this->db->where('id',$id)->update(db_prefix().'sccc_calls',['twilio_sid'=>$r['data']['sid'],'updated_at'=>date('Y-m-d H:i:s')]);}
  else {$this->db->where('id',$id)->update(db_prefix().'sccc_calls',['status'=>'failed','notes'=>$r['error']??'']);}
  return $r+['call_id'=>$id];
 }
 public function process_active_campaigns($limit=10){$rows=$this->db->where('status','running')->limit($limit)->get(db_prefix().'sccc_campaigns')->result_array();foreach($rows as $c)$this->process_campaign((int)$c['id'],1);}
 public function process_campaign($id,$count=1){
  $c=$this->db->where('id',$id)->get(db_prefix().'sccc_campaigns')->row_array();if(!$c||$c['status']!=='running')return;
  for($i=0;$i<$count;$i++){$this->db->where('list_id',$c['list_id'])->where('do_not_call',0)->where('status','ready')->where('attempts <',(int)$c['max_attempts'])->group_start()->where('next_attempt_at IS NULL',null,false)->or_where('next_attempt_at <=',date('Y-m-d H:i:s'))->group_end()->order_by('id','ASC');$x=$this->db->get(db_prefix().'sccc_list_contacts')->row_array();if(!$x)return;$this->db->where('id',$x['id'])->update(db_prefix().'sccc_list_contacts',['status'=>'dialing','attempts'=>(int)$x['attempts']+1,'last_call_at'=>date('Y-m-d H:i:s')]);$this->place_call($x['phone'],$c['staff_id'],$c['id'],$x['id'],$c['mode']==='ai'?$c['ai_agent_id']:null);}
 }
 public function process_due_callbacks(){return;}
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Webhook extends App_Controller {
 public function __construct(){parent::__construct();$this->load->helper('smart_choice_call_center/smart_choice_call_center');if(!sccc_valid_twilio_request((string)$this->input->get_request_header('X-Twilio-Signature'),$this->input->post(null,false)?:[]))show_error('Invalid Twilio signature',403);$this->load->model('smart_choice_call_center/Call_center_model','cc');}
 private function xml($x){$this->output->set_content_type('text/xml')->set_output($x);}
 private function greeting($id){$g=$id?$this->db->where('id',(int)$id)->get(db_prefix().'sccc_greetings')->row_array():null;if($g&&$g['file_path'])return '<Play>'.sccc_xml(base_url($g['file_path'])).'</Play>';if($g&&$g['text_to_speech'])return '<Say voice="'.sccc_xml($g['voice']?:'Polly.Joanna').'" language="'.sccc_xml($g['language']?:'en-US').'">'.sccc_xml($g['text_to_speech']).'</Say>';return '<Say>'.sccc_xml(_l('sccc_default_greeting')).'</Say>';}
 private function assigned_staff_number($number){
  $number=sccc_phone($number);if($number==='')return null;
  $p=db_prefix();$rows=$this->db->query("SELECT DISTINCT v.relid AS staff_id FROM {$p}customfieldsvalues v JOIN {$p}customfields f ON f.id=v.fieldid JOIN {$p}staff s ON s.staffid=v.relid WHERE f.fieldto='staff' AND f.slug='staff_twilio_phone_number' AND v.fieldto='staff' AND v.value=? AND s.active=1 LIMIT 2",[$number])->result_array();
  return count($rows)===1?(int)$rows[0]['staff_id']:null;
 }
 public function incoming(){
  $staff=$this->assigned_staff_number($this->input->post('To'));
  if($staff){
   $id=$this->cc->create_call(['twilio_sid'=>$this->input->post('CallSid'),'staff_id'=>$staff,'direction'=>'inbound','from_number'=>$this->input->post('From'),'to_number'=>sccc_phone($this->input->post('To')),'status'=>'ringing']);
   $action=site_url('smart_choice_call_center/webhook/browser_complete?call_id='.$id);
   $record=get_option('sccc_record_calls')==='1'?' record="record-from-answer-dual" recordingStatusCallback="'.sccc_xml(site_url('smart_choice_call_center/webhook/recording?call_id='.$id)).'"':'';
   $this->xml('<?xml version="1.0" encoding="UTF-8"?><Response><Dial timeout="25" action="'.sccc_xml($action).'" method="POST"'.$record.'><Client>staff_'.$staff.'</Client></Dial></Response>');return;
  }
  $from=$this->input->post('From');$to=$this->input->post('To');$sid=$this->input->post('CallSid');$id=$this->cc->create_call(['twilio_sid'=>$sid,'direction'=>'inbound','from_number'=>$from,'to_number'=>$to,'status'=>'ringing']);$g=$this->greeting((int)get_option('sccc_main_greeting_id'));$action=site_url('smart_choice_call_center/webhook/ivr_route?call_id='.$id);$prompt=_l('sccc_ivr_prompt');$xml='<?xml version="1.0" encoding="UTF-8"?><Response>'.$g.'<Gather input="dtmf speech" numDigits="3" timeout="5" speechTimeout="auto" action="'.sccc_xml($action).'" method="POST"><Say>'.sccc_xml($prompt).'</Say></Gather><Redirect method="POST">'.sccc_xml($action).'</Redirect></Response>';$this->xml($xml);
 }
 public function ivr_route(){
  $call=(int)$this->input->get('call_id');$digits=trim((string)$this->input->post('Digits'));$speech=trim((string)$this->input->post('SpeechResult'));$ext=null;if($digits!=='')$ext=$this->db->where('extension',$digits)->where('active',1)->get(db_prefix().'sccc_extensions')->row_array();if(!$ext&&$speech!==''){$rows=$this->db->where('active',1)->get(db_prefix().'sccc_extensions')->result_array();foreach($rows as $r){if(stripos($speech,$r['display_name'])!==false){$ext=$r;break;}}}
  if(!$ext && $digits===get_option('sccc_operator_key') && get_option('sccc_operator_extension'))$ext=$this->db->where('extension',get_option('sccc_operator_extension'))->get(db_prefix().'sccc_extensions')->row_array();
  if($digits===get_option('sccc_callback_key') && get_option('sccc_enable_callbacks')==='1'){$this->db->insert(db_prefix().'sccc_callbacks',['call_id'=>$call,'phone'=>$this->input->post('From'),'status'=>'pending','created_at'=>date('Y-m-d H:i:s')]);$this->xml('<?xml version="1.0"?><Response><Say>'.sccc_xml(_l('sccc_callback_confirmed')).'</Say><Hangup/></Response>');return;}
  if(!$ext){$this->xml('<?xml version="1.0"?><Response><Say>'.sccc_xml(_l('sccc_extension_not_found')).'</Say><Redirect>'.sccc_xml(site_url('smart_choice_call_center/webhook/incoming')).'</Redirect></Response>');return;}
  $dest=$ext['destination_type']==='browser'?'client:staff_'.$ext['staff_id']:sccc_phone($ext['destination']);$record=get_option('sccc_record_calls')==='1'?' record="record-from-answer-dual" recordingStatusCallback="'.sccc_xml(site_url('smart_choice_call_center/webhook/recording?call_id='.$call)).'"':'';$dial='<Dial timeout="'.(int)$ext['ring_seconds'].'"'.$record.'><Number>'.sccc_xml($dest).'</Number></Dial>';if($ext['destination_type']==='browser')$dial='<Dial timeout="'.(int)$ext['ring_seconds'].'"'.$record.'><Client>staff_'.(int)$ext['staff_id'].'</Client></Dial>';$this->xml('<?xml version="1.0"?><Response><Say>'.sccc_xml(_l('sccc_connecting_to',$ext['display_name'])).'</Say>'.$dial.'</Response>');
 }
 public function outgoing_twiml(){
  $call=(int)$this->input->get('call_id');$ai=(int)$this->input->get('ai_agent_id');$r=$this->db->where('id',$call)->get(db_prefix().'sccc_calls')->row_array();if(!$r)show_404();
  if(!empty($r['twilio_sid']) && !hash_equals($r['twilio_sid'],(string)$this->input->post('CallSid')))show_error('Call does not match callback',403);
  if($ai){
   $a=$this->db->where('id',$ai)->where('active',1)->get(db_prefix().'sccc_ai_agents')->row_array();
   if(get_option('sccc_ai_enabled')!=='1'||!$a||sccc_ai_agent_error($a)){$this->xml('<?xml version="1.0"?><Response><Say>'.sccc_xml(_l('sccc_ai_agent_unavailable')).'</Say><Hangup/></Response>');return;}
   if($a['provider']==='crm_openai'){
    $this->load->library('smart_choice_call_center/Call_center_ai',null,'voice_ai');
    $this->xml($this->voice_ai->twiml($call,$ai,0,'Hello, this is the Smart Choice Contractors AI assistant. How can I help you today?'));return;
   }
   if($a['provider']==='webhook'){
    $url=$a['webhook_url'].(strpos($a['webhook_url'],'?')===false?'?':'&').http_build_query(['call_id'=>$call,'agent_id'=>$ai]);
    $this->xml('<?xml version="1.0"?><Response><Redirect method="POST">'.sccc_xml($url).'</Redirect></Response>');return;
   }
   $parameters='';foreach(['call_id'=>$call,'agent_id'=>$ai,'system_prompt'=>$a['system_prompt']??'','voice_name'=>$a['voice_name']??''] as $key=>$value)$parameters.='<Parameter name="'.$key.'" value="'.sccc_xml($value).'"/>';
   $this->xml('<?xml version="1.0"?><Response><Connect><Stream url="'.sccc_xml($a['websocket_url']).'">'.$parameters.'</Stream></Connect><Hangup/></Response>');return;
  }
  $record=get_option('sccc_record_calls')==='1'?' record="record-from-answer-dual" recordingStatusCallback="'.sccc_xml(site_url('smart_choice_call_center/webhook/recording?call_id='.$call)).'"':'';$action=site_url('smart_choice_call_center/webhook/browser_complete?call_id='.$call);$this->xml('<?xml version="1.0"?><Response><Dial'.$record.' action="'.sccc_xml($action).'" method="POST" callerId="'.sccc_xml($r['from_number']).'"><Number>'.sccc_xml($r['to_number']).'</Number></Dial></Response>');
 }
 public function ai_turn(){
  $call=(int)$this->input->get('call_id');$ai=(int)$this->input->get('ai_agent_id');$turn=(int)$this->input->get('turn');
  $lock=db_prefix().'sccc_ai_turn_'.$call;
  $acquired=$this->db->query('SELECT GET_LOCK(?, 2) AS acquired',[$lock])->row_array();if(empty($acquired['acquired']))show_error('Call turn busy',503);
  try{
   $r=$this->db->where('id',$call)->get(db_prefix().'sccc_calls')->row_array();
   if(!$r||empty($r['twilio_sid'])||!hash_equals($r['twilio_sid'],(string)$this->input->post('CallSid')))show_error('Call does not match callback',403);
   $a=$this->db->where('id',$ai)->where('active',1)->get(db_prefix().'sccc_ai_agents')->row_array();
   if(!$a||$a['provider']!=='crm_openai'||get_option('sccc_ai_enabled')!=='1')show_error('AI agent unavailable',403);
   $this->load->library('smart_choice_call_center/Call_center_ai',null,'voice_ai');
   $state=json_decode((string)($r['ai_summary']??''),true);if(!is_array($state)||($state['format']??'')!=='sccc_voice_v1')$state=['format'=>'sccc_voice_v1','agent_id'=>$ai,'next_turn'=>0,'history'=>[]];
   if((int)$state['agent_id']!==$ai)show_error('Agent does not match call',403);
   if($turn===(int)$state['next_turn']-1&&!empty($state['last_xml'])){$this->xml($state['last_xml']);return;}
   if($turn!==(int)$state['next_turn']||$turn<0||$turn>8)show_error('Invalid call turn',409);
   $speech=mb_substr(trim((string)$this->input->post('SpeechResult')),0,1000);
   $finish=$speech===''||$turn>=8||preg_match('/^(goodbye|bye|stop|stop calling|no thanks|not interested)[.! ]*$/i',$speech);
   if($finish)$reply=$speech===''?'I did not hear a response. Thank you for your time. Goodbye.':'Thank you for your time. Goodbye.';
   else{try{$reply=$this->voice_ai->reply($a,$state['history'],$speech);}catch(RuntimeException $e){$reply='I am sorry, the AI service is temporarily unavailable. Please contact our team for help. Goodbye.';$finish=true;$state['provider_error']=true;}}
   if($speech!=='')$state['history'][]=['role'=>'user','content'=>$speech];
   $state['history'][]=['role'=>'assistant','content'=>$reply];$state['history']=array_slice($state['history'],-16);
   $xml=$this->voice_ai->twiml($call,$ai,$turn+1,$reply,(bool)$finish);$state['next_turn']=$turn+1;$state['last_xml']=$xml;
   $this->db->where('id',$call)->update(db_prefix().'sccc_calls',['ai_summary'=>json_encode($state),'updated_at'=>date('Y-m-d H:i:s')]);
   $this->xml($xml);
  }finally{$this->db->query('SELECT RELEASE_LOCK(?)',[$lock]);}
 }
 public function browser_outgoing(){
  $to=$this->input->post('To')?:$this->input->get('To');$from=get_option('sccc_outbound_caller_id')?:get_option('sccc_main_number');$identity=(string)$this->input->post('From');$staff_id=0;if(preg_match('/^client:staff_([1-9][0-9]*)$/',$identity,$m))$staff_id=(int)$m[1];if(!$staff_id||!staff_can('create','smart_choice_call_center',$staff_id))show_error('Calling is not permitted',403);$to=sccc_phone($to);$from=sccc_phone($from);if(!$to||!$from)show_error('Invalid phone number',422);$id=$this->cc->create_call(['twilio_sid'=>$this->input->post('CallSid'),'staff_id'=>$staff_id?:null,'direction'=>'outbound','from_number'=>$from,'to_number'=>sccc_phone($to),'status'=>'in-progress','answered_at'=>date('Y-m-d H:i:s')]);$record=get_option('sccc_record_calls')==='1'?' record="record-from-answer-dual" recordingStatusCallback="'.sccc_xml(site_url('smart_choice_call_center/webhook/recording?call_id='.$id)).'"':'';$action=site_url('smart_choice_call_center/webhook/browser_complete?call_id='.$id);$this->xml('<?xml version="1.0"?><Response><Dial'.$record.' callerId="'.sccc_xml($from).'" action="'.sccc_xml($action).'"><Number>'.sccc_xml(sccc_phone($to)).'</Number></Dial></Response>'); }
 public function browser_complete(){
  $this->update_call_status((string)$this->input->post('DialCallStatus')?:'completed',max(0,(int)$this->input->post('DialCallDuration')),false);
  $this->xml('<?xml version="1.0"?><Response><Hangup/></Response>');
 }
 public function status(){
  $this->update_call_status((string)$this->input->post('CallStatus'),max(0,(int)$this->input->post('CallDuration')));echo 'OK';
 }
 private function update_call_status(string $status,int $duration,bool $advance=true){
  $id=(int)$this->input->get('call_id');$sid=(string)$this->input->post('CallSid');
  $terminal=['completed','busy','failed','no-answer','canceled'];
  if(!in_array($status,array_merge(['queued','initiated','ringing','in-progress'],$terminal),true))show_error('Invalid call status',422);
  $c=$this->db->where('id',$id)->get(db_prefix().'sccc_calls')->row_array();if(!$c)show_404();
  if($sid===''||(!empty($c['twilio_sid'])&&!hash_equals($c['twilio_sid'],$sid)))show_error('Call does not match callback',403);
  // Terminal deliveries are retried by Twilio; never reopen a completed call or redial twice.
  $d=['status'=>$status,'twilio_sid'=>$sid,'updated_at'=>date('Y-m-d H:i:s')];
  if($status==='in-progress')$d['answered_at']=date('Y-m-d H:i:s');
  if(in_array($status,$terminal,true)){$d['ended_at']=date('Y-m-d H:i:s');$d['duration']=$duration;}
  $this->db->where('id',$id)->where_not_in('status',$terminal)->update(db_prefix().'sccc_calls',$d);
  if($this->db->affected_rows()===1 && !empty($c['list_contact_id']) && !empty($c['campaign_id']) && in_array($status,$terminal,true)){
   $campaign=$this->db->where('id',(int)$c['campaign_id'])->get(db_prefix().'sccc_campaigns')->row_array();
   if($campaign){$this->cc->finish_campaign_contact($c['list_contact_id'],$campaign,$status);if($advance)$this->cc->process_campaign((int)$c['campaign_id'],1);}
  }
 }
 public function recording(){ $call=(int)$this->input->get('call_id');$sid=$this->input->post('RecordingSid');$url=$this->input->post('RecordingUrl');if($url){$this->db->insert(db_prefix().'sccc_recordings',['call_id'=>$call,'twilio_recording_sid'=>$sid,'recording_url'=>$url,'duration'=>(int)$this->input->post('RecordingDuration'),'channels'=>(int)$this->input->post('RecordingChannels')?:1,'created_at'=>date('Y-m-d H:i:s')]);}echo 'OK'; }
}

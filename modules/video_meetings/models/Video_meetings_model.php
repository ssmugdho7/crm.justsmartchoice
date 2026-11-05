<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Video_meetings_model extends App_Model {
 public function all_for_staff(){ return $this->db->order_by('id','DESC')->get(db_prefix().'video_meetings')->result_array(); }
 public function all_for_contact($cid){ $p=db_prefix(); return $this->db->query("SELECT DISTINCT m.* FROM {$p}video_meetings m LEFT JOIN {$p}video_meeting_invites i ON i.meeting_id=m.id WHERE (i.contact_id=? OR (m.host_type='contact' AND m.host_id=?)) ORDER BY m.id DESC",[$cid,$cid])->result_array(); }
 public function get($id){ $m=$this->db->where('id',(int)$id)->get(db_prefix().'video_meetings')->row_array(); if($m && empty($m['guest_token'])){$m['guest_token']=$this->new_token();$this->db->where('id',(int)$id)->update(db_prefix().'video_meetings',['guest_token'=>$m['guest_token']]);} return $m; }
 public function get_by_token($token){ if(!preg_match('/^[a-f0-9]{48}$/',$token))return false; return $this->db->where('guest_token',$token)->get(db_prefix().'video_meetings')->row_array(); }
 private function new_token(){ return bin2hex(random_bytes(24)); }
 public function create($title,$type,$host,$duration=null){ $key='crm-'.bin2hex(random_bytes(16)); $token=$this->new_token(); $this->db->insert(db_prefix().'video_meetings',['room_key'=>$key,'guest_token'=>$token,'title'=>$title,'host_type'=>$type,'host_id'=>$host,'status'=>'active','duration_minutes'=>$duration ? (int)$duration : null,'ends_at'=>$duration ? date('Y-m-d H:i:s',time()+((int)$duration*60)) : null,'created_at'=>date('Y-m-d H:i:s')]); return $this->db->insert_id(); }
 public function invite($mid,$cid){ if(!$cid)return; $this->db->replace(db_prefix().'video_meeting_invites',['meeting_id'=>(int)$mid,'contact_id'=>(int)$cid,'created_at'=>date('Y-m-d H:i:s')]); }
 public function contact_allowed($mid,$cid){ $m=$this->get($mid); if(!$m)return false; if($m['host_type']==='contact' && (int)$m['host_id']===(int)$cid)return true; return $this->db->where(['meeting_id'=>$mid,'contact_id'=>$cid])->count_all_results(db_prefix().'video_meeting_invites')>0; }
 public function end($id){ return $this->db->where('id',$id)->update(db_prefix().'video_meetings',['status'=>'ended','ended_at'=>date('Y-m-d H:i:s')]); }

 public function set_duration($id,$minutes){ $minutes=(int)$minutes; return $this->db->where('id',(int)$id)->update(db_prefix().'video_meetings',['duration_minutes'=>$minutes>0?$minutes:null,'ends_at'=>$minutes>0?date('Y-m-d H:i:s',time()+($minutes*60)):null]); }
 public function enforce_expiry($meeting){ if($meeting && $meeting['status']==='active' && !empty($meeting['ends_at']) && strtotime($meeting['ends_at'])<=time()){ $this->end($meeting['id']); $meeting['status']='ended'; } return $meeting; }
 public function guest_url($meeting){ return site_url('video_meetings/guest/join/'.$meeting['guest_token']); }
 public function create_join_request($meeting_id,$name){ $token=bin2hex(random_bytes(24)); $this->db->insert(db_prefix().'video_meeting_join_requests',['meeting_id'=>(int)$meeting_id,'request_token'=>$token,'guest_name'=>mb_substr(trim($name),0,100),'status'=>'pending','created_at'=>date('Y-m-d H:i:s')]); return $token; }
 public function get_join_request($token){ if(!preg_match('/^[a-f0-9]{48}$/',$token))return false; return $this->db->where('request_token',$token)->get(db_prefix().'video_meeting_join_requests')->row_array(); }
 public function pending_requests($meeting_id){ return $this->db->where(['meeting_id'=>(int)$meeting_id,'status'=>'pending'])->order_by('id','ASC')->get(db_prefix().'video_meeting_join_requests')->result_array(); }
 public function decide_request($request_id,$meeting_id,$status){ if(!in_array($status,['approved','denied'],true))return false; return $this->db->where(['id'=>(int)$request_id,'meeting_id'=>(int)$meeting_id,'status'=>'pending'])->update(db_prefix().'video_meeting_join_requests',['status'=>$status,'decided_at'=>date('Y-m-d H:i:s')]); }

 public function join_peer($meeting_id,$name,$type,$identity_id=null,$is_host=false){
  $peer=bin2hex(random_bytes(16)); $token=bin2hex(random_bytes(24)); $now=date('Y-m-d H:i:s');
  $this->db->insert(db_prefix().'video_meeting_participants',['meeting_id'=>(int)$meeting_id,'peer_id'=>$peer,'peer_token'=>$token,'display_name'=>mb_substr(trim($name),0,100),'identity_type'=>$type,'identity_id'=>$identity_id ? (int)$identity_id : null,'is_host'=>$is_host?1:0,'status'=>'active','last_seen'=>$now,'created_at'=>$now]);
  return ['peer_id'=>$peer,'peer_token'=>$token];
 }
 public function peer_by_token($token){ if(!preg_match('/^[a-f0-9]{48}$/',(string)$token))return false; return $this->db->where('peer_token',$token)->get(db_prefix().'video_meeting_participants')->row_array(); }
 public function active_peers($meeting_id){ $cut=date('Y-m-d H:i:s',time()-20); return $this->db->where('meeting_id',(int)$meeting_id)->where('status','active')->where('last_seen >=',$cut)->order_by('id','ASC')->get(db_prefix().'video_meeting_participants')->result_array(); }
 public function touch_peer($token){ return $this->db->where(['peer_token'=>$token,'status'=>'active'])->update(db_prefix().'video_meeting_participants',['last_seen'=>date('Y-m-d H:i:s')]); }
 public function send_signal($peer,$to,$type,$payload){ if(!$peer||$peer['status']!=='active')return false; if(!in_array($type,['offer','answer','ice','control'],true))return false; $target=$this->db->where(['meeting_id'=>$peer['meeting_id'],'peer_id'=>$to,'status'=>'active'])->get(db_prefix().'video_meeting_participants')->row_array(); if(!$target)return false; return $this->db->insert(db_prefix().'video_meeting_signals',['meeting_id'=>$peer['meeting_id'],'from_peer'=>$peer['peer_id'],'to_peer'=>$to,'signal_type'=>$type,'payload'=>$payload,'created_at'=>date('Y-m-d H:i:s')]); }
 public function signals($peer,$after=0){ return $this->db->where('meeting_id',$peer['meeting_id'])->where('to_peer',$peer['peer_id'])->where('id >',(int)$after)->order_by('id','ASC')->limit(100)->get(db_prefix().'video_meeting_signals')->result_array(); }
 public function leave_peer($token){ return $this->db->where('peer_token',$token)->update(db_prefix().'video_meeting_participants',['status'=>'left','last_seen'=>date('Y-m-d H:i:s')]); }
 public function kick_peer($meeting_id,$peer_id){ return $this->db->where(['meeting_id'=>(int)$meeting_id,'peer_id'=>$peer_id])->update(db_prefix().'video_meeting_participants',['status'=>'kicked']); }
 public function cleanup_signals(){ $this->db->where('created_at <',date('Y-m-d H:i:s',time()-3600))->delete(db_prefix().'video_meeting_signals'); }

 public function invite_staff($meeting_id,$staff_id){
  if(!$this->db->table_exists(db_prefix().'video_meeting_staff_invites'))throw new RuntimeException('Employee invitations need the Video Meetings schema update.');
  return $this->db->replace(db_prefix().'video_meeting_staff_invites',['meeting_id'=>(int)$meeting_id,'staff_id'=>(int)$staff_id,'created_at'=>date('Y-m-d H:i:s')]);
 }
 public function staff_allowed($meeting_id,$staff_id){
  $m=$this->db->where('id',(int)$meeting_id)->get(db_prefix().'video_meetings')->row_array();
  if(!$m)return false;
  if($m['host_type']==='staff' && (int)$m['host_id']===(int)$staff_id)return true;
  return $this->db->table_exists(db_prefix().'video_meeting_staff_invites') && $this->db->where(['meeting_id'=>(int)$meeting_id,'staff_id'=>(int)$staff_id])->count_all_results(db_prefix().'video_meeting_staff_invites')>0;
 }
 public function delete_meeting($meeting_id){
  $this->db->trans_begin();
  try{
   foreach(['video_meeting_signals','video_meeting_chat','video_meeting_participants','video_meeting_join_requests','video_meeting_invites','video_meeting_staff_invites'] as $table){
    if($this->db->table_exists(db_prefix().$table) && !$this->db->where('meeting_id',(int)$meeting_id)->delete(db_prefix().$table))throw new RuntimeException('Meeting cleanup failed.');
   }
   if(!$this->db->where('id',(int)$meeting_id)->delete(db_prefix().'video_meetings') || !$this->db->trans_status())throw new RuntimeException('Meeting deletion failed.');
   $this->db->trans_commit();return true;
  }catch(Throwable $e){$this->db->trans_rollback();log_message('error','Video meeting deletion failed.');return false;}
 }
}

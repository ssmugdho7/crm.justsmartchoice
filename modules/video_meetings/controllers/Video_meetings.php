<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Video_meetings extends AdminController {
 public function __construct(){ parent::__construct(); $this->load->model('video_meetings/video_meetings_model'); if(!is_admin() && !staff_can('view','video_meetings') && $this->router->fetch_method() !== 'room') access_denied(_l('vm_video_meetings')); }
 public function index(){ $data=['title'=>_l('vm_video_meetings'),'meetings'=>$this->video_meetings_model->all_for_staff()]; $this->load->view('video_meetings/admin_list',$data); }
 public function create(){
  if(!is_admin() && !staff_can('create','video_meetings')){access_denied(_l('vm_video_meetings'));return;}
  if($this->input->post()){
   $title=strtoupper(trim((string)$this->input->post('title')));
   if(!$title)$title=strtoupper(_l('vm_instant_meeting'));
   $duration=(int)$this->input->post('duration_minutes');
   if(!in_array($duration,[0,30,45,60],true))$duration=0;
   $staffIds=array_values(array_unique(array_filter(array_map('intval',(array)$this->input->post('staff_ids')))));
   if($staffIds && !$this->db->table_exists(db_prefix().'video_meeting_staff_invites')){show_error('Employee invitations need the Video Meetings schema update.',503);return;}
   foreach($staffIds as $staffId){
    if(!$this->db->where(['staffid'=>$staffId,'active'=>1])->count_all_results(db_prefix().'staff')){show_error('Select active employees from the list.',422);return;}
   }
   $this->db->trans_begin();
   try{
    $id=$this->video_meetings_model->create($title,'staff',get_staff_user_id(),$duration);
    if(!$id)throw new RuntimeException('Meeting creation failed.');
    foreach($staffIds as $staffId){if(!$this->video_meetings_model->invite_staff($id,$staffId))throw new RuntimeException('Employee invitation failed.');}
    foreach((array)$this->input->post('contacts') as $cid)$this->video_meetings_model->invite($id,(int)$cid);
    if(!$this->db->trans_status())throw new RuntimeException('Meeting creation failed.');
    $this->db->trans_commit();
   }catch(Throwable $e){$this->db->trans_rollback();log_message('error','Video meeting creation failed.');show_error('Meeting could not be created. Please try again.',500);return;}
   redirect(admin_url('video_meetings/room/'.$id));return;
  }
  $this->db->select('id,firstname,lastname,email,userid')->where('active',1);
  $data=['title'=>_l('vm_create_video_meeting'),'contacts'=>$this->db->get(db_prefix().'contacts')->result_array()];
  $data['staff']=$this->db->select('staffid,firstname,lastname,email')->where('active',1)->order_by('firstname','ASC')->get(db_prefix().'staff')->result_array();
  $this->load->view('video_meetings/admin_create',$data);
 }
 public function room($id){ if(!is_admin() && !staff_can('view','video_meetings') && !$this->video_meetings_model->staff_allowed((int)$id,get_staff_user_id())){access_denied(_l('vm_video_meeting'));return;} $m=$this->video_meetings_model->enforce_expiry($this->video_meetings_model->get($id)); if(!$m)show_404(); $host=$m['host_type']==='staff' && (int)$m['host_id']===(int)get_staff_user_id(); $peer=$m['status']==='active'?$this->video_meetings_model->join_peer($id,get_staff_full_name(get_staff_user_id()),'staff',get_staff_user_id(),$host):null; $data=['title'=>$m['title'],'meeting'=>$m,'is_host'=>$host,'display_name'=>get_staff_full_name(get_staff_user_id()),'share_url'=>$this->video_meetings_model->guest_url($m),'peer'=>$peer,'csrf_name'=>$this->security->get_csrf_token_name(),'csrf_hash'=>$this->security->get_csrf_hash()]; $this->load->view('video_meetings/room',$data); }
 public function end($id){ $m=$this->video_meetings_model->get($id); if(!$m)show_404(); $host=$m['host_type']==='staff' && (int)$m['host_id']===(int)get_staff_user_id(); if(!$host)access_denied(_l('vm_only_creator_end')); $this->video_meetings_model->end($id); echo json_encode(['success'=>true]); }
 public function kick($id,$peer_id){ $m=$this->video_meetings_model->get($id); if(!$m)show_404(); $host=$m['host_type']==='staff'&&(int)$m['host_id']===(int)get_staff_user_id(); if(!$host)access_denied(_l('vm_video_meeting')); $this->video_meetings_model->kick_peer($id,$peer_id); $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true])); }
 public function pending($id){ $m=$this->video_meetings_model->get($id); if(!$m)show_404(); $host=$m['host_type']==='staff'&&(int)$m['host_id']===(int)get_staff_user_id(); if(!$host)access_denied(_l('vm_video_meeting')); $this->output->set_content_type('application/json')->set_output(json_encode(['requests'=>$this->video_meetings_model->pending_requests($id)])); }
 public function decide($id,$request_id,$decision){ $m=$this->video_meetings_model->get($id); if(!$m)show_404(); $host=$m['host_type']==='staff'&&(int)$m['host_id']===(int)get_staff_user_id(); if(!$host)access_denied(_l('vm_video_meeting')); $status=$decision==='approve'?'approved':'denied'; $ok=$this->video_meetings_model->decide_request($request_id,$id,$status); $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>(bool)$ok])); }

 public function duration($id){ $m=$this->video_meetings_model->get($id); if(!$m)show_404(); $host=$m['host_type']==='staff'&&(int)$m['host_id']===(int)get_staff_user_id(); if(!$host)access_denied(_l('vm_video_meeting')); $d=(int)$this->input->post('duration_minutes'); if(!in_array($d,[0,30,45,60],true))$d=0; $this->video_meetings_model->set_duration($id,$d); $m=$this->video_meetings_model->get($id); $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true,'duration_minutes'=>$m['duration_minutes'],'ends_at'=>$m['ends_at']])); }
 public function delete($id){
  if($this->input->method(true)!=='POST'){show_error('POST required.',405);return;}
  $id=(int)$id;
  // Read directly: get() lazily creates guest tokens and must not mutate on denied requests.
  $meeting=$this->db->where('id',$id)->get(db_prefix().'video_meetings')->row_array();
  if(!$meeting){show_404();return;}
  if(!is_admin() && !(staff_can('manage','video_meetings') && $meeting['host_type']==='staff' && (int)$meeting['host_id']===(int)get_staff_user_id())){access_denied(_l('vm_video_meeting'));return;}
  if(!$this->video_meetings_model->delete_meeting($id)){set_alert('danger','Meeting could not be deleted.');}
  else set_alert('success','Meeting deleted.');
  redirect(admin_url('video_meetings'));
 }

}

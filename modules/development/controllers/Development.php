<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Development extends AdminController {
 public function __construct(){ parent::__construct(); $this->load->model('development/development_model'); if(!is_admin()&&!staff_can('view','development')&&!staff_can('view_own','development'))access_denied(_l('dev_development')); }
 private function can($c){ return is_admin()||staff_can($c,'development'); }
 private function staff(){ return $this->db->select('staffid,firstname,lastname,email')->where('active',1)->order_by('firstname')->get(db_prefix().'staff')->result_array(); }
 private function clean_url($u){ $u=trim((string)$u); if($u==='')return ''; return filter_var($u,FILTER_VALIDATE_URL)?$u:''; }
 private function links(){
  $input=$this->input->post('reference_links');
  $urls=[];
  foreach((array)$input as $url){$url=$this->clean_url($url);if($url!==''&&!in_array($url,$urls,true))$urls[]=$url;}
  return array_slice($urls,0,30);
 }
 private function notify_new_assignees($id,$before=[]){
  $item=$this->development_model->get($id);
  if(!$item || empty($item['assignees']))return;
  foreach($item['assignees'] as $a){
   $sid=(int)$a['staff_id'];
   if(in_array($sid,$before,true)||$sid===(int)get_staff_user_id())continue;
   // Native CRM notification, visible in the staff navigation bell.
   if(function_exists('add_notification')){
    $notification=['description'=>'dev_assigned_notification','touserid'=>$sid,'fromuserid'=>get_staff_user_id(),'link'=>'development/view/'.(int)$id,'additional_data'=>serialize([$item['title']])];
    if(add_notification($notification) && function_exists('pusher_trigger_notification'))pusher_trigger_notification([$sid]);
   }
   // Native CRM email template, delivered using the configured CRM mail transport.
   if(function_exists('send_mail_template')){
    try {send_mail_template('repair_assigned','development',$sid,$item['title'],admin_url('development/view/'.$id));}
    catch(Throwable $e){log_message('error','CRM Repair Note assignment email: '.$e->getMessage());}
   }
  }
 }
 public function index(){ $this->load->view('development/list',['title'=>_l('dev_development'),'items'=>$this->development_model->all()]); }
 public function create(){
  if(!$this->can('create'))access_denied(_l('dev_development'));
  if($this->input->post()){
   $title=trim((string)$this->input->post('title'));
   $desc=trim((string)$this->input->post('description'));
   if($title===''){$title=function_exists('mb_substr')?mb_substr($desc,0,120):substr($desc,0,120);if($title==='')$title=_l('dev_new_repair');}
   $urls=$this->links();
   // Persist the main record first. Never redirect to a missing record after an unsuccessful insert.
   try {
    $id=$this->development_model->create(['title'=>$title,'description'=>$desc,'reference_url'=>($urls[0]??''),'estimated_hours'=>$this->hours($this->input->post('estimated_hours'))]);
   } catch (Throwable $e) {
    log_message('error','CRM Repair Note create failed: '.$e->getMessage());
    set_alert('danger','Unable to save repair note. Please review CRM application logs.');
    redirect(admin_url('development/create')); return;
   }
   if(!$id){log_message('error','CRM Repair Note: failed to insert new record: '.json_encode($this->db->error()));set_alert('danger','Unable to create the repair note. Please check the CRM error log.');redirect(admin_url('development/create'));return;}
   // Secondary operations must not turn a successfully created note into an HTTP 500.
   try {
    if($this->can('assign'))$this->development_model->set_assignees($id,$this->input->post('assignees'));
    $this->development_model->set_reference_links($id,$urls);
    $saved=$this->upload_files($id,null);
    if($saved<count(array_filter((array)($_FILES['attachments']['name']??[]))))set_alert('warning',_l('dev_files_rejected'));
   }catch(Throwable $e){log_message('error','CRM Repair Note '.$id.' optional create data: '.$e->getMessage());}
   try {$this->notify_new_assignees($id);}catch(Throwable $e){log_message('error','CRM Repair Note '.$id.' notification: '.$e->getMessage());}
   set_alert('success',_l('dev_created'));
   redirect(admin_url('development'));return;
  }
  $this->load->view('development/form',['title'=>_l('dev_new_repair'),'item'=>null,'staff'=>$this->staff()]);
 }
 public function edit($id){ if(!$this->can('edit'))access_denied(_l('dev_development')); $item=$this->development_model->get($id);if(!$item)show_404(); $assignedIds=$this->development_model->assigned_ids($id); if($this->input->post()){ if(in_array($item['status'],['completed','approved'],true)){ $raw=$this->input->post('completed_by'); if(!is_scalar($raw)||!ctype_digit((string)$raw)||!(int)$raw||!in_array((int)$raw,$assignedIds,true)){set_alert('warning',_l('dev_completed_required'));redirect(admin_url('development/edit/'.$id));return;} $this->db->where('id',(int)$id)->update(db_prefix().'development_items',['completed_by'=>(int)$raw]); } $previous=$this->development_model->assigned_ids($id); $this->development_model->update_item($id,['title'=>trim((string)$this->input->post('title')),'description'=>trim((string)$this->input->post('description')),'reference_url'=>($this->links()[0]??''),'estimated_hours'=>$this->hours($this->input->post('estimated_hours'))]); if($this->can('assign'))$this->development_model->set_assignees($id,$this->input->post('assignees')); $this->development_model->set_reference_links($id,$this->links()); $this->notify_new_assignees($id,$previous); $this->upload_files($id,null); set_alert('success',_l('dev_saved'));redirect(admin_url('development/view/'.$id)); } $this->load->view('development/form',['title'=>_l('dev_edit_repair'),'item'=>$item,'staff'=>$this->staff()]); }
 public function view($id){ $item=$this->development_model->get($id);if(!$item)show_404();$this->load->view('development/view',['title'=>$item['title'],'item'=>$item,'staff'=>$this->staff()]); }
 public function note($id){ if($this->input->method(true)!=='POST')show_error('POST required',405); if(!$this->can('notes'))access_denied(_l('dev_development'));$item=$this->development_model->get($id);if(!$item)show_404();$note=trim((string)$this->input->post('note'));$next=trim((string)$this->input->post('next_step'));$sent=count(array_filter((array)($_FILES['attachments']['name']??[])));if($note!==''||$next!==''||$sent){$nid=$this->development_model->add_note($id,$note,$next);$saved=$this->upload_files($id,$nid);set_alert($saved<$sent?'warning':'success',_l($saved<$sent?'dev_files_rejected':'dev_note_added'));}else set_alert('warning',_l('dev_note_empty'));redirect(admin_url('development/view/'.$id)); }
 public function complete($id){ if($this->input->method(true)!=='POST'){show_error(_l('dev_complete_post_required'),405);return;} if(!$this->can('complete')&&!$this->can('edit'))access_denied(_l('dev_development'));$item=$this->development_model->get($id);if(!$item)show_404();if($this->input->method(true)!=='POST')show_error('POST required',405);$done=!in_array($item['status'],['completed','approved'],true); $sid=null;
 if($done){$raw=$this->input->post('completed_by'); $assignedIds=$this->development_model->assigned_ids($id); if(!is_scalar($raw)||!ctype_digit((string)$raw)||!(int)$raw||!in_array((int)$raw,$assignedIds,true)){set_alert('warning',_l('dev_completed_required'));redirect(admin_url('development/view/'.$id));return;} $sid=(int)$raw;}
 $this->development_model->complete($id,$done,$sid);redirect(admin_url('development/view/'.$id)); }
 public function approve($id){
  if(!$this->can('approve'))access_denied(_l('dev_development'));
  if($this->input->method(true)!=='POST')show_error('POST required',405);
  $item=$this->development_model->get($id);if(!$item)show_404();
  if($item['status']!=='completed'){set_alert('warning',_l('dev_approve_requires_complete'));}
  else {$this->development_model->approve($id);set_alert('success',_l('dev_approved'));}
  redirect(admin_url('development/view/'.$id));
 }
 public function delete($id){
  if(!$this->can('delete'))access_denied(_l('dev_development'));
  if($this->input->method(true)!=='POST'){ show_error(_l('dev_delete_post_required'),405); return; }
  if(!ctype_digit((string)$id)||(int)$id<1||!$this->development_model->get((int)$id)){ show_404(); return; }
  $deleted=$this->development_model->delete_item((int)$id);
  set_alert($deleted?'success':'danger',_l($deleted?'dev_deleted':'dev_delete_failed'));
  redirect(admin_url('development'));
 }
 public function bulk_delete(){
  if(!$this->can('delete'))access_denied(_l('dev_development'));
  if($this->input->method(true)!=='POST'){show_error(_l('dev_delete_post_required'),405);return;}
  $raw=(string)$this->input->post('ids');
  $ids=array_values(array_unique(array_filter(array_map('intval',explode(',',$raw)),function($id){return $id>0;})));
  if(!$ids){set_alert('warning',_l('dev_bulk_select_first'));redirect(admin_url('development'));return;}
  $deleted=0;
  foreach(array_slice($ids,0,500) as $id){if($this->development_model->get($id) && $this->development_model->delete_item($id))$deleted++;}
  set_alert($deleted?'success':'danger',sprintf(_l('dev_bulk_deleted_count'),$deleted));
  redirect(admin_url('development'));
 }
 public function file($aid){ $a=$this->development_model->attachment($aid);if(!$a||!$this->development_model->get($a['item_id']))show_404();$f=$this->development_model->upload_dir($a['item_id']).$a['stored_name'];if(!is_file($f))show_404();$this->output->set_content_type($a['mime_type']?:'application/octet-stream');$this->output->set_header('Content-Disposition: inline; filename="'.str_replace('"','',$a['original_name']).'"');$this->output->set_output(file_get_contents($f)); }
 private function hours($v){ $v=(float)$v;return $v>0?min($v,9999):null; }
 private function upload_files($id,$note_id){
  if(empty($_FILES['attachments']['name']))return 0;
  $upload=$_FILES['attachments'];$names=(array)$upload['name'];$saved=0;
  // Perfex native file-extension allowlist. Never allow executable files, even if misconfigured.
  $configured=function_exists('get_option')?(string)get_option('allowed_files'):'';
  $allowed=array_filter(array_map(function($v){return strtolower(ltrim(trim($v),'.'));},preg_split('/[,;\s]+/',$configured)));
  if(!$allowed){$allowed=['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','txt','csv','mp3','wav','m4a','mp4','mov','webm'];}
  $blocked=['php','php3','php4','php5','php7','php8','phtml','phar','cgi','pl','py','sh','bash','exe','dll','bat','cmd','com','msi','htaccess','htpasswd','html','htm','js','svg'];
  $dir=$this->development_model->upload_dir($id);
  if(!is_dir($dir)&&!@mkdir($dir,0755,true)){log_message('error','Repair Note upload directory not writable: '.$dir);return 0;}
  if(!is_file($dir.'index.html'))@(\function_exists('sc_recovery_file_put_contents') ? '\sc_recovery_file_put_contents' : '\file_put_contents')($dir.'index.html','');
  foreach($names as $i=>$orig){
   if(!$orig)continue;
   $tmp=is_array($upload['tmp_name'])?($upload['tmp_name'][$i]??''):$upload['tmp_name'];
   $err=is_array($upload['error'])?($upload['error'][$i]??UPLOAD_ERR_NO_FILE):$upload['error'];
   $size=is_array($upload['size'])?($upload['size'][$i]??0):$upload['size'];
   if((int)$err!==UPLOAD_ERR_OK||!is_uploaded_file($tmp)){log_message('error','Repair Note attachment upload error: '.(int)$err);continue;}
   $ext=strtolower(pathinfo((string)$orig,PATHINFO_EXTENSION));
   if(!$ext||!in_array($ext,$allowed,true)||in_array($ext,$blocked,true)){log_message('error','Repair Note attachment extension not allowed: '.$ext);continue;}
   $mime='application/octet-stream';
   if(class_exists('finfo')){$fi=new finfo(FILEINFO_MIME_TYPE);$mime=$fi->file($tmp)?:$mime;}
   $stored=bin2hex(random_bytes(16)).'.'.$ext;
   if((\function_exists('sc_recovery_move_uploaded_file') ? '\sc_recovery_move_uploaded_file' : '\move_uploaded_file')($tmp,$dir.$stored)){
    $this->development_model->add_attachment($id,$note_id,basename((string)$orig),$stored,$mime,$size);$saved++;
   }else log_message('error','Repair Note attachment could not be moved to '.$dir);
  }
  return $saved;
 }
}

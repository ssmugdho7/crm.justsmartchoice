<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Development_model extends App_Model {
 private function scope(){ if(is_admin()||staff_can('view','development'))return; $sid=(int)get_staff_user_id(); $p=db_prefix(); $this->db->group_start()->where('created_by',$sid)->or_where("EXISTS (SELECT 1 FROM `{$p}development_assignees` a WHERE a.item_id = `{$p}development_items`.id AND a.staff_id = {$sid})",null,false)->group_end(); }
 public function all(){ $this->scope(); $this->db->order_by('updated_at','DESC'); return $this->db->get(db_prefix().'development_items')->result_array(); }
 public function get($id){ $this->scope(); $r=$this->db->where('id',(int)$id)->get(db_prefix().'development_items')->row_array(); if(!$r)return false; $r['reference_links']=$this->reference_links($id); $r['assignees']=$this->assignees($id); $r['notes']=$this->notes($id); $r['attachments']=$this->attachments($id); return $r; }
 public function create($d){ $now=date('Y-m-d H:i:s'); $ok=$this->db->insert(db_prefix().'development_items',['title'=>$d['title'],'description'=>$d['description'],'reference_url'=>$d['reference_url'],'status'=>'open','estimated_hours'=>$d['estimated_hours'],'created_by'=>get_staff_user_id(),'created_at'=>$now,'updated_at'=>$now]); if(!$ok){log_message('error','CRM Repair Note insert failed: '.json_encode($this->db->error()));return false;} return (int)$this->db->insert_id(); }
 public function update_item($id,$d){ return $this->db->where('id',(int)$id)->update(db_prefix().'development_items',['title'=>$d['title'],'description'=>$d['description'],'reference_url'=>$d['reference_url'],'estimated_hours'=>$d['estimated_hours'],'updated_at'=>date('Y-m-d H:i:s')]); }
 public function touch($id){ $this->db->where('id',(int)$id)->update(db_prefix().'development_items',['updated_at'=>date('Y-m-d H:i:s')]); }
 public function set_assignees($id,$ids){ $ids=array_values(array_unique(array_filter(array_map('intval',(array)$ids)))); $this->db->where('item_id',(int)$id)->delete(db_prefix().'development_assignees'); foreach($ids as $sid)$this->db->insert(db_prefix().'development_assignees',['item_id'=>(int)$id,'staff_id'=>$sid,'assigned_by'=>get_staff_user_id(),'assigned_at'=>date('Y-m-d H:i:s')]); $this->touch($id); }
 public function assignees($id){ return $this->db->select('a.staff_id,s.firstname,s.lastname')->from(db_prefix().'development_assignees a')->join(db_prefix().'staff s','s.staffid=a.staff_id','left')->where('a.item_id',(int)$id)->get()->result_array(); }
 public function reference_links($id){
  $rows=$this->db->where('item_id',(int)$id)->order_by('sort_order','ASC')->get(db_prefix().'development_links')->result_array();
  if(!$rows){$old=$this->db->select('reference_url')->where('id',(int)$id)->get(db_prefix().'development_items')->row_array();if($old && $old['reference_url'])return [$old['reference_url']];}
  return array_column($rows,'url');
 }
 public function set_reference_links($id,$urls){
  $this->db->where('item_id',(int)$id)->delete(db_prefix().'development_links');
  foreach(array_values((array)$urls) as $i=>$url){
   $this->db->insert(db_prefix().'development_links',['item_id'=>(int)$id,'url'=>$url,'sort_order'=>$i]);
  }
 }
 public function assigned_ids($id){return array_map('intval',array_column($this->assignees($id),'staff_id'));}
 public function add_note($id,$note,$next){ $this->db->insert(db_prefix().'development_notes',['item_id'=>(int)$id,'staff_id'=>get_staff_user_id(),'note'=>$note,'next_step'=>$next,'created_at'=>date('Y-m-d H:i:s')]); $nid=$this->db->insert_id(); $this->touch($id); return $nid; }
 public function notes($id){ return $this->db->select('n.*,s.firstname,s.lastname')->from(db_prefix().'development_notes n')->join(db_prefix().'staff s','s.staffid=n.staff_id','left')->where('n.item_id',(int)$id)->order_by('n.id','DESC')->get()->result_array(); }
 public function complete($id,$done,$staff_id=null){ $data=$done?['status'=>'completed','completed_by'=>$staff_id,'completed_at'=>date('Y-m-d H:i:s')]:['status'=>'open','completed_by'=>null,'completed_at'=>null,'approved_by'=>null,'approved_at'=>null]; $data['updated_at']=date('Y-m-d H:i:s'); return $this->db->where('id',(int)$id)->update(db_prefix().'development_items',$data); }
 public function approve($id){return $this->db->where('id',(int)$id)->where('status','completed')->update(db_prefix().'development_items',['status'=>'approved','approved_by'=>get_staff_user_id(),'approved_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')]);}
 public function add_attachment($id,$note_id,$orig,$stored,$mime,$size){ $this->db->insert(db_prefix().'development_attachments',['item_id'=>(int)$id,'note_id'=>$note_id?:null,'uploaded_by'=>get_staff_user_id(),'original_name'=>$orig,'stored_name'=>$stored,'mime_type'=>$mime,'file_size'=>(int)$size,'created_at'=>date('Y-m-d H:i:s')]); $this->touch($id); }
 public function attachments($id){ return $this->db->select('a.*,s.firstname,s.lastname')->from(db_prefix().'development_attachments a')->join(db_prefix().'staff s','s.staffid=a.uploaded_by','left')->where('a.item_id',(int)$id)->order_by('a.id','DESC')->get()->result_array(); }
 public function attachment($aid){ return $this->db->where('id',(int)$aid)->get(db_prefix().'development_attachments')->row_array(); }
 public function delete_item($id){ $atts=$this->attachments($id); foreach($atts as $a){$f=$this->upload_dir($id).$a['stored_name'];if(is_file($f))@(\function_exists('sc_recovery_unlink') ? '\sc_recovery_unlink' : '\unlink')($f);} $this->db->where('item_id',(int)$id)->delete(db_prefix().'development_attachments');$this->db->where('item_id',(int)$id)->delete(db_prefix().'development_notes');$this->db->where('item_id',(int)$id)->delete(db_prefix().'development_assignees');$this->db->where('item_id',(int)$id)->delete(db_prefix().'development_links');return $this->db->where('id',(int)$id)->delete(db_prefix().'development_items'); }
 public function upload_dir($id){ return FCPATH.'uploads/development/'.(int)$id.'/'; }
}

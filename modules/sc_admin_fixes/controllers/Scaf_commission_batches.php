<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Scaf_commission_batches extends AdminController {
 public function index(){
  if(!is_admin()){access_denied('commission_reports');return;}
  if(strtoupper($this->input->method())!=='POST'){show_404();return;}
  require_once __DIR__.'/../includes/commission_workflows.php';$month=$this->input->post('month');$action=$this->input->post('action');if(!scaf_commission_month_valid($month)||!in_array($action,['generate','approve'],true)){return $this->respond(['success'=>false,'message'=>'Invalid month or action.'],422);}
  $lock='scaf_commission_batches';$locked=$this->db->query('SELECT GET_LOCK(?, 5) AS acquired',[$lock])->row();if(!$locked||(int)$locked->acquired!==1)return $this->respond(['success'=>false,'message'=>'Batch is busy. Retry shortly.'],409);
  try{
   $this->load->model('commission/commission_model');$rows=$this->commission_model->smartchoice_commission_summary(['from_date'=>$month.'-01','to_date'=>date('Y-m-t',strtotime($month.'-01')),'paid'=>'0']);$snapshot=scaf_commission_snapshot($rows,$month);
   if(isset($snapshot['totals']['Unknown']))return $this->respond(['success'=>false,'message'=>'Invoice currency is missing. Resolve the source records before batch approval.'],422);
   if(!$snapshot['items'])return $this->respond(['success'=>false,'message'=>'No outstanding commissions in this month.'],422);
   $key='scaf_commission_batch_'.$month;$stored=$this->db->where('name',$key)->get(db_prefix().'options')->row();$raw=$stored->value??'';$batch=$raw?json_decode($raw,true):null;
   if($action==='approve'&&(!is_string($this->input->post('hash'))||!hash_equals($snapshot['hash'],$this->input->post('hash'))))return $this->respond(['success'=>false,'message'=>'Reviewed batch no longer matches the ledger.'],409);
   $batch=scaf_commission_transition($batch,$snapshot,$action,get_staff_user_id(),date('Y-m-d H:i:s'));
   if($action==='approve'){
    $indexRow=$this->db->where('name','scaf_commission_approved_ids')->get(db_prefix().'options')->row();$indexRaw=$indexRow->value??'';$index=$indexRaw?json_decode($indexRaw,true):[];if(!is_array($index))$index=[];
    foreach($snapshot['items'] as $item){if(isset($index[$item['id']])&&$index[$item['id']]['month']!==$month)throw new RuntimeException('A commission was already approved in another monthly batch. Review its previous approval before proceeding.');}
    foreach($snapshot['items'] as $item)$index[$item['id']]=['month'=>$month,'hash'=>$snapshot['hash'],'approved_by'=>get_staff_user_id()];
    update_option('scaf_commission_approved_ids',json_encode($index));
   }
   update_option($key,json_encode($batch));
   return $this->respond(['success'=>true,'batch'=>$batch,'message'=>$batch['state']==='approved'?'Batch review approved. No payment or transfer was recorded.':'Batch generated for review. No payment or transfer was recorded.']);
  }catch(RuntimeException $e){return $this->respond(['success'=>false,'message'=>$e->getMessage()],409);}finally{$this->db->query('SELECT RELEASE_LOCK(?)',[$lock]);}
 }
 private function respond($data,$status=200){return $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));}
}

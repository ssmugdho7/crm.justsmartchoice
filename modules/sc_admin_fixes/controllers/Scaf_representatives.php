<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Scaf_representatives extends AdminController {
 public function policy($id){
  if(!is_admin()||!ctype_digit((string)$id)){access_denied('sales_center');return;}
  require_once __DIR__.'/../includes/representatives.php';$this->load->model('sales_center/sales_center_model');$rep=$this->sales_center_model->get((int)$id);if(!$rep){show_404();return;}
  $error='';if($this->input->post()){
   try{$policy=scaf_rep_policy_validate($this->input->post());if(!$this->db->where('name',$policy['currency'])->get(db_prefix().'currencies')->row())throw new InvalidArgumentException('Currency is not configured in CRM.');update_option('scaf_rep_policy_'.(int)$id,json_encode($policy));set_alert('success','Monthly goal and tier display policy saved. Existing commission ledger amounts were not changed.');redirect(admin_url('sales_center/view/'.(int)$id));return;}catch(InvalidArgumentException $e){$error=$e->getMessage();}
  }
  $this->load->view('../../modules/sc_admin_fixes/views/representatives/policy',['rep'=>$rep,'policy'=>scaf_rep_policy($id),'error'=>$error]);
 }
}

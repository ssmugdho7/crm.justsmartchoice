<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Scaf_estimates extends AdminController {
    public function __construct(){parent::__construct();$this->load->helper('estimates');$this->load->helper('invoices');}
    private function source($id){
        if(!ctype_digit((string)$id)||!staff_can('create','invoices')||!staff_can('edit','estimates')||!user_can_view_estimate($id)){access_denied('estimates');return null;}
        $this->load->model('estimates_model');$source=$this->estimates_model->get((int)$id);if(!$source){show_404();return null;}return $source;
    }
    public function preview($id){
        $source=$this->source($id);if(!$source)return;
        $this->output->set_content_type('application/json')->set_output(json_encode(scaf_estimate_dialog_data($source)));
    }
    public function convert($id){
        if(strtoupper($this->input->method())!=='POST'){show_404();return;}
        if(!$this->source($id))return;$this->load->model('sc_admin_fixes/Scaf_estimates_model');
        try{$result=$this->Scaf_estimates_model->convert((int)$id,(string)$this->input->post('mode'),(string)$this->input->post('percent'),(string)$this->input->post('version'));$result['success']=true;$result['url']=admin_url('invoices/invoice/'.$result['invoice_id']);set_alert('success',!empty($result['created'])?(!empty($result['draft'])?'Estimate draw draft created. Review before issuing.':_l('estimate_convert_to_invoice_successfully')):'Existing linked invoice opened.');}
        catch(Throwable $e){$this->output->set_status_header(422);$result=['success'=>false,'message'=>$e instanceof RuntimeException||$e instanceof InvalidArgumentException?$e->getMessage():'Unable to convert the estimate. Refresh before retrying.'];}
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }
}

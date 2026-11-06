<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Workflow extends AdminController {
    public function __construct(){parent::__construct();$this->load->helper('appointly/workflow');$this->load->model('appointly/workflow_model');}
    private function appointment($id,$edit=false){
        if(!ctype_digit((string)$id))show_404();
        $a=$this->db->where('id',(int)$id)->get(db_prefix().'appointly_appointments')->row_array();
        if(!$a || !appointly_workflow_visible($a) || ($edit && !staff_can('edit','appointments')))access_denied('appointments');
        return $a;
    }
    public function reschedule($id){
        if(strtoupper($this->input->method())!=='POST')show_404();
        $expected=(string)$this->session->userdata('appointly_workflow_token');$token=$this->input->post('workflow_token');
        if(!$expected || !is_string($token) || !hash_equals($expected,$token))show_error('Invalid scheduling security token. Refresh the dispatch board.',403);
        $a=$this->appointment($id,true);
        try{$result=$this->workflow_model->reschedule($a,(string)$this->input->post('date'),(string)$this->input->post('time'),(string)$this->input->post('version'));}
        catch(Throwable $e){$this->output->set_status_header(409);$result=['success'=>false,'message'=>$e instanceof RuntimeException || $e instanceof InvalidArgumentException ? $e->getMessage() : 'Unable to reschedule. Refresh and retry.'];}
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }
    public function addresses(){
        if(!staff_can('create','appointments') && !staff_can('edit','appointments'))access_denied('appointments');
        $q=trim((string)$this->input->get('q'));$rows=[];
        if(mb_strlen($q)>=3 && mb_strlen($q)<=120){$this->db->select('id,address,created_by')->like('address',$q)->where('address !=','');if(!staff_can('view','appointments'))$this->db->where('created_by',get_staff_user_id());$rows=$this->db->limit(15)->get(db_prefix().'appointly_appointments')->result_array();}
        $values=array_values(array_unique(array_column($rows,'address')));
        $this->output->set_content_type('application/json')->set_output(json_encode($values));
    }
}

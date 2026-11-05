<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Sc_admin_fixes extends AdminController {
    public function customer_statements() {
        if (strtoupper($this->input->method()) !== 'POST') { show_404(); return; }
        require_once __DIR__.'/../includes/customer_data.php';
        $action=$this->input->post('action');
        $reviews=$this->session->userdata('scaf_statement_reviews') ?: [];
        if ($action === 'preview') {
            $ids=$this->input->post('ids');$from=$this->input->post('from');$to=$this->input->post('to');
            if (!scaf_statement_request_valid($ids,$from,$to)) { $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['success'=>false])); return; }
            $contacts=[];
            foreach(array_unique(array_map('intval',$ids)) as $id) {
                $contacts[$id]=$this->db->select('id,firstname,lastname,email')->where('userid',$id)->where('active',1)->where('invoice_emails',1)->get(db_prefix().'contacts')->result_array();
            }
            $token=bin2hex(random_bytes(24));
            // Keep bounded, short-lived reviewed batches in the current staff session.
            $reviews=array_filter($reviews,static function($r){return $r['expires']>=time();});
            if(count($reviews)>10)$reviews=array_slice($reviews,-10,null,true);
            $reviews[$token]=['ids'=>array_keys($contacts),'contacts'=>$contacts,'from'=>$from,'to'=>$to,'expires'=>time()+900,'staff'=>get_staff_user_id(),'state'=>'review'];
            $this->session->set_userdata('scaf_statement_reviews',$reviews);
            $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true,'token'=>$token,'contacts'=>$contacts,'from'=>$from,'to'=>$to]));return;
        }
        $token=$this->input->post('token');$review=is_string($token)?($reviews[$token]??null):null;
        if (!$review || $review['expires']<time() || (int)$review['staff']!==(int)get_staff_user_id() || !scaf_statement_request_valid($review['ids'],$review['from'],$review['to'])) { $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['success'=>false]));return; }
        if(isset($review['result'])){$this->output->set_content_type('application/json')->set_output(json_encode($review['result']));return;}
        if($action!=='send' || $review['state']!=='review'){$this->output->set_status_header(409)->set_content_type('application/json')->set_output(json_encode(['success'=>false]));return;}
        $selected=$this->input->post('contacts');$targets=[];
        if(!is_array($selected)||!$selected){$this->output->set_status_header(422)->set_output(json_encode(['success'=>false]));return;}
        foreach($selected as $contactId){
            if(!is_scalar($contactId)||!ctype_digit((string)$contactId)){access_denied('customers');return;}
            $contact=$this->db->where('id',(int)$contactId)->where('active',1)->where('invoice_emails',1)->get(db_prefix().'contacts')->row_array();
            $customer=(int)($contact['userid']??0);$allowed=array_column($review['contacts'][$customer]??[],'id');
            if(!$contact||!in_array($customer,$review['ids'],true)||!in_array((int)$contactId,array_map('intval',$allowed),true)||!scaf_customer_allowed($customer)){access_denied('customers');return;}
            $snapshot=null;
            foreach($review['contacts'][$customer] as $reviewedContact) if((int)$reviewedContact['id']===(int)$contactId){$snapshot=$reviewedContact;break;}
            if(!$snapshot || !isset($snapshot['email'],$contact['email']) || mb_strtolower(trim($snapshot['email']))!==mb_strtolower(trim($contact['email']))){
                $this->output->set_status_header(409)->set_content_type('application/json')->set_output(json_encode(['success'=>false,'message'=>'Recipient changed. Review the batch again.']));return;
            }
            $targets[(int)$contactId]=$customer;
        }
        $reviews[$token]['state']='sending';$this->session->set_userdata('scaf_statement_reviews',$reviews);
        $this->load->model('clients_model');$sent=0;$failed=0;
        foreach($targets as $contactId=>$customer){try{$ok=$this->clients_model->send_statement_to_email($customer,[$contactId],_d($review['from']),_d($review['to']));if($ok)$sent++;else$failed++;}catch(Throwable $error){$failed++;}}
        load_admin_language();
        $result=['success'=>$failed===0,'sent'=>$sent,'failed'=>$failed];$reviews[$token]['state']='done';$reviews[$token]['result']=$result;$this->session->set_userdata('scaf_statement_reviews',$reviews);
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }
    public function project_scope($id) {
        if(strtoupper($this->input->method())!=='POST') { show_404(); }
        require_once __DIR__.'/../includes/project_data.php';
        if(!ctype_digit((string)$id) || !staff_can('create','tasks') || !scaf_project_allowed($id)) { access_denied('projects'); }
        $this->load->model('sc_admin_fixes/sc_admin_fixes_model');
        try { $result=$this->sc_admin_fixes_model->project_scope_convert((int)$id);$result['success']=true; }
        catch(Throwable $e) { $this->output->set_status_header(422);$result=['success'=>false,'message'=>$e instanceof InvalidArgumentException || $e instanceof RuntimeException ? $e->getMessage() : 'Scope conversion failed. Please retry.']; }
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }
    public function project_batch() {
        if(strtoupper($this->input->method())!=='POST')show_404();
        require_once __DIR__.'/../includes/project_data.php';
        $ids=array_unique(array_map('intval',(array)$this->input->post('ids')));$status=(int)$this->input->post('status');
        if(!$ids || count($ids)>100 || !staff_can('edit','projects'))access_denied('projects');
        $this->load->model('projects_model');$valid=array_column($this->projects_model->get_project_statuses(),'id');
        if(!in_array($status,$valid))show_error('Invalid project status',422);
        foreach($ids as $id)if(!scaf_project_allowed($id))access_denied('projects');
        $updated=0;foreach($ids as $id)if($this->projects_model->mark_as(['project_id'=>$id,'status_id'=>$status]))$updated++;
        $this->output->set_content_type('application/json')->set_output(json_encode(['success'=>true,'updated'=>$updated]));
    }
    public function project_kanban($id) {
        require_once __DIR__.'/../includes/project_data.php';
        if(!ctype_digit((string)$id) || !scaf_project_allowed($id))access_denied('projects');
        $this->load->model('projects_model');$this->load->model('tasks_model');
        $tasks=$this->projects_model->get_tasks((int)$id,[],true);
        $this->load->view('sc_admin_fixes/projects/kanban',['tasks'=>$tasks,'statuses'=>$this->tasks_model->get_statuses()]);
    }
}

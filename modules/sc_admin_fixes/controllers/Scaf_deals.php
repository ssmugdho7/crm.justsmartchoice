<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Scaf_deals extends AdminController
{
    public function __construct(){parent::__construct();require_once __DIR__.'/../includes/deals_workflows.php';}
    private function writable()
    {
        if(strtoupper($this->input->method())!=='POST'){show_404();return false;}
        if(!$this->app_modules->is_active('deals')){access_denied('deals');return false;}
        return true;
    }
    public function setup()
    {
        if(!$this->writable())return;
        if(!is_admin()){access_denied('deals');return;}
        require module_dir_path('deals','install.php');
        $pipeline=scaf_deals_seed_contractor_pipeline($this->db);
        set_alert('success','Contractor pipeline configured. Existing pipelines and deals were preserved.');
        redirect(admin_url('deals?pipeline_id='.$pipeline));
    }
    public function convert($id)
    {
        if(!$this->writable())return;
        if(!ctype_digit((string)$id)||!staff_can('create','projects')||!staff_can('edit','deals')){access_denied('deals');return;}
        $this->load->model('sc_admin_fixes/Scaf_deals_model');
        try{$result=$this->Scaf_deals_model->convert((int)$id);$result['success']=true;}
        catch(Throwable $error){$this->output->set_status_header(422);$result=['success'=>false,'message'=>$error instanceof RuntimeException?$error->getMessage():'Conversion failed.'];}
        $this->output->set_content_type('application/json')->set_output(json_encode($result));
    }
}

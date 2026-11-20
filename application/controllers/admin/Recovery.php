<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Recovery extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin()) access_denied('Trash & Recovery');
        $this->load->helper('recovery');
    }
    public function index()
    {
        $page=max(1,(int)$this->input->get('page'));$search=trim((string)$this->input->get('search',true));$all=$this->input->get('all')==='1';
        $ready=sc_recovery_enabled();$rows=[];$files=[];$maintenance=[];
        if(is_file(sc_recovery_root().'/maintenance.json')) $maintenance=json_decode(file_get_contents(sc_recovery_root().'/maintenance.json'),true) ?: [];
        if($ready){
            $files=sc_recovery_store()->entries($page,$all,substr($search,0,150));
            $rows=$this->db->select('batch_id,MIN(deleted_at) AS deleted_at,MAX(actor) AS actor,COUNT(*) AS records,COUNT(DISTINCT table_name) AS tables',false)->from(db_prefix().'sc_recovery_rows')->group_by('batch_id')->order_by('MIN(id)','DESC',false)->limit(50,($page-1)*50)->get()->result_array();
        }
        $this->load->view('admin/recovery/index',['title'=>'Trash & Recovery','ready'=>$ready,'files'=>$files,'batches'=>$rows,'page'=>$page,'search'=>$search,'all'=>$all,'maintenance'=>$maintenance]);
    }
    public function download($id)
    {
        if(!ctype_digit((string)$id)||!sc_recovery_enabled())show_404();
        try{$entry=sc_recovery_store()->entry((int)$id);$path=sc_recovery_store()->object($entry);}catch(RuntimeException $e){show_error('This recovery copy failed verification or is unavailable.',409);return;}
        // Stream large videos without loading them into PHP memory.
        header('Content-Type: application/octet-stream');header('Content-Disposition: attachment; filename="'.str_replace(['"',"\r","\n"],'_',$entry['name']).'"');header('X-Content-Type-Options: nosniff');header('Content-Security-Policy: sandbox');header('Cache-Control: private, no-store');header('Content-Length: '.filesize($path));readfile($path);exit;
    }
    public function restore($id)
    {
        if($this->input->method(true)!=='POST'){show_error('Use the Restore button to restore a file.',405);return;}
        if(!ctype_digit((string)$id)||!sc_recovery_enabled())show_404();
        try{$path=sc_recovery_store()->restore((int)$id);log_activity('Recovery file restored [entry '.(int)$id.']');set_alert('success','The file was restored to its original location.');}
        catch(RuntimeException $e){set_alert('warning',$e->getMessage());}
        redirect(admin_url('recovery'));
    }
    public function records($batch)
    {
        if(!preg_match('/^[a-zA-Z0-9-]{16,64}$/D',(string)$batch)||!sc_recovery_enabled())show_404();
        $rows=$this->db->where('batch_id',$batch)->order_by('id','ASC')->get(db_prefix().'sc_recovery_rows')->result_array();
        if(!$rows)show_404();
        $this->output->set_content_type('application/json')->set_header('Content-Disposition: attachment; filename="crm-record-recovery-'.$batch.'.json"')->set_header('X-Content-Type-Options: nosniff')->set_header('Cache-Control: private, no-store')->set_output(json_encode(['format'=>'crm-deleted-records-base64-v1','batch'=>$batch,'records'=>$rows],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
}

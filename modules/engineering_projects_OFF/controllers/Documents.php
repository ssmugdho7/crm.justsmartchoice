<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Documents extends AdminController
{
    public function __construct(){ parent::__construct(); $this->load->model('engineering_projects/documents_model'); $this->load->helper('engineering_projects/project_files'); }
    public function index(){ redirect(admin_url('engineering_projects/documents')); }
    public function document($id=''){
        if (!has_permission('engineering_projects','','view') && !is_admin()) { access_denied('engineering_projects'); }
        $id = is_numeric($id) ? (int)$id : 0;
        if ($this->input->post()) {
            $postId = $this->input->post('id'); if (is_numeric($postId) && (int)$postId > 0) { $id=(int)$postId; }
            $data = ['name'=>trim((string)$this->input->post('name', true)), 'engineering_project_id'=>(int)$this->input->post('engineering_project_id'), 'project_id'=>(int)$this->input->post('project_id'), 'customer_id'=>(int)$this->input->post('customer_id')];
            $data['folder_path'] = engproj_project_folder($data['customer_id'], $data['project_id'], 'Documents');
            foreach(['noc','eng_letter','site_insp','permit'] as $field){ if(!empty($_FILES[$field]['name']) && is_uploaded_file($_FILES[$field]['tmp_name'])){ _maybe_create_upload_path($data['folder_path']); $fn=unique_filename($data['folder_path'], $_FILES[$field]['name']); if(move_uploaded_file($_FILES[$field]['tmp_name'], $data['folder_path'].$fn)){ $data[$field]=$fn; } } }
            if($id>0){ $this->documents_model->update($data,$id); set_alert('success','Document updated successfully.'); } else { $newId=$this->documents_model->add($data); set_alert($newId?'success':'warning',$newId?'Document created successfully.':'The document could not be saved.'); }
            redirect(admin_url('engineering_projects/documents'));
        }
        $this->load->model('projects_model'); $this->load->model('clients_model'); $this->load->model('engineering_projects/engineering_projects_model');
        $data['projects']=$this->projects_model->get(''); $data['customers']=$this->clients_model->get('', ['active'=>1]); $data['engineering_projects']=$this->engineering_projects_model->get(''); $data['document']=$id?$this->documents_model->get($id):null; $data['id']=$id; $data['title']=$id?'Edit Document':'New Document';
        $this->load->view('engineering_projects/forms/document_form', $data);
    }
    public function delete($id){ if(!has_permission('engineering_projects','','delete')&&!is_admin()){access_denied('engineering_projects');} if($id){$this->documents_model->delete((int)$id);} redirect(admin_url('engineering_projects/documents')); }
}

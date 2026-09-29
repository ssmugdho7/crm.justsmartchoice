<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Drawings extends AdminController
{
    public function __construct(){ parent::__construct(); $this->load->model('engineering_projects/drawings_model'); $this->load->helper('engineering_projects/project_files'); }
    public function index(){ redirect(admin_url('engineering_projects/drawings')); }
    public function drawing($id=''){
        if (!has_permission('engineering_projects','','view') && !is_admin()) { access_denied('engineering_projects'); }
        $id = is_numeric($id) ? (int)$id : 0;
        if ($this->input->post()) {
            $postId = $this->input->post('id'); if (is_numeric($postId) && (int)$postId > 0) { $id=(int)$postId; }
            $data = ['name'=>trim((string)$this->input->post('name', true)), 'type'=>trim((string)$this->input->post('type', true)), 'engineering_project_id'=>(int)$this->input->post('engineering_project_id'), 'project_id'=>(int)$this->input->post('project_id'), 'customer_id'=>(int)$this->input->post('customer_id')];
            $data['folder_path'] = engproj_project_folder($data['customer_id'], $data['project_id'], 'Drawings');
            foreach(['draf','final_doc'] as $field){ if(!empty($_FILES[$field]['name']) && is_uploaded_file($_FILES[$field]['tmp_name'])){ _maybe_create_upload_path($data['folder_path']); $fn=unique_filename($data['folder_path'], $_FILES[$field]['name']); if(move_uploaded_file($_FILES[$field]['tmp_name'], $data['folder_path'].$fn)){ $data[$field]=$fn; } } }
            if($id>0){ $this->drawings_model->update($data,$id); set_alert('success','Drawing updated successfully.'); } else { $newId=$this->drawings_model->add($data); set_alert($newId?'success':'warning',$newId?'Drawing created successfully.':'The drawing could not be saved.'); }
            redirect(admin_url('engineering_projects/drawings'));
        }
        $this->load->model('projects_model'); $this->load->model('clients_model'); $this->load->model('engineering_projects/engineering_projects_model');
        $data['projects']=$this->projects_model->get(''); $data['customers']=$this->clients_model->get('', ['active'=>1]); $data['engineering_projects']=$this->engineering_projects_model->get(''); $data['drawing']=$id?$this->drawings_model->get($id):null; $data['id']=$id; $data['title']=$id?'Edit Drawing':'New Drawing';
        $this->load->view('engineering_projects/forms/drawing_form', $data);
    }
    public function delete($id){ if(!has_permission('engineering_projects','','delete')&&!is_admin()){access_denied('engineering_projects');} if($id){$this->drawings_model->delete((int)$id);} redirect(admin_url('engineering_projects/drawings')); }
}

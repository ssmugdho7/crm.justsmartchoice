<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Dmn extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('dmn_model');
    }
    public function index()
    {
        if (!has_permission('dmn', '', 'view')) {
            access_denied('dmn');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('dmn', 'table'));
        }
        $data['switch_grid'] = false;
        if ($this->session->userdata('dmn_grid_view') == 'true') {
            $data['switch_grid'] = true;
            $data['switch_view_icon'] = 'list';
        }else{
            $data['switch_view_icon'] = 'th';
        }
        $this->load->model('staff_model');
        $data['staffs'] = $this->staff_model->get();
        $data['projects'] = $this->dmn_model->get_projects();
        $data['title'] = _l('dmn');
        $this->app_scripts->add('manage-js','modules/dmn/assets/js/manage.js');
        $this->load->view('manage-dmn', $data);
    }
    public function table()
    {
        if (!has_permission('dmn', '', 'view')) {
            access_denied('dmn');
        }
        $this->app->get_table_data(module_views_path('dmn', 'table'));
    }
    public function dmn_detail($id=null)
    {
        if (!has_permission('dmn', '', 'view')) {
            access_denied('dmn');
        }
        if($id){
            if ($this->input->post()) 
            {
                if (!has_permission('dmn', '', 'edit')) {
                    access_denied('dmn');
                }
                $success = $this->dmn_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('dmn')));
                }
                redirect(admin_url('dmn/dmn_create/' . $id));
            }
            $data['title'] = _l('dmn_create_new');
            $data['dmn'] = $this->dmn_model->get($id);
            $data['projects']    = $this->dmn_model->get_projects();
            $this->load->view('dmn_detail',$data);
        }else{
            if ($this->input->post()) 
            {
                if (!has_permission('dmn', '', 'create')) 
                {
                    access_denied('dmn');
                }
                $id = $this->dmn_model->add($this->input->post());
                if ($id) 
                {
                    set_alert('success', _l('added_successfully', _l('dmn')));
                    redirect(admin_url('dmn/dmn_create/' . $id));
                }
            }
            $data['title'] = _l('dmn_create_new');
            $data['projects']    = $this->dmn_model->get_projects();
            $this->load->view('dmn_detail',$data);
        }
    }
    public function dmn_create($id)
    {  
        if (!has_permission('dmn', '', 'view')) {
            access_denied('dmn');
        }
        if ($this->input->post()) 
        {
            if ($id == '') 
            {
                if (!has_permission('dmn', '', 'create')) 
                {
                    access_denied('dmn');
                }
                $id = $this->dmn_model->add($this->input->post());
                if ($id) 
                {
                    set_alert('success', _l('added_successfully', _l('dmn')));
                    redirect(admin_url('dmn/dmn_create/' . $id));
                }
            } 
            else 
            {
                if (!has_permission('dmn', '', 'edit')) {
                    access_denied('dmn');
                }
                $success = $this->dmn_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('dmn')));
                }
                redirect(admin_url('dmn/dmn_create/' . $id));
            }
        }
        $data['dmn'] = $this->dmn_model->get($id);
        if(isset($data['dmn']) && !empty($data['dmn'])){
            $data['title'] = _l('dmn_create_new');
            $data['projects'] = $this->dmn_model->get_projects();
            $data['votes'] = json_decode($this->dmn_model->get_canvas_vote_count(array('dmn_id'=>$id)));
            $data['vote'] = $this->dmn_model->get_canvas_vote(array('dmn_id'=>$id, 'user_id'=>get_staff_user_id(),'user_type'=>'staff'));
            $this->app_scripts->add('dmn-dev-js','modules/dmn/assets/js/development.js');
            $this->app_scripts->add('dmn-jq-dev-js','modules/dmn/assets/js/development.js');
            $this->app_scripts->add('dmn-js','modules/dmn/assets/js/dmn.js');
            $this->load->view('canvas',$data);
        }else{
            access_denied(_l('dmn_not_found!'));
        }
    }
    public function switch_grid($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'false';
        } else {
            $set = 'true';
        }
        $this->session->set_userdata([
            'dmn_grid_view' => $set,
        ]);
        if ($manual == false) {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
    public function grid()
    {
        echo $this->load->view('dmn/grid', [], true);
    }
    public function likeCanvas()
    {
        if ($this->input->is_ajax_request()) {
            $post_data = $this->input->post();
            $post_data['user_id'] = get_staff_user_id();
            $post_data['user_type'] = 'staff';
            $resp = $this->dmn_model->add_canvas_vote($post_data);
            echo $this->dmn_model->get_canvas_vote_count($this->input->post());
        }        
    }
    public function delete($id)
    {
        if (!has_permission('dmn', '', 'delete')) {
            access_denied('dmn');
        }
        if (!$id) {
            redirect(admin_url('dmn'));
        }
        $response = $this->dmn_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('dmn_deleted', _l('dmn')));
        } 
        else {
            set_alert('warning', _l('problem_deleting', _l('dmn_lowercase')));
        }
        redirect(admin_url('dmn'));
    }
    public function update_dmn()
    {  
        $post_data = $this->input->post(NULL, TRUE);
        $id = $this->input->post('id',true);
        if (!$id) {
            redirect(admin_url('dmn'));
        }
        $success = $this->dmn_model->dmn_update_details($post_data, $id);
        if ($success && ($this->input->server('REQUEST_METHOD') === 'POST')) {
            set_alert('success', _l('updated_successfully', _l('dmn')));
        }
    }
    public function preview($id = 0)
    {
        if (!has_permission('dmn', '', 'view')) {
            access_denied('dmn');
        }
        $data['dmn'] = $this->dmn_model->get($id);
        if (!$data['dmn']) {
            blank_page(_l('dmn_not_found'), 'danger');
        }
        if($this->input->post('preview',true)){
            $post_data = $this->input->post();
            unset($post_data['preview']);
            unset($post_data['color']);
            if (!has_permission('dmn', '', 'edit')) {
                access_denied('dmn');
            }
            $success = $this->dmn_model->update($post_data, $id);
            if ($success && ($this->input->server('REQUEST_METHOD') === 'GET')) {
                set_alert('success', _l('updated_successfully', _l('dmn')));
            }
        }
        $data['votes'] = json_decode($this->dmn_model->get_canvas_vote_count(array('dmn_id'=>$id)));
        $data['vote'] = $this->dmn_model->get_canvas_vote(array('dmn_id'=>$id, 'user_id'=>get_staff_user_id(),'user_type'=>'staff'));
        $data['title'] = _l('preview_dmn');
        $data['projects']= $this->dmn_model->get_projects();
        $data['staff']= $this->staff_model->get('', ['active' => 1]);
        $data['contacts'] = $this->clients_model->get_contacts('',array('active'=>1));
        $this->app_scripts->add('dmn-dev-js','modules/dmn/assets/js/development.js');
        $this->app_scripts->add('dmn-jq-dev-js','modules/dmn/assets/js/jq-development.js');
        $this->app_scripts->add('preview-js','modules/dmn/assets/js/preview.js');
        $this->load->view('preview', $data);
    }
    public function project_table()
    {
        if (!has_permission('dmn', '', 'view')) {
            access_denied('dmn');
        }
        $this->app->get_table_data(module_views_path('dmn', 'admin/project_table'));
    }
}
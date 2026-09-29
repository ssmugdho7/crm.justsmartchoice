<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Dmn_client extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $CI = &get_instance();
        $this->load->model('dmn_model');
    }
    public function project($id)
    {
        $project = $this->projects_model->get($id, ['clientid' => get_client_user_id(),]);
        if (!$project) {
            show_404();
        }
        $data['project'] = $project;
        $data['project']->settings->available_features = unserialize($data['project']->settings->available_features);
        $data['title'] = $data['project']->name;
        $data['project_status'] = get_project_status_by_id($data['project']->status);
        if (!$this->input->get('group')) {
            $group = 'project_overview';
        } 
        else {
            $group = $this->input->get('group');
        }
        $data['group']    = $group;
        $data['currency'] = $this->projects_model->get_currency($id);
        $data['members']  = $this->projects_model->get_project_members($id);
        if ($group == 'project_dmn') {
            $data['dmn'] = $this->dmn_model->get_dmn_by_project_id($id);
        } 
        $this->data($data);
        $this->view('clients/project');
        $this->layout();
    }
    public function preview($id = 0)
    {
        $data['dmn']  = $this->dmn_model->get($id);
        if (!$data['dmn']) {
            blank_page(_l('dmn_not_found'), 'danger');
        }
        $title = _l('preview_dmn');
        $data['title'] = $title;
        $data['projects'] = $this->dmn_model->get_projects();
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['contacts'] = $this->clients_model->get_contacts('',array('active'=>1));
        $data['votes'] = json_decode($this->dmn_model->get_canvas_vote_count(array('dmn_id'=>$id)));
        $data['vote'] = $this->dmn_model->get_canvas_vote(array('dmn_id'=>$id, 'user_id'=>get_client_user_id(),'user_type'=>'client'));
        $this->data($data);
        $this->view('clients/preview', $data);
        $this->layout();
    }
    public function likeCanvas()
    {
        if ($this->input->is_ajax_request()) {
            $post_data = $this->input->post();
            $post_data['user_id'] = get_client_user_id();
            $post_data['user_type'] = 'client';
            $resp = $this->dmn_model->add_canvas_vote($post_data);
            echo $this->dmn_model->get_canvas_vote_count($this->input->post());
        }        
    }
}
?>
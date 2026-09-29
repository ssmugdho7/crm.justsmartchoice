<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employees_tracker_client extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('employees_tracker/employees_tracker_model');
    }

    public function index()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        $data['title'] = _l('employees_tracker_client_menu');
        $client_ref = function_exists('get_contact_user_id') && get_contact_user_id() ? get_contact_user_id() : get_client_user_id();
        $data['projects'] = $this->employees_tracker_model->get_client_projects($client_ref);
        $this->data($data);
        $this->view('employees_tracker/client/projects');
        $this->layout();
    }

    public function project($project_id)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        $contact_id = (function_exists('get_contact_user_id') && get_contact_user_id()) ? get_contact_user_id() : get_client_user_id();
        if (!$this->employees_tracker_model->client_can_view_project($contact_id, $project_id)) {
            show_404();
        }

        $data['title'] = _l('employees_tracker_project_track');
        $data['project'] = $this->employees_tracker_model->get_project($project_id);
        $data['installers'] = $this->employees_tracker_model->get_project_installers($project_id);
        $data['poll'] = (int) get_option('employees_tracker_poll_interval');
        $data['api_key'] = $this->employees_tracker_model->get_google_api_key();

        $this->data($data);
        $this->view('employees_tracker/client/project_track');
        $this->layout();
    }

    public function project_status($project_id)
    {
        if (!is_client_logged_in()) {
            show_404();
        }

        $contact_id = (function_exists('get_contact_user_id') && get_contact_user_id()) ? get_contact_user_id() : get_client_user_id();
        if (!$this->employees_tracker_model->client_can_view_project($contact_id, $project_id)) {
            show_404();
        }

        $resp = $this->employees_tracker_model->get_project_status_payload($project_id);
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'data'=>$resp]);
    }
}

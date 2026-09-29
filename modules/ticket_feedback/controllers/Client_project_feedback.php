<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Client_project_feedback extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Project_feedback_model');
        // ✅ Load Project Helper to use format_project_status()
        $this->load->helper('projects');
    }

    public function list_projects()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        $client_id = get_client_user_id();
        $projects = $this->Project_feedback_model->get_client_projects($client_id);

        log_message('error', 'Projects Data: ' . print_r($projects, true)); // Debugging

        $data['projects'] = $projects;
        $data['title'] = _l('My Project Feedback');

        $this->data($data);
        $this->view('client/project_feedback_list');
        $this->layout();
    }


    // Show the feedback form
    public function submit_feedback_form($project_id)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        $data['project'] = $this->Project_feedback_model->get_project($project_id);
        $data['title'] = _l('Submit Project Feedback');

        $this->data($data);
        $this->view('client/submit_project_feedback');
        $this->layout();
    }

    public function store_feedback()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        $this->load->helper('security');

        // Validate input fields
        $this->form_validation->set_rules('project_id', _l('Project ID'), 'required|integer');
        $this->form_validation->set_rules('rating', _l('Rating'), 'required|integer|greater_than[0]|less_than[6]');
        $this->form_validation->set_rules('satisfaction_level', _l('Satisfaction Level'), 'required|in_list[0,1]');
        $this->form_validation->set_rules('communication_quality', _l('Communication Quality'), 'required|in_list[0,1]');
        $this->form_validation->set_rules('comments', _l('Comments'), 'trim|xss_clean');

        if ($this->form_validation->run() === FALSE) {
            set_alert('danger', validation_errors());
            redirect(site_url('ticket_feedback/Client_project_feedback/submit_feedback_form/' . $this->input->post('project_id')));
        }

        $project_id = $this->input->post('project_id', TRUE);
        $customer_id = get_client_user_id();

        // Check if feedback already exists for the project
        $existing_feedback = $this->db->get_where(db_prefix() . 'project_feedback', [
            'project_id' => $project_id,
            'customer_id' => $customer_id
        ])->row();

        $data = [
            'rating' => $this->input->post('rating', TRUE),
            'satisfaction_level' => $this->input->post('satisfaction_level', TRUE),
            'communication_quality' => $this->input->post('communication_quality', TRUE),
            'comments' => $this->input->post('comments', TRUE),
            'feedback_received' => 1, // ✅ Mark as feedback received
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing_feedback) {
            // ✅ Update feedback if it already exists
            $this->db->where('id', $existing_feedback->id);
            $update = $this->db->update(db_prefix() . 'project_feedback', $data);
        } else {
            // ✅ Insert only if no previous feedback exists
            $data['project_id'] = $project_id;
            $data['customer_id'] = $customer_id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $update = $this->db->insert(db_prefix() . 'project_feedback', $data);
        }

        if ($update) {
            set_alert('success', _l('Feedback submitted successfully!'));
        } else {
            set_alert('danger', _l('Error submitting feedback. Please try again.'));
        }

        redirect(site_url('ticket_feedback/Client_project_feedback/list_projects'));
    }



}

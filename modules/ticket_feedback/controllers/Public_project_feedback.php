<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Public_project_feedback extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Project_feedback_model');
        $this->load->model('Projects_model');
        $this->load->model('Clients_model');
    }

    // Publicly viewable project feedback
    public function view($project_id)
    {
        if (!$project_id) {
            show_404();
        }

        // Fetch project feedback
        $feedback = $this->Project_feedback_model->get_feedback_by_project($project_id);

        // If no feedback is received, don't show it publicly
        if (!$feedback || $feedback['feedback_received'] == 0) {
            show_404();
        }

        // Fetch project details
        $project = $this->Projects_model->get($project_id);

        // Fetch customer name
        $customer = $this->Clients_model->get($feedback['customer_id']);

        // Pass data to view
        $data['title'] = 'Project Feedback - ' . ($project->name ?? 'Unknown Project');
        $data['project_name'] = $project->name ?? 'Unknown Project';
        $data['project_description'] = $project->description ?? 'No description available.';
        $data['customer_name'] = $customer->company ?? 'Unknown Customer';
        $data['feedback'] = $feedback;

        $this->load->view('public/project_feedback_view', $data);
    }
}

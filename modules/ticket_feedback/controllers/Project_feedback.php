<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Project_feedback extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('project_feedback_model');
    }

    // View all project feedback
    public function index()
    {
        $this->load->model('projects_model');
        $this->load->model('project_feedback_model');

        $data['title'] = _l('Project Feedback');
        $data['feedbacks'] = $this->project_feedback_model->get_all_feedback();

        // ✅ Check if admin or client
        if (is_admin()) {
            $data['customers'] = $this->db->select('userid as id, company')
                ->from(db_prefix() . 'clients')
                ->get()
                ->result_array();
            $data['completed_projects'] = [];
        } else {
            $client_id = get_client_user_id();
            $data['completed_projects'] = $this->project_feedback_model->get_completed_projects($client_id);
        }

        $this->load->view('project_feedback/manage', $data);
    }



    public function send_project_feedback_email()
    {
        $project_id = $this->input->post('project_id');
        $this->load->model('projects_model');
        $this->load->model('clients_model'); // Ensure clients model is loaded

        $project = $this->projects_model->get($project_id);

        if (!$project) {
            set_alert('danger', 'Invalid project ID.');
            redirect(admin_url('ticket_feedback/project_feedback/manage_project_feedback'));
        }

        $client_id = $project->clientid;

        // ✅ Fetch email from `tblcontacts` instead of `tblclients`
        $client_email = $this->db->select('email')
            ->from(db_prefix() . 'contacts')
            ->where('userid', $client_id)
            ->where('is_primary', 1) // Ensure we get the primary contact email
            ->get()
            ->row('email');

        if (empty($client_email)) {
            set_alert('danger', 'No email found for this client.');
            redirect(admin_url('ticket_feedback/project_feedback'));
        }

        $feedback_link = site_url('ticket_feedback/Client_project_feedback/submit_feedback_form/' . $project_id);


        $message = "<p>Hello,</p>
                <p>We value your feedback on your completed project.</p>
                <p>Please take a moment to complete a short survey by clicking the link below:</p>
                <p><a href='{$feedback_link}'>Provide Feedback</a></p>
                <p>Thank you!</p>";

        $this->load->model('emails_model');
        $this->emails_model->send_simple_email($client_email, "We'd love your feedback on the project!", $message);

        // ✅ Log Feedback Request in Database
        $this->project_feedback_model->log_feedback_request($project_id, $client_id);

        set_alert('success', 'Project feedback request email sent successfully.');
        redirect(admin_url('ticket_feedback/project_feedback'));
    }


    public function get_completed_projects()
    {
        $customer_id = $this->input->post('customer_id');

        if (!$customer_id) {
            echo json_encode([]); // ✅ Return empty if no customer selected
            exit;
        }

        $this->load->model('project_feedback_model');
        $projects = $this->project_feedback_model->get_completed_projects($customer_id);

       

        echo json_encode($projects); // ✅ Return JSON response
        exit;
    }






}

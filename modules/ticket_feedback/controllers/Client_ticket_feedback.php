<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Client_ticket_feedback extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('tickets_model');
        $this->load->model('ticket_feedback_model');
    }

    /**
     * Display list of tickets that require feedback
     */
    public function feedback_tickets()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        $client_id = get_client_user_id();
        $data['tickets'] = $this->ticket_feedback_model->get_client_tickets_without_feedback($client_id);

        $data['title'] = _l('My Ticket Feedback');
        $this->data($data);
        $this->view('client/feedback_tickets');
        $this->layout();
    }

    /**
     * Show the feedback form for a specific ticket
     */
    public function submit_feedback_form($ticket_id)
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        $data['ticket_id'] = $ticket_id;
        $data['title'] = _l('Submit Feedback');
        $this->data($data);
        $this->view('client/submit_feedback');
        $this->layout();
    }

    public function store_feedback()
    {
        if (!is_client_logged_in()) {
            echo json_encode(["success" => false, "message" => "Unauthorized access"]);
            exit;
        }

        // Get sanitized input
        $ticket_id = $this->input->post('ticket_id');
        $rating = $this->input->post('rating');
        $resolved = $this->input->post('resolved');
        $response_time_satisfactory = $this->input->post('response_time_satisfactory');
        $comments = $this->input->post('comments');

        // Ensure ticket ID is provided
        if (empty($ticket_id)) {
            echo json_encode(["success" => false, "message" => "Invalid ticket ID"]);
            exit;
        }

        // Check if feedback already exists
        $existing_feedback = $this->db->get_where(db_prefix() . 'ticket_feedback', [
            'ticket_id' => $ticket_id
        ])->row();

        $data = [
            'ticket_id' => $ticket_id,
            'rating' => $rating,
            'resolved' => $resolved,
            'response_time_satisfactory' => $response_time_satisfactory,
            'comments' => $comments,
            'feedback_received' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($existing_feedback) {
            // Update existing feedback
            $this->db->where('ticket_id', $ticket_id);
            $this->db->update(db_prefix() . 'ticket_feedback', $data);
        } else {
            // Insert new feedback
            $this->db->insert(db_prefix() . 'ticket_feedback', $data);
        }

        echo json_encode(["success" => true, "message" => "Feedback submitted successfully"]);
        redirect(site_url('client/client_feedback_dashboard'));
        exit;
    }


    public function client_feedback_dashboard()
    {
       
        $data['title'] = _l('Feedback');
        $this->data($data);
        $this->view('client/client_feedback_dashboard'); // Loads the new page
        $this->layout();
    }




}

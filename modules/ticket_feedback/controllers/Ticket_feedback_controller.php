<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ticket_feedback_controller extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ticket_feedback_model');
        $this->load->model('tickets_model');
    }

    public function manage()
    {
        $data['title'] = _l('Ticket Feedback Management');
    
        // Get closed tickets where feedback request has NOT been sent
        $data['tickets'] = $this->ticket_feedback_model->get_closed_tickets_without_feedback();
    
        // Get all feedback requests (to show in the table)
        $data['feedback_requests'] = $this->ticket_feedback_model->get_all_feedback_requests();
    
        $this->load->view('admin/manage_feedback', $data);
    }
    

    public function send_feedback_email()
    {
        $ticket_id = $this->input->post('ticket_id');
        $this->load->model('tickets_model');

        $ticket = $this->tickets_model->get($ticket_id);

        if (!$ticket) {
            set_alert('danger', 'Invalid ticket ID.');
            redirect(admin_url('ticket_feedback_controller/manage'));
        }

        $client_id = $ticket->userid; // ✅ Fix: Use object property instead of array key
        $client_email = $ticket->email;
        $feedback_link = site_url('ticket_feedback/client_ticket_feedback/submit_feedback_form/' . $ticket->ticketid);


        $message = "<p>Hello,</p>
                    <p>We value your feedback on your support request.</p>
                    <p>Please take a moment to complete a short survey by clicking the link below:</p>
                    <p><a href='{$feedback_link}'>Provide Feedback</a></p>
                    <p>Thank you!</p>";

        $this->load->model('emails_model');
        $this->emails_model->send_simple_email($client_email, "We'd love your feedback!", $message);

        // ✅ Log Feedback Request in Database
        $this->ticket_feedback_model->log_feedback_request($ticket_id);

        set_alert('success', 'Feedback request email sent successfully.');
        redirect(admin_url('ticket_feedback/ticket_feedback_controller/manage'));
    }




    public function feedback_by_customer()
    {
        if (!is_admin()) {
            access_denied('Feedback');
        }

        $this->load->model('clients_model');
        $this->load->model('ticket_feedback_model');

        $data['customers'] = $this->clients_model->get(); // Get all customers
        $data['feedbacks'] = []; // Empty feedback initially

        if ($this->input->post('customer_id')) {
            $customer_id = $this->input->post('customer_id');
            $data['feedbacks'] = $this->ticket_feedback_model->get_feedback_by_customer($customer_id);
            $data['selected_customer'] = $customer_id;
        }
        $data['title'] = _l('Ticket Feedback Management');
        $this->load->view('admin/feedback_by_customer', $data);
    }


    public function send_feedback_reminders()
    {
        $this->load->model('ticket_feedback_model');
        $this->load->model('emails_model');

        $days = 3; // Send reminders after 3 days
        $tickets = $this->ticket_feedback_model->get_tickets_without_feedback($days);

        foreach ($tickets as $ticket) {
            // Prepare email variables
            $email_to = $ticket['email'];
            $subject = "Reminder: Please provide feedback for your support ticket";
            $feedback_link = site_url('ticket_feedback/client_ticket_feedback/submit_feedback_form/' . $ticket['ticketid']);

            // Email message content
            $message = "
            <p>Hello " . $ticket['client_name'] . ",</p>
            <p>We noticed that you haven’t provided feedback for your support ticket: <strong>" . $ticket['subject'] . "</strong>.</p>
            <p>Your feedback is valuable to us! Please take a moment to share your experience:</p>
            <p><a href='" . $feedback_link . "' style='background: #28a745; color: #fff; padding: 10px 15px; text-decoration: none;'>Submit Feedback</a></p>
            <p>Thank you for your time!</p>
        ";

            // Send email
            $this->emails_model->send_simple_email($email_to, $subject, $message);
        }

        log_activity('Feedback reminder emails sent to clients.');
    }




}

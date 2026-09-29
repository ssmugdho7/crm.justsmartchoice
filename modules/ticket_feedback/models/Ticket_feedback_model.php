<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ticket_feedback_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function log_feedback_request($ticket_id)
    {
        return $this->db->insert('ticket_feedback', [
            'ticket_id' => $ticket_id,
            'email_sent' => 1,
            'feedback_received' => 0
        ]);
    }


    public function get_all_feedback_requests()
    {
        return $this->db->get('ticket_feedback')->result_array();
    }

    public function get_closed_tickets_without_feedback()
    {
        $this->db->select('t.ticketid, t.subject');
        $this->db->from(db_prefix() . 'tickets t');
        $this->db->join(db_prefix() . 'ticket_feedback tf', 't.ticketid = tf.ticket_id', 'left');
        $this->db->where('t.status', 5); // Only Closed Tickets
        $this->db->where('(tf.email_sent IS NULL OR tf.email_sent = 0)'); // Feedback request NOT sent
        return $this->db->get()->result_array();
    }



    public function get_client_tickets_without_feedback($client_id)
    {
        $this->db->select('t.ticketid, t.subject, t.status, t.lastreply, 
                           IFNULL(tf.feedback_received, 0) as feedback_received'); // Ensure it returns 0 if null
        $this->db->from(db_prefix() . 'tickets t');
        $this->db->join(db_prefix() . 'ticket_feedback tf', 't.ticketid = tf.ticket_id', 'left');
        $this->db->where('t.userid', $client_id);
        $this->db->where('tf.email_sent', 1); // Ensure feedback was requested by admin
        return $this->db->get()->result_array();
    }






    /**
     * Store client feedback in the database
     */
    public function add_feedback($data)
    {
        return $this->db->insert('tblticket_feedback', $data);
    }

    public function get_feedback_by_customer($customer_id)
    {
        $this->db->select('
            tblticket_feedback.*, 
            tbltickets.subject, 
            tbltickets.ticketid, 
            IF(tbltickets.date IS NOT NULL AND tblticket_feedback.created_at IS NOT NULL, 
                TIMESTAMPDIFF(HOUR, tbltickets.date, tblticket_feedback.created_at), 
                NULL) AS response_time
        ');
        $this->db->from('tblticket_feedback');
        $this->db->join('tbltickets', 'tbltickets.ticketid = tblticket_feedback.ticket_id');
        $this->db->where('tbltickets.userid', $customer_id);
        $this->db->where('tblticket_feedback.feedback_received', 1); // ✅ Only show received feedback
        return $this->db->get()->result_array();
    }



    public function get_feedback_by_staff()
    {
        $query = $this->db->query("
            SELECT
                s.staffid,
                CONCAT(s.firstname, ' ', s.lastname) AS staff_name,
                COUNT(tf.id) AS total_feedbacks,
                AVG(tf.rating) AS avg_rating,
                SUM(CASE WHEN t.status = 5 THEN 1 ELSE 0 END) AS resolved_tickets
            FROM " . db_prefix() . "ticket_feedback tf
            JOIN " . db_prefix() . "tickets t ON tf.ticket_id = t.ticketid
            JOIN " . db_prefix() . "staff s ON t.assigned = s.staffid
            WHERE tf.feedback_received = 1  -- ✅ Only count feedback that has been submitted
            GROUP BY s.staffid
        ");

        return $query->result_array();
    }


    public function send_feedback_reminders()
    {
        $this->load->model('emails_model');
    
        // ✅ Fetch email from `tblcontacts` and company name from `tblclients`
        $tickets = $this->db->select('t.ticketid, cl.company, c.email') // ✅ Corrected alias
            ->from(db_prefix() . 'tickets t')
            ->join(db_prefix() . 'ticket_feedback tf', 't.ticketid = tf.ticket_id', 'left')
            ->join(db_prefix() . 'clients cl', 't.userid = cl.userid', 'left') // ✅ Fetch company name
            ->join(db_prefix() . 'contacts c', 'c.userid = cl.userid AND c.is_primary = 1', 'left') // ✅ Get primary contact's email
            ->where('tf.email_sent', 1)  // ✅ Feedback was requested
            ->where('tf.feedback_received', 0)  // ❌ But not received yet
            ->get()
            ->result_array();
    
        foreach ($tickets as $ticket) {
            if (!empty($ticket['email'])) { // ✅ Ensure email exists
                $feedback_link = site_url('ticket_feedback/client_ticket_feedback/submit_feedback_form/' . $ticket['ticketid']);
                $message = "<p>Hello " . $ticket['company'] . ",</p>
                            <p>We value your feedback on your support request.</p>
                            <p>Please take a moment to complete a short survey by clicking the link below:</p>
                            <p><a href='{$feedback_link}'>Provide Feedback</a></p>
                            <p>Thank you!</p>";						                    		
    
                // ✅ Send email
                $this->emails_model->send_simple_email($ticket['email'], "Reminder: Provide Feedback", $message);
            } else {
                log_activity('ERROR: No email found for Ticket ID: ' . $ticket['ticketid']);
            }
        }
    
        log_activity('Feedback reminder emails sent successfully.');
    }
    
    






}

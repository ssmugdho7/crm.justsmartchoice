<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Project_feedback_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Get all feedback for projects
    public function get_all_feedback()
    {
        $query = $this->db->query("
        SELECT pf.*, p.name AS project_name, c.company AS customer_name
        FROM " . db_prefix() . "project_feedback pf
        JOIN " . db_prefix() . "projects p ON pf.project_id = p.id
        JOIN " . db_prefix() . "clients c ON pf.customer_id = c.userid
        WHERE pf.feedback_received = 1 -- ✅ Show only feedback actually submitted by the customer
    ");
        return $query->result_array();
    }

    // Store new feedback for a project
    public function add_feedback($data)
    {
        return $this->db->insert(db_prefix() . 'project_feedback', $data);
    }

    // Get feedback by project ID
    public function get_feedback_by_project($project_id)
    {
        return $this->db->where('project_id', $project_id)->get(db_prefix() . 'project_feedback')->row_array();
    }

    public function get_client_projects($customer_id)
    {
        $this->db->select('p.id, p.name, p.status, 
                        (SELECT COUNT(id) FROM ' . db_prefix() . 'project_feedback pf 
                         WHERE pf.project_id = p.id AND pf.feedback_received = 1) AS feedback_given'); // ✅ Only count received feedback
        $this->db->from(db_prefix() . 'projects p');
        $this->db->join(db_prefix() . 'project_feedback pf', 'pf.project_id = p.id', 'inner'); // ✅ Only include projects where feedback was requested
        $this->db->where('p.clientid', $customer_id);
        $this->db->where('p.status', 4); // ✅ Ensure project is finished
        $this->db->where('pf.email_sent', 1); // ✅ Only show projects where feedback request was sent

        $query = $this->db->get();
        return $query->result_array();
    }






    public function get_project($project_id)
    {
        return $this->db->where('id', $project_id)->get(db_prefix() . 'projects')->row();
    }

    public function insert_feedback($data)
    {
        return $this->db->insert(db_prefix() . 'project_feedback', $data);
    }

    public function get_completed_projects($customer_id)
    {
        if (!$customer_id) {
            return []; // ✅ Return empty if no customer selected
        }
    
        $this->db->select('p.id, p.name');
        $this->db->from(db_prefix() . 'projects p');
        $this->db->where('p.status', 4); // ✅ Only completed projects
        $this->db->where('p.clientid', $customer_id); // ✅ Filter by selected customer
        $this->db->where("NOT EXISTS (
            SELECT 1 FROM " . db_prefix() . "project_feedback pf 
            WHERE pf.project_id = p.id
        )");
    
        return $this->db->get()->result(); // ✅ Return array instead of echoing JSON
    }
    
    

    


    public function log_feedback_request($project_id, $customer_id)
    {
        // Check if a feedback entry already exists
        $existing = $this->db->get_where(db_prefix() . 'project_feedback', [
            'project_id' => $project_id,
            'customer_id' => $customer_id
        ])->row();

        if ($existing) {
            // ✅ Only update `email_sent`, do NOT modify `feedback_received`
            $this->db->where('id', $existing->id);
            return $this->db->update(db_prefix() . 'project_feedback', ['email_sent' => 1]);
        } else {
            // ✅ Only insert if it's genuinely new (feedback_received remains 0 until submitted)
            $data = [
                'project_id' => $project_id,
                'customer_id' => $customer_id,
                'email_sent' => 1,
                'feedback_received' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            return $this->db->insert(db_prefix() . 'project_feedback', $data);
        }
    }







}

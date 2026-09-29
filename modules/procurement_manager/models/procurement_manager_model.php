<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Procurement_manager_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get list of items, optionally filtered by project_id
     */
    public function get_items($project_id = null)
    {
        if ($project_id) {
            $this->db->where('project_id', $project_id);
        }
        $this->db->order_by('date_created', 'DESC');
        return $this->db->get(db_prefix() . 'procurement_items')->result_array();
    }

    /**
     * Get single item
     */
    public function get_item($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'procurement_items')->row_array();
    }

    /**
     * Add new item
     */
    public function add_item($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'procurement_items', $data);
        return $this->db->insert_id();
    }

    /**
     * Update item
     */
    public function update_item($data, $id)
    {
        $data['date_updated'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'procurement_items', $data);
    }

    /**
     * Delete item
     */
    public function delete_item($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'procurement_items');
        return true;
    }

    /**
     * Summary by supplier for dashboard chart
     */
    public function get_summary_by_supplier($project_id = null)
    {
        $this->db->select('supplier_name, SUM(COALESCE(quoted_price,0)) as total_quoted', false);
        if ($project_id) {
            $this->db->where('project_id', $project_id);
        }
        $this->db->group_by('supplier_name');
        return $this->db->get(db_prefix() . 'procurement_items')->result_array();
    }

    /**
     * Summary by category for dashboard chart
     */
    public function get_summary_by_category($project_id = null)
    {
        $this->db->select('category, SUM(COALESCE(quoted_price,0)) as total_quoted', false);
        if ($project_id) {
            $this->db->where('project_id', $project_id);
        }
        $this->db->group_by('category');
        return $this->db->get(db_prefix() . 'procurement_items')->result_array();
    }
}

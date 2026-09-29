<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Documents_model extends App_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    private function clean_data($data)
  {
    $allowed = ['engineering_project_id', 'project_id', 'customer_id', 'name', 'noc', 'eng_letter', 'site_insp', 'permit', 'folder_path', 'datecreated'];
    return array_intersect_key((array)$data, array_flip($allowed));
  }

  /**
     * @param  integer (optional)
     * @return object
     * Get single document
     */
    public function get($id = '', $exclude_notified = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'eng_documents')->row();
        }

        return $this->db->get(db_prefix() . 'eng_documents')->result_array();
    }

    public function get_all_documents($exclude_notified = true)
    {
        $documents = $this->db->get(db_prefix() . 'eng_documents')->result_array();
        return array_values($documents);
    }

    /**
     * Add new document
     * @param mixed $data All $_POST dat
     * @return mixed
     */
    public function add($data)
    {
        $data = $this->clean_data($data);
    $this->db->insert(db_prefix() . 'eng_documents', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Engineering Record Added [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update document
     * @param  mixed $data All $_POST data
     * @param  mixed $id   document id
     * @return boolean
     */
    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $data = $this->clean_data($data);
    $this->db->update(db_prefix() . 'eng_documents', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Engineering Record Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete document
     * @param  mixed $id document id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'eng_documents');
        if ($this->db->affected_rows() > 0) {
            log_activity('Engineering Record Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }
}

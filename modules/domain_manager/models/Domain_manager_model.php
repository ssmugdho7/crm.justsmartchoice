<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Domain_manager_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();

    }
    /**
     * Retrieve domain_manager data.
     *
     * @param int|string $id Optional ID of the domain_manager.
     * @return array|object Returns all records if no ID is provided, otherwise returns a single record.
     */
    public function get($id = ''){
        if($id == ''){
            return  $this->db->get(db_prefix().'domain_manager')->result_array();
        }else{
            $this->db->where('id',$id);
            return $this->db->get(db_prefix().'domain_manager')->row();
        }
    }
     /**
     * Add a new domain_manager record.
     *
     * @param array $data Array of domain_manager data.
     * @return int The ID of the newly inserted domain_manager.
     */
    public function add($data){
        $this->db->insert(db_prefix() . 'domain_manager', $data);
        return $this->db->insert_id();
    }
    /**
     * Update an existing domain_manager record.
     *
     * @param array $data Array of domain_manager data, including ID for the record to be updated.
     * @return bool|int Returns the number of affected rows or false if no ID is provided.
     */
   
    public function update($id,$data)
    {
        if ($id) {
            unset($data['id']);
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'domain_manager', $data);

            return $this->db->affected_rows();
        }

        return false;
    }
     /**
     * Retrieve all domain_managers created by the current staff member.
     *
     * @return array Returns an array of domain_managers created by the current staff member.
     */
    public function all(){
        $CI = &get_instance();
        $CI->db->from(db_prefix() . 'domain_manager');
        $CI->db->where('created_by', get_staff_user_id());
        $query = $CI->db->get();
        return $query->result_array();

    }
    /**
     * Delete a domain_manager record by ID.
     *
     * @param int $id ID of the domain_manager to be deleted.
     * @return bool Returns true if the record was deleted successfully, otherwise false.
     */
    public function delete($id)
    {
        if (isset($id) && is_numeric($id)) {
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'domain_manager');
            return ($this->db->affected_rows() > 0);
        }
        return false;
    }

     /**
     * Get Projects
     * @param  mixed project (Optional)
     * @return mixed     object or array
     */
    public function get_projects()
    {
        return $this->db->get(db_prefix() . 'projects')->result_array();
    }

     /**
     * Get Projects
     * @param  mixed project (Optional)
     * @return mixed     object or array
     */
    public function get_clients()
    {
        return $this->db->get(db_prefix() . 'clients')->result_array();
    }
}
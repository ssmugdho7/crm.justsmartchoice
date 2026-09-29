<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexagiletasks_model extends App_Model
{
    protected $table = 'flexagiletasks';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array $conditions
     * @return array|array[]
     * get all models
     */
    public function all($conditions = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if(!empty($conditions)){
            $this->db->where($conditions);
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * @param $conditions
     * @return array
     * get model by id
     */
    public function get($conditions)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if(!empty($conditions)){
            $this->db->where($conditions);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    /**
     * @param $data
     * @return bool
     * add model
     */
    public function add($data)
    {
        $this->db->insert(db_prefix() . $this->table, $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * @param $data
     * @param $id
     * @return bool
     * update model
     */
    public function update($data, $id)
    {
        $this->db->where('id', $id);
        if ($this->db->update(db_prefix() . $this->table, $data)) {
            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     * delete model
     */
    public function delete($id, $by_task_id = false, $sprint_id = null)
    {
        if($by_task_id){
            $this->db->where('task_id', $id);
        }elseif($sprint_id) {
            $this->db->where('sprint_id', $sprint_id);
        }else{
            $this->db->where('id', $id);
        }
        if ($this->db->delete(db_prefix() . $this->table)) {
            return true;
        }
        return false;
    }
}
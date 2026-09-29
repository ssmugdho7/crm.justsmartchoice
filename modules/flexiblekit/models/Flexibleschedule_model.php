<?php

use Carbon\Carbon;

defined('BASEPATH') or exit('No direct script access allowed');

class Flexibleschedule_model extends App_Model
{
    protected $table = 'flexibleschedules';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array $conditions
     * @return array|array[]
     * get all models
     */
    public function all($conditions = [], $sortings = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }

        if (!empty($sortings)) {
            for ($i = 0; $i < count($sortings); $i++) {
                $this->db->order_by($sortings[$i]['field'], $sortings[$i]['order']);
            }
        }
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * @param $conditions
     * @return array
     * get model by conditions
     */
    public function get($conditions)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where($conditions);
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
            log_activity('New Schedule Added [ID:' . $insert_id . ', ' . $data['flexibleschedule_subject'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     * update model
     */
    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $this->table, $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Schedule Updated [ID:' . $id . ', ' . $data['flexibleschedule_subject'] . ']');
            return true;
        }
        return false;
    }

    /**
     * @param $conditions
     * @return bool
     * delete model
     */
    public function delete($conditions)
    {
        $this->db->where($conditions);
        $this->db->delete(db_prefix() . $this->table);
        if ($this->db->affected_rows() > 0) {
            log_activity('Schedule Deleted');
            return true;
        }
        return false;
    }

    public function previous($user_id, $limit = 3)
    {
        $conditions = [
            'flexibleschedule_userid' => $user_id,
            'flexibleschedule_start_datetime <=' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where($conditions)
        ->order_by('flexibleschedule_start_datetime', 'desc')
        ->limit($limit);
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    public function team($user_id, $limit = 3)
    {
        $conditions = [
            'flexibleschedule_userid !=' => $user_id,
            'flexibleschedule_status' => FLEXIBLEKIT_SCHEDULED_STATUS
        ];

        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where($conditions)
        ->order_by('flexibleschedule_start_datetime', 'asc')
        ->limit($limit);
        
        $query = $this->db->get();
        return $query->result_array();
    }
}

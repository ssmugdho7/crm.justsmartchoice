<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Import_Tasks_model extends App_Model
{
    private $tableName;

    public function __construct()
    {
        parent::__construct();
        $this->tableName = db_prefix() . 'import_tasks_history';
    }

    public function store($data)
    {
        if (!isset($data['created'])) {
            $data['created'] = date('Y-m-d H:i:s');
        }
        $this->db->insert($this->tableName, $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Tasks Import Added [ID: ' . $insert_id . ', Filename: ' . $data['filename'] . ']');
        }
        return $insert_id;
    }

    public function get($id = '')
    {
        $this->db->select('*');
        if ($id != '') {
            $this->db->where('id', $id);
        }
        return $this->db->get($this->tableName)->result_array();
    }

    public function getAll()
    {
        $this->db->select('*');
        return $this->db->get($this->tableName)->result_array();
    }

    public function delete($id)
    {
        $this->db->select('*');
        if ($id != '') {
            $this->db->where('id', $id);
        }
        return $this->db->delete($this->tableName);
    }

    public function setTasksCount($id, $count)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, ['tasks_count' => $count]);
    }
}

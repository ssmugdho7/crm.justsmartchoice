<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Taskbookmarks_model extends App_Model
{
	public function __construct()
    {
        parent::__construct();
    }

    public function add_taskbookmarks($data){
        
        $this->db->insert('tbltaskbookmarks', $data);
        $taskbookmarks_id = $this->db->insert_id();

        if($taskbookmarks_id){
            return $taskbookmarks_id;
        }
        return false;
    }

    public function update_taskbookmarks($data, $id){
        
        $this->db->where('id', $id);
        $this->db->update('tbltaskbookmarks', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function get_taskbookmarks($id = ''){
        if($id != ''){
            $this->db->where('id',$id);
            $taskbookmarks = $this->db->get('tbltaskbookmarks')->result_array();
            $taskbookmarks[0]['list_task'] = $this->get_taskbookmarks_list_task($id);
            return $taskbookmarks[0];
        }else{
            $this->db->where('creator',get_staff_user_id());
            $taskbookmarks = $this->db->get('tbltaskbookmarks')->result_array();
            return $taskbookmarks;
        }
        
    }

    public function get_taskbookmarks_by_task_id($taskid){

        $this->db->where('task_id',$taskid);
        $taskbookmarks = $this->db->get('tbltaskbookmarks_detail')->result_array();
        
        if(count($taskbookmarks) > 0){
            $value_return = [];
            foreach ($taskbookmarks as $value) {
                $value_return[] = $this->get_taskbookmarks($value['taskbookmarks_id']);
            }
          return $value_return;
        }
        return false;
    }

    public function get_taskbookmarks_list_task($id, $is_string = false){
        $data = [];
        $this->db->where('taskbookmarks_id',$id);
        $list_task = $this->db->get('tbltaskbookmarks_detail')->result_array();

        foreach($list_task as $row){
            $data[] = $row['task_id'];
        }
        if($is_string == true){
            $data = implode(',', $data);
        }
        return $data;
    }
    
    public function delete_taskbookmarks($id){
        $this->db->where('taskbookmarks_id', $id);
        $this->db->delete('tbltaskbookmarks_detail');
        $this->db->where('id', $id);
        $this->db->delete('tbltaskbookmarks');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function add_task_filter_widget($data){
        $this->db->insert('tbllist_widget', $data);
        $filter_id = $this->db->insert_id();
        return $filter_id;
    }

    public function remove_task_filter_widget($id){
        $this->db->where('id', $id);
        $this->db->delete('tbllist_widget');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function get_filter_widget($staff, $type = ''){
        return $this->db->query('select * from tbllist_widget where add_from = '.htmlspecialchars($staff).' and rel_type = "'.htmlspecialchars($type).'"')->result_array();
    }

    public function view_taskbookmarks_helper($id){
        $taskbookmarks = $this->get_taskbookmarks($id);
        $list_tasks = $this->get_taskbookmarks_list_task($id, true);
        
        $data = [];
        $data['taskbookmarks'] = $taskbookmarks;
        $data['list_tasks'] = $list_tasks;
        $data['id'] = $id;
        $data['title'] = $taskbookmarks['name'];
        
        return $data;
    }
}
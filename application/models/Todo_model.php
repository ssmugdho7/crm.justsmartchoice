<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Todo_model extends App_Model
{
    public $todo_limit;

    public function __construct()
    {
        parent::__construct();
        $this->todo_limit = hooks()->apply_filters('todos_limit', 10);
    }

    public function setTodosLimit($limit) { $this->todo_limit = $limit; }
    public function getTodosLimit() { return $this->todo_limit; }

    private function can_access($id)
    {
        $staffId = (int) get_staff_user_id();
        $this->db->where('todoid', (int) $id);
        $this->db->group_start();
        $this->db->where('staffid', $staffId);
        if ($this->db->table_exists(db_prefix() . 'todoassignees')) {
            $this->db->or_where('EXISTS (SELECT 1 FROM ' . db_prefix() . 'todoassignees ta WHERE ta.todoid=' . db_prefix() . 'todos.todoid AND ta.staffid=' . $staffId . ')', null, false);
        }
        $this->db->group_end();
        return $this->db->get(db_prefix() . 'todos')->row();
    }

    public function get($id = '')
    {
        $staffId = (int) get_staff_user_id();
        if (is_numeric($id)) {
            $todo = $this->can_access($id);
            if ($todo && $this->db->table_exists(db_prefix() . 'todoassignees')) {
                $todo->assignees = array_map('intval', array_column($this->db->select('staffid')->where('todoid', (int)$id)->get(db_prefix().'todoassignees')->result_array(), 'staffid'));
            } elseif ($todo) {
                $todo->assignees = [];
            }
            return $todo;
        }

        $this->db->group_start();
        $this->db->where('staffid', $staffId);
        if ($this->db->table_exists(db_prefix() . 'todoassignees')) {
            $this->db->or_where('EXISTS (SELECT 1 FROM ' . db_prefix() . 'todoassignees ta WHERE ta.todoid=' . db_prefix() . 'todos.todoid AND ta.staffid=' . $staffId . ')', null, false);
        }
        $this->db->group_end();
        return $this->db->get(db_prefix().'todos')->result_array();
    }


    public function count_todo_items($finished)
    {
        $staffId = (int) get_staff_user_id();
        $this->db->from(db_prefix().'todos');
        $this->db->where('finished', (int)$finished);
        $this->db->group_start();
        $this->db->where(db_prefix().'todos.staffid', $staffId);
        if ($this->db->table_exists(db_prefix() . 'todoassignees')) {
            $this->db->or_where('EXISTS (SELECT 1 FROM ' . db_prefix() . 'todoassignees ta WHERE ta.todoid=' . db_prefix() . 'todos.todoid AND ta.staffid=' . $staffId . ')', null, false);
        }
        $this->db->group_end();
        return (int)$this->db->count_all_results();
    }

    public function get_todo_items($finished, $page = '')
    {
        $staffId = (int) get_staff_user_id();
        $this->db->select(db_prefix().'todos.*');
        $this->db->from(db_prefix().'todos');
        $this->db->where('finished', $finished);
        $this->db->group_start();
        $this->db->where(db_prefix().'todos.staffid', $staffId);
        if ($this->db->table_exists(db_prefix() . 'todoassignees')) {
            $this->db->or_where('EXISTS (SELECT 1 FROM ' . db_prefix() . 'todoassignees ta WHERE ta.todoid=' . db_prefix() . 'todos.todoid AND ta.staffid=' . $staffId . ')', null, false);
        }
        $this->db->group_end();
        $this->db->order_by('item_order', 'asc');
        if ($page != '' && $this->input->post('todo_page')) {
            $this->db->limit($this->todo_limit, ($page * $this->todo_limit));
        } else {
            $this->db->limit($this->todo_limit);
        }
        $todos = $this->db->get()->result_array();
        foreach ($todos as $i => $todo) {
            $todos[$i]['dateadded']    = _dt($todo['dateadded']);
            $todos[$i]['datefinished'] = _dt($todo['datefinished']);
            $todos[$i]['description']  = $todo['description'];
            $todos[$i]['title']        = isset($todo['title']) ? $todo['title'] : '';
            $todos[$i]['is_owner']     = ((int)$todo['staffid'] === $staffId);
            $todos[$i]['shared_with']  = $this->assignee_names((int)$todo['todoid']);
        }
        return $todos;
    }

    public function add($data)
    {
        $assignees = isset($data['assignees']) ? (array)$data['assignees'] : [];
        unset($data['assignees']);
        $data['dateadded']   = date('Y-m-d H:i:s');
        $data['description'] = nl2br($data['description']);
        $data['title']       = trim((string)($data['title'] ?? ''));
        $data['staffid']     = get_staff_user_id();
        $this->db->insert(db_prefix().'todos', $data);
        $id = (int)$this->db->insert_id();
        if ($id) { $this->sync_assignees($id, $assignees, true); }
        return $id;
    }

    public function update($id, $data)
    {
        $todo = $this->can_access($id);
        if (!$todo || (int)$todo->staffid !== (int)get_staff_user_id()) { return false; }
        $assignees = isset($data['assignees']) ? (array)$data['assignees'] : null;
        unset($data['assignees']);
        $data['description'] = nl2br($data['description']);
        $data['title']       = trim((string)($data['title'] ?? ''));
        $this->db->where('todoid', $id)->update(db_prefix().'todos', $data);
        $changed = $this->db->affected_rows() > 0;
        if ($assignees !== null) { $changed = $this->sync_assignees((int)$id, $assignees, true) || $changed; }
        return $changed;
    }

    private function sync_assignees($todoId, array $assignees, $notify = false)
    {
        if (!$this->db->table_exists(db_prefix().'todoassignees')) { return false; }
        $owner = (int)get_staff_user_id();
        $assignees = array_values(array_unique(array_filter(array_map('intval', $assignees), fn($id) => $id > 0 && $id !== $owner)));
        $existing = array_map('intval', array_column($this->db->select('staffid')->where('todoid', $todoId)->get(db_prefix().'todoassignees')->result_array(), 'staffid'));
        $changed = ($existing !== $assignees);
        $this->db->where('todoid', $todoId)->delete(db_prefix().'todoassignees');
        foreach ($assignees as $staffId) {
            $this->db->insert(db_prefix().'todoassignees', ['todoid'=>$todoId,'staffid'=>$staffId]);
        }
        if ($notify) {
            $newAssignees = array_values(array_diff($assignees, $existing));
            $notified = [];
            $todo = $this->db->where('todoid', $todoId)->get(db_prefix().'todos')->row();
            foreach ($newAssignees as $staffId) {
                $ok = add_notification([
                    'description' => 'todo_shared_notification',
                    'touserid' => $staffId,
                    'fromuserid' => $owner,
                    'link' => 'todo',
                    'additional_data' => serialize([$todo && $todo->title ? $todo->title : _l('todo')]),
                ]);
                if ($ok) { $notified[] = $staffId; }
            }
            if ($notified) { pusher_trigger_notification($notified); }
        }
        return $changed;
    }

    private function assignee_names($todoId)
    {
        if (!$this->db->table_exists(db_prefix().'todoassignees')) { return ''; }
        $rows = $this->db->select('s.staffid,s.firstname,s.lastname')->from(db_prefix().'todoassignees ta')->join(db_prefix().'staff s','s.staffid=ta.staffid','left')->where('ta.todoid',$todoId)->get()->result_array();
        return implode(', ', array_filter(array_map(fn($r) => trim($r['firstname'].' '.$r['lastname']), $rows)));
    }

    public function update_todo_items_order($data)
    {
        for ($i = 0; $i < count($data['data']); $i++) {
            $todo = $this->can_access($data['data'][$i][0]);
            if (!$todo) { continue; }
            $update = ['item_order'=>$data['data'][$i][1], 'finished'=>$data['data'][$i][2]];
            if ($data['data'][$i][2] == 1) { $update['datefinished'] = date('Y-m-d H:i:s'); }
            $this->db->where('todoid', $data['data'][$i][0])->update(db_prefix().'todos', $update);
        }
    }

    public function delete_todo_item($id)
    {
        $this->db->where('todoid', $id)->where('staffid', get_staff_user_id())->delete(db_prefix().'todos');
        if ($this->db->affected_rows() > 0) {
            if ($this->db->table_exists(db_prefix().'todoassignees')) { $this->db->where('todoid',$id)->delete(db_prefix().'todoassignees'); }
            return true;
        }
        return false;
    }

    public function change_todo_status($id, $status)
    {
        $todo = $this->can_access($id);
        if (!$todo) { return ['success'=>false]; }
        $this->db->where('todoid', $id)->update(db_prefix().'todos', ['finished'=>$status,'datefinished'=>date('Y-m-d H:i:s')]);
        return ['success'=>$this->db->affected_rows() > 0];
    }
}

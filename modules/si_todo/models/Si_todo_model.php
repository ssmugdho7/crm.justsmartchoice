<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Si_todo_model extends App_Model
{
    public $todo_limit;

    public function __construct()
    {
        parent::__construct();
        $this->todo_limit = 20;
    }

    public function setTodosLimit($limit)
    {
        $limit = (int)$limit;
        $this->todo_limit = $limit > 0 ? $limit : 20;
    }

    public function getTodosLimit()
    {
        return (int)$this->todo_limit;
    }

    public function get($id = '')
    {
        $this->db->where('staffid', get_staff_user_id());
        if (is_numeric($id)) {
            $this->db->where('todoid', (int)$id);
            return $this->db->get(db_prefix() . 'si_todos')->row();
        }
        return $this->db->get(db_prefix() . 'si_todos')->result_array();
    }

    public function get_todo_items($finished, $page = '', $category_id = 0)
    {
        $finished = (int)$finished;
        $this->db->select(db_prefix() . 'si_todos.*,' . db_prefix() . 'si_todos_category.category_name,' . db_prefix() . 'si_todos_category.color');
        $this->db->from(db_prefix() . 'si_todos');
        $this->db->where('finished', $finished);
        $this->db->where(db_prefix() . 'si_todos.staffid', get_staff_user_id());
        if (is_numeric($category_id) && (int)$category_id > 0) {
            $this->db->where('category', (int)$category_id);
        }
        $this->db->join(db_prefix() . 'si_todos_category', db_prefix() . 'si_todos_category.id=' . db_prefix() . 'si_todos.category', 'left');
        $this->db->order_by('item_order', 'asc');

        if ($page !== '' && is_numeric($page)) {
            $position = max(0, ((int)$page * $this->todo_limit));
            $this->db->limit($this->todo_limit, $position);
        } else {
            $this->db->limit($this->todo_limit);
        }

        $todos = $this->db->get()->result_array();
        foreach ($todos as $i => $todo) {
            $todos[$i]['dateadded'] = !empty($todo['dateadded']) ? _dt($todo['dateadded']) : '';
            $todos[$i]['datefinished'] = !empty($todo['datefinished']) ? _dt($todo['datefinished']) : '';
            $todos[$i]['description'] = check_for_links($todo['description'] ?? '');
            $todos[$i]['priority'] = isset($todo['priority']) ? (int)$todo['priority'] : 1;
            $todos[$i]['category_name'] = $todo['category_name'] ?? '';
            $todos[$i]['color'] = $todo['color'] ?? '#333333';
        }
        return $todos;
    }

    public function get_total_pending_todo()
    {
        $this->db->select('count(todoid) as total_pending', false);
        $this->db->where('finished', 0);
        $this->db->where('staffid', get_staff_user_id());
        $result = $this->db->get(db_prefix() . 'si_todos');
        return $result && $result->row() ? (int)$result->row()->total_pending : 0;
    }

    public function add($data)
    {
        $data = $this->sanitize_todo_payload($data);
        $data['dateadded'] = date('Y-m-d H:i:s');
        $data['description'] = nl2br($data['description']);
        $data['staffid'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'si_todos', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $data = $this->sanitize_todo_payload($data);
        $data['description'] = nl2br($data['description']);
        $this->db->where('todoid', (int)$id);
        $this->db->where('staffid', get_staff_user_id());
        $this->db->update(db_prefix() . 'si_todos', $data);
        return $this->db->affected_rows() > 0;
    }

    public function update_todo_items_order($data)
    {
        $rows = isset($data['data']) && is_array($data['data']) ? $data['data'] : [];
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row[0], $row[1], $row[2])) {
                continue;
            }
            $update = [
                'item_order' => (int)$row[1],
                'finished'   => (int)$row[2],
            ];
            if ((int)$row[2] === 1) {
                $update['datefinished'] = date('Y-m-d H:i:s');
            }
            $this->db->where('todoid', (int)$row[0]);
            $this->db->where('staffid', get_staff_user_id());
            $this->db->update(db_prefix() . 'si_todos', $update);
        }
    }

    public function delete_todo_item($id)
    {
        $this->db->where('todoid', (int)$id);
        $this->db->where('staffid', get_staff_user_id());
        $this->db->delete(db_prefix() . 'si_todos');
        return $this->db->affected_rows() > 0;
    }

    public function change_todo_status($id, $status)
    {
        $status = (int)$status === 1 ? 1 : 0;
        $this->db->where('todoid', (int)$id);
        $this->db->where('staffid', get_staff_user_id());
        $this->db->update(db_prefix() . 'si_todos', [
            'finished'     => $status,
            'datefinished' => $status === 1 ? date('Y-m-d H:i:s') : null,
        ]);
        return ['success' => $this->db->affected_rows() > 0];
    }

    public function get_category($id = '')
    {
        $this->db->where('staffid', get_staff_user_id());
        if (is_numeric($id)) {
            $this->db->where('id', (int)$id);
            return $this->db->get(db_prefix() . 'si_todos_category')->row();
        }
        $this->db->select(db_prefix() . 'si_todos_category.*,(select count(todoid) from ' . db_prefix() . 'si_todos where category=' . db_prefix() . 'si_todos_category.id and staffid=' . (int)get_staff_user_id() . ') as total,(select count(todoid) from ' . db_prefix() . 'si_todos where category=' . db_prefix() . 'si_todos_category.id and finished=1 and staffid=' . (int)get_staff_user_id() . ') as finished', false);
        $this->db->order_by('cat_order', 'asc');
        return $this->db->get(db_prefix() . 'si_todos_category')->result_array();
    }

    public function add_category($data)
    {
        $data = $this->sanitize_category_payload($data);
        $data['dateadded'] = date('Y-m-d H:i:s');
        $data['staffid'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'si_todos_category', $data);
        return $this->db->insert_id();
    }

    public function update_category($id, $data)
    {
        $data = $this->sanitize_category_payload($data);
        $this->db->where('id', (int)$id);
        $this->db->where('staffid', get_staff_user_id());
        $this->db->update(db_prefix() . 'si_todos_category', $data);
        return $this->db->affected_rows() > 0;
    }

    public function update_todo_categories_order($data)
    {
        $rows = isset($data['data']) && is_array($data['data']) ? $data['data'] : [];
        foreach ($rows as $row) {
            if (!is_array($row) || !isset($row[0], $row[1])) {
                continue;
            }
            $this->db->where('id', (int)$row[0]);
            $this->db->where('staffid', get_staff_user_id());
            $this->db->update(db_prefix() . 'si_todos_category', ['cat_order' => (int)$row[1]]);
        }
    }

    public function delete_todo_category($id)
    {
        $this->db->where('id', (int)$id);
        $this->db->where('staffid', get_staff_user_id());
        $this->db->delete(db_prefix() . 'si_todos_category');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('category', (int)$id);
            $this->db->where('staffid', get_staff_user_id());
            $this->db->delete(db_prefix() . 'si_todos');
            return true;
        }
        return false;
    }

    public function get_settings()
    {
        $staffid = get_staff_user_id();
        $this->db->where('staffid', $staffid);
        $result = $this->db->get(db_prefix() . 'si_todos_settings')->row();
        if (!$result) {
            $this->db->insert(db_prefix() . 'si_todos_settings', ['staffid' => $staffid, 'dateadded' => date('Y-m-d H:i:s')]);
            $this->db->where('staffid', $staffid);
            $result = $this->db->get(db_prefix() . 'si_todos_settings')->row();
        }
        return $result ? (array)$result : [
            'todos_load_limit' => 20,
            'dashboard_finished_limit' => 5,
            'dashboard_unfinished_limit' => 5,
        ];
    }

    public function save_settings($data)
    {
        $payload = [
            'todos_load_limit' => max(1, (int)($data['todos_load_limit'] ?? 20)),
            'dashboard_finished_limit' => max(0, (int)($data['dashboard_finished_limit'] ?? 5)),
            'dashboard_unfinished_limit' => max(0, (int)($data['dashboard_unfinished_limit'] ?? 5)),
            'dateadded' => date('Y-m-d H:i:s'),
        ];
        $this->db->where('staffid', get_staff_user_id());
        $exists = $this->db->get(db_prefix() . 'si_todos_settings')->row();
        if ($exists) {
            $this->db->where('staffid', get_staff_user_id());
            $this->db->update(db_prefix() . 'si_todos_settings', $payload);
        } else {
            $payload['staffid'] = get_staff_user_id();
            $this->db->insert(db_prefix() . 'si_todos_settings', $payload);
        }
        return true;
    }

    public function health_check()
    {
        $tables = ['si_todos', 'si_todos_category', 'si_todos_settings'];
        $checks = [];
        foreach ($tables as $table) {
            $full = db_prefix() . $table;
            $checks[] = [
                'name' => ucwords(str_replace('_', ' ', $table)),
                'status' => $this->db->table_exists($full),
                'details' => $this->db->table_exists($full) ? total_rows($full) . ' records found' : 'Missing table',
            ];
        }
        return $checks;
    }

    private function sanitize_todo_payload($data)
    {
        return [
            'category' => max(0, (int)($data['category'] ?? 0)),
            'description' => trim((string)($data['description'] ?? '')),
            'priority' => min(4, max(1, (int)($data['priority'] ?? 1))),
        ];
    }

    private function sanitize_category_payload($data)
    {
        $color = trim((string)($data['color'] ?? '#333333'));
        if (!preg_match('/^#[a-fA-F0-9]{3,6}$/', $color)) {
            $color = '#333333';
        }
        return [
            'category_name' => trim((string)($data['category_name'] ?? '')),
            'color' => $color,
        ];
    }
}

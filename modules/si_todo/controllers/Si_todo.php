<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Si_todo extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('si_todo/si_todo_model');
    }

    public function index()
    {
        $category_id = $this->input->get('group');
        $settings = $this->si_todo_model->get_settings();
        $this->si_todo_model->setTodosLimit((int)($settings['todos_load_limit'] ?? 20));

        if ($this->input->is_ajax_request()) {
            echo json_encode($this->si_todo_model->get_todo_items((int)$this->input->post('finished'), (int)$this->input->post('todo_page'), $category_id));
            exit;
        }

        $data['bodyclass'] = 'si-todo-page';
        $where = [
            'staffid'  => get_staff_user_id(),
            'finished' => 1,
        ];

        if (is_numeric($category_id) && (int)$category_id > 0) {
            $where['category'] = (int)$category_id;
        }

        $limit = max(1, (int)$this->si_todo_model->getTodosLimit());
        $data['total_pages_finished'] = (int)ceil(total_rows(db_prefix() . 'si_todos', $where) / $limit);
        $where['finished'] = 0;
        $data['total_pages_unfinished'] = (int)ceil(total_rows(db_prefix() . 'si_todos', $where) / $limit);
        $data['categories'] = $this->si_todo_model->get_category();
        $data['total_pending'] = $this->si_todo_model->get_total_pending_todo();
        $data['title'] = _l('si_todo');
        $this->load->view('todo_list', $data);
    }

    public function todo()
    {
        if (!$this->input->post()) {
            redirect(admin_url('si_todo'));
        }

        $data = $this->input->post(null, true);
        $todoid = isset($data['todoid']) ? (int)$data['todoid'] : 0;
        unset($data['todoid']);

        if ($todoid <= 0) {
            $id = $this->si_todo_model->add($data);
            if ($id) {
                set_alert('success', _l('added_successfully', _l('todo')));
            }
        } else {
            $success = $this->si_todo_model->update($todoid, $data);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('todo')));
            }
        }

        redirect($this->agent->referrer() ?: admin_url('si_todo'));
    }

    public function get_by_id($id)
    {
        $todo = $this->si_todo_model->get((int)$id);
        if (!$todo) {
            echo json_encode(new stdClass());
            return;
        }
        $todo->description = clear_textarea_breaks($todo->description ?? '');
        echo json_encode($todo);
    }

    public function change_todo_status($id, $status)
    {
        $success = $this->si_todo_model->change_todo_status((int)$id, (int)$status);
        if (!empty($success['success'])) {
            set_alert('success', _l('todo_status_changed'));
        }
        redirect($this->agent->referrer() ?: admin_url('si_todo'));
    }

    public function update_todo_items_order()
    {
        if ($this->input->post()) {
            $this->si_todo_model->update_todo_items_order($this->input->post(null, true));
        }
    }

    public function delete_todo_item($id)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'success' => $this->si_todo_model->delete_todo_item((int)$id),
            ]);
        }
        die();
    }

    public function category_list()
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->si_todo_model->get_category());
            exit;
        }
        $data['bodyclass'] = 'si-todo-category-page';
        $data['title'] = _l('si_todo_category');
        $this->load->view('category_list', $data);
    }

    public function category()
    {
        if (!$this->input->post()) {
            redirect(admin_url('si_todo/category_list'));
        }

        $data = $this->input->post(null, true);
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        unset($data['id']);

        if ($id <= 0) {
            $new_id = $this->si_todo_model->add_category($data);
            echo json_encode([
                'success' => $new_id ? true : false,
                'message' => $new_id ? _l('added_successfully', _l('si_todo_category')) : '',
                'id'      => $new_id,
                'name'    => $this->input->post('category_name', true),
            ]);
            return;
        }

        $success = $this->si_todo_model->update_category($id, $data);
        if ($success) {
            set_alert('success', _l('updated_successfully', _l('si_todo_category')));
        }
        redirect($this->agent->referrer() ?: admin_url('si_todo/category_list'));
    }

    public function get_category_by_id($id)
    {
        $category = $this->si_todo_model->get_category((int)$id);
        if (!$category) {
            echo json_encode(new stdClass());
            return;
        }
        $category->category_name = clear_textarea_breaks($category->category_name ?? '');
        echo json_encode($category);
    }

    public function update_todo_categories_order()
    {
        if ($this->input->post()) {
            $this->si_todo_model->update_todo_categories_order($this->input->post(null, true));
        }
    }

    public function delete_todo_category($id)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'success' => $this->si_todo_model->delete_todo_category((int)$id),
            ]);
        }
        die();
    }

    public function settings()
    {
        if ($this->input->post()) {
            $data = $this->input->post(null, true);
            $success = $this->si_todo_model->save_settings($data);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('settings')));
            } else {
                set_alert('success', _l('si_todo_settings_saved'));
            }
            redirect(admin_url('si_todo/settings'));
        }
        $data['settings'] = $this->si_todo_model->get_settings();
        $data['title'] = _l('si_todo_settings');
        $this->load->view('settings', $data);
    }

    public function health()
    {
        $data['title'] = _l('si_todo_health_check');
        $data['health'] = $this->si_todo_model->health_check();
        $this->load->view('health', $data);
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Books extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('roles_model');
        $this->load->model('staff_model');
        $this->load->model('training_manual_books_model');
    }

    public function index()
    {
        if (!has_permission('training_manual_books', '', 'view') && !has_permission('training_manual_books', '', 'view_own')) {
            access_denied('training_manual_books');
        }
        $staff_id = function_exists('get_staff_user_id') ? get_staff_user_id() : $this->session->userdata('tfa_staffid');
        $user = get_staff($staff_id);
        $data['title'] = _l('training_manual_books_list');
        $filter_query = $this->input->get('filter_query');
        if(!isset($filter_query)){
            $filter_query = "";
        }
        $data['books'] = $this->training_manual_books_model->get_all_books($filter_query);
        $data['filter_query'] = $filter_query;
        $data['user_id'] = isset($user->staffid) ? $user->staffid : 0;
        $this->load->view('books_manage', $data);
    }

    public function customer()
    {
        if (!has_permission('training_manual_books', '', 'view') && !has_permission('training_manual_books', '', 'view_own')) {
            access_denied('training_manual_books');
        }
        $data['title'] = _l('training_manual_customer_books');
        $filter_query = (string) $this->input->get('filter_query');
        $data['books'] = $this->training_manual_books_model->get_customer_books($filter_query, false);
        $data['filter_query'] = $filter_query;
        $data['customer_books_mode'] = true;
        $this->load->view('books_manage', $data);
    }

    public function book($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('training_manual_books', '', 'create')) {
                    access_denied('training_manual_books');
                }
                $id = $this->training_manual_books_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('training_manual_book')));
                    redirect(admin_url('training_manual/books/book/' . $id));
                }
            } else {
                if (!has_permission('training_manual_books', '', 'edit')) {
                    access_denied('training_manual_books');
                }
                $success = $this->training_manual_books_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('training_manual_book')));
                }else{
                
                }
                $back_url = $this->input->post('back_url');
                if(isset($back_url)){
                    redirect($back_url);
                }else{
                    redirect(admin_url('training_manual/books/book/' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('training_manual_create_book');
        } else {
            $data['book']        = $this->training_manual_books_model->get($id);
            $back_url = $this->input->get('back_url');
            if(isset($back_url)){
                $data['back_url'] = $back_url;
            }
            $title = _l('edit', _l('training_manual_book_lowercase'));
        }
        $data['title']                 = $title;
        $data['roles']    = $this->roles_model->get();
        $data['members'] = $this->staff_model->get('', [
            'active'       => 1,
            'is_not_staff' => 0,
        ]);
        $this->load->view('book', $data);
    }

    public function delete($id)
    {
        if (!has_permission('training_manual_books', '', 'delete')) {
            access_denied('training_manual_books');
        }
        if (!$id) {
            redirect(admin_url('training_manual/books'));
        }
        $response = $this->training_manual_books_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('training_manual_book')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('training_manual_book_lowercase')));
        }
        redirect(admin_url('training_manual/books'));
    }
}

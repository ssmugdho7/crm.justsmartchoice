<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_links extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        smart_choice_links_ensure_database();
        $this->load->model('smart_choice_links/smart_choice_links_model');
    }

    public function index()
    {
        if (!is_admin() && !has_permission('settings', '', 'view')) {
            access_denied('smart_choice_links');
        }

        $data['title'] = _l('smart_choice_links');
        $data['links'] = $this->smart_choice_links_model->get();
        $this->load->view('manage', $data);
    }

    public function modal($id = 0)
    {
        if (!is_admin() && !has_permission('settings', '', 'edit')) {
            access_denied('smart_choice_links');
        }

        $data['link'] = $id ? $this->smart_choice_links_model->get($id) : null;
        $data['roles'] = $this->roles_model->get();
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('modal', $data);
    }

    public function save($id = 0)
    {
        if (!is_admin() && !has_permission('settings', '', 'edit')) {
            access_denied('smart_choice_links');
        }

        if ($this->input->post()) {
            if ((int) $id > 0) {
                $this->smart_choice_links_model->update($id, $this->input->post());
            } else {
                $this->smart_choice_links_model->add($this->input->post());
            }

            set_alert('success', _l('updated_successfully', _l('smart_choice_links')));
        }

        redirect(admin_url('smart_choice_links'));
    }


    public function reorder()
    {
        if (!is_admin() && !has_permission('settings', '', 'edit')) {
            access_denied('smart_choice_links');
        }

        $order = $this->input->post('order');

        if (is_string($order)) {
            $decoded = json_decode($order, true);
            if (is_array($decoded)) {
                $order = $decoded;
            }
        }

        if (!is_array($order)) {
            echo json_encode(['success' => false, 'message' => 'Invalid order data.']);
            return;
        }

        $position = 1;
        foreach ($order as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $this->db->where('id', $id);
                $this->db->update(SMART_CHOICE_LINKS_TABLE, [
                    'position' => $position,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $position++;
            }
        }

        echo json_encode(['success' => true]);
    }

    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('smart_choice_links');
        }

        $this->smart_choice_links_model->delete($id);
        set_alert('success', _l('deleted', _l('smart_choice_links')));
        redirect(admin_url('smart_choice_links'));
    }
}

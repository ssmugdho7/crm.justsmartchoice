<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Toast_master extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model');
        $this->load->helper('toast_master/toast_master');
    }

    public function settings()
    {
        if (!is_admin()) {
            access_denied('toast_master');
        }

        if ($this->input->post()) {
            $post_data = $this->input->post();
            $settings = isset($post_data['settings']) && is_array($post_data['settings']) ? $post_data['settings'] : [];

            $checkboxes = [
                'toast_master_enable',
                'toast_master_sound_enable',
                'toast_master_pause_on_hover',
                'toast_master_progress_bar',
                'toast_master_intercept_alert_float',
                'toast_master_intercept_browser_alert',
                'toast_master_intercept_unsaved_warning',
                'toast_master_intercept_ajax',
                'toast_master_intercept_validation',
                'toast_master_intercept_messages',
                'toast_master_intercept_announcements',
                'toast_master_intercept_module_updates',
                'toast_master_intercept_cron',
                'toast_master_history_enable',
            ];

            foreach ($checkboxes as $checkbox) {
                $settings[$checkbox] = isset($settings[$checkbox]) ? '1' : '0';
            }

            $settings['toast_master_version'] = '2.0.2';
            $post_data['settings'] = $settings;

            $success = $this->settings_model->update($post_data);
            if ($success > 0) {
                set_alert('success', _l('settings_updated'));
            } else {
                set_alert('warning', _l('toast_master_no_changes'));
            }

            redirect(admin_url('toast_master/settings'));
        }

        $data['title'] = _l('toast_master_setting');
        $data['styles'] = toast_master_styles();
        $data['positions'] = toast_master_positions();
        $data['sounds'] = toast_master_sounds();
        $data['animations'] = toast_master_animations();
        $data['channels'] = toast_master_channels();
        $this->load->view('settings', $data);
    }

    public function history()
    {
        if (!is_admin()) {
            access_denied('toast_master');
        }

        $data['title'] = _l('toast_master_history');
        $data['history'] = [];
        if ($this->db->table_exists(db_prefix() . 'toast_master_history')) {
            $this->db->order_by('id', 'DESC');
            $data['history'] = $this->db->get(db_prefix() . 'toast_master_history', 100)->result_array();
        }

        $this->load->view('history', $data);
    }

    public function clear_history()
    {
        if (!is_admin()) {
            access_denied('toast_master');
        }

        if ($this->db->table_exists(db_prefix() . 'toast_master_history')) {
            $this->db->truncate(db_prefix() . 'toast_master_history');
        }
        set_alert('success', _l('toast_master_history_cleared'));
        redirect(admin_url('toast_master/history'));
    }

    public function log()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $type = $this->input->post('type', true) ?: 'info';
        $title = $this->input->post('title', true) ?: '';
        $message = $this->input->post('message', true) ?: '';
        $source = $this->input->post('source', true) ?: 'crm';
        $url = $this->input->post('url', true) ?: '';

        toast_master_log($type, $message, $title, $source, $url);
        echo json_encode(['success' => true]);
    }
}

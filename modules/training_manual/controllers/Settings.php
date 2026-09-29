<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if (!is_admin()) {
            access_denied('Training Manual Settings');
        }
        if ($this->input->post()) {
            update_option('training_manual_default_language', $this->input->post('training_manual_default_language') ?: 'en');
            update_option('training_manual_default_style', $this->input->post('training_manual_default_style') ?: 'smart_choice');
            update_option('training_manual_external_css_enabled', $this->input->post('training_manual_external_css_enabled') ? '1' : '0');
            set_alert('success', 'Training Manual settings updated successfully.');
            redirect(admin_url('training_manual/settings'));
        }
        $data['title'] = 'Training Manual Settings';
        $this->load->view('settings', $data);
    }

    public function health()
    {
        if (!is_admin()) {
            access_denied('Training Manual Health Check');
        }
        $data['title'] = 'Training Manual Health Check';
        $data['checks'] = $this->run_health_checks();
        $this->load->view('health', $data);
    }

    private function run_health_checks()
    {
        $checks = [];
        $checks[] = ['name' => 'PHP Version', 'status' => version_compare(PHP_VERSION, '8.0.0', '>=') ? 'ok' : 'warning', 'message' => PHP_VERSION];
        $checks[] = ['name' => 'Wiki Books Table', 'status' => $this->db->table_exists(db_prefix() . 'wiki_books') ? 'ok' : 'bad', 'message' => db_prefix() . 'wiki_books'];
        $checks[] = ['name' => 'Wiki Articles Table', 'status' => $this->db->table_exists(db_prefix() . 'wiki_articles') ? 'ok' : 'bad', 'message' => db_prefix() . 'wiki_articles'];
        $checks[] = ['name' => 'Article Upload Folder', 'status' => is_writable(FCPATH . 'uploads/training_manual') ? 'ok' : 'warning', 'message' => FCPATH . 'uploads/training_manual'];
        $checks[] = ['name' => 'External CSS File', 'status' => file_exists(FCPATH . 'modules/training_manual/assets/css/smart_choice_training_manual_content.css') ? 'ok' : 'warning', 'message' => 'modules/training_manual/assets/css/smart_choice_training_manual_content.css'];
        return $checks;
    }
}

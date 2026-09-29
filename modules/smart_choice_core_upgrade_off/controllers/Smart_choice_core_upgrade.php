<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_core_upgrade extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smart_choice_core_upgrade/smart_choice_core_upgrade_model');
    }

    public function index()
    {
        $data['title'] = 'Smart Choice Core';
        $data['health'] = $this->smart_choice_core_upgrade_model->health_report();
        $data['version'] = $this->smart_choice_core_upgrade_model->version_info();
        $data['modules'] = $this->smart_choice_core_upgrade_model->embedded_modules();
        $this->load->view('smart_choice_core_upgrade/dashboard', $data);
    }

    public function health()
    {
        $data['title'] = 'Smart Choice Health Check';
        $data['health'] = $this->smart_choice_core_upgrade_model->health_report();
        $data['version'] = $this->smart_choice_core_upgrade_model->version_info();
        $this->load->view('smart_choice_core_upgrade/health', $data);
    }

    public function embedded_modules()
    {
        $data['title'] = 'Embedded Modules';
        $data['modules'] = $this->smart_choice_core_upgrade_model->embedded_modules();
        $this->load->view('smart_choice_core_upgrade/embedded_modules', $data);
    }

    public function reports()
    {
        $data['title'] = 'Smart Choice Reports';
        $data['reports'] = $this->smart_choice_core_upgrade_model->reports();
        $this->load->view('smart_choice_core_upgrade/reports', $data);
    }

    public function help()
    {
        $data['title'] = 'Smart Choice Help Guide';
        $this->load->view('smart_choice_core_upgrade/help', $data);
    }

    public function update_center()
    {
        $data['title'] = 'Smart Choice Update Center';
        $data['version'] = $this->smart_choice_core_upgrade_model->version_info();
        $data['history'] = $this->db->table_exists(db_prefix() . 'smart_choice_upgrade_history') ? $this->db->order_by('id', 'DESC')->limit(25)->get(db_prefix() . 'smart_choice_upgrade_history')->result() : [];
        $this->load->view('smart_choice_core_upgrade/update_center', $data);
    }

    public function save_settings()
    {
        if (!$this->input->post()) {
            redirect(admin_url('settings?group=smart_choice_core_upgrade'));
        }
        $textOptions = [
            'smart_choice_core_theme_name',
            'smart_choice_windows_timezone_label',
            'smart_choice_default_state',
            'smart_choice_default_tax_mode',
        ];
        foreach ($textOptions as $option) {
            update_option($option, trim((string)$this->input->post($option, true)));
        }
        $switches = [
            'smart_choice_core_theme_enabled',
            'smart_choice_core_main_menu_search',
            'smart_choice_core_setup_menu_search',
            'smart_choice_core_table_tools',
            'smart_choice_quickbooks_desktop_enabled',
            'smart_choice_reports_enabled',
        ];
        foreach ($switches as $option) {
            update_option($option, $this->input->post($option) === '1' ? '1' : '0');
        }
        $this->smart_choice_core_upgrade_model->apply_core_defaults();
        $this->smart_choice_core_upgrade_model->log('info', 'settings_saved', 'Smart Choice core settings saved.');
        set_alert('success', 'Smart Choice settings updated successfully.');
        redirect(admin_url('settings?group=smart_choice_core_upgrade'));
    }

    public function apply_defaults()
    {
        if (!$this->input->post()) {
            redirect(admin_url('smart_choice_core_upgrade'));
        }
        $this->smart_choice_core_upgrade_model->apply_core_defaults();
        $this->smart_choice_core_upgrade_model->log('info', 'defaults_applied', 'Smart Choice defaults applied manually.');
        set_alert('success', 'Smart Choice CRM 3.1.2 defaults applied successfully.');
        redirect(admin_url('smart_choice_core_upgrade'));
    }
}

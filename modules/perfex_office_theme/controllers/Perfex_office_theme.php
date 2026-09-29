<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Perfex_office_theme extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin()) {
            access_denied('Office Theme');
        }
    }

    public function index()
    {
        redirect(admin_url('perfex_office_theme/settings'));
    }

    public function settings()
    {
        $data = [];
        $data['title'] = 'Office Theme Settings';
        $this->load->view('perfex_office_theme/perfex_office_theme_settings_page', $data);
    }

    public function health()
    {
        $data = [];
        $data['title'] = 'Office Theme Health';
        $this->load->view('perfex_office_theme/perfex_office_theme_health_page', $data);
    }

    public function office_settings()
    {
        $this->settings();
    }

    public function office_health()
    {
        $this->health();
    }
}

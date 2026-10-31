<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Theme_style extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            access_denied('Theme Style');
        }
    }

    public function index()
    {
        $this->load->helper('theme_style/theme_style');
        $this->lang->load('theme_style/theme_style');
        $data['title'] = _l('theme_style');
        $this->load->view('theme_style', $data);
    }

    public function select_admin_theme()
    {
        if ($this->input->method() !== 'post') { show_404(); return; }
        $key = $this->input->post('admin_theme');
        if (!is_string($key) || !isset(smartchoice_admin_themes()[$key])) {
            show_error(_l('sc_admin_theme_invalid'), 400); return;
        }
        update_option('smartchoice_admin_theme', $key);
        set_alert('success', _l('settings_updated'));
        redirect(admin_url('theme_style'));
    }

    public function reset()
    {
        update_option('theme_style', '[]');
        redirect(admin_url('theme_style'));
    }

    public function save()
    {
        hooks()->do_action('before_save_theme_style');

        update_option('theme_style', $this->input->post('data'));

        foreach(['admin_area','clients_area','clients_and_admin'] as $css_area) {
            // Also created the variables
            $$css_area = $this->input->post($css_area);
            $$css_area = trim($$css_area);
            $$css_area = nl2br($$css_area);
        }

        update_option('theme_style_custom_admin_area', $admin_area);
        update_option('theme_style_custom_clients_area', $clients_area);
        update_option('theme_style_custom_clients_and_admin_area', $clients_and_admin);
    }
}

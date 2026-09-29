<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Styleflow extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('styleflow_model');
        $this->load->helper('styleflow/styleflow');
    }

    public function index(){ redirect(admin_url('styleflow/manage_templates')); }
    public function manage_invoice_templates(){ redirect(admin_url('styleflow/manage_templates')); }
    public function activate_invoice_template($theme){ return $this->activate('invoice', $theme); }


    public function settings()
    {
        styleflow_ensure_ready();
        if (!is_admin()) access_denied('styleflow');
        redirect(admin_url('settings?group=styleflow'));
    }

    public function save_document_assignments()
    {
        styleflow_ensure_ready();
        if (!is_admin()) access_denied('styleflow');
        if (!$this->input->post()) show_404();

        foreach (['invoice','estimate','proposal'] as $type) {
            $slug = trim((string) $this->input->post('styleflow_selected_' . $type . '_template', true));
            if ($slug === '') $slug = 'default';
            if (!styleflow_template_available($slug, $type)) {
                set_alert('danger', _l('styleflow_template_not_available'));
                redirect(admin_url('settings?group=styleflow'));
            }
            update_option('styleflow_selected_' . $type . '_template', $slug);
        }

        set_alert('success', _l('styleflow_settings_saved'));
        redirect(admin_url('settings?group=styleflow'));
    }

    public function manage_templates()
    {
        styleflow_ensure_ready();
        if (!has_permission('styleflow','','view')) access_denied('styleflow');
        $data['title'] = _l('styleflow') . ' - ' . _l('styleflow_document_templates');
        $data['templates'] = styleflow_get_templates();
        $this->load->view('manage', $data);
    }

    public function activate($type, $slug)
    {
        styleflow_ensure_ready();
        if (!has_permission('styleflow','','edit')) access_denied('styleflow');
        if (!in_array($type,['invoice','estimate','proposal'],true) || !styleflow_template_available($slug,$type)) {
            set_alert('danger', _l('styleflow_template_not_available'));
            redirect(admin_url('styleflow/manage_templates'));
        }
        update_option('styleflow_selected_'.$type.'_template', $slug);
        log_activity(_l('styleflow_activity_template_activated', _l('styleflow_'.$type), styleflow_template_display_name(styleflow_get_template($slug))));
        set_alert('success', _l('styleflow_activated_for', _l('styleflow_'.$type)));
        redirect(admin_url('styleflow/manage_templates'));
    }

    public function preview($slug)
    {
        styleflow_ensure_ready();
        if (!has_permission('styleflow','','view')) access_denied('styleflow');
        $tpl = styleflow_get_template($slug);
        if (!$tpl) show_404();
        $data['title'] = _l('styleflow_preview') . ' - ' . styleflow_template_display_name($tpl);
        $data['template'] = $tpl;
        $this->load->view('preview', $data);
    }

    public function save_template()
    {
        styleflow_ensure_ready();
        if (!has_permission('styleflow','','edit')) access_denied('styleflow');
        if (!$this->input->post()) show_404();
        $id = (int)$this->input->post('id');
        $result = $this->styleflow_model->save_template($this->input->post(null, true), $id);
        set_alert($result ? 'success' : 'danger', $result ? _l('updated_successfully', _l('styleflow_template')) : _l('problem_updating', _l('styleflow_template')));
        redirect(admin_url('settings?group=styleflow'));
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Usi_smartchoice_seo_automation extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usi_smartchoice_seo/Usi_smartchoice_seo_automation_model', 'sammy_automation');
    }

    public function index()
    {
        if (!has_permission('usi_smartchoice_seo', '', 'view') && !has_permission('usi_smartchoice_seo', '', 'view_own')) {
            access_denied('Sammy AI');
        }

        $filters = [
            'status' => $this->input->get('status', true),
            'priority' => $this->input->get('priority', true),
            'source_area' => $this->input->get('source_area', true),
        ];

        $data['title'] = 'Sammy AI Alerts & Automation Center';
        $data['alerts'] = $this->sammy_automation->get_alerts($filters);
        $data['queue'] = $this->sammy_automation->get_queue([]);
        $data['filters'] = $filters;
        $this->load->view('usi_smartchoice_seo/automation_center', $data);
    }

    public function create()
    {
        if (!has_permission('usi_smartchoice_seo', '', 'create')) {
            access_denied('Sammy AI');
        }
        if ($this->input->post()) {
            $this->sammy_automation->create_alert($this->input->post(null, true));
            set_alert('success', 'Alert created successfully.');
        }
        redirect(admin_url('usi_smartchoice_seo_automation'));
    }

    public function build_defaults()
    {
        if (!has_permission('usi_smartchoice_seo', '', 'create')) {
            access_denied('Sammy AI');
        }
        $count = $this->sammy_automation->build_default_alerts();
        set_alert('success', $count . ' default alerts prepared.');
        redirect(admin_url('usi_smartchoice_seo_automation'));
    }

    public function complete($id)
    {
        if (!has_permission('usi_smartchoice_seo', '', 'edit')) {
            access_denied('Sammy AI');
        }
        $this->sammy_automation->complete_alert((int) $id);
        set_alert('success', 'Alert marked completed.');
        redirect(admin_url('usi_smartchoice_seo_automation'));
    }

    public function delete($id)
    {
        if (!has_permission('usi_smartchoice_seo', '', 'delete')) {
            access_denied('Sammy AI');
        }
        $this->sammy_automation->delete_alerts([(int) $id]);
        set_alert('success', 'Alert deleted.');
        redirect(admin_url('usi_smartchoice_seo_automation'));
    }

    public function mass_delete()
    {
        if (!has_permission('usi_smartchoice_seo', '', 'delete')) {
            access_denied('Sammy AI');
        }
        $ids = $this->input->post('ids');
        $this->sammy_automation->delete_alerts($ids);
        set_alert('success', 'Selected alerts deleted.');
        redirect(admin_url('usi_smartchoice_seo_automation'));
    }
}

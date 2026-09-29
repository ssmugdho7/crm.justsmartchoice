<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Social_lead_funnel extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('social_lead_funnel/social_lead_funnel_model');
    }

    public function index()
    {
        if (!has_permission('social_lead_funnel', '', 'view')) {
            access_denied('social_lead_funnel');
        }
        $filters = [
            'status' => $this->input->get('status', true),
            'source' => $this->input->get('source', true),
            'search' => $this->input->get('search', true),
        ];
        $data['title'] = _l('social_lead_funnel');
        $data['opportunities'] = $this->social_lead_funnel_model->get_opportunities($filters);
        $data['filters'] = $filters;
        $this->load->view('social_lead_funnel/index', $data);
    }

    public function create()
    {
        if (!has_permission('social_lead_funnel', '', 'create')) {
            access_denied('social_lead_funnel');
        }
        if ($this->input->post()) {
            $id = $this->social_lead_funnel_model->create_opportunity($this->input->post(null, true));
            set_alert('success', _l('social_lead_funnel_created'));
            redirect(admin_url('social_lead_funnel/view/' . $id));
        }
        $data['title'] = _l('social_lead_funnel_new_opportunity');
        $this->load->view('social_lead_funnel/form', $data);
    }

    public function view($id)
    {
        if (!has_permission('social_lead_funnel', '', 'view')) {
            access_denied('social_lead_funnel');
        }
        $data['opportunity'] = $this->social_lead_funnel_model->get_opportunity($id);
        if (!$data['opportunity']) {
            show_404();
        }
        $data['title'] = _l('social_lead_funnel_opportunity');
        $this->load->view('social_lead_funnel/view', $data);
    }

    public function edit($id)
    {
        if (!has_permission('social_lead_funnel', '', 'edit')) {
            access_denied('social_lead_funnel');
        }
        if ($this->input->post()) {
            $this->social_lead_funnel_model->update_opportunity($id, $this->input->post(null, true));
            set_alert('success', _l('updated_successfully', _l('social_lead_funnel_opportunity')));
            redirect(admin_url('social_lead_funnel/view/' . $id));
        }
        $data['opportunity'] = $this->social_lead_funnel_model->get_opportunity($id);
        $data['title'] = _l('edit');
        $this->load->view('social_lead_funnel/form', $data);
    }

    public function delete($id)
    {
        if (!has_permission('social_lead_funnel', '', 'delete')) {
            access_denied('social_lead_funnel');
        }
        if ($this->input->post()) {
            $this->social_lead_funnel_model->delete_opportunity($id);
            set_alert('success', _l('deleted', _l('social_lead_funnel_opportunity')));
        }
        redirect(admin_url('social_lead_funnel'));
    }

    public function convert_to_lead($id)
    {
        if (!has_permission('social_lead_funnel', '', 'create')) {
            access_denied('social_lead_funnel');
        }
        if ($this->input->post()) {
            $lead_id = $this->social_lead_funnel_model->create_crm_lead($id);
            if ($lead_id) {
                set_alert('success', _l('social_lead_funnel_converted'));
            } else {
                set_alert('danger', _l('social_lead_funnel_convert_failed'));
            }
        }
        redirect(admin_url('social_lead_funnel/view/' . $id));
    }

    public function settings_save()
    {
        if (!has_permission('social_lead_funnel', '', 'settings')) {
            access_denied('social_lead_funnel');
        }
        if ($this->input->post()) {
            $fields = [
                'social_lead_funnel_enable_facebook',
                'social_lead_funnel_enable_nextdoor',
                'social_lead_funnel_enable_ai_drafts',
                'social_lead_funnel_auto_create_task',
                'social_lead_funnel_auto_notify_staff',
                'social_lead_funnel_keywords',
                'social_lead_funnel_default_staff_id',
                'social_lead_funnel_default_department_id',
                'social_lead_funnel_facebook_page_id',
                'social_lead_funnel_facebook_access_token',
                'social_lead_funnel_nextdoor_api_key',
                'social_lead_funnel_ai_provider',
                'social_lead_funnel_ai_api_key',
            ];
            foreach ($fields as $field) {
                update_option($field, $this->input->post($field, true) ?? '');
            }
            foreach (['social_lead_funnel_enable_facebook','social_lead_funnel_enable_nextdoor','social_lead_funnel_enable_ai_drafts','social_lead_funnel_auto_create_task','social_lead_funnel_auto_notify_staff'] as $checkbox) {
                update_option($checkbox, $this->input->post($checkbox) ? '1' : '0');
            }
            set_alert('success', _l('settings_updated'));
        }
        redirect(admin_url('settings?group=social_lead_funnel'));
    }

    public function templates()
    {
        if (!has_permission('social_lead_funnel', '', 'view')) {
            access_denied('social_lead_funnel');
        }
        $data['templates'] = $this->social_lead_funnel_model->get_templates();
        $data['title'] = _l('social_lead_funnel_templates');
        $this->load->view('social_lead_funnel/templates', $data);
    }

    public function template_save()
    {
        if (!has_permission('social_lead_funnel', '', 'edit')) {
            access_denied('social_lead_funnel');
        }
        if ($this->input->post()) {
            $this->social_lead_funnel_model->save_template($this->input->post(null, true));
            set_alert('success', _l('updated_successfully', _l('social_lead_funnel_template')));
        }
        redirect(admin_url('social_lead_funnel/templates'));
    }

    public function health()
    {
        if (!has_permission('social_lead_funnel', '', 'view')) {
            access_denied('social_lead_funnel');
        }
        $data['health'] = $this->social_lead_funnel_model->health();
        $data['title'] = _l('social_lead_funnel_health');
        $this->load->view('social_lead_funnel/health', $data);
    }

    public function help()
    {
        if (!has_permission('social_lead_funnel', '', 'view')) {
            access_denied('social_lead_funnel');
        }
        $data['title'] = _l('social_lead_funnel_help');
        $this->load->view('social_lead_funnel/help', $data);
    }

    public function webhook()
    {
        // Placeholder endpoint for future Meta/Nextdoor approved webhooks.
        $payload = file_get_contents('php://input');
        $this->social_lead_funnel_model->log('info', 'webhook_received', 'Webhook payload received', ['payload_length' => strlen((string)$payload)]);
        echo json_encode(['success' => true]);
    }
}

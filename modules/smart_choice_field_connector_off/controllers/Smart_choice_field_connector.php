<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_choice_field_connector extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smart_choice_field_connector/smart_choice_field_connector_model', 'scfc_model');
    }

    public function index()
    {
        if (!has_permission('smart_choice_field_connector', '', 'view') && !is_admin()) {
            access_denied('smart_choice_field_connector');
        }

        $data['title'] = _l('scfc_menu_name');
        $data['modules'] = $this->scfc_model->get_supported_modules();
        $data['mappings'] = $this->scfc_model->get_mappings();
        $data['tokens'] = $this->scfc_model->get_custom_tokens();
        $data['db_report'] = $this->scfc_model->database_report();
        $this->load->view('dashboard', $data);
    }

    public function save_mapping()
    {
        if (!has_permission('smart_choice_field_connector', '', 'edit') && !is_admin()) {
            access_denied('smart_choice_field_connector');
        }

        $data = $this->input->post();
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $success = $this->scfc_model->save_mapping($data, $id);
        set_alert($success ? 'success' : 'warning', $success ? _l('scfc_saved') : _l('scfc_save_failed'));
        redirect(admin_url('smart_choice_field_connector'));
    }

    public function save_token()
    {
        if (!has_permission('smart_choice_field_connector', '', 'edit') && !is_admin()) {
            access_denied('smart_choice_field_connector');
        }

        $data = $this->input->post();
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $success = $this->scfc_model->save_custom_token($data, $id);
        set_alert($success ? 'success' : 'warning', $success ? _l('scfc_saved') : _l('scfc_save_failed'));
        redirect(admin_url('smart_choice_field_connector#custom-tokens'));
    }

    public function delete_mapping($id)
    {
        if (!has_permission('smart_choice_field_connector', '', 'delete') && !is_admin()) {
            access_denied('smart_choice_field_connector');
        }
        $this->scfc_model->delete_mapping((int)$id);
        set_alert('success', _l('deleted', _l('scfc_mapping')));
        redirect(admin_url('smart_choice_field_connector'));
    }

    public function delete_token($id)
    {
        if (!has_permission('smart_choice_field_connector', '', 'delete') && !is_admin()) {
            access_denied('smart_choice_field_connector');
        }
        if (get_option('scfc_allow_delete_tokens') != '1') {
            set_alert('warning', _l('scfc_delete_disabled'));
            redirect(admin_url('smart_choice_field_connector#custom-tokens'));
        }
        $impact = $this->scfc_model->token_impact((int)$id);
        if (!empty($impact['total'])) {
            set_alert('warning', _l('scfc_token_has_impact'));
            redirect(admin_url('smart_choice_field_connector/impact/token/' . (int)$id));
        }
        $this->scfc_model->delete_custom_token((int)$id);
        set_alert('success', _l('deleted', _l('scfc_custom_token')));
        redirect(admin_url('smart_choice_field_connector#custom-tokens'));
    }

    public function impact($type = '', $id = 0)
    {
        if (!has_permission('smart_choice_field_connector', '', 'view') && !is_admin()) {
            access_denied('smart_choice_field_connector');
        }
        $data['title'] = _l('scfc_impact_report');
        $data['type'] = $type;
        $data['id'] = (int)$id;
        $data['report'] = ($type === 'token') ? $this->scfc_model->token_impact((int)$id) : $this->scfc_model->mapping_impact((int)$id);
        $this->load->view('impact', $data);
    }

    public function health()
    {
        if (!has_permission('smart_choice_field_connector', '', 'view') && !is_admin()) {
            access_denied('smart_choice_field_connector');
        }
        $data['title'] = _l('scfc_health_check');
        $data['report'] = $this->scfc_model->health_check();
        $this->load->view('health', $data);
    }

    public function refresh()
    {
        if (!has_permission('smart_choice_field_connector', '', 'view') && !is_admin()) {
            access_denied('smart_choice_field_connector');
        }
        set_alert('success', _l('scfc_refreshed'));
        redirect(admin_url('smart_choice_field_connector'));
    }

    public function fields_json($module = '')
    {
        if (!has_permission('smart_choice_field_connector', '', 'view') && !is_admin()) {
            ajax_access_denied();
        }
        echo json_encode($this->scfc_model->get_fields_for_module($module));
        die;
    }
}

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
        $this->requirePermission('view');

        $data['title']        = _l('scfc_menu_name');
        $data['modules']      = $this->scfc_model->get_supported_modules();
        $data['mappings']     = $this->scfc_model->get_mappings();
        $data['tokens']       = $this->scfc_model->get_custom_tokens();
        $data['groups']       = $this->scfc_model->get_groups();
        $data['combinations'] = $this->scfc_model->get_combinations();
        $data['db_report']    = $this->scfc_model->database_report();
        $this->load->view('dashboard', $data);
    }

    public function save_group()
    {
        $this->requirePermission('create');
        $data = $this->input->post();
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $success = $this->scfc_model->save_group($data, $id);
        set_alert($success ? 'success' : 'warning', $success ? _l('scfc_saved') : _l('scfc_save_failed'));
        redirect(admin_url('smart_choice_field_connector#field-groups'));
    }

    public function save_mapping()
    {
        $this->requirePermission('edit');
        $data = $this->input->post();
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $success = $this->scfc_model->save_mapping($data, $id);
        set_alert($success ? 'success' : 'warning', $success ? _l('scfc_saved') : _l('scfc_save_failed'));
        redirect(admin_url('smart_choice_field_connector#field-mappings'));
    }

    public function save_token()
    {
        $this->requirePermission('edit');
        $data = $this->input->post();
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $success = $this->scfc_model->save_custom_token($data, $id);
        set_alert($success ? 'success' : 'warning', $success ? _l('scfc_saved') : _l('scfc_save_failed'));
        redirect(admin_url('smart_choice_field_connector#custom-tokens'));
    }

    public function save_combination()
    {
        $this->requirePermission('edit');
        $data = $this->input->post();
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        $success = $this->scfc_model->save_combination($data, $id);
        set_alert($success ? 'success' : 'warning', $success ? _l('scfc_saved') : _l('scfc_save_failed'));
        redirect(admin_url('smart_choice_field_connector#field-combinations'));
    }

    public function delete_mapping($id)
    {
        $this->requirePermission('delete');
        $this->scfc_model->delete_mapping((int) $id);
        set_alert('success', _l('deleted', _l('scfc_mapping')));
        redirect(admin_url('smart_choice_field_connector#field-mappings'));
    }

    public function delete_group($id)
    {
        $this->requirePermission('delete');
        $this->scfc_model->delete_group((int) $id);
        set_alert('success', _l('deleted', _l('scfc_group')));
        redirect(admin_url('smart_choice_field_connector#field-groups'));
    }

    public function delete_combination($id)
    {
        $this->requirePermission('delete');
        $this->scfc_model->delete_combination((int) $id);
        set_alert('success', _l('deleted', _l('scfc_combination')));
        redirect(admin_url('smart_choice_field_connector#field-combinations'));
    }

    public function delete_token($id)
    {
        $this->requirePermission('delete');

        if (get_option('scfc_allow_delete_tokens') != '1') {
            set_alert('warning', _l('scfc_delete_disabled'));
            redirect(admin_url('smart_choice_field_connector#custom-tokens'));
        }

        $impact = $this->scfc_model->token_impact((int) $id);
        if (!empty($impact['total'])) {
            set_alert('warning', _l('scfc_token_has_impact'));
            redirect(admin_url('smart_choice_field_connector/impact/token/' . (int) $id));
        }

        $this->scfc_model->delete_custom_token((int) $id);
        set_alert('success', _l('deleted', _l('scfc_custom_token')));
        redirect(admin_url('smart_choice_field_connector#custom-tokens'));
    }

    public function impact($type = '', $id = 0)
    {
        $this->requirePermission('view');
        $data['title'] = _l('scfc_impact_report');
        $data['type'] = $type;
        $data['id'] = (int) $id;
        if ($type === 'token') {
            $data['report'] = $this->scfc_model->token_impact((int) $id);
        } elseif ($type === 'combination') {
            $data['report'] = $this->scfc_model->combination_impact((int) $id);
        } else {
            $data['report'] = $this->scfc_model->mapping_impact((int) $id);
        }
        $this->load->view('impact', $data);
    }

    public function health()
    {
        $this->requirePermission('view');
        $data['title'] = _l('scfc_health_check');
        $data['report'] = $this->scfc_model->health_check();
        $this->load->view('health', $data);
    }

    public function refresh()
    {
        $this->requirePermission('view');
        set_alert('success', _l('scfc_refreshed'));
        redirect(admin_url('smart_choice_field_connector'));
    }

    public function fields_json($module = '')
    {
        $this->requireAjaxPermission();
        header('Content-Type: application/json');
        echo json_encode($this->scfc_model->get_fields_for_module($module));
        exit;
    }

    public function test_json()
    {
        $this->requireAjaxPermission();
        $template = (string) $this->input->post('template');
        $result = $this->scfc_model->preview_template($template);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function test_combination_json($id = 0)
    {
        $this->requireAjaxPermission();
        $combo = $this->scfc_model->get_combination((int) $id);
        $template = $combo ? $combo['template'] : (string) $this->input->post('template');
        $result = $this->scfc_model->preview_template($template);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function repair_database()
    {
        $this->requirePermission('settings');
        require_once(module_dir_path('smart_choice_field_connector') . 'install.php');
        set_alert('success', _l('scfc_database_repaired'));
        redirect(admin_url('smart_choice_field_connector/health'));
    }

    private function requirePermission($permission)
    {
        if (!is_admin() && !has_permission('smart_choice_field_connector', '', $permission)) {
            access_denied('smart_choice_field_connector');
        }
    }

    private function requireAjaxPermission()
    {
        if (!is_admin() && !has_permission('smart_choice_field_connector', '', 'view')) {
            ajax_access_denied();
        }
    }
}

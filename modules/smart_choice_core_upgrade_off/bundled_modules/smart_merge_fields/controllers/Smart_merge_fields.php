<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_merge_fields extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smart_merge_fields/Smart_merge_fields_model', 'merge_model');
        $this->load->helper('smart_merge_fields/smart_merge_fields');
    }

    public function index()
    {
        $this->require_view();
        $data['title'] = 'Merge Fields Automation';
        $data['fields'] = $this->merge_model->get_fields($this->input->get('search', true) ?: '');
        $data['mappings'] = $this->merge_model->get_mappings();
        $data['tables'] = $this->merge_model->get_tables_summary();
        $data['logs'] = $this->merge_model->get_logs(20);
        $this->load->view('dashboard', $data);
    }

    public function scan()
    {
        $this->require_view();
        $count = $this->merge_model->scan_database();
        set_alert('success', 'Database scan completed successfully. ' . $count . ' fields were discovered.');
        redirect(admin_url('smart_merge_fields'));
    }

    public function mapping($id = '')
    {
        $this->require_view();
        if ($this->input->post()) {
            $this->require_edit();
            $savedId = $this->merge_model->save_mapping($this->input->post(null, true));
            set_alert('success', 'Merge field mapping saved successfully.');
            redirect(admin_url('smart_merge_fields/mapping/' . $savedId));
        }
        $data['title'] = $id ? 'Edit Mapping' : 'Create Mapping';
        $data['mapping'] = $id ? $this->merge_model->get_mapping($id) : [];
        $data['fields'] = $this->merge_model->get_fields('');
        $this->load->view('mapping', $data);
    }

    public function delete($id)
    {
        $this->require_delete();
        $this->merge_model->delete_mapping($id);
        set_alert('success', 'Merge field mapping deleted successfully.');
        redirect(admin_url('smart_merge_fields'));
    }

    public function sync($id)
    {
        $this->require_edit();
        $result = $this->merge_model->sync_mapping($id);
        set_alert($result['success'] ? 'success' : 'warning', $result['message'] . ' Records affected: ' . (int) $result['affected']);
        redirect(admin_url('smart_merge_fields'));
    }

    public function settings()
    {
        $this->require_view();
        if ($this->input->post()) {
            $this->require_edit();
            $this->merge_model->save_settings($this->input->post(null, true));
            set_alert('success', 'Settings saved successfully.');
            redirect(admin_url('smart_merge_fields/settings'));
        }
        $data['title'] = 'Merge Fields Settings';
        $data['settings'] = $this->merge_model->get_settings();
        $this->load->view('settings', $data);
    }

    public function health()
    {
        $this->require_view();
        $data['title'] = 'Merge Fields Health Check';
        $data['checks'] = $this->merge_model->health_check();
        $data['logs'] = $this->merge_model->get_logs(50);
        $this->load->view('health', $data);
    }

    public function help()
    {
        $this->require_view();
        $data['title'] = 'Merge Fields Help Guide';
        $this->load->view('help', $data);
    }

    public function lead_field()
    {
        $this->require_edit();
        if ($this->input->post()) {
            $name = $this->input->post('field_name', true);
            $type = $this->input->post('field_type', true) ?: 'input';
            $id = $this->merge_model->create_lead_custom_field($name, $type);
            if ($id) {
                set_alert('success', 'Lead custom field created successfully.');
            } else {
                set_alert('warning', 'Lead custom field was not created. Check database permissions.');
            }
        }
        redirect(admin_url('smart_merge_fields/settings'));
    }

    public function export()
    {
        $this->require_view();
        $csv = $this->merge_model->export_mappings_csv();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="smart_merge_fields_mappings.csv"');
        echo $csv;
        exit;
    }

    private function require_view()
    {
        if (!has_permission('smart_merge_fields', '', 'view')) {
            access_denied('Merge Fields Automation');
        }
    }

    private function require_edit()
    {
        if (!has_permission('smart_merge_fields', '', 'edit') && !has_permission('smart_merge_fields', '', 'create')) {
            access_denied('Merge Fields Automation');
        }
    }

    private function require_delete()
    {
        if (!has_permission('smart_merge_fields', '', 'delete')) {
            access_denied('Merge Fields Automation');
        }
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Superman extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('superman/superman_model');
    }

    public function index()
    {
        $this->guard('view');
        $data['title'] = _l('superman_menu_name');
        $data['modules'] = $this->superman_model->get_supported_modules();
        $data['mappings'] = $this->superman_model->get_mappings();
        $data['tokens'] = $this->superman_model->get_tokens();
        $data['merge_catalog'] = $this->superman_model->build_merge_catalog();
        $data['settings'] = $this->superman_model->get_settings_payload();
        $data['profiles'] = $this->superman_model->get_profiles('settings');
        $data['field_groups'] = $this->superman_model->get_visual_field_groups();
        $data['flow_sets'] = $this->superman_model->get_flow_sets();
        $this->load->view('dashboard', $data);
    }

    public function save_mapping()
    {
        $this->guard('edit');
        $data = $this->input->post();
        $success = $this->superman_model->save_mapping($data, isset($data['id']) ? (int)$data['id'] : 0);
        set_alert($success ? 'success' : 'warning', $success ? _l('superman_saved') : _l('superman_save_failed'));
        redirect(admin_url('superman'));
    }

    public function delete_mapping($id)
    {
        $this->guard('delete');
        $this->superman_model->delete_mapping((int)$id);
        set_alert('success', _l('superman_deleted'));
        redirect(admin_url('superman'));
    }

    public function save_token()
    {
        $this->guard('edit');
        $data = $this->input->post();
        $success = $this->superman_model->save_token($data, isset($data['id']) ? (int)$data['id'] : 0);
        set_alert($success ? 'success' : 'warning', $success ? _l('superman_saved') : _l('superman_save_failed'));
        redirect(admin_url('superman#tokens'));
    }

    public function delete_token($id)
    {
        $this->guard('delete');
        $this->superman_model->delete_token((int)$id);
        set_alert('success', _l('superman_deleted'));
        redirect(admin_url('superman#tokens'));
    }



    public function toggle_mapping($id)
    {
        $this->guard('edit');
        $this->superman_model->toggle_mapping((int)$id);
        set_alert('success', _l('superman_saved'));
        redirect(admin_url('superman'));
    }

    public function bulk_delete_mappings()
    {
        $this->guard('delete');
        $ids = $this->input->post('ids');
        $this->superman_model->bulk_delete_mappings(is_array($ids) ? $ids : []);
        set_alert('success', _l('superman_deleted'));
        redirect(admin_url('superman'));
    }

    public function export_mappings()
    {
        $this->guard('view');
        $rows = $this->superman_model->get_mappings(false);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="superman-mappings.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['source_module','source_field','destination_module','destination_field','merge_tag','map_group','priority','overwrite_existing','is_active','notes']);
        foreach ($rows as $row) {
            fputcsv($out, [$row['source_module'],$row['source_field'],$row['destination_module'],$row['destination_field'],$row['merge_tag'],$row['map_group'],$row['priority'],$row['overwrite_existing'],$row['is_active'],$row['notes']]);
        }
        fclose($out);
        die;
    }

    public function sample_header()
    {
        $this->guard('view');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="superman-mapping-sample-header.csv"');
        echo "source_module,source_field,destination_module,destination_field,merge_tag,map_group,priority,overwrite_existing,is_active,notes\n";
        echo "lead,email,contact,email,{lead_email},Lead To Contact,10,0,1,Sample row\n";
        die;
    }

    public function import_mappings()
    {
        $this->guard('create');
        if (!empty($_FILES['mapping_file']['tmp_name'])) {
            $count = $this->superman_model->import_mappings_csv($_FILES['mapping_file']['tmp_name']);
            set_alert('success', $count . ' ' . _l('superman_imported_rows'));
        } else {
            set_alert('warning', _l('superman_save_failed'));
        }
        redirect(admin_url('superman'));
    }

    public function save_visual_flow()
    {
        $this->guard('edit');
        $payload = $this->input->post('flow_payload');
        $name = trim((string)$this->input->post('flow_name'));
        $success = $this->superman_model->save_visual_flow($name ?: 'Custom Visual Flow ' . date('Y-m-d H:i'), $payload);
        set_alert($success ? 'success' : 'warning', $success ? _l('superman_saved') : _l('superman_save_failed'));
        redirect(admin_url('superman'));
    }

    public function settings_save()
    {
        $this->guard('edit');
        $this->superman_model->save_settings($this->input->post());
        set_alert('success', _l('superman_settings_saved'));
        redirect(admin_url('superman/settings'));
    }

    public function save_profile()
    {
        $this->guard('edit');
        $name = trim((string)$this->input->post('profile_name'));
        if ($name === '') {
            $name = 'Manual Backup ' . date('Y-m-d H:i:s');
        }
        $this->superman_model->save_profile($name, 'settings', $this->superman_model->get_settings_payload(), false);
        set_alert('success', _l('superman_profile_saved'));
        redirect(admin_url('superman/settings'));
    }

    public function restore_profile($id)
    {
        $this->guard('edit');
        $success = $this->superman_model->restore_profile((int)$id);
        set_alert($success ? 'success' : 'warning', $success ? _l('superman_profile_restored') : _l('superman_save_failed'));
        redirect(admin_url('superman/settings'));
    }


    public function settings()
    {
        $this->guard('view');
        $data['title'] = _l('superman_settings_title');
        $data['settings'] = $this->superman_model->get_settings_payload();
        $data['profiles'] = $this->superman_model->get_profiles('settings');
        $this->load->view('settings/index', $data);
    }

    public function health()
    {
        $this->guard('view');
        $data['title'] = _l('superman_health_check');
        $data['report'] = $this->superman_model->health_check();
        $this->load->view('health', $data);
    }

    public function merge_fields()
    {
        $this->guard('view');
        $data['title'] = _l('superman_merge_catalog');
        $data['merge_catalog'] = $this->superman_model->build_merge_catalog();
        $data['find_mode'] = $this->input->get('find') ? true : false;
        $this->load->view('merge_fields', $data);
    }

    public function preview_flow()
    {
        $this->guard('view');
        $payload = $this->input->post('payload');
        $decoded = json_decode((string) $payload, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'title' => _l('superman_preview_combination'),
            'html' => $this->superman_model->render_flow_preview($decoded),
        ]);
        die;
    }

    public function fields_json($module = '')
    {
        if (!has_permission('superman', '', 'view') && !is_admin()) {
            ajax_access_denied();
        }
        echo json_encode($this->superman_model->get_fields_for_module($module));
        die;
    }

    public function client_bundle($clientId = 0)
    {
        if (!has_permission('superman', '', 'view') && !is_admin()) {
            ajax_access_denied();
        }
        header('Content-Type: application/json');
        echo json_encode($this->superman_model->get_client_bundle((int)$clientId));
        die;
    }

    private function guard($capability)
    {
        if ($capability === 'view') {
            if (!has_permission('superman', '', 'view') && !has_permission('superman', '', 'view_global') && !is_admin()) {
                access_denied('superman');
            }
            return;
        }

        if (!has_permission('superman', '', $capability) && !is_admin()) {
            access_denied('superman');
        }
    }
}

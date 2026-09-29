<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Crm_file_explorer_manager extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('crm_file_explorer_manager/Crm_file_explorer_manager_model', 'file_explorer_model');
        if (!has_permission(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, '', 'view') && !has_permission(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, '', 'view_own') && !is_admin()) {
            access_denied(_l('crm_file_explorer_manager'));
        }
    }

    public function index()
    {
        $data['title'] = _l('crm_file_explorer_manager');
        $data['summary'] = $this->file_explorer_model->get_summary();
        $data['files'] = $this->file_explorer_model->get_files($this->input->get('type'), $this->input->get('search'));
        $this->load->view('dashboard/index', $data);
    }

    public function media()
    {
        $data['title'] = _l('media_viewer');
        $type = $this->input->get('type') ?: 'image';
        $data['type'] = $type;
        $data['files'] = $this->file_explorer_model->get_files($type, $this->input->get('search'), 500);
        $this->load->view('dashboard/media', $data);
    }

    public function scan()
    {
        if (!has_permission(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, '', 'create') && !is_admin()) {
            access_denied(_l('scan_files'));
        }
        $path = $this->input->post('base_path') ?: get_option('crm_file_explorer_manager_root_path');
        $result = $this->file_explorer_model->run_scan($path, 'manual');
        if ($result['success']) {
            set_alert('success', _l('scan_completed') . ': ' . $result['total_files'] . ' ' . _l('files'));
        } else {
            set_alert('danger', $result['message']);
        }
        $redirectTo = $this->input->post('redirect_to');
        if ($redirectTo === 'media') {
            redirect(admin_url('crm_file_explorer_manager/media'));
        }
        redirect(admin_url('crm_file_explorer_manager'));
    }

    public function properties($id)
    {
        $file = $this->file_explorer_model->get_file($id);
        if (!$file) {
            show_404();
        }
        $data['file'] = $file;
        $data['associations'] = $this->file_explorer_model->find_associations($file);
        $this->load->view('dashboard/properties', $data);
    }

    public function preview($id)
    {
        $file = $this->file_explorer_model->get_file($id);
        if (!$file) {
            show_404();
        }
        $path = realpath(FCPATH . $file['relative_path']);
        if (!$path || !is_file($path) || strpos($path, realpath(FCPATH)) !== 0) {
            show_404();
        }
        $mime = $file['mime_type'] ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }


    public function settings()
    {
        if (!is_admin()) {
            access_denied(_l('settings'));
        }
        $data['title'] = _l('crm_file_explorer_manager_settings');
        $this->load->view('settings/settings_full', $data);
    }

    public function reset_scan()
    {
        if (!has_permission(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, '', 'delete') && !is_admin()) {
            access_denied(_l('reset_scan_list'));
        }
        $this->file_explorer_model->reset_scan_index();
        set_alert('success', _l('scan_list_reset_successfully'));
        redirect(admin_url('crm_file_explorer_manager'));
    }

    public function health()
    {
        $data['title'] = _l('health_checker');
        $data['checks'] = $this->file_explorer_model->health_check();
        $this->load->view('settings/health', $data);
    }

    public function help()
    {
        $data['title'] = _l('help_guide');
        $this->load->view('settings/help', $data);
    }

    public function settings_save()
    {
        if (!is_admin()) {
            access_denied(_l('settings'));
        }
        $root = $this->input->post('root_path');
        $max = (int)$this->input->post('max_scan_files');
        update_option('crm_file_explorer_manager_root_path', $root ?: FCPATH);
        update_option('crm_file_explorer_manager_max_scan_files', $max > 0 ? (string)$max : '5000');
        update_option('crm_file_explorer_manager_allow_backup_zip', $this->input->post('allow_backup_zip') ? '1' : '0');
        update_option('crm_file_explorer_manager_public_preview', $this->input->post('public_preview') ? '1' : '0');
        set_alert('success', _l('settings_updated'));
        $redirect = $this->input->post('redirect_back') ?: admin_url('settings?group=crm_file_explorer_manager_settings');
        redirect($redirect);
    }

    public function backups()
    {
        $data['title'] = _l('safe_backups');
        $data['backups'] = $this->file_explorer_model->get_backups();
        $this->load->view('dashboard/backups', $data);
    }

    public function create_backup()
    {
        if (!has_permission(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, '', 'create') && !is_admin()) {
            access_denied(_l('safe_backups'));
        }
        $type = $this->input->post('backup_type') ?: 'module_files';
        $result = $this->file_explorer_model->create_backup_zip($type);
        set_alert($result['success'] ? 'success' : 'danger', $result['message'] ?? (_l('backup_created') . ': ' . ($result['file'] ?? '')));
        redirect(admin_url('crm_file_explorer_manager/backups'));
    }

    public function delete_backup($id)
    {
        if (!has_permission(CRM_FILE_EXPLORER_MANAGER_MODULE_NAME, '', 'delete') && !is_admin()) {
            access_denied(_l('delete_backup'));
        }

        if (!$this->input->is_ajax_request() && strtolower($this->input->method()) !== 'post') {
            show_error(_l('invalid_request'), 405);
        }

        $result = $this->file_explorer_model->delete_backup((int) $id);
        set_alert($result['success'] ? 'success' : 'danger', $result['message']);
        redirect(admin_url('crm_file_explorer_manager/backups'));
    }

    public function download_backup($id)
    {
        $row = $this->db->where('id', (int)$id)->get(db_prefix() . 'crm_file_explorer_backups')->row_array();
        if (!$row) {
            show_404();
        }
        $path = realpath(FCPATH . $row['relative_path']);
        if (!$path || !is_file($path)) {
            show_404();
        }
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}

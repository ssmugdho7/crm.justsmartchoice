<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_module_manager extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('smart_module_manager/smart_module_manager');
        $this->load->model('smart_module_manager/smart_module_manager_model');
        $this->load->library('smart_module_manager/Smart_module_packager');
    }

    public function index()
    {
        if (!has_permission(SMART_MODULE_MANAGER_MODULE_NAME, '', 'view') && !is_admin()) {
            access_denied(SMART_MODULE_MANAGER_MODULE_NAME);
        }

        $data['title'] = _l('smart_module_manager');
        $data['modules'] = $this->smart_module_manager_model->get_modules();
        $this->load->view('admin/dashboard', $data);
    }

    public function settings()
    {
        if (!has_permission(SMART_MODULE_MANAGER_MODULE_NAME, '', 'settings') && !is_admin()) {
            access_denied(SMART_MODULE_MANAGER_MODULE_NAME);
        }

        if ($this->input->post()) {
            update_option('smart_module_manager_enabled', $this->input->post('smart_module_manager_enabled') ? '1' : '0');
            update_option('smart_module_manager_allow_unload', $this->input->post('smart_module_manager_allow_unload') ? '1' : '0');
            update_option('smart_module_manager_backup_before_unload', $this->input->post('smart_module_manager_backup_before_unload') ? '1' : '0');
            update_option('smart_module_manager_allow_database_cleanup', $this->input->post('smart_module_manager_allow_database_cleanup') ? '1' : '0');
            update_option('smart_module_manager_export_path', trim((string) $this->input->post('smart_module_manager_export_path')) ?: 'uploads/smart_module_manager/exports');
            set_alert('success', _l('updated_successfully', _l('smart_module_manager_settings')));
            redirect(admin_url('smart_module_manager/settings'));
        }

        $data['title'] = _l('smart_module_manager_settings');
        $this->load->view('admin/settings', $data);
    }

    public function health()
    {
        if (!has_permission(SMART_MODULE_MANAGER_MODULE_NAME, '', 'health_check') && !is_admin()) {
            access_denied(SMART_MODULE_MANAGER_MODULE_NAME);
        }

        $data['title'] = _l('smart_module_manager_health_check');
        $data['modules'] = $this->smart_module_manager_model->get_modules();
        $this->load->view('admin/health', $data);
    }

    public function repair_database()
    {
        if (!is_admin()) {
            access_denied(SMART_MODULE_MANAGER_MODULE_NAME);
        }

        require_once module_dir_path(SMART_MODULE_MANAGER_MODULE_NAME) . 'install.php';
        set_alert('success', _l('smart_module_manager_repair_database'));
        redirect(admin_url('smart_module_manager/health'));
    }

    public function download($moduleName = '')
    {
        if (!has_permission(SMART_MODULE_MANAGER_MODULE_NAME, '', 'download') && !is_admin()) {
            access_denied(SMART_MODULE_MANAGER_MODULE_NAME);
        }

        try {
            $exportPath = FCPATH . get_option('smart_module_manager_export_path');
            $zipPath = $this->smart_module_packager->package($moduleName, $exportPath);
            smart_module_manager_log($moduleName, 'download', 'Module package created.', $zipPath);

            if (!file_exists($zipPath)) {
                throw new Exception(_l('smart_module_manager_error_zip'));
            }

            $this->load->helper('download');
            force_download(basename($zipPath), file_get_contents($zipPath));
        } catch (Exception $e) {
            set_alert('danger', $e->getMessage());
            redirect(admin_url('smart_module_manager'));
        }
    }

    public function unload($moduleName = '')
    {
        if (!has_permission(SMART_MODULE_MANAGER_MODULE_NAME, '', 'unload') && !is_admin()) {
            access_denied(SMART_MODULE_MANAGER_MODULE_NAME);
        }

        if (get_option('smart_module_manager_allow_unload') !== '1') {
            set_alert('danger', _l('smart_module_manager_error_permission'));
            redirect(admin_url('smart_module_manager'));
        }

        if (!smart_module_manager_is_safe_slug($moduleName) || $moduleName === SMART_MODULE_MANAGER_MODULE_NAME) {
            set_alert('danger', _l('smart_module_manager_error_invalid_module'));
            redirect(admin_url('smart_module_manager'));
        }

        $modulePath = FCPATH . 'modules/' . $moduleName;
        if (!is_dir($modulePath)) {
            set_alert('danger', _l('smart_module_manager_error_missing_module'));
            redirect(admin_url('smart_module_manager'));
        }

        if ($this->input->post('confirm') !== 'yes') {
            $data['title'] = _l('smart_module_manager_complete_unload');
            $data['module_name'] = $moduleName;
            $this->load->view('admin/confirm_unload', $data);
            return;
        }

        if (get_option('smart_module_manager_backup_before_unload') === '1') {
            try {
                $exportPath = FCPATH . get_option('smart_module_manager_export_path');
                $zipPath = $this->smart_module_packager->package($moduleName, $exportPath);
                smart_module_manager_log($moduleName, 'backup_before_unload', 'Backup created before unload.', $zipPath);
            } catch (Exception $e) {
                set_alert('danger', $e->getMessage());
                redirect(admin_url('smart_module_manager'));
            }
        }

        if (get_option('smart_module_manager_allow_database_cleanup') === '1') {
            $this->smart_module_manager_model->cleanup_module_records($moduleName);
        }

        if (function_exists('uninstall_module')) {
            @uninstall_module($moduleName);
        }

        smart_module_manager_delete_dir_safe($modulePath);
        smart_module_manager_log($moduleName, 'unload', 'Module folder removed.');
        set_alert('success', _l('smart_module_manager_success_unload'));
        redirect(admin_url('smart_module_manager'));
    }
}

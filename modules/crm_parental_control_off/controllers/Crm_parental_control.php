<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Crm_parental_control extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('crm_parental_control/Crm_parental_control_model', 'crm_pc');
        if (!is_admin() && !has_permission('crm_parental_control', '', 'view')) {
            access_denied('CRM Parental Control');
        }
    }

    public function index()
    {
        $data['title'] = (_l('crm_parental_control') === 'crm_parental_control') ? 'CRM Parental Control' : _l('crm_parental_control');
        $data['health'] = $this->crm_pc->health();
        $data['preview'] = $this->crm_pc->preview_company_standard();
        $data['departments'] = $this->crm_pc->standard_departments();
        $data['roles'] = $this->crm_pc->role_templates();
        $data['staff'] = $this->crm_pc->current_staff();
        $this->load->view('crm_parental_control/dashboard', $data);
    }

    public function backup_database()
    {
        if (!is_admin() && !has_permission('crm_parental_control', '', 'backup')) { access_denied('Backup'); }
        $result = $this->crm_pc->create_database_backup();
        set_alert($result['status'] === 'created' ? 'success' : 'warning', $result['status'] === 'created' ? 'Database backup created.' : 'Database backup failed.');
        redirect(admin_url('crm_parental_control'));
    }

    public function backup_crm()
    {
        if (!is_admin() && !has_permission('crm_parental_control', '', 'backup')) { access_denied('Backup'); }
        $result = $this->crm_pc->create_crm_backup();
        set_alert(in_array($result['status'], ['created','partial'], true) ? 'success' : 'warning', $result['message'] ?? 'CRM safe backup completed.');
        redirect(admin_url('crm_parental_control'));
    }


    public function download_module()
    {
        if (!is_admin() && !has_permission('crm_parental_control', '', 'backup')) { access_denied('Download Module'); }
        $result = $this->crm_pc->create_module_zip();
        if (($result['status'] ?? '') !== 'created' || empty($result['path']) || !file_exists($result['path'])) {
            set_alert('warning', $result['message'] ?? 'Module ZIP could not be created.');
            redirect(admin_url('crm_parental_control'));
        }
        $this->load->helper('download');
        force_download($result['path'], null);
    }


    public function repair_database()
    {
        if (!is_admin() && !has_permission('crm_parental_control', '', 'settings')) { access_denied('Repair Database'); }
        require module_dir_path('crm_parental_control', 'install.php');
        set_alert('success', 'CRM Parental Control database tables and defaults were repaired.');
        redirect(admin_url('crm_parental_control'));
    }

    public function preview()
    {
        if (!is_admin() && !has_permission('crm_parental_control', '', 'view')) { access_denied('Preview'); }
        $data = $this->crm_pc->preview_company_standard();
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    public function apply()
    {
        if (!is_admin() && !has_permission('crm_parental_control', '', 'settings')) { access_denied('Apply'); }
        if (!$this->input->post('confirm_apply')) {
            set_alert('warning', 'Confirmation missing. No changes were made.');
            redirect(admin_url('crm_parental_control'));
        }
        $summary = $this->crm_pc->apply_company_standard();
        set_alert('success', 'Company standard applied. Review the audit summary and test users now.');
        redirect(admin_url('crm_parental_control'));
    }

    public function rollback()
    {
        if (!is_admin() && !has_permission('crm_parental_control', '', 'rollback')) { access_denied('Rollback'); }
        if (!$this->input->post('confirm_rollback')) {
            set_alert('warning', 'Rollback confirmation missing. No changes were made.');
            redirect(admin_url('crm_parental_control'));
        }
        $result = $this->crm_pc->rollback_last_apply();
        set_alert($result['status'] === 'completed' ? 'success' : 'warning', $result['message']);
        redirect(admin_url('crm_parental_control'));
    }
}

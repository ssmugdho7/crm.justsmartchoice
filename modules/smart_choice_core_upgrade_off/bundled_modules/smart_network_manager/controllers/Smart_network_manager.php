<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_network_manager extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smart_network_manager/Smart_network_manager_model', 'snm');
        $this->load->library('smart_network_manager/Snm_agent');
    }

    private function require_view()
    {
        if (!has_permission('smart_network_manager', '', 'view') && !is_admin()) { access_denied('smart_network_manager'); }
    }

    private function require_edit()
    {
        if (!has_permission('smart_network_manager', '', 'edit') && !is_admin()) { access_denied('smart_network_manager'); }
    }

    private function require_post()
    {
        if (strtoupper($this->input->method()) !== 'POST') { show_error('Invalid request method.'); }
    }

    public function index()
    {
        $this->require_view();
        $data['title'] = _l('smart_network_manager');
        $data['devices'] = $this->snm->devices();
        $data['logs'] = $this->snm->logs(20);
        $data['schedules'] = $this->snm->schedules();
        $data['health'] = $this->snm->health_report();
        $this->load->view('dashboard', $data);
    }

    public function health()
    {
        $this->require_view();
        $data['title'] = 'Smart Network Manager Health Checker';
        $data['health'] = $this->snm->health_report();
        $data['database'] = $this->snm->database_report();
        $data['errors'] = $this->snm->recent_error_report(40);
        $this->load->view('health', $data);
    }

    public function help()
    {
        $this->require_view();
        $data['title'] = 'Smart Network Manager Help Guide';
        $this->load->view('help', $data);
    }

    public function scan()
    {
        $this->require_edit();
        $this->require_post();
        $result = $this->snm_agent->request('scan', []);
        if (!empty($result['devices']) && is_array($result['devices'])) {
            foreach ($result['devices'] as $device) { $this->snm->upsert_device($device); }
            $this->snm->log('info', 'scan', 'Network scan completed.', ['count' => count($result['devices'])]);
            set_alert('success', 'Network scan completed.');
        } else {
            $this->snm->log('warning', 'scan', 'Network scan failed.', $result);
            set_alert('warning', $result['message'] ?? 'Network scan did not return devices.');
        }
        redirect(admin_url('smart_network_manager'));
    }

    public function clear_cache()
    {
        if (!is_admin()) { access_denied('smart_network_manager'); }
        $this->require_post();
        if (get_option('smart_network_manager_allow_cache_cleanup') !== '1') {
            set_alert('warning', 'Cache cleanup is disabled in settings.');
            redirect(admin_url('smart_network_manager'));
        }
        $path = APPPATH . 'cache/';
        $deleted = 0;
        foreach (glob($path . '*') as $file) {
            if (is_file($file) && basename($file) !== 'index.html') { @unlink($file); $deleted++; }
        }
        $this->snm->log('info', 'cache', 'CRM cache cleaned safely.', ['deleted' => $deleted]);
        set_alert('success', 'Cache cleaned safely. index.html was preserved. Deleted files: ' . $deleted);
        redirect(admin_url('smart_network_manager'));
    }

    public function toggle_client_portal($status)
    {
        if (!is_admin()) { access_denied('smart_network_manager'); }
        $this->require_post();
        $value = $status === 'enable' ? 1 : 0;
        update_option('allow_registration', $value);
        $this->snm->log('info', 'client_portal', 'Client portal registration toggled.', ['status' => $status]);
        set_alert('success', 'Client portal registration set to: ' . $status);
        redirect(admin_url('smart_network_manager'));
    }

    public function device_action($id, $action)
    {
        $this->require_edit();
        $this->require_post();
        if (get_option('smart_network_manager_enable_controls') !== '1') {
            set_alert('warning', 'Controls are disabled in settings. This module is in read-only mode.');
            redirect(admin_url('smart_network_manager'));
        }
        $allowed = ['allow','block','wake'];
        if (!in_array($action, $allowed, true)) { show_error('Invalid device action.'); }
        $device = $this->db->where('id', (int)$id)->get(db_prefix() . 'snm_devices')->row_array();
        if (!$device) { set_alert('danger', 'Device not found.'); redirect(admin_url('smart_network_manager')); }
        $result = $this->snm_agent->request('device-action', ['device' => $device, 'action' => $action]);
        if ($action === 'block' || $action === 'allow') {
            $this->db->where('id', (int)$id)->update(db_prefix() . 'snm_devices', ['is_blocked' => $action === 'block' ? 1 : 0]);
        }
        $this->snm->log(!empty($result['success']) ? 'info' : 'warning', 'device_action', 'Device action requested.', ['device' => $device, 'action' => $action, 'result' => $result]);
        set_alert(!empty($result['success']) ? 'success' : 'warning', $result['message'] ?? 'Action sent to agent.');
        redirect(admin_url('smart_network_manager'));
    }

    public function db_tool($action)
    {
        if (!is_admin()) { access_denied('smart_network_manager'); }
        $this->require_post();
        if (get_option('smart_network_manager_allow_database_tools') !== '1') {
            set_alert('warning', 'Database tools are disabled in settings.');
            redirect(admin_url('smart_network_manager/health'));
        }
        $allowed = ['check','analyze','optimize','repair'];
        if (!in_array($action, $allowed, true)) { show_error('Invalid database tool.'); }
        $tables = [db_prefix().'snm_devices', db_prefix().'snm_schedules', db_prefix().'snm_logs'];
        foreach ($tables as $table) {
            if ($this->db->table_exists($table)) { $this->db->query(strtoupper($action) . ' TABLE `' . $table . '`'); }
        }
        $this->snm->log('info', 'database_tool', 'Safe database tool executed.', ['action' => $action, 'tables' => $tables]);
        set_alert('success', ucfirst($action) . ' completed for Smart Network Manager tables.');
        redirect(admin_url('smart_network_manager/health'));
    }

    public function save_settings()
    {
        if (!is_admin()) { access_denied('smart_network_manager'); }
        $this->require_post();
        update_option('smart_network_manager_agent_url', trim((string)$this->input->post('agent_url')));
        update_option('smart_network_manager_agent_token', trim((string)$this->input->post('agent_token')));
        update_option('smart_network_manager_agent_ip_whitelist', trim((string)$this->input->post('agent_ip_whitelist')));
        update_option('smart_network_manager_router_type', trim((string)$this->input->post('router_type')));
        update_option('smart_network_manager_router_url', trim((string)$this->input->post('router_url')));
        update_option('smart_network_manager_phpmyadmin_url', trim((string)$this->input->post('phpmyadmin_url')));
        update_option('smart_network_manager_enable_controls', $this->input->post('enable_controls') ? '1' : '0');
        update_option('smart_network_manager_allow_cache_cleanup', $this->input->post('allow_cache_cleanup') ? '1' : '0');
        update_option('smart_network_manager_allow_database_tools', $this->input->post('allow_database_tools') ? '1' : '0');
        update_option('smart_network_manager_theme_color', trim((string)$this->input->post('theme_color')) ?: '#169179');
        $this->snm->log('info', 'settings', 'Smart Network Manager settings saved.');
        set_alert('success', 'Settings saved.');
        redirect(admin_url('settings?group=smart_network_manager'));
    }
}

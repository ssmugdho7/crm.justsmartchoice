<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_installer_tracking extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('smart_installer_tracking/smart_installer_tracking_model');
        $this->load->helper('smart_installer_tracking/smart_installer_tracking');
    }

    public function index(): void
    {
        $this->require_view();
        $data['title'] = smart_installer_tracking_title(_l('smart_installer_tracking_dashboard'));
        $data['stats'] = $this->smart_installer_tracking_model->get_dashboard_stats();
        $data['trips'] = $this->smart_installer_tracking_model->get_trips([], 25);
        $this->load->view('index', $data);
    }

    public function live_map(): void
    {
        $this->require_view();
        $data['title'] = smart_installer_tracking_title(_l('smart_installer_tracking_live_map'));
        $data['trips'] = $this->smart_installer_tracking_model->get_latest_locations();
        $data['google_maps_api_key'] = smart_installer_tracking_google_key();
        $this->load->view('live_map', $data);
    }

    public function trips(): void
    {
        $this->require_view();
        $data['title'] = smart_installer_tracking_title(_l('smart_installer_tracking_trips'));
        $data['trips'] = $this->smart_installer_tracking_model->get_trips([], 100);
        $this->load->view('trips', $data);
    }

    public function create(): void
    {
        if (!has_permission('smart_installer_tracking', '', 'create')) {
            access_denied('smart_installer_tracking');
        }
        if ($this->input->post()) {
            $id = $this->smart_installer_tracking_model->create_trip($this->input->post(null, true));
            set_alert('success', _l('smart_installer_tracking_trip_created_successfully'));
            redirect(admin_url('smart_installer_tracking/view/' . $id));
        }
        $data['title'] = smart_installer_tracking_title(_l('smart_installer_tracking_new_trip'));
        $data['staff'] = $this->smart_installer_tracking_model->get_staff_options();
        $data['appointments'] = $this->smart_installer_tracking_model->get_appointment_options();
        $data['projects'] = $this->smart_installer_tracking_model->get_project_options();
        $data['clients'] = $this->smart_installer_tracking_model->get_client_options();
        $data['google_maps_api_key'] = smart_installer_tracking_google_key();
        $this->load->view('trip_form', $data);
    }

    public function view($id = ''): void
    {
        $this->require_view();
        $trip_id = (int) $id;
        $data['trip'] = $this->smart_installer_tracking_model->get_trip($trip_id);
        if (!$data['trip']) {
            show_404();
        }
        $data['title'] = smart_installer_tracking_title(_l('smart_installer_tracking_trip_details'));
        $data['public_url'] = smart_installer_tracking_public_url((string) $data['trip']['tracking_token']);
        $data['google_maps_api_key'] = smart_installer_tracking_google_key();
        $this->load->view('trip_view', $data);
    }

    public function update_status($id = '', $status = ''): void
    {
        if (!has_permission('smart_installer_tracking', '', 'edit')) {
            access_denied('smart_installer_tracking');
        }
        if (!$this->input->post()) {
            show_404();
        }
        $ok = $this->smart_installer_tracking_model->update_trip_status((int) $id, (string) $status);
        set_alert($ok ? 'success' : 'danger', $ok ? _l('smart_installer_tracking_status_updated') : _l('smart_installer_tracking_status_not_updated'));
        redirect(admin_url('smart_installer_tracking/view/' . (int) $id));
    }

    public function save_location(): void
    {
        if (!$this->input->post()) {
            show_404();
        }
        $trip_id = (int) $this->input->post('trip_id', true);
        $staff_id = get_staff_user_id();
        $ok = $this->smart_installer_tracking_model->save_location($trip_id, $staff_id, $this->input->post(null, true));
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
    }

    public function geocode_address(): void
    {
        if (!has_permission('smart_installer_tracking', '', 'create') && !has_permission('smart_installer_tracking', '', 'edit')) {
            access_denied('smart_installer_tracking');
        }
        if (!$this->input->post()) {
            show_404();
        }
        $address = trim((string) $this->input->post('address', true));
        $result = $this->smart_installer_tracking_model->geocode_address($address);
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    public function reports(): void
    {
        if (!has_permission('smart_installer_tracking', '', 'reports')) {
            access_denied('smart_installer_tracking');
        }
        $filters = [
            'from' => $this->input->get('from', true) ?: date('Y-m-01'),
            'to' => $this->input->get('to', true) ?: date('Y-m-d'),
            'staff_id' => $this->input->get('staff_id', true),
        ];
        $data['title'] = smart_installer_tracking_title(_l('smart_installer_tracking_reports'));
        $data['filters'] = $filters;
        $data['staff'] = $this->smart_installer_tracking_model->get_staff_options();
        $data['reports'] = $this->smart_installer_tracking_model->get_reports($filters);
        $this->load->view('reports', $data);
    }

    public function health(): void
    {
        $this->require_view();
        $data['title'] = smart_installer_tracking_title(_l('smart_installer_tracking_health_checker'));
        $data['checks'] = $this->smart_installer_tracking_model->health_check();
        $data['logs'] = $this->smart_installer_tracking_model->get_logs(50);
        $this->load->view('health', $data);
    }

    public function repair(): void
    {
        if (!has_permission('smart_installer_tracking', '', 'settings')) {
            access_denied('smart_installer_tracking');
        }
        if (!$this->input->post()) {
            show_404();
        }
        $this->smart_installer_tracking_model->repair_schema();
        set_alert('success', _l('smart_installer_tracking_repair_completed'));
        redirect(admin_url('smart_installer_tracking/health'));
    }

    public function clear_safe_cache(): void
    {
        if (!has_permission('smart_installer_tracking', '', 'settings')) {
            access_denied('smart_installer_tracking');
        }
        if (!$this->input->post()) {
            show_404();
        }
        $cachePath = APPPATH . 'cache/';
        if (is_dir($cachePath)) {
            foreach (glob($cachePath . '*') ?: [] as $file) {
                if (is_file($file) && basename($file) !== 'index.html') {
                    @unlink($file);
                }
            }
        }
        $this->smart_installer_tracking_model->log_action(get_staff_user_id(), 'Safe Cache Cleanup', 'Application cache files were cleaned without deleting index.html.');
        set_alert('success', _l('smart_installer_tracking_cache_cleaned'));
        redirect(admin_url('smart_installer_tracking/health'));
    }

    public function help(): void
    {
        $this->require_view();
        $data['title'] = smart_installer_tracking_title(_l('smart_installer_tracking_help_guide'));
        $this->load->view('help', $data);
    }

    private function require_view(): void
    {
        if (!has_permission('smart_installer_tracking', '', 'view') && !has_permission('smart_installer_tracking', '', 'view_own')) {
            access_denied('smart_installer_tracking');
        }
    }
}

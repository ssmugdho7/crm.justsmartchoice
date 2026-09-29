<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Employees_tracker extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('employees_tracker/employees_tracker_model');
    }

    public function index()
    {
        if (!has_permission('employees_tracker', '', 'view') && !has_permission('employees_tracker', '', 'view_own')) {
            access_denied('employees_tracker');
        }

        $data['title']    = _l('employees_tracker_dashboard');
        $data['poll']     = (int) get_option('employees_tracker_poll_interval') ?: 30;
        $data['staff']    = $this->employees_tracker_model->get_trackable_staff();
        $data['projects'] = $this->employees_tracker_model->get_trackable_projects();
        $data['api_key']  = $this->employees_tracker_model->get_google_api_key();
        $this->load->view('employees_tracker/admin/dashboard', $data);
    }

    public function assignments()
    {
        if (!has_permission('employees_tracker', '', 'view') && !has_permission('employees_tracker', '', 'view_own')) {
            access_denied('employees_tracker');
        }

        if ($this->input->post()) {
            if (!has_permission('employees_tracker', '', 'manage') && !has_permission('employees_tracker', '', 'edit')) {
                access_denied('employees_tracker');
            }

            $payload = [
                'project_id'      => $this->input->post('project_id'),
                'staff_id'        => $this->input->post('staff_id'),
                'appointment_id'  => $this->input->post('appointment_id'),
                'service_type'    => $this->input->post('service_type'),
                'job_notes'       => $this->input->post('job_notes'),
                'scheduled_start' => $this->input->post('scheduled_start'),
                'enabled'         => $this->input->post('enabled'),
                'notify_client'   => $this->input->post('notify_client'),
            ];

            $this->employees_tracker_model->save_assignment($payload);

            $project_id = (int)$this->input->post('project_id');
            $lat = $this->input->post('project_lat');
            $lng = $this->input->post('project_lng');
            $address = $this->input->post('project_address');
            if ($project_id > 0 && $lat !== '' && $lng !== '') {
                $this->employees_tracker_model->set_project_location($project_id, $lat, $lng, $address);
            }

            set_alert('success', _l('settings_updated'));
            redirect(admin_url('employees_tracker/assignments'));
        }

        $data['title']       = _l('employees_tracker_assignments');
        $data['projects']    = $this->employees_tracker_model->get_all_projects();
        $data['staff']       = $this->employees_tracker_model->get_all_staff();
        $data['appointments']= $this->employees_tracker_model->get_appointments();
        $data['assignments'] = $this->employees_tracker_model->get_assignments();
        $this->load->view('employees_tracker/admin/assignments', $data);
    }

    public function settings()
    {
        if (!has_permission('employees_tracker', '', 'manage')) {
            access_denied('employees_tracker');
        }

        if ($this->input->post()) {
            update_option('employees_tracker_poll_interval', max(10, (int) $this->input->post('poll_interval')));
            update_option('employees_tracker_allow_staff_self_share', (int) $this->input->post('allow_staff_self_share'));
            update_option('employees_tracker_google_api_key', trim((string)$this->input->post('google_api_key')));
            update_option('employees_tracker_eta_notify_minutes', max(5, (int)$this->input->post('eta_notify_minutes')));
            update_option('employees_tracker_enable_email_notifications', (int)$this->input->post('enable_email_notifications'));
            update_option('employees_tracker_distance_provider', trim((string)$this->input->post('distance_provider')));
            update_option('employees_tracker_default_service_type', trim((string)$this->input->post('default_service_type')));

            set_alert('success', _l('settings_updated'));
            redirect(admin_url('employees_tracker/settings'));
        }

        $data['title'] = _l('employees_tracker_settings');
        $data['poll']  = (int) get_option('employees_tracker_poll_interval');
        $data['allow_staff_self_share'] = (int) get_option('employees_tracker_allow_staff_self_share');
        $data['google_api_key'] = get_option('employees_tracker_google_api_key');
        $data['core_google_api_key'] = get_option('google_api_key');
        $data['eta_notify_minutes'] = (int)get_option('employees_tracker_eta_notify_minutes');
        $data['enable_email_notifications'] = (int)get_option('employees_tracker_enable_email_notifications');
        $data['distance_provider'] = get_option('employees_tracker_distance_provider');
        $data['default_service_type'] = get_option('employees_tracker_default_service_type');

        $this->load->view('employees_tracker/admin/settings', $data);
    }

    public function save_location()
    {
        if (!is_staff_logged_in()) {
            show_404();
        }

        header('Content-Type: application/json');

        $allow = (int)get_option('employees_tracker_allow_staff_self_share');
        if ($allow !== 1 && !has_permission('employees_tracker', '', 'manage')) {
            echo json_encode(['success' => false, 'message' => 'Location sharing is disabled.']);
            exit;
        }

        $lat = $this->input->post('lat');
        $lng = $this->input->post('lng');
        $acc = $this->input->post('accuracy');
        $battery = $this->input->post('battery');

        if ($lat === null || $lng === null || !is_numeric($lat) || !is_numeric($lng)) {
            echo json_encode(['success' => false, 'message' => 'Missing coordinates']);
            exit;
        }

        $insert_id = $this->employees_tracker_model->save_location(get_staff_user_id(), $lat, $lng, $acc, $battery, 'browser');
        $error = $this->db->error();

        if (!$insert_id || (!empty($error['code']) && (int)$error['code'] !== 0)) {
            echo json_encode(['success' => false, 'message' => 'CRM could not save location.', 'db_error' => $error]);
            exit;
        }

        echo json_encode(['success' => true, 'insert_id' => (int)$insert_id]);
        exit;
    }

    public function staff_status($staff_id = 0, $project_id = null)
    {
        if (!has_permission('employees_tracker', '', 'view') && !has_permission('employees_tracker', '', 'view_own')) {
            show_404();
        }

        header('Content-Type: application/json');

        $staff_id = (int)$staff_id;
        $project_id = $project_id !== null ? (int)$project_id : null;

        if ($staff_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid staff id']);
            exit;
        }

        $out = $this->employees_tracker_model->get_staff_latest($staff_id);

        if ($project_id && $out) {
            $proj = $this->employees_tracker_model->get_project_location($project_id);
            if ($proj && isset($proj['lat']) && isset($proj['lng'])) {
                $distance = $this->employees_tracker_model->distance_km($proj['lat'], $proj['lng'], $out['lat'], $out['lng']);
                $out['distance_km'] = $distance;
                $out['distance_text'] = $distance !== null ? round($distance * 0.621371, 1) . ' miles' : null;
                $out['eta_minutes'] = $this->employees_tracker_model->eta_minutes_from_distance($distance);
            }
        }

        echo json_encode(['success' => (bool)$out, 'data' => $out ?: null]);
        exit;
    }



    public function live_locations()
    {
        if (!has_permission('employees_tracker', '', 'view') && !has_permission('employees_tracker', '', 'view_own')) {
            show_404();
        }

        header('Content-Type: application/json');
        $locations = $this->employees_tracker_model->get_all_latest_locations();
        echo json_encode(['success' => true, 'data' => $locations]);
        exit;
    }


    public function save_test_location()
    {
        if (!is_staff_logged_in()) {
            show_404();
        }

        header('Content-Type: application/json');

        $staff_id = get_staff_user_id();
        $lat = $this->input->post('lat');
        $lng = $this->input->post('lng');

        // Default Smart Choice test point near Spring Hill/Tampa Bay if browser location is not available.
        if ($lat === null || $lng === null || !is_numeric($lat) || !is_numeric($lng)) {
            $lat = 28.4769;
            $lng = -82.5255;
        }

        $insert_id = $this->employees_tracker_model->save_location($staff_id, $lat, $lng, 25, null, 'manual_test');
        $error = $this->db->error();

        if (!$insert_id || (!empty($error['code']) && (int)$error['code'] !== 0)) {
            echo json_encode(['success' => false, 'message' => 'Test location could not be saved.', 'db_error' => $error]);
            exit;
        }

        echo json_encode(['success' => true, 'insert_id' => (int)$insert_id, 'lat' => (float)$lat, 'lng' => (float)$lng]);
        exit;
    }

    public function my_latest_location()
    {
        if (!is_staff_logged_in()) {
            show_404();
        }
        header('Content-Type: application/json');
        $latest = $this->employees_tracker_model->get_staff_latest(get_staff_user_id());
        echo json_encode(['success' => (bool)$latest, 'data' => $latest ?: null]);
        exit;
    }

    public function health()
    {
        if (!is_admin()) {
            access_denied('employees_tracker');
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'module' => 'Installer ETA',
            'version' => '1.1.3',
            'php' => PHP_VERSION,
            'maps_api_key_available' => $this->employees_tracker_model->get_google_api_key() !== '',
            'saved_module_google_value_is_valid' => $this->employees_tracker_model->is_valid_google_maps_key(get_option('employees_tracker_google_api_key')),
            'live_locations_url' => admin_url('employees_tracker/live_locations'),
            'save_location_url' => admin_url('employees_tracker/save_location'),
            'save_test_location_url' => admin_url('employees_tracker/save_test_location'),
            'database' => $this->employees_tracker_model->get_database_health(),
        ]);
        exit;
    }
}

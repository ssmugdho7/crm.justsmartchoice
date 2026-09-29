<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Services extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('service_model');
        $this->load->model('staff_model');
    }

    public function index()
    {
        if (!staff_can('view', 'appointments')) {
            access_denied();
        }

        $data['title'] = _l('appointly_services');

        $this->load->view('services/manage', $data);
    }

    /**
     * Edit service or add new one
     *
     * @param integer $id
     */
    public function service($id = '')
    {
        if (!staff_can('view', 'appointments')) {
            access_denied();
        }
        if (isset($id)) {
            $data['is_service_in_use'] = $this->service_model->is_service_in_use($id);
        } else {
            $data['is_service_in_use'] = false;
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            // Handle service providers
            $providers = [];
            $primary_provider = null;

            if (!empty($data['staff_members'])) {
                $providers = $data['staff_members'];
                unset($data['staff_members']);
            }

            if (!empty($data['primary_provider'])) {
                $primary_provider = $data['primary_provider'];
                unset($data['primary_provider']);
            }

            // Handle buffer times
            $data['buffer_before'] = $data['buffer_before'] ?? 0;
            $data['buffer_after'] = $data['buffer_after'] ?? 0;

            // Check if service is in use and handle active status accordingly
            if ($id !== '' && $this->service_model->is_service_in_use($id)) {
                $existing = $this->service_model->get($id);

                // Check if existing service was found
                if (!$existing) {
                    echo json_encode([
                        'success' => false,
                        'message' => _l('service_not_found')
                    ]);
                    die();
                }

                // For services in use, show warning but allow updates (except disabling)
                $data['active'] = $existing->active; // Keep existing active status

                // If trying to disable a service that's in use, return error
                if (isset($data['active']) && $data['active'] != $existing->active && $data['active'] == '0') {
                    echo json_encode([
                        'success' => false,
                        'message' => _l('appointly_service_in_use_warning')
                    ]);
                    die();
                }

                // Show warning that service is in use but allow update
                $service_in_use_warning = true;
            } else {
                $data['active'] = isset($data['active']) && $data['active'] == '1' ? 1 : 0;
                $service_in_use_warning = false;
            }

            if ($id == '') {
                // Adding new service
                $service_id = $this->service_model->add($data);
                if ($service_id) {
                    // Update service providers
                    if (!empty($providers)) {
                        $this->service_model->update_service_providers($service_id, $providers, $primary_provider);
                    }

                    echo json_encode([
                        'success' => true,
                        'message' => _l('appointly_service_add_success', _l('service')),
                        'id' => $service_id
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => _l('error_adding_service')
                    ]);
                }
            } else {
                // Updating existing service
                $success = $this->service_model->update($id, $data);
                if ($success) {
                    // Update service providers
                    if (!empty($providers)) {
                        $this->service_model->update_service_providers($id, $providers, $primary_provider);
                    }

                    $message = _l('appointly_service_edit_success', _l('service'));
                    if (isset($service_in_use_warning) && $service_in_use_warning) {
                        $message .= ' ' . _l('appointly_service_in_use_warning');
                    }

                    echo json_encode([
                        'success' => true,
                        'message' => $message,
                        'id' => $id,
                        'warning' => isset($service_in_use_warning) ? $service_in_use_warning : false
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => _l('error_updating_service')
                    ]);
                }
            }
            die();
        }

        if ($id == '') {
            $title = _l('add_new', _l('service_lowercase'));
            $data['service_is_active_checked'] = null;
            $data['service'] = new stdClass();
            $data['service']->name = '';
            $data['service']->description = '';
            $data['service']->duration = 60;
            $data['service']->price = 0;
            $data['service']->color = '#28B8DA';
            $data['service']->active = 1;
            $data['service']->buffer_before = 0;
            $data['service']->buffer_after = 0;
            $data['service']->staff_members = [];
            $data['service']->working_hours = [];
        } else {
            $service = $this->service_model->get($id);

            // Check if service exists
            if (!$service) {
                show_404();
                return;
            }

            $data['service'] = $service;
            $title = _l('edit', _l('service_lowercase'));
            $data['service_is_active_checked'] = $service->active == 1 ? 'checked' : '';
        }

        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $data['title'] = $title;
        $this->load->view('services/service', $data);
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'appointments')) {
            ajax_access_denied();
        }

        // Check if service is in use
        if ($this->service_model->is_service_in_use($id)) {
            echo json_encode([
                'success' => false,
                'message' => _l('appointly_service_in_use_warning')
            ]);
            return;
        }

        $response = [
            'success' => $this->service_model->delete($id),
            'message' => _l('appointly_service_delete_success')
        ];

        echo json_encode($response);
    }

    public function reorder()
    {
        if(!staff_can('edit','appointments')){ajax_access_denied();}
        $ids = $this->input->post('service_ids');
        if (!is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'Invalid order.']);
            return;
        }

        $servicesTable = db_prefix() . 'appointly_services';
        if (!$this->db->field_exists('sort_order', $servicesTable)) {
            $this->db->query(
                'ALTER TABLE `' . $servicesTable . '` ADD `sort_order` INT(11) NOT NULL DEFAULT 0 AFTER `active`'
            );
        }

        foreach ($ids as $i => $id) {
            $this->db->where('id', (int) $id)->update($servicesTable, ['sort_order' => $i + 1]);
        }
        echo json_encode(['success' => true, 'message' => 'Service order saved.']);
    }

    public function table()
    {
        if (!staff_can('view', 'appointments')) {
            ajax_access_denied();
        }


        $this->app->get_table_data(module_views_path('appointly', 'tables/services'));
    }

    public function change_status($id, $status)
    {
        if (!staff_can('edit', 'appointments')) {
            ajax_access_denied();
        }

        // Check if service is in use
        if ($this->service_model->is_service_in_use($id)) {
            echo json_encode([
                'success' => false,
                'message' => _l('appointly_service_in_use_warning')
            ]);
            return;
        }

        $success = $this->service_model->change_status($id, $status);

        echo json_encode([
            'success' => $success,
            'message' => $success
                ? _l('service_status_changed_success')
                : _l('service_status_changed_fail')
        ]);
    }

    public function get_working_hours($service_id)
    {
        // Load the model if not already loaded
        if (!isset($this->appointly_model)) {
            $this->load->model('appointly/appointly_model');
        }

        // Get service provider information
        $this->db->select('staff_id, is_primary');
        $this->db->from(db_prefix() . 'appointly_service_staff');
        $this->db->where('service_id', $service_id);
        $this->db->where('is_primary', 1);
        $primary_provider = $this->db->get()->row();

        $staff_id = $primary_provider ? $primary_provider->staff_id : null;

        if ($staff_id) {
            // Get staff working hours
            $working_hours = $this->appointly_model->get_staff_working_hours($staff_id);
            echo json_encode($working_hours);
        } else {
            // Fallback to company schedule
            $company_schedule = $this->appointly_model->get_company_schedule();
            echo json_encode($company_schedule);
        }
    }


    /**
     * Get service duration by service ID
     * 
     * @param int $service_id
     * @return int
     */
    public function get_service_duration($service_id)
    {
        $this->db->select('duration');
        $this->db->where('id', $service_id);
        $service = $this->db->get(db_prefix() . 'appointly_services')->row();

        return $service ? $service->duration : 60; // Default to 60 minutes if not found
    }

    public function get_service_details()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $service_id = $this->input->post('service_id');

        if (!$service_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Service ID is required'
            ]);
            die();
        }

        // Get service details
        $service = $this->service_model->get($service_id);

        // Get staff members assigned to this service
        $providers = [];
        if ($service && !empty($service->staff_members)) {
            $staff_members = is_array($service->staff_members) ? $service->staff_members : json_decode($service->staff_members, true);

            if (!empty($staff_members)) {
                $this->db->select('staffid, firstname, lastname');
                $this->db->from(db_prefix() . 'staff');
                $this->db->where_in('staffid', $staff_members);
                $this->db->where('active', 1);
                $providers = $this->db->get()->result_array();
            }
        }

        if (!$service) {
            echo json_encode([
                'success' => false,
                'message' => 'Service not found'
            ]);
            die();
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'service' => $service,
                'providers' => $providers
            ]
        ]);
        die();
    }

    public function get_staff_schedule()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $staff_id = $this->input->post('staff_id');

        // Use the helper function
        $result = appointly_get_staff_schedule($staff_id);

        // Return the result as JSON
        echo json_encode($result);
    }

    /**
     * Get service staff (admin variant)
     *
     * This method returns staff members assigned to a service
     * Used by the admin appointment modal
     *
     * @return void
     */
    public function get_service_staff()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $service_id = $this->input->post('service_id');

        // Use the helper function
        $result = appointly_get_service_staff($service_id);

        // Return the result as JSON
        echo json_encode($result);
    }

    /**
     * Display and manage company schedule
     */
    public function company_schedule()
    {
        if (!staff_can('view', 'appointments')) {
            access_denied();
        }
        if ($this->input->post()) {
            // Handle form submission
            $schedule_data = $this->input->post('company_schedule');

            // Load model
            $this->load->model('appointly/appointly_model');

            // Process and save the schedule data
            $schedule = [];
            foreach ($schedule_data as $day => $hours) {
                // Check for value "1" for is_enabled (checkbox checked)
                $is_enabled = isset($hours['is_enabled']) && $hours['is_enabled'] == 1;

                // Default values for disabled days
                $start_time = '09:00';
                $end_time = '17:00';

                // Only use submitted times if the day is enabled and times are set
                if ($is_enabled) {
                    $start_time = isset($hours['start_time']) ? $hours['start_time'] : $start_time;
                    $end_time = isset($hours['end_time']) ? $hours['end_time'] : $end_time;
                }

                $schedule[$day] = [
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'is_enabled' => $is_enabled ? 1 : 0
                ];
            }

            $success = $this->appointly_model->save_company_schedule($schedule);

            if ($success) {
                set_alert('success', _l('settings_updated'));
            } else {
                set_alert('warning', _l('settings_update_failed'));
            }

            redirect(admin_url('appointly/services/company_schedule'));
        }

        $data['title'] = _l('appointly_company_schedule');
        $this->load->view('settings/company_schedule', $data);
    }

    /**
     * Manage staff working hours
     *
     * @param int $staff_id Optional staff ID
     */
    public function staff_working_hours($staff_id = null)
    {
        // Staff can always view/edit their own working hours
        // Admin or those with edit permission can view/edit anyone's hours
        if (!staff_can('edit', 'appointments') && $staff_id != get_staff_user_id()) {
            access_denied('appointments');
        }

        // If no staff ID is provided, use the current user
        if (!$staff_id) {
            $staff_id = get_staff_user_id();
        }

        if ($this->input->post()) {
            // Handle form submission
            $working_hours_data = $this->input->post('working_hours');

            // Load model
            if (!isset($this->appointly_model)) {
                $this->load->model('appointly/appointly_model');
            }

            // Process and save the working hours data
            $working_hours = [];
            foreach ($working_hours_data as $day => $hours) {
                // Check for value "1" for is_available (checkbox checked)
                $is_available = isset($hours['is_available']) && $hours['is_available'] == 1;

                // Check for use_company_schedule checkbox
                $use_company_schedule = isset($hours['use_company_schedule']) && $hours['use_company_schedule'] == 1;

                // Default values for unavailable days
                $start_time = '09:00';
                $end_time = '17:00';

                // Only use submitted times if the day is available and times are set
                if ($is_available) {
                    $start_time = isset($hours['start_time']) ? $hours['start_time'] : $start_time;
                    $end_time = isset($hours['end_time']) ? $hours['end_time'] : $end_time;
                }

                $working_hours[$day] = [
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'is_available' => $is_available ? 1 : 0,
                    'use_company_schedule' => $use_company_schedule ? 1 : 0
                ];
            }

            $success = $this->appointly_model->save_staff_working_hours($staff_id, $working_hours);

            if ($success) {
                set_alert('success', _l('settings_updated'));
            } else {
                set_alert('warning', _l('settings_update_failed'));
            }

            redirect(admin_url('appointly/services/staff_working_hours/' . $staff_id));
        }

        // Get staff data
        $staff = $this->staff_model->get($staff_id);
        if (!$staff) {
            show_404();
        }

        // Load working hours from database
        $this->load->model('appointly/appointly_model');
        $working_hours = $this->appointly_model->get_staff_working_hours($staff_id);

        // Pass data to view
        if ($staff && (!empty($staff->firstname) || !empty($staff->lastname))) {
            $data['title'] = _l('appointly_staff_working_hours') . ' - ' . $staff->firstname . ' ' . $staff->lastname;
        } else {
            $data['title'] = _l('appointly_staff_working_hours');
        }
        $data['staff'] = $staff;
        $data['working_hours'] = $working_hours;
        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);

        $this->load->view('settings/staff_working_hours', $data);
    }

    /**
     * Get service available slots
     * 
     * Used by the appointment booking form to show available slots
     * for a specific service on a specific date
     */
    public function get_available_slots()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $service_id = $this->input->post('service_id');
        $date = $this->input->post('date');
        $provider_id = $this->input->post('provider_id');

        if (!$service_id || !$date) {
            echo json_encode([
                'success' => false,
                'message' => 'Service ID and date are required'
            ]);
            die();
        }

        // If no provider ID is specified, try to get the primary provider
        if (!$provider_id) {
            $service = $this->service_model->get($service_id);
            if ($service && isset($service->staff_members) && !empty($service->staff_members)) {
                if (is_string($service->staff_members)) {
                    $staff_members = json_decode($service->staff_members, true);
                    if (!empty($staff_members)) {
                        $provider_id = $staff_members[0];
                    }
                } else if (is_array($service->staff_members)) {
                    $provider_id = $service->staff_members[0];
                }
            }
        }

        if (!$provider_id) {
            echo json_encode([
                'success' => false,
                'message' => 'No provider available for this service'
            ]);
            die();
        }

        // Load model
        $this->load->model('appointly/appointly_model');

        // Get available slots
        $slots = $this->appointly_model->get_available_time_slots($provider_id, $date, $service_id);

        echo json_encode([
            'success' => true,
            'data' => [
                'slots' => $slots
            ]
        ]);
        die();
    }

    /**
     * Check if service is in use
     * 
     * @param int $service_id
     * @return json
     */
    public function is_service_in_use($service_id)
    {
        $this->load->model('appointly/service_model');
        $is_in_use = $this->service_model->is_service_in_use($service_id);

        echo json_encode([
            'success' => true,
            'in_use' => $is_in_use
        ]);
    }

    /**
     * Save services availability for the external booking form
     * Called via AJAX from the services_selection_settings_js function
     * 
     * @return void
     */
    public function save_service_availability_ajax()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!staff_can('edit', 'appointments')) {
            ajax_access_denied();
        }

        $services = $this->input->post('services');

        if ($services === null) {
            echo json_encode([
                'success' => false,
                'message' => 'No services data provided'
            ]);
            die();
        }

        // Validate that we have valid JSON
        if (!json_decode($services)) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid services data format'
            ]);
            die();
        }

        $success = update_option('appointments_booking_services_availability', $services);

        echo json_encode([
            'success' => $success,
            'message' => $success
                ? _l('services_availability_updated_successfully')
                : _l('services_availability_update_failed')
        ]);
        die();
    }
}

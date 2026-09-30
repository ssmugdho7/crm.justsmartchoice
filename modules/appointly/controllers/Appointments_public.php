<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointments_public extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('appointly');

        $this->load->model('appointly/appointly_model', 'apm');
        $this->load->model('staff_model');
    }

    private function is_local_request()
    {
        $localHosts = ['localhost', '127.0.0.1', '::1'];
        $configuredHost = strtolower((string) parse_url((string) APP_BASE_URL, PHP_URL_HOST));
        $requestHost = strtolower((string) parse_url(
            'http://' . (string) $this->input->server('HTTP_HOST', true),
            PHP_URL_HOST
        ));
        $remoteAddress = (string) $this->input->server('REMOTE_ADDR', true);

        return in_array($configuredHost, $localHosts, true)
            && in_array($requestHost, $localHosts, true)
            && in_array($remoteAddress, ['127.0.0.1', '::1'], true);
    }


    /**
     * Clean public booking link used by Smart Choice website, emails, QR codes, and customer portal.
     * This method is intentionally public and does not require admin login.
     * URL: /booking-link
     * Fallback URL: /smart-choice-booking
     * Fallback URL: /appointly/public-booking
     *
     * @return void
     */
    public function public_booking_link()
    {
        return $this->book();
    }

    /**
     * Clients hash view.
     *
     * @return void
     */
    public function client_hash()
    {
        // Try to get hash from URL segment first (for friendly URLs), then from query parameter (backward compatibility)
        $hash = $this->uri->segment(3) ?: $this->input->get('hash');
        $form = new stdClass();
        $form->language = get_option('active_language');

        $this->lang->load($form->language . '_lang', $form->language);

        if (file_exists(APPPATH . 'language/' . $form->language . '/custom_lang.php')) {
            $this->lang->load('custom_lang', $form->language);
        }

        if (!$hash) {
            show_404();
        }

        $appointment = $this->apm->get_public_appointment_by_hash($hash);

        if (!$appointment) {
            show_404();
        }
        $appointment['url'] = site_url('appointly/appointments_public/cancel_appointment');
        // Get service details
        if (isset($appointment['service_id'])) {
            $service = $this->apm->get_service($appointment['service_id']);
            if ($service) {
                $appointment['service'] = [
                    'name' => $service->name,
                    'description' => $service->description,
                    'duration' => $service->duration,
                    'price' => $service->price
                ];
            } else {
                // Service not found, set default values
                $appointment['service'] = [
                    'name' => 'Service Not Available',
                    'description' => '',
                    'duration' => 0,
                    'price' => 0
                ];
            }
        }
        $appointment['feedback_url'] = site_url('appointly/appointments_public/handleFeedbackPost');

        // Get assigned staff details
        if (isset($appointment['provider_id'])) {
            $provider = $this->staff_model->get($appointment['provider_id']);
            if ($provider) {
                $appointment['assigned_staff'] = [
                    'staffid' => $provider->staffid,
                    'full_name' => $provider->firstname . ' ' . $provider->lastname,
                    'firstname' => $provider->firstname,
                    'lastname' => $provider->lastname,
                    'email' => $provider->email,
                    'profile_image' => staff_profile_image_url($provider->staffid)
                ];
            } else {
                // Provider not found, set default values
                $appointment['assigned_staff'] = [
                    'staffid' => null,
                    'full_name' => 'Staff Member Not Available',
                    'firstname' => 'Staff Member',
                    'lastname' => 'Not Available',
                    'email' => '',
                    'profile_image' => staff_profile_image_url(0)
                ];
            }
        }

        // Check for pending reschedule requests
        $appointment['has_pending_reschedule'] = $this->apm->has_pending_reschedule($appointment['id']);

        // Get pending reschedule details if exists
        if ($appointment['has_pending_reschedule']) {
            $this->db->select('*');
            $this->db->from(db_prefix() . 'appointly_reschedule_requests');
            $this->db->where('appointment_id', $appointment['id']);
            $this->db->where('status', 'pending');
            $this->db->order_by('requested_at', 'DESC');
            $this->db->limit(1);
            $pending_reschedule = $this->db->get()->row_array();

            if ($pending_reschedule) {
                $appointment['pending_reschedule'] = $pending_reschedule;
            }
        }

        $data['appointment'] = $appointment;
        $data['form'] = $form;
        // Throws error now is ok -- we dont event use recpatcha here but we use app_external_form thats why we need this
        $data['form']->recaptcha = 0;

        $this->load->view('clients/clients_hash', $data);
    }

    /**
     * Fetches contact data if client who requested meeting is already in the system.
     *
     * @return void
     */
    public function external_fetch_contact_data()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('contact_id');

        header('Content-Type: application/json');
        echo json_encode($this->apm->apply_contact_data($id, false));
    }

    /**
     * Handles clients external public form.
     *
     * @return void
     */
    public function book()
    {
        $form = new stdClass();
        $form->language = get_option('active_language');

        $this->lang->load($form->language . '_lang', $form->language);

        if (file_exists(APPPATH . 'language/' . $form->language . '/custom_lang.php')) {
            $this->lang->load('custom_lang', $form->language);
        }

        // Always show every active service on the public booking form.
        // Previous builds stored only the four original IDs in
        // appointments_booking_services_availability, which hid newly-added
        // services and made the booking page appear incomplete.
        $services = $this->apm->get_services();


        $data = [
            'form' => $form,
            'services' => $services
        ];
        // Set recaptcha if enabled AND API keys are properly configured
        $appointly_recaptcha_enabled = get_option('appointly_appointments_recaptcha');
        $recaptcha_keys_configured = (get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '');

        // Only enable recaptcha if both settings are properly configured
        $data['form']->recaptcha = !$this->is_local_request()
            && $appointly_recaptcha_enabled == 1
            && $recaptcha_keys_configured;

        // Get base currency for pricing display
        $data['baseCurrency'] = appointly_get_base_currency();

        // Check if terms and conditions are enabled
        $data['enable_terms'] = (get_option('appointments_enable_terms_conditions') == 1);
        $data['terms_link'] = get_option('terms_and_conditions_url');

        $this->load->view('forms/appointments_external_form', $data);
    }

    /**
     * Handles creation of an external appointment.
     *
     * @return void
     */
    public function create_external_appointment()
    {
        $data = $this->input->post();

        // Check if reCAPTCHA is enabled and properly configured
        $appointly_recaptcha_enabled = get_option('appointly_appointments_recaptcha');
        $recaptcha_keys_configured = (get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '');
        $recaptcha_active = !$this->is_local_request()
            && $appointly_recaptcha_enabled == 1
            && $recaptcha_keys_configured;

        // Skip reCAPTCHA validation if it's disabled or keys not configured
        if (!$recaptcha_active) {
            unset($data['g-recaptcha-response']);
        }
        // Validate reCAPTCHA if enabled and keys are properly configured
        else {
            if (!isset($data['g-recaptcha-response']) || !do_recaptcha_validation($data['g-recaptcha-response'])) {
                echo json_encode([
                    'success' => false,
                    'recaptcha' => false,
                    'message' => _l('recaptcha_error')
                ]);
                die;
            }
        }

        // Combine firstname and lastname into a single name field
        $data['name'] = trim($data['firstname'] . ' ' . $data['lastname']);

        // Handle logged-in client - set contact_id if client is logged in
        if (is_client_logged_in()) {

            $client_id = get_client_user_id();

            // Find the contact that matches the email for this client
            $this->db->select('id');
            $this->db->where('userid', $client_id);
            $this->db->where('email', $data['email']);
            $contact = $this->db->get(db_prefix() . 'contacts')->row();

            if ($contact) {
                $data['contact_id'] = $contact->id;
            } else {
                // If no matching contact found, try to get the primary contact
                $this->db->select('id');
                $this->db->where('userid', $client_id);
                $this->db->where('is_primary', 1);
                $primary_contact = $this->db->get(db_prefix() . 'contacts')->row();

                if ($primary_contact) {
                    $data['contact_id'] = $primary_contact->id;
                }
            }
        }

        unset($data['firstname'], $data['lastname'], $data['logged_in_client_id']);
        // Process date and time fields
        if (!empty($data['date']) && !empty($data['start_hour'])) {
            // Ensure date is in correct format
            $data['date'] = to_sql_date($data['date']);

            // Format start_hour properly if needed
            if (!preg_match('/^\d{2}:\d{2}$/', $data['start_hour'])) {
                // If it's not in HH:MM format, try to convert it
                $start_time = date_create_from_format('g:i A', $data['start_hour']);
                if ($start_time) {
                    $data['start_hour'] = $start_time->format('H:i');
                }
            }

            // Calculate end_hour if not provided but we have duration
            if ((!isset($data['end_hour']) || empty($data['end_hour'])) && !empty($data['service_id'])) {
                // Get service duration
                $this->load->model('appointly/service_model');
                $service = $this->apm->get_service($data['service_id']);

                if ($service && isset($service->duration)) {
                    // Parse start hour
                    list($hours, $minutes) = explode(':', $data['start_hour']);

                    // Add duration minutes
                    $end_timestamp = mktime($hours, $minutes, 0) + ($service->duration * 60);

                    // Format end hour
                    $data['end_hour'] = date('H:i', $end_timestamp);
                }
            }
        }

        // Remove recaptcha from data before insert
        unset($data['g-recaptcha-response']);

        // Remove notification preferences - these should be managed by staff after approval
        unset($data['by_sms']);
        unset($data['by_email']);

        // Set additional fields for external appointments
        $data['rel_type'] = 'external';

        $appointment_id = $this->apm->insert_external_appointment($data);

        if ($appointment_id) {
            // Store the appointment ID in session for the success page
            $this->session->set_userdata('last_appointment_id', $appointment_id);

            // Generate a security token to prevent direct URL access to success page
            $security_token = md5('appointly_success_' . $appointment_id . time() . rand(1000, 9999));
            $this->session->set_userdata('appointly_success_token', $security_token);

            echo json_encode([
                'success' => true,
                'message' => _l('appointment_created'),
                'appointment_id' => $appointment_id,
                'redirect_url' => site_url('appointly/appointments_public/success_message?token=' . $security_token)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_creation_failed')
            ]);
        }
    }

    /**
     * Handles appointment cancelling.
     *
     * @return bool|void
     */
    public function cancel_appointment()
    {
        if ($this->input->get('hash')) {
            $hash = $this->input->get('hash');
            $notes = $this->input->get('notes');

            if ($notes == '') {
                return false;
            }

            if (! $hash) {
                show_404();
            }

            $appointment = $this->apm->get_public_appointment_by_hash($hash);

            if (! $appointment) {
                show_404();
            } else {
                $cancellation_in_progress = $this->apm->check_if_user_requested_appointment_cancellation($hash);

                header('Content-Type: application/json');
                if ($cancellation_in_progress['cancel_notes'] === null) {
                    echo json_encode($this->apm->appointment_cancellation_handler($hash, $notes));
                } else {
                    echo json_encode([
                        'response' => [
                            'message' => _l('appointments_already_applied_for_cancelling'),
                            'success' => false,
                        ],
                    ]);
                }
            }
        } else {
            show_404();
        }
    }

    public function handleFeedbackPost()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');

        if (! $id) {
            show_404();
        }

        $rating = $this->input->post('rating');

        $comment = ($this->input->post('feedback_comment')) ?: null;

        $appointmentData = $this->apm->get_appointment($id);

        if (!$appointmentData || !is_array($appointmentData)) {
            show_404();
            return;
        }

        // Ensure required fields exist
        $appointmentData['date'] = $appointmentData['date'] ?? date('Y-m-d');
        $appointmentData['start_hour'] = $appointmentData['start_hour'] ?? '00:00';
        $appointmentData['end_hour'] = $appointmentData['end_hour'] ?? '00:00';

        if ($this->apm->handle_feedback_post($id, $rating, $comment)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function reschedule_appointment()
    {
        header('Content-Type: application/json');

        $hash = $this->input->get('hash');
        if (!$hash) {
            echo json_encode(['success' => false, 'message' => 'Missing appointment hash']);
            return;
        }

        // Get appointment by hash
        $appointment = $this->apm->get_public_appointment_by_hash($hash);
        if (!$appointment) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }

        // Check if appointment can be rescheduled
        if (!in_array($appointment['status'], ['pending', 'in-progress'])) {
            echo json_encode(['success' => false, 'message' => _l('appointment_cannot_be_rescheduled')]);
            return;
        }

        // Check if already has pending reschedule
        if ($this->apm->has_pending_reschedule($appointment['id'])) {
            echo json_encode(['success' => false, 'message' => 'A reschedule request is already pending for this appointment']);
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid request data']);
            return;
        }

        $reschedule_date = $input['reschedule_date'] ?? '';
        $reschedule_time = $input['reschedule_time'] ?? '';
        $reschedule_reason = $input['reschedule_reason'] ?? '';

        // Validation
        if (empty($reschedule_date)) {
            echo json_encode(['success' => false, 'message' => _l('appointment_reschedule_date_required')]);
            return;
        }

        if (empty($reschedule_time)) {
            echo json_encode(['success' => false, 'message' => _l('appointment_reschedule_time_required')]);
            return;
        }

        // Check if the new date/time is in the future
        $requested_datetime = $reschedule_date . ' ' . $reschedule_time;
        if (strtotime($requested_datetime) <= time()) {
            echo json_encode(['success' => false, 'message' => _l('appointment_reschedule_future_datetime')]);
            return;
        }

        // Create reschedule request in separate table
        $reschedule_id = $this->apm->create_reschedule_request(
            $appointment['id'],
            $reschedule_date,
            $reschedule_time,
            $reschedule_reason
        );

        if ($reschedule_id) {
            // Send notification to staff using shared model method
            $this->apm->_send_reschedule_notification_to_staff($appointment, $reschedule_date, $reschedule_time, $reschedule_reason);

            echo json_encode([
                'success' => true,
                'message' => _l('appointment_reschedule_request_submitted')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('appointment_error_occurred')]);
        }
    }



    public function get_busy_times()
    {
        $date = $this->input->get('date');
        $staff_id = $this->input->get('staff_id');
        $service_id = $this->input->get('service_id');

        if (!$date) {
            echo json_encode([]);
            die();
        }

        $busy_times = $this->apm->getBusyTimes($date, $staff_id, $service_id);
        echo json_encode($busy_times);
        die();
    }

    /**
     * Get staff schedule
     * 
     * @return json
     */
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

    public function select_request_type()
    {
        $this->load->view('forms/request_type_selection');
    }

    public function success_message()
    {
        // Verify access - Check for security token
        $token = $this->input->get('token');
        $session_token = $this->session->userdata('appointly_success_token');
        $last_appointment_id = $this->session->userdata('last_appointment_id');

        // Get the referer URL
        $referer = $this->input->server('HTTP_REFERER');
        $valid_referer = false;

        // Check if referer is from our site
        if ($referer) {
            $site_url = site_url();
            $valid_referer = (str_starts_with($referer, $site_url));
        }

        // If no token provided or token doesn't match session token or no appointment ID, redirect to booking form
        if (empty($token) || empty($session_token) || $token !== $session_token || empty($last_appointment_id) || !$valid_referer) {
            // Clear any existing session data
            $this->session->unset_userdata('last_appointment_id');
            $this->session->unset_userdata('appointly_success_token');

            // Redirect to booking form
            redirect(site_url('appointly/appointments_public/book'));
        }

        // Token is valid, clear it to prevent reuse
        $this->session->unset_userdata('appointly_success_token');

        $data['message'] = _l('appointment_successfully_scheduled_message');
        $data['title'] = _l('appointment_booking_confirmed');
        $data['sub_message'] = _l('appointment_pending_approval_message');
        $data['whats_next'] = _l('appointment_whats_next');
        $data['staff_review'] = _l('appointment_staff_review');
        $data['email_confirmation'] = _l('appointment_email_confirmation');
        $data['prepare'] = _l('appointment_prepare');

        // Check if we have an appointment ID in the session (set during booking)
        $appointment_id = $this->session->userdata('last_appointment_id');

        // If we have an appointment ID, get the appointment details
        if ($appointment_id) {
            $appointment = $this->apm->get_appointment_data($appointment_id);

            // If appointment found, add service and provider details
            if ($appointment) {
                // Add service details if available
                if (!empty($appointment['service_id'])) {
                    $service = $this->apm->get_service($appointment['service_id']);
                    if ($service) {
                        $appointment['service_name'] = $service->name;
                        $appointment['service_duration'] = $service->duration;
                        $appointment['service_description'] = $service->description;
                    }
                }

                // Add provider details if available
                if (!empty($appointment['provider_id'])) {
                    $provider = $this->staff_model->get($appointment['provider_id']);
                    if ($provider) {
                        $appointment['provider_name'] = $provider->firstname . ' ' . $provider->lastname;
                        $appointment['provider_email'] = $provider->email;
                    }
                }

                $data['appointment'] = $appointment;
            }
        }

        $data['form'] = new stdClass();
        $data['form']->language = get_option('active_language');
        $data['form']->recaptcha = get_option('appointly_appointments_recaptcha');

        $this->lang->load($data['form']->language . '_lang', $data['form']->language);

        if (file_exists(APPPATH . 'language/' . $data['form']->language . '/custom_lang.php')) {
            $this->lang->load('custom_lang', $data['form']->language);
        }

        $this->load->view('forms/success_message', $data);
    }

    /**
     * Generate .ics calendar file for appointment (public access)
     */
    public function download_ics($appointment_id)
    {
        if (!$appointment_id) {
            show_404();
        }

        // Use existing get_appointment_data method for consistency
        $appointment = $this->apm->get_appointment_data($appointment_id);

        if (!$appointment) {
            show_404();
        }

        // Check permissions - allow if user is staff, client contact, or public access
        $has_permission = false;

        if (is_staff_logged_in()) {
            $has_permission = true;
        } elseif (is_client_logged_in()) {
            // Check if this is the client's appointment through contact_id
            if ($appointment['source'] === 'internal' && !empty($appointment['contact_id'])) {
                $this->load->model('clients_model');
                $contact = $this->clients_model->get_contact($appointment['contact_id']);
                if ($contact && $contact->userid == get_client_user_id()) {
                    $has_permission = true;
                }
            }
        } else {
            // For public access, allow download (appointment existence is already verified)
            $has_permission = true;
        }

        if (!$has_permission) {
            show_404();
        }

        // Generate .ics content using helper function
        $ics_content = generate_appointment_ics_content($appointment);

        // Set headers for .ics download
        $filename = strtolower(_l('appointment_label')) . '_' . $appointment_id . '_' . date('Y-m-d', strtotime($appointment['date'])) . '.ics';

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

        echo $ics_content;
        exit;
    }



    public function get_service_staff($service_id)
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->load->model('appointly/service_model');

        // Get service details
        $service = $this->service_model->get($service_id);
        if (!$service) {
            echo json_encode(['success' => false, 'message' => 'Service not found']);
            return;
        }

        // Get staff members from the service object (now populated from service_staff table)
        $staff_members = [];

        if (!empty($service->staff_members)) {
            $this->db->select('staffid, firstname, lastname, email, phonenumber');
            $this->db->from(db_prefix() . 'staff');
            $this->db->where_in('staffid', array_column($service->staff_members, 'staff_id'));
            $this->db->where('active', 1);
            $staff_query = $this->db->get();

            if ($staff_query && $staff_query->num_rows() > 0) {
                $staff_members = $staff_query->result_array();

                //  profile images
                foreach ($staff_members as &$member) {
                    $member['profile_image'] = staff_profile_image_url($member['staffid']);
                }
            }
        }

        // Get the primary provider's schedule if available, otherwise use company default
        $primary_provider_id = $service->primary_provider ?? null;

        // Format working hours - this uses the staff's working hours from settings
        $working_hours = [];
        $formatted_hours = [];

        if ($primary_provider_id) {
            // Use helper function to get staff schedule
            $schedule_data = appointly_get_staff_schedule($primary_provider_id);
            if (isset($schedule_data['data']['schedule'])) {
                $working_hours = $schedule_data['data']['schedule'];
                $formatted_hours = $schedule_data['data']['formatted_hours'];
            }
        } else {
            // Get company schedule
            $company_schedule = $this->apm->get_company_schedule();
            $company_working_hours = [];
            $company_formatted_hours = [];

            if (!empty($company_schedule)) {
                foreach ($company_schedule as $day_name => $day_data) {
                    $day_number = getWorkingDayNumber($day_name);
                    $day_key = strtolower($day_name);

                    $company_working_hours[$day_number] = [
                        'enabled' => isset($day_data['is_enabled']) && (bool) $day_data['is_enabled'],
                        'start_time' => $day_data['start_time'] ?? '09:00:00',
                        'end_time' => $day_data['end_time'] ?? '17:00:00'
                    ];

                    if (isset($day_data['is_enabled']) && $day_data['is_enabled']) {
                        $company_formatted_hours[] = [
                            'day' => _l('appointly_day_' . $day_key),
                            'day_key' => $day_key,
                            'start' => date('H:i', strtotime($day_data['start_time'] ?? '09:00:00')),
                            'end' => date('H:i', strtotime($day_data['end_time'] ?? '17:00:00'))
                        ];
                    }
                }
            }
        }

        // Prepare response data
        $response = [
            'success' => true,
            'data' => [
                'service' => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'description' => $service->description,
                    'duration' => $service->duration,
                    'price' => $service->price
                ],
                'working_hours' => $working_hours,
                'formatted_hours' => $formatted_hours,
                'staff' => $staff_members
            ]
        ];

        echo json_encode($response);
        die();
    }

    public function get_services()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->load->model('appointly/service_model');
        $services = $this->service_model->get_active_services();

        // Format services with necessary data
        foreach ($services as &$service) {
            $service['color'] = $service['color'] ?? '#3B82F6';  // Default to blue if no color set
        }

        echo json_encode($services);
        die();
    }

    /**
     * Get staff for a specific service
     */
    public function get_service_staff_public()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $service_id = $this->input->post('service_id');

        // Also check GET parameters for testing/compatibility
        if (!$service_id) {
            $service_id = $this->input->get('service_id');
        }

        if (!$service_id) {
            echo json_encode([
                'success' => false,
                'message' => _l('service_id_required')
            ]);
            die();
        }

        try {
            // Get service details first
            $this->load->model('appointly/service_model');
            $service = $this->service_model->get($service_id);

            if (!$service) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Service not found'
                ]);
                die();
            }

            // Get staff members for this service
            $staff_members = [];

            // Get staff IDs from service_staff table
            $this->db->select('staff_id, is_primary');
            $this->db->from(db_prefix() . 'appointly_service_staff');
            $this->db->where('service_id', $service_id);
            $service_staff = $this->db->get()->result_array();

            $staff_ids = [];
            $primary_provider_id = null;

            foreach ($service_staff as $staff) {
                $staff_ids[] = $staff['staff_id'];
                if ($staff['is_primary']) {
                    $primary_provider_id = $staff['staff_id'];
                }
            }

            // Get staff details
            if (!empty($staff_ids)) {
                $this->db->select('staffid, firstname, lastname, email, phonenumber');
                $this->db->from(db_prefix() . 'staff');
                $this->db->where_in('staffid', $staff_ids);
                $this->db->where('active', 1);
                $staff_query = $this->db->get();

                if ($staff_query && $staff_query->num_rows() > 0) {
                    $staff_members = $staff_query->result_array();

                    // Add profile images
                    foreach ($staff_members as &$member) {
                        $member['profile_image'] = staff_profile_image_url($member['staffid']);
                    }
                }
            }

            // Do not leave the booking form in a permanent loading state when
            // a newly-added service has not been assigned to a provider yet.
            // The response remains valid and the form displays its normal
            // "no providers" message.

            // Get company schedule directly from database
            $this->db->select('*');
            $this->db->from(db_prefix() . 'appointly_company_schedule');
            $company_schedule_raw = $this->db->get()->result_array();

            // Create company_working_hours directly from the raw company schedule
            $company_working_hours = [];
            $company_formatted_hours = [];

            foreach ($company_schedule_raw as $day) {
                // Use weekday to get day number
                $day_name = $day['weekday'];
                $day_number = getWorkingDayNumber($day_name);
                $day_key = strtolower($day_name);

                // Build company_working_hours array
                $company_working_hours[$day_number] = [
                    'enabled' => (bool)$day['is_enabled'],
                    'start_time' => $day['start_time'],
                    'end_time' => $day['end_time']
                ];

                // Add to formatted hours if enabled
                if ($day['is_enabled']) {
                    $company_formatted_hours[] = [
                        'day' => _l('appointly_day_' . $day_key),
                        'day_key' => $day_key,
                        'start' => date('H:i', strtotime($day['start_time'])),
                        'end' => date('H:i', strtotime($day['end_time']))
                    ];
                }
            }

            // Get staff-specific schedules
            $staff_schedules = [];

            foreach ($staff_members as $staff) {
                $staff_id = $staff['staffid'];

                // Get this staff's working hours
                $hours = $this->apm->get_staff_working_hours($staff_id);
                $formatted_hours = [];
                $working_hours = [];

                if (!empty($hours)) {
                    // Staff has custom hours
                    foreach ($hours as $day => $hour_data) {
                        $day_number = getWorkingDayNumber($day);
                        $day_key = strtolower($day);

                        // First determine if this day should be enabled and what hours to use
                        $is_available = isset($hour_data['is_available']) && (bool) $hour_data['is_available'];
                        $use_company = isset($hour_data['use_company_schedule']) && (bool) $hour_data['use_company_schedule'];
                        $company_day_enabled = false;
                        $start_time = $hour_data['start_time'] ?? '09:00:00';
                        $end_time = $hour_data['end_time'] ?? '17:00:00';

                        // If using company schedule, check if that day is enabled in company schedule
                        if ($use_company) {
                            if (isset($company_working_hours[$day_number])) {
                                $company_day_enabled = isset($company_working_hours[$day_number]['enabled']) && (bool) $company_working_hours[$day_number]['enabled'];

                                if ($company_day_enabled) {
                                    $start_time = $company_working_hours[$day_number]['start_time'];
                                    $end_time = $company_working_hours[$day_number]['end_time'];

                                    // When using company schedule AND that day is enabled, 
                                    // we need to make sure is_available is true so it shows up
                                    $is_available = true;
                                }
                            }
                        }

                        // Store in the working_hours array
                        $working_hours[$day_number] = [
                            'enabled' => $use_company ? $company_day_enabled : $is_available,
                            'start_time' => $start_time,
                            'end_time' => $end_time,
                            'use_company_schedule' => $use_company
                        ];

                        // For formatted hours, include this day if:
                        // 1. Staff is available and not using company schedule, OR
                        // 2. Staff is using company schedule and that day is enabled in company schedule
                        if ((!$use_company && $is_available) || ($use_company && $company_day_enabled)) {
                            $formatted_hours[] = [
                                'day' => _l('appointly_day_' . $day_key),
                                'day_key' => $day_key,
                                'start' => date('H:i', strtotime($start_time)),
                                'end' => date('H:i', strtotime($end_time))
                            ];
                        }
                    }
                } else {
                    // Staff uses company schedule
                    $working_hours = $company_working_hours;
                    $formatted_hours = $company_formatted_hours;
                }

                $staff_schedules[$staff_id] = [
                    'working_hours' => $working_hours,
                    'formatted_hours' => $formatted_hours
                ];
            }

            // Get busy times
            $busy_times = $this->apm->get_busy_times_by_service($service_id);

            // Prepare response
            $response = [
                'success' => true,
                'data' => [
                    'service' => $service,
                    'staff' => $staff_members,
                    'staff_schedules' => $staff_schedules,
                    'working_hours' => $staff_schedules[$primary_provider_id]['working_hours'] ?? $company_working_hours,
                    'formatted_hours' => $staff_schedules[$primary_provider_id]['formatted_hours'] ?? $company_formatted_hours,
                    'busy_times' => $busy_times
                ]
            ];

            echo json_encode($response);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => _l('appointly_error_loading_providers')
            ]);
        }

        die();
    }

    /**
     * Get available time slots for a specific date.
     *
     * @return void
     */
    public function get_available_time_slots()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Get required parameters
        $service_id = $this->input->post('service_id');
        $provider_id = $this->input->post('provider_id') ?: $this->input->post('staff_id');
        $date = $this->input->post('date');
        $timezone = $this->input->post('timezone') ?: date_default_timezone_get();

        // Validate required parameters
        if (!$service_id || !$provider_id || !$date) {
            echo json_encode([
                'success' => false,
                'message' => _l('appointly_missing_required_fields')
            ]);
            die();
        }

        try {
            // Get service information
            $this->load->model('appointly/service_model');
            $service = $this->service_model->get($service_id);

            if (!$service) {
                echo json_encode([
                    'success' => false,
                    'message' => _l('appointly_service_not_found')
                ]);
                die();
            }

            // Get provider's working hours for this day
            $day_of_week = date('l', strtotime($date));

            // Initialize working hours
            $working_hours = [];
            $provider_available = false;

            try {
                // First try to get staff working hours
                $staff_hours = $this->apm->get_staff_working_hours($provider_id);
                $staff_has_custom_hours = false;

                if ($staff_hours && isset($staff_hours[$day_of_week])) {
                    $day_schedule = $staff_hours[$day_of_week];
                    $staff_has_custom_hours = true;

                    // Check if staff uses company schedule or has custom hours
                    if (isset($day_schedule['use_company_schedule']) && $day_schedule['use_company_schedule']) {
                        // Use company schedule
                        $company_schedule = $this->apm->get_company_schedule();
                        if (isset($company_schedule[$day_of_week]) && $company_schedule[$day_of_week]['is_enabled']) {
                            $provider_available = true;
                            $working_hours['start_time'] = $company_schedule[$day_of_week]['start_time'];
                            $working_hours['end_time'] = $company_schedule[$day_of_week]['end_time'];
                        }
                    } elseif (
                        isset($day_schedule['is_available'], $day_schedule['start_time']) && $day_schedule['is_available'] && isset($day_schedule['end_time'])
                    ) {
                        $provider_available = true;
                        $working_hours['start_time'] = $day_schedule['start_time'];
                        $working_hours['end_time'] = $day_schedule['end_time'];
                    }
                }
            } catch (Exception $e) {
                log_message('error', 'Error getting staff working hours: ' . $e->getMessage());
            }

            // Only fall back to company schedule if staff has NO custom hours set for this day
            if (!$provider_available && !$staff_has_custom_hours) {
                try {
                    $company_schedule = $this->apm->get_company_schedule();

                    if (isset($company_schedule[$day_of_week]) && $company_schedule[$day_of_week]['is_enabled']) {
                        $provider_available = true;
                        $working_hours['start_time'] = $company_schedule[$day_of_week]['start_time'];
                        $working_hours['end_time'] = $company_schedule[$day_of_week]['end_time'];
                    }
                } catch (Exception $e) {
                    log_message('error', 'Error getting company schedule: ' . $e->getMessage());
                }
            }

            // Check if provider is available for this day
            if (!$provider_available) {
                echo json_encode([
                    'success' => true,
                    'time_slots' => [],
                    'date' => $date,
                    'service_id' => $service_id,
                    'provider_id' => $provider_id,
                    'timezone' => $timezone,
                    'message' => _l('appointment_provider_not_available')
                ]);
                die();
            }

            // Validate that we have working hours
            if (! isset($working_hours['start_time'], $working_hours['end_time'])) {
                echo json_encode([
                    'success' => false,
                    'message' => _l('appointly_invalid_working_hours')
                ]);
                die();
            }

            // Validate working hours format
            $start_time = strtotime($working_hours['start_time']);
            $end_time = strtotime($working_hours['end_time']);

            if (!$start_time || !$end_time || $start_time >= $end_time) {
                echo json_encode([
                    'success' => false,
                    'message' => _l('appointly_invalid_working_hours')
                ]);
                die();
            }

            // Get busy times for the provider on this date
            $busy_times = $this->apm->get_busy_times_by_date($provider_id, $date);

            // Service duration in minutes
            $duration = (int) $service->duration;
            $buffer_before = (int) ($service->buffer_before ?? 0);
            $buffer_after = (int) ($service->buffer_after ?? 0);

            // Total slot time including buffers
            $slot_duration = $duration + $buffer_before + $buffer_after;

            // Generate time slots
            $slots = [];
            $current_time = $start_time;

            while ($current_time + ($slot_duration * 60) <= $end_time) {
                $slot_start = date('H:i', $current_time + ($buffer_before * 60));
                $slot_end = date('H:i', $current_time + ($buffer_before * 60) + ($duration * 60));

                // Format for display
                $display_time = $slot_start . ' - ' . $slot_end;

                // Check if slot is available (not in busy times)
                $is_available = true;

                foreach ($busy_times as $busy) {
                    $busy_start = strtotime($date . ' ' . $busy['start_hour']);
                    $busy_end = strtotime($date . ' ' . $busy['end_hour']);

                    // Convert slot times to timestamps for this date
                    $slot_start_time = strtotime($date . ' ' . $slot_start);
                    $slot_end_time = strtotime($date . ' ' . $slot_end);

                    // Check for overlap
                    if (
                        ($slot_start_time >= $busy_start && $slot_start_time < $busy_end) || // Slot start during busy
                        ($slot_end_time > $busy_start && $slot_end_time <= $busy_end) || // Slot end during busy
                        ($slot_start_time <= $busy_start && $slot_end_time >= $busy_end) // Slot contains busy
                    ) {
                        $is_available = false;
                        break;
                    }
                }

                // Add slot to array
                $slots[] = [
                    'value' => $slot_start,
                    'text' => $display_time,
                    'end_time' => $slot_end,
                    'available' => $is_available
                ];

                // Move to next slot start time
                $current_time += 30 * 60; // 30 minute intervals
            }

            // Return results
            echo json_encode([
                'success' => true,
                'time_slots' => $slots,
                'date' => $date,
                'service_id' => $service_id,
                'provider_id' => $provider_id,
                'timezone' => $timezone,
                'working_hours' => $working_hours
            ]);
        } catch (Exception $e) {
            log_message('error', 'Error in get_available_time_slots: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => _l('appointly_error_getting_time_slots'),
                'debug' => $e->getMessage()
            ]);
        }

        die();
    }

    /**
     * Get blocked days for the calendar
     *
     * @return json
     */
    public function get_blocked_days()
    {
        // Get blocked days from settings
        $blocked_days = get_option('appointly_blocked_days');
        $blocked_days_array = $blocked_days ? json_decode($blocked_days, true) : [];

        echo json_encode(['success' => true, 'blocked_days' => $blocked_days_array]);
        die();
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Appointments extends AdminController
{
    private bool $staff_no_view_permissions;
    private bool $staff_no_edit_permissions;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('appointly');

        $this->staff_no_view_permissions = ! staff_can('view', 'appointments');
        $this->staff_no_edit_permissions = ! staff_can('edit', 'appointments');

        $this->load->model('appointly/appointly_model', 'apm');
    }

    /**
     * Main view
     *
     * @return void
     */
    public function index()
    {
        if ($this->staff_no_view_permissions) {
            access_denied('Appointments');
        }
        // Get today's appointments
        $data['todays_appointments'] = $this->getTodaysAppointments();

        // Get pending cancellation requests
        $data['pending_cancellations'] = $this->apm->get_pending_cancellations();

        // Get pending reschedule requests
        $data['pending_reschedules'] = $this->apm->get_pending_reschedules();

        $this->load->view('index', $data);
    }

    /**
     * Get today's appointments
     *
     * @return array
     */
    public function getTodaysAppointments()
    {
        return $this->apm->fetch_todays_appointments();
    }

    /**
     * Standalone create appointment page (no modal)
     *
     * @return void
     */
    public function create_page()
    {
        if ($this->staff_no_view_permissions) {
            access_denied('Appointments');
        }

        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $data['contacts']      = appointly_get_staff_customers();
        $data['services']      = $this->apm->get_services();

        // Get URL parameters (used when creating from client/lead profiles)
        $data['client_id'] = $this->input->get('client_id');
        $data['contact_id'] = $this->input->get('contact_id');
        $data['rel_type'] = $this->input->get('rel_type') ?: ($data['client_id'] ? 'internal' : '');
        $data['rel_id'] = $this->input->get('rel_id');

        // Special handling for client_id parameter (client profile tab)
        if ($data['client_id']) {
            $this->load->model('clients_model');
            $data['client_contacts'] = $this->clients_model->get_contacts($data['client_id']);

            // Pre-select "internal" in the rel_type dropdown
            $data['rel_type'] = 'internal';

            // If contact_id is provided, verify it belongs to this client
            if ($data['contact_id']) {
                $contact_belongs_to_client = false;
                foreach ($data['client_contacts'] as $contact) {
                    if ($contact['id'] == $data['contact_id']) {
                        $contact_belongs_to_client = true;
                        break;
                    }
                }

                if (!$contact_belongs_to_client) {
                    // If contact doesn't belong to client, unset it
                    unset($data['contact_id']);
                }
            }
        }
        // Special handling for lead-related appointments
        else if ($data['rel_type'] === 'lead_related' && $data['rel_id']) {
            $this->load->model('leads_model');
            $lead = $this->leads_model->get($data['rel_id']);

            if ($lead) {
                $data['lead_data'] = $lead;
            }
        }

        $data['title'] = _l('appointment_new_appointment');

        $this->load->view('pages/create', $data);
    }

    /**
     * Standalone update appointment page (no modal)
     *
     * @param  int  $appointment_id
     *
     * @return void
     */
    public function update_page($appointment_id)
    {
        if ($this->staff_no_edit_permissions) {
            access_denied('Appointments');
        }

        if (! $appointment_id) {
            redirect(admin_url('appointly/appointments'));
        }

        $data['appointment'] = $this->apm->get_appointment_data($appointment_id);

        if (! $data['appointment']) {
            redirect(admin_url('appointly/appointments'));
        }

        $data['staff_members'] = $this->staff_model->get('', ['active' => 1]);
        $data['contacts']      = appointly_get_staff_customers();
        $data['services']      = $this->apm->get_services();

        // Use the appointment data we already have
        $appointment = $data['appointment'];

        // Get attendees IDs array if not already included
        if (! isset($appointment['attendees'])) {
            $this->load->model('appointly/appointly_attendees_model', 'atm');
            $appointment['attendees'] = $this->atm->attendees($appointment_id);
        }

        // Convert attendees from full objects to just IDs for the form
        if (isset($appointment['attendees']) && is_array($appointment['attendees'])) {
            $attendee_ids = [];
            foreach ($appointment['attendees'] as $attendee) {
                if (is_array($attendee) && isset($attendee['staffid'])) {
                    $attendee_ids[] = $attendee['staffid'];
                } elseif (is_string($attendee) || is_numeric($attendee)) {
                    $attendee_ids[] = $attendee;
                }
            }
            $appointment['attendees'] = $attendee_ids;
        }

        // Special handling for lead-related appointments
        $lead_id = null;
        if ($appointment['source'] == 'lead_related' && isset($appointment['contact_id'])) {
            // For lead-related appointments, we store lead ID in contact_id field
            $lead_id               = $appointment['contact_id'];
            $appointment['rel_id'] = $lead_id;

            // Make sure lead data is loaded
            $this->load->model('leads_model');
            $lead = $this->leads_model->get($lead_id);

            if ($lead) {
                // Set lead data to make sure form fields are populated
                $appointment['lead_data'] = $lead;
            }
        }

        // Pass lead_id to the view
        $data['lead_id'] = $lead_id;

        // Get providers for the selected service
        $data['service_providers'] = [];
        if (! empty($appointment['service_id'])) {
            $this->db->select('s.staffid, s.firstname, s.lastname, ss.is_primary');
            $this->db->from(db_prefix() . 'staff s');
            $this->db->join(
                db_prefix() . 'appointly_service_staff ss',
                's.staffid = ss.staff_id',
                'inner'
            );
            $this->db->where('ss.service_id', $appointment['service_id']);
            $this->db->where('s.active', 1);
            $providers = $this->db->get()->result_array();

            // Format providers for consistent rendering
            foreach ($providers as $provider) {
                $data['service_providers'][] = [
                    'staffid'    => $provider['staffid'],
                    'firstname'  => $provider['firstname'],
                    'lastname'   => $provider['lastname'],
                    'is_primary' => $provider['is_primary'],
                ];
            }
        }

        // Get full attendee details if needed
        $this->load->model('appointly/appointly_attendees_model', 'atm');
        $attendees = $this->atm->get($appointment_id);

        // Set the appointment data
        $data['appointment'] = $appointment;

        // Get responsible person (creator) if available
        if (! empty($data['appointment']['created_by'])) {
            $data['appointment_organizer'] = $this->staff_model->get($data['appointment']['created_by']);
        } else {
            $data['appointment_organizer'] = null;
        }

        // Who gets mails
        $data['user_email_notifications'] = isset($data['appointment']['email_notification']) && $data['appointment']['email_notification'] ? explode(
            ',',
            $data['appointment']['email_notification']
        ) : [];
        $data['google_calendar_link']     = $data['appointment']['google_calendar_link'] ?? '';

        $data['title'] = _l('appointment_edit_appointment');

        $data['appointment'] = $appointment;
        $data['history']     = array_merge($appointment, [
            'selected_staff' => $appointment['attendees'],
            'attendees'      => $attendees,
            'created_by'     => $appointment['created_by'],
        ]);

        $this->load->view('pages/update', $data);
    }

    /**
     * Single appointment view
     *
     * @return void
     */
    public function view($id = null)
    {
        if ($this->staff_no_view_permissions) {
            access_denied('Appointments');
        }

        $this->session->unset_userdata('from_view_id');

        $appointment_id = $this->input->get('appointment_id');
        $check_outlook  = $this->input->get('check_outlook');
        $appointment = $this->apm->get_appointment_data($appointment_id);

        // Redirect to appointments page if appointment not found
        if (!$appointment) {
            redirect(admin_url('appointly/appointments'));
        }

        // If this is just a check for Outlook integration, return JSON
        if ($check_outlook) {

            if ($appointment) {
                header('Content-Type: application/json');
                echo json_encode([
                    'outlook_event_id'    => $appointment['outlook_event_id'] ?? null,
                    'outlook_added_by_id' => $appointment['outlook_added_by_id'] ?? null,
                ]);

                return;
            }
        }

        // Regular view logic continues...
        $attendees             = $this->atm->attendees($appointment_id);
        $google_meet_attendees = [];

        // Get staff emails for attendees
        if (! empty($attendees) && is_array($attendees)) {
            foreach ($attendees as $staff_id) {
                $staff = $this->staff_model->get($staff_id);

                if ($staff && is_staff_logged_in()) {
                    // if logged in user email is same as staff email then skip
                    if ($staff->email == appointly_get_staff(get_staff_user_id())['email']) {
                        continue;
                    }
                    $google_meet_attendees[] = $staff->email;
                }
            }
        }
        /**
         * If user is assigned to a appointment but have no permissions at all eg. edit or view
         * User will be able to open the url send to mail (But only to view this specific meeting or meetings that the user is assigned to)
         */
        if (! in_array((int)get_staff_user_id(), array_map('intval', $attendees), true)) {
            // Global view permissions required
            if (! staff_can('view', 'appointments')) {
                access_denied('Appointments');
            }
        }

        $data['appointment'] = fetch_appointment_data($appointment_id);

        if ($data['appointment']) {
            // Original URL format
            $data['appointment']['public_url'] = appointly_get_appointment_url($data['appointment']['hash']);

            $data['appointment']['formatted_date'] = _d($data['appointment']['date']);

            if ($data['appointment']['source'] !== 'internal_staff') {
                // For service-based appointments, use service duration
                if (! empty($data['appointment']['service_id'])) {
                    if (empty($data['appointment']['start_hour']) && ! empty($data['appointment']['end_hour'])) {
                        // Calculate start_hour based on end_hour and service duration
                        $service_duration = ! empty($data['appointment']['service_duration'])
                            ? $data['appointment']['service_duration']
                            : 0;

                        if ($service_duration > 0) {
                            $end_time                          = strtotime($data['appointment']['end_hour']);
                            $start_time                        = $end_time - ($service_duration * 60);
                            $data['appointment']['start_hour'] = date('H:i', $start_time);

                            // Update the database for future requests
                            $this->db->where('id', $appointment_id);
                            $this->db->update(db_prefix() . 'appointly_appointments', [
                                'start_hour' => $data['appointment']['start_hour'],
                            ]);
                        }
                    } elseif (! empty($data['appointment']['start_hour']) && empty($data['appointment']['end_hour'])) {
                        // Calculate end_hour based on start_hour and service duration
                        $service_duration = ! empty($data['appointment']['service_duration'])
                            ? $data['appointment']['service_duration']
                            : 0;

                        if ($service_duration > 0) {
                            $start_time                      = strtotime($data['appointment']['start_hour']);
                            $end_time                        = $start_time + ($service_duration * 60);
                            $data['appointment']['end_hour'] = date('H:i', $end_time);

                            // Update the database for future requests
                            $this->db->where('id', $appointment_id);
                            $this->db->update(db_prefix() . 'appointly_appointments', [
                                'end_hour' => $data['appointment']['end_hour'],
                            ]);
                        }
                    }
                }
            } else {
                // For staff-only appointments, use the provided duration
                if (! empty($data['appointment']['duration'])) {
                    if (empty($data['appointment']['start_hour']) && ! empty($data['appointment']['end_hour'])) {
                        // Calculate start_hour based on end_hour and duration
                        $duration                          = $data['appointment']['duration'];
                        $end_time                          = strtotime($data['appointment']['end_hour']);
                        $start_time                        = $end_time - ($duration * 60);
                        $data['appointment']['start_hour'] = date('H:i', $start_time);

                        // Update the database for future requests
                        $this->db->where('id', $appointment_id);
                        $this->db->update(db_prefix() . 'appointly_appointments', [
                            'start_hour' => $data['appointment']['start_hour'],
                        ]);
                    } elseif (! empty($data['appointment']['start_hour']) && empty($data['appointment']['end_hour'])) {
                        // Calculate end_hour based on start_hour and duration
                        $duration                        = $data['appointment']['duration'];
                        $start_time                      = strtotime($data['appointment']['start_hour']);
                        $end_time                        = $start_time + ($duration * 60);
                        $data['appointment']['end_hour'] = date('H:i', $end_time);

                        // Update the database for future requests
                        $this->db->where('id', $appointment_id);
                        $this->db->update(db_prefix() . 'appointly_appointments', [
                            'end_hour' => $data['appointment']['end_hour'],
                        ]);
                    }
                }
            }

            // format the time with correct start and end hours
            $data['appointment']['formatted_time'] = date('H:i A', strtotime($data['appointment']['start_hour'])) .
                ($data['appointment']['end_hour'] ? ' - ' . date('H:i A', strtotime($data['appointment']['end_hour'])) : '');

            // Get client details if internal appointment
            if ($data['appointment']['source'] == 'internal' && $data['appointment']['contact_id']) {
                $this->load->model('clients_model');
                $client = $this->clients_model->get_contact($data['appointment']['contact_id']);
                if ($client) {
                    $client_info                    = $this->clients_model->get($client->userid);
                    $data['appointment']['details'] = [
                        'userid'    => $client->userid,
                        'company'   => $client_info ? $client_info->company : '',
                        'full_name' => $client->firstname . ' ' . $client->lastname,
                        'email'     => $client->email,
                        'phone'     => $client->phonenumber,
                        'address'   => $client_info ? $client_info->address : '',
                        'city'      => $client_info ? $client_info->city : '',
                        'state'     => $client_info ? $client_info->state : '',
                        'zip'       => $client_info ? $client_info->zip : '',
                        'country'   => $client_info ? $client_info->country : '',
                        'full_address' => $this->format_full_address($client_info),
                    ];
                }
            }

            // Get lead details if lead-related appointment
            if ($data['appointment']['source'] == 'lead_related' && $data['appointment']['contact_id']) {
                $this->load->model('leads_model');
                $lead = $this->leads_model->get($data['appointment']['contact_id']);
                if ($lead) {
                    $data['appointment']['details'] = [
                        'userid'    => null,
                        'company'   => $lead->company,
                        'full_name' => $lead->name,
                        'email'     => $lead->email,
                        'phone'     => $lead->phonenumber,
                        'address'   => $lead->address,
                        'city'      => $lead->city,
                        'state'     => $lead->state,
                        'zip'       => $lead->zip,
                        'country'   => $lead->country,
                        'full_address' => $this->format_full_address($lead),
                    ];
                }
            }

            // Get service details
            if (isset($data['appointment']['service_id'])) {
                $data['appointment']['service'] = $this->apm->get_service($data['appointment']['service_id']);
            }

            $data['appointment']['attendees'] = $attendees;
            $data['google_meet_attendees']    = $google_meet_attendees;

            // Get email tracking
            $data['appointment']['email_tracking'] = get_tracked_emails($appointment_id, 'appointment');

            //  provider details
            if ($data['appointment']['provider_id']) {
                $provider = $this->staff_model->get($data['appointment']['provider_id']);
                if ($provider) {
                    $data['appointment']['provider'] = [
                        'staffid'       => $provider->staffid,
                        'full_name'     => $provider->firstname . ' ' . $provider->lastname,
                        'email'         => $provider->email,
                        'profile_image' => staff_profile_image_url($provider->staffid),
                    ];
                }
            }
        } else {
            appointly_redirect_after_event('warning', _l('appointment_not_exists'));
        }

        if (! $data['appointment']) {
            show_404();
        }
        $data['google_api_key'] = get_option('google_api_key');
        //dd($data);
        $this->load->view('tables/appointment', $data);
    }

    /**
     * Handle appointment update
     *
     * @return void
     */
    public function update()
    {
        if (! staff_can('edit', 'appointments')) {
            access_denied('Appointments');
        }

        header('Content-Type: application/json');

        // Get all POST data
        $updateData = $this->input->post();

        // Special handling for lead-related appointments
        if ($updateData['rel_type'] === 'lead_related') {
            // For lead-related appointments, we need to set source and handle the lead ID
            $updateData['source'] = 'lead_related';

            if (! empty($updateData['rel_id'])) {
                // Store lead ID in contact_id field
                $updateData['contact_id'] = $updateData['rel_id'];

                log_message('debug', 'Setting contact_id for lead-related appointment: ' . $updateData['contact_id']);

                // Load lead data to enhance the appointment
                $this->load->model('leads_model');
                $lead = $this->leads_model->get($updateData['rel_id']);
                if ($lead) {
                    // Set lead fields
                    $updateData['name']  = $lead->name;
                    $updateData['email'] = $lead->email;
                    $updateData['phone'] = $lead->phonenumber;

                    log_message('debug', 'Lead data loaded: ' . $lead->name);
                }
            }
        } elseif ($updateData['rel_type'] === 'internal') {
            // For internal appointments, ensure we have contact_id
            $updateData['source'] = 'internal';
        } elseif ($updateData['rel_type'] === 'external') {
            // For external appointments, ensure name and email are provided
            $updateData['source']     = 'external';
            $updateData['contact_id'] = null;
        } elseif ($updateData['rel_type'] === 'internal_staff') {
            // For staff-only appointments, clear client-related fields
            $updateData['source']     = 'internal_staff';
            $updateData['contact_id'] = null;
            $updateData['name']       = '';
            $updateData['email']      = '';
            $updateData['phone']      = '';
        }

        // Attempt to update the appointment
        $success = $this->apm->update_appointment($updateData);

        if ($success) {
            echo json_encode([
                'success' => true,
                'result'  => true,
                'message' => _l('appointment_updated'),
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'result'  => false,
                'message' => _l('appointment_update_failed'),
            ]);
        }
    }

    /**
     * Render table view
     *
     * @return void
     */
    public function table()
    {
        if ($this->staff_no_view_permissions) {
            access_denied('Appointments');
        }

        // Fix the status filter by explicitly setting the fully qualified table.column
        $status_filters = [
            'status_pending',
            'status_in-progress',
            'status_completed',
            'status_cancelled',
            'status_no-show'
        ];

        foreach ($status_filters as $filter) {
            if ($this->input->post($filter)) {
                // Extract the status value (remove 'status_' prefix)
                $status_value = str_replace('status_', '', $filter);

                // Explicitly qualify the status column with the table name
                $_POST[$filter] = db_prefix() . 'appointly_appointments.status = \'' . $status_value . '\'';
            }
        }

        // Get regular appointments first
        // here if google is not connected we have error when trying to filter by google calendar
        $this->app->get_table_data(module_views_path('appointly', 'tables/index'));

        // Check if Google sync is enabled and debug
        if (get_option('appointments_googlesync_show_in_table') == 1 && appointlyGoogleAuth()) {
            $this->load->model('googlecalendar');
            $google_events = $this->googlecalendar->getEvents();
            if ($google_events) {
                $data = [];
                foreach ($google_events as $event) {
                    $data[] = [
                        'id'                   => $event->id,
                        'subject'              => $event->summary,
                        'description'          => $event->description ?? '',
                        'date'                 => $event->start->dateTime,
                        'source'               => 'google_calendar',
                        'google_event_id'      => $event->id,
                        'google_calendar_link' => $event->htmlLink,
                    ];
                }

                echo json_encode(['data' => $data]);
            }
        }
    }

    /**
     * Get contact data
     *
     * @return void
     */
    public function fetch_contact_data()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $id      = $this->input->post('contact_id');
        $is_lead = $this->input->post('lead');

        log_message('info', 'Appointment fetch_contact_data called - ID: ' . $id . ', is_lead: ' . $is_lead);

        if (! $id) {
            header('Content-Type: application/json');
            echo json_encode([]);

            return;
        }

        if ($is_lead) {
            // Get lead data
            $this->load->model('leads_model');
            $lead = $this->leads_model->get($id);

            if ($lead) {
                // Format lead data to match expected structure
                $formatted_data = [
                    'name'        => $lead->name,
                    'email'       => $lead->email,
                    'phonenumber' => $lead->phonenumber,
                    'address'     => $lead->address,
                ];

                header('Content-Type: application/json');
                echo json_encode($formatted_data);

                return;
            }
        } else {
            // Get contact data
            $this->load->model('clients_model');
            $contact = $this->clients_model->get_contact($id);

            if ($contact) {
                // Get client/company data for additional details
                $client = $this->clients_model->get($contact->userid);

                // Format contact data
                $formatted_data = [
                    'firstname'   => $contact->firstname,
                    'lastname'    => $contact->lastname,
                    'email'       => $contact->email,
                    'phonenumber' => $contact->phonenumber,
                    'address'     => $client ? $client->address : '',
                ];

                header('Content-Type: application/json');
                echo json_encode($formatted_data);

                return;
            }
        }

        // Return empty result if no data found
        header('Content-Type: application/json');
        echo json_encode([]);
    }

    /**
     * Create appointment
     *
     * @return void
     */
    public function create()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!staff_can('create', 'appointments')) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied')
            ]);
            return;
        }

        $data = $this->input->post();

        header('Content-Type: application/json');

        $appointment_id = $this->apm->create_appointment($data);

        if ($appointment_id) {
            $response = [
                'success' => true,
                'message' => _l('appointment_created'),
                'appointment_id' => $appointment_id
            ];

            // Check if Outlook integration was requested
            $outlook_integration_data = $this->session->userdata('appointly_outlook_integration');
            if ($outlook_integration_data && $outlook_integration_data['appointment_id'] == $appointment_id) {
                $response['outlook_integration'] = $outlook_integration_data;
                // Clear the session data after use
                $this->session->unset_userdata('appointly_outlook_integration');
            }

            echo json_encode($response);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_could_not_be_created')
            ]);
        }
    }

    /**
     * Delete Google synced appointment
     *
     * @param  string  $google_event_id  Google event ID
     *
     * @return void
     */
    public function deleteGoogleSyncedAppointment($google_event_id)
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        // Load Google Calendar model
        $this->load->model('appointly/googlecalendar');

        // Check if user is authenticated with Google
        if (! appointlyGoogleAuth()) {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_google_not_authenticated'),
            ]);

            return;
        }

        // Try to delete the event from Google Calendar
        $success = $this->googlecalendar->deleteEvent($google_event_id);
        // Get the appointment ID from the Google event ID
        $appointment = $this->db->get_where(db_prefix() . 'appointly_appointments', ['google_event_id' => $google_event_id])->row();

        if ($appointment) {
            // Direct SQL update to clear Google-related fields
            $this->db->query("
                UPDATE " . db_prefix() . "appointly_appointments
                SET google_event_id = NULL,
                    google_calendar_link = NULL,
                    google_meet_link = NULL,
                    google_added_by_id = NULL
                WHERE google_event_id = ?
            ", [$google_event_id]);
        }

        if ($success) {
            // Also remove from the local cache/array of Google events
            // This is important to ensure it doesn't show in the table anymore
            $this->session->set_userdata(
                'appointly_google_events_deleted',
                array_merge($this->session->userdata('appointly_google_events_deleted') ?: [], [$google_event_id])
            );

            echo json_encode([
                'success' => true,
                'message' => _l('appointment_deleted'),
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_delete_failed'),
            ]);
        }
    }

    /**
     * Approve new appointment
     *
     * @return void
     */
    public function approve()
    {
        if (! staff_can('approve', 'appointments')) {
            access_denied('Appointments');
        }

        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'result' => $this->apm->approve_appointment($this->input->post('appointment_id')),
            ]);

            return;
        }

        if ($this->apm->approve_appointment($this->input->get('appointment_id'))) {
            appointly_redirect_after_event('success', _l('appointment_appointment_approved'));
        }
    }

    /**
     * Mark appointment as finished
     *
     * @return bool
     */
    public function finished()
    {
        return $this->apm->mark_as_finished($this->input->post('id'));
    }

    /**
     * Mark appointment as ongoing
     *
     * @return void
     */
    public function mark_as_ongoing_appointment()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');

        $success = $this->apm->mark_as_ongoing($id);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => (bool) $success,
            'message' => $success ? _l('appointment_marked_as_ongoing') : _l('appointment_status_change_failed'),
        ]);
    }

    /**
     * Mark appointment as cancelled
     *
     * @return void|boolean
     */
    public function cancel_appointment()
    {
        return $this->apm->cancel_appointment($this->input->post('id'));
    }

    /**
     * Mark appointment as no-show
     *
     * @return void
     */
    public function mark_as_no_show()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');

        $success = $this->apm->mark_as_no_show($id);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => (bool) $success,
            'message' => $success ? _l('appointment_marked_as_no_show') : _l('appointment_status_change_failed'),
        ]);
    }

    /**
     * Send appointment early reminders
     *
     * @return void
     */
    public function send_appointment_early_reminders()
    {
        $result = ['success' => false];

        if ($this->apm->send_appointment_early_reminders($this->input->post('id'))) {
            $result['success'] = true;
        }

        echo json_encode($result);
    }

    /**
     * Add event to google calendar
     *
     * @return void
     */
    public function addEventToGoogleCalendar()
    {
        if (! staff_can('edit', 'appointments') || ! $this->input->is_ajax_request()) {
            access_denied('Appointments');
        }

        $data = $this->input->post();

        if (! empty($data)) {
            header('Content-Type: application/json');
            $result = $this->apm->add_event_to_google_calendar($data);
            if ($result) {
                echo json_encode($result);
            }
        }
    }

    /**
     * Request new appointment feedback
     *
     * @return void
     */
    public function requestAppointmentFeedback()
    {
        $id = $this->input->post('appointment_id');

        if (! empty($id)) {
            header('Content-Type: application/json');
            $result = $this->apm->request_appointment_feedback($id);
            echo $result;
        }
    }

    /**
     * Delete Outlook event from appointment
     *
     * @return void
     */
    public function deleteOutlookEvent()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $appointment_id = $this->input->post('appointment_id');
        $outlook_event_id = $this->input->post('outlook_event_id');

        if (empty($appointment_id) || empty($outlook_event_id)) {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_missing_required_fields'),
            ]);
            return;
        }

        // Get the appointment to verify it exists
        $appointment = $this->db->get_where(db_prefix() . 'appointly_appointments', ['id' => $appointment_id])->row();

        if (!$appointment) {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_not_found'),
            ]);
            return;
        }

        // Clear Outlook-related fields from the appointment
        $this->db->where('id', $appointment_id);
        $success = $this->db->update(db_prefix() . 'appointly_appointments', [
            'outlook_event_id' => null,
            'outlook_calendar_link' => null,
        ]);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => _l('appointment_outlook_integration_removed'),
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_outlook_removal_failed'),
            ]);
        }
    }

    /**
     * Delete Google event from appointment
     *
     * @return void
     */
    public function deleteGoogleEvent()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $appointment_id = $this->input->post('appointment_id');
        $google_event_id = $this->input->post('google_event_id');

        if (empty($appointment_id) || empty($google_event_id)) {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_missing_required_fields'),
            ]);
            return;
        }

        // Get the appointment to verify it exists
        $appointment = $this->db->get_where(db_prefix() . 'appointly_appointments', ['id' => $appointment_id])->row();

        if (!$appointment) {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_not_found'),
            ]);
            return;
        }

        // Load Google Calendar model for deletion
        $this->load->model('appointly/googlecalendar');

        // Try to delete from Google Calendar if authenticated
        $google_deleted = false;
        if (appointlyGoogleAuth()) {
            $google_deleted = $this->googlecalendar->deleteEvent($google_event_id);
        }

        // Clear Google-related fields from the appointment regardless of Google API result
        $this->db->where('id', $appointment_id);
        $success = $this->db->update(db_prefix() . 'appointly_appointments', [
            'google_event_id' => null,
            'google_calendar_link' => null,
            'google_meet_link' => null,
            'google_added_by_id' => null,
        ]);

        if ($success) {
            $message = $google_deleted
                ? _l('appointment_google_integration_removed_and_deleted')
                : _l('appointment_google_integration_removed');

            echo json_encode([
                'success' => true,
                'message' => $message,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_google_removal_failed'),
            ]);
        }
    }

    /**
     * Get attendee details
     *
     * @return void
     */
    public function getAttendeeData()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        if ($this->input->post('ids')) {
            header('Content-Type: application/json');
            echo json_encode($this->atm->details($this->input->post('ids')));
        }
    }

    /**
     * Get contact email details
     *
     * @return void
     */
    public function getContactEmail()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $contact_id = $this->input->post('contact_id');

        if ($contact_id) {
            // Get contact details from database
            $this->db->select('email, firstname, lastname');
            $this->db->from(db_prefix() . 'contacts');
            $this->db->where('id', $contact_id);
            $contact = $this->db->get()->row_array();

            if ($contact) {
                $response = [
                    'email' => $contact['email'],
                    'name' => trim($contact['firstname'] . ' ' . $contact['lastname'])
                ];
            } else {
                $response = [
                    'error' => 'Contact not found'
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No contact ID provided']);
        }
    }

    /**
     * Add new outlook event to calendar
     *
     * @return void
     */
    public function newOutlookEvent()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $data = $this->input->post();

        if (! empty($data)) {
            header('Content-Type: application/json');
            echo json_encode(['result' => $this->apm->insert_new_outlook_event($data)]);
        }
    }

    /**
     * Add new outlook event to calendar from existing appointment
     *
     * @return void
     */
    public function update_outlook_event()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $data = $this->input->post();

        if (! empty($data)) {
            header('Content-Type: application/json');
            echo json_encode(['result' => $this->apm->update_outlook_event($data)]);
        }
    }

    /**
     * Send custom email to request meet via Google Meet
     *
     * @return void
     */
    public function send_appointment_custom_email()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $data = $this->input->post();

        if (! empty($data)) {
            $result = $this->apm->send_google_meet_request_email($data);

            // Handle both old boolean response and new array response for backward compatibility
            if (is_array($result)) {
                header('Content-Type: application/json');
                echo json_encode($result);
            } else {
                // Legacy boolean response
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? _l('appointment_email_sent_success') : _l('appointment_email_sent_failed')
                ]);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'No data provided in sending custom email'
            ]);
        }
    }


    /**
     * Delete appointment
     *
     * @param  string|int  $id  appointment ID or Google event ID
     *
     * @return void
     */
    public function delete($id)
    {
        if (! staff_can('delete', 'appointments')) {
            access_denied('Appointments');
        }

        if ($this->input->is_ajax_request() && $id) {
            // Use the model to handle all deletion logic
            $result = $this->apm->delete_appointment($id);

            // Return the result from the model
            echo json_encode($result);
            die;
        }
    }

    /**
     * AJAX endpoint for saving blocked days
     *
     * @return void
     */
    public function save_blocked_days_ajax()
    {
        // Check for AJAX request
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        // Check permissions
        if (! is_admin()) {
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);

            return;
        }

        // Log the entire POST data for debugging
        $post_data = $this->input->post();

        // Get blocked days from POST
        $blocked_days = $this->input->post('blocked_days');

        if ($blocked_days) {
            // Validate JSON format
            $decoded = json_decode($blocked_days, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // Update option in database
                update_option('appointly_blocked_days', $blocked_days);

                echo json_encode([
                    'success' => true,
                    'message' => _l('settings_updated'),
                    'data'    => $decoded,
                ]);
            } else {
                echo json_encode([
                    'success'       => false,
                    'message'       => 'Invalid JSON format: ' . json_last_error_msg(),
                    'received_data' => $blocked_days,
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No data provided',
            ]);
        }
    }


    /**
     * Get available time slots for a specific date
     */
    public function get_available_time_slots()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $date           = $this->input->post('date');
        $provider_id    = $this->input->post('provider_id');
        $service_id     = $this->input->post('service_id');
        $appointment_id = $this->input->post('appointment_id'); // For excluding current appointment when editing
        $timezone       = $this->input->post('timezone') ?? get_option('default_timezone');
        $rel_type       = $this->input->post('rel_type'); // Get appointment type

        // Ensure date is in the correct format (YYYY-MM-DD)
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $timestamp = strtotime($date);
            if ($timestamp) {
                $date = date('Y-m-d', $timestamp);
                //log_message('debug', "Appointly: Converted date format from '{$this->input->post('date')}' to '{$date}'");
            } else {
                // If strtotime fails, try to detect the format using common patterns
                $formats = [
                    'm.d.Y' => '/^\d{1,2}\.\d{1,2}\.\d{4}$/',
                    'd.m.Y' => '/^\d{1,2}\.\d{1,2}\.\d{4}$/',
                    'm/d/Y' => '/^\d{1,2}\/\d{1,2}\/\d{4}$/',
                    'd/m/Y' => '/^\d{1,2}\/\d{1,2}\/\d{4}$/',
                    'd-m-Y' => '/^\d{1,2}-\d{1,2}-\d{4}$/',
                    'm-d-Y' => '/^\d{1,2}-\d{1,2}-\d{4}$/',
                ];

                foreach ($formats as $format => $pattern) {
                    if (preg_match($pattern, $date)) {
                        $date_obj = DateTime::createFromFormat($format, $date);
                        if ($date_obj) {
                            $date = $date_obj->format('Y-m-d');
                            log_message('debug', "Appointly: Converted date from format {$format}: '{$this->input->post('date')}' to '{$date}'");
                            break;
                        }
                    }
                }

                // If still not in correct format, set to today
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $date = date('Y-m-d');
                }
            }
        }

        // Get service details for duration
        $service  = $this->apm->get_service($service_id);
        $duration = $service ? $service->duration : 60;

        // For staff-only appointments, use special handling
        $is_internal_staff = ($rel_type === 'internal_staff');
        $staff_respect_availability = get_option('appointly_staff_respect_availability') == 1;

        // Get availability restrictions - not needed for staff appointments (they use simple 30-min slots)
        $available_slots = [];
        if (!$is_internal_staff) {
            $available_slots = $this->apm->get_available_time_slots($provider_id, $date, $service_id, $timezone, $appointment_id);
        }

        // Get booked/busy time slots - needed for staff appointments only when availability setting is enabled
        $busy_slots = [];
        if (!$is_internal_staff || ($is_internal_staff && $staff_respect_availability)) {
            $busy_slots = $this->apm->get_busy_times_by_staff($provider_id, $date, $appointment_id);
        }

        // Time format based on app settings
        $time_format = get_option('time_format') == 24 ? 'H:i' : 'g:i A';

        // Get the current appointment time if we're editing
        $current_appointment = null;
        if ($appointment_id) {
            $current_appointment = $this->db->where('id', $appointment_id)
                ->get(db_prefix() . 'appointly_appointments')
                ->row_array();
        }

        $time_slots = [];

        // Handle internal staff appointments differently - they have more flexible time slots
        if ($is_internal_staff) {
            // For internal staff meetings, generate time slots at 30-minute intervals
            // Staff appointments always use this simple approach (no services needed)
            $start_time = strtotime('08:00');
            $end_time   = strtotime('17:00');

            while ($start_time < $end_time) {
                $slot_start = date('H:i', $start_time);
                $slot_end   = date('H:i', strtotime('+30 minutes', $start_time));

                $is_current = false;
                if (
                    $current_appointment && ! empty($current_appointment['start_hour'])
                    && $slot_start == date('H:i', strtotime($current_appointment['start_hour']))
                ) {
                    $is_current = true;
                }

                // Format display text
                $start_formatted = date($time_format, strtotime($slot_start));
                $end_formatted   = date($time_format, strtotime($slot_end));
                $display_text    = $start_formatted . ' - ' . $end_formatted;

                if ($is_current) {
                    $display_text .= ' (current)';
                }

                // Check for conflicts only if staff respect availability is enabled
                $is_booked          = false;
                $unavailable_reason = '';

                if ($staff_respect_availability) {
                    // When enabled, check for busy times (conflicts with other appointments)
                    foreach ($busy_slots as $busy) {
                        // Skip current appointment
                        if ($appointment_id && $busy['id'] == $appointment_id) {
                            continue;
                        }

                        $busy_start = strtotime($busy['start_hour']);
                        $busy_end   = strtotime($busy['end_hour']);

                        // Apply buffer times from the existing appointment if they exist
                        if (isset($busy['buffer_before']) && $busy['buffer_before']) {
                            $busy_start -= ($busy['buffer_before'] * 60);
                        }
                        if (isset($busy['buffer_after']) && $busy['buffer_after']) {
                            $busy_end += ($busy['buffer_after'] * 60);
                        }

                        $slot_start_time = strtotime($slot_start);
                        $slot_end_time   = strtotime($slot_end);

                        // Check if there's an overlap (staff appointments have no buffer themselves)
                        if (
                            ($slot_start_time >= $busy_start && $slot_start_time < $busy_end) ||
                            ($slot_end_time > $busy_start && $slot_end_time <= $busy_end) ||
                            ($slot_start_time <= $busy_start && $slot_end_time >= $busy_end)
                        ) {
                            $is_booked = true;

                            // Get service name if available
                            $service_name = '';
                            if (! empty($busy['service_id'])) {
                                $busy_service = $this->apm->get_service($busy['service_id']);
                                if ($busy_service) {
                                    $service_name = $busy_service->name;
                                }
                            }

                            $unavailable_reason = _l('appointment_slot_already_booked');
                            if (! empty($service_name)) {
                                $unavailable_reason .= ' - ' . $service_name;
                            }

                            break;
                        }
                    }
                }

                // Add slot to list
                $time_slots[] = [
                    'value'              => $slot_start,
                    'text'               => $display_text,
                    'end_time'           => $slot_end,
                    'available'          => ! $is_booked,
                    'is_current'         => $is_current,
                    'unavailable_reason' => $unavailable_reason,
                ];

                // Move to next time slot (30 minutes)
                $start_time = strtotime('+30 minutes', $start_time);
            }
        } else {
            // Regular appointment handling for service-based appointments

            // First add all available slots
            foreach ($available_slots as $slot) {
                $is_current = false;

                // If editing, check if this is the current appointment's time
                if (
                    $current_appointment && ! empty($current_appointment['start_hour'])
                    && $slot['start'] == $current_appointment['start_hour']
                ) {
                    $is_current = true;
                }

                // Format the start and end times for display
                $start_formatted = date($time_format, strtotime($slot['start']));
                $end_formatted   = date($time_format, strtotime($slot['end']));

                $display_text = $start_formatted . ' - ' . $end_formatted;

                // Add "current" label if this is the current appointment's time
                if ($is_current) {
                    $display_text .= ' (current)';
                }

                $time_slots[] = [
                    'value'      => $slot['start'],
                    'text'       => $display_text,
                    'end_time'   => $slot['end'],
                    'available'  => true,
                    'is_current' => $is_current,
                ];
            }

            // Now add all booked/busy slots - these will be marked as unavailable in the dropdown
            foreach ($busy_slots as $busy) {
                // Skip if this is the current appointment being edited
                if ($appointment_id && $busy['id'] == $appointment_id) {
                    continue;
                }

                $start_hour = $busy['start_hour'];
                $end_hour   = $busy['end_hour'];

                // Format for display
                $start_formatted = date($time_format, strtotime($start_hour));
                $end_formatted   = date($time_format, strtotime($end_hour));
                $display_text    = $start_formatted . ' - ' . $end_formatted;

                // Get service name if available
                $service_name = '';
                if (! empty($busy['service_id'])) {
                    $busy_service = $this->apm->get_service($busy['service_id']);
                    if ($busy_service) {
                        $service_name = $busy_service->name;
                    }
                }

                // Add a reason for unavailability
                $unavailable_reason = _l('appointment_slot_already_booked');
                if (! empty($service_name)) {
                    $unavailable_reason .= ' - ' . $service_name;
                }

                // Add to time slots as unavailable
                $time_slots[] = [
                    'value'              => $start_hour,
                    'text'               => $display_text,
                    'end_time'           => $end_hour,
                    'available'          => false,
                    'unavailable_reason' => $unavailable_reason,
                ];
            }
        }

        // Sort time slots by start time
        usort($time_slots, function ($a, $b) {
            return strtotime($a['value']) - strtotime($b['value']);
        });

        echo json_encode([
            'success'          => true,
            'time_slots'       => $time_slots,
            'service_duration' => $duration,
        ]);
    }

    public function save_notes_from_appointment_view()
    {
        if (! $this->input->post()) {
            redirect(admin_url('appointly/appointments'));
        }

        $appointment_id = $this->input->post('appointment_id');
        $notes          = $this->input->post('notes');

        echo $this->apm->update_appointment_private_notes($appointment_id, $notes);
    }

    public function save_appointment_description()
    {
        if (! staff_can('edit', 'appointments')) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            $data = [
                'description'    => $this->input->post('description'),
                'appointment_id' => $this->input->post('appointment_id'),
            ];

            $success = $this->apm->save_appointment_description($data['appointment_id'], $data['description']);

            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => _l('appointment_description_updated'),
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => _l('appointment_description_update_failed'),
                ]);
            }
        }
    }

    public function save_outlook_event_id()
    {
        $appointment_id        = $this->input->post('appointment_id');
        $outlook_event_id      = $this->input->post('outlook_event_id');
        $outlook_calendar_link = $this->input->post('outlook_calendar_link');

        $success = $this->apm->save_outlook_event_id(
            (int) $appointment_id,
            $outlook_event_id,
            $outlook_calendar_link,
            get_staff_user_id()
        );

        echo json_encode([
            'success' => $success,
            'message' => $success ? _l('appointment_outlook_event_saved') : _l('appointment_outlook_event_save_failed'),
        ]);
    }

    /**
     * Update integration data for appointment (Outlook)
     *
     * @return void
     */
    public function update_integration_data()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $appointment_id = $this->input->post('appointment_id');
        $integration_type = $this->input->post('integration_type'); // 'outlook'

        $success = false;
        $message = '';

        if ($integration_type === 'outlook') {
            $outlook_event_id = $this->input->post('outlook_event_id');
            $outlook_calendar_link = $this->input->post('outlook_calendar_link');

            $success = $this->apm->save_outlook_event_id(
                (int) $appointment_id,
                $outlook_event_id,
                $outlook_calendar_link,
                get_staff_user_id()
            );

            $message = $success ? _l('appointment_outlook_event_saved') : _l('appointment_outlook_event_save_failed');
        }

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Get appointment data for invoice
     *
     * This endpoint provides appointment data for the invoice creation form
     *
     * @return void
     */
    public function get_appointment_data_for_invoice()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $appointment_id = $this->input->post('appointment_id');
        $appointment = $this->apm->get_appointment_data($appointment_id);

        // Get client ID if it's an internal appointment with a contact
        if ($appointment['source'] == 'internal' && ! empty($appointment['contact_id'])) {
            $this->load->model('clients_model');
            $contact = $this->clients_model->get_contact($appointment['contact_id']);
            if ($contact) {
                // Contact's userid field refers to the client/customer ID (company)
                $appointment['client_id'] = $contact->userid;
            }
        }

        // Get service details if available
        if (! empty($appointment['service_id'])) {
            $appointment['service'] = $this->apm->get_service($appointment['service_id']);
        }

        echo json_encode([
            'success' => true,
            'data'    => $appointment,
        ]);
    }

    /**
     * Get appointment data for tooltips
     * Returns rich appointment information for calendar tooltips
     */
    public function get_appointment_data()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $appointment_id = $this->input->post('appointment_id');

        // Get appointment with service and provider details
        $this->db->select('a.*, s.name as service_name, s.color as service_color, s.duration as service_duration, st.firstname as provider_firstname, st.lastname as provider_lastname');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        $this->db->join(db_prefix() . 'appointly_services s', 's.id = a.service_id', 'left');
        $this->db->join(db_prefix() . 'staff st', 'st.staffid = a.provider_id', 'left');
        $this->db->where('a.id', $appointment_id);

        $appointment = $this->db->get()->row_array();

        // Check permissions
        if (! is_admin() && $appointment['created_by'] != get_staff_user_id()) {
            // Check if user is an attendee
            $this->db->where('appointment_id', $appointment_id);
            $this->db->where('staff_id', get_staff_user_id());
            $attendee = $this->db->get(db_prefix() . 'appointly_attendees')->row();

            if (! $attendee) {
                access_denied();
            }
        }

        echo json_encode([
            'success' => true,
            'data'    => $appointment,
        ]);
    }

    /**
     * Convert appointment to invoice
     *
     * Converts an appointment to an invoice with pre-filled data
     * Only works for internal appointments with contacts
     *
     * @return void
     */
    public function convert_to_invoice()
    {
        // Check permissions
        if (! staff_can('create', 'invoices')) {
            access_denied();
        }

        // Get appointment ID
        $appointment_id = $this->input->post('appointment_id');
        if (! $appointment_id) {
            echo json_encode(['success' => false, 'message' => _l('appointment_convert_to_invoice_no_appointment_id')]);

            return;
        }

        $appointment = $this->apm->get_appointment_data($appointment_id);

        if (! $appointment) {
            echo json_encode(['success' => false, 'message' => _l('appointment_convert_to_invoice_appointment_not_found')]);

            return;
        }

        // Check if appointment is internal and has contact_id
        if ($appointment['source'] != 'internal' || empty($appointment['contact_id'])) {
            echo json_encode(['success' => false, 'message' => _l('appointment_convert_to_invoice_only_contacts_allowed')]);

            return;
        }

        // Get client ID from contact
        // client/customer is the company entity, while contacts are individual people associated with that company
        $this->load->model('clients_model');
        $contact = $this->clients_model->get_contact($appointment['contact_id']);

        if (! $contact || ! $contact->userid) {
            echo json_encode(['success' => false, 'message' => _l('appointment_convert_to_invoice_unable_to_find_client_for_this_contact')]);

            return;
        }

        // The userid field in a contact refers to the client_id (customer/company ID)
        $client_id = $contact->userid;

        // Get client info for billing details
        $client = $this->clients_model->get($client_id);
        if (! $client) {
            echo json_encode(['success' => false, 'message' => _l('appointment_convert_to_invoice_client_not_found')]);

            return;
        }

        // Get service details for invoice item
        $service_details = [];
        if (! empty($appointment['service_id'])) {
            $service = $this->apm->get_service($appointment['service_id']);
            if ($service) {
                $service_details = [
                    'name'        => $service->name,
                    'price'       => $service->price,
                    'description' => $service->description,
                ];
            }
        }

        // Prepare invoice data
        $invoice_data = [
            'clientid'   => $client_id,
            'date'       => date('Y-m-d'),
            'duedate'    => date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY')),
            'currency'   => get_base_currency()->id,
            'number'     => get_option('next_invoice_number'),
            'status'     => 6,
            'tags'       => strtolower(_l('appointment_appointments')),
            // Set the sale agent as the appointment organizer
            'sale_agent' => $appointment['created_by'] ?? get_staff_user_id(),

            'billing_street'   => $client->billing_street ?? '',
            'billing_city'     => $client->billing_city ?? '',
            'billing_state'    => $client->billing_state ?? '',
            'billing_zip'      => $client->billing_zip ?? '',
            'billing_country'  => $client->billing_country ?? 0,
            'shipping_street'  => $client->shipping_street ?? '',
            'shipping_city'    => $client->shipping_city ?? '',
            'shipping_state'   => $client->shipping_state ?? '',
            'shipping_zip'     => $client->shipping_zip ?? '',
            'shipping_country' => $client->shipping_country ?? 0,

            'terms'            => get_option('predefined_terms_invoice'),
            'clientnote'       => get_option('predefined_clientnote_invoice'),
            // Add appointment private notes to admin notes
            'adminnote'        => ! empty($appointment['notes']) ? strip_tags($appointment['notes']) : '',
            'addedfrom'        => get_staff_user_id(),
            'show_quantity_as' => 1,
            'newitems'         => [
                [
                    'description'      => ! empty($appointment['subject']) ? strip_tags($appointment['subject']) : 'Appointment Service',
                    'long_description' => 'Appointment on ' . $appointment['date'] .
                        (! empty($appointment['start_hour']) ? ' at ' . date('H:i', strtotime($appointment['start_hour'])) : '') .
                        (! empty($appointment['description']) ? "\n\n" . strip_tags($appointment['description']) : ''),
                    'qty'              => 1,
                    'unit'             => '',
                    'rate'             => ! empty($service_details['price']) ? $service_details['price'] : 0,
                    'taxname'          => $this->getInvoiceTaxName(),
                    'order'            => 1,
                ],
            ],
        ];

        // Load invoices model
        $this->load->model('invoices_model');

        // Create the invoice
        $invoice_id = $this->invoices_model->add($invoice_data);

        if ($invoice_id) {
            // Update appointment with invoice ID
            $this->db->where('id', $appointment_id);
            $this->db->update(db_prefix() . 'appointly_appointments', [
                'invoice_id'   => $invoice_id,
                'invoice_date' => date('Y-m-d H:i:s'),
            ]);

            // Return success with invoice ID
            echo json_encode([
                'success'    => true,
                'invoice_id' => $invoice_id,
                'message'    => _l('appointment_converted_to_invoice'),
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('appointment_convert_to_invoice_failed')]);
        }
    }

    /**
     * Get tax name array for invoice conversion
     * Creates a tax entry with the configured VAT percentage
     *
     * @return array Tax name array for invoice items
     */
    private function getInvoiceTaxName()
    {
        $vat_percentage = get_option('appointly_invoice_default_vat', 0);

        if ($vat_percentage <= 0) {
            return [];
        }

        // Create tax name in the format expected by the invoice system
        $tax_name = 'VAT|' . $vat_percentage;

        return [$tax_name];
    }

    /**
     * Get providers associated with a specific service
     * Used for dynamic provider loading when selecting a service
     */
    public function get_providers_by_service()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $service_id = $this->input->post('service_id');

        if (! $service_id) {
            header('Content-Type: application/json');
            echo json_encode([]);

            return;
        }

        // Get service providers from the linking table
        $this->db->select('s.staffid, s.firstname, s.lastname, ss.is_primary');
        $this->db->from(db_prefix() . 'staff s');
        $this->db->join(
            db_prefix() . 'appointly_service_staff ss',
            's.staffid = ss.staff_id',
            'inner'
        );
        $this->db->where('ss.service_id', $service_id);
        $this->db->where('s.active', 1);

        $providers = $this->db->get()->result_array();

        // Format the providers data
        $formatted_providers = [];
        foreach ($providers as $provider) {
            $formatted_providers[] = [
                'id'         => $provider['staffid'],
                'staffid'    => $provider['staffid'],
                'firstname'  => $provider['firstname'],
                'lastname'   => $provider['lastname'],
                'name'       => $provider['firstname'] . ' ' . $provider['lastname'],
                'is_primary' => $provider['is_primary'],
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($formatted_providers);
    }

    /**
     * Get available dates for a provider
     * Considers company schedule, staff hours, and existing appointments
     */
    public function get_available_dates()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $service_id  = $this->input->post('service_id');
        $provider_id = $this->input->post('provider_id');
        $timezone    = $this->input->post('timezone') ?? get_option('default_timezone');

        // Get working hours for this provider
        $working_hours = $this->apm->get_staff_working_hours($provider_id);

        // Log what we found for debugging
        log_message('debug', "Staff ID {$provider_id} working hours: " . json_encode($working_hours));

        // Get ALL company schedule days (not just enabled ones)
        $this->db->select('*');
        $this->db->from(db_prefix() . 'appointly_company_schedule');
        $company_schedule = $this->db->get()->result_array();

        log_message('debug', "Company schedule (all days): " . count($company_schedule));

        // Organize by day for easier lookup
        $company_schedule_by_day = [];
        foreach ($company_schedule as $day) {
            $company_schedule_by_day[$day['weekday']] = $day;
        }

        // Create array of available days
        $available_days = [];
        $start_date     = new DateTime('today', new DateTimeZone($timezone));

        // Check next 30 days
        for ($i = 0; $i < 30; $i++) {
            $current_date = clone $start_date;
            $current_date->add(new DateInterval("P{$i}D"));

            $day_name = $current_date->format('l'); // Get day name (Monday, Tuesday, etc.)

            // Check if this day is available
            $day_available = false;

            // Check if staff has specific hours for this day
            if (isset($working_hours[$day_name])) {
                // Staff has hours set for this day
                if ($working_hours[$day_name]['is_available']) {
                    // Staff is available on this day
                    $day_available = true;
                    log_message('debug', "Day {$day_name} available: Staff has hours and is available");
                }
            } else {
                // Staff has no hours set for this day, check company schedule
                if (isset($company_schedule_by_day[$day_name])) {
                    // Check if company has this day enabled in schedule
                    $day_available = (bool)$company_schedule_by_day[$day_name]['is_enabled'];
                    log_message('debug', "Day {$day_name} available: " . ($day_available ? 'true' : 'false') . " (using company schedule, no staff hours)");
                }
            }

            if ($day_available) {
                $available_days[] = $current_date->format('Y-m-d');
            }
        }

        echo json_encode([
            'success'         => true,
            'available_dates' => $available_days,
        ]);
    }

    /**
     * Change appointment status
     *
     * @return void
     */
    public function change_appointment_status()
    {
        if (! $this->input->is_ajax_request()) {
            show_404();
        }

        $id     = $this->input->post('id');
        $status = $this->input->post('status');

        // Validate status value
        $valid_statuses = ['pending', 'cancelled', 'completed', 'no-show', 'in-progress'];
        if (! in_array($status, $valid_statuses)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid status value',
            ]);

            return;
        }

        // Handle specific status changes that need extra processing
        if ($status === 'in-progress') {
            // Send notifications as done in approve_appointment
            $this->apm->appointment_approve_notification_and_sms_triggers($id);

            // Update external_notification_date for tracking purposes
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'appointly_appointments', [
                'external_notification_date' => date('Y-m-d'),
            ]);
        }

        // Change the status
        $success = $this->apm->change_appointment_status($id, $status);

        header('Content-Type: application/json');

        echo json_encode([
            'success' => (bool) $success,
            'message' => $success
                ? _l('appointment_status_changed_successfully')
                : _l('appointment_status_change_failed'),
        ]);
    }

    /**
     * Approve reschedule request
     *
     * @return void
     */
    public function approve_reschedule()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $reschedule_id = $this->input->post('reschedule_id');
        if (!$reschedule_id) {
            echo json_encode(['success' => false, 'message' => 'Missing reschedule ID']);
            return;
        }

        // Get reschedule request data
        $reschedule = $this->apm->get_reschedule_request($reschedule_id);
        if (!$reschedule || $reschedule['status'] != 'pending') {
            echo json_encode(['success' => false, 'message' => 'Invalid reschedule request']);
            return;
        }

        $success = $this->apm->approve_reschedule_request($reschedule_id, get_staff_user_id());

        if ($success) {
            // Send approval notification to client using correct appointly pattern
            $template = mail_template(
                'appointly_appointment_reschedule_approved_to_contact',
                'appointly',
                array_to_object($reschedule)
            );
            @$template->send();

            // Log the approval using standard Perfex logging
            log_activity('Appointment Reschedule Approved [AppointmentID: ' . $reschedule['appointment_id'] . ', New Date: ' . $reschedule['requested_date'] . ' ' . $reschedule['requested_time'] . ', Approved by: ' . get_staff_user_id() . ']');

            echo json_encode([
                'success' => true,
                'message' => _l('appointment_reschedule_approved_successfully')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_reschedule_approval_failed')
            ]);
        }
    }

    /**
     * Deny reschedule request
     *
     * @return void
     */
    public function deny_reschedule()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $reschedule_id = $this->input->post('reschedule_id');
        $denial_reason = $this->input->post('denial_reason');
        if (!$reschedule_id) {
            echo json_encode(['success' => false, 'message' => 'Missing reschedule ID']);
            return;
        }

        if (empty($denial_reason)) {
            echo json_encode(['success' => false, 'message' => 'Denial reason is required']);
            return;
        }

        // Get reschedule request data
        $reschedule = $this->apm->get_reschedule_request($reschedule_id);
        if (!$reschedule || $reschedule['status'] != 'pending') {
            echo json_encode(['success' => false, 'message' => 'Invalid reschedule request']);
            return;
        }

        $success = $this->apm->deny_reschedule_request($reschedule_id, get_staff_user_id(), $denial_reason);

        if ($success) {
            // Send denial notification to client using correct appointly pattern
            $template = mail_template(
                'appointly_appointment_reschedule_denied_to_contact',
                'appointly',
                array_to_object($reschedule)
            );
            @$template->send();

            // Log the denial using standard Perfex logging
            log_activity('Appointment Reschedule Denied [AppointmentID: ' . $reschedule['appointment_id'] . ', Denial Reason: ' . $denial_reason . ', Denied by: ' . get_staff_user_id() . ']');

            echo json_encode([
                'success' => true,
                'message' => _l('appointment_reschedule_denied_successfully')
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => _l('appointment_reschedule_denial_failed')
            ]);
        }
    }

    /**
     * Format full address from client or lead data
     *
     * @param object|array $data Client or lead data object/array
     * @return string Formatted full address
     */
    private function format_full_address($data)
    {
        if (!$data) {
            return '';
        }

        // Convert object to array for consistent handling
        if (is_object($data)) {
            $data = (array) $data;
        }

        $address_parts = [];

        // Add address components in logical order
        if (!empty($data['address'])) {
            $address_parts[] = $data['address'];
        }

        if (!empty($data['city'])) {
            $address_parts[] = $data['city'];
        }

        if (!empty($data['state'])) {
            $address_parts[] = $data['state'];
        }

        if (!empty($data['zip'])) {
            $address_parts[] = $data['zip'];
        }

        if (!empty($data['country'])) {
            // If country is numeric (country ID), get country name using Perfex helper
            if (is_numeric($data['country']) && $data['country'] > 0) {
                $country_name = get_country_name($data['country']);
                if ($country_name) {
                    $address_parts[] = $country_name;
                }
            } else {
                // If country is already a string, use it directly
                $address_parts[] = $data['country'];
            }
        }

        // Join with commas and return
        return implode(', ', array_filter($address_parts));
    }

    /**
     * Generate .ics calendar file for appointment
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

        // Check permissions - allow if user is staff, client contact, or lead contact
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
            // For public access (appointment hash links), check if appointment exists
            // This allows access via direct appointment hash links
            $has_permission = true;
        }

        if (!$has_permission) {
            show_404();
        }

        // Generate .ics content using helper function
        $ics_content = generate_appointment_ics_content($appointment);

        // Set headers for .ics download
        $filename = 'appointment_' . $appointment_id . '_' . date('Y-m-d', strtotime($appointment['date'])) . '.ics';

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

        echo $ics_content;
        exit;
    }
}

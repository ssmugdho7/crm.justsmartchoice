<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Appointly_model extends App_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('appointly/appointly_attendees_model', 'atm');
        $this->load->model('appointly/service_model', 'service_model');
    }

    /**
     * Insert new appointment
     *
     * @param  array  $data
     *
     * @return bool
     * @throws Exception
     */
    public function create_appointment($data)
    {
        $attendees    = [];
        $relation     = $data['rel_type'];
        $external_cid = null;

        unset($data['rel_type']);
        // Ensure rel_lead_type is unset to avoid SQL errors
        if (isset($data['rel_lead_type'])) {
            unset($data['rel_lead_type']);
        }

        // Ensure notification flags are properly set
        $data['by_sms']   = $data['by_sms'] ?? 0;
        $data['by_email'] = $data['by_email'] ?? 0;

        // Check if this is a staff-only appointment (internal_staff)
        if ($relation == 'internal_staff') {
            // Skip service validation for staff-only appointments
            // Set default duration for staff-only
            $data['duration'] = $data['duration'] ?? 60;
            // Set source for staff-only
            $data['source'] = 'internal_staff';
            // Remove client/contact/lead info for staff-only
            unset($data['contact_id'], $data['email'], $data['phone'], $data['name'], $data['rel_id']);
            // Ensure address is set for Google Calendar
            $data['address'] = $data['address'] ?? '';
        } elseif (isset($data['service_id'])) {
            // Get service details and validate for non-staff-only appointments
            $service = $this->service_model->get($data['service_id']);
            if (! $service) {
                return false;
            }
            // Set duration from service
            $data['duration'] = $service->duration;
        }

        if ($relation == 'lead_related') {
            $this->load->model('leads_model');
            $lead = $this->leads_model->get($data['rel_id']);
            if ($lead) {
                $data['contact_id'] = $data['rel_id'];
                $data['name']       = $lead->name;
                if ($lead->phonenumber != '') {
                    $data['phone'] = $lead->phonenumber;
                }
                if ($lead->address != '') {
                    $data['address'] = $lead->address;
                }
                if ($lead->email != '') {
                    $data['email'] = $lead->email;
                }
            }
            $data['source'] = 'lead_related';
            // Unset rel_id here now that we've used it
            if (isset($data['rel_id'])) {
                unset($data['rel_id']);
            }
        } elseif ($relation == 'internal') {
            $contact_id = $data['contact_id'] ?? null;
            if ($contact_id) {
                $this->load->model('clients_model');
                $data['contact_id'] = $contact_id;
                $data['source']     = 'internal';
            }
            // Unset rel_id if it exists, but we don't need it
            if (isset($data['rel_id'])) {
                unset($data['rel_id']);
            }
        } elseif ($relation == 'external') {
            if (! $data['email']) {
                return false;
            }
            $data['source'] = 'external';
            // Unset rel_id if it exists, but we don't need it
            if (isset($data['rel_id'])) {
                unset($data['rel_id']);
            }
        }

        // Process recurring data using the validateRecurringData method
        if (isset($data['repeat_appointment'])) {
            $data = $this->validateRecurringData($data);
        }

        // Process reminder fields first
        $data = handleDataReminderFields($data);

        // Remove recurring fields if recurring isn't enabled
        if (!isset($data['recurring']) || $data['recurring'] != 1) {
            unset($data['repeat_every']);
            unset($data['repeat_every_custom']);
            unset($data['repeat_type_custom']);
        }

        // Only process reminder settings if notifications are enabled
        if ((!isset($data['by_email']) || $data['by_email'] != 1) &&
            (!isset($data['by_sms']) || $data['by_sms'] != 1)
        ) {
            unset($data['reminder_before']);
            unset($data['reminder_before_type']);
        }

        // Remove repeat_appointment field as it's only used for UI
        unset($data['repeat_appointment']);

        // Ensure address is set for all appointment types
        $data['address'] = $data['address'] ?? '';

        // Format date properly
        $data['date'] = to_sql_date($data['date'], true);

        // Description can be empty
        $data['description'] = $data['description'] ?? '';

        // Ensure start_hour and end_hour are properly set
        if (! empty($data['start_hour'])) {
            // Format start_hour for consistent storage (HH:MM format without seconds)
            $data['start_hour'] = date('H:i', strtotime($data['start_hour']));

            // Calculate end_hour from start_hour and duration if not already set
            if ((empty($data['end_hour'])) && ! empty($data['duration'])) {
                $end_time         = strtotime($data['start_hour']) + ($data['duration'] * 60);
                $data['end_hour'] = date('H:i', $end_time);
            }
        } elseif (! empty($data['end_hour']) && ! empty($data['duration'])) {
            // If only end_hour is set, calculate start_hour by subtracting duration
            $data['end_hour']   = date('H:i', strtotime($data['end_hour']));
            $start_time         = strtotime($data['end_hour']) - ($data['duration'] * 60);
            $data['start_hour'] = date('H:i', $start_time);
        }

        // Ensure end_hour is formatted properly if it was set directly
        if (! empty($data['end_hour'])) {
            $data['end_hour'] = date('H:i', strtotime($data['end_hour']));
        }

        // Set created by
        $data['created_by'] = $data['created_by'] ?? get_staff_user_id();

        // Initialize links
        $data['google_calendar_link']  = '';
        $data['google_added_by_id']    = '';
        $data['outlook_calendar_link'] = '';
        $data['outlook_event_id']      = '';
        $data['feedback_comment']      = '';

        // Format phone number
        if (! empty($data['phone'])) {
            $data['phone'] = trim($data['phone']);
        }

        // Get contact data for internal appointments
        if (
            isset($data['source']) && $data['source'] == 'internal'
            && empty($data['email'])
        ) {
            $contact_data  = get_appointment_contact_details($data['contact_id']);
            $data['email'] = $contact_data['email'];
            $data['name']  = $contact_data['full_name'];
            $data['phone'] = $contact_data['phone'];
        }

        // Handle status - default to 'in-progress' if not set
        $data['status'] = isset($data['status']) && in_array($data['status'], ['pending', 'cancelled', 'completed', 'no-show', 'in-progress'])
            ? $data['status']
            : 'in-progress';

        // Handle timezone
        $data['timezone'] = $data['timezone'] ?? get_option('default_timezone');

        // Extract attendees
        if (isset($data['attendees'])) {
            $attendees = $data['attendees'];
            unset($data['attendees']);
        }

        //  provider to attendees if not already included
        $provider_id = $data['provider_id'] ?? null;
        if ($provider_id && ! in_array((int)$provider_id, $attendees, true)) {
            $attendees[] = (int)$provider_id;
        }

        // Staff-only appointment validation
        if ($relation == 'internal_staff' && empty($attendees)) {
            // Return false instead of throwing exception
            return false;
        }

        // Google Calendar integration
        if ((isset($data['google']) && $data['google'] && appointlyGoogleAuth()) || (get_option('appointly_auto_enable_google_meet') == '1' && appointlyGoogleAuth())) {
            // For staff-only, ensure each attendee has a valid email before adding to Google
            if ($relation == 'internal_staff' && ! empty($attendees)) {
                // Validate attendee emails before trying to add to Google Calendar
                $valid_attendees_emails = [];
                foreach ($attendees as $attendee_id) {
                    $staff = $this->staff_model->get($attendee_id);

                    if ($staff && filter_var($staff->email, FILTER_VALIDATE_EMAIL)) {
                        $valid_attendees_emails[] = $staff->email;
                    }
                }

                // Only proceed with Google Calendar if we have valid attendees
                if (! empty($valid_attendees_emails)) {
                    $data['external_contact_id'] = $external_cid;
                    $googleEvent                 = insertAppointmentToGoogleCalendar($data, $valid_attendees_emails);
                    if ($googleEvent) {
                        $data['google_event_id']      = $googleEvent['google_event_id'];
                        $data['google_calendar_link'] = $googleEvent['htmlLink'];
                        if (isset($googleEvent['hangoutLink'])) {
                            $data['google_meet_link'] = $googleEvent['hangoutLink'];
                        }
                        $data['google_added_by_id'] = get_staff_user_id();
                    }
                }
            } else {
                $data['external_contact_id'] = $external_cid;
                $googleEvent                 = insertAppointmentToGoogleCalendar($data, $attendees);
                if ($googleEvent) {
                    $data['google_event_id']      = $googleEvent['google_event_id'];
                    $data['google_calendar_link'] = $googleEvent['htmlLink'];
                    if (isset($googleEvent['hangoutLink'])) {
                        $data['google_meet_link'] = $googleEvent['hangoutLink'];
                    }
                    $data['google_added_by_id'] = get_staff_user_id();
                }
            }
            unset($data['google'], $data['external_contact_id']);
        }

        // Final safety check before database insert
        if (isset($data['rel_id'])) {
            unset($data['rel_id']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        // We need this for external appointments form view public url ... 
        $data['hash'] = app_generate_hash();

        // Remove CSRF token and other non-database fields before insert
        $fieldsToRemove = ['ci_csrf_token', 'csrf_token_name'];
        foreach ($fieldsToRemove as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        // Insert the appointment
        $this->db->insert(db_prefix() . 'appointly_appointments', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $data['appointment_id'] = $insert_id;
            $data['id']             = $insert_id;
            $data['feedbacks']      = null;

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }
            // Create attendees
            if (! empty($attendees)) {
                $this->atm->create($insert_id, $attendees);
            }

            // Link appointment to service if service_id is provided
            if (! empty($data['service_id'])) {
                $this->link_appointment_service($insert_id, $data['service_id']);
            }

            // Handle custom fields and notifications
            $this->prepareAndNotifyUsers($data, $insert_id);

            return $insert_id;
        }

        return false;
    }

    /**
     * Normalize and validate recurring appointment data (for insert or update)
     *
     * @param  array  $data
     * @param  array|null  $original  Optional original data for update comparison
     *
     * @return array
     */
    private function validateRecurringData(array $data, ?array $original = null)
    {
        if (
            isset($original['repeat_every'], $data['repeat_every']) && $original && $original['repeat_every'] !== '' && $data['repeat_every'] === ''
        ) {
            $data['cycles']              = 0;
            $data['total_cycles']        = 0;
            $data['last_recurring_date'] = null;
        }

        if (! empty($data['repeat_every'])) {
            $data['recurring'] = 1;

            if ($data['repeat_every'] === 'custom') {
                if (isset($data['repeat_every_custom'])) {
                    $data['repeat_every']     = $data['repeat_every_custom'];
                    $data['recurring_type']   = $data['repeat_type_custom'] ??
                        null;
                    $data['custom_recurring'] = 1;
                }
            } else {
                $_temp = explode('-', $data['repeat_every']);
                if (count($_temp) > 1) {
                    $data['recurring_type']   = $_temp[1];
                    $data['repeat_every']     = $_temp[0];
                    $data['custom_recurring'] = 0;
                }
            }
        } else {
            $data['recurring'] = 0;
        }

        // Normalize cycles if not set or recurrence is off
        $data['cycles'] = (! isset($data['cycles']) || $data['recurring'] == 0)
            ? 0
            : $data['cycles'];

        // Always clean temporary fields
        unset($data['repeat_type_custom'], $data['repeat_every_custom'], $data['repeat_appointment']);
        return $data;
    }

    public function link_appointment_service($appointment_id, $service_id)
    {
        $this->db->insert(db_prefix() . 'appointly_appointment_services', [
            'appointment_id' => $appointment_id,
            'service_id'     => $service_id,
        ]);

        return $this->db->insert_id();
    }

    /**
     * Helper function for create appointment
     *w
     *
     * @param $data
     * @param $insert_id
     *
     * @return bool
     */
    public function prepareAndNotifyUsers($data, $insert_id)
    {
        $data = array_merge($data, convertDateForDatabase($data['date']));
        // Remove unnecessary fields
        $fieldsToRemove = ['custom_fields', 'rel_id', 'staff_id'];
        foreach ($fieldsToRemove as $field) {
            if (isset($data[$field])) {
                unset($data[$field]);
            }
        }

        if (isset($data['rel_id'])) {
            unset($data['rel_id']);
        }

        // Ensure reminders are setwe have the by_sms and by_email flags
        $by_sms   = $data['by_sms'] ?? 0;
        $by_email = $data['by_email'] ?? 0;

        // Update appointment with notification flags if they weren't already set
        $this->db->where('id', $insert_id);
        $this->db->update(db_prefix() . 'appointly_appointments', ['by_sms' => $by_sms, 'by_email' => $by_email]);

        // Pass these flags to the notification function
        $appointment = $this->get_appointment_data($insert_id);

        // Ensure appointment has email before sending notifications
        if (! empty($appointment['email'])) {
            $this->appointment_approve_notification_and_sms_triggers($insert_id);
        }

        return $insert_id;
    }

    /**
     * Get single appointment data
     *
     * @param  string  $appointment_id
     *
     * @return array|bool
     */
    public function get_appointment_data($appointment_id)
    {
        $this->db->where('id', $appointment_id);
        $appointment = $this->db->get(db_prefix() . 'appointly_appointments')->row_array();

        if ($this->db->affected_rows() > 0) {
            $appointment['attendees'] = $this->atm->get($appointment_id);

            return $appointment;
        }

        return false;
    }

    /**
     * Send email and SMS notifications
     *
     * @param  string  $appointment_id
     *
     * @return void
     */
    public function appointment_approve_notification_and_sms_triggers($appointment_id): void
    {
        try {
            // Get appointment data
            $appointment = $this->get_appointment_data($appointment_id);

            // Fetch attendees (array of staff_id)
            $attendees     = $this->get_appointment_attendees($appointment_id);
            $recipient_ids = array_unique(array_column($attendees, 'staff_id'));

            // remove creator from recipient_ids
            if ($appointment && isset($appointment['created_by'])) {
                $recipient_ids = array_diff($recipient_ids, [$appointment['created_by']]);
            }

            foreach ($recipient_ids as $staff_id) {
                if ($staff_id == $appointment['created_by']) {
                    continue;
                }

                add_notification([
                    'description' => 'appointment_notification',
                    'touserid'    => $staff_id,
                    'fromcompany' => true,
                    'link'        => 'appointly/appointments/view?appointment_id=' . $appointment_id,
                ]);
            }

            $template = null;
            // Send email to customer if by_email is enabled
            if (! empty($appointment['email'])) {
                try {
                    $template = mail_template(
                        'appointly_appointment_approved_to_contact',
                        'appointly',
                        array_to_object($appointment)
                    );

                    // Send the email
                    $template->send();
                } catch (Exception $e) {
                    // Log the error but continue
                    log_message('error', 'Failed to send appointment email: ' . $e->getMessage());
                }
            }

            // Send SMS to customer if by_sms is enabled
            if (! empty($appointment['phone'])) {
                try {
                    // Trigger SMS
                    $this->app_sms->trigger(
                        APPOINTLY_SMS_APPOINTMENT_APPROVED_TO_CLIENT,
                        $appointment['phone'],
                        isset($template) ? $template->get_merge_fields() : []
                    );
                } catch (Exception $e) {
                    // Log the error but continue
                    log_message('error', 'Failed to send appointment SMS: ' . $e->getMessage());
                }
            }

            // Send email to attendees
            if (!empty($attendees)) {
                foreach ($attendees as $staff) {
                    if (isset($staff['email']) && !empty($staff['email'])) {
                        send_mail_template(
                            'appointly_appointment_approved_to_staff_attendees',
                            'appointly',
                            array_to_object($appointment),
                            array_to_object($staff)
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            // Notification failures must never roll back a valid status update.
            log_message('error', 'Error in appointment notifications: ' . $e->getMessage());
        }
    }

    public function get_appointment_attendees($appointment_id)
    {
        $this->db->select('staff.*, appointly_attendees.appointment_id');
        $this->db->from(db_prefix() . 'staff staff');
        $this->db->join(
            db_prefix() . 'appointly_attendees appointly_attendees',
            'appointly_attendees.staff_id = staff.staffid',
            'inner'
        );
        $this->db->where('appointly_attendees.appointment_id', $appointment_id);

        return $this->db->get()->result_array();
    }

    public function recurringAddGoogleNewEvent($data, $attendees)
    {
        $googleInsertData                         = [];
        $googleEvent                              = insertAppointmentToGoogleCalendar($data, $attendees);
        $googleInsertData['google_event_id']      = $googleEvent['google_event_id'];
        $googleInsertData['google_calendar_link'] = $googleEvent['htmlLink'];
        if (isset($googleEvent['hangoutLink'])) {
            $googleInsertData['google_meet_link'] = $googleEvent['hangoutLink'];
        }

        return $googleInsertData;
    }

    /**
     * Add appointment to google calendar
     *
     * @param  array  $data
     *
     * @return array
     * @throws Exception
     */
    public function add_event_to_google_calendar($data)
    {
        $result = [
            'result'  => 'error',
            'message' => _l('Oops, something went wrong, please try again...'),
        ];

        if (appointlyGoogleAuth()) {
            // info log to see what data we're receiving
            log_message('info', 'Google Calendar Add Event - Data: ' . json_encode($data));

            if (isset($data['appointment_id'])) {
                // Get the full appointment data - this ensures we have all necessary information
                $appointment = $this->get_appointment_data($data['appointment_id']);
                if (! $appointment) {
                    return [
                        'result'  => 'error',
                        'message' => 'Appointment not found',
                    ];
                }

                // Ensure start_hour and end_hour are properly formatted for Google Calendar
                if (isset($appointment['start_hour']) && ! strpos($appointment['start_hour'], ':')) {
                    $appointment['start_hour'] .= ':00';
                }
                if (isset($appointment['end_hour']) && ! strpos($appointment['end_hour'], ':')) {
                    $appointment['end_hour'] .= ':00';
                }

                // Prepare attendees
                $attendees = $data['attendees'] ?? [];
                if (empty($attendees) && isset($appointment['attendees'])) {
                    $attendees = array_column(
                        $appointment['attendees'],
                        'staffid'
                    );
                }

                // Log the appointment data we're sending to Google
                log_message('debug', 'Google Calendar Add Event - Using appointment: ' . json_encode(
                    [
                        'id'         => $appointment['id'],
                        'date'       => $appointment['date'],
                        'start_hour' => $appointment['start_hour'],
                        'end_hour'   => $appointment['end_hour'],
                        'attendees'  => $attendees,
                    ]
                ));

                // Create the Google Calendar event using the same function as when creating appointments
                $googleEvent = insertAppointmentToGoogleCalendar($appointment, $attendees);

                if (! $googleEvent || ! isset($googleEvent['google_event_id'])) {
                    log_message('error', 'Google Calendar Add Event - Failed to create event');

                    return [
                        'result'  => 'error',
                        'message' => 'Failed to create Google Calendar event',
                    ];
                }

                // Update the appointment with Google Calendar data
                $googleUpdateData = [
                    'google_event_id'      => $googleEvent['google_event_id'],
                    'google_calendar_link' => $googleEvent['htmlLink'],
                    'google_added_by_id'   => get_staff_user_id(),
                ];

                // Add Google Meet link if available
                if (isset($googleEvent['hangoutLink'])) {
                    $googleUpdateData['google_meet_link'] = $googleEvent['hangoutLink'];
                }

                $this->db->where('id', $data['appointment_id']);
                $this->db->update(db_prefix() . 'appointly_appointments', $googleUpdateData);

                if ($this->db->affected_rows() > 0) {
                    log_message('info', 'Google Calendar Add Event - Successfully added for appointment ID: ' . $data['appointment_id']);

                    return [
                        'result'               => 'success',
                        'message'              => _l('appointments_added_to_google_calendar'),
                        'google_event_id'      => $googleEvent['google_event_id'],
                        'google_calendar_link' => $googleEvent['htmlLink'],
                        'google_meet_link'     => $googleEvent['hangoutLink'] ?? null,
                    ];
                }
            }
        }

        log_message('error', 'Google Calendar Add Event - Failed with data: ' . json_encode($data));

        return $result;
    }

    /**
     * Inserts appointment submitted from external clients form
     *
     * @param  array  $data
     *
     * @return bool
     */
    public function insert_external_appointment($data)
    {
        // Basic data preparation
        $data['hash']   = app_generate_hash();
        $data['source'] = 'external';

        // Clean phone number
        if (isset($data['phone']) && $data['phone']) {
            $data['phone'] = trim($data['phone']);
        }

        // Get service details and set duration
        if (! empty($data['service_id'])) {
            $this->load->model('appointly/service_model');
            $service = $this->service_model->get($data['service_id']);

            if ($service) {
                $data['duration'] = $service->duration;

                // Store service id for later use
                $appointment_service_id = $data['service_id'];
            }
        }

        // Add provider_id from staff_id
        if (! empty($data['staff_id'])) {
            $data['provider_id'] = $data['staff_id'];
            unset($data['staff_id']);
        } elseif (! empty($data['hidden_staff_id'])) {
            $data['provider_id'] = $data['hidden_staff_id'];
        }

        // Remove duplicate provider_id value if it exists
        unset($data['hidden_staff_id']);

        // Convert date for database and ensure start_hour is properly set
        if (isset($data['date']) && !empty($data['date'])) {
            $date_data = convertDateForDatabase($data['date']);
            $data['date'] = $date_data['date'];

            // Make sure start_hour is set and has the correct format
            if (!isset($data['start_hour']) || empty($data['start_hour'])) {
                $data['start_hour'] = $date_data['start_hour'];
            }

            // Calculate end_hour based on start_hour and duration
            if (isset($data['start_hour']) && !empty($data['start_hour']) && isset($data['duration'])) {
                // Parse start hour
                list($hours, $minutes) = explode(':', $data['start_hour']);

                // Add duration minutes
                $end_timestamp = mktime($hours, $minutes, 0) + ($data['duration'] * 60);

                // Format end hour
                $data['end_hour'] = date('H:i', $end_timestamp);
            }
        }

        $data['status'] = 'pending';

        // Handle custom fields if present
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
        }

        // Remove unnecessary fields
        unset($data['rel_type'], $data['terms_accepted'], $data['custom_fields']);

        // Insert appointment
        $this->db->insert(db_prefix() . 'appointly_appointments', $data);
        $appointment_id = $this->db->insert_id();

        if ($appointment_id) {
            // If we have a service, link it to the appointment
            if (! empty($appointment_service_id)) {
                $this->link_appointment_service(
                    $appointment_id,
                    $appointment_service_id
                );
            }

            // Handle custom fields
            if (isset($custom_fields)) {
                handle_custom_fields_post($appointment_id, $custom_fields);
            }

            // Send notifications if approved by default
            $data['id'] = $appointment_id;

            // For external appointments, notify all relevant staff so they can approve
            if ($data['source'] === 'external') {
                $this->notify_admins($appointment_id);
            } else {
                $this->notify_appointment_staff($appointment_id, $data);
            }

            // Send confirmation email to client
            $this->send_client_submission_confirmation($appointment_id, $data);

            // Auto-create invoice if setting is enabled and user is logged in
            $this->auto_create_invoice_on_booking($appointment_id, $data);
        }

        return $appointment_id;
    }

    /**
     * Auto-create invoice on booking if setting is enabled
     *
     * @param int $appointment_id
     * @param array $data
     * @return void
     */
    private function auto_create_invoice_on_booking($appointment_id, $data)
    {
        // Check if invoice creation is enabled
        if (get_option('appointly_show_invoice_option') != '1') {
            return;
        }

        // Only create invoice if user is logged in (has contact_id)
        if (empty($data['contact_id'])) {
            return;
        }

        // Load the invoice model
        $this->load->model('appointly/AppointlyInvoices_model', 'appointly_invoices');

        // Create the invoice
        $invoice_id = $this->appointly_invoices->create_invoice_from_appointment($appointment_id);

        if ($invoice_id) {
            // Update appointment with invoice_id
            $this->db->where('id', $appointment_id);
            $this->db->update(db_prefix() . 'appointly_appointments', [
                'invoice_id' => $invoice_id,
                'invoice_date' => date('Y-m-d H:i:s')
            ]);

            log_message('info', 'Auto-created invoice #' . $invoice_id . ' for appointment #' . $appointment_id . ' on booking');
        }
    }

    /**
     * Notify all staff
     *
     * @param  int  $appointment_id
     * @param  array  $data
     *
     * @return void
     */
    private function notify_admins($appointment_id)
    {
        // Fetch all staff with permissions to view appointments or admin access
        $this->db->select('staffid');
        $this->db->where('admin', 1);
        $this->db->where('active', 1);
        $staff = $this->db->get(db_prefix() . 'staff')->result_array();

        $notified_users = [];

        // Send notifications to each eligible staff member
        foreach ($staff as $member) {
            add_notification([
                'description' => 'new_appointment_notification',
                'touserid'    => $member['staffid'],
                'fromcompany' => 1,
                'link'        => 'appointly/appointments/view?appointment_id=' . $appointment_id,
            ]);

            $notified_users[] = $member['staffid'];
        }

        // Trigger real-time notifications
        if (!empty($notified_users)) {
            pusher_trigger_notification($notified_users);
        }
    }

    /**
     * Notify staff involved in the appointment
     *
     * @param  int  $appointment_id
     * @param  array  $data
     *
     * @return void
     */
    private function notify_appointment_staff($appointment_id, $data)
    {
        // Get staff directly involved in the appointment
        $this->db->select('staffid');
        $this->db->where('staffid', $data['provider_id']);
        $this->db->where('active', 1);
        $staff = $this->db->get(db_prefix() . 'staff')->result_array();

        $notified_users = [];

        // Send notifications only to the provider
        foreach ($staff as $member) {
            add_notification([
                'description' => 'new_appointment_notification',
                'touserid'    => $member['staffid'],
                'fromcompany' => 1,
                'link'        => 'appointly/appointments/view?appointment_id=' . $appointment_id,
            ]);

            $notified_users[] = $member['staffid'];
        }

        // Trigger real-time notifications
        if (!empty($notified_users)) {
            pusher_trigger_notification($notified_users);
        }
    }

    /**
     * Send confirmation email to client after external appointment submission
     *
     * @param  int  $appointment_id
     * @param  array  $data
     *
     * @return void
     */
    private function send_client_submission_confirmation($appointment_id, $data)
    {
        try {
            // Only send if we have a valid email
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return;
            }

            // Get full appointment data for email template
            $appointment = $this->get_appointment_data($appointment_id);

            if (!$appointment) {
                log_message('error', 'Failed to get appointment data for client confirmation email: ' . $appointment_id);
                return;
            }

            // Send confirmation email to client
            $template = mail_template(
                'appointly_appointment_submitted_to_contact',
                'appointly',
                array_to_object($appointment)
            );

            if ($template) {
                $template->send();
                log_message('info', 'Client confirmation email sent for appointment ID: ' . $appointment_id);
            }
        } catch (Exception $e) {
            // Log the error but don't interrupt the appointment creation
            log_message('error', 'Failed to send client confirmation email for appointment ID ' . $appointment_id . ': ' . $e->getMessage());
        }
    }

    public function update_appointment_private_notes($appointment_id, $notes)
    {
        $this->db->where('id', $appointment_id);

        $this->db->update(db_prefix() . 'appointly_appointments', [
            'notes' => $notes,
        ]);

        return json_encode([
            'success' => $this->db->affected_rows() !== 0,
            'message' => _l('appointment_notes_updated'),
        ]);
    }

    /**
     * Update existing appointment
     *
     * @param  array  $data
     *
     * @return bool
     * @throws Exception
     */
    public function update_appointment($data)
    {
        $originalAppointment = $this->get_appointment_data($data['appointment_id']);
        $current_attendees   = $this->atm->attendees($data['appointment_id']);

        // Remove white spaces from phone number
        if (isset($data['phone'])) {
            $data['phone'] = trim($data['phone']);
        }

        // Handle external or lead appointment correctly
        if (isset($data['contact_id']) && $data['contact_id'] == 0) {
            unset($data['contact_id']);
        }

        // Handle reminder fields
        $data = handleDataReminderFields($data);

        // Format the date in SQL format
        if (isset($data['date'])) {
            $data['date'] = to_sql_date($data['date']);
        }

        if (! empty($data['description'])) {
            $cleanContent        = strip_tags($data['description']);
            $data['description'] = $cleanContent;
        }

        // Format the update data
        $updateData = [
            'subject'     => $data['subject'] ?? $originalAppointment['subject'],
            'description' => $data['description'] ?? $originalAppointment['description'],
            'notes'       => $data['notes'] ?? $originalAppointment['notes'],
            'date'        => $data['date'] ?? $originalAppointment['date'],
            'address'     => $data['address'] ?? $originalAppointment['address'],
            'status'      => $data['status'] ?? $originalAppointment['status'],
            'by_sms'      => isset($data['by_sms']) ? '1' : '0',
            'by_email'    => isset($data['by_email']) ? '1' : '0',
            'timezone'    => $data['timezone'] ?? $originalAppointment['timezone'],
        ];

        if (isset($data['status']) && $data['status'] !== 'cancelled') {
            $updateData['cancel_notes'] = null;
        }

        // Handle relationship fields based on rel_type
        if (isset($data['rel_type'])) {
            if ($data['rel_type'] === 'lead_related') {
                $updateData['source'] = 'lead_related';

                // For lead-related appointments, we use contact_id to store the lead ID
                if (! empty($data['rel_id'])) {
                    $updateData['contact_id'] = $data['rel_id'];

                    // Get lead details to populate name, email, phone
                    $this->load->model('leads_model');
                    $lead = $this->leads_model->get($data['rel_id']);

                    if ($lead) {
                        $updateData['name']  = $lead->name;
                        $updateData['email'] = $lead->email;
                        $updateData['phone'] = $lead->phonenumber;
                    }
                } else {
                    // If no lead ID provided but type is lead_related, keep existing contact_id
                    $updateData['contact_id'] = $originalAppointment['contact_id'];
                }
            } elseif ($data['rel_type'] === 'internal' && isset($data['contact_id'])) {
                $updateData['source']     = 'internal';
                $updateData['contact_id'] = $data['contact_id'];

                // Get contact details for internal appointments
                $contact = $this->clients_model->get_contact($data['contact_id']);
                if ($contact) {
                    $updateData['name']  = $contact->firstname . ' ' . $contact->lastname;
                    $updateData['email'] = $contact->email;
                    $updateData['phone'] = $contact->phonenumber;
                }
            } elseif ($data['rel_type'] === 'external') {
                $updateData['source']     = 'external';
                $updateData['contact_id'] = null;

                // Add external contact information
                if (isset($data['name'])) {
                    $updateData['name'] = $data['name'];
                }
                if (isset($data['email'])) {
                    $updateData['email'] = $data['email'];
                }
                if (isset($data['phone'])) {
                    $updateData['phone'] = $data['phone'];
                }
            } elseif ($data['rel_type'] === 'internal_staff') {
                $updateData['source']     = 'internal_staff';
                $updateData['contact_id'] = null;
            }
        }

        // Handle service and provider
        if (isset($data['service_id'])) {
            $updateData['service_id'] = $data['service_id'];

            // Get service duration for non-internal_staff appointments
            if ($updateData['source'] !== 'internal_staff' && ! empty($updateData['service_id'])) {
                $service = $this->get_service($updateData['service_id']);
                if ($service) {
                    $updateData['duration'] = $service->duration;
                }
            }
        }

        if (isset($data['provider_id'])) {
            $updateData['provider_id'] = $data['provider_id'];
        }

        // Handle time fields
        if (! empty($data['start_hour'])) {
            $updateData['start_hour'] = $data['start_hour'];
        }

        if (! empty($data['end_hour'])) {
            $updateData['end_hour'] = $data['end_hour'];
        }

        // For internal_staff, use provided duration
        if (
            isset($updateData['source'], $data['duration'])
            && $updateData['source'] === 'internal_staff'
        ) {
            $updateData['duration'] = $data['duration'];
        }

        // Handle reminder settings
        if (isset($data['reminder_before'])) {
            $updateData['reminder_before'] = $data['reminder_before'];
        }

        if (isset($data['reminder_before_type'])) {
            $updateData['reminder_before_type'] = $data['reminder_before_type'];
        }

        // Handle notification checkboxes (unchecked checkboxes don't send any value)
        $updateData['by_sms'] = isset($data['by_sms']) ? 1 : 0;
        $updateData['by_email'] = isset($data['by_email']) ? 1 : 0;

        // If both notifications are disabled, clear reminder settings
        if ($updateData['by_sms'] == 0 && $updateData['by_email'] == 0) {
            $updateData['reminder_before'] = null;
            $updateData['reminder_before_type'] = null;
        }
        // Process recurring data
        if (isset($data['repeat_appointment'])) {
            // transform the data for database
            $data = $this->validateRecurringData($data, $originalAppointment);

            // Add processed recurring fields to updateData
            $recurringFields = ['recurring', 'recurring_type', 'repeat_every', 'custom_recurring', 'cycles', 'total_cycles', 'last_recurring_date'];
            foreach ($recurringFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }
        } else {
            // If repeat_appointment checkbox is not checked, clear all recurring settings
            $updateData['recurring'] = 0;
            $updateData['recurring_type'] = null;
            $updateData['repeat_every'] = null;
            $updateData['custom_recurring'] = null;
            $updateData['cycles'] = null;
            $updateData['total_cycles'] = null;
            $updateData['last_recurring_date'] = null;
        }

        // Remove CSRF token and other non-database fields before update
        $fieldsToRemove = ['ci_csrf_token', 'csrf_token_name'];
        foreach ($fieldsToRemove as $field) {
            if (isset($updateData[$field])) {
                unset($updateData[$field]);
            }
        }

        // Update the appointment
        $this->db->where('id', $data['appointment_id']);
        $this->db->update(db_prefix() . 'appointly_appointments', $updateData);

        // Handle attendees - for internal_staff appointments, attendees are required
        if (isset($data['attendees']) && is_array($data['attendees'])) {
            $attendees           = $data['attendees'];
            $attendee_difference = array_diff($attendees, $current_attendees);

            // Update the attendees
            $this->atm->update($data['appointment_id'], $attendees);

            // Send notifications to new attendees
            if (! empty($attendee_difference)) {
                $new_attendees = [];

                foreach ($attendee_difference as $new_attendee) {
                    $new_attendees[] = appointly_get_staff($new_attendee);
                }

                // Prepare data for notification
                $notification_data = array_merge(
                    $data,
                    ['id' => $data['appointment_id']]
                );
                $this->atm->send_notifications_to_new_attendees($new_attendees, $notification_data);
            }

            // Send notifications for update
            $updateAppointment = $this->get_appointment_data($data['appointment_id']);

            // Only send notifications if the appointment is approved and not cancelled or completed
            if (
                isset($updateAppointment['status'])
                && $updateAppointment['status'] != 'cancelled'
                && $updateAppointment['status'] != 'completed'
            ) {
                // Send notifications to all attendees (staff)
                foreach ($attendees as $staff_id) {
                    $staff = appointly_get_staff($staff_id);

                    if (! empty($staff)) {
                        // do not notify the creator
                        if ($staff_id == $updateAppointment['created_by']) {
                            continue;
                        }

                        add_notification([
                            'description' => 'appointment_updated',
                            'touserid'    => $staff_id,
                            'fromcompany' => true,
                            'link'        => 'appointly/appointments/view?appointment_id=' . $data['appointment_id'],
                        ]);

                        send_mail_template(
                            'appointly_appointment_updated_to_staff',
                            'appointly',
                            array_to_object($staff),
                            array_to_object($updateAppointment)
                        );
                    }
                }

                // Send email notification if by_email is enabled to contact
                $template = mail_template('appointly_appointment_updated_to_contact', 'appointly', array_to_object($updateAppointment));

                if ($template) {
                    $template->send();

                    // Send SMS notification if by_sms is enabled
                    if (! empty($updateAppointment['phone'])) {
                        $merge_fields = $template->get_merge_fields();
                        $this->app_sms->trigger(
                            APPOINTLY_SMS_APPOINTMENT_UPDATED_TO_CLIENT,
                            $updateAppointment['phone'],
                            $merge_fields
                        );
                    }
                }

                // Trigger notifications
                pusher_trigger_notification($attendees);
            }
        } else {
            $this->atm->update($data['appointment_id'], []);
        }

        // Update service link if service_id is provided
        if (! empty($data['service_id'])) {
            // First delete existing service links for this appointment
            $this->db->where('appointment_id', $data['appointment_id']);
            $this->db->delete(db_prefix() . 'appointly_appointment_services');

            // Then create a new link
            $this->link_appointment_service($data['appointment_id'], $data['service_id']);
        }

        // Handle custom fields
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            handle_custom_fields_post($data['appointment_id'], $custom_fields);
        }
        // Handle Google Calendar integration
        if (isset($data['google_event_id']) && appointlyGoogleAuth()) {
            // If appointment is in Google Calendar, update it
            updateAppointmentToGoogleCalendar(array_merge($data, [
                'id'         => $data['appointment_id'],
                'date'       => $updateData['date'],
                'start_hour' => $updateData['start_hour'],
                'end_hour'   => $updateData['end_hour'],
            ]));
        }

        return true;
    }

    /**
     * Get single service by ID
     *
     * @param  int  $service_id  Service ID
     *
     * @return object|null Service object or null if not found
     */
    public function get_service($service_id)
    {
        if (! $service_id) {
            return null;
        }

        $this->db->select('*');
        $this->db->from(db_prefix() . 'appointly_services');
        $this->db->where('id', $service_id);

        return $this->db->get()->row();
    }

    /**
     * Delete appointment
     *
     * @param $id
     *
     * @return array
     */
    public function delete_appointment($id)
    {
        // For Google two-way sync appointments that are not in database (direct Google event ID)
        if (! is_numeric($id)) {
            if (appointlyGoogleAuth()) {
                $this->load->model('googlecalendar');
                $this->googlecalendar->deleteEvent($id);

                // Check if there's a matching event in the database to delete
                $this->db->where('google_event_id', $id);
                $appointment = $this->db->get(db_prefix() . 'appointly_appointments')->row_array();

                if ($appointment) {
                    // Now delete the database record with the actual appointment ID
                    return $this->delete_appointment($appointment['id']);
                }

                return [
                    'success' => true,
                    'message' => _l('appointment_deleted'),
                ];
            }

            return [
                'success' => false,
                'message' => _l('appointment_delete_failed'),
            ];
        }

        // Regular appointment deletion logic
        $this->db->where('id', $id);
        $appointment = $this->db->get(db_prefix() . 'appointly_appointments')->row_array();

        if (! $appointment) {
            return [
                'success' => false,
                'message' => _l('appointment_not_found'),
            ];
        }

        // Delete from Google Calendar if enabled and we have a Google event ID
        if (! empty($appointment['google_event_id']) && get_option('appointly_also_delete_in_google_calendar') && appointlyGoogleAuth()) {
            // Only delete from Google if current user added it or is admin
            if ($appointment['google_added_by_id'] == get_staff_user_id() || is_admin()) {
                $this->load->model('googlecalendar');
                $this->googlecalendar->deleteEvent($appointment['google_event_id']);
            }
        }

        // Delete related records from appointly_appointment_services table
        $this->db->where('appointment_id', $id);
        $this->db->delete(db_prefix() . 'appointly_appointment_services');

        // Delete attendees
        $this->atm->deleteAll($id);

        // Delete the appointment - apply correct permission logic
        $this->db->where('id', $id);

        // If not admin and has deleted permission, restrict to only appointments created by this user
        if (! is_admin() && staff_can('delete', 'appointments')) {
            $this->db->where('created_by', get_staff_user_id());
        } elseif (! staff_can('delete', 'appointments')) {
            // No delete permission at all
            return ['success' => false, 'message' => _l('access_denied')];
        }

        $this->db->delete(db_prefix() . 'appointly_appointments');

        if ($this->db->affected_rows() !== 0) {
            return [
                'success' => true,
                'message' => _l('appointment_deleted'),
            ];
        }

        return [
            'success' => false,
            'message' => _l('appointment_delete_failed'),
        ];
    }

    /**
     * Get today's appointments
     *
     * @return array
     */
    public function fetch_todays_appointments()
    {
        $date  = new DateTime();
        $today = $date->format('Y-m-d');

        $staffCanViewAllAppointments = staff_can('view', 'appointments');

        if (! $staffCanViewAllAppointments) {
            $this->db->where('id IN (SELECT appointment_id FROM ' . db_prefix() . 'appointly_attendees WHERE staff_id=' . get_staff_user_id() . ')');
        }

        $this->db->where('date', $today);

        if ($_SERVER['REQUEST_URI'] != '/admin/appointly/appointments') {
            $this->db->where('source !=', 'internal_staff');
        }

        $dbAppointments = $this->db->get(db_prefix() . 'appointly_appointments')->result_array();

        // Extract all Google event IDs from database appointments to avoid duplicates
        $googleEventIds = [];
        foreach ($dbAppointments as $appointment) {
            if (! empty($appointment['google_event_id'])) {
                $googleEventIds[] = $appointment['google_event_id'];
            }
        }
        appointlyGoogleAuth();

        $googleCalendarAppointments = [];

        $isGoogleAuthenticatedAndShowInTable = appointlyGoogleAuth() && get_option('appointments_googlesync_show_in_table');

        if ($isGoogleAuthenticatedAndShowInTable) {
            $googleCalendarAppointments = appointlyGetGoogleCalendarData();

            // Filter Google calendar appointments to only include today's appointments
            $googleCalendarAppointments = array_filter(
                $googleCalendarAppointments,
                static function ($appointment) use ($today, $googleEventIds) {
                    // Check if this is today's appointment
                    $appointmentDate = $appointment['date'] ?? '';
                    $isToday         = strpos($appointmentDate, $today) !== false || strpos($appointmentDate, (string) ($today)) !== false;

                    // Check if this appointment is not already in our database
                    $isNotInDatabase = empty($appointment['id']) && (! isset($appointment['google_event_id'])
                        || ! in_array($appointment['google_event_id'], $googleEventIds));

                    return $isToday && $isNotInDatabase;
                }
            );

            // Ensure each Google calendar appointment has source='google'
            foreach ($googleCalendarAppointments as &$appointment) {
                $appointment['source'] = 'google';
            }
        }

        return array_merge($dbAppointments, $googleCalendarAppointments);
    }

    /**
     * Get all appointment data for calendar event
     *
     * @param  string  $start
     * @param  string  $end
     * @param  array  $data
     *
     * @return array
     */
    public function get_calendar_data($start, $end, $data)
    {
        log_message('info', 'Appointly Calendar: Getting calendar data for ' . $start . ' to ' . $end);
        // Get appointments from database with left join to services table
        $this->db->select('a.*, s.name as service_name, s.color as service_color, s.duration as service_duration, a.status, st.firstname as provider_firstname, st.lastname as provider_lastname');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        $this->db->join(
            db_prefix() . 'appointly_services s',
            's.id = a.service_id',
            'left'
        );
        $this->db->join(
            db_prefix() . 'staff st',
            'st.staffid = a.provider_id',
            'left'
        );
        $this->db->where('a.status !=', 'cancelled');
        $this->db->where('a.status !=', 'completed');

        if (! is_client_logged_in()) {
            if (! is_admin()) {
                $this->db->where('(a.created_by=' . get_staff_user_id()
                    . ' OR a.id IN (SELECT appointment_id FROM '
                    . db_prefix()
                    . 'appointly_attendees WHERE staff_id='
                    . get_staff_user_id() . '))');
            }
        } else {
            $this->db->where(
                'a.id IN (SELECT appointment_id FROM ' . db_prefix() . 'appointly_attendees WHERE contact_id=' . get_contact_user_id() . ')'
            );
        }

        $this->db->where('(CONCAT(a.date, " ", a.start_hour) BETWEEN "' . $start . '" AND "' . $end . '")');

        $appointments = $this->db->get()->result_array();

        // Decode subject html entities before sending to calendar
        foreach ($appointments as &$appointment) {
            $appointment['subject'] = html_entity_decode($appointment['subject']);
        }

        // Collect Google event IDs from database to avoid duplicates
        $googleEventIds = [];
        foreach ($appointments as $appointment) {
            if (! empty($appointment['google_event_id'])) {
                $googleEventIds[] = $appointment['google_event_id'];
            }
        }

        // Get Google Calendar appointments if enabled
        $googleCalendarAppointments          = [];
        $isGoogleAuthenticatedAndShowInTable = appointlyGoogleAuth() && get_option('appointments_googlesync_show_in_table');

        if ($isGoogleAuthenticatedAndShowInTable) {
            $googleData = appointlyGetGoogleCalendarData();
            log_message('info', 'Appointly Calendar: Found ' . count($googleData) . ' Google Calendar events');

            // Filter out Google events that already exist in our database
            // and ensure they fall within the calendar date range
            foreach ($googleData as $googleEvent) {
                if (
                    (! isset($googleEvent['google_event_id']) || ! in_array($googleEvent['google_event_id'], $googleEventIds))
                    && isset($googleEvent['date'])
                    && strtotime($googleEvent['date']) >= strtotime($start)
                    && strtotime($googleEvent['date']) <= strtotime($end)
                ) {
                    $googleEvent['source']        = 'google';
                    $googleCalendarAppointments[] = $googleEvent;
                }
            }

            log_message(
                'info',
                'Appointly Calendar: Added ' . count($googleCalendarAppointments)
                    . ' unique Google Calendar events'
            );
        }

        // Merge database appointments with Google Calendar appointments
        $allAppointments = array_merge(
            $appointments,
            $googleCalendarAppointments
        );

        // Process each appointment for calendar display
        foreach ($allAppointments as $appointment) {
            // Prepare base appointment details for calendar
            $calendarEvent = [
                'id'     => $appointment['id'] ?? md5($appointment['google_event_id'] ?? uniqid('', true)),
                'title'  => $appointment['subject'] ?? $appointment['title'] ?? 'Untitled',
                'start'  => $appointment['date'] . ' ' . ($appointment['start_hour'] ?? '00:00:00'),
                'end'    => $appointment['date'] . ' ' . ($appointment['end_hour'] ?? '00:00:00'),
                'allDay' => false,
            ];

            // Base URL for viewing appointment
            if (is_client_logged_in()) {
                $calendarEvent['url'] = isset($appointment['hash'])
                    ? appointly_get_appointment_url($appointment['hash'])
                    : $appointment['google_calendar_link'] ?? '';
            } else {
                $calendarEvent['url'] = isset($appointment['id'])
                    ? admin_url('appointly/appointments/view?appointment_id=' . $appointment['id'])
                    : $appointment['google_calendar_link'] ?? '';
            }

            // Format appointment time
            $appointmentTime = isset($appointment['start_hour']) ?
                date('g:i A', strtotime($appointment['start_hour'])) : '';

            if (! empty($appointment['end_hour'])) {
                $appointmentTime .= ' - ' . date('g:i A', strtotime($appointment['end_hour']));
            }

            // Build enhanced tooltip content with beautiful HTML structure
            $tooltipContent = $this->buildEnhancedTooltip($appointment, $appointmentTime);

            // Determine color based on service or source
            if (! empty($appointment['service_color'])) {
                // Use service color from join
                $calendarEvent['color'] = $appointment['service_color'];
            } elseif (! empty($appointment['service_id'])) {
                // Fallback to service lookup
                $service = $this->get_service($appointment['service_id']);
                if ($service) {
                    $calendarEvent['color'] = $service->color;
                }
            } else {
                // Default colors based on source
                if (isset($appointment['source'])) {
                    switch ($appointment['source']) {
                        case 'google':
                            $calendarEvent['color'] = '#4285F4';
                            break;
                        case 'lead_related':
                            $calendarEvent['color'] = '#F4B400';
                            break;
                        case 'internal_staff':
                            $calendarEvent['color'] = '#34A853';
                            break;
                        default:
                            $calendarEvent['color'] = '#28B8DA';
                    }
                } else {
                    $calendarEvent['color'] = '#28B8DA';
                }
            }

            $calendarEvent['_tooltip'] = $tooltipContent;
            $calendarEvent['appointlyData'] = $appointment; // Store full appointment data for enhanced tooltips
            $data[]                    = $calendarEvent;
        }

        log_message('info', 'Appointly Calendar: Returning ' . count($data) . ' total calendar events');

        return $data;
    }

    /**
     * Build enhanced tooltip content with beautiful HTML structure
     */
    private function buildEnhancedTooltip($appointment, $appointmentTime)
    {
        $title = $appointment['subject'] ?? $appointment['title'] ?? 'Untitled';

        // Build a simple text-only tooltip for the title attribute
        $tooltip = $title;

        // Add service info
        if (!empty($appointment['service_name'])) {
            $tooltip .= "\n" . $appointment['service_name'];
        }

        // Add time info
        if (!empty($appointmentTime)) {
            $tooltip .= "\n" . $appointmentTime;
            if (!empty($appointment['duration'])) {
                $tooltip .= ' (' . $appointment['duration'] . ' min)';
            }
        }

        // Add provider info
        if (!empty($appointment['provider_firstname']) && !empty($appointment['provider_lastname'])) {
            $tooltip .= "\nProvider: " . $appointment['provider_firstname'] . ' ' . $appointment['provider_lastname'];
        }

        // Add status
        if (!empty($appointment['status'])) {
            $tooltip .= "\nStatus: " . ucfirst(str_replace('-', ' ', $appointment['status']));
        }

        // Add contact info
        if (!empty($appointment['email'])) {
            $tooltip .= "\nEmail: " . $appointment['email'];
        }
        if (!empty($appointment['phone'])) {
            $tooltip .= "\nPhone: " . $appointment['phone'];
        }

        // Add client name
        if (!empty($appointment['name'])) {
            $tooltip .= "\nClient: " . $appointment['name'];
        }

        return $tooltip;
    }

    /**
     * Get Bootstrap badge class for appointment status
     */
    private function getBootstrapStatusClass($status)
    {
        switch ($status) {
            case 'completed':
                return 'success';
            case 'pending':
                return 'warning';
            case 'cancelled':
            case 'no-show':
                return 'danger';
            case 'in-progress':
                return 'primary';
            default:
                return 'secondary';
        }
    }

    /**
     * Fetch contact data and apply to fields in modal
     *
     * @param  string  $contact_id
     *
     * @param        $is_lead
     *
     * @return mixed
     */
    public function apply_contact_data($contact_id, $is_lead)
    {
        if ($is_lead == 'false' || ! $is_lead) {
            return $this->clients_model->get_contact($contact_id);
        }

        $this->load->model('leads_model');

        return $this->leads_model->get($contact_id);
    }

    /**
     * Cancel appointment
     *
     * @param  string  $appointment_id
     *
     * @return void
     */
    public function cancel_appointment($appointment_id)
    {
        $success = $this->change_appointment_status($appointment_id, 'cancelled');

        if ($success) {
            $appointment    = $this->get_appointment_data($appointment_id);
            $notified_users = [];
            $attendees      = $appointment['attendees'];

            foreach ($attendees as $staff) {
                if ($staff['staffid'] === get_staff_user_id()) {
                    continue;
                }
                add_notification([
                    'description' => 'appointment_is_cancelled',
                    'touserid'    => $staff['staffid'],
                    'fromcompany' => true,
                    'link'        => 'appointly/appointments/view?appointment_id=' . $appointment['id'],
                ]);

                $notified_users[] = $staff['staffid'];

                send_mail_template(
                    'appointly_appointment_notification_cancelled_to_staff',
                    'appointly',
                    array_to_object($appointment),
                    array_to_object($staff)
                );
            }

            pusher_trigger_notification(array_unique($notified_users));

            $template = mail_template(
                'appointly_appointment_notification_cancelled_to_contact',
                'appointly',
                array_to_object($appointment)
            );

            if (! empty($appointment['phone'])) {
                $merge_fields = $template->get_merge_fields();
                $this->app_sms->trigger(
                    APPOINTLY_SMS_APPOINTMENT_CANCELLED_TO_CLIENT,
                    $appointment['phone'],
                    $merge_fields
                );
            }

            @$template->send();
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }

    /**
     * Generate available time slots for a provider on a specific date with a specific service
     *
     * @param $appointment_id
     * @param $status
     *
     * @return bool Array of available time slots
     */
    public function change_appointment_status($appointment_id, $status)
    {
        // First, get the current appointment data
        $this->db->where('id', $appointment_id);
        $current = $this->db->get(db_prefix() . 'appointly_appointments')->row_array();

        $update_data = [
            'status'       => $status,
            'cancel_notes' => null,
        ];

        if ($status !== 'cancelled') {
            $update_data['cancel_notes'] = null;
        }

        $this->db->where('id', $appointment_id);
        $success = $this->db->update(db_prefix() . 'appointly_appointments', $update_data);

        // A valid update is successful even when the selected status is unchanged.
        return $success !== false;
    }

    /**
     * Approve appointment
     *
     * @param  string  $appointment_id
     *
     * @return bool
     */
    public function approve_appointment($appointment_id)
    {
        $appointment_id = (int) $appointment_id;
        if ($appointment_id <= 0) {
            return false;
        }

        $appointment = $this->db
            ->select('id, status')
            ->where('id', $appointment_id)
            ->get(db_prefix() . 'appointly_appointments')
            ->row_array();

        if (! $appointment) {
            log_message('error', 'Appointly approval failed: appointment #' . $appointment_id . ' was not found.');
            return false;
        }

        $this->db->trans_start();
        $this->db->where('id', $appointment_id);
        $updated = $this->db->update(db_prefix() . 'appointly_appointments', [
            'status' => 'approved',
            'cancel_notes' => null,
            'external_notification_date' => date('Y-m-d'),
        ]);
        $this->db->trans_complete();

        if ($updated === false || $this->db->trans_status() === false) {
            log_message('error', 'Appointly approval database update failed for appointment #' . $appointment_id . ': ' . $this->db->error()['message']);
            return false;
        }

        try {
            $this->appointment_approve_notification_and_sms_triggers($appointment_id);
        } catch (\Throwable $e) {
            log_message('error', 'Appointment #' . $appointment_id . ' was approved, but notification delivery failed: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Check for external client hash token
     *
     * @param  string  $hash
     *
     * @return bool|void
     */
    public function get_public_appointment_by_hash($hash)
    {
        $this->db->where('hash', $hash);
        $appointment = $this->db->get(db_prefix() . 'appointly_appointments')
            ->row_array();

        if ($appointment) {
            $appointment['feedbacks']
                = json_decode(get_option('appointly_default_feedbacks'));
            $appointment['selected_contact'] = $appointment['contact_id'];

            // Handle different appointment sources properly
            if (! empty($appointment['selected_contact'])) {
                if ($appointment['source'] == 'lead_related') {
                    // For lead-related appointments, contact_id actually contains the lead ID
                    $this->load->model('leads_model');
                    $lead = $this->leads_model->get($appointment['selected_contact']);

                    if ($lead) {
                        $appointment['details'] = [
                            'email' => $lead->email ?? '',
                            'phone' => $lead->phonenumber ?? '',
                            'full_name' => $lead->name ?? '',
                            'company_name' => $lead->company ?? '',
                            'userid' => null // Leads don't have userid like contacts
                        ];
                    }
                } elseif ($appointment['source'] == 'internal') {
                    // For internal appointments, use the contact details function
                    $appointment['details']
                        = get_appointment_contact_details($appointment['selected_contact']);
                }
                // For external, internal_staff, google, and other sources,
                // we don't need to fetch additional details as they're already in the appointment data
            }
            $appointment['attendees'] = $this->atm->get($appointment['id']);

            return $appointment;
        }

        return false;
    }

    /**
     * Marks appointment as finished
     *
     * @param $id
     *
     * @return void
     */
    public function mark_as_finished($id)
    {
        $success = $this->change_appointment_status($id, 'completed');

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }

    /**
     * Marks appointment as ongoing
     *
     * @param $id
     *
     * @return boolean
     */
    public function mark_as_ongoing($id)
    {
        try {
            // First, notify users about the status change
            $this->appointment_approve_notification_and_sms_triggers($id);
        } catch (Exception $e) {
            // Log the error but continue with status change
            log_message('error', 'Error in appointment notifications: ' . $e->getMessage());
        }

        // Update the appointment status
        $success = $this->change_appointment_status($id, 'in-progress');

        // Explicitly log the operation result
        log_message(
            'info',
            'Appointment status change attempted - ID: ' . $id . ', Success: '
                . ($success ? 'true' : 'false')
        );

        return $success;
    }

    /**
     * Mark appointment as no-show
     *
     * @param  int  $id
     *
     * @return bool
     */
    public function mark_as_no_show($id)
    {
        $success = $this->change_appointment_status($id, 'no-show');

        // Explicitly log the operation result
        log_message(
            'info',
            'Appointment marked as no-show - ID: ' . $id . ', Success: ' . ($success
                ? 'true' : 'false')
        );

        return $success;
    }

    /**
     * Handles appointment cancellation and updates status
     *
     * @param  string  $hash
     * @param  string  $notes
     *
     * @return array
     */
    public function appointment_cancellation_handler($hash, $notes)
    {
        // Get appointment data first for notifications
        $this->db->where('hash', $hash);
        $appointment = $this->db->get(db_prefix() . 'appointly_appointments')->row_array();

        if (!$appointment) {
            return [
                'response' => [
                    'message' => _l('appointment_not_found'),
                    'success' => false,
                ],
            ];
        }

        // Only set cancel_notes - do NOT change status to cancelled
        // This creates a "pending cancellation" that staff must approve
        $this->db->where('hash', $hash);
        $this->db->update(db_prefix() . 'appointly_appointments', [
            'cancel_notes' => $notes,
            // Status remains unchanged - staff will approve/deny the cancellation
        ]);

        if ($this->db->affected_rows() !== 0) {
            // Send notifications to staff about the cancellation request
            $this->send_client_cancellation_request_notifications($appointment, $notes);

            return [
                'response' => [
                    'message' => _l('appointments_thank_you_cancel_request'),
                    'success' => true,
                ],
            ];
        }

        return [
            'response' => [
                'message' => _l('appointment_error_occurred'),
                'success' => false,
            ],
        ];
    }

    /**
     * Send notifications when client requests cancellation
     * @param array $appointment
     * @param string $reason
     */
    private function send_client_cancellation_request_notifications($appointment, $reason)
    {
        try {
            // Load the appointly helper
            $CI = &get_instance();
            $CI->load->helper(['appointly/appointly']);

            // Get appointment full data with attendees
            $appointment_data = $this->get_appointment_data($appointment['id']);
            if (!$appointment_data) {
                return;
            }

            $client_name = !empty($appointment['name']) ? $appointment['name'] : 'Client';
            $appointment_subject = !empty($appointment['subject']) ? $appointment['subject'] : 'Appointment';

            // Get all relevant staff to notify
            $staff_to_notify = [];
            $notifications_to_send = [];

            // 1. Appointment creator
            if (!empty($appointment['created_by'])) {
                $creator = appointly_get_staff($appointment['created_by']);
                if ($creator) {
                    $staff_to_notify[$creator['staffid']] = $creator;
                }
            }

            // 2. Provider
            if (!empty($appointment['provider_id']) && $appointment['provider_id'] != $appointment['created_by']) {
                $provider = appointly_get_staff($appointment['provider_id']);
                if ($provider) {
                    $staff_to_notify[$provider['staffid']] = $provider;
                }
            }

            // 3. All attendees
            if (!empty($appointment_data['attendees'])) {
                foreach ($appointment_data['attendees'] as $attendee) {
                    if (!isset($staff_to_notify[$attendee['staffid']])) {
                        $staff_to_notify[$attendee['staffid']] = $attendee;
                    }
                }
            }

            // Send notifications to all relevant staff
            foreach ($staff_to_notify as $staff) {
                // Add notification
                add_notification([
                    'description' => _l('appointment_cancellation_requested'),
                    'touserid'    => $staff['staffid'],
                    'fromcompany' => true,
                    'link'        => 'appointly/appointments/view?appointment_id=' . $appointment['id'],
                ]);

                $notifications_to_send[] = $staff['staffid'];
            }

            // Send live notifications
            if (!empty($notifications_to_send)) {
                pusher_trigger_notification($notifications_to_send);
            }

            // Log the notification
            log_message('info', 'Client cancellation request notifications sent for appointment ID: ' . $appointment['id'] . ' to ' . count($staff_to_notify) . ' staff members');
        } catch (Exception $e) {
            log_message('error', 'Error sending client cancellation request notifications: ' . $e->getMessage());
        }
    }

    /**
     * Check if cancellation is in progress already
     *
     * @param [appointment hash] $hash
     *
     * @return array
     */
    public function check_if_user_requested_appointment_cancellation($hash)
    {
        $this->db->select('cancel_notes');
        $this->db->where('hash', $hash);

        return $this->db->get(db_prefix() . 'appointly_appointments')
            ->row_array()
        ;
    }

    /**
     * Send appointment early reminders
     *
     * @param  string|int  $appointment_id
     *
     * @return bool
     */
    public function send_appointment_early_reminders($appointment_id)
    {
        $appointment = $this->get_appointment_data($appointment_id);

        // Early validation
        if (! $appointment) {
            return false;
        }

        // Don't send reminders for cancelled or completed appointments
        if ($appointment['status'] == 'cancelled' || $appointment['status'] == 'completed') {
            return false;
        }

        // Ensure appointment data doesn't have null values that could cause str_replace errors
        $appointment = array_map(function ($value) {
            return $value ?? '';
        }, $appointment);

        $staff_to_notify = [];

        // Send notifications to staff attendees
        foreach ($appointment['attendees'] as $staff) {
            if (! $staff['staffid']) {
                continue;
            }

            // do not notify the creator
            if ($staff['staffid'] == $appointment['created_by']) {
                continue;
            }

            $staff_to_notify[] = $staff['staffid'];

            //  notification
            add_notification([
                'description' => 'appointment_you_have_new_appointment',
                'touserid'    => $staff['staffid'],
                'fromcompany' => true,
                'link'        => 'appointly/appointments/view?appointment_id=' . $appointment_id,
            ]);

            // Only send email if staff has valid email
            if (! filter_var($staff['email'], FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Ensure all staff fields are not null to prevent str_replace errors
            $staff_safe = array_map(function ($value) {
                return $value ?? '';
            }, $staff);

            send_mail_template(
                'appointly_appointment_cron_reminder_to_staff',
                'appointly',
                array_to_object($appointment),
                array_to_object($staff_safe)
            );
        }

        // Send notification to contact if by_email is enabled
        if (
            ! empty($appointment['email']) && isset($appointment['by_email'])
            && $appointment['by_email'] == 1
        ) {
            $template = mail_template(
                'appointly_appointment_cron_reminder_to_contact',
                'appointly',
                array_to_object($appointment)
            );

            $template->send();
        }

        // Send SMS to contact if by_sms is enabled
        if (
            isset($appointment['by_sms']) && $appointment['by_sms'] == 1
            && ! empty($appointment['phone'])
        ) {
            // Trigger SMS
            $this->app_sms->trigger(
                APPOINTLY_SMS_APPOINTMENT_APPOINTMENT_REMINDER_TO_CLIENT,
                $appointment['phone'],
                $template->get_merge_fields()
            );
        }

        // Trigger notifications for staff
        if (! empty($staff_to_notify)) {
            pusher_trigger_notification($staff_to_notify);
        }

        return true;
    }

    /**
     * Handles the request for new appointment feedback
     *
     * @param  string  $appointment_id
     *
     * @return void
     */
    public function request_appointment_feedback($appointment_id)
    {
        $appointment = $this->get_appointment_data($appointment_id);

        $success = false;

        if (is_array($appointment) && ! empty($appointment)) {
            send_mail_template(
                'appointly_appointment_request_feedback',
                'appointly',
                array_to_object($appointment)
            );
            $success = true;
        }
        echo json_encode(['success' => $success]);
    }

    /**
     * Handles new feedback
     *
     * @param  string  $id
     * @param  string  $feedback
     * @param  string  $comment
     *
     * @return bool
     */
    public function handle_feedback_post($id, $feedback, $comment = null)
    {
        $data           = ['feedback' => $feedback];
        $notified_users = [];
        $appointment    = $this->apm->get_appointment_data($id);

        if (! $appointment || ! is_array($appointment)) {
            return false;
        }

        // Ensure all required fields exist
        $appointment['subject'] = $appointment['subject'] ?? _l('appointment_feedback_label');
        $appointment['email']   = $appointment['email'] ?? '';
        $appointment['name']    = $appointment['name'] ?? '';

        $tmp_name = 'appointly_appointment_feedback_received';
        $tmp_lang = 'appointment_new_feedback_added';

        if ($appointment['feedback'] !== null) {
            $tmp_name = 'appointly_appointment_feedback_updated';
            $tmp_lang = 'appointly_feedback_updated';
        }

        // Notify organizer (created_by)
        if (!empty($appointment['created_by'])) {
            $admin_staff = appointly_get_staff($appointment['created_by']);

            if ($admin_staff && is_array($admin_staff)) {
                send_mail_template(
                    $tmp_name,
                    'appointly',
                    array_to_object($appointment),
                    array_to_object($admin_staff)
                );

                add_notification([
                    'description' => $tmp_lang,
                    'touserid'    => $admin_staff['staffid'],
                    'fromcompany' => true,
                    'link'        => 'appointly/appointments/view?appointment_id=' . $id,
                ]);

                $notified_users[] = $admin_staff['staffid'];
            }
        }

        // Notify provider (if different from organizer)
        if (!empty($appointment['provider_id']) && $appointment['provider_id'] != $appointment['created_by']) {
            $provider_staff = appointly_get_staff($appointment['provider_id']);

            if ($provider_staff && is_array($provider_staff)) {
                mail_template(
                    $tmp_name,
                    'appointly',
                    array_to_object($appointment),
                    array_to_object($provider_staff)
                );

                add_notification([
                    'description' => $tmp_lang,
                    'touserid'    => $provider_staff['staffid'],
                    'fromcompany' => true,
                    'link'        => 'appointly/appointments/view?appointment_id=' . $id,
                ]);

                $notified_users[] = $provider_staff['staffid'];
            }
        }

        // Send pusher notifications to all notified users
        if (!empty($notified_users)) {
            pusher_trigger_notification($notified_users);
        }

        if ($comment !== null) {
            $data['feedback_comment'] = $comment;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'appointly_appointments', $data);

        return $this->db->affected_rows() !== 0;
    }

    /**
     * Inserts new event to Outlook calendar in database
     *
     * @param  array  $data
     *
     * @return bool
     */
    public function insert_new_outlook_event($data)
    {
        $last_appointment_id = $this->db->get(db_prefix()
            . 'appointly_appointments')
            ->last_row()->id;
        $this->db->where('id', $last_appointment_id);
        $this->db->update(
            db_prefix() . 'appointly_appointments',
            [
                'outlook_event_id'      => $data['outlook_event_id'],
                'outlook_calendar_link' => $data['outlook_calendar_link'],
                'outlook_added_by_id'   => get_staff_user_id(),
            ]
        );

        return true;
    }

    /**
     * Inserts new event to outlook calendar
     *
     * @param  array  $data
     *
     * @return bool
     */
    public function update_outlook_event($data)
    {
        $this->db->where('id', $data['appointment_id']);
        $this->db->update(
            db_prefix() . 'appointly_appointments',
            [
                'outlook_event_id'      => $data['outlook_event_id'],
                'outlook_calendar_link' => $data['outlook_calendar_link'],
                'outlook_added_by_id'   => get_staff_user_id(),
            ]
        );

        return $this->db->affected_rows() !== 0;
    }

    /**
     * Handles sending custom email to client
     *
     * @param  array  $data
     *
     * @return bool
     */
    public function send_google_meet_request_email($data)
    {
        $this->load->model('emails_model');
        $attendees = isset($data['attendees']) && !empty($data['attendees'])
            ? (is_array($data['attendees']) ? $data['attendees'] : json_decode($data['attendees'], true))
            : [];
        $message   = $data['message'];
        $subject   = _l('appointment_connect_via_google_meet');

        $sent_emails = [];
        $failed_emails = [];

        // Always send to the primary recipient (client/lead/external contact)
        $primary_sent = false;
        if (!empty($data['to'])) {
            $primary_sent = $this->emails_model->send_simple_email(
                $data['to'],
                $subject,
                $message
            );

            if ($primary_sent) {
                $sent_emails[] = $data['to'];

                // Log activity for primary recipient
                log_activity('Google Meet Invitation Sent [' . $subject . '] to primary recipient: ' . $data['to']);
            } else {
                $failed_emails[] = $data['to'];
                log_activity('Failed to send Google Meet Invitation [' . $subject . '] to primary recipient: ' . $data['to']);
            }
        }
        // Send to attendees only if requested and available
        $attendees_sent = 0;
        if (is_array($attendees) && count($attendees) > 0) {
            $current_staff = appointly_get_staff(get_staff_user_id());
            $current_staff_email = $current_staff ? $current_staff['email'] : '';

            foreach ($attendees as $attendee_email) {
                // Don't send to current staff member (they're sending the email)
                if ($attendee_email !== $current_staff_email && !empty($attendee_email)) {
                    $attendee_sent = $this->emails_model->send_simple_email(
                        $attendee_email,
                        $subject,
                        $message
                    );

                    if ($attendee_sent) {
                        $sent_emails[] = $attendee_email;
                        $attendees_sent++;

                        // Log activity for each attendee
                        log_activity('Google Meet Invitation Sent [' . $subject . '] to attendee: ' . $attendee_email);
                    } else {
                        $failed_emails[] = $attendee_email;
                        log_activity('Failed to send Google Meet Invitation [' . $subject . '] to attendee: ' . $attendee_email);
                    }
                }
            }
        }

        // Log summary activity
        $total_sent = count($sent_emails);
        $total_failed = count($failed_emails);

        if ($total_sent > 0) {
            log_activity('Google Meet Invitation Campaign: ' . $total_sent . ' emails sent successfully' .
                ($total_failed > 0 ? ', ' . $total_failed . ' failed' : ''));
        }

        // Return detailed results
        return [
            'success' => $primary_sent || $attendees_sent > 0,
            'primary_sent' => $primary_sent,
            'attendees_sent' => $attendees_sent,
            'total_sent' => $total_sent,
            'sent_emails' => $sent_emails,
            'failed_emails' => $failed_emails,
            'message' => $total_sent > 0 ?
                'Google Meet invitation sent to ' . $total_sent . ' recipient(s)' :
                'Failed to send Google Meet invitation'
        ];
    }

    /**
     * Get all services
     */
    public function get_services()
    {
        $servicesTable = db_prefix() . 'appointly_services';

        $this->db->select('s.*, COUNT(DISTINCT ss.staff_id) as provider_count');
        $this->db->from($servicesTable . ' s');
        $this->db->where('s.active', 1);
        // LEFT JOIN is required so newly-created services remain visible even
        // before a provider is assigned to them.
        $this->db->join(
            db_prefix() . 'appointly_service_staff ss',
            's.id = ss.service_id AND ss.is_provider = 1',
            'left'
        );
        $this->db->group_by('s.id');

        // The module must still load if an earlier migration did not create
        // sort_order yet.
        if ($this->db->field_exists('sort_order', $servicesTable)) {
            $this->db->order_by('s.sort_order', 'ASC');
        }
        $this->db->order_by('s.id', 'ASC');

        return $this->db->get()->result_array();
    }

    public function get_appointment($id)
    {
        if (! $id) {
            return null;
        }

        // First get the basic appointment data without the join that's causing errors
        $this->db->select('a.*, s.name as service_name, s.duration as service_duration, s.color as service_color');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        $this->db->join(
            db_prefix() . 'appointly_services s',
            's.id = a.service_id',
            'left'
        );
        $this->db->where('a.id', $id);

        $appointment = $this->db->get()->row_array();

        if ($appointment) {
            // Get service providers separately
            $service_providers = [];
            if (
                isset($appointment['service_id'])
                && ! empty($appointment['service_id'])
            ) {
                $this->db->select('staff_id');
                $this->db->from(db_prefix() . 'appointly_service_staff');
                $this->db->where('service_id', $appointment['service_id']);
                $providers_query = $this->db->get();

                if ($providers_query && $providers_query->num_rows() > 0) {
                    foreach ($providers_query->result_array() as $provider) {
                        $service_providers[] = $provider['staff_id'];
                    }
                }
            }

            // Get attendees (staff assigned to this appointment)
            $this->db->select('staff_id');
            $this->db->from(db_prefix() . 'appointly_attendees');
            $this->db->where('appointment_id', $id);
            $assigned_providers = $this->db->get()->result_array();

            // Get assigned provider IDs as a simple array
            $assigned_provider_ids = array_column(
                $assigned_providers,
                'staff_id'
            );

            // Include the creator in assigned providers if not already included
            if (! in_array(
                $appointment['created_by'],
                $assigned_provider_ids
            )) {
                $assigned_provider_ids[] = $appointment['created_by'];
            }

            // Merge everything into the appointment array
            $appointment = array_merge($appointment, [
                'appointment_id'    => $appointment['id'],
                'service_providers' => $service_providers,
                // Available providers for the service
                'selected_staff'    => $assigned_provider_ids,
                // Currently assigned providers
                'created_by'        => $appointment['created_by'],
                // Keep original creator
            ]);

            return $appointment;
        }

        return null;
    }

    public function getBusyTimes()
    {
        $this->db->select('date, start_hour, service_id');
        $this->db->from(db_prefix() . 'appointly_appointments');
        $this->db->where('status !=', 'cancelled');
        $this->db->where('status !=', 'completed');
        $this->db->where('date >=', date('Y-m-d'));

        $appointments = $this->db->get()->result_array();

        $busy_times = [];
        foreach ($appointments as $appointment) {
            $service  = $this->service_model->get($appointment['service_id']);
            $duration = $service ? $service->duration : 60;

            $start_time = strtotime($appointment['date'] . ' '
                . $appointment['start_hour']);
            $end_time   = strtotime("+{$duration} minutes", $start_time);

            $busy_times[] = [
                'date'       => $appointment['date'],
                'start_hour' => $appointment['start_hour'],
                'end_hour'   => date('H:i', $end_time),
            ];
        }

        return $busy_times;
    }

    /**
     * Get appointments by date with filters
     *
     * @param  array  $params  Filter parameters
     *
     * @return array
     */
    public function get_appointments_by_date($params = [])
    {
        $this->db->select('a.*, s.name as service_name, s.duration as service_duration');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        $this->db->join(
            db_prefix() . 'appointly_services s',
            's.id = a.service_id',
            'left'
        );

        // Apply filters
        if (isset($params['where'])) {
            foreach ($params['where'] as $key => $value) {
                if (is_array($value)) {
                    $this->db->where_in('a.' . $key, $value);
                } else {
                    $this->db->where('a.' . $key, $value);
                }
            }
        }

        // Date range filter
        if (isset($params['start_date'])) {
            $this->db->where('a.date >=', $params['start_date']);
        }
        if (isset($params['end_date'])) {
            $this->db->where('a.date <=', $params['end_date']);
        }

        // Staff filter
        if (isset($params['staff_id'])) {
            $this->db->where('a.created_by', $params['staff_id']);
        }

        // Service filter
        if (isset($params['service_id'])) {
            $this->db->where('a.service_id', $params['service_id']);
        }

        // Order by date and start hour
        $this->db->order_by('a.date', 'ASC');
        $this->db->order_by('a.start_hour', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get appointment timezone info
     *
     * @param  array  $appointment
     *
     * @return array
     */
    public function get_appointment_timezone_info($appointment)
    {
        if (empty($appointment['timezone'])) {
            return get_option('default_timezone');
        }

        try {
            $timezone = new DateTimeZone($appointment['timezone']);
            $date     = new DateTime('now', $timezone);

            return [
                'timezone' => $appointment['timezone'],
                'offset'   => $date->format('P'),
                'abbr'     => $date->format('T'),
            ];
        } catch (Exception $e) {
            log_message(
                'error',
                'Timezone info fetch failed: ' . $e->getMessage()
            );

            return [
                'timezone' => get_option('default_timezone'),
                'offset'   => '+00:00',
                'abbr'     => 'UTC',
            ];
        }
    }

    /**
     * Save appointment description
     *
     * @param  integer  $appointment_id
     * @param  string  $description
     *
     * @return boolean
     */
    public function save_appointment_description($appointment_id, $description)
    {
        $this->db->where('id', $appointment_id);

        return $this->db->update(
            db_prefix() . 'appointly_appointments',
            ['description' => $description]
        );
    }

    public function save_outlook_event_id(
        $appointment_id,
        $outlook_event_id,
        $outlook_calendar_link,
        $staff_id
    ) {
        return $this->db->update(db_prefix() . 'appointly_appointments', [
            'outlook_event_id'      => $outlook_event_id,
            'outlook_calendar_link' => $outlook_calendar_link,
            'outlook_added_by_id'   => $staff_id,
        ], ['id' => $appointment_id]);
    }

    /**
     * Get pending cancellation requests
     *
     * @return array
     */
    public function get_pending_cancellations()
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'appointly_appointments');
        $this->db->where('cancel_notes IS NOT NULL');
        $this->db->where('status !=', 'cancelled');
        $this->db->where('status !=', 'completed');
        $this->db->order_by('date', 'desc');

        $appointments = $this->db->get()->result_array();

        foreach ($appointments as &$appointment) {
            //  related contact/staff info
            if (! empty($appointment['contact_id'])) {
                $this->load->model('clients_model');
                $contact
                    = $this->clients_model->get_contact($appointment['contact_id']);
                if ($contact) {
                    $appointment['contact_name']  = $contact->firstname . ' '
                        . $contact->lastname;
                    $appointment['contact_email'] = $contact->email;
                }
            }

            //  service info if exists
            if (! empty($appointment['service_id'])) {
                $service = $this->get_service($appointment['service_id']);
                if ($service) {
                    $appointment['service_name'] = $service->name;
                }
            }
        }

        return $appointments;
    }

    /**
     * Get pending reschedule requests
     *
     * @return array
     */
    public function get_pending_reschedules()
    {
        $this->db->select('rr.*, a.subject, a.date, a.start_hour, a.end_hour, a.contact_id, a.email, a.name, a.service_id');
        $this->db->from(db_prefix() . 'appointly_reschedule_requests rr');
        $this->db->join(db_prefix() . 'appointly_appointments a', 'a.id = rr.appointment_id');
        $this->db->where('rr.status', 'pending');
        $this->db->order_by('rr.requested_at', 'DESC');

        $reschedules = $this->db->get()->result_array();

        foreach ($reschedules as &$reschedule) {
            // Add related contact/staff info
            if (!empty($reschedule['contact_id'])) {
                $this->load->model('clients_model');
                $contact = $this->clients_model->get_contact($reschedule['contact_id']);
                if ($contact) {
                    $reschedule['contact_name'] = $contact->firstname . ' ' . $contact->lastname;
                    $reschedule['contact_email'] = $contact->email;
                }
            }

            // Add service info if exists
            if (!empty($reschedule['service_id'])) {
                $service = $this->get_service($reschedule['service_id']);
                if ($service) {
                    $reschedule['service_name'] = $service->name;
                }
            }
        }

        return $reschedules;
    }

    /**
     * Create a reschedule request
     *
     * @param  int  $appointment_id  The appointment ID
     * @param  string  $requested_date  The requested date
     * @param  string  $requested_time  The requested time
     * @param  string  $reason  The reason for the reschedule request
     *
     * @return int  The ID of the created reschedule request
     */
    public function create_reschedule_request($appointment_id, $requested_date, $requested_time, $reason = null)
    {
        $data = [
            'appointment_id' => $appointment_id,
            'requested_date' => $requested_date,
            'requested_time' => $requested_time,
            'reason' => $reason,
            'status' => 'pending',
            'requested_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert(db_prefix() . 'appointly_reschedule_requests', $data);
        return $this->db->insert_id();
    }

    /**
     * Approve a reschedule request
     *
     * @param  int  $reschedule_id  The reschedule request ID
     * @param  int  $staff_id  The staff ID approving the request
     *
     * @return boolean
     */
    public function approve_reschedule_request($reschedule_id, $staff_id)
    {
        // Get the reschedule request details
        $reschedule = $this->db->get_where(db_prefix() . 'appointly_reschedule_requests', ['id' => $reschedule_id])->row_array();
        if (!$reschedule) {
            return false;
        }

        // Update the original appointment with new date/time
        $this->db->where('id', $reschedule['appointment_id']);
        $this->db->update(db_prefix() . 'appointly_appointments', [
            'date' => $reschedule['requested_date'],
            'start_hour' => $reschedule['requested_time']
        ]);

        // Mark reschedule request as approved
        $this->db->where('id', $reschedule_id);
        $this->db->update(db_prefix() . 'appointly_reschedule_requests', [
            'status' => 'approved',
            'processed_by' => $staff_id,
            'processed_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    }

    /**
     * Deny a reschedule request
     *
     * @param  int  $reschedule_id  The reschedule request ID
     * @param  int  $staff_id  The staff ID denying the request
     * @param  string  $denial_reason  The reason for denial
     *
     * @return boolean
     */
    public function deny_reschedule_request($reschedule_id, $staff_id, $denial_reason = null)
    {
        $this->db->where('id', $reschedule_id);
        $this->db->update(db_prefix() . 'appointly_reschedule_requests', [
            'status' => 'denied',
            'processed_by' => $staff_id,
            'processed_at' => date('Y-m-d H:i:s'),
            'denial_reason' => $denial_reason
        ]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Get a reschedule request by ID
     *
     * @param  int  $reschedule_id  The reschedule request ID
     *
     * @return array|null
     */
    public function get_reschedule_request($reschedule_id)
    {
        $this->db->select('rr.*, a.subject, a.date, a.start_hour, a.end_hour, a.contact_id, a.email, a.name, a.service_id');
        $this->db->from(db_prefix() . 'appointly_reschedule_requests rr');
        $this->db->join(db_prefix() . 'appointly_appointments a', 'a.id = rr.appointment_id');
        $this->db->where('rr.id', $reschedule_id);

        return $this->db->get()->row_array();
    }

    /**
     * Check if an appointment has a pending reschedule request
     *
     * @param  int  $appointment_id  The appointment ID to check
     *
     * @return boolean
     */
    public function has_pending_reschedule($appointment_id)
    {
        $this->db->where('appointment_id', $appointment_id);
        $this->db->where('status', 'pending');
        return $this->db->count_all_results(db_prefix() . 'appointly_reschedule_requests') > 0;
    }

    /**
     * Check if a staff member is busy on a specific date and time
     *
     * @param  int  $staff_id  The staff ID to check
     * @param  string  $date  The date to check (Y-m-d format)
     * @param  string  $start_hour  The start time (H:i format)
     * @param  string  $end_hour  The end time (H:i format)
     *
     * @return boolean
     */
    public function staff_is_busy($staff_id, $date, $start_hour, $end_hour)
    {
        $this->db->select('a.id, a.status, a.date, a.start_hour, a.end_hour');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        $this->db->join(
            db_prefix() . 'appointly_attendees att',
            'att.appointment_id = a.id'
        );
        $this->db->where('att.staff_id', $staff_id);
        $this->db->where('a.date', $date);
        $this->db->where('a.status !=', 'cancelled');
        $this->db->where('a.status !=', 'completed');
        $this->db->where("(
            (a.start_hour <= '$start_hour' AND a.end_hour > '$start_hour') OR
            (a.start_hour < '$end_hour' AND a.end_hour >= '$end_hour') OR
            (a.start_hour >= '$start_hour' AND a.end_hour <= '$end_hour')
        )");

        return $this->db->count_all_results() > 0;
    }

    /**
     * Get busy times for a specific service
     *
     * @param  int  $service_id  The service ID to get busy times for
     *
     * @return array
     */
    public function get_busy_times_by_service($service_id)
    {
        // Clean and validate service_id
        $service_id = $this->db->escape_str($service_id);

        $this->db->select('a.id AS appointment_id, a.date, a.start_hour, a.end_hour, a.provider_id, a.duration, a.status');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        $this->db->where('a.service_id', $service_id);
        $this->db->where('a.status !=', 'cancelled');
        $this->db->where('a.status !=', 'completed');

        // Only include dates from today onwards
        $today = date('Y-m-d');
        $this->db->where('a.date >=', $today);

        $appointments = $this->db->get()->result_array();

        $busy_times = [];

        foreach ($appointments as $appointment) {
            $busy_times[] = [
                'appointment_id' => $appointment['appointment_id'],
                'date'           => $appointment['date'],
                'start_hour'     => $appointment['start_hour'],
                'end_hour'       => $appointment['end_hour'],
                'provider_id'    => $appointment['provider_id'],
                'duration'       => $appointment['duration'],
            ];
        }

        return $busy_times;
    }

    /**
     * Get appointment feedback
     *
     * @param  int  $appointment_id
     *
     * @return array
     */
    public function get_appointment_feedback($appointment_id)
    {
        $this->db->select('feedback, comment');
        $this->db->from(db_prefix() . 'appointly_appointments');
        $this->db->where('id', $appointment_id);
        $this->db->where('feedback IS NOT NULL');
        $this->db->where('comment IS NOT NULL');
        $result = $this->db->get()->row_array();

        return $result;
    }

    /**
     * Get company schedule for each day of the week
     *
     * @return array Company schedule by weekday
     */
    public function get_company_schedule()
    {
        $this->db->order_by('FIELD(weekday, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")');
        $result = $this->db->get(db_prefix() . 'appointly_company_schedule')
            ->result_array();

        // Convert to associative array with weekday as key
        $schedule = [];
        foreach ($result as $day) {
            $schedule[$day['weekday']] = [
                'start_time' => $day['start_time'],
                'end_time'   => $day['end_time'],
                'is_enabled' => (bool) $day['is_enabled'],
            ];
        }

        return $schedule;
    }

    /**
     * Get staff working hours for a specific staff member
     *
     * @param  int  $staff_id  Staff ID
     *
     * @return array Working hours by day
     */
    public function get_staff_working_hours($staff_id)
    {
        $this->db->where('staff_id', $staff_id);
        $query         = $this->db->get(db_prefix()
            . 'appointly_staff_working_hours');
        $working_hours = [];

        // Return empty array if no hours set
        if ($query->num_rows() === 0) {
            return $working_hours;
        }

        foreach ($query->result_array() as $day) {
            $working_hours[$day['weekday']] = [
                'weekday'              => $day['weekday'],
                'start_time'           => $day['start_time'],
                'end_time'             => $day['end_time'],
                'enabled'              => (bool) $day['is_available'],
                'is_available'         => (bool) $day['is_available'],
                'use_company_schedule' => isset($day['use_company_schedule'])
                    ? (bool) $day['use_company_schedule'] : false,
            ];
        }

        return $working_hours;
    }

    /**
     * Get available time slots for a provider on a specific date for a service
     *
     * @param  int  $provider_id  Staff ID
     * @param  string  $date  Date in Y-m-d format
     * @param  int  $service_id  Service ID
     * @param  string  $timezone  User's timezone (optional)
     * @param  int  $exclude_appointment_id  Optional ID of appointment being edited
     *
     * @return array Array of available time slots
     */
    public function get_available_time_slots(
        $provider_id,
        $date,
        $service_id,
        $timezone = null,
        $exclude_appointment_id = null
    ) {
        // Basic validation
        if (empty($date)) {
            return [];
        }

        // Ensure date is in Y-m-d format
        $formatted_date = $date;
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            // Try various date formats
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                // d-m-Y format
                $parts          = explode('-', $date);
                $formatted_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                // mm/dd/yyyy format
                $parts          = explode('/', $date);
                $formatted_date = $parts[2] . '-' . $parts[0] . '-' . $parts[1];
            } elseif (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
                // dd.mm.yyyy format
                $parts          = explode('.', $date);
                $formatted_date = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            } else {
                // Last resort - try general parsing
                $timestamp = strtotime($date);
                if ($timestamp === false) {
                    return [];
                }
                $formatted_date = date('Y-m-d', $timestamp);
            }

            // Validate the new format
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $formatted_date)) {
                return [];
            }
        }

        // Get day of week
        $day_of_week = date('l', strtotime($formatted_date));

        // Get service details
        $service = $this->get_service($service_id);
        if (! $service) {
            log_message('error', "Service not found: {$service_id}");

            return [];
        }

        $duration      = $service->duration;
        $buffer_before = property_exists($service, 'buffer_before')
            ? $service->buffer_before : 0;
        $buffer_after  = property_exists($service, 'buffer_after')
            ? $service->buffer_after : 0;

        // Get company schedule first as we might need it for fallback
        $this->db->where('weekday', $day_of_week);
        $company_schedule = $this->db->get(db_prefix()
            . 'appointly_company_schedule')
            ->row();

        // If company doesn't have schedule for this day at all, then no availability
        if (! $company_schedule) {
            return [];
        }

        // If company schedule is disabled for this day, no availability
        if (! $company_schedule->is_enabled) {
            return [];
        }

        // Get staff working hours - if they exist
        $this->db->where('staff_id', $provider_id);
        $this->db->where('weekday', $day_of_week);
        $staff_hours = $this->db->get(db_prefix()
            . 'appointly_staff_working_hours')->row();

        // Determine which schedule to use:
        // 1. If staff has hours and use_company_schedule=1 - use company schedule regardless of availability
        // 2. If staff has hours, use_company_schedule=0 and is_available=0 - no availability
        // 3. If staff has hours, use_company_schedule=0 and is_available=1 - use staff hours
        // 4. If staff has no hours set for this day - fall back to company schedule

        $use_company_schedule = false;

        if (! $staff_hours) {
            // No staff hours set for this day - use company schedule
            $use_company_schedule = true;
        } else {
            // Check if using company schedule is enabled - this takes priority over availability
            if (
                isset($staff_hours->use_company_schedule)
                && $staff_hours->use_company_schedule == 1
            ) {
                $use_company_schedule = true;
            } else {
                // Staff has hours set and isn't using company schedule - check if available
                if ($staff_hours->is_available == 0) {
                    // Staff explicitly marked as not available and not using company schedule

                    return [];
                } else {
                    // Staff is available and using their own hours
                    $use_company_schedule = false;
                }
            }
        }

        // Set start and end times based on which schedule to use
        if ($use_company_schedule) {
            $start_time = $company_schedule->start_time;
            $end_time   = $company_schedule->end_time;
        } else {
            // When staff has custom hours, ensure we're using them correctly
            if ($staff_hours) {
                $start_time = $staff_hours->start_time;
                $end_time   = $staff_hours->end_time;
            } else {
                // This shouldn't happen based on our logic above, but as a fallback
                $start_time = $company_schedule->start_time;
                $end_time   = $company_schedule->end_time;
            }
        }

        // Get existing appointments for this provider on this date, excluding the current appointment if provided
        $busy_times = $this->get_busy_times_by_staff(
            $provider_id,
            $formatted_date,
            $exclude_appointment_id
        );

        // Generate available slots
        $slots         = [];
        $slot_interval = 15; // 15-minute intervals

        // Convert start and end times to timestamps
        $start_timestamp = strtotime($formatted_date . ' ' . $start_time);
        $end_timestamp   = strtotime($formatted_date . ' ' . $end_time);

        if (! $start_timestamp || ! $end_timestamp) {
            return [];
        }

        $current_time = time();

        // Handle timezone adjustment if needed
        if ($timezone && $timezone != get_option('default_timezone')) {
            $system_tz = new DateTimeZone(get_option('default_timezone'));
            $user_tz   = new DateTimeZone($timezone);

            // Calculate timezone offset for today
            $system_dt      = new DateTime('now', $system_tz);
            $user_dt        = new DateTime('now', $user_tz);
            $offset_seconds = $system_tz->getOffset($system_dt)
                - $user_tz->getOffset($user_dt);

            // Adjust timestamps for timezone differences
            $start_timestamp -= $offset_seconds;
            $end_timestamp   -= $offset_seconds;
            $current_time    -= $offset_seconds;
        }

        // Loop through the time slots
        $slot_time = $start_timestamp;
        while ($slot_time + ($duration * 60) <= $end_timestamp) {
            $slot_end_time = $slot_time + ($duration * 60);

            // Add buffer times
            $slot_start_with_buffer = $slot_time - ($buffer_before * 60);
            $slot_end_with_buffer   = $slot_end_time + ($buffer_after * 60);

            // Check if slot with buffers conflicts with any busy times
            $available = true;
            foreach ($busy_times as $busy) {
                $busy_start = strtotime($formatted_date . ' '
                    . $busy['start_hour']);
                $busy_end   = strtotime($formatted_date . ' ' . $busy['end_hour']);

                // Add buffer times to busy slot if it has them
                if (isset($busy['buffer_before']) && $busy['buffer_before']) {
                    $busy_start -= ($busy['buffer_before'] * 60);
                }
                if (isset($busy['buffer_after']) && $busy['buffer_after']) {
                    $busy_end += ($busy['buffer_after'] * 60);
                }

                // Check if there's an overlap
                if (
                    ($slot_start_with_buffer >= $busy_start
                        && $slot_start_with_buffer < $busy_end)
                    || // Start time is within busy period
                    ($slot_end_with_buffer > $busy_start
                        && $slot_end_with_buffer <= $busy_end)
                    || // End time is within busy period
                    ($slot_start_with_buffer <= $busy_start
                        && $slot_end_with_buffer
                        >= $busy_end) // Slot completely encompasses busy period
                ) {
                    $available = false;

                    break;
                }
            }

            // Check if this time slot is in the past for today's date
            if ($formatted_date === date('Y-m-d')) {
                $current_datetime = time();

                if ($slot_time <= $current_datetime) {
                    $available = false;
                }
            }

            if ($available) {
                // Format the time values for display
                $start_formatted   = date('H:i', $slot_time);
                $end_formatted     = date('H:i', $slot_end_time);
                $display_formatted = date('H:i', $slot_time) . ' - ' . date('H:i', $slot_end_time);

                // Include all required keys to satisfy both frontend and controller expectations
                $slots[] = [
                    // Keys expected by the controller
                    'start'          => $start_formatted,
                    'end'            => $end_formatted,
                    'formatted_time' => $display_formatted,
                    'available'      => true,

                    // Keys for frontend compatibility
                    'value'          => $start_formatted,
                    'text'           => $display_formatted,
                    'end_time'       => $end_formatted,
                ];
            }

            // Move to next slot
            $slot_time += ($slot_interval * 60);
        }

        return $slots;
    }

    /**
     * Get busy times for a staff member for a specific date
     *
     * @param  int  $staff_id
     * @param  string  $date  Date in Y-m-d format (optional, if null will get all future dates)
     * @param  int  $exclude_appointment_id  Exclude this appointment when finding busy times (for editing)
     *
     * @return array Array of busy times
     */
    public function get_busy_times_by_staff(
        $staff_id,
        $date = null,
        $exclude_appointment_id = null
    ) {
        if (! $staff_id) {
            return [];
        }

        // Start with the basic query
        $this->db->select('a.id, a.service_id, a.date, a.start_hour, a.end_hour, s.buffer_before, s.buffer_after')
            ->from(db_prefix() . 'appointly_appointments a')
            ->join(
                db_prefix() . 'appointly_services s',
                'a.service_id = s.id',
                'left'
            )
            ->where('a.provider_id', $staff_id)
            ->where('a.status !=', 'cancelled')
            ->order_by('a.date', 'ASC')
            ->order_by('a.start_hour', 'ASC')
        ;

        // Add date constraint if provided
        if ($date) {
            $this->db->where('a.date', $date);
        } else {
            $this->db->where('a.date >=', date('Y-m-d'));
        }

        // Exclude the appointment being edited
        if ($exclude_appointment_id) {
            $this->db->where('a.id !=', $exclude_appointment_id);
        }

        // Execute the query
        $busy_times = $this->db->get()->result_array();

        return $busy_times;
    }

    /**
     * Save staff working hours
     *
     * @param  int  $staff_id  Staff ID
     * @param  array  $working_hours  Array of working hours by day
     *
     * @return bool
     */
    public function save_staff_working_hours($staff_id, $working_hours)
    {
        // Validate staff_id
        $staff_id = (int) $staff_id;
        if (! $staff_id) {
            log_message('error', 'Appointly - Invalid staff_id: ' . $staff_id);

            return false;
        }

        // Start transaction
        $this->db->trans_begin();

        try {
            // First, delete existing working hours for this staff
            $this->db->where('staff_id', $staff_id);
            $this->db->delete(db_prefix() . 'appointly_staff_working_hours');

            // Format and validate working hours data
            $valid_days  = [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday',
            ];
            $insert_data = [];

            foreach ($valid_days as $day) {
                // Initialize default values
                $day_data = [
                    'staff_id'             => $staff_id,
                    'weekday'              => $day,
                    'start_time'           => '09:00:00',
                    'end_time'             => '17:00:00',
                    'is_available'         => 0, // Default to unavailable
                    'use_company_schedule' => 0,
                    // Default to not using company schedule
                ];

                // Update with posted values if available
                if (isset($working_hours[$day])) {
                    $hours = $working_hours[$day];

                    // Check if is_available was explicitly set to 1
                    // This is critical - checkbox values need special handling
                    if (
                        isset($hours['is_available'])
                        && ($hours['is_available'] == 1
                            || $hours['is_available'] === true
                            || $hours['is_available'] === 'on')
                    ) {
                        $day_data['is_available'] = 1;
                    }

                    // Check if use_company_schedule was explicitly set
                    if (
                        isset($hours['use_company_schedule'])
                        && ($hours['use_company_schedule'] == 1
                            || $hours['use_company_schedule'] === true
                            || $hours['use_company_schedule'] === 'on')
                    ) {
                        $day_data['use_company_schedule'] = 1;
                    }

                    // Update times if provided
                    if (! empty($hours['start_time'])) {
                        // Format time properly with seconds
                        $start_time
                            = $this->format_time_with_seconds($hours['start_time']);
                        if ($start_time) {
                            $day_data['start_time'] = $start_time;
                        }
                    }

                    if (! empty($hours['end_time'])) {
                        // Format time properly with seconds
                        $end_time
                            = $this->format_time_with_seconds($hours['end_time']);
                        if ($end_time) {
                            $day_data['end_time'] = $end_time;
                        }
                    }
                }

                $insert_data[] = $day_data;
            }

            if (! empty($insert_data)) {
                $result = $this->db->insert_batch(
                    db_prefix()
                        . 'appointly_staff_working_hours',
                    $insert_data
                );

                if (! $result) {
                    log_message('error', 'Appointly - Batch insert failed: '
                        . $this->db->error()['message']);
                    $this->db->trans_rollback();

                    return false;
                }
            }

            $this->db->trans_commit();

            // Verify the insert worked by counting rows
            $this->db->where('staff_id', $staff_id);
            $count = $this->db->count_all_results(db_prefix()
                . 'appointly_staff_working_hours');

            return $count === 7; // We should have exactly 7 days
        } catch (Exception $e) {
            $this->db->trans_rollback();

            return false;
        }
    }

    /**
     * Format time with seconds
     * Ensures time strings always include seconds (HH:MM:SS)
     *
     * @param  string  $time  Time string (HH:MM or HH:MM:SS)
     *
     * @return string Time with seconds (HH:MM:SS)
     */
    private function format_time_with_seconds($time)
    {
        if (empty($time)) {
            return '00:00:00';
        }

        // If time already has seconds, return as is
        if (substr_count($time, ':') == 2) {
            return $time;
        }

        // Otherwise add seconds
        return $time . ':00';
    }

    /**
     * Save company schedule
     *
     * @param  array  $schedule  Day => hour settings
     *
     * @return bool
     */
    public function save_company_schedule($schedule)
    {
        // First, clear the existing schedule
        $this->db->truncate(db_prefix() . 'appointly_company_schedule');

        // Format and validate schedule data
        $valid_days = [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];

        $insert_data = [];
        foreach ($valid_days as $day) {
            if (isset($schedule[$day])) {
                $day_data = $schedule[$day];

                // Check for value "1" for is_enabled (checkbox checked)
                $enabled = isset($day_data['is_enabled'])
                    && $day_data['is_enabled'] == 1;

                // Default values
                $start_time = '09:00';
                $end_time   = '17:00';

                // Only use submitted times if they are set
                if (! empty($day_data['start_time'])) {
                    // Convert to 15-minute interval if needed
                    $start_time
                        = $this->round_time_to_15_min($day_data['start_time']);
                }

                if (! empty($day_data['end_time'])) {
                    // Convert to 15-minute interval if needed
                    $end_time
                        = $this->round_time_to_15_min($day_data['end_time']);
                }

                $insert_data[] = [
                    'weekday'    => $day,
                    'start_time' => $start_time,
                    'end_time'   => $end_time,
                    'is_enabled' => $enabled ? 1 : 0,
                ];
            } else {
                // If day not provided in schedule, add with default values
                $insert_data[] = [
                    'weekday'    => $day,
                    'start_time' => '09:00',
                    'end_time'   => '17:00',
                    'is_enabled' => 0,
                ];
            }
        }

        // Insert the schedule
        return $this->db->insert_batch(
            db_prefix() . 'appointly_company_schedule',
            $insert_data
        );
    }

    /**
     * Round a time value to the nearest 15-minute interval
     *
     * @param  string  $time  Time in HH:MM or HH:MM:SS format
     *
     * @return string Time in HH:MM format rounded to nearest 15 minutes
     */
    private function round_time_to_15_min($time)
    {
        // Extract hours and minutes
        $parts   = explode(':', $time);
        $hours   = (int) $parts[0];
        $minutes = isset($parts[1]) ? (int) $parts[1] : 0;

        // Round minutes to nearest 15
        $rounded_minutes = round($minutes / 15) * 15;

        // Handle case where minutes round to 60
        if ($rounded_minutes == 60) {
            $hours++;
            $rounded_minutes = 0;
        }

        // Format and return
        return sprintf('%02d:%02d', $hours, $rounded_minutes);
    }

    /**
     * Update service providers
     *
     * @param  int  $service_id
     * @param  array  $provider_ids
     * @param  int  $primary_provider_id  Primary provider ID (optional)
     *
     * @return bool
     */
    public function update_service_providers(
        $service_id,
        $provider_ids,
        $primary_provider_id = null
    ) {
        // Delete existing provider relationships
        $this->db->where('service_id', $service_id);
        $this->db->delete(db_prefix() . 'appointly_service_staff');

        // Ensure we have an array of providers
        if (! is_array($provider_ids)) {
            $provider_ids = [$provider_ids];
        }

        // If no primary provider is set but we have providers, set the first one as primary
        if (! $primary_provider_id && ! empty($provider_ids)) {
            $primary_provider_id = $provider_ids[0];
        }

        // Insert provider relationships
        foreach ($provider_ids as $staff_id) {
            $this->db->insert(db_prefix() . 'appointly_service_staff', [
                'service_id'  => $service_id,
                'staff_id'    => $staff_id,
                'is_provider' => 1,
                'is_primary'  => ($staff_id == $primary_provider_id) ? 1 : 0,
            ]);
        }

        return true;
    }

    /**
     * Get busy times for a specific service and staff combination
     *
     * @param  int  $service_id  Service ID
     * @param  int  $staff_id  Staff ID
     * @param  string  $date  Optional specific date (Y-m-d format)
     *
     * @return array Array of busy times with buffer considerations
     */
    public function get_busy_times_by_service_and_staff(
        $service_id,
        $staff_id,
        $date = null
    ) {
        // Clean and validate inputs
        $service_id = $this->db->escape_str($service_id);
        $staff_id   = $this->db->escape_str($staff_id);

        // Get service details for buffer times
        $service = $this->get_service($service_id);
        if (! $service) {
            return [];
        }

        $default_buffer_before = property_exists($service, 'buffer_before')
            ? $service->buffer_before : 0;
        $default_buffer_after  = property_exists($service, 'buffer_after')
            ? $service->buffer_after : 0;

        $busy_times = [];
        $today      = date('Y-m-d');

        // 1. Get appointments where the staff is a provider
        $this->db->select('a.id AS appointment_id, a.date, a.start_hour, a.end_hour, a.duration, a.buffer_before, a.buffer_after, a.service_id');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        $this->db->where('a.provider_id', $staff_id);
        $this->db->where('a.status !=', 'cancelled');

        // Filter by date if provided
        if ($date) {
            $this->db->where('a.date', $date);
        } else {
            // Only include dates from today onwards
            $this->db->where('a.date >=', $today);
        }

        $provider_appointments = $this->db->get()->result_array();

        // 2. Get appointments where the staff is an attendee
        $this->db->select('a.id AS appointment_id, a.date, a.start_hour, a.end_hour, a.duration, a.buffer_before, a.buffer_after, a.service_id');
        $this->db->from(db_prefix() . 'appointly_appointments a');
        $this->db->join(
            db_prefix() . 'appointly_attendees att',
            'a.id = att.appointment_id',
            'inner'
        );
        $this->db->where('att.staff_id', $staff_id);
        $this->db->where('a.status !=', 'cancelled');

        if ($date) {
            $this->db->where('a.date', $date);
        } else {
            $this->db->where('a.date >=', $today);
        }

        $attendee_appointments = $this->db->get()->result_array();

        // Combine both sets of appointments (avoiding duplicates)
        $all_appointments = array_merge(
            $provider_appointments,
            $attendee_appointments
        );
        $processed_ids    = [];

        foreach ($all_appointments as $appointment) {
            // Skip if we've already processed this appointment
            if (in_array((int)$appointment['appointment_id'], $processed_ids, true)) {
                continue;
            }

            $processed_ids[] = (int)$appointment['appointment_id'];

            // Ensure we have end_hour (calculate if needed)
            $end_hour = $appointment['end_hour'];
            if (empty($end_hour) && ! empty($appointment['start_hour'])) {
                // Try to get duration from appointment
                $duration = $appointment['duration'];

                // If no duration but service_id exists, try to get from service
                if (! $duration && ! empty($appointment['service_id'])) {
                    $appt_service
                        = $this->get_service($appointment['service_id']);
                    if ($appt_service) {
                        $duration = $appt_service->duration;
                    }
                }

                // Default to 60 minutes if no duration found
                if (! $duration) {
                    $duration = 60;
                }

                $start_time = strtotime($appointment['start_hour']);
                $end_time   = $start_time + ($duration * 60);
                $end_hour   = date('H:i:s', $end_time);
            }

            // Get buffer times (either from appointment or defaults from service)
            $buffer_before = $appointment['buffer_before'] ?? $default_buffer_before;

            $buffer_after = $appointment['buffer_after'] ?? $default_buffer_after;

            $busy_times[] = [
                'appointment_id' => $appointment['appointment_id'],
                'date'           => $appointment['date'],
                'start_hour'     => $appointment['start_hour'],
                'end_hour'       => $end_hour,
                'buffer_before'  => $buffer_before,
                'buffer_after'   => $buffer_after,
            ];
        }

        return $busy_times;
    }

    /**
     * Get busy times for a specific provider on a specific date
     *
     * @param  int  $provider_id  The provider ID
     * @param  string  $date  The date in YYYY-MM-DD format
     *
     * @return array Array of busy time slots
     */
    public function get_busy_times_by_date($provider_id, $date)
    {
        if (! $provider_id || ! $date) {
            return [];
        }

        // Sanitize inputs
        $provider_id = $this->db->escape_str($provider_id);
        $date        = $this->db->escape_str($date);

        // Check if buffer columns exist in services table
        $buffer_columns_exist = $this->db->field_exists('buffer_before', db_prefix() . 'appointly_services') &&
            $this->db->field_exists('buffer_after', db_prefix() . 'appointly_services');

        // Build select clause based on available columns
        if ($buffer_columns_exist) {
            $select_clause = 'a.id, a.date, a.start_hour, a.end_hour, a.service_id, s.buffer_before, s.buffer_after';
        } else {
            $select_clause = 'a.id, a.date, a.start_hour, a.end_hour, a.service_id, 0 as buffer_before, 0 as buffer_after';
        }

        // Query appointments where the staff is the provider
        $this->db->select($select_clause)
            ->from(db_prefix() . 'appointly_appointments a')
            ->join(
                db_prefix() . 'appointly_services s',
                'a.service_id = s.id',
                'left'
            )
            ->where('a.provider_id', $provider_id)
            ->where('a.date', $date)
            ->where('a.status !=', 'cancelled')
            ->where('a.status !=', 'completed')
        ;

        $provider_appointments = $this->db->get()->result_array();

        // Also get appointments where the staff is an attendee
        $this->db->select($select_clause)
            ->from(db_prefix() . 'appointly_appointments a')
            ->join(
                db_prefix() . 'appointly_attendees att',
                'a.id = att.appointment_id',
                'inner'
            )
            ->join(
                db_prefix() . 'appointly_services s',
                'a.service_id = s.id',
                'left'
            )
            ->where('att.staff_id', $provider_id)
            ->where('a.date', $date)
            ->where('a.status !=', 'cancelled')
            ->where('a.status !=', 'completed')
        ;

        $attendee_appointments = $this->db->get()->result_array();

        // Merge both sets of appointments (avoiding duplicates)
        $busy_times    = [];
        $processed_ids = [];

        foreach (
            array_merge($provider_appointments, $attendee_appointments) as
            $appointment
        ) {
            // Skip if we've already processed this appointment
            if (in_array((int)$appointment['id'], $processed_ids, true)) {
                continue;
            }

            $processed_ids[] = (int)$appointment['id'];
            $busy_times[]    = $appointment;
        }

        log_message(
            'debug',
            'Get busy times by date - Provider: ' . $provider_id . ', Date: ' . $date
                . ', Found: ' . count($busy_times) . ' appointments'
        );

        return $busy_times;
    }

    /**
     * Get appointments for a client
     * 
     * @param int $client_id 
     * @return array
     */
    public function get_client_appointments($client_id)
    {
        // Get client contact emails first to avoid collation issues
        $contact_emails = $this->db->select('email')
            ->where('userid', $client_id)
            ->get(db_prefix() . 'contacts')
            ->result_array();

        $emails = array_column($contact_emails, 'email');
        $emails = array_filter($emails); // Remove empty emails

        // Get appointments for specific client - include both internal and external appointments
        $this->db->select(db_prefix() . 'appointly_appointments.*');
        $this->db->from(db_prefix() . 'appointly_appointments');
        $this->db->where_in(db_prefix() . 'appointly_appointments.source', ['internal', 'external']);

        // Match appointments either by:
        // 1. contact_id (for internal appointments with existing contacts)
        // 2. email address (for external appointments)
        $this->db->group_start();
        $this->db->where(db_prefix() . 'appointly_appointments.contact_id IN (SELECT id FROM ' . db_prefix() . 'contacts WHERE userid = ' . $this->db->escape($client_id) . ')');
        if (!empty($emails)) {
            $this->db->or_where_in(db_prefix() . 'appointly_appointments.email', $emails);
        }
        $this->db->group_end();

        $this->db->order_by(db_prefix() . 'appointly_appointments.date', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Get appointments for a lead
     * 
     * @param int $lead_id 
     * @return array
     */
    public function get_lead_appointments($lead_id)
    {
        $this->db->select(db_prefix() . 'appointly_appointments.*');
        $this->db->from(db_prefix() . 'appointly_appointments');
        $this->db->where(db_prefix() . 'appointly_appointments.source', 'lead_related');
        $this->db->where(db_prefix() . 'appointly_appointments.contact_id', $lead_id);
        $this->db->order_by(db_prefix() . 'appointly_appointments.date', 'desc');
        return $this->db->get()->result_array();
    }

    /**
     * Get upcoming appointments with configurable date range
     *
     * @param string $range - '7_days', '14_days', '30_days', '4_weeks'
     * @return array
     */
    public function fetch_upcoming_appointments($range = '7_days')
    {
        $startDate = date('Y-m-d');
        $endDate = '';

        // Calculate end date based on range
        switch ($range) {
            case '7_days':
                $endDate = date('Y-m-d', strtotime('+7 days'));
                break;
            case '14_days':
                $endDate = date('Y-m-d', strtotime('+14 days'));
                break;
            case '30_days':
                $endDate = date('Y-m-d', strtotime('+30 days'));
                break;
            case '4_weeks':
                $endDate = date('Y-m-d', strtotime('+4 weeks'));
                break;
            default:
                $endDate = date('Y-m-d', strtotime('+7 days'));
        }

        $staffCanViewAllAppointments = staff_can('view', 'appointments');

        $this->db->select('*');
        $this->db->from(db_prefix() . 'appointly_appointments');

        if (!$staffCanViewAllAppointments) {
            $this->db->where('(created_by=' . get_staff_user_id()
                . ' OR id IN (SELECT appointment_id FROM ' . db_prefix() . 'appointly_attendees WHERE staff_id=' . get_staff_user_id() . '))');
        }

        $this->db->where('date >=', $startDate);
        $this->db->where('date <=', $endDate);
        $this->db->where('status !=', 'cancelled');
        $this->db->where('status !=', 'completed');

        // Exclude internal staff appointments from widget
        if ($_SERVER['REQUEST_URI'] != '/admin/appointly/appointments') {
            $this->db->where('source !=', 'internal_staff');
        }

        $this->db->order_by('date', 'ASC');
        $this->db->order_by('start_hour', 'ASC');
        $this->db->limit(20); // Limit to 20 appointments to avoid overwhelming the widget

        $dbAppointments = $this->db->get()->result_array();

        // Extract all Google event IDs from database appointments to avoid duplicates
        $googleEventIds = [];
        foreach ($dbAppointments as $appointment) {
            if (!empty($appointment['google_event_id'])) {
                $googleEventIds[] = $appointment['google_event_id'];
            }
        }

        $googleCalendarAppointments = [];
        $isGoogleAuthenticatedAndShowInTable = appointlyGoogleAuth() && get_option('appointments_googlesync_show_in_table');

        if ($isGoogleAuthenticatedAndShowInTable) {
            $googleCalendarAppointments = appointlyGetGoogleCalendarData();

            // Filter Google calendar appointments to only include upcoming appointments in range
            $googleCalendarAppointments = array_filter(
                $googleCalendarAppointments,
                static function ($appointment) use ($startDate, $endDate, $googleEventIds) {
                    $appointmentDate = $appointment['date'] ?? '';
                    $isInRange = $appointmentDate >= $startDate && $appointmentDate <= $endDate;

                    // Check if this appointment is not already in our database
                    $isNotInDatabase = empty($appointment['id']) && (!isset($appointment['google_event_id'])
                        || !in_array($appointment['google_event_id'], $googleEventIds));

                    return $isInRange && $isNotInDatabase;
                }
            );

            // Ensure each Google calendar appointment has source='google'
            foreach ($googleCalendarAppointments as &$appointment) {
                $appointment['source'] = 'google';
            }
        }

        return array_merge($dbAppointments, $googleCalendarAppointments);
    }

    /**
     * Submit client cancellation request (used by logged-in clients)
     *
     * @param int $appointment_id
     * @param string $reason
     * @return bool
     */
    public function submit_client_cancellation_request($appointment_id, $reason)
    {
        // Get appointment data
        $appointment = $this->get_appointment($appointment_id);
        if (!$appointment) {
            return false;
        }

        // Update appointment with cancellation notes
        $this->db->where('id', $appointment_id);
        $this->db->update(db_prefix() . 'appointly_appointments', [
            'cancel_notes' => $reason,
        ]);

        if ($this->db->affected_rows() > 0) {
            // Send notifications to staff about the cancellation request
            $this->send_client_cancellation_request_notifications($appointment, $reason);
            return true;
        }

        return false;
    }

    /**
     * Submit client reschedule request (used by logged-in clients)
     *
     * @param int $appointment_id
     * @param string $new_date
     * @param string $new_time
     * @param string $reason
     * @param int $contact_id
     * @return bool
     */
    public function submit_client_reschedule_request($appointment_id, $new_date, $new_time, $reason, $contact_id)
    {
        // Get appointment data
        $appointment = $this->get_appointment($appointment_id);
        if (!$appointment) {
            return false;
        }

        // Create reschedule request
        $reschedule_id = $this->create_reschedule_request(
            $appointment_id,
            $new_date,
            $new_time,
            $reason
        );

        if ($reschedule_id) {
            // Send notification to staff (reuse existing method)
            $this->_send_reschedule_notification_to_staff($appointment, $new_date, $new_time, $reason);
            return true;
        }

        return false;
    }

    /**
     * Send reschedule notification to staff (shared method)
     *
     * @param array $appointment
     * @param string $new_date
     * @param string $new_time
     * @param string $reason
     */
    public function _send_reschedule_notification_to_staff($appointment, $new_date, $new_time, $reason)
    {
        // Get appointment attendees (assigned staff)
        $attendees = $this->get_appointment_attendees($appointment['id']);
        $notified_users = [];

        // Notify each assigned staff member
        foreach ($attendees as $staff) {
            // Skip if it's the current user (shouldn't happen in client context but safety check)
            if (function_exists('get_staff_user_id') && $staff['staffid'] === get_staff_user_id()) {
                continue;
            }

            // Add notification
            add_notification([
                'description' => 'appointment_reschedule_requested',
                'touserid'    => $staff['staffid'],
                'fromcompany' => true,
                'link'        => 'appointly/appointments/view?appointment_id=' . $appointment['id'],
            ]);

            $notified_users[] = $staff['staffid'];

            // Send email using the correct appointly pattern
            send_mail_template(
                'appointly_appointment_reschedule_request_to_staff',
                'appointly',
                array_to_object($appointment),
                array_to_object($staff)
            );
        }

        pusher_trigger_notification(array_unique($notified_users));

        log_activity('Appointment Reschedule Requested [AppointmentID: ' . $appointment['id'] . ', Requested Date: ' . $new_date . ' ' . $new_time . ', Reason: ' . $reason . ']');
    }
}

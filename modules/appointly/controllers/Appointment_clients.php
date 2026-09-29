<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Appointment_clients extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('appointly');
        $this->load->model('appointly/appointly_model', 'apm');
    }

    /**
     * Check if contact has appointments permission
     */
    private function check_appointments_permission()
    {
        if (!is_client_logged_in()) {
            redirect(site_url('authentication/login'));
        }

        if (!has_contact_permission('appointments')) {
            access_denied('appointments');
        }
    }

    /**
     * Default index method - redirects to appointments
     */
    public function appointments()
    {
        // Check permissions
        $this->check_appointments_permission();

        $client_id = get_client_user_id();
        $appointments = $this->apm->get_client_appointments($client_id);

        // Load models once
        $this->load->model('invoices_model');
        $this->load->model('staff_model');

        // Get all staff IDs at once for batch loading
        $staff_ids = [];
        foreach ($appointments as $appointment) {
            $staff_id = !empty($appointment['provider_id']) ? $appointment['provider_id'] : (!empty($appointment['staff_id']) ? $appointment['staff_id'] : null);
            if ($staff_id && !in_array($staff_id, $staff_ids)) {
                $staff_ids[] = $staff_id;
            }
        }

        // Batch load staff data
        $staff_data = [];
        if (!empty($staff_ids)) {
            foreach ($staff_ids as $staff_id) {
                $staff = $this->staff_model->get($staff_id);
                if ($staff) {
                    $staff_data[$staff_id] = $staff;
                }
            }
        }

        // Get pending reschedule requests for all appointments
        $appointment_ids = array_column($appointments, 'id');
        $pending_reschedules = [];
        if (!empty($appointment_ids)) {
            $this->db->select('appointment_id');
            $this->db->from(db_prefix() . 'appointly_reschedule_requests');
            $this->db->where_in('appointment_id', $appointment_ids);
            $this->db->where('status', 'pending');
            $reschedule_results = $this->db->get()->result_array();

            foreach ($reschedule_results as $reschedule) {
                $pending_reschedules[$reschedule['appointment_id']] = true;
            }
        }

        foreach ($appointments as &$appointment) {
            // Get service details
            if (!empty($appointment['service_id'])) {
                $service = $this->apm->get_service($appointment['service_id']);
                if ($service) {
                    $appointment['service_name'] = $service->name;
                    $appointment['service_color'] = $service->color;
                    $appointment['service_duration'] = $service->duration;
                    $appointment['service_price'] = $service->price;
                }
            }

            // Get invoice details if invoice_id exists
            if (!empty($appointment['invoice_id'])) {
                try {
                    $invoice = $this->invoices_model->get($appointment['invoice_id']);
                    if ($invoice && !empty($invoice->hash)) {
                        $appointment['invoice_hash'] = $invoice->hash;
                        $appointment['invoice_status'] = $invoice->status;
                        $appointment['invoice_total'] = $invoice->total;
                    } else {
                        // Invoice exists but missing hash - log error
                        log_message('error', 'Invoice #' . $appointment['invoice_id'] . ' missing hash for appointment #' . $appointment['id']);
                        unset($appointment['invoice_id']); // Don't show broken invoice link
                    }
                } catch (Exception $e) {
                    log_message('error', 'Error loading invoice #' . $appointment['invoice_id'] . ': ' . $e->getMessage());
                    unset($appointment['invoice_id']);
                }
            }

            // Get staff/provider name using cached data
            $staff_id = !empty($appointment['provider_id']) ? $appointment['provider_id'] : (!empty($appointment['staff_id']) ? $appointment['staff_id'] : null);
            if ($staff_id && isset($staff_data[$staff_id])) {
                $staff = $staff_data[$staff_id];
                $appointment['provider_name'] = $staff->firstname . ' ' . $staff->lastname;
            }

            // Add reschedule status
            $appointment['has_pending_reschedule'] = isset($pending_reschedules[$appointment['id']]) ? 1 : 0;

            // Add cancellation status
            $appointment['has_pending_cancellation'] = !empty($appointment['cancel_notes']) ? 1 : 0;
        }

        $data = [
            'appointments' => $appointments,
            'title' => _l('appointment_appointments')
        ];

        $this->data($data);
        $this->view('client_appointments_dashboard');
        $this->layout();
    }

    /**
     * View single appointment details for client
     */
    public function view_single()
    {
        // Check permissions first
        $this->check_appointments_permission();

        $id = $this->input->post('id');

        if (!$id) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => 'Invalid appointment ID']);
                return;
            } else {
                show_404();
            }
        }

        // Get appointment details
        $appointment = $this->apm->get_appointment($id);

        if (!$appointment) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => 'Appointment not found']);
                return;
            } else {
                show_404();
            }
        }

        // Verify this appointment belongs to the logged-in client
        $client_id = get_client_user_id();
        if (!$this->appointment_belongs_to_client($appointment, $client_id)) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                return;
            } else {
                show_404();
            }
        }

        // For AJAX requests, return JSON with HTML content
        if ($this->input->is_ajax_request()) {
            // Get service details
            $service = null;
            if (!empty($appointment['service_id'])) {
                $service = $this->apm->get_service($appointment['service_id']);
            }

            // Get staff details - check for provider_id first, then staff_id
            $staff = null;
            $staff_id = !empty($appointment['provider_id']) ? $appointment['provider_id'] : (!empty($appointment['staff_id']) ? $appointment['staff_id'] : null);

            if ($staff_id) {
                $this->load->model('staff_model');
                $staff = $this->staff_model->get($staff_id);
            }

            // Get latest reschedule request
            $this->db->select('*');
            $this->db->from(db_prefix() . 'appointly_reschedule_requests');
            $this->db->where('appointment_id', $appointment['id']);
            $this->db->order_by('requested_at', 'desc');
            $this->db->limit(1);
            $latest_reschedule = $this->db->get()->row_array();

            // Build HTML content
            $html = '<div class="row">';
            $html .= '<div class="col-md-12">';

            // Show cancellation status message
            if (!empty($appointment['cancel_notes'])) {
                $html .= '<div class="alert alert-warning">';
                $html .= '<i class="fa fa-exclamation-triangle"></i> ';
                $html .= _l('appointment_cancellation_pending_review');
                $html .= '<br><strong>' . _l('appointment_cancellation_reason') . ':</strong> ' . e($appointment['cancel_notes']);
                $html .= '</div>';
            }

            // Show reschedule status messages
            if ($latest_reschedule) {
                $reschedule_status = $latest_reschedule['status'];
                $processed_at = $latest_reschedule['processed_at'];
                $denial_reason = $latest_reschedule['denial_reason'];

                if ($reschedule_status === 'pending') {
                    $html .= '<div class="alert alert-info">';
                    $html .= '<i class="fa fa-clock"></i> ';
                    $html .= _l('appointment_reschedule_pending_review');
                    $html .= '</div>';
                } elseif ($reschedule_status === 'approved') {
                    $html .= '<div class="alert alert-success">';
                    $html .= '<i class="fa fa-check"></i> ';
                    $html .= _l('appointment_reschedule_approved');
                    if ($processed_at) {
                        $html .= ' <small>(' . _dt($processed_at) . ')</small>';
                    }
                    $html .= '</div>';
                } elseif ($reschedule_status === 'denied') {
                    $html .= '<div class="alert alert-danger">';
                    $html .= '<i class="fa fa-times"></i> ';
                    $html .= _l('appointment_reschedule_rejected');
                    if ($processed_at) {
                        $html .= ' <small>(' . _dt($processed_at) . ')</small>';
                    }
                    if ($denial_reason) {
                        $html .= '<br><strong>' . _l('appointment_reschedule_denial_reason') . ':</strong> ' . e($denial_reason);
                    }
                    if ($appointment['status'] === 'cancelled') {
                        $html .= '<br><strong><i class="fa fa-ban"></i> ' . _l('appointment_cancelled_title') . '</strong>';
                    }
                    $html .= '</div>';
                }
            }

            $html .= '<table class="table table-striped">';
            $html .= '<tr><td><strong>' . _l('appointment_subject') . ':</strong></td><td>' . e($appointment['subject']) . '</td></tr>';

            // Combine date and time with timezone
            $date_time = _d($appointment['date']) . ' ' . e($appointment['start_hour']);
            $timezone = get_option('default_timezone');
            if (!empty($timezone)) {
                $date_time .= ' (' . $timezone . ')';
            }
            $html .= '<tr><td><strong>' . _l('appointment_date_and_time') . ':</strong></td><td>' . $date_time . '</td></tr>';

            // Status with proper mapping like in dashboard
            $status = !empty($appointment['status']) ? $appointment['status'] : 'pending';
            $status_mapping = [
                'pending' => 'appointment_status_pending',
                'in-progress' => 'appointment_status_in-progress',
                'completed' => 'appointment_status_completed',
                'cancelled' => 'appointment_status_cancelled',
                'no-show' => 'appointment_status_no-show',
                'approved' => 'appointment_status_pending',
                'confirmed' => 'appointment_status_pending'
            ];
            $lang_key = isset($status_mapping[$status]) ? $status_mapping[$status] : 'appointment_status_pending';
            $status_text = _l($lang_key);
            if ($status_text === $lang_key) {
                $status_text = ucfirst(str_replace(['_', '-'], ' ', $status));
            }

            $html .= '<tr><td><strong>' . _l('appointment_status') . ':</strong></td><td>' . $status_text . '</td></tr>';

            if ($staff) {
                $staff_name = is_object($staff) ? $staff->firstname . ' ' . $staff->lastname : (is_array($staff) ? $staff['firstname'] . ' ' . $staff['lastname'] : 'Unknown');
                $html .= '<tr><td><strong>' . _l('appointment_provider') . ':</strong></td><td>' . e($staff_name) . '</td></tr>';
            }

            // Get and display attendees
            $attendees = $this->apm->get_appointment_attendees($appointment['id']);
            if (!empty($attendees)) {
                $attendee_names = [];
                foreach ($attendees as $attendee) {
                    $attendee_names[] = $attendee['firstname'] . ' ' . $attendee['lastname'];
                }
                $html .= '<tr><td><strong>' . _l('appointment_staff_attendees') . ':</strong></td><td>' . e(implode(', ', $attendee_names)) . '</td></tr>';
            }

            if ($service) {
                $service_name = is_object($service) ? $service->name : (is_array($service) ? $service['name'] : 'Unknown');
                $html .= '<tr><td><strong>' . _l('appointment_service') . ':</strong></td><td>' . e($service_name) . '</td></tr>';
                $html .= '<tr><td><strong>' . _l('appointment_service_price') . ':</strong></td><td>' . app_format_money($service->price, get_base_currency()) . '</td></tr>';
            }

            if (!empty($appointment['description'])) {
                $html .= '<tr><td><strong>' . _l('appointment_description') . ':</strong></td><td>' . nl2br(e($appointment['description'])) . '</td></tr>';
            }

            if (!empty($appointment['location'])) {
                $html .= '<tr><td><strong>' . _l('appointment_location') . ':</strong></td><td>' . nl2br(e($appointment['location'])) . '</td></tr>';
            }

            // Add invoice information display
            if (!empty($appointment['invoice_id'])) {
                $this->load->model('invoices_model');
                $invoice = $this->invoices_model->get($appointment['invoice_id']);
                if ($invoice) {
                    $invoice_number = format_invoice_number($appointment['invoice_id']);
                    $invoice_url = site_url('invoice/' . $invoice->id . '/' . $invoice->hash);
                    $html .= '<tr><td><strong>' . _l('invoice') . ':</strong></td><td>';
                    $html .= '<a href="' . $invoice_url . '" target="_blank" class="btn btn-sm btn-info">';
                    $html .= '<i class="fa fa-file-invoice"></i> ' . $invoice_number;
                    $html .= '</a>';

                    // Show invoice status
                    if ($invoice->status == 1) {
                        $html .= ' <span class="label label-warning">' . _l('invoice_status_unpaid') . '</span>';
                    } elseif ($invoice->status == 2) {
                        $html .= ' <span class="label label-success">' . _l('invoice_status_paid') . '</span>';
                    } elseif ($invoice->status == 6) {
                        $html .= ' <span class="label label-default">' . _l('invoice_status_draft') . '</span>';
                    }

                    // Show total amount
                    if (!empty($invoice->total)) {
                        $html .= '<br><small>' . _l('invoice_total') . ': ' . app_format_money($invoice->total, $invoice->currency_name) . '</small>';
                    }
                    $html .= '</td></tr>';
                }
            }

            $html .= '</table>';

            // Add calendar download button
            $html .= '<div class="tw-mt-4 tw-pt-4 tw-border-t tw-border-neutral-200">';
            $html .= '<a href="' . site_url('appointly/appointments_public/download_ics/' . $appointment['id']) . '" class="btn btn-default btn-sm" title="' . _l('appointment_download_ics_tooltip') . '" data-toggle="tooltip">';
            $html .= '<i class="fa-solid fa-calendar-plus"></i>';
            $html .= '</a>';
            $html .= '</div>';

            $html .= '</div>';
            $html .= '</div>';

            echo json_encode(['success' => true, 'html' => $html]);
            return;
        }
    }

    /**
     * Handle appointment cancellation request from client
     */
    public function cancel_request()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Check permissions
        $this->check_appointments_permission();

        $appointment_id = $this->input->post('appointment_id');
        $reason = $this->input->post('reason');

        // Get appointment and verify ownership
        $appointment = $this->apm->get_appointment($appointment_id);
        $client_id = get_client_user_id();

        if (!$appointment || !$this->appointment_belongs_to_client($appointment, $client_id)) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }

        // Check if appointment can be cancelled
        if (!in_array($appointment['status'], ['pending', 'in-progress'])) {
            echo json_encode(['success' => false, 'message' => 'Appointment cannot be cancelled']);
            return;
        }

        // Check if cancellation request already exists
        if (!empty($appointment['cancel_notes'])) {
            echo json_encode(['success' => false, 'message' => _l('appointment_cancellation_already_requested')]);
            return;
        }

        // Submit cancellation request
        $result = $this->apm->submit_client_cancellation_request($appointment_id, $reason);

        echo json_encode(['success' => $result]);
    }

    /**
     * Handle appointment reschedule request from client
     */
    public function reschedule_request()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Check permissions
        $this->check_appointments_permission();

        $appointment_id = $this->input->post('appointment_id');
        $new_date = $this->input->post('new_date');
        $new_time = $this->input->post('new_time');
        $reason = $this->input->post('reason');

        // Get appointment and verify ownership
        $appointment = $this->apm->get_appointment($appointment_id);
        $client_id = get_client_user_id();

        if (!$appointment || !$this->appointment_belongs_to_client($appointment, $client_id)) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }

        // Check if appointment can be rescheduled
        if (!in_array($appointment['status'], ['pending', 'in-progress'])) {
            echo json_encode(['success' => false, 'message' => 'Appointment cannot be rescheduled']);
            return;
        }

        // Check if there's already a pending reschedule request
        if ($this->apm->has_pending_reschedule($appointment_id)) {
            echo json_encode(['success' => false, 'message' => 'There is already a pending reschedule request for this appointment. Please wait for the previous request to be processed.']);
            return;
        }

        // Submit reschedule request
        $result = $this->apm->submit_client_reschedule_request($appointment_id, $new_date, $new_time, $reason, get_contact_user_id());

        echo json_encode(['success' => $result]);
    }

    /**
     * Get status label CSS class
     */
    private function get_status_label_class($status)
    {
        switch ($status) {
            case 'pending':
                return 'warning';
            case 'in-progress':
                return 'info';
            case 'completed':
                return 'success';
            case 'cancelled':
                return 'danger';
            case 'no-show':
                return 'default';
            default:
                return 'default';
        }
    }

    /**
     * Check if appointment belongs to client
     */
    private function appointment_belongs_to_client($appointment, $client_id)
    {
        // Check if appointment is linked to this client through contact_id
        if (isset($appointment['contact_id']) && !empty($appointment['contact_id'])) {
            $this->load->model('clients_model');
            $contact = $this->clients_model->get_contact($appointment['contact_id']);
            return $contact && $contact->userid == $client_id;
        }

        // Check if appointment email matches any of the client's contact emails
        if (isset($appointment['email']) && !empty($appointment['email'])) {
            $this->load->model('clients_model');
            $client_contacts = $this->clients_model->get_contacts($client_id);
            foreach ($client_contacts as $contact) {
                if (strtolower($contact['email']) === strtolower($appointment['email'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get blocked days for date picker
     */
    public function get_blocked_days()
    {
        // Check permissions
        $this->check_appointments_permission();

        header('Content-Type: application/json');

        try {
            $blocked_days = get_appointly_blocked_days();
            echo json_encode(['success' => true, 'blocked_days' => $blocked_days]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error loading blocked days']);
        }
    }

    /**
     * Get available times for reschedule
     */
    public function get_available_times()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Set content type to JSON and disable error reporting to prevent HTML in JSON response
        header('Content-Type: application/json');

        // Check permissions
        $this->check_appointments_permission();

        $appointment_id = $this->input->post('appointment_id');
        $date = $this->input->post('date');

        // Get appointment and verify ownership
        $appointment = $this->apm->get_appointment($appointment_id);
        $client_id = get_client_user_id();

        if (!$appointment || !$this->appointment_belongs_to_client($appointment, $client_id)) {
            echo json_encode(['success' => false, 'message' => 'Appointment not found']);
            return;
        }

        try {
            // Get service information
            $service = $this->apm->get_service($appointment['service_id']);
            if (!$service) {
                echo json_encode(['success' => false, 'message' => 'Service not found']);
                return;
            }

            // Get provider's working hours for this day
            $day_of_week = date('l', strtotime($date));
            $provider_id = $appointment['provider_id'];

            // Get working hours
            $working_hours = [];
            $provider_available = false;

            // Try to get staff working hours
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
                    isset($day_schedule['is_available'], $day_schedule['start_time']) &&
                    $day_schedule['is_available'] &&
                    isset($day_schedule['end_time'])
                ) {
                    $provider_available = true;
                    $working_hours['start_time'] = $day_schedule['start_time'];
                    $working_hours['end_time'] = $day_schedule['end_time'];
                }
            }

            // Fall back to company schedule if staff has NO custom hours set for this day
            if (!$provider_available && !$staff_has_custom_hours) {
                $company_schedule = $this->apm->get_company_schedule();
                if (isset($company_schedule[$day_of_week]) && $company_schedule[$day_of_week]['is_enabled']) {
                    $provider_available = true;
                    $working_hours['start_time'] = $company_schedule[$day_of_week]['start_time'];
                    $working_hours['end_time'] = $company_schedule[$day_of_week]['end_time'];
                }
            }

            // Check if provider is available for this day
            if (!$provider_available || !isset($working_hours['start_time'], $working_hours['end_time'])) {
                echo json_encode([
                    'success' => true,
                    'times' => [],
                    'date' => $date,
                    'message' => 'Provider not available'
                ]);
                return;
            }

            // Get busy times for the provider on this date (excluding current appointment)
            $busy_times = $this->apm->get_busy_times_by_date($provider_id, $date);

            // Remove current appointment from busy times
            $busy_times = array_filter($busy_times, function ($busy) use ($appointment_id) {
                return isset($busy['id']) && $busy['id'] != $appointment_id;
            });

            // Service duration and buffers
            $duration = (int) $service->duration;
            $buffer_before = (int) ($service->buffer_before ?? 0);
            $buffer_after = (int) ($service->buffer_after ?? 0);

            // Generate time slots
            $slots = [];
            $start_time = strtotime($working_hours['start_time']);
            $end_time = strtotime($working_hours['end_time']);
            $current_time = $start_time;

            // Total time needed including buffers
            $total_time_needed = $duration + $buffer_before + $buffer_after;

            while ($current_time + ($total_time_needed * 60) <= $end_time) {
                // Actual appointment time (excluding buffers for display)
                $slot_start = date('H:i', $current_time + ($buffer_before * 60));
                $slot_end = date('H:i', $current_time + ($buffer_before * 60) + ($duration * 60));

                // Time including buffers for conflict checking
                $slot_with_buffer_start = $current_time;
                $slot_with_buffer_end = $current_time + ($total_time_needed * 60);

                // Check if slot is available (not in busy times)
                $is_available = true;

                foreach ($busy_times as $busy) {
                    $busy_start = strtotime($date . ' ' . $busy['start_hour']);
                    $busy_end = strtotime($date . ' ' . $busy['end_hour']);

                    // Add buffers to busy times if they exist
                    if (isset($busy['buffer_before']) && $busy['buffer_before']) {
                        $busy_start -= ($busy['buffer_before'] * 60);
                    }
                    if (isset($busy['buffer_after']) && $busy['buffer_after']) {
                        $busy_end += ($busy['buffer_after'] * 60);
                    }

                    // Check for overlap using buffered times
                    if (
                        ($slot_with_buffer_start >= $busy_start && $slot_with_buffer_start < $busy_end) || // Slot start during busy
                        ($slot_with_buffer_end > $busy_start && $slot_with_buffer_end <= $busy_end) || // Slot end during busy
                        ($slot_with_buffer_start <= $busy_start && $slot_with_buffer_end >= $busy_end) // Slot contains busy
                    ) {
                        $is_available = false;
                        break;
                    }
                }

                // Add slot to array
                $slots[] = [
                    'start_time' => $slot_start,
                    'end_time' => $slot_end,
                    'available' => $is_available
                ];

                // Move to next slot (30 minute intervals)
                $current_time += 30 * 60;
            }

            echo json_encode([
                'success' => true,
                'times' => $slots,
                'date' => $date
            ]);
        } catch (Exception $e) {
            log_message('error', 'Error in get_available_times: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error getting available times',
                'debug' => $e->getMessage()
            ]);
        }
    }
}

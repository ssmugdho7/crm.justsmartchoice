<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Generate user-friendly appointment URL
 *
 * @param string $hash The appointment hash
 * @return string The user-friendly URL
 */
if (!function_exists('appointly_get_appointment_url')) {
    function appointly_get_appointment_url($hash)
    {
        return site_url('appointly/appointment/' . $hash);
    }
}

/**
 * Fetches from database all staff assigned customers
 * If admin fetches all customers.
 *
 * @return array
 */
if (!function_exists('appointly_get_staff_customers')) {
    function appointly_get_staff_customers()
    {
        $CI = &get_instance();

        $staffCanViewAllClients = staff_can('view', 'customers');

        $CI->db->select(
            'firstname, lastname, ' . db_prefix() . 'contacts.id as contact_id, ' . get_sql_select_client_company()
        );
        $CI->db->where(db_prefix() . 'clients.active', '1');
        $CI->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid=' . db_prefix() . 'contacts.userid', 'left');
        $CI->db->select(db_prefix() . 'clients.userid as client_id');

        if (! $staffCanViewAllClients) {
            $CI->db->where(
                '(' . db_prefix() . 'clients.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' .
                    get_staff_user_id() . '))'
            );
        }

        $result = $CI->db->get(db_prefix() . 'contacts')->result_array();

        foreach ($result as &$contact) {
            if ($contact['company'] == $contact['firstname'] . ' ' . $contact['lastname']) {
                $contact['company'] = _l('appointments_individual_contact');
            } else {
                $contact['company'] = "" . _l('appointments_company_for_select') . "(" . $contact['company'] . ")";
            }
        }

        if ($CI->db->affected_rows() !== 0) {
            return $result;
        } else {
            return [];
        }
    }
}


/**
 * Fetch current appointment data.
 *
 * @param [string] $appointment_id
 *
 * @return array
 */
if (!function_exists('fetch_appointment_data')) {
    function fetch_appointment_data($id)
    {
        $CI = &get_instance();
        $CI->db->select('*');
        $CI->db->from(db_prefix() . 'appointly_appointments');
        $CI->db->where('id', $id);

        $appointment = $CI->db->get()->row_array();

        // Get service details
        if (isset($appointment['service_id']) && $appointment['service_id']) {
            $CI->db->select('id, duration, name as service_name, color');
            $CI->db->from(db_prefix() . 'appointly_services');
            $CI->db->where('id', $appointment['service_id']);
            $service = $CI->db->get()->row_array();

            if ($service) {
                // Merge service details into appointment
                $appointment = array_merge($appointment, [
                    'service_duration' => $service['duration'],
                    'service_name' => $service['service_name'],
                    'service_color' => $service['color']
                ]);
            }
        }

        // Get provider details
        if (isset($appointment['provider_id']) && $appointment['provider_id']) {
            $CI->db->select('firstname, lastname, staffid');
            $CI->db->from(db_prefix() . 'staff');
            $CI->db->where('staffid', $appointment['provider_id']);
            $provider = $CI->db->get()->row_array();

            if ($provider) {
                // Merge provider details into appointment
                $appointment = array_merge($appointment, [
                    'provider_firstname' => $provider['firstname'],
                    'provider_lastname' => $provider['lastname']
                ]);
            }
        }

        // Get attendees as an array of staff IDs
        $CI->db->select('staff_id');
        $CI->db->from(db_prefix() . 'appointly_attendees');
        $CI->db->where('appointment_id', $id);
        $attendees_result = $CI->db->get()->result_array();

        $attendees_ids = [];
        foreach ($attendees_result as $attendee) {
            $attendees_ids[] = $attendee['staff_id'];
        }

        $appointment['attendees'] = $attendees_ids;

        return $appointment;
    }
}

/**
 * Convert dates for database insertion
 *
 * @param string $date
 *
 * @return array
 */
if (!function_exists('convertDateForDatabase')) {
    function convertDateForDatabase($date)
    {
        // Check if the date is already in SQL format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            // It's just a date without time - handle it differently
            return [
                'date' => $date,
                'start_hour' => '00:00', // Default time if no time specified
            ];
        }

        // Handle date with potential time component
        try {
            $datetime = new DateTime($date);
            return [
                'date' => $datetime->format('Y-m-d'),
                'start_hour' => $datetime->format('H:i'),
            ];
        } catch (Exception $e) {
            // Fallback if date parsing fails
            $parsed_date = to_sql_date($date, true);
            return [
                'date' => date('Y-m-d', strtotime($parsed_date)),
                'start_hour' => date('H:i', strtotime($parsed_date)),
            ];
        }
    }
}


/**
 * Convert dates for database insertion
 *
 * @param string $date
 *
 * @param $time
 *
 * @return array
 */
if (!function_exists('convertDateForValidation')) {
    function convertDateForValidation($date, $time)
    {
        $date = to_sql_date($date, true);

        $dt = 'H:i';

        if ($time == '12') {
            $dt = 'g:i A';
        }

        $toTime = strtotime($date);

        return [
            'date' => date('Y-m-d', $toTime),
            'start_hour' => date($dt, $toTime),
        ];
    }
}


/**
 * Send email and push notifications for newly created recurring appointment
 *
 * @param string $appointment_id
 *
 * @return void
 */
if (!function_exists('newRecurringAppointmentNotifications')) {
    function newRecurringAppointmentNotifications($appointment_id)
    {
        $CI = &get_instance();
        $CI->db->where('id', $appointment_id);

        // get appointment
        $appointment = $CI->db->get(db_prefix() . 'appointly_appointments')->row_array();

        if ($CI->db->affected_rows() > 0) {
            // get attendees
            $CI->db->where('appointment_id', $appointment_id);
            $CI->db->join('staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'appointly_attendees.staff_id');
            $appointment['attendees'] = $CI->db->get('appointly_attendees')->result_array();
        }

        $notified_users = [];

        $attendees = $appointment['attendees'];

        foreach ($attendees as $staff) {
            if ($staff['staffid'] === get_staff_user_id()) {
                continue;
            }

            add_notification([
                'description' => 'appointment_recurring_re_created',
                'touserid' => $staff['staffid'],
                'fromcompany' => true,
                'link' => 'appointly/appointments/view?appointment_id=' . $appointment_id,
            ]);

            $notified_users[] = $staff['staffid'];

            send_mail_template(
                'appointly_appointment_recurring_recreated_to_staff',
                'appointly',
                array_to_object($appointment),
                array_to_object($staff)
            );
        }

        pusher_trigger_notification(array_unique($notified_users));

        $template = mail_template(
            'appointly_appointment_recurring_recreated_to_contacts',
            'appointly',
            array_to_object($appointment)
        );

        @$template->send();
    }
}


/**
 * Helper function to handle reminder fields
 *
 * @param array $data
 *
 * @return array
 */
if (!function_exists('handleDataReminderFields')) {
    function handleDataReminderFields($data)
    {
        (isset($data['by_email']) && $data['by_email'] == 'on')
            ? $data['by_email'] = '1'
            : $data['by_email'] = null;

        (isset($data['by_sms']) && $data['by_sms'] == 'on')
            ? $data['by_sms'] = '1'
            : $data['by_sms'] = null;

        if ($data['by_email'] === null && $data['by_sms'] === null) {
            $data['reminder_before'] = null;
            $data['reminder_before_type'] = null;
        }

        if (isset($data['by_email']) || isset($data['by_sms'])) {
            if ($data['reminder_before'] == '') {
                $data['reminder_before'] = '30';
            }
        }

        return $data;
    }
}

/**
 * Helper redirect function with alert message.
 *
 * @param [string] $type 'success' | 'danger'
 * @param [string] $message
 */
if (!function_exists('redirect_after_event')) {
    function appointly_redirect_after_event($type, $message, $path = null)
    {
        $CI = &get_instance();

        $CI->session->set_flashdata('message-' . $type, $message);

        if ($path) {
            redirect('admin/appointly/' . $path);
        } else {
            redirect('admin/appointly/appointments');
        }
    }
}

/**
 * Helper function to get contact specific data.
 *
 * @param [string] $contact_id
 *
 * @return array
 */
if (!function_exists('get_appointment_contact_details')) {
    function get_appointment_contact_details($contact_id)
    {
        if (!$contact_id) {
            return null;
        }

        $CI = &get_instance();
        $CI->db->select('email, userid, phonenumber as phone, CONCAT(firstname, " " , lastname) AS full_name');
        $CI->db->where('id', $contact_id);
        $contact = $CI->db->get(db_prefix() . 'contacts')->row_array();

        if (!$contact) {
            return null;
        }

        $contact['company_name'] = get_company_name($contact['userid']);
        return $contact;
    }
}

/**
 * Get staff.
 *
 * @param [string] $staffid
 *
 * @return array
 */
if (!function_exists('appointly_get_staff')) {
    function appointly_get_staff($staffid)
    {
        $CI = &get_instance();
        $CI->db->where('staffid', $staffid);

        return $CI->db->get(db_prefix() . 'staff')->row_array();
    }
}


/**
 * Include appointment view
 *
 * @param $path
 * @param $name
 *
 * @return mixed
 */
if (!function_exists('include_appointment_view')) {
    function include_appointment_view($path, $name, $variables = [])
    {
        // Extract variables to make them available in the included file
        if (!empty($variables)) {
            extract($variables);
        }
        return require 'modules/appointly/views/' . $path . '/' . $name . '.php';
    }
}


/**
 * Get projects summary
 *
 * @return array
 */
if (! function_exists('get_appointments_summary')) {
    function get_appointments_summary($googleSync = null)
    {
        $CI = &get_instance();
        $CI->load->database();

        // here i want logic if is admin to show all appointments
        // Apply permission-based filtering properly
        $CI->db->select('*');
        $CI->db->from(db_prefix() . 'appointly_appointments');

        if (!staff_can('view', 'appointments')) {
            $CI->db->group_start();
            $CI->db->where(db_prefix() . 'appointly_appointments.created_by', get_staff_user_id());
            $CI->db->or_where(db_prefix() . 'appointly_appointments.id IN (SELECT appointment_id FROM ' . db_prefix() . 'appointly_attendees WHERE staff_id=' . get_staff_user_id() . ')');
            $CI->db->group_end();
        }

        $appointments = $CI->db->get()->result_array();

        // Ensure we have a valid array result
        if (!is_array($appointments)) {
            $appointments = [];
        }

        // Initialize data with zeros
        $data = [
            'total_appointments' => 0,
            'upcoming' => [
                'total' => 0,
                'name' => _l('appointment_upcoming'),
                'color' => '#0284c7', // Blue
            ],
            'not_approved' => [
                'total' => 0,
                'name' => _l('appointment_not_approved'),
                'color' => '#eab308', // Yellow
            ],
            'cancelled' => [
                'total' => 0,
                'name' => _l('appointment_cancelled'),
                'color' => '#ef4444', // Red
            ],
            'no_show' => [
                'total' => 0,
                'name' => _l('appointment_no_show'),
                'color' => '#f97316', // Orange
            ],
            'finished' => [
                'total' => 0,
                'name' => _l('appointment_finished'),
                'color' => '#22c55e', // Green
            ],
        ];

        // Only process if we actually have appointments and the result is a valid array
        if (is_array($appointments) && count($appointments) > 0) {
            $data['total_appointments'] = count($appointments);

            foreach ($appointments as $appointment) {
                // Using the status ENUM field instead of individual boolean fields
                if ($appointment['status'] == 'cancelled') {
                    $data['cancelled']['total']++;
                } elseif ($appointment['status'] == 'no-show') {
                    $data['no_show']['total']++;
                } elseif ($appointment['status'] == 'pending') {
                    $data['not_approved']['total']++;
                } elseif ($appointment['status'] == 'completed') {
                    $data['finished']['total']++;
                } elseif ($appointment['status'] == 'in-progress') {
                    // Check if the appointment is in the future (upcoming) or in the past (should be no-show)
                    if (strtotime($appointment['date'] . ' ' . $appointment['start_hour']) < time()) {
                        // If it's in the past, increment no_show
                        $data['no_show']['total']++;
                    } else {
                        // If it's in the future, increment upcoming
                        $data['upcoming']['total']++;
                    }
                }
            }
        }

        // Only add Google sync count if it's a valid positive number
        if ($googleSync && is_numeric($googleSync) && $googleSync > 0) {
            $data['total_appointments'] += (int)$googleSync;
        }

        return $data;
    }
}

if (!function_exists('get_appointly_staff_userrole')) {
    function get_appointly_staff_userrole($role_id)
    {
        $CI = &get_instance();
        $CI->db->select('name');
        $CI->db->where('roleid', $role_id);

        $result = $CI->db->get(db_prefix() . 'roles')->row_array();

        if ($result !== null) {
            return $result['name'];
        }

        return null;
    }
}


/**
 *
 * Get contact user id from contacts table
 * Used for when creating new task in appointments.
 *
 * @param $contact_id
 *
 * @return mixed
 */
if (!function_exists('appointly_get_contact_customer_id')) {
    function appointly_get_contact_customer_id($contact_id)
    {
        $CI = &get_instance();
        $CI->db->select('userid');
        $CI->db->where('id', $contact_id);
        $result = $CI->db->get(db_prefix() . 'contacts')->row_array();
        if ($result !== null) {
            return $result['userid'];
        }

        return null;
    }
}


/**
 * Get table filters
 *
 * @return array
 */
if (!function_exists('get_appointments_table_filters')) {
    function get_appointments_table_filters()
    {
        return [
            [
                'id' => 'all',
                'status' => 'All',
            ],
            [
                'id' => 'pending',
                'status' => _l('appointment_status_pending'),
            ],
            [
                'id' => 'in-progress',
                'status' => _l('appointment_status_in-progress'),
            ],
            [
                'id' => 'cancelled',
                'status' => _l('appointment_cancelled'),
            ],
            [
                'id' => 'completed',
                'status' => _l('appointment_completed'),
            ],
            [
                'id' => 'no-show',
                'status' => _l('appointment_status_no-show'),
            ],
            [
                'id' => 'today',
                'status' => _l('appointment_today'),
            ],
            [
                'id' => 'tomorrow',
                'status' => _l('appointment_tomorrow'),
            ],
            [
                'id' => 'this_week',
                'status' => _l('appointment_this_week'),
            ],
            [
                'id' => 'next_week',
                'status' => _l('appointment_next_week'),
            ],
            [
                'id' => 'this_month',
                'status' => _l('appointment_this_month'),
            ],
            [
                'id' => 'upcoming',
                'status' => _l('appointment_upcoming'),
            ],
            // Assignment-based filters
            [
                'id' => 'my_appointments',
                'status' => _l('appointment_my_appointments'),
            ],
            [
                'id' => 'assigned_to_me',
                'status' => _l('appointment_assigned_to_me'),
            ],
            // Source-based filters
            [
                'id' => 'internal',
                'status' => _l('appointment_internal'),
            ],
            [
                'id' => 'external',
                'status' => _l('appointment_external'),
            ],
            [
                'id' => 'recurring',
                'status' => _l('appointment_recurring'),
            ],
            [
                'id' => 'lead_related',
                'status' => _l('appointment_lead_related'),
            ],
            [
                'id' => 'internal_staff',
                'status' => _l('appointment_internal_staff'),
            ],
        ];
    }
}

/**
 * Get staff or contact email.
 *
 * @param $id
 * @param string $type
 *
 * @return mixed
 */
if (!function_exists('appointly_get_user_email')) {
    function appointly_get_user_email($id, $type = 'staff')
    {
        $CI = &get_instance();
        $CI->db->select('email');
        $table = 'staff';
        $selector = 'staffid';

        if ($type == 'contact') {
            $table = 'contacts';
            $selector = 'id';
        }

        $CI->db->where($selector, $id);
        $result = $CI->db->get(db_prefix() . $table)->row_array();
        if ($result !== null) {
            return $result['email'];
        }

        return null;
    }
}

/**
 * Insert new appointment to google calendar.
 *
 * @param array $data The appointment data
 * @param array $attendees The attendees data
 *
 * @return array
 */
if (!function_exists('insertAppointmentToGoogleCalendar')) {
    function insertAppointmentToGoogleCalendar($data, $attendees)
    {
        // First check if Google Calendar integration is enabled and authenticated
        if (!appointlyGoogleAuth()) {
            return [];
        }

        try {
            // Validate required data fields
            if (empty($data['date'])) {
                log_message('error', 'Google Calendar: Missing required date field');
                return [];
            }

            if (empty($data['subject'])) {
                log_message('info', 'Google Calendar: Missing subject field, using default');
                $data['subject'] = 'CRM Appointment';
            }

            // Format dates properly for Google Calendar API
            if (isset($data['start_hour'])) {
                // If start_hour is provided, use it with the date
                // Check if the date already includes time (contains space)
                if (strpos($data['date'], ' ') !== false) {
                    // Extract just the date part
                    $datePart = trim(explode(' ', $data['date'])[0]);
                    $dateStr = $datePart . ' ' . (strpos($data['start_hour'], ':') !== false ? $data['start_hour'] : $data['start_hour'] . ':00');
                } else {
                    // Use date as is
                    $dateStr = $data['date'] . ' ' . (strpos($data['start_hour'], ':') !== false ? $data['start_hour'] : $data['start_hour'] . ':00');
                }
                $startDateTime = new DateTime($dateStr);
            } else {
                // Fall back to the original method
                $startDateTime = new DateTime(to_sql_date($data['date'], true));
            }

            // Calculate end time based on duration
            $endDateTime = clone $startDateTime;
            $duration = 60; // Default 60 minutes

            // For internal_staff meetings, use appointment duration
            if (isset($data['source']) && $data['source'] == 'internal_staff' && isset($data['duration']) && $data['duration'] > 0) {
                $duration = $data['duration'];
            }
            // For other appointments, try to get duration from service
            else if (isset($data['service_id'])) {
                $CI = &get_instance();
                $CI->load->model('appointly/service_model');
                $service = $CI->service_model->get($data['service_id']);
                if ($service && isset($service->duration) && $service->duration > 0) {
                    $duration = $service->duration;
                }
            }

            // Use end_hour if available instead of calculating
            if (!empty($data['end_hour'])) {
                try {
                    $endDateStr = $data['date'] . ' ' . (strpos($data['end_hour'], ':') !== false ? $data['end_hour'] : $data['end_hour'] . ':00');
                    $tempEndDateTime = new DateTime($endDateStr);

                    // Validate that the provided end time is after start time
                    if ($tempEndDateTime > $startDateTime) {
                        $endDateTime = $tempEndDateTime;
                    } else {
                        log_message('info', 'Google Calendar: Provided end_hour (' . $data['end_hour'] . ') is before start_hour (' . $data['start_hour'] . '), recalculating from duration');
                        // Fall back to duration-based calculation
                        $endDateTime->add(new DateInterval('PT' . $duration . 'M'));
                    }
                } catch (Exception $e) {
                    // log_message('warning', 'Google Calendar: Failed to parse end_hour (' . $data['end_hour'] . '), using duration-based calculation: ' . $e->getMessage());
                    // If end_hour parsing fails, fall back to duration-based calculation
                    $endDateTime->add(new DateInterval('PT' . $duration . 'M'));
                }
            } else {
                // Add duration minutes to end time
                $endDateTime->add(new DateInterval('PT' . $duration . 'M'));
            }

            // Final validation that end time is after start time
            if ($endDateTime <= $startDateTime) {
                log_message('error', 'Google Calendar: End time (' . $endDateTime->format('Y-m-d H:i:s') . ') is still not after start time (' . $startDateTime->format('Y-m-d H:i:s') . ') even after recalculation');
                return [];
            }



            $gmail_guests = [];

            // Process attendees (staff)
            if (!empty($attendees) && is_array($attendees)) {
                foreach ($attendees as $attendee) {
                    $email = appointly_get_user_email($attendee);
                    if ($email) {
                        $gmail_guests[] = ['email' => $email];
                    }
                }
            }

            // Add contact/client to attendees
            if (!empty($data['contact_id']) && isset($data['source']) && $data['source'] != 'lead_related') {
                $email = appointly_get_user_email($data['contact_id'], 'contact');
                if ($email) {
                    $gmail_guests[] = ['email' => $email];
                }
            } else {
                if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $gmail_guests[] = ['email' => $data['email']];
                }
            }

            // Clear any invalid emails
            $gmail_guests = array_filter($gmail_guests, function ($v) {
                return !empty($v['email']) && filter_var($v['email'], FILTER_VALIDATE_EMAIL);
            });

            // Get timezone from settings or appointment
            $timezone = !empty($data['timezone'])
                ? $data['timezone']
                : get_option('default_timezone');

            // Prepare event data for Google Calendar
            $params = [
                'summary'     => $data['subject'] ?? 'CRM Appointment',
                'location'    => $data['address'] ?? '',
                'description' => $data['description'] ?? '',
                'start'       => [
                    'dateTime' => $startDateTime->format('Y-m-d\TH:i:s'),
                    'timeZone' => $timezone
                ],
                'end'         => [
                    'dateTime' => $endDateTime->format('Y-m-d\TH:i:s'),
                    'timeZone' => $timezone
                ],
                'attendees'   => $gmail_guests,
                'reminders'   => [
                    'useDefault' => false,
                    'overrides'  => [
                        [
                            'method'  => 'popup',
                            'minutes' => (int) get_option('appointly_google_meet_reminder_minutes') ?: 30
                        ]
                    ]
                ]
            ];

            // Send request to Google Calendar API
            $response = get_instance()->googlecalendar->addEvent($params);

            if ($response) {
                $result = [
                    'google_event_id'   => $response->getId(),
                    'htmlLink'          => $response->getHtmlLink(),
                    'google_added_by_id' => get_staff_user_id()
                ];

                if ($response->getHangoutLink()) {
                    $result['hangoutLink'] = $response->getHangoutLink();
                }

                return $result;
            }

            return [];
        } catch (Exception $e) {
            log_message('error', 'Google Calendar Integration Error: ' . $e->getMessage());
            return [];
        }
    }
}

function appointly_analytics_calculate_percentage_change($new_value, $old_value)
{
    if ($old_value == 0) {
        return $new_value > 0 ? 100 : 0;
    }
    return round((($new_value - $old_value) / $old_value) * 100, 1);
}

function appointly_analytics_get_date_range($range)
{
    $today = new DateTime();
    $today->setTime(0, 0, 0);

    switch ($range) {
        case 'today':
            return [
                'from' => $today->format('Y-m-d'),
                'to' => $today->format('Y-m-d')
            ];
        case 'this_week':
            $start = clone $today;
            $start->modify('monday this week');
            $end = clone $start;
            $end->modify('sunday this week');
            return [
                'from' => $start->format('Y-m-d'),
                'to' => $end->format('Y-m-d')
            ];
        case 'this_year':
            return [
                'from' => $today->format('Y-01-01'),
                'to' => $today->format('Y-12-31')
            ];
        default:
            return [
                'from' => $today->format('Y-m-01'),
                'to' => $today->format('Y-m-t')
            ];
    }
}

/**
 * @param $data
 *
 * @return array
 * @throws \Exception
 */
if (!function_exists('updateAppointmentToGoogleCalendar')) {
    /**
     * @throws Exception
     */
    function updateAppointmentToGoogleCalendar($data)
    {
        // First check if Google Calendar integration is enabled and authenticated
        if (!appointlyGoogleAuth()) {
            return [];
        }
        try {
            // Validate required parameters
            if (empty($data['google_event_id'])) {
                return [];
            }

            // Debug log to troubleshoot time issues

            // Format dates properly for Google Calendar API
            if (isset($data['start_hour'])) {
                // If start_hour is provided, use it with the date
                // Check if the date already includes time (contains space)
                if (strpos($data['date'], ' ') !== false) {
                    // Extract just the date part
                    $datePart = trim(explode(' ', $data['date'])[0]);
                    $dateStr = $datePart . ' ' . (strpos($data['start_hour'], ':') !== false ? $data['start_hour'] : $data['start_hour'] . ':00');
                } else {
                    // Use date as is
                    $dateStr = $data['date'] . ' ' . (strpos($data['start_hour'], ':') !== false ? $data['start_hour'] : $data['start_hour'] . ':00');
                }
                $startDateTime = new DateTime($dateStr);
            } else {
                // Fall back to the original method
                $startDateTime = new DateTime(to_sql_date($data['date'], true));
            }

            // Calculate end time based on duration
            $endDateTime = clone $startDateTime;
            $duration = 60; // Default 60 minutes

            // For internal_staff meetings, use appointment duration
            if (isset($data['source']) && $data['source'] == 'internal_staff' && isset($data['duration']) && $data['duration'] > 0) {
                $duration = $data['duration'];
            }
            // For other appointments, try to get duration from service
            else if (isset($data['service_id'])) {
                $CI = &get_instance();
                $CI->load->model('appointly/service_model');
                $service = $CI->service_model->get($data['service_id']);
                if ($service && isset($service->duration) && $service->duration > 0) {
                    $duration = $service->duration;
                }
            }

            // Use end_hour if available instead of calculating
            if (isset($data['end_hour']) && !empty($data['end_hour'])) {
                try {
                    $endDateStr = $data['date'] . ' ' . (strpos($data['end_hour'], ':') !== false ? $data['end_hour'] : $data['end_hour'] . ':00');
                    $endDateTime = new DateTime($endDateStr);
                } catch (Exception $e) {
                    // If end_hour parsing fails, fall back to duration-based calculation
                    $endDateTime->add(new DateInterval('PT' . $duration . 'M'));
                }
            } else {
                // Add duration minutes to end time
                $endDateTime->add(new DateInterval('PT' . $duration . 'M'));
            }

            // Validate that end time is after start time
            if ($endDateTime <= $startDateTime) {
                return [];
            }

            // Log the final times

            // Process attendees
            $gmail_guests = [];
            if (isset($data['attendees'])) {
                $gmail_attendees = $data['attendees'];
                foreach ($gmail_attendees as $attendee) {
                    $email = appointly_get_user_email($attendee);
                    if ($email) {
                        $gmail_guests[] = ['email' => $email];
                    }
                }
            }

            // Add contact/client to attendees
            if (!empty($data['contact_id']) && isset($data['source']) && $data['source'] != 'lead_related') {
                $email = appointly_get_user_email($data['contact_id'], 'contact');
                if ($email) {
                    $gmail_guests[] = ['email' => $email];
                }
            } elseif (isset($data['selected_contact']) && isset($data['source']) && $data['source'] != 'lead_related') {
                $email = appointly_get_user_email($data['selected_contact'], 'contact');
                if ($email) {
                    $gmail_guests[] = ['email' => $email];
                }
            } elseif (isset($data['source']) && $data['source'] != 'lead_related') {
                if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $gmail_guests[] = ['email' => $data['email']];
                }
            }

            // Clear empty emails
            $gmail_guests = array_filter($gmail_guests, function ($v) {
                return !empty($v['email']) && filter_var($v['email'], FILTER_VALIDATE_EMAIL);
            });

            // Get timezone from settings or appointment
            $timezone = isset($data['timezone']) && !empty($data['timezone'])
                ? $data['timezone']
                : get_option('default_timezone');

            // Prepare event data for Google Calendar
            $params = [
                'summary'     => $data['subject'],
                'location'    => $data['address'] ?? '',
                'description' => $data['description'] ?? '',
                'start'       => [
                    'dateTime' => $startDateTime->format('Y-m-d\TH:i:s'),
                    'timeZone' => $timezone
                ],
                'end'         => [
                    'dateTime' => $endDateTime->format('Y-m-d\TH:i:s'),
                    'timeZone' => $timezone
                ],
                'attendees'   => $gmail_guests
            ];

            // Send request to Google Calendar API
            $response = get_instance()->googlecalendar->updateEvent($data['google_event_id'], $params);

            if ($response) {
                $return_data = [];
                if (isset($response['hangoutLink'])) {
                    $return_data['google_meet_link'] = $response['hangoutLink'];
                }
                $return_data['google_event_id'] = $response['id'];
                $return_data['htmlLink'] = $response['htmlLink'];

                return $return_data;
            } else {
                return [];
            }
        } catch (Exception $e) {
            return [];
        }
    }
}

/**
 * @param $aRow
 * @return bool
 */
if (! function_exists('isSynceGoogleMeeting')) {
    function isSynceGoogleMeeting($aRow): bool
    {
        return $aRow['source'] !== 'google';
    }
}


/**
 * Check if user is authenticated with Google calendar
 * Refresh access token.
 *
 * @return bool
 */
/**
 * Check if user is authenticated with Google calendar
 * Refresh access token.
 *
 * @return bool
 */
if (! function_exists('appointlyGoogleAuth')) {

    function appointlyGoogleAuth()
    {
        $CI = &get_instance();
        $CI->load->model('appointly/googlecalendar');

        $account = $CI->googlecalendar->getAccountDetails();

        if (! $account) return false;

        $newToken = '';

        if ($account) {
            $account = $account[0];

            $currentToken = [
                'access_token' => $account->access_token,
                'expires_in'   => $account->expires_in
            ];

            $CI->googleplus
                ->client
                ->setAccessToken($currentToken);

            $refreshToken = $account->refresh_token;
            // renew 5 minutes before token expire
            if ($account->expires_in <= time() + 300) {

                if ($CI->googleplus->isAccessTokenExpired()) {
                    $CI->googleplus
                        ->client
                        ->setAccessToken($currentToken);
                }

                if ($refreshToken) {
                    // { "error": "invalid_grant", "error_description": "Token has been expired or revoked." }
                    try {
                        $newToken = $CI->googleplus->client->refreshToken($refreshToken);
                    } catch (Exception $e) {
                        if ($e->getCode() === 400) {
                            return false;
                        }

                        if ($e->getCode() === 401) {
                            return false;
                        }
                    }

                    $CI->googleplus
                        ->client
                        ->setAccessToken($newToken);

                    if ($newToken) {
                        $CI->googlecalendar->saveNewTokenValues($newToken);
                    }
                }
            } else {
                try {
                    $newToken = $CI->googleplus->client->refreshToken($refreshToken);
                } catch (Exception $e) {
                    if ($e->getCode() === 400) {
                        return false;
                    }

                    if ($e->getCode() === 401) {
                        return false;
                    }
                }
            }

            $CI->googleplus
                ->client
                ->setAccessToken(($newToken !== '') ? $newToken : $account->access_token);
        }

        if ($CI->googleplus->client->getAccessToken()) {
            return $CI->googleplus->client->getAccessToken();
        }

        return false;
    }
}



/**
 * @return array
 */
if (!function_exists('getAppointlyUserMeta')) {
    function getAppointlyUserMeta($data = [])
    {
        $data['appointly_show_summary'] = get_meta('staff', get_staff_user_id(), 'appointly_show_summary');

        $data['appointly_default_table_filter'] = get_meta(
            'staff',
            get_staff_user_id(),
            'appointly_default_table_filter'
        );

        return $data;
    }
}

/**
 * Handle appointly user meta
 *
 * @param $meta
 *
 * @return void
 */
if (!function_exists('handleAppointlyUserMeta')) {
    function handleAppointlyUserMeta($meta)
    {
        foreach ($meta as $key => $value) {
            update_meta('staff', get_staff_user_id(), $key, $value);
        }
    }
}


/**
 * Get appointment default feedbacks.
 *
 * @return array
 */
if (!function_exists('getAppointmentsFeedbacks')) {
    function getAppointmentsFeedbacks()
    {
        return [
            ['value' => '0', 'name' => _l('ap_feedback_not_sure')],
            ['value' => '1', 'name' => _l('ap_feedback_the_worst')],
            ['value' => '2', 'name' => _l('ap_feedback_bad')],
            ['value' => '3', 'name' => _l('ap_feedback_not_bad')],
            ['value' => '4', 'name' => _l('ap_feedback_good')],
            ['value' => '5', 'name' => _l('ap_feedback_very_good')],
            ['value' => '6', 'name' => _l('ap_feedback_extremely_good')],
        ];
    }
}

/**
 * Renders appointment feedbacks html
 *
 * @param $appointment
 * @param bool $fallback
 *
 * @return string
 */
if (!function_exists('renderAppointmentFeedbacks')) {
    function renderAppointmentFeedbacks($appointment, $fallback = false)
    {
        $appointmentFeedbacks = getAppointmentsFeedbacks();

        if ($fallback && is_string($appointment)) {
            $CI = &get_instance();
            $appointment = $CI->apm->get_appointment_data($appointment);
        }
        $html = '<div class="col-lg-12 col-xs-12 mtop20 text-center" id="feedback_wrapper">';
        $html .= '<span class="label label-default" style="line-height: 30px;">' . _l(
            'appointment_feedback_label'
        ) . '</span><br>';

        if ($appointment['feedback'] !== null && ! is_staff_logged_in()) {
            $html = '<span class="label label-primary" style="line-height: 30px;">' . _l(
                'appointment_feedback_label_current'
            ) . '</span><br>';
        }

        if ($fallback) {
            $html = '<span class="label label-success" style="line-height: 30px;">' . _l(
                'appointment_feedback_label_added'
            ) . '</span><br>';
        }

        $savedFeedbacks = json_decode(get_option('appointly_default_feedbacks'));
        $count = 0;

        foreach ($appointmentFeedbacks as $feedback) {
            if ($savedFeedbacks !== null) {
                if (! in_array($feedback['value'], $savedFeedbacks)) {
                    continue;
                }
            }

            $rating_class = '';

            if ($appointment['feedback'] >= $feedback['value']) {
                $rating_class = 'star_rated';
            }

            $onClick = '';
            if (!is_staff_logged_in()) {
                $onClick = 'onclick="handle_appointment_feedback(this)"';
            }

            $html .= '<span ' . $onClick . ' data-count="' . $count++ . '"
                            data-rating="' . $feedback['value'] . '" data-toggle="tooltip"
                            title="' . $feedback['name'] . '" class="feedback_star text-center ' . $rating_class . '"><i
                                class="fa fa-star" aria-hidden="true"></i></span>';
        }

        if (! is_bool($appointment['feedback_comment'])) {
            if ($appointment['feedback_comment'] !== null) {
                $html .= '<div class="col-md-12 text-center mtop5" id="feedback_comment_area">';
                $html .= '<h6>' . $appointment['feedback_comment'] . '</h6>';
                $html .= '</div>';
                $html .= '<div class="clearfix"></div>';
            }
        }

        if ($appointment['feedback'] !== null && !is_staff_logged_in()) {
            echo '<div>';
        }

        $html .= '</div>';

        return $html;
    }
}

/**
 * Render appointments timezone.
 *
 * @param $appointment
 *
 * @return string
 * @throws \Exception
 */
if (!function_exists('render_appointments_timezone')) {
    /**
     * @throws Exception
     */
    function render_appointments_timezone($appointment)
    {
        $CI = &get_instance();
        $CI->load->model('appointly/appointly_model', 'apm');

        $timezone_info = $CI->apm->get_appointment_timezone_info($appointment);

        return sprintf(
            '<i data-toggle="tooltip" title="%s (GMT %s)" class="fa fa-globe timezone" aria-hidden="true"></i>',
            $timezone_info['timezone'],
            $timezone_info['offset']
        );
    }
}
if (!function_exists('get_service_name')) {
    function get_service_name($service_id)
    {
        $CI = &get_instance();
        $CI->load->model('appointly/service_model', 'sm');
        return $CI->sm->get_service_name($service_id);
    }
}

/**
 * Check and render appointment status with HTML.
 *
 * @param array $aRow
 * @param string $format 'text' or 'html'
 *
 * @return string
 */
if (!function_exists('checkAppointlyStatus')) {
    function checkAppointlyStatus($aRow, $format = 'text')
    {
        // Check if appointment is in the past
        $is_past = false;

        if (!empty($aRow['date']) && !empty($aRow['start_hour'])) {
            // Make sure we have valid date and time values
            $date_str = trim($aRow['date']);
            $time_str = trim($aRow['start_hour']);

            // Format the datetime properly
            if (strpos($date_str, ' ') !== false) {
                // Date already contains time
                $appointment_datetime = strtotime($date_str);
            } else {
                // Combine date and time
                $appointment_datetime = strtotime($date_str . ' ' . $time_str);
            }

            // Only mark as past if we have a valid timestamp
            if ($appointment_datetime !== false) {
                $current_datetime = time();
                $is_past = $appointment_datetime < $current_datetime;
            }
        }

        // Prepare the status text and CSS class
        $status_text = '';
        $status_class = '';

        switch ($aRow['status']) {
            case 'pending':
                $status_text = _l('appointment_pending_approval');
                $status_class = 'warning'; // Yellow for pending
                break;
            case 'cancelled':
                $status_text = _l('appointment_cancelled');
                $status_class = 'danger'; // Red for cancelled
                break;
            case 'completed':
                $status_text = _l('appointment_finished');
                $status_class = 'success'; // Green for completed
                break;
            case 'finished':
                $status_text = _l('appointment_finished');
                $status_class = 'success'; // Green for finished
                break;
            case 'approved':
                $status_text = _l('appointment_approved');
                $status_class = 'success'; // Green for approved
                break;
            case 'no-show':
                $status_text = _l('appointment_no_show');
                $status_class = 'danger'; // Red for no-show
                break;
            case 'in-progress':
                $status_text = _l('appointment_ongoing');
                $status_class = 'info'; // Blue for in-progress
                break;
            default:
                $status_text = _l('appointment_upcoming');
                $status_class = 'info'; // Default blue
                break;
        }

        // Return formatted output based on requested format
        if ($format === 'html') {
            return '<span class="label label-' . $status_class . '">' . $status_text . '</span>';
        } else {
            return $status_text;
        }
    }
}


/**
 * Get module version.
 *
 * @return string
 */
if (!function_exists('get_appointly_version')) {
    function get_appointly_version()
    {
        get_instance()->db->where('module_name', 'appointly');
        $version = get_instance()->db->get(db_prefix() . 'modules');

        if ($version->num_rows() > 0) {
            return _l('appointly_current_version') . $version->row('installed_version');
        }

        return 'Unknown';
    }
}

/**
 * @param $source
 * @param string $externalLinks
 * @param array $row
 * @return array
 */
function checkAppointtlySource($source, string $externalLinks, array $row): array
{
    if ($source == 'google_calendar') {
        $row[] = '<div class="text-center tw-flex tw-flex-wrap tw-items-center tw-justify-between">'
            . _l('appointments_source_google_imported') .
            $externalLinks . '</div>';
    }

    if ($source == 'outlook_imported') {
        $row[] = '<div class="text-center tw-flex tw-flex-wrap tw-items-center tw-justify-between">'
            . _l('appointments_source_outlook_imported') .
            $externalLinks . '</div>';
    }

    if ($source == 'external') {
        $row[] = '<div class="text-center tw-flex tw-flex-wrap tw-items-center tw-justify-between">'
            . _l(
                'appointments_source_external_label'
            ) .
            $externalLinks . '</div>';
    }

    if ($source == 'internal') {
        $row[] = '<div class="text-center tw-flex tw-flex-wrap tw-items-center tw-justify-between">'
            . _l(
                'appointments_source_internal_label'
            ) .
            $externalLinks . '</div>';
    }

    if ($source == 'lead_related') {
        $row[] = '<div class="text-center tw-flex tw-flex-wrap tw-items-center tw-justify-between">'
            . _l(
                'lead'
            ) . $externalLinks . '</div>';
    }

    return $row;
}

function has_appointly_permission($permission)
{
    $CI = &get_instance();

    if (is_admin()) {
        return true;
    }

    if (!is_staff_member()) {
        return false;
    }

    $staff_id = get_staff_user_id();
    $CI->load->model('staff_model');
    $permissions = $CI->staff_model->get_staff_permissions($staff_id);

    foreach ($permissions as $perm) {
        if ($perm['permission_name'] === $permission) {
            return true;
        }
    }

    return false;
}

if (!function_exists('createFilters')) {
    function createFilters()
    {
        $CI = &get_instance();
        $filters = [];

        // Check if we're specifically filtering for Google Calendar synced events
        $isGoogleCalendarFilter = !empty($CI->input->post('google_calendar_synced')) ||
            ($CI->input->post('custom_view') === 'google_calendar_synced');

        // Check if we're on the "All" filter
        $isAllFilter = true;
        foreach (
            [
                'status_pending',
                'status_in-progress',
                'status_completed',
                'status_cancelled',
                'status_no-show',
                'internal',
                'external',
                'lead_related',
                'upcoming',
                'no-show',
                'recurring',
                'internal_staff',
                'google_calendar_synced',
                'today',
                'tomorrow',
                'this_week',
                'next_week',
                'this_month',
                'my_appointments',
                'assigned_to_me'
            ] as $filter
        ) {
            if (!empty($CI->input->post($filter))) {
                $isAllFilter = false;
                break;
            }
        }

        if (!empty($CI->input->post('custom_view'))) {
            $isAllFilter = false;
        }

        // If filtering specifically for Google Calendar events, only use that filter
        if ($isGoogleCalendarFilter) {
            $filters[] = 'AND (' . db_prefix() . 'appointly_appointments.source = "google_calendar")';
            return $filters;
        }

        // For all other filters, use only the new status field (not legacy fields)
        $filterConditions = [
            // Status filters - using the ENUM status field
            'status_pending' => 'AND (' . db_prefix() . 'appointly_appointments.status = "pending")',
            'status_in-progress' => 'AND (' . db_prefix() . 'appointly_appointments.status = "in-progress")',
            'status_completed' => 'AND (' . db_prefix() . 'appointly_appointments.status = "completed")',
            'status_cancelled' => 'AND (' . db_prefix() . 'appointly_appointments.status = "cancelled")',
            'status_no-show' => 'AND (' . db_prefix() . 'appointly_appointments.status = "no-show")',

            // Time-based filters - Added new time-based filters
            'today' => 'AND ' . db_prefix() . 'appointly_appointments.date = CURDATE()',
            'tomorrow' => 'AND ' . db_prefix() . 'appointly_appointments.date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)',
            'this_week' => 'AND WEEK(' . db_prefix() . 'appointly_appointments.date, 1) = WEEK(CURDATE(), 1) AND YEAR(' . db_prefix() . 'appointly_appointments.date) = YEAR(CURDATE())',
            'next_week' => 'AND WEEK(' . db_prefix() . 'appointly_appointments.date, 1) = WEEK(DATE_ADD(CURDATE(), INTERVAL 1 WEEK), 1) AND YEAR(' . db_prefix() . 'appointly_appointments.date) = YEAR(DATE_ADD(CURDATE(), INTERVAL 1 WEEK))',
            'this_month' => 'AND MONTH(' . db_prefix() . 'appointly_appointments.date) = MONTH(CURDATE()) AND YEAR(' . db_prefix() . 'appointly_appointments.date) = YEAR(CURDATE())',
            'upcoming' => 'AND ' . db_prefix() . 'appointly_appointments.date > CURDATE()',
            'no-show' => 'AND (' . db_prefix() . 'appointly_appointments.status = "no-show" OR (' . db_prefix() . 'appointly_appointments.date < CURDATE() AND ' . db_prefix() . 'appointly_appointments.status != "completed" AND ' . db_prefix() . 'appointly_appointments.status != "cancelled"))',


            // Assignment-based filters
            'my_appointments' => 'AND ' . db_prefix() . 'appointly_appointments.created_by = ' . get_staff_user_id(),
            'assigned_to_me' => 'AND ' . db_prefix() . 'appointly_appointments.provider_id = ' . get_staff_user_id() . ' OR ' . db_prefix() . 'appointly_appointments.id IN (SELECT appointment_id FROM ' . db_prefix() . 'appointly_attendees WHERE staff_id = ' . get_staff_user_id() . ')',

            // Source-based filters
            'internal' => 'AND (' . db_prefix() . 'appointly_appointments.source = "internal")',
            'external' => 'AND (' . db_prefix() . 'appointly_appointments.source = "external")',
            'lead_related' => 'AND (' . db_prefix() . 'appointly_appointments.source = "lead_related")',
            'internal_staff' => 'AND (' . db_prefix() . 'appointly_appointments.source = "internal_staff")',

            // Other filters
            'recurring' => 'AND ' . db_prefix() . 'appointly_appointments.recurring = 1',
        ];

        foreach ($filterConditions as $key => $condition) {
            if ($CI->input->post($key)) {
                $filters[] = $condition;
            }
        }

        // Handle custom_view for all the new filters
        $custom_view = $CI->input->post('custom_view');
        if ($custom_view && isset($filterConditions[$custom_view])) {
            $filters[] = $filterConditions[$custom_view];
        }

        // For non-Google filters (except ALL), exclude Google Calendar appointments
        // Only exclude Google Calendar appointments when a specific filter is active (not "All")
        if (!$isGoogleCalendarFilter && !$isAllFilter && get_option('appointments_googlesync_show_in_table')) {
            $filters[] = 'AND ' . db_prefix() . 'appointly_appointments.source != "google_calendar"';
        }

        return $filters;
    }
}

if (!function_exists('get_staff_role')) {
    function get_staff_role($staff_id)
    {
        $CI = &get_instance();

        // Get staff member's role ID
        $CI->db->select('role');
        $CI->db->where('staffid', $staff_id);
        $staff = $CI->db->get(db_prefix() . 'staff')->row();

        if (!$staff || !$staff->role) {
            return false;
        }

        // Get role details
        $CI->db->where('roleid', $staff->role);
        $role = $CI->db->get(db_prefix() . 'roles')->row_array();

        return $role ?: false;
    }
}

if (!function_exists('get_timezones_list')) {
    function get_timezones_list()
    {
        // Get timezones from the core
        $timezones = \app\services\Timezones::get();

        // Instead of returning the nested array structure, return a flat array 
        // with identifiers as keys and display names as values
        $flat_timezones = [];

        foreach ($timezones as $region => $list) {
            if (is_array($list)) {
                foreach ($list as $timezone) {
                    $flat_timezones[$timezone] = $timezone;
                }
            }
        }

        return $flat_timezones;
    }
}

function getStaffProfileImage($staffid)
{
    // Default placeholder URL - always return this if anything goes wrong
    $placeholder = base_url('assets/images/user-placeholder.jpg');

    $CI = &get_instance();
    $CI->load->model('staff_model');
    $staff = $CI->staff_model->get($staffid);

    // If staff doesn't exist or has no profile image, return placeholder
    if (!$staff || empty($staff->profile_image)) {
        return $placeholder;
    }

    // Check if path already contains full URL
    if (str_starts_with($staff->profile_image, 'http')) {
        return $staff->profile_image;
    }

    // Construct the file path exactly like Perfex CRM core does
    $profileImagePath = 'uploads/staff_profile_images/' . $staffid . '/thumb_' . $staff->profile_image;

    // Check if the file actually exists on disk
    if (file_exists($profileImagePath)) {
        return base_url($profileImagePath);
    }

    // File doesn't exist, return placeholder to avoid 404 errors
    return $placeholder;
}


/**
 * Get base currency for the appointly module
 * This avoids conflicts with the core function in sales_helper.php
 * @return object
 */
function appointly_get_base_currency()
{
    $CI = &get_instance();

    if (!class_exists('currencies_model', false)) {
        $CI->load->model('currencies_model');
    }

    return $CI->currencies_model->get_base_currency();
}


if (!function_exists('appointly_get_service_staff')) {
    /**
     * Get staff members assigned to a service
     * Common helper used by public controllers
     * 
     * @param int $service_id The service ID
     * @return array Response with service details and assigned staff
     */
    function appointly_get_service_staff($service_id)
    {
        $CI = &get_instance();

        if (!$service_id) {
            return [
                'success' => false,
                'message' => _l('service_id_required')
            ];
        }

        // Load required models
        if (!class_exists('Service_model', false)) {
            $CI->load->model('appointly/service_model');
        }
        if (!isset($CI->apm)) {
            $CI->load->model('appointly/appointly_model', 'apm');
        }

        // Get service details
        $service = $CI->service_model->get($service_id);

        if (!$service) {
            return [
                'success' => false,
                'message' => _l('service_not_found')
            ];
        }


        // Get staff members assigned to this service
        $staff_members = [];

        // Get staff IDs from the service_staff table
        $CI->db->select('staff_id');
        $CI->db->from(db_prefix() . 'appointly_service_staff');
        $CI->db->where('service_id', $service_id);
        $query = $CI->db->get();

        $staff_ids = [];
        if ($query && $query->num_rows() > 0) {
            $staff_ids = array_column($query->result_array(), 'staff_id');
        }

        // Now fetch the staff data
        if (!empty($staff_ids)) {
            $CI->db->select('staffid, firstname, lastname, email, phonenumber, profile_image');
            $CI->db->from(db_prefix() . 'staff');
            $CI->db->where_in('staffid', $staff_ids);
            $CI->db->where('active', 1);
            $staff_members = $CI->db->get()->result_array();


            // Process profile images
            foreach ($staff_members as &$staff) {
                $staff['profile_image'] = $staff['profile_image']
                    ? staff_profile_image_url($staff['staffid'])
                    : base_url('assets/images/user-placeholder.jpg');
            }
        }

        // Get working hours
        $working_hours = [];

        // Fall back to company schedule
        $company_schedule = $CI->apm->get_company_schedule();
        if (!empty($company_schedule)) {
            foreach ($company_schedule as $day) {
                // Check if weekday key exists to prevent PHP warnings
                if (!isset($day['weekday'])) {
                    continue; // Skip this entry if weekday is not set
                }

                $day_number = getWorkingDayNumber($day['weekday']);
                $working_hours[$day_number] = [
                    'enabled' => isset($day['is_enabled']) && (bool) $day['is_enabled'],
                    'start_time' => $day['start_time'] ?? '09:00',
                    'end_time' => $day['end_time'] ?? '17:00'
                ];
            }
        } else {
            // Default working hours if nothing is configured
            $working_hours = [
                1 => ['enabled' => true, 'start_time' => '09:00', 'end_time' => '17:00'],
                2 => ['enabled' => true, 'start_time' => '09:00', 'end_time' => '17:00'],
                3 => ['enabled' => true, 'start_time' => '09:00', 'end_time' => '17:00'],
                4 => ['enabled' => true, 'start_time' => '09:00', 'end_time' => '17:00'],
                5 => ['enabled' => true, 'start_time' => '09:00', 'end_time' => '17:00'],
                6 => ['enabled' => false, 'start_time' => '09:00', 'end_time' => '17:00'],
                7 => ['enabled' => false, 'start_time' => '09:00', 'end_time' => '17:00']
            ];
        }

        // Format working hours for frontend
        $formatted_hours = [];
        foreach ($working_hours as $day_number => $hours) {
            $day_name = '';
            switch ($day_number) {
                case 1:
                    $day_name = 'Monday';
                    break;
                case 2:
                    $day_name = 'Tuesday';
                    break;
                case 3:
                    $day_name = 'Wednesday';
                    break;
                case 4:
                    $day_name = 'Thursday';
                    break;
                case 5:
                    $day_name = 'Friday';
                    break;
                case 6:
                    $day_name = 'Saturday';
                    break;
                case 7:
                    $day_name = 'Sunday';
                    break;
            }

            if ($hours['enabled']) {
                $formatted_hours[] = [
                    'day' => $day_name,
                    'day_key' => strtolower($day_name),
                    'start' => $hours['start_time'],
                    'end' => $hours['end_time']
                ];
            } else {
                $formatted_hours[] = [
                    'day' => $day_name,
                    'day_key' => strtolower($day_name),
                    'start' => null,
                    'end' => null
                ];
            }
        }

        // Get busy times (already booked appointments)
        $busy_times = $CI->apm->get_busy_times_by_service($service_id);

        // Prepare and log the final result
        $result = [
            'success' => true,
            'data' => [
                'service' => $service,
                'staff' => $staff_members,
                'working_hours' => $working_hours,
                'formatted_hours' => $formatted_hours,
                'busy_times' => $busy_times
            ]
        ];


        return $result;
    }
}


if (!function_exists('appointly_get_staff_schedule')) {
    /**
     * Get staff working schedule
     * Common helper used by both admin and public controllers
     * 
     * @param int $staff_id The staff ID to get schedule for
     * @return array Response with working hours
     */
    function appointly_get_staff_schedule($staff_id)
    {
        $CI = &get_instance();

        if (!$staff_id) {
            return [
                'success' => false,
                'message' => _l('appointly_staff_id_required')
            ];
        }

        // Load appointly model if not loaded
        if (!isset($CI->apm)) {
            $CI->load->model('appointly/appointly_model', 'apm');
        }

        // Get staff working hours from the staff_working_hours table
        $working_hours = $CI->apm->get_staff_working_hours($staff_id);

        if (empty($working_hours)) {
            // Fallback to company schedule
            $working_hours = $CI->apm->get_company_schedule();

            if (empty($working_hours)) {
                return [
                    'success' => false,
                    'message' => _l('appointly_no_working_hours')
                ];
            }
        }

        $formatted_hours = [];
        $schedule = [];

        // Format working hours for frontend
        foreach ($working_hours as $day => $hours) {
            $day_number = getWorkingDayNumber($day);

            $schedule[$day_number] = [
                'enabled' => $hours['is_available'] ?? $hours['is_enabled'] ?? false,
                'start_time' => $hours['start_time'],
                'end_time' => $hours['end_time']
            ];

            if ($schedule[$day_number]['enabled']) {
                $formatted_hours[] = [
                    'day' => _l('appointly_day_' . strtolower($day)),
                    'day_key' => strtolower($day),
                    'start' => date('H:i', strtotime($hours['start_time'])),
                    'end' => date('H:i', strtotime($hours['end_time']))
                ];
            }
        }

        return [
            'success' => true,
            'data' => [
                'schedule' => $schedule,
                'formatted_hours' => $formatted_hours
            ]
        ];
    }
}

/**
 * Helper function to convert weekday name to day number
 * 
 * @param string $day_name Weekday name
 * @return int Day number (1-7)
 */
function getWorkingDayNumber($day_name)
{
    // Handle null or empty day_name to prevent warnings
    if (empty($day_name)) {
        return 1; // Default to Monday if day_name is not provided
    }

    $days = [
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 7
    ];

    return $days[strtolower($day_name)] ?? 1;
}

/**
 * Helper function to get service duration from services array
 * 
 * @param int $service_id
 * @param array $services
 * @return int Duration in minutes
 */
function get_service_duration($service_id, $services)
{
    if (!$service_id || !$services) {
        return 60; // Default to 60 minutes
    }

    foreach ($services as $service) {
        if ($service['id'] == $service_id) {
            return $service['duration'];
        }
    }

    return 60; // Default to 60 minutes if not found
}

/**
 * Get blocked days for date picker
 * 
 * Returns the blocked days in a format that can be used by the datepicker
 * 
 * @return array Array of blocked days formatted for datepicker
 */
function get_appointly_blocked_days()
{
    $blocked_days = get_option('appointly_blocked_days');

    if (empty($blocked_days)) {
        return [];
    }

    $blocked_dates = json_decode($blocked_days, true);

    if (!is_array($blocked_dates)) {
        return [];
    }

    // Format dates for the datepicker - Use YYYY-MM-DD format for consistency
    $formatted_dates = [];
    foreach ($blocked_dates as $date) {
        // Make sure the date is properly formatted (Y-m-d)
        if (!empty($date) && strtotime($date)) {
            // Store in YYYY-MM-DD format for consistent date handling
            $formatted_dates[] = date('Y-m-d', strtotime($date));
        }
    }

    return $formatted_dates;
}

function generate_appointment_ics_content($appointment)
{
    // Parse appointment date and time
    $start_datetime = new DateTime($appointment['date'] . ' ' . $appointment['start_hour']);
    $end_datetime = clone $start_datetime;

    // Calculate end time from start_hour and end_hour or use duration
    if (!empty($appointment['end_hour'])) {
        $end_datetime = new DateTime($appointment['date'] . ' ' . $appointment['end_hour']);
    } else {
        // Add duration (default 1 hour if not specified)
        $duration_minutes = !empty($appointment['duration']) ? (int)$appointment['duration'] : 60;
        $end_datetime->add(new DateInterval('PT' . $duration_minutes . 'M'));
    }

    // Format dates for ICS (UTC format)
    $start_datetime->setTimezone(new DateTimeZone('UTC'));
    $end_datetime->setTimezone(new DateTimeZone('UTC'));

    $dtstart = $start_datetime->format('Ymd\THis\Z');
    $dtend = $end_datetime->format('Ymd\THis\Z');
    $dtstamp = gmdate('Ymd\THis\Z');

    // Generate unique ID
    $uid = 'appointment-' . $appointment['id'] . '-' . time() . '@' . $_SERVER['HTTP_HOST'];

    // Prepare description
    $description = '';
    if (!empty($appointment['description'])) {
        $description = escape_ics_text($appointment['description']);
    }

    if (!empty($appointment['google_meet_link'])) {
        $description .= ($description !== '' ? '\\n' : '') . escape_ics_text('Join video meeting: ' . $appointment['google_meet_link']);
    }

    // Prepare location
    $location = '';
    if (!empty($appointment['location'])) {
        $location = escape_ics_text($appointment['location']);
    }

    // Prepare organizer (provider)
    $organizer = '';
    if (!empty($appointment['provider_id'])) {
        $CI = &get_instance();
        $CI->load->model('staff_model');
        $provider = $CI->staff_model->get($appointment['provider_id']);
        if ($provider) {
            $organizer = 'ORGANIZER;CN=' . escape_ics_text($provider->firstname . ' ' . $provider->lastname) . ':MAILTO:' . $provider->email;
        }
    }

    // Prepare attendees
    $attendees = '';

    // Get staff attendees
    $CI = &get_instance();
    $CI->load->model('appointly/appointly_attendees_model', 'atm');
    $staff_attendees = $CI->atm->get($appointment['id']);

    foreach ($staff_attendees as $attendee) {
        $attendees .= 'ATTENDEE;CN=' . escape_ics_text($attendee['firstname'] . ' ' . $attendee['lastname']) . ':MAILTO:' . $attendee['email'] . "\r\n";
    }

    // Add client/contact as attendee
    if ($appointment['source'] === 'external') {
        if (!empty($appointment['email']) && !empty($appointment['name'])) {
            $attendees .= 'ATTENDEE;CN=' . escape_ics_text($appointment['name']) . ':MAILTO:' . $appointment['email'] . "\r\n";
        }
    } elseif ($appointment['source'] === 'internal' && !empty($appointment['contact_id'])) {
        // Get contact details
        $CI->load->model('clients_model');
        $contact = $CI->clients_model->get_contact($appointment['contact_id']);
        if ($contact) {
            $attendees .= 'ATTENDEE;CN=' . escape_ics_text($contact->firstname . ' ' . $contact->lastname) . ':MAILTO:' . $contact->email . "\r\n";
        }
    }

    // Build ICS content
    $ics = "BEGIN:VCALENDAR\r\n";
    $ics .= "VERSION:2.0\r\n";
    $ics .= "PRODID:-//Appointly//Appointment Calendar//EN\r\n";
    $ics .= "CALSCALE:GREGORIAN\r\n";
    $ics .= "METHOD:PUBLISH\r\n";
    $ics .= "BEGIN:VEVENT\r\n";
    $ics .= "UID:" . $uid . "\r\n";
    $ics .= "DTSTAMP:" . $dtstamp . "\r\n";
    $ics .= "DTSTART:" . $dtstart . "\r\n";
    $ics .= "DTEND:" . $dtend . "\r\n";
    $ics .= "SUMMARY:" . escape_ics_text($appointment['subject']) . "\r\n";

    if ($description) {
        $ics .= "DESCRIPTION:" . $description . "\r\n";
    }

    if ($location) {
        $ics .= "LOCATION:" . $location . "\r\n";
    }

    if ($organizer) {
        $ics .= $organizer . "\r\n";
    }

    if ($attendees) {
        $ics .= $attendees;
    }

    // Add reminder (using system setting)
    $reminderMinutes = (int) get_option('appointly_google_meet_reminder_minutes') ?: 30;
    $ics .= "BEGIN:VALARM\r\n";
    $ics .= "TRIGGER:-PT{$reminderMinutes}M\r\n";
    $ics .= "ACTION:DISPLAY\r\n";
    $ics .= "DESCRIPTION:Appointment Reminder\r\n";
    $ics .= "END:VALARM\r\n";

    $ics .= "STATUS:CONFIRMED\r\n";
    $ics .= "SEQUENCE:0\r\n";
    $ics .= "END:VEVENT\r\n";
    $ics .= "END:VCALENDAR\r\n";

    return $ics;
}

/**
 * Escape text for ICS format
 * 
 * @param string $text Text to escape
 * @return string Escaped text
 */
function escape_ics_text($text)
{
    // Remove HTML tags
    $text = strip_tags($text);

    // Escape special characters
    $text = str_replace(array("\\", ";", ",", "\n", "\r"), array("\\\\", "\\;", "\\,", "\\n", ""), $text);

    // Limit line length (ICS spec recommends 75 characters)
    $text = wordwrap($text, 73, "\r\n ", true);

    return $text;
}

/** Include the saved video room in invitations whose existing template omits it. */
function appointly_jitsi_invitation_link($template)
{
    $mailer = $GLOBALS['SENDING_EMAIL_TEMPLATE_CLASS'] ?? null;
    $invitationSlugs = ['appointment-submitted-to-contact', 'appointment-submitted-to-staff',
        'appointment-approved-to-contact', 'appointment-approved-to-staff',
        'appointment-cron-reminder-to-contact', 'appointment-cron-reminder-to-staff',
        'appointment-updated-to-contact', 'appointment-updated-to-staff'];
    if (!$template || !$mailer || !method_exists($mailer, 'get_merge_fields')
        || !in_array($mailer->slug ?? '', $invitationSlugs, true)
        || (get_option('video_meeting_provider') ?: 'jitsi') !== 'jitsi') { return $template; }
    $fields = $mailer->get_merge_fields();
    $link = trim((string)($fields['{appointment_google_meet_link}'] ?? ''));
    require_once dirname(__DIR__, 2) . '/google_meet/helpers/jitsi_helper.php';
    if (!jitsi_meeting_room((object)['meet_link' => $link])
        || strpos($template->message, '{appointment_google_meet_link}') !== false
        || strpos($template->message, $link) !== false
        || strpos($template->message, html_escape($link)) !== false) { return $template; }
    $template = clone $template;
    $template->message .= !empty($template->plaintext)
        ? "\nJoin video meeting: " . $link
        : '<p><strong>Join video meeting:</strong> <a href="' . html_escape($link) . '">' . html_escape($link) . '</a></p>';
    return $template;
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
  // db_prefix() . 'appointly_appointments.id as id',
  db_prefix() . 'appointly_appointments.subject as subject',
  'CAST(CONCAT(' . db_prefix() . 'appointly_appointments.date, \' \', '
    . db_prefix() . 'appointly_appointments.start_hour) AS DATETIME) as date',
  'firstname as creator_firstname',
  db_prefix() . 'appointly_appointments.service_id as service_id',
  db_prefix() . 'appointly_appointments.provider_id as provider_id',
  db_prefix() . 'appointly_appointments.status as status',
  db_prefix() . 'appointly_appointments.date_created as date_created',
  db_prefix() . 'appointly_appointments.id as id',
];

$sIndexColumn = 'subject';
$sTable       = db_prefix() . 'appointly_appointments';

$join = [
  'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = '
    . db_prefix() . 'appointly_appointments.created_by',
  'LEFT JOIN ' . db_prefix() . 'appointly_services ON ' . db_prefix()
    . 'appointly_services.id = ' . db_prefix() . 'appointly_appointments.service_id',
  'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'appointly_appointments.contact_id',
  'LEFT JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'appointly_appointments.contact_id',
];

$additionalSelect = [
  db_prefix() . 'appointly_appointments.source as source',
  db_prefix() . 'appointly_appointments.date as appointment_date',
  db_prefix() . 'appointly_appointments.created_by as created_by',
  'lastname as creator_lastname',
  db_prefix() . 'appointly_appointments.name as name',
  db_prefix() . 'appointly_services.name as service_name',
  db_prefix() . 'appointly_services.color as service_color',
  db_prefix() . 'appointly_services.price as service_price',
  db_prefix() . 'appointly_appointments.contact_id as contact_id',
  db_prefix() . 'appointly_appointments.google_calendar_link',
  db_prefix() . 'appointly_appointments.outlook_calendar_link',
  db_prefix() . 'appointly_appointments.feedback',
  db_prefix() . 'appointly_appointments.email as contact_email',
  db_prefix() . 'appointly_appointments.phone as phone',
  db_prefix() . 'appointly_appointments.google_event_id',
  db_prefix() . 'appointly_appointments.outlook_event_id',
  db_prefix() . 'appointly_appointments.outlook_added_by_id',
  db_prefix() . 'appointly_appointments.description',
  db_prefix() . 'appointly_appointments.start_hour'
];

$where = [];

if (! is_admin() && ! staff_can('view', 'appointments')) {
  $where[] = 'AND (' . db_prefix() . 'appointly_appointments.created_by='
    . get_staff_user_id() . ')
    OR ' . db_prefix() . 'appointly_appointments.id
    IN (SELECT appointment_id FROM ' . db_prefix()
    . 'appointly_attendees WHERE staffid=' . get_staff_user_id() . ')';
}

$filters = createFilters();

if (count($filters) > 0) {
  $where[] = 'AND (' . prepare_dt_filter($filters) . ')';
}

$result  = data_tables_init(
  $aColumns,
  $sIndexColumn,
  $sTable,
  $join,
  $where,
  $additionalSelect
);

$output  = $result['output'];
$rResult = $result['rResult'];

[
  $CI,
  $googleData,
  $filteredResults,
  $output,
  $rResult,
]
  = checkGoogleTwoWaySyncResults(
    $filters,
    $rResult,
    $output,
    $result
  );
foreach ($rResult as $aRow) {

  $row = [];

  $googleLink = $aRow['google_calendar_link'] ?? '';

  $subjectHtml = '<div class="tw-flex tw-flex-col tw-group">';

  $subjectHtml .= '<div class="tw-flex tw-items-center tw-gap-2">';

  // Calendar badges with links 
  if (isset($aRow['google_event_id']) && $aRow['google_event_id']    || !empty($aRow['from_2way_sync'])) {
    $subjectHtml .= '<a href="' . $aRow['google_calendar_link'] . '"
        target="_blank" 
        class="tw-text-[#4285f4] hover:tw-opacity-80" 
        data-toggle="tooltip" 
        title="' . _l('appointment_view_in_calendar') . '">
        <i class="fa-brands fa-google" style="color:#4285F4"></i>
    </a>';
  }

  if (isset($aRow['outlook_event_id'])    && $aRow['outlook_added_by_id'] == get_staff_user_id()) {
    $subjectHtml .= '<a href="' . $aRow['outlook_calendar_link'] . '"
                        target="_blank" 
                        class="tw-text-[#00a1f1] hover:tw-opacity-80" 
                        data-toggle="tooltip" 
                        title="' . _l('appointments_outlook_view_in_calendar') . '">
                        <i class="fa fa-envelope tw-text-primary-500"></i>
                    </a>';
  }

  // Subject text (clickable link to view appointment)
  if (staff_can('view', 'appointments')) {
    $subjectHtml .= '<a href="' . admin_url('appointly/appointments/view?appointment_id=' . $aRow['id']) . '" class="tw-font-medium hover:tw-text-primary-600 tw-transition-colors">' . $aRow['subject'] . '</a>';
  } else {
    $subjectHtml .= '<span class="tw-font-medium">' . $aRow['subject'] . '</span>';
  }
  $subjectHtml .= '</div>';

  $subjectHtml .= '<div class="row-options tw-mt-1">';

  // Check if it's a Google synced appointment
  if (!empty($aRow['from_2way_sync'])) {
    // For Google synced appointments, show View in Google Calendar and Delete options
    $subjectHtml .= '<a href="' . $aRow['google_calendar_link'] . '" target="_blank">' . _l('appointment_open_google_calendar') . '</a>';

    // Add delete button for Google synced appointments
    if (staff_can('delete', 'appointments')) {
      // Extract Google event ID from calendar link if needed
      $googleEventId = $aRow['google_event_id'] ?? '';

      // If no direct event ID, extract from google_calendar_link
      if (empty($googleEventId) && !empty($aRow['google_calendar_link'])) {
        // Extract event ID from eid parameter in Google Calendar URL
        if (preg_match('/[?&]eid=([^&]+)/', $aRow['google_calendar_link'], $matches)) {
          $decodedEid = base64_decode($matches[1]);
          // Extract the event ID (before the first space and underscore)
          if (strpos($decodedEid, ' ') !== false) {
            $googleEventId = explode(' ', $decodedEid)[0];
          } elseif (strpos($decodedEid, '_') !== false) {
            $googleEventId = explode('_', $decodedEid)[0];
          } else {
            $googleEventId = $decodedEid;
          }
        }
        // Alternative: try to extract from cid parameter
        elseif (preg_match('/[?&]cid=([^&]+)/', $aRow['google_calendar_link'], $matches)) {
          $googleEventId = urldecode($matches[1]);
        }
        // Fallback: use appointment ID if no Google event ID found
        if (empty($googleEventId)) {
          $googleEventId = $aRow['id'];
        }
      }

      // Use appointment ID as fallback if still empty
      if (empty($googleEventId)) {
        $googleEventId = $aRow['id'];
      }

      $subjectHtml .= ' | <a href="javascript:void(0);" class="text-danger" data-google-event-id="' . $googleEventId . '" onclick="deleteGoogleSyncedAppointment(this)">' . _l('delete') . '</a>';
    }
  } else {
    // Regular appointment options
    if (staff_can('view', 'appointments')) {
      $subjectHtml .= '<a href="' . admin_url('appointly/appointments/view?appointment_id='  . $aRow['id']) . '">' . _l('view') . '</a>';
    }
    if (staff_can('edit', 'appointments')) {
      $subjectHtml .= ' | <a href="' . admin_url('appointly/appointments/update_page/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }

    $task_data = [
      'id'           => $aRow['id'],
      'name'         => $aRow['name'],
      'subject'      => $aRow['subject'],
      'description'  => $aRow['description'] ?? '',
      'date'         => _dt($aRow['date']),
      'contact_id'   => $aRow['contact_id'],
      'email'        => $aRow['contact_email'],
      'phone'        => $aRow['phone'],
      'created_by'   => $aRow['created_by'],
      'service_name' => $aRow['service_name'] ?? '',
      'service_price' => $aRow['service_price'] ?? 0,
    ];

    $subjectHtml .= ' | <a href="#" onclick="new_task_from_relation_appointment(' . html_escape(json_encode($task_data)) . '); return false;" class="tw-text-neutral-500 hover:tw-text-neutral-700">
        <i class="fa fa-tasks tw-mr-1"></i> ' . _l('new_task') . '
    </a>';

    // Only show "Convert to Lead" for external appointments (not internal contacts)
    if ($aRow['source'] === 'external') {
      $lead_data = [
        'id'          => $aRow['id'],
        'name'        => $aRow['name'],
        'email'       => $aRow['contact_email'],
        'phone'       => $aRow['phone'],
        'description' => $aRow['description'] ?? '',
        'subject'     => $aRow['subject'],
        'address'     => $aRow['address'] ?? '',
        'city'        => $aRow['city'] ?? '',
        'state'       => $aRow['state'] ?? '',
        'country'     => $aRow['country'] ?? '',
        'zip'         => $aRow['zip'] ?? '',
        'source'      => $aRow['source'],
        'contact_id'  => $aRow['contact_id'],
        'service_name' => $aRow['service_name'] ?? '',
        'date'        => _dt($aRow['date']),
      ];

      $subjectHtml .= ' | <a href="#" 
              class="convert-to-lead tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700" 
              onclick="init_appointment_lead(' . html_escape(json_encode($lead_data)) . '); return false;">
              <i class="fa fa-user-plus tw-mr-1"></i> ' . _l('appointments_convert_to_lead_label') . '
          </a>';
    }

    if (staff_can('delete', 'appointments')) {
      $subjectHtml .= ' | <a href="javascript:void(0);" class="text-danger" onclick="deleteAppointment(\'' . $aRow['id'] . '\', this)">' . _l('delete') . '</a>';
    }
  }

  $subjectHtml .= '</div>';
  $subjectHtml .= '</div>';
  $row[]       = $subjectHtml;

  $dateClass = getDateStatusClass($aRow['date']);
  $row[]     = '<div class="tw-flex tw-items-center">
      <span class="tw-w-2 tw-h-2 tw-rounded-md ' . $dateClass . ' tw-mr-2"></span>
      <span class="tw-text-sm">' . _dt($aRow['date']) . '</span>
  </div>';

  $initiatedBy = $aRow['creator_firstname'] . ' ' . $aRow['creator_lastname'];

  if ($aRow['source'] == 'external' && empty($aRow['created_by'])) {
    $initiatedBy .= '<span class="tw-text-xs tw-font-medium tw-text-neutral-500">' . $aRow['name'] . '</span>';
    $row[] = '<div class="tw-flex tw-items-center">
      <span class="tw-text-sm">' . $initiatedBy . '</span>
    </div>';
  } else {
    $row[] = '<div class="tw-flex tw-items-center">
      <a target="_blank" href="' . admin_url('staff/profile/' . $aRow['created_by']) . '">
        <img src="' . staff_profile_image_url($aRow['created_by'], 'small') . '"
             class="tw-w-8 tw-h-8 tw-rounded-md tw-mr-2" alt="' . $initiatedBy . '"/>
        <span class="tw-text-sm tw-font-medium">' . $initiatedBy . '</span>
      </a>
    </div>';
  }

  // Service with clean badge
  if (isset($aRow['service_id'])) {
    $serviceClass = $aRow['service_color'] ?? 'tw-bg-neutral-50';
    $row[] = '<a target="_blank" href="' . admin_url(APPOINTLY_MODULE_NAME . '/services/service/' . $aRow['service_id']) . '">
        <span class="tw-px-2 tw-py-1 tw-text-xs tw-rounded-md tw-text-white" style="background-color: ' . $serviceClass . ';">' . ($aRow['service_name'] ?? 'N/A') . '</span>
    </a>';
  } else {
    $row[] = '<span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-neutral-50 tw-text-neutral-700">N/A</span>';
  }

  // Provider with avatar
  $provider_id   = $aRow['provider_id'] ?? '';
  $provider_name = '';
  if (empty($provider_id)) {
    $row[] = '<span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-neutral-50 tw-text-neutral-700">N/A</span>';
  } else {
    $provider      = get_staff($provider_id);
    $provider_name = $provider ? $provider->firstname . ' ' . $provider->lastname : '';
    $row[]         = '<div class="tw-flex tw-items-center">
        <a target="_blank" href="' . admin_url('staff/profile/' . $provider_id) . '">
            <img src="' . staff_profile_image_url($provider_id) . '"
                 class="tw-w-8 tw-h-8 tw-rounded-md tw-mr-2" alt="' . html_escape($provider_name) . '" />
            <span class="tw-text-sm tw-font-medium">' . html_escape($provider_name) . '</span>
        </a>
    </div>';
  }

  // Source with badge
  $sourceLabels = [
    'internal'        => '<span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-info-50 tw-text-primary-500">' . _l('appointment_source_internal_client') . '</span>',
    'internal_staff'  => '<span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-info-50 tw-text-neutral-700">' . _l('appointment_internal_staff') . '</span>',
    'lead_related'    => '<span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-info-50 tw-text-info-700">' . _l('appointment_source_lead') . '</span>',
    'external'        => '<span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-info-50 tw-text-neutral-700">' . _l('appointment_source_external_text') . '</span>',
    'google_calendar' => '<span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-info-50 tw-text-info-700">' . _l('appointly_google_synced_title') . '</span>',
  ];

  // Determine the source label with client/lead info
  $sourceHtml = '';

  if (!empty($aRow['from_2way_sync'])) {
    $sourceHtml = $sourceLabels['google_calendar'];
  } elseif ($aRow['source'] == 'external' && empty($aRow['created_by'])) {
    $sourceHtml = '<span class="tw-px-2 tw-py-1 tw-text-xs tw-font-medium tw-rounded-md tw-bg-info-50 tw-text-neutral-700">' . _l('appointment_booked_from_external_booking_form') . '</span>';
  } else {
    $sourceHtml = $sourceLabels[$aRow['source']] ?? $sourceLabels['internal'];
  }

  // Add client/lead name if available
  if (!empty($aRow['name']) && in_array($aRow['source'], ['internal', 'lead_related', 'external'])) {
    if ($aRow['source'] == 'internal' && !empty($aRow['contact_id'])) {
      // Add link to client's contact profile
      $sourceHtml .= '<div class="tw-mt-1 tw-text-xs tw-font-medium">
        <a href="' . admin_url('clients/client/' . get_user_id_by_contact_id($aRow['contact_id']) . '?contactid=' . $aRow['contact_id']) . '" target="_blank" class="tw-text-primary-700 hover:tw-underline" title="' . _l('view') . '" data-toggle="tooltip">
          ' . $aRow['name'] . '
        </a>
      </div>';
    } else if ($aRow['source'] == 'lead_related' && !empty($aRow['contact_id'])) {
      // Add link to lead profile
      $sourceHtml .= '<div class="tw-mt-1 tw-text-xs tw-font-medium">
        <a href="' . admin_url('leads/index/' . $aRow['contact_id']) . '" target="_blank" class="tw-text-primary-700 hover:tw-underline" title="' . _l('view') . '" data-toggle="tooltip">
          ' . $aRow['name'] . '
        </a>
      </div>';
    } else {
      // Default display for external sources or when no contact_id is available
      $sourceHtml .= '<div class="tw-mt-1 tw-text-xs tw-font-medium">' . $aRow['name'] . '</div>';
    }
  }

  // For "Booking From" external sources
  if ($aRow['source'] == 'external' && !empty($aRow['email'])) {
    if (!empty($aRow['name'])) {
      $sourceHtml .= '<div class="tw-mt-1 tw-text-xs tw-text-muted">(' . $aRow['email'] . ')</div>';
    } else {
      $sourceHtml .= '<div class="tw-mt-1 tw-text-xs tw-font-medium">' . $aRow['email'] . '</div>';
    }
  }

  $row[] = '<div class="tw-flex tw-flex-col">' . $sourceHtml . '</div>';

  $row[] = getStatusHtml($aRow);
  $row[] = '<span class="tw-text-sm">' . _dt($aRow['date_created'] ??
    $aRow['date']) . '</span>';

  // Add row class for past appointments
  $row['DT_RowClass'] = 'has-row-options';

  // Check if appointment is in the past
  if (!empty($aRow['date'])) {
    $timestamp = strtotime($aRow['date']);
    if ($timestamp !== false && $timestamp < time()) {
      $row['DT_RowClass'] .= ' past-appointment-row';
    }
  }

  $output['aaData'][] = $row;
}


// Helper functions
function getDateStatusClass($date)
{
  if (empty($date)) {
    return 'tw-bg-neutral-50';
  }

  // Safely convert to string and handle null/empty values
  $timestamp = is_string($date) || is_numeric($date) ? strtotime((string) $date) : false;
  if ($timestamp === false) {
    return 'tw-bg-neutral-50';
  }

  $now  = time();
  $diff = $timestamp - $now;

  if ($diff < 0) {
    return 'tw-bg-red-500';
  } elseif ($diff < 24 * 3600) {
    return 'tw-bg-amber-500';
  } elseif ($diff < 7 * 24 * 3600) {
    return 'tw-bg-blue-500';
  } else {
    return 'tw-bg-green-500';
  }
}


function getStatusHtml($aRow)
{
  // Check if this is a Google synced appointment
  $isGoogleSynced = !empty($aRow['from_2way_sync']);

  if ($isGoogleSynced) {
    // For Google synced appointments, determine status based on date
    $timestamp = !empty($aRow['date']) ? strtotime((string) $aRow['date']) : false;
    $now = time();

    if ($timestamp === false || $timestamp > $now) {
      $statusClass = 'tw-bg-info-50 tw-text-info-700';
      $statusText = _l('appointment_upcoming');
    } else {
      $statusClass = 'tw-bg-danger-200';
      $statusText = _l('appointment_missed_label');
    }

    // Return a read-only badge for Google synced appointments
    return '<div class="tw-inline-block">
              <span class="tw-p-3 tw-py-1 tw-text-sm tw-rounded-md ' . $statusClass . '">
                ' . $statusText . '
              </span>
            </div>';
  }

  // For regular appointments
  if (staff_can('edit', 'appointments')) {
    // Get status text and HTML version for display
    $currentStatus = checkAppointlyStatus($aRow); // Text version for dropdown label
    $statusHtml = checkAppointlyStatus($aRow, 'html'); // HTML version for read-only display

    // Get status color class
    $statusClass = '';

    // Get status color class based on appointment status
    switch ($aRow['status']) {
      case 'cancelled':
        $statusClass = 'tw-bg-danger-200 tw-text-danger-700';
        break;
      case 'completed':
      case 'finished':
        $statusClass = 'tw-bg-success-50 tw-text-success-700';
        break;
      case 'in-progress':
        $statusClass = 'tw-bg-primary-50 tw-text-primary-700';
        break;
      case 'pending':
        $statusClass = 'tw-bg-warning-50 tw-text-warning-700';
        break;
      case 'approved':
        $statusClass = 'tw-bg-success-50 tw-text-success-700';
        break;
      case 'no-show':
        $statusClass = 'tw-bg-danger-200 tw-text-danger-700';
        break;
      default:
        $statusClass = 'tw-bg-neutral-50 tw-text-neutral-700';
    }

    // Is the appointment completed? If so, don't create a dropdown
    $isCompleted = $aRow['status'] == 'completed';

    if ($isCompleted) {
      // For completed appointments, just show a badge - no dropdown
      return '<div class="tw-inline-block">
                <span class="tw-px-3 tw-py-1 tw-text-sm tw-font-medium tw-rounded-md ' . $statusClass . '">
                  ' . $currentStatus . '
                </span>
              </div>';
    }

    // For other statuses, create a dropdown
    $outputStatus = '<div class="dropdown tw-inline-block ' . (!empty($aRow['from_2way_sync']) ? 'google-synced-disabled' : '') . '">';

    $outputStatus .= '<a href="#" class="tw-px-3 tw-py-1 tw-text-sm tw-font-medium tw-rounded-md '
      . $statusClass . ' hover:tw-opacity-80 tw-inline-flex tw-items-center"
                            id="appointmentStatusesDropdown' . $aRow['id'] . '"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false">';
    $outputStatus .= $currentStatus;
    $outputStatus .= '<i class="fa fa-caret-down tw-ml-2"></i>';
    $outputStatus .= '</a>';

    // Show dropdown menu with available actions
    $outputStatus .= '<ul class="dropdown-menu tw-py-2 tw-min-w-[160px] tw-mt-1 tw-bg-white tw-shadow-lg tw-rounded-lg tw-border tw-border-neutral-200"
                                aria-labelledby="appointmentStatusesDropdown'
      . $aRow['id'] . '">';

    // Use universal status to determine available actions
    $currentStatusValue = $aRow['status'];

    // Needs approval
    if ($currentStatusValue == 'pending' && staff_can('approve', 'appointments')) {
      $outputStatus .= '<li>
      <a href="#" class="tw-px-4 tw-py-2 tw-text-sm tw-text-neutral-700 hover:tw-bg-neutral-50 tw-block"
        onclick="markAppointmentAsApproved(' . $aRow['id'] . '); return false;">' . _l('appointment_approve') . '</a></li>';
    }

    // Can be cancelled
    if (
      $currentStatusValue != 'cancelled' && $currentStatusValue != 'completed'
      && (staff_can('edit', 'appointments') || $aRow['created_by'] == get_staff_user_id() || in_array(get_staff_user_id(), explode(',', $aRow['attendees'])))
    ) {
      $outputStatus .= '<li>
      <a href="#" class="tw-px-4 tw-py-2 tw-text-sm tw-text-neutral-700 hover:tw-bg-neutral-50 tw-block"
       onclick="markAppointmentAsCancelled(' . $aRow['id'] . '); return false;">' . _l('task_mark_as', _l('appointment_status_cancelled')) . '</a></li>';
    }

    // Can be finished
    if (
      $currentStatusValue != 'completed' && $currentStatusValue != 'cancelled' &&
      $currentStatusValue == 'in-progress' &&
      (staff_can('edit', 'appointments') || $aRow['created_by'] == get_staff_user_id() || in_array(get_staff_user_id(), explode(',', $aRow['attendees'])))
    ) {
      $outputStatus .= '<li>
      <a href="#" class="tw-px-4 tw-py-2 tw-text-sm tw-text-neutral-700 hover:tw-bg-neutral-50 tw-block" 
       onclick="markAppointmentAsFinished(' . $aRow['id'] . '); return false;"> ' . _l('task_mark_as', _l('appointment_status_completed')) . '</a></li>';
    }

    // Can be marked as no-show
    if (
      $currentStatusValue != 'no-show' && $currentStatusValue != 'completed' &&
      (staff_can('edit', 'appointments') || $aRow['created_by'] == get_staff_user_id() || in_array(get_staff_user_id(), explode(',', $aRow['attendees'])))
    ) {
      $outputStatus .= '<li>
      <a href="#" class="tw-px-4 tw-py-2 tw-text-sm tw-text-neutral-700 hover:tw-bg-neutral-50 tw-block" 
      onclick="markAppointmentAsNoShow(' . $aRow['id'] . '); return false;">' . _l('task_mark_as', _l('appointment_status_no-show')) . '</a></li>';
    }

    // Can be marked as ongoing
    if (
      ($currentStatusValue == 'cancelled' || $currentStatusValue == 'no-show') &&
      (staff_can('edit', 'appointments') || $aRow['created_by'] == get_staff_user_id() || in_array(get_staff_user_id(), explode(',', $aRow['attendees'])))
    ) {
      $outputStatus .= '<li>
      <a href="#" class="tw-px-4 tw-py-2 tw-text-sm tw-text-neutral-700 hover:tw-bg-neutral-50 tw-block" 
       onclick="markAppointmentAsOngoing(' . $aRow['id'] . '); return false;">' . _l('task_mark_as', _l('appointment_status_in-progress')) . '</a></li>';
    }

    $outputStatus .= '</ul>';
    $outputStatus .= '</div>';

    return $outputStatus;
  } else {
    // For read-only status (no edit permissions)
    $currentStatus = checkAppointlyStatus($aRow);
    $statusClass = '';

    // Get status color class based on appointment status
    switch ($aRow['status']) {
      case 'cancelled':
        $statusClass = 'tw-bg-danger-200 tw-text-danger-700';
        break;
      case 'completed':
      case 'finished':
        $statusClass = 'tw-bg-success-50 tw-text-success-700';
        break;
      case 'in-progress':
        $statusClass = 'tw-bg-primary-50 tw-text-primary-700';
        break;
      case 'pending':
        $statusClass = 'tw-bg-warning-50 tw-text-warning-700';
        break;
      case 'approved':
        $statusClass = 'tw-bg-success-50 tw-text-success-700';
        break;
      case 'no-show':
        $statusClass = 'tw-bg-danger-200 tw-text-danger-700';
        break;
      default:
        $statusClass = 'tw-bg-neutral-50 tw-text-neutral-700';
    }

    $outputStatus  = '<div class="tw-inline-block">';
    $outputStatus  .= '<span class="tw-px-3 tw-py-1 tw-text-sm tw-font-medium tw-rounded-md '
      . $statusClass . '">';
    $outputStatus  .= $currentStatus;
    $outputStatus  .= '</span>';
    $outputStatus  .= '</div>';
  }

  return $outputStatus;
}

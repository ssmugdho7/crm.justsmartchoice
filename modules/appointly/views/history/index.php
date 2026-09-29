<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'subject',
    'CAST(CONCAT(date, \' \', start_hour) AS DATETIME) as date',
    'firstname as creator_firstname',
    'description',
    'source'
];


$sIndexColumn = 'id';
$sTable = db_prefix() . 'appointly_appointments';

$where = [];

if (!staff_can('view', 'appointments')) {
    array_push($where, 'AND (' . db_prefix() . 'appointly_appointments.created_by=' . get_staff_user_id() . ')
    OR ' . db_prefix() . 'appointly_appointments.id
    IN (SELECT appointment_id FROM ' . db_prefix() . 'appointly_attendees WHERE staff_id=' . get_staff_user_id() . ')');
}


$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'appointly_appointments.created_by',
];

$additionalSelect = [
    'created_by',
    'status',
    'lastname as creator_lastname',
    'firstname as creator_firstname',
    'name',
    db_prefix() . 'appointly_appointments.email as contact_email',
    db_prefix() . 'appointly_appointments.phone',
    'contact_id',
    'google_calendar_link',
    'google_added_by_id',
    'outlook_calendar_link',
    'outlook_added_by_id',
    'outlook_event_id',
    'feedback'
];
$where[] = "AND status = 'completed'";

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {

    $label_class = 'primary';
    $tooltip = '';

    if (date('Y-m-d H:i', strtotime($aRow['date'])) < date('Y-m-d H:i')) {
        $label_class = 'danger';
        $tooltip = 'data-toggle="tooltip" title="' . _l('appointment_missed') . '"';
    }

    $row = [];

    $hrefAttr = 'data-toggle="tooltip" title="' . _l('appointment_view_meeting') . '" href="' . admin_url('appointly/appointments/view?appointment_id=' . $aRow['id']) . '"';
    $row[] = $aRow['id'];

    $nameRow = '<a href="' . admin_url('appointly/appointments/view?appointment_id=' . $aRow['id']) . '">' . $aRow['subject'] . '</a>';

    if ($aRow['status'] && $aRow['status'] == 'cancelled') {
        $nameRow .= '<p class="text-danger no-mbot">' . _l('appointment_cancelled') . '</p>';
    }

    $nameRow .= '<div class="row-options no-mtop">';

    $nameRow .= '<a ' . $hrefAttr . '>' . _l('view') . '</a>';

    if (staff_can('edit', 'appointments')) {
        $nameRow .= ' | <a data-toggle="tooltip" title="' . _l('appointment_edit_history_notes') . '" data-id="' . $aRow['id'] . '" onclick="editAppointmentNotes(this)" style="cursor:pointer;">' . _l('appointment_edit_history_notes') . '</a>';
    }
    if (staff_can('delete', 'appointments')) {
        $nameRow .= ' | <a href="#" class="text-danger" onclick="deleteAppointment(' . (int) $aRow['id'] . ', this); return false;">' . _l('delete') . '</a>';
    }

    $nameRow .= '</div>';

    $row[] = $nameRow;
    // the feedback needs to be shown in starts like in appointment.php
    $feedback = $aRow['feedback'] ?? 'N/A';
    $stars = '<div class="tw-flex tw-items-center tw-gap-3">';
    for ($i = 0; $i < 5; $i++) {
        $starColor = ($feedback > $i) ? 'tw-text-yellow-400' : 'tw-text-neutral-300';
        $stars .= '<i class="fa fa-star ' . $starColor . ' tw-text-black"></i>';
    }
    $stars .= '</div>';
    $row[] = $stars;

    $row[] = '<span  ' . $tooltip . ' class="label label-' . $label_class . '">' . _dt($aRow['date']) . '</span>';


    $initiatedBy = $aRow['creator_firstname'] . ' ' . $aRow['creator_lastname'];

    if ($aRow['source'] == 'external' && empty($aRow['created_by'])) {
        $initiatedBy .= '<span class="tw-text-xs tw-font-medium tw-text-neutral-500" data-toggle="tooltip" title="' . _l('appointment_externally_booked_info') . '">' . $aRow['name'] . '</span>';
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

    $row[] = $aRow['description'];

    if ($aRow['source'] == 'external') {
        $row[] = _l('appointments_source_external_label');
    }
    if ($aRow['source'] == 'internal') {
        $row[] = _l('appointments_source_internal_label');
    }
    if ($aRow['source'] == 'lead_related') {
        $row[] = _l('lead');
    }

    $options = '';
    $_google_calendar_link = $aRow['google_calendar_link'] !== null && $aRow['google_added_by_id'] == get_staff_user_id();
    $_outlook_calendar_link = $aRow['outlook_calendar_link'] !== null && $aRow['outlook_added_by_id'] == get_staff_user_id();

    $options .= '<div class="text-center">';

    if ($_google_calendar_link) {
        $options .= '<a data-toggle="tooltip" title="' . _l('appointment_open_google_calendar') . '" href="' . $aRow['google_calendar_link'] . '" target="_blank" class="mleft10 calendar_list"><i class="fa-brands fa-google" aria-hidden="true"></i></a>';
    }

    if ($_outlook_calendar_link) {
        $options .= '<a data-outlook-id="' . $aRow['outlook_event_id'] . '" id="outlookLink_' . $aRow['id'] . '" data-toggle="tooltip" title="' . _l('appointment_open_outlook_calendar') . '" href="' . $aRow['outlook_calendar_link'] . '" target="_blank" class="mleft5 calendar_list float-right"><i class="fa-regular fa-envelope" aria-hidden="true"></i></a>';
    }
    if (!$_google_calendar_link && !$_outlook_calendar_link) {
        $options .= '<p class="text-muted">' . _l('appointment_not_added_to_calendars_yet') . '</p>'; #lang
    }

    $options .= '</div>';


    $row['DT_RowId'] = 'appointment_id' . $aRow['id'];

    if (isset($row['DT_RowClass'])) {
        $row['DT_RowClass'] .= ' has-row-options';
    } else {
        $row['DT_RowClass'] = 'has-row-options';
    }

    $row[] = $options;

    $output['aaData'][] = $row;
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');
init_head();
$source = $appointment['source'] ?? '';

$rel_type = $appointment['source'] ?? 'internal';
if ($rel_type == 'lead') {
    $rel_type = 'lead_related';
}

$lead_id = '';
if ($rel_type == 'lead_related' && isset($appointment['contact_id'])) {
    $lead_id = $appointment['contact_id'];
}
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-font-semibold tw-pl-2 tw-text-gray-800 tw-text-left sm:tw-text-right tw-w-full sm:tw-w-auto tw-mt-2 sm:tw-mt-0"><?= _l('appointment_edit_appointment') ?></h4>
                <div class="panel_s">
                    <div class="panel-body tw-p-4 sm:tw-p-6">
                        <!-- Header Section -->
                        <div class="tw-mb-6">
                            <div class="tw-flex tw-flex-col sm:tw-flex-row tw-justify-between tw-items-start sm:tw-items-center tw-gap-4 tw-mb-4">
                                <div class="tw-flex tw-flex-col sm:tw-flex-row tw-w-full sm:tw-w-auto tw-gap-2 sm:tw-gap-3">
                                    <a href="<?= admin_url('appointly/appointments') ?>" class="btn btn-default tw-w-full sm:tw-w-auto">
                                        <i class="fa fa-arrow-left tw-mr-2"></i><?= _l('appointment_want_to_go_back') ?>
                                    </a>
                                    <a href="<?= admin_url('appointly/appointments/view?appointment_id=' . $appointment['id']) ?>" class="btn btn-default tw-w-full sm:tw-w-auto">
                                        <i class="fa fa-eye tw-mr-2"></i><?= _l('view') ?>
                                    </a>
                                </div>
                                <?php
                                if (($appointment['google_event_id'] != '') || ($appointment['outlook_event_id'] != '')): ?>
                                    <div class="tw-flex tw-flex-col sm:tw-flex-row tw-gap-2 tw-justify-end">

                                        <?php
                                        if ($appointment['google_event_id'] != ''): ?>
                                            <div class="tw-group tw-w-full sm:tw-w-auto">
                                                <div class="btn btn-default sm:tw-w-auto tw-w-full calendar-btn-container">
                                                    <a href="<?= $appointment['google_calendar_link']; ?>" target="_blank" class="calendar-btn-open tw-text-neutral-700">
                                                        <i class="fab fa-google"></i>
                                                        <span><?= _l('appointment_google_calendar'); ?></span>
                                                    </a>
                                                    <?php
                                                    if (staff_can('edit', 'appointments')): ?>
                                                        <div class="calendar-btn-delete"
                                                            onclick="deleteGoogleIntegration(<?= $appointment['id'] ?>, '<?= $appointment['google_event_id'] ?>')">
                                                            <i class="fa fa-times"></i>
                                                        </div>
                                                    <?php
                                                    endif; ?>
                                                </div>
                                            </div>
                                        <?php
                                        endif; ?>

                                        <?php
                                        if (isset($appointment['outlook_event_id']) && $appointment['outlook_event_id'] != ''): ?>
                                            <div class="tw-group tw-w-full sm:tw-w-auto">
                                                <div class="btn btn-default sm:tw-w-auto tw-w-full calendar-btn-container">
                                                    <a href="<?= $appointment['outlook_calendar_link']; ?>" target="_blank" class="calendar-btn-open tw-text-neutral-700">
                                                        <i class="fab fa-microsoft"></i>
                                                        <span><?= _l('appointment_outlook_calendar'); ?></span>
                                                    </a>
                                                    <?php
                                                    if (staff_can('edit', 'appointments')): ?>
                                                        <div class="calendar-btn-delete"
                                                            onclick="deleteOutlookIntegration(<?= $appointment['id']; ?>, '<?= $appointment['outlook_event_id']; ?>')">
                                                            <i class="fa fa-times"></i>
                                                        </div>
                                                    <?php
                                                    endif; ?>
                                                </div>
                                            </div>
                                        <?php
                                        endif; ?>

                                    </div>
                                <?php
                                endif; ?>
                            </div>
                        </div>
                        <hr class="tw-my-4">
                        <!-- Appointment Form -->
                        <form action="<?= admin_url('appointly/appointments/update/' . $appointment['id']) ?>" method="post" id="appointment-update-form" autocomplete="off">
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                            <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?? '' ?>">
                            <input type="hidden" name="google_event_id" value="<?= $appointment['google_event_id'] ?? '' ?>">
                            <input type="hidden" name="outlook_event_id" value="<?= $appointment['outlook_event_id'] ?? '' ?>">
                            <input type="hidden" name="rel_lead_type" id="rel_lead_type" value="leads">
                            <?= form_hidden('source', $source) ?>

                            <div class="row">
                                <!-- LEFT COLUMN - WHO/RELATION/CONTEXT -->
                                <div class="col-md-6">
                                    <h5 class="tw-m-0 tw-mb-4 tw-border tw-rounded-md tw-border-solid tw-border-neutral-300 tw-p-2 tw-w-fit tw-font-medium"><?= _l('appointment_contact_relationship') ?></h5>

                                    <!-- Related Type -->
                                    <div id="related_field_wrapper">
                                        <div class="form-group select-placeholder">
                                            <label for="rel_type"><?= _l('appointment_related') ?></label>
                                            <select name="rel_type" id="rel_type" class="selectpicker" data-width="100%" data-none-selected-text="<?= _l('dropdown_non_selected_tex') ?>">
                                                <option value=""></option>
                                                <option id="lead_related"
                                                    value="lead_related" <?= $rel_type == 'lead_related' ? 'selected' : '' ?>><?= _l('lead') ?></option>
                                                <option id="external"
                                                    value="external" <?= $rel_type == 'external' ? 'selected' : '' ?>><?= _l('appointments_source_external_label') ?></option>
                                                <option id="internal"
                                                    value="internal" <?= $rel_type == 'internal' ? 'selected' : '' ?>><?= _l('appointment_source_internal') ?></option>
                                                <?php
                                                if ($rel_type == 'internal_staff'): ?>
                                                    <option id="internal_staff"
                                                        value="internal_staff"
                                                        selected><?= _l('appointment_staff_only') ?></option>
                                                <?php
                                                endif; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Contact Selection -->
                                    <div class="form-group hidden client-fields"
                                        id="select_contacts">
                                        <?= render_select(
                                            'contact_id',
                                            $contacts,
                                            ['contact_id', ['firstname', 'lastname', 'company',]],
                                            'appointment_select_single_contact',
                                            $appointment['contact_id'] ?? '',
                                            [],
                                            [],
                                            '',
                                            '',
                                            true
                                        ) ?>
                                    </div>
                                    <!-- Related Contact Fields -->
                                    <div class="form-group select-placeholder hide client-fields"
                                        id="rel_id_wrapper">
                                        <input type="hidden" name="rel_lead_type" id="rel_lead_type" value="leads"> <label for="rel_id"><?= _l('leads') ?></label>
                                        <div id="rel_id_select">
                                            <select name="rel_id" id="rel_id" class="ajax-search" data-width="100%" data-live-search="true">
                                                <?php
                                                if ($rel_type == 'lead_related' && ! empty($lead_id)) {
                                                    $rel_data
                                                        = get_relation_data(
                                                            'leads',
                                                            $lead_id
                                                        );
                                                    $rel_val
                                                        = get_relation_values(
                                                            $rel_data,
                                                            'leads'
                                                        );
                                                    echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- External Contact Details -->
                                    <div class="form-group hidden client-fields" id="div_name">
                                        <label for="name"><?= _l('appointment_name') ?></label>
                                        <input type="text" class="form-control" name="name" id="name" value="<?= html_escape($appointment['name'] ?? '') ?>" disabled>
                                    </div>
                                    <div class="form-group hidden client-fields" id="div_email">
                                        <label for="email"><?= _l('appointment_email') ?></label>
                                        <input type="email" class="form-control" name="email" id="email" value="<?= html_escape($appointment['email'] ?? '') ?>" disabled>
                                    </div>
                                    <div class="form-group hidden client-fields" id="div_phone">
                                        <label for="phone"><?= _l('appointment_phone') ?> (<?= _l('appointment_your_phone_example') ?>)</label>
                                        <input type="text" class="form-control" name="phone" id="phone" value="<?= html_escape($appointment['phone'] ?? '') ?>" disabled>
                                    </div>

                                    <!-- Service & Provider -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group staffonly-hide" id="service_field">
                                                <label for="service_id"><?= _l('appointment_service') ?></label>
                                                <select name="service_id" id="service_id" class="selectpicker" data-width="100%" data-live-search="true">
                                                    <option value=""><?= _l('dropdown_non_selected_tex') ?></option>
                                                    <?php
                                                    foreach ($services as $service) { ?>
                                                        <option value="<?= $service['id'] ?>"
                                                            data-duration="<?= $service['duration'] ?>"
                                                            <?= ($appointment['service_id'] ?? '') == $service['id'] ? 'selected' : '' ?>
                                                            data-content="<span class='service-option' style='border-left: 3px solid <?= $service['color'] ?>; padding-left: 5px;'>
                                                                    <?= $service['name'] ?>
                                                                    <small class='text-muted'>(<?= $service['duration'] ?> <?= _l('minutes') ?>)</small>
                                                                </span>">
                                                            <?= $service['name'] ?>
                                                        </option>
                                                    <?php
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group staffonly-hide" id="provider_field">
                                                <label for="provider_id"><?= _l('appointment_provider') ?></label>
                                                <select name="provider_id" id="provider_id" class="selectpicker" data-width="100%" data-live-search="true">
                                                    <option value=""><?= _l('appointment_select_provider') ?></option>
                                                    <?php
                                                    if (! empty($service_providers)) {
                                                        foreach ($service_providers as $provider) { ?>
                                                            <option value="<?= $provider['staffid'] ?>" <?= ($appointment['provider_id'] ?? '') == $provider['staffid'] ? 'selected' : '' ?>>
                                                                <?= $provider['firstname'] . ' ' . $provider['lastname'] ?>
                                                            </option>
                                                    <?php
                                                        }
                                                    } ?>
                                                </select>
                                                <div id="provider_loading" class="hide">
                                                    <div class="display-block">
                                                        <div class="spinner-border text-dark" role="status" style="width: 1rem; height: 1rem;"></div>
                                                        <span class="text-muted"><?= _l('loading_text') ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php
                                    $hasStatus    = ! empty($appointment['status']);
                                    $hasNotes     = ! empty($appointment['notes']);
                                    $hasRecurring = ! empty($appointment['repeat_every']) || ! empty($appointment['recurring_type']) || ! empty($appointment['cycles']);
                                    $hasReminder  = ! empty($appointment['reminder_before']) || ! empty($appointment['reminder_before_type']);
                                    $hasAttendees = isset($appointment['attendees']) && is_array($appointment['attendees']) && count($appointment['attendees']) > 0;
                                    ?>
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('appointment_status') ?></span>
                                            <span class="appointment-toggle-plus"><?= $hasStatus ? '−' : '+' ?></span>
                                        </div>
                                        <div class="appointment-collapsible-content" style="display: <?= $hasStatus ? 'block' : 'none' ?>;">
                                            <div class="form-group">
                                                <select name="status" id="status" class="selectpicker" data-width="100%">
                                                    <option value="in-progress" <?= ($appointment['status'] ?? '') == 'in-progress' ? 'selected' : '' ?>><?= _l('appointment_status_in-progress') ?></option>
                                                    <option value="pending" <?= ($appointment['status'] ?? '') == 'pending' ? 'selected' : '' ?>><?= _l('appointment_status_pending') ?></option>
                                                    <option value="cancelled" <?= ($appointment['status'] ?? '') == 'cancelled' ? 'selected' : '' ?>><?= _l('appointment_status_cancelled') ?></option>
                                                    <option value="completed" <?= ($appointment['status'] ?? '') == 'completed' ? 'selected' : '' ?>><?= _l('appointment_status_completed') ?></option>
                                                    <option value="no-show" <?= ($appointment['status'] ?? '') == 'no-show' ? 'selected' : '' ?>><?= _l('appointment_status_no-show') ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Attendees (Collapsible) -->
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('appointment_select_attendees') ?></span>
                                            <span class="appointment-toggle-plus"><?= $hasAttendees ? '−' : '+' ?></span>
                                        </div>
                                        <div class="appointment-collapsible-content" style="display: <?= $hasAttendees ? 'block' : 'none' ?>;">
                                            <div class="appointment-attendees">
                                                <div class="form-group">
                                                    <p class="text-muted mtop5 tw-text-xs"><?= _l('appointment_select_attendees_help') ?></p>
                                                    <?= render_select(
                                                        'attendees[]',
                                                        $staff_members,
                                                        [
                                                            'staffid',
                                                            [
                                                                'firstname',
                                                                'lastname',
                                                            ],
                                                        ],
                                                        '',
                                                        $appointment['attendees'] ?? [],
                                                        ['multiple' => true],
                                                        [],
                                                        '',
                                                        '',
                                                        false
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('appointment_notes') ?></span>
                                            <span class="appointment-toggle-plus"><?= $hasNotes ? '−' : '+' ?></span>
                                        </div>
                                        <div class="appointment-collapsible-content" style="display: <?= $hasNotes ? 'block' : 'none' ?>;">
                                            <div class="form-group m-0">
                                                <?= render_textarea('notes', '', $appointment['notes'] ?? '', ['rows' => 4]) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('appointments_reminders_label') ?></span>
                                            <span class="appointment-toggle-plus"><?= $hasReminder ? '−' : '+' ?></span>
                                        </div>
                                        <div class="appointment-collapsible-content" style="display: <?= $hasReminder ? 'block' : 'none' ?>;">
                                            <p class="text-muted mbot15">
                                                <?= _l('appointment_modal_notification_info') ?>
                                            </p>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="checkbox">
                                                        <input type="checkbox" name="by_sms" id="by_sms" <?= ! empty($appointment['by_sms']) ? 'checked' : '' ?>>
                                                        <label for="by_sms"><?= _l('appoontment_sms_notification') ?></label>
                                                    </div>
                                                    <div class="checkbox">
                                                        <input type="checkbox" name="by_email" id="by_email" <?= ! empty($appointment['by_email']) ? 'checked' : '' ?>>
                                                        <label for="by_email"><?= _l('appoontment_email_notification') ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tw-flex tw-items-center tw-gap-2">
                                                <div class="form-group tw-mb-0 tw-flex-1">
                                                    <label for="reminder_before" class="tw-sr-only"><?= _l('appointments_reminder_time_value') ?></label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" name="reminder_before" id="reminder_before"
                                                            value="<?= html_escape(($appointment['reminder_before'] ?? '') !== '' ? $appointment['reminder_before'] : 30) ?>"
                                                            placeholder="<?= _l('appointments_reminder_time_value_placeholder') ?>">
                                                        <span class="input-group-addon">
                                                            <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?= _l('reminder_notification_placeholder') ?>"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="form-group tw-mb-0" style="min-width:40%;">
                                                    <label for="reminder_before_type" class="tw-sr-only">Type</label>
                                                    <select name="reminder_before_type" id="reminder_before_type" class="selectpicker" data-width="100%">
                                                        <option value="minutes" <?= ($appointment['reminder_before_type'] ?? '') == 'minutes' ? 'selected' : '' ?>><?= _l('minutes') ?></option>
                                                        <option value="hours" <?= ($appointment['reminder_before_type'] ?? '') == 'hours' ? 'selected' : '' ?>><?= _l('hours') ?></option>
                                                        <option value="days" <?= ($appointment['reminder_before_type'] ?? '') == 'days' ? 'selected' : '' ?>><?= _l('days') ?></option>
                                                        <option value="weeks" <?= ($appointment['reminder_before_type'] ?? '') == 'weeks' ? 'selected' : '' ?>><?= _l('weeks') ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('expense_recurring_indicator') ?></span>
                                            <span class="appointment-toggle-plus"><?= $hasRecurring ? '−' : '+' ?></span>
                                        </div>
                                        <div class="appointment-collapsible-content" style="display: <?= $hasRecurring ? 'block' : 'none' ?>;">
                                            <div class="checkbox">
                                                <input type="checkbox" name="repeat_appointment" id="repeat_appointment" <?= $hasRecurring ? 'checked' : '' ?>>
                                                <label for="repeat_appointment"><?= _l('expense_recurring_indicator') ?></label>
                                            </div>
                                            <div class="recurring_type_wrapper <?= $hasRecurring ? '' : 'hide' ?>">
                                                <?php
                                                $this->load->view('view_includes/recurring_wrapper'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <!-- Schedule Details Heading -->
                                    <h5 class="tw-m-0 tw-mb-4 tw-border tw-rounded-md tw-border-solid tw-border-neutral-300 tw-px-3 tw-py-2 tw-w-fit tw-font-medium"><?= _l('appointment_schedule_details') ?></h5>

                                    <!-- Subject Field -->
                                    <div class="form-group">
                                        <label for="subject"><?= _l('appointment_subject') ?></label>
                                        <input type="text"
                                            name="subject"
                                            id="subject"
                                            class="form-control"
                                            value="<?= html_escape($appointment['subject'] ?? '') ?>"
                                            required>
                                    </div>

                                    <!-- Hidden Fields -->
                                    <input type="hidden" name="duration" id="appointment_duration" value="<?= html_escape($appointment['duration'] ?? '') ?>">
                                    <input type="hidden" name="end_hour" id="end_hour" value="<?= html_escape($appointment['end_hour'] ?? '') ?>">
                                    <input type="hidden" name="start_hour" id="start_hour" value="<?= html_escape($appointment['start_hour'] ?? '') ?>">

                                    <!-- Date & Time Label and Picker -->
                                    <div class="form-group">
                                        <label for="appointment_date"><?= _l('appointment_date_time') ?></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar calendar-icon"></i></span>
                                            <input type="text" id="appointment_date" name="date" class="form-control datepicker" data-current-date="<?= _d($appointment['date'] ?? '') ?>"
                                                autocomplete="off" readonly value="<?= _d($appointment['date'] ?? '') ?>">
                                        </div>
                                        <div id="date_loading" class="hide">
                                            <div class="display-block">
                                                <div class="spinner-border text-dark" role="status" style="width: 1rem; height: 1rem;"></div>
                                                <span class="text-muted"><?= _l('appointment_checking_availability') ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Time Selection -->
                                    <div class="form-group select-placeholder">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa-regular fa-clock"></i>
                                            </span>
                                            <select name="available_times"
                                                id="available_times"
                                                class="selectpicker"
                                                data-width="100%"
                                                data-none-selected-text="<?= _l('appointment_date_required') ?>">
                                                <?php
                                                if (! empty($appointment['start_hour'])): ?>
                                                    <option value="<?= html_escape($appointment['start_hour']) ?>"
                                                        selected>
                                                        <?php
                                                        $time_format = get_option('time_format') == 24 ? 'H:i' : 'g:i A';
                                                        ?>
                                                        <?= date(
                                                            $time_format,
                                                            strtotime($appointment['start_hour'])
                                                        ) .
                                                            (! empty($appointment['end_hour']) ? ' - ' . date(
                                                                $time_format,
                                                                strtotime($appointment['end_hour'])
                                                            ) : '') ?>
                                                    </option>
                                                <?php
                                                else: ?>
                                                    <option value=""><?= _l('appointment_date_required') ?></option>
                                                <?php
                                                endif; ?>
                                            </select>
                                        </div>
                                        <div id="slot_loading" class="hide">
                                            <div class="display-block">
                                                <div class="spinner-border text-dark"
                                                    role="status"
                                                    style="width: 1rem; height: 1rem;"></div>
                                                <span class="text-muted"><?= _l('appointment_checking_time_slots') ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Timezone -->
<div class="form-group">
    <label for="timezone"><?= _l('timezone') ?></label>
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-globe"></i></span>
        <select name="timezone"
            id="timezone"
            class="form-control selectpicker"
            data-live-search="true">
            <?php
            $timezone_labels = [
                'America/New_York'    => 'Eastern Time (ET) - Tampa, Florida / New York, NY / Miami, FL - Same as Tampa',
                'America/Chicago'     => 'Central Time (CT) - Chicago, IL / Dallas, TX / Houston, TX - 1 hour behind Tampa',
                'America/Denver'      => 'Mountain Time (MT) - Denver, CO / Salt Lake City, UT - 2 hours behind Tampa',
                'America/Phoenix'     => 'Arizona Time (MST) - Phoenix, AZ - 2 or 3 hours behind Tampa',
                'America/Los_Angeles' => 'Pacific Time (PT) - Los Angeles, CA / Seattle, WA - 3 hours behind Tampa',
                'America/Anchorage'   => 'Alaska Time (AKT) - Anchorage, AK - 4 hours behind Tampa',
                'Pacific/Honolulu'    => 'Hawaii Time (HT) - Honolulu, HI - 5 or 6 hours behind Tampa',
            ];

            foreach (get_timezones_list() as $region => $timezones) { ?>
                <optgroup label="<?= e($region); ?>">
                    <?php foreach ($timezones as $timezone) { ?>
                        <option value="<?= e($timezone); ?>"
                            <?= ($appointment['timezone'] ?? get_option('default_timezone')) == $timezone ? 'selected' : '' ?>>
                            <?= e($timezone_labels[$timezone] ?? $timezone); ?>
                        </option>
                    <?php } ?>
                </optgroup>
            <?php } ?>
        </select>
    </div>
</div>
<div class="clearfix"></div>
                                    <!-- Session Overview (Collapsible) -->
                                    <?php
                                    $hasDescription = ! empty($appointment['description']);
                                    ?>
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('appointment_description') ?></span>
                                            <span class="appointment-toggle-plus"><?= $hasDescription ? '−' : '+' ?></span>
                                        </div>
                                        <div class="appointment-collapsible-content" style="display: <?= $hasDescription ? 'block' : 'none' ?>;">
                                            <div class="form-group m-0">
                                                <?= render_textarea('description', '', $appointment['description'] ?? '', ['rows' => 4]) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Custom Fields (move here, right column, under Session Overview) -->
                                    <?php
                                    $rel_cf_id     = $appointment['id'];
                                    $custom_fields = get_custom_fields('appointly');
                                    if (count($custom_fields) > 1) {
                                        $cf_expanded = true;
                                    ?>
                                        <div class="appointment-collapsible">
                                            <div class="appointment-collapsible-header">
                                                <span class="appointment-toggle-text"><?= _l('custom_fields'); ?></span>
                                                <span class="appointment-toggle-plus"><?= $cf_expanded ? '−' : '+' ?></span>
                                            </div>
                                            <div class="appointment-collapsible-content" style="display: <?= $cf_expanded ? 'block' : 'none' ?>;">
                                                <?= render_custom_fields('appointly', $rel_cf_id); ?>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="tw-flex tw-items-center tw-justify-between">
                                        <!-- Outlook Calendar (Collapsible) -->
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <?php
                                            if (empty($appointment['google_event_id']) && appointlyGoogleAuth() && get_option('appointly_google_client_secret')) : ?>
                                                <button type="button" class="btn tw-w-auto tw-px-3 sm:tw-px-4" onclick="addEventToGoogleCalendar(this)"
                                                    title="<?= _l('appointment_add_to_calendar'); ?>" data-toggle="tooltip">
                                                    <span class="tw-hidden sm:tw-inline"><?= _l('appointment_add_to_calendar'); ?> </span><i class="fa-brands fa-google"></i>
                                                </button>
                                            <?php
                                            endif; ?>
                                            <?php
                                            if (empty($appointment['outlook_event_id']) && get_option('appointly_outlook_client_id')) : ?>
                                                <button type="button"
                                                    class="btn tw-w-auto tw-px-3 sm:tw-px-4"
                                                    onclick="addEventToOutlookCalendar(this, <?= $appointment['id'] ?>)"
                                                    title="<?= _l('appointment_add_to_outlook') ?>"
                                                    data-toggle="tooltip">
                                                    <span class="tw-hidden sm:tw-inline"><?= _l('appointment_add_to_outlook') ?> </span><i class="fa fa-envelope"></i>
                                                </button>
                                            <?php
                                            endif; ?>
                                        </div>
                                        <div class="text-right">
                                            <a href="<?= admin_url('appointly/appointments') ?>"
                                                class="btn btn-default">
                                                <?= _l('cancel') ?>
                                            </a>
                                            <button type="submit"
                                                class="btn btn-primary tw-ml-2"
                                                id="appointment_submit">
                                                <?= _l('save') ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
init_tail(); ?>

<!-- Define translation variables for the helper JS -->
<script>
    // Define translations for the helper JS
    var appointlyLang = {
        dropdown_non_selected_tex: "<?= addslashes(_l('dropdown_non_selected_tex')); ?>",
        appointment_select_provider: "<?= addslashes(_l('appointment_select_provider')); ?>",
        appointment_date_required: "<?= addslashes(_l('appointment_date_required')); ?>",
        appointment_unavailable_slots_shown: "<?= addslashes(_l('appointment_unavailable_slots_shown')); ?>",
        appointment_all_slots_booked: "<?= addslashes(_l('appointment_all_slots_booked')); ?>",
        appointment_no_slots_available: "<?= addslashes(_l('appointment_no_slots_available')); ?>",
        appointment_error_loading_slots: "<?= addslashes(_l('appointment_error_loading_slots')); ?>",
        appointment_error_loading_providers: "<?= addslashes(_l('appointment_error_loading_providers')); ?>",
        appointment_provider_unavailable: "<?= addslashes(_l('appointment_provider_unavailable')); ?>",
        appointment_available_days: "<?= addslashes(_l('appointment_available_days')); ?>",
        appointment_blocked_days: "<?= addslashes(_l('appointment_blocked_days')); ?>",
        appointment_company_closed: "<?= addslashes(_l('appointment_company_closed')); ?>",
        appointment_past_date: "<?= addslashes(_l('appointment_past_date')); ?>",
    };

    // Add CSRF token for AJAX requests
    var csrfTokenName = '<?= $this->security->get_csrf_token_name() ?>';
    var csrfTokenValue = '<?= $this->security->get_csrf_hash() ?>';
</script>

<!-- Include helper file -->
<script src="<?= module_dir_url(
                    'appointly',
                    'assets/js/helpers/appointly_helpers.js'
                ) ?>?v=<?= time(); ?>"></script>

<!-- Include JavaScript for handling form submission -->
<?php
require('modules/appointly/assets/js/pages/update_js.php'); ?>
<!-- Hidden fields for Microsoft Outlook integration -->
<input type="hidden" id="ms-access-token" value="" />
<input type="hidden" id="ms-outlook-event-id"
    value="<?= $appointment['outlook_event_id'] ?? '' ?>" />

<?php
// Include Outlook integration if needed
$appointly_outlook_client_id = get_option('appointly_outlook_client_id');
require('modules/appointly/assets/js/outlook_js.php');
?>

<script>
    // Initialize toggles based on checked checkboxes
    $(function() {
        // Handle collapsible sections
        $('.appointment-collapsible-header').on('click', function() {
            var $content = $(this).next('.appointment-collapsible-content');
            var $plus = $(this).find('.appointment-toggle-plus');

            $content.slideToggle(200);

            if ($plus.text() === '+') {
                $plus.text('−');
            } else {
                $plus.text('+');
            }
        });

        // Handle recurring appointment toggle
        $('#repeat_appointment').on('change', function() {
            if ($(this).is(':checked')) {
                $('.recurring_type_wrapper').removeClass('hide');
            } else {
                $('.recurring_type_wrapper').addClass('hide');
            }
        });

        // For backward compatibility with the old toggle function
        window.appointlyToggleOptionalField = function(fieldId, linkElement) {
            $('#' + fieldId).slideToggle(200);
            return false;
        };

        <?php $hasRecurring
            = ! empty($appointment['repeat_every']) || ! empty($appointment['recurring_type']) || ! empty($appointment['cycles']); ?>
        <?php if ($hasRecurring): ?>
            // If this is a recurring appointment, check the box and show options
            $('#repeat_appointment').prop('checked', true);
            $('.recurring_type_wrapper').removeClass('hide');
        <?php endif; ?>
    });

    // Calendar button animation enhancement
    $(function() {
        // Prevent the click on delete button from triggering the parent link
        $('.calendar-btn-delete').on('click', function(e) {
            e.stopPropagation();
        });

        // Add ripple effect on delete button click
        $('.calendar-btn-delete').on('mousedown', function(e) {
            const $this = $(this);

            // Create ripple element if it doesn't exist
            if ($this.find('.ripple').length === 0) {
                $this.append('<span class="ripple"></span>');
            }

            const $ripple = $this.find('.ripple');
            $ripple.removeClass('animate');

            // Set ripple size and position
            const d = Math.max($this.outerWidth(), $this.outerHeight());
            $ripple.css({
                height: d,
                width: d,
                top: e.pageY - $this.offset().top - d / 2,
                left: e.pageX - $this.offset().left - d / 2
            });

            $ripple.addClass('animate');
        });

        // Add smooth transition on hover
        $('.calendar-btn-container').on('mouseenter', function() {
            // Force browser to recalculate layout for smoother transition
            $(this).css('transform', 'translateZ(0)');
            // Ensure delete button is displayed immediately before width animation
            $(this).find('.calendar-btn-delete').css('display', 'flex');

            // Small delay to ensure display:flex is applied before width animation
            setTimeout(function() {
                $(this).addClass('hovered');
            }.bind(this), 10);
        }).on('mouseleave', function() {
            $(this).css('transform', 'translateZ(0)');
            $(this).removeClass('hovered');

            // Hide delete button after transition completes
            const $deleteBtn = $(this).find('.calendar-btn-delete');
            setTimeout(function() {
                if (!$(this).is(':hover')) {
                    $deleteBtn.css('display', 'none');
                }
            }.bind(this), 500); // Match transition duration
        });
    });
</script>

<style>
    /* Calendar Button Animation Styles */
    .calendar-btn-container {
        position: relative;
        overflow: hidden;
        transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
        cursor: pointer;
        will-change: transform;
        padding: 0;
        height: 31px;
        /* Match the height of other buttons */
        display: flex;
        align-items: center;
    }

    .calendar-btn-open {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        transition: width 0.5s cubic-bezier(0.19, 1, 0.22, 1);
        z-index: 1;
        will-change: width;
        height: 100%;
        padding: 0 10px;
        text-decoration: none;
    }

    .calendar-btn-delete {
        display: none;
        /* Hidden by default */
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 0;
        align-items: center;
        justify-content: center;
        background-color: rgba(255, 0, 0, 0.1);
        transition: width 0.5s cubic-bezier(0.19, 1, 0.22, 1),
            background-color 0.3s ease;
        overflow: hidden;
        margin: 6px;
        border-radius: 4px;
        margin-left: 10px;
        will-change: width;
    }

    .calendar-btn-delete i {
        color: #ff3e3e;
        opacity: 0;
        transform: scale(0.7);
        transition: opacity 0.4s ease, transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        z-index: 5;
    }

    .calendar-btn-container:hover .calendar-btn-open,
    .calendar-btn-container.hovered .calendar-btn-open {
        width: 80%;
    }

    .calendar-btn-container:hover .calendar-btn-delete,
    .calendar-btn-container.hovered .calendar-btn-delete {
        display: flex;
        /* Show on hover */
        width: 20%;
        right: 0;
    }

    /* Smooth transition when leaving hover state */
    .calendar-btn-container {
        transition-delay: 0.05s;
    }

    .calendar-btn-open,
    .calendar-btn-delete {
        transition-delay: 0.05s;
    }

    /* Media queries for responsive behavior */
    @media (max-width: 767px) {
        .calendar-btn-container:hover .calendar-btn-open {
            width: 75%;
        }

        .calendar-btn-container:hover .calendar-btn-delete {
            width: 25%;
        }
    }

    .calendar-btn-container:hover .calendar-btn-delete i,
    .calendar-btn-container.hovered .calendar-btn-delete i {
        opacity: 1;
        transform: scale(1);
    }

    .calendar-btn-delete:hover {
        background-color: rgba(255, 0, 0, 0.2);
    }

    .calendar-btn-delete:active {
        background-color: rgba(255, 0, 0, 0.3);
    }

    /* Add a subtle pulse animation to the delete icon on hover */
    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
        }
    }

    .calendar-btn-delete:hover i,
    .calendar-btn-container.hovered .calendar-btn-delete:hover i {
        animation: pulse 1s infinite;
    }

    .calendar-btn-delete {
        position: relative;
        overflow: hidden;
    }

    .ripple {
        position: absolute;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: scale(0);
        pointer-events: none;
        opacity: 1;
    }

    .ripple.animate {
        animation: ripple-effect 0.5s linear;
    }

    @keyframes ripple-effect {
        0% {
            transform: scale(0);
            opacity: 0;
            /* Start invisible */
        }

        50% {
            transform: scale(1.5);
            opacity: 0.5;
            /* Fade in */
        }

        100% {
            transform: scale(2.5);
            opacity: 0;
            /* Fade out */
        }
    }
</style>

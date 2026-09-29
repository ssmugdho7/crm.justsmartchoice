<?php

defined('BASEPATH') or exit('No direct script access allowed');

$rel_type = $this->input->get('rel_type');
$rel_id   = $this->input->get('rel_id');
$client_id = $this->input->get('client_id');
$contact_id = $this->input->get('contact_id');
init_head();
?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-font-semibold tw-pl-2 tw-text-gray-800 tw-text-left sm:tw-text-right tw-w-full sm:tw-w-auto tw-mt-2 sm:tw-mt-0"><?= _l('appointment_new_appointment') ?></h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Back button -->
                        <div class="tw-flex tw-justify-between tw-items-baseline">
                            <div>
                                <a href="<?= admin_url('appointly/appointments') ?>"
                                    class="btn btn-default btn-sm back-button">
                                    <i class="fa fa-arrow-left"></i> <?= _l('appointment_want_to_go_back') ?>
                                </a>
                            </div>
                        </div>
                        <hr />
                        <!-- Appointment Form -->
                        <form action="<?= admin_url('appointly/appointments/create') ?>"
                            method="post" id="appointment-form"
                            autocomplete="off">
                            <input type="hidden"
                                name="<?= $this->security->get_csrf_token_name() ?>"
                                value="<?= $this->security->get_csrf_hash() ?>">
                            <div class="row">
                                <!-- Left Column - WHO/RELATION/CONTEXT -->
                                <div class="col-md-6">
                                    <!-- Contact & Relationship Info Header -->
                                    <h5 class="tw-m-0 tw-mb-4 tw-border tw-rounded-md tw-border-solid tw-border-neutral-300 tw-p-2 tw-w-fit tw-font-medium"><?= _l('appointment_contact_relationship') ?></h5>
                                    <!-- Appointment Type -->
                                    <div class="form-group">
                                        <label for="rel_type"><small
                                                class="req text-danger">* </small><?= _l('appointment_related') ?>
                                        </label>
                                        <select name="rel_type" id="rel_type"
                                            class="selectpicker"
                                            data-width="100%" required>
                                            <option value=""></option>
                                            <option id="lead_related" value="lead_related" <?= $rel_type                                                                            == 'lead_related'                                                                            ? 'selected'                                                                            : '' ?>><?= _l('lead') ?></option>
                                            <option id="external" value="external" <?= $rel_type == 'external' ? 'selected' : '' ?>><?= _l('appointments_source_external_label') ?></option>
                                            <option id="internal" value="internal" <?= $rel_type == 'internal' ? 'selected' : '' ?>><?= _l('appointment_source_internal') ?></option>
                                            <option id="internal_staff" value="internal_staff" <?= $rel_type == 'internal_staff' ? 'selected' : '' ?>><?= _l('appointment_staff_only') ?></option>
                                        </select>
                                    </div>

                                    <!-- Client/Contact Fields (Dynamic) -->
                                    <div class="form-group hidden client-fields"
                                        id="select_contacts">
                                        <?php if (!empty($client_id) && isset($client_contacts)): ?>
                                            <label for="contact_id"><?= _l('appointment_select_single_contact') ?></label>
                                            <select name="contact_id" id="contact_id" class="selectpicker appointly-clean-select" data-width="100%" data-size="8" data-container="body" data-live-search="true">
                                                <option value=""><?= _l('dropdown_non_selected_tex') ?></option>
                                                <?php foreach ($client_contacts as $contact): ?>
                                                    <option value="<?= $contact['id'] ?>" <?= (isset($contact_id) && $contact_id == $contact['id']) ? 'selected' : '' ?>>
                                                        <?= $contact['firstname'] . ' ' . $contact['lastname'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <?= render_select(
                                                'contact_id',
                                                $contacts,
                                                [
                                                    'contact_id',
                                                    [
                                                        'firstname',
                                                        'lastname',
                                                        'company',
                                                    ],
                                                ],
                                                'appointment_select_single_contact',
                                                $contact_id ?? '',
                                                [
                                                    'data-client-id' => $client_id ?? '',
                                                ],
                                                [],
                                                '',
                                                '',
                                                false
                                            ) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group select-placeholder hide client-fields"
                                        id="rel_id_wrapper">
                                        <input type="hidden"
                                            name="rel_lead_type"
                                            id="rel_lead_type" value="leads">
                                        <label for="rel_id"><?= _l('leads') ?></label>
                                        <div id="rel_id_select">
                                            <select name="rel_id" id="rel_id"
                                                class="ajax-search"
                                                data-width="100%"
                                                data-live-search="true">
                                                <?php
                                                if ($rel_id && $rel_type == 'lead_related') {
                                                    // For leads, use the leads_model to get the proper data
                                                    $this->load->model('leads_model');
                                                    $lead = $this->leads_model->get($rel_id);

                                                    if ($lead) {
                                                        echo '<option value="' . $lead->id . '" selected>' . $lead->name . '</option>';
                                                    }
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group hidden client-fields"
                                        id="div_name">
                                        <label for="name"><?= _l('appointment_name') ?></label>
                                        <input type="text" class="form-control"
                                            name="name" id="name">
                                    </div>
                                    <div class="form-group hidden client-fields"
                                        id="div_email">
                                        <label for="email"><?= _l('appointment_email') ?></label>
                                        <input type="email" class="form-control"
                                            name="email" id="email">
                                    </div>
                                    <div class="form-group hidden client-fields"
                                        id="div_phone">
                                        <label for="phone"><?= _l('appointment_phone') ?>
                                            (<?= _l('appointment_your_phone_example') ?>
                                            )</label>
                                        <input type="text" class="form-control"
                                            name="phone" id="phone">
                                    </div>

                                    <!-- Service & Provider Section -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- Service Selection -->
                                            <div class="form-group">
                                                <label for="service_id"><small
                                                        class="req text-danger">* </small><?= _l('appointment_service') ?>
                                                </label>
                                                <select name="service_id"
                                                    id="service_id"
                                                    class="selectpicker appointly-clean-select"
                                                    data-width="100%"
                                                    data-size="8"
                                                    data-container="body"
                                                    data-live-search="true"
                                                    required>
                                                    <option value=""><?= _l('dropdown_non_selected_tex') ?></option>
                                                    <?php
                                                    foreach ($services as $service) { ?>
                                                        <option value="<?= $service['id'] ?>"
                                                            data-duration="<?= $service['duration'] ?>"
                                                            data-content="<span class='service-option appointly-service-option' style='border-left: 3px solid <?= $service['color'] ?>; padding-left: 5px;'><?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?> <small class='text-muted'>(<?= $service['duration'] ?> <?= _l('minutes') ?>)</small></span>">
                                                            <?= htmlspecialchars($service['name'], ENT_QUOTES, 'UTF-8') ?>
                                                        </option>
                                                    <?php
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <!-- Provider Selection -->
                                            <div class="form-group">
                                                <label for="provider_id"><small
                                                        class="req text-danger">* </small><?= _l('appointment_provider') ?>
                                                </label>
                                                <select name="provider_id"
                                                    id="provider_id"
                                                    class="selectpicker appointly-clean-select"
                                                    data-width="100%"
                                                    data-size="8"
                                                    data-container="body"
                                                    data-live-search="true"
                                                    required>
                                                    <option value=""><?= _l('appointment_select_provider') ?></option>
                                                </select>
                                                <div id="provider_loading" class="hide">
                                                    <div class="display-block">
                                                        <div class="spinner-border text-dark"
                                                            role="status"
                                                            style="width: 1rem; height: 1rem;"></div>
                                                        <span class="text-muted"><?= _l('loading_text') ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="duration"
                                        id="appointment_duration" value="">

                                    <!-- Status (Collapsible) -->
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('appointment_status') ?></span>
                                            <span class="appointment-toggle-plus">+</span>
                                        </div>
                                        <div class="appointment-collapsible-content"
                                            style="display: none;">
                                            <div class="form-group">
                                                <select name="status"
                                                    id="status"
                                                    class="selectpicker"
                                                    data-width="100%">
                                                    <option value="in-progress"
                                                        selected><?= _l('appointment_status_in-progress') ?></option>
                                                    <option value="confirmed" <?= ($appointment['status'] ?? '') == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                    <option value="on-the-way" <?= ($appointment['status'] ?? '') == 'on-the-way' ? 'selected' : '' ?>>Installer On The Way</option>
                                                    <option value="15-minutes-away" <?= ($appointment['status'] ?? '') == '15-minutes-away' ? 'selected' : '' ?>>15 Minutes Away</option>
                                                    <option value="arrived" <?= ($appointment['status'] ?? '') == 'arrived' ? 'selected' : '' ?>>Arrived</option>
                                                    <option value="pending"><?= _l('appointment_status_pending') ?></option>
                                                    <option value="follow-up" <?= ($appointment['status'] ?? '') == 'follow-up' ? 'selected' : '' ?>>Follow Up</option>
                                                    <option value="cancelled"><?= _l('appointment_status_cancelled') ?></option>
                                                    <option value="completed"><?= _l('appointment_status_completed') ?></option>
                                                    <option value="no-show"><?= _l('appointment_status_no-show') ?></option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Attendees (Collapsible) -->
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('appointment_select_attendees') ?></span>
                                            <span class="appointment-toggle-plus">+</span>
                                        </div>
                                        <div class="appointment-attendees appointment-collapsible-content" style="display: none;">
                                            <div class="form-group">
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
                                                    [],
                                                    ['multiple' => true],
                                                    [],
                                                    '',
                                                    '',
                                                    false
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notes (Collapsible) -->
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('appointment_notes') ?></span>
                                            <span class="appointment-toggle-plus">+</span>
                                        </div>
                                        <div class="appointment-collapsible-content"
                                            style="display: none;">
                                            <div class="form-group m-0">
                                                <?= render_textarea(
                                                    'notes',
                                                    '',
                                                    '',
                                                    ['rows' => 4]
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reminders (Collapsible) -->
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('appointments_reminders_label') ?></span>
                                            <span class="appointment-toggle-plus">+</span>
                                        </div>
                                        <div class="appointment-collapsible-content"
                                            style="display: none;">
                                            <p class="text-muted mbot15">
                                                <?= _l('appointment_modal_notification_info') ?>
                                            </p>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="checkbox">
                                                        <input type="checkbox"
                                                            name="by_sms"
                                                            id="by_sms">
                                                        <label for="by_sms"><?= _l('appoontment_sms_notification') ?></label>
                                                    </div>
                                                    <div class="checkbox">
                                                        <input type="checkbox"
                                                            name="by_email"
                                                            id="by_email">
                                                        <label for="by_email"><?= _l('appoontment_email_notification') ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mtop15">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <input type="number"
                                                                class="form-control"
                                                                name="reminder_before"
                                                                id="reminder_before"
                                                                value="30">
                                                            <span class="input-group-addon">
                                                                <i class="fa fa-question-circle"
                                                                    data-toggle="tooltip"
                                                                    data-title="<?= _l('reminder_notification_placeholder') ?>"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <select name="reminder_before_type"
                                                            id="reminder_before_type"
                                                            class="selectpicker"
                                                            data-width="100%">
                                                            <option value="minutes"
                                                                selected><?= _l('minutes') ?></option>
                                                            <option value="hours"><?= _l('hours') ?></option>
                                                            <option value="days"><?= _l('days') ?></option>
                                                            <option value="weeks"><?= _l('weeks') ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Recurring Option (Collapsible) -->
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('expense_recurring_indicator') ?></span>
                                            <span class="appointment-toggle-plus">+</span>
                                        </div>
                                        <div class="appointment-collapsible-content"
                                            style="display: none;">
                                            <div class="checkbox">
                                                <input type="checkbox"
                                                    name="repeat_appointment"
                                                    id="repeat_appointment">
                                                <label for="repeat_appointment"><?= _l('expense_recurring_indicator') ?></label>
                                            </div>
                                            <div class="recurring_type_wrapper hide">
                                                <?php
                                                $this->load->view('view_includes/recurring_wrapper') ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column - WHEN/WHERE/DETAILS -->
                                <div class="col-md-6">
                                    <!-- Schedule Details Header -->
                                    <h5 class="tw-m-0 tw-mb-4 tw-border tw-rounded-md tw-border-solid tw-border-neutral-300 tw-p-2 tw-w-fit tw-font-medium"><?= _l('appointment_schedule_details') ?></h5>
                                    <!-- Subject -->
                                    <?= render_input(
                                        'subject',
                                        'appointment_subject',
                                        html_escape($appointment['subject'] ??
                                            '')
                                    ) ?>

                                    <!-- Date Selection -->
                                    <div class="form-group">
                                        <?php
                                        if (
                                            isset($disabled_dates)
                                            && is_array($disabled_dates)
                                        ) { ?>
                                            <input type="hidden"
                                                name="disabled_dates"
                                                value="<?= json_encode($disabled_dates) ?>">
                                        <?php
                                        } ?>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i
                                                    class="fa fa-calendar calendar-icon"></i></span>
                                            <input type="text"
                                                id="appointment_date"
                                                name="date"
                                                class="form-control datepicker"
                                                placeholder="<?= _l('appointment_date') ?>"
                                                autocomplete="off" readonly>
                                        </div>
                                        <div id="date_loading" class="hide">
                                            <div class="display-block">
                                                <div class="spinner-border text-dark"
                                                    role="status"
                                                    style="width: 1rem; height: 1rem;"></div>
                                                <span class="text-muted"><?= _l('appointment_checking_availability') ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Time Selection -->
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i
                                                    class="fa-regular fa-clock"></i></span>
                                            <select name="start_hour"
                                                id="available_times"
                                                class="selectpicker"
                                                data-width="100%"
                                                data-none-selected-text="<?= _l('appointment_date_required') ?>"
                                                disabled>
                                                <option value=""><?= _l('appointment_date_required') ?></option>
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

                                    <!-- Location Field -->
                                    <div class="form-group">
                                        <label for="location"><?= _l('appointment_meeting_location') ?></label>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-map-marker"></i>
                                            </span>
                                            <input type="text" class="form-control"
                                                name="address" id="location">
                                        </div>
                                    </div>

                                    <!-- Timezone -->
                                    <div class="form-group">
                                        <label for="timezone"><?= _l('timezone') ?></label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i
                                                    class="fa fa-globe"></i></span>
                                            <select name="timezone"
                                                id="timezone"
                                                class="form-control selectpicker"
                                                data-live-search="true">
                                                <?php
                                                foreach (
                                                    get_timezones_list() as
                                                    $region => $timezones
                                                ) { ?>
                                                    <optgroup
                                                        label="<?= $region ?>">
                                                        <?php
                                                        foreach (
                                                            $timezones as
                                                            $timezone
                                                        ) { ?>
                                                            <option value="<?= $timezone ?>"
                                                                <?= get_option('default_timezone')
                                                                    == $timezone
                                                                    ? 'selected'
                                                                    : '' ?>>
                                                                <?= $timezone ?>
                                                            </option>
                                                        <?php
                                                        } ?>
                                                    </optgroup>
                                                <?php
                                                } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Description (Collapsible) -->
                                    <div class="appointment-collapsible">
                                        <div class="appointment-collapsible-header">
                                            <span class="appointment-toggle-text"><?= _l('appointment_description') ?></span>
                                            <span class="appointment-toggle-plus">+</span>
                                        </div>
                                        <div class="appointment-collapsible-content"
                                            style="display: none;">
                                            <div class="form-group m-0">
                                                <?= render_textarea(
                                                    'description',
                                                    '',
                                                    '',
                                                    ['rows' => 5]
                                                ) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Custom Fields (Collapsible) -->
                                    <?php
                                    $rel_cf_id = (isset($appointment)
                                        ? $appointment['appointment_id']
                                        : false);
                                    $custom_fields = get_custom_fields('appointly');

                                    // Check if any custom field is required
                                    $has_required_cf = false;
                                    foreach ($custom_fields as $field) {
                                        if ($field['required'] == 1) {
                                            $has_required_cf = true;
                                            break;
                                        }
                                    }

                                    if (count($custom_fields) > 0) : ?>
                                        <div class="appointment-collapsible">
                                            <div class="appointment-collapsible-header">
                                                <span class="appointment-toggle-text"><?= _l('custom_fields') ?></span>
                                                <span class="appointment-toggle-plus"><?= $has_required_cf ? '−' : '+' ?></span>
                                            </div>
                                            <div class="appointment-collapsible-content" style="display: <?= $has_required_cf ? 'block' : 'none' ?>;">
                                                <?= render_custom_fields(
                                                    'appointly',
                                                    $rel_cf_id
                                                ) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Calendar Integrations -->
                                    <!-- Google Calendar (Collapsible) -->
                                    <?php if (appointlyGoogleAuth()) {
                                        $is_auto_meet_enabled = get_option('appointly_auto_enable_google_meet') == '1';
                                    ?>
                                        <div class="appointment-collapsible">
                                            <div class="appointment-collapsible-header">
                                                <span class="appointment-toggle-text"><?= _l('appointment_add_to_google_calendar') ?></span>
                                                <span class="appointment-toggle-plus">+</span>
                                            </div>
                                            <div class="appointment-collapsible-content"
                                                style="<?= !$is_auto_meet_enabled ? 'display: none;' : '' ?>">
                                                <div class="checkbox checkbox-primary">
                                                    <?php
                                                    $checked = (isset($appointment) && ! empty($appointment['google_event_id'])) || $is_auto_meet_enabled ? 'checked' : '';
                                                    $disabled = $is_auto_meet_enabled ? 'disabled' : '';
                                                    ?>
                                                    <input type="checkbox" name="google" id="google" <?= $checked ?> <?= $disabled ?>>
                                                    <label for="google"><?= _l('appointment_add_to_google_calendar') ?></label>
                                                </div>
                                                <?php if ($is_auto_meet_enabled): ?>
                                                    <p class="label label-info mtop5"><?= _l('appointly_auto_enable_google_meet_help') ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <!-- Outlook Calendar (Collapsible) -->
                                    <?php
                                    if (get_option('appointly_outlook_client_id')) : ?>
                                        <div class="appointment-collapsible">
                                            <div class="appointment-collapsible-header">
                                                <span class="appointment-toggle-text"><?= _l('appointment_add_to_outlook') ?></span>
                                                <span class="appointment-toggle-plus">+</span>
                                            </div>
                                            <div class="appointment-collapsible-content"
                                                style="display: none;">
                                                <div class="checkbox checkbox-primary">
                                                    <input type="checkbox"
                                                        name="outlook"
                                                        id="outlook">
                                                    <label for="outlook"><?= _l('appointment_add_to_outlook') ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    endif; ?>
                                </div>
                            </div>
                            <hr>
                            <!-- Form Footer -->
                            <div class="row">
                                <div class="col-md-12">
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
init_tail() ?>

<!-- Define translation variables for the helper JS -->
<script>
    // Define translations for the helper JS
    var appointlyLang = {
        dropdown_non_selected_tex: "<?= addslashes(_l('dropdown_non_selected_tex')) ?>",
        appointment_select_provider: "<?= addslashes(_l('appointment_select_provider')) ?>",
        appointment_date_required: "<?= addslashes(_l('appointment_date_required')) ?>",
        appointment_unavailable_slots_shown: "<?= addslashes(_l('appointment_unavailable_slots_shown')) ?>",
        appointment_all_slots_booked: "<?= addslashes(_l('appointment_all_slots_booked')) ?>",
        appointment_no_slots_available: "<?= addslashes(_l('appointment_no_slots_available')) ?>",
        appointment_error_loading_slots: "<?= addslashes(_l('appointment_error_loading_slots')) ?>",
        appointment_error_loading_providers: "<?= addslashes(_l('appointment_error_loading_providers')) ?>",
    };

    // Add CSRF token for AJAX requests
    var csrfTokenName = '<?= $this->security->get_csrf_token_name() ?>';
    var csrfTokenValue = '<?= $this->security->get_csrf_hash() ?>';
</script>

<!-- Include helper file -->
<script src="<?= module_dir_url(
                    'appointly',
                    'assets/js/helpers/appointly_helpers.js'
                ) ?>?v=<?= time() ?>"></script>

<!-- Include JavaScript for handling form submission -->
<?php
require('modules/appointly/assets/js/pages/create_js.php') ?>

<!-- Hidden fields for Microsoft Outlook integration -->
<input type="hidden" id="ms-access-token" value="" />
<input type="hidden" id="ms-outlook-event-id" value="" />

<!-- JavaScript for toggle functionality -->
<script>
    $(document).ready(function() {
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

        // For backward compatibility with the old toggle function
        window.appointlyToggleOptionalField = function(fieldId, linkElement) {
            $('#' + fieldId).slideToggle(200);
            return false;
        };

        // Handle recurring appointment toggle
        $('#repeat_appointment').on('change', function() {
            if ($(this).is(':checked')) {
                $('.recurring_type_wrapper').removeClass('hide');
            } else {
                $('.recurring_type_wrapper').addClass('hide');
            }
        });

        // Handle pre-selected client and contact
        var clientId = <?= json_encode($client_id ?? '') ?>;
        var contactId = <?= json_encode($contact_id ?? '') ?>;

        if (clientId) {
            // Make sure internal is selected and contact fields are shown
            if ($('#rel_type').val() !== 'internal') {
                $('#rel_type').val('internal').change().selectpicker('refresh');
            }

            // Show contact selection field
            $('#select_contacts').removeClass('hidden');

            // If contact_id is provided, make sure it's selected and fetch data
            if (contactId) {
                // Just to make sure contact is selected
                $('select[name="contact_id"]').val(contactId).selectpicker('refresh');

                // Fetch contact data
                setTimeout(function() {
                    appointlyFetchContactData(contactId, false);
                }, 1000);
            }
        }
    });
</script>

<?php

$appointly_outlook_client_id = get_option('appointly_outlook_client_id');
require('modules/appointly/assets/js/outlook_js.php');
?>

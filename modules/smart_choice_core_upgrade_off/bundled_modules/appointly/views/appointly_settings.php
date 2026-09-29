<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Get all settings
$settings             = get_option('appointly_settings');
$externalFormSettings = ! empty($settings)
    ? json_decode($settings, true)
    : [
        'external_form_heading'     => '',
        'external_form_description' => '',
    ];

// Other settings
$appointly_show_clients_schedule_button = get_option('appointly_show_clients_schedule_button');
$appointly_tab_on_clients_page          = get_option('appointly_tab_on_clients_page');
$appointly_show_summary                 = get_option('appointly_show_summary');

// Get blocked days
$blocked_days       = get_option('appointly_blocked_days');
$blocked_days_array = $blocked_days ? json_decode($blocked_days, true) : [];

// New external form options
$external_form_heading     = get_option('external_form_heading');
$external_form_description = get_option('external_form_description');

// User settings data
$filters                        = get_appointments_table_filters();
$appointly_default_table_filter = get_option('appointly_default_table_filter');


// Google Meet settings
$appointly_auto_enable_google_meet = get_option('appointly_auto_enable_google_meet');
$appointly_google_meet_recording = get_option('appointly_google_meet_recording');
$appointly_google_meet_waiting_room = get_option('appointly_google_meet_waiting_room');
$appointly_google_meet_reminder_minutes = get_option('appointly_google_meet_reminder_minutes');
$appointly_disable_google_meeting_emails = get_option('appointly_disable_google_meeting_emails');

?>
<div class="horizontal-scrollable-tabs">
    <div class="horizontal-tabs">
        <ul class="nav nav-tabs nav-tabs-horizontal" role="tablist">
            <li role="presentation" class="active">
                <a href="#general" aria-controls="general" role="tab"
                    data-toggle="tab"
                    aria-expanded="true">
                    <i class="fa fa-calendar tw-mr-2"></i><?= ucfirst(_l('general_settings')) ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#form" aria-controls="form" role="tab"
                    data-toggle="tab"
                    aria-expanded="false">
                    <i class="fa fa-link tw-mr-2"></i><?= ucfirst(_l('appointment_menu_form_link')) ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#comp_schedule" aria-controls="comp_schedule" role="tab" data-toggle="tab">
                    <i class="fa fa-calendar-alt tw-mr-2"></i><?= _l('appointly_company_schedule'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="#invoice_settings" aria-controls="invoice_settings" role="tab" data-toggle="tab">
                    <i class="fa fa-file-invoice tw-mr-2"></i><?= _l('appointly_invoice_settings'); ?>
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="tab-content">
    <!-- General Settings Tab -->
    <div role="tabpanel" class="tab-pane active" id="general">
        <?php
        if (is_admin()) {
            $google_client_id = get_option('google_client_id');
            $appointly_outlook_client_id = get_option('appointly_outlook_client_id');
            $appointly_google_client_secret = get_option('appointly_google_client_secret');
            $appointly_also_delete_in_google_calendar = get_option('appointly_also_delete_in_google_calendar');
            $appointments_show_past_dates = get_option('appointments_show_past_dates');
            $appointly_recaptcha = get_option('appointly_appointments_recaptcha');
            $appointly_view_all_in_calendar = get_option('appointly_view_all_in_calendar');
            $appointments_googlesync_show_in_table = get_option('appointments_googlesync_show_in_table');
            $appointments_enable_terms_conditions = get_option('appointments_enable_terms_conditions');
            $staff_members = $this->staff_model->get('', ['active' => 1]);
        ?>

            <!-- 1. General Settings (Core) -->
            <div class="panel panel-default" style="margin-bottom: 15px;">
                <div class="panel-heading" style="cursor: pointer; border-radius:7px;" data-toggle="collapse" data-target="#general-settings-content" aria-expanded="true">
                    <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <i class="fa fa-cogs" style="margin-right: 8px;"></i>
                            <?= _l('general_settings') ?>
                        </span>
                        <i class="fa fa-chevron-down collapse-icon" style="transition: transform 0.3s;"></i>
                    </h4>
                </div>
                <div id="general-settings-content" class="panel-collapse collapse in">
                    <div class="panel-body" style="padding: 20px;">
                        <!-- Default appointments table filter -->
                        <div class="form-group">
                            <label for="settings[appointly_default_table_filter]"><?= _l('appointly_default_table_filter_label') ?></label>
                            <select class="selectpicker"
                                name="settings[appointly_default_table_filter]"
                                id="appointly_default_table_filter" data-width="100%">
                                <?php
                                $filters = get_appointments_table_filters();
                                $appointly_default_table_filter = get_option('appointly_default_table_filter');
                                foreach ($filters as $filter) { ?>
                                    <option value="<?= $filter['id'] ?>" <?= ($appointly_default_table_filter == $filter['id']) ? 'selected' : '' ?>>
                                        <?= $filter['status'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <hr />
                        <!-- Show summary option -->
                        <div class="form-group">
                            <label class="control-label clearfix">
                                <?= _l('appointly_show_summary_in_appointments_dashboard') ?>
                            </label>
                            <?php $appointly_show_summary = get_option('appointly_show_summary'); ?>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="y_opt_1_appointly_show_summary"
                                    name="settings[appointly_show_summary]"
                                    value="1" <?= ($appointly_show_summary == '1') ? ' checked' : '' ?>>
                                <label for="y_opt_1_appointly_show_summary"><?= _l('settings_yes') ?></label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="y_opt_2_appointly_show_summary"
                                    name="settings[appointly_show_summary]"
                                    value="0" <?= ($appointly_show_summary == '0' || $appointly_show_summary == '') ? ' checked' : '' ?>>
                                <label for="y_opt_2_appointly_show_summary"><?= _l('settings_no') ?></label>
                            </div>
                        </div>
                        <hr />
                        <!-- Staff respect availability setting -->
                        <?php $appointly_staff_respect_availability = get_option('appointly_staff_respect_availability'); ?>
                        <div class="form-group">
                            <label class="control-label clearfix">
                                <?= _l('appointly_staff_respect_availability') ?>
                                <i class="fa fa-question-circle" data-toggle="tooltip" data-title="<?= _l('appointly_staff_respect_availability_tooltip') ?>"></i>
                            </label>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="y_opt_1_appointly_staff_respect_availability"
                                    name="settings[appointly_staff_respect_availability]"
                                    value="1" <?= ($appointly_staff_respect_availability == '1') ? ' checked' : '' ?>>
                                <label for="y_opt_1_appointly_staff_respect_availability"><?= _l('settings_yes') ?></label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="y_opt_2_appointly_staff_respect_availability"
                                    name="settings[appointly_staff_respect_availability]"
                                    value="0" <?= ($appointly_staff_respect_availability == '0' || $appointly_staff_respect_availability == '') ? ' checked' : '' ?>>
                                <label for="y_opt_2_appointly_staff_respect_availability"><?= _l('settings_no') ?></label>
                            </div>
                        </div>
                        <hr />
                        <!-- Buffer hours setting -->
                        <div class="form-group">
                            <label for="y_opt_1_appointments_show_past_dates"
                                class="control-label clearfix"><?= _l('appointments_buffer_hours_label') ?></label>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="y_opt_1_appointments_show_past_dates"
                                    name="settings[appointments_show_past_dates]"
                                    value="1" <?= ($appointments_show_past_dates == '1')
                                                    ? ' checked' : '' ?>>
                                <label for="y_opt_1_appointments_show_past_dates"><?= _l('settings_yes') ?></label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="y_opt_2_appointments_show_past_dates"
                                    name="settings[appointments_show_past_dates]"
                                    value="0" <?= ($appointments_show_past_dates == '0')
                                                    ? ' checked' : '' ?>>
                                <label for="y_opt_2_appointments_show_past_dates"><?= _l('settings_no') ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Client Area Settings -->
            <?php if (staff_can('edit', 'appointments')) { ?>
                <div class="panel panel-default" style="margin-bottom: 15px;">
                    <div class="panel-heading" style="cursor: pointer; border-radius:7px;" data-toggle="collapse" data-target="#client-area-settings-content" aria-expanded="true">
                        <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                            <span>
                                <i class="fa fa-users" style="margin-right: 8px;"></i>
                                Client Area Settings
                            </span>
                            <i class="fa fa-chevron-down collapse-icon" style="transition: transform 0.3s;"></i>
                        </h4>
                    </div>
                    <div id="client-area-settings-content" class="panel-collapse collapse in">
                        <div class="panel-body" style="padding: 20px;">
                            <div class="form-group">
                                <label class="control-label clearfix"><?= _l('appointly_allow_non_logged_clients_appointment') ?></label>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio"
                                        id="appointly_show_clients_schedule_button"
                                        name="settings[appointly_show_clients_schedule_button]"
                                        value="1" <?= ($appointly_show_clients_schedule_button == '1') ? ' checked' : '' ?>>
                                    <label for="y_opt_1_appointly_show_clients_schedule_button"><?= _l('settings_yes') ?></label>
                                </div>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio"
                                        id="y_opt_2_appointly_show_clients_schedule_button"
                                        name="settings[appointly_show_clients_schedule_button]"
                                        value="0" <?= ($appointly_show_clients_schedule_button == '0') ? ' checked' : '' ?>>
                                    <label for="y_opt_2_appointly_show_clients_schedule_button"><?= _l('settings_no') ?></label>
                                </div>
                            </div>
                            <hr />
                            <div class="form-group">
                                <label class="control-label clearfix"><?= _l('appointly_show_appointments_menu_item_in_clients_area') ?></label>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio"
                                        id="y_opt_1_appointly_tab_on_clients_page"
                                        name="settings[appointly_tab_on_clients_page]"
                                        value="1" <?= ($appointly_tab_on_clients_page == '1') ? ' checked' : '' ?>>
                                    <label for="y_opt_1_appointly_tab_on_clients_page"><?= _l('settings_yes') ?></label>
                                </div>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio"
                                        id="y_opt_2_appointly_tab_on_clients_page"
                                        name="settings[appointly_tab_on_clients_page]"
                                        value="0" <?= ($appointly_tab_on_clients_page == '0') ? ' checked' : '' ?>>
                                    <label for="y_opt_2_appointly_tab_on_clients_page"><?= _l('settings_no') ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <!-- 3. Blocked Days -->
            <div class="panel panel-default" style="margin-bottom: 15px;">
                <div class="panel-heading" style="cursor: pointer; border-radius:7px;" data-toggle="collapse" data-target="#blocked-days-content" aria-expanded="false">
                    <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <i class="fa fa-ban" style="margin-right: 8px;"></i>
                            <?= _l('appointments_blocked_days_on_calendar_title') ?>
                        </span>
                        <i class="fa fa-chevron-right collapse-icon" style="transition: transform 0.3s;"></i>
                    </h4>
                </div>
                <div id="blocked-days-content" class="panel-collapse collapse">
                    <div class="panel-body" style="padding: 20px;">
                        <div class="alert alert-info">
                            <?= _l('appointments_blocked_days_on_calendar_info') ?>
                        </div>
                        <div class="form-group">
                            <label class="control-label" style="font-weight: 600; margin-bottom: 8px;">
                                <?= _l('select_blocked_days') ?>
                            </label>
                            <div class="blocked-days-section">
                                <!-- Use the bootstrap-datepicker that Perfex uses -->
                                <div class="input-group date">
                                    <input type="text" id="blocked_dates_picker"
                                        class="form-control datepicker"
                                        placeholder="<?= _l('select_blocked_days') ?>"
                                        autocomplete="off">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar calendar-icon"></i>
                                    </div>
                                </div>

                                <!-- Container for selected dates -->
                                <div id="blocked_dates_container" class="mtop10"></div>

                                <!-- Hidden input for form submission -->
                                <input type="hidden" id="blocked_days_input"
                                    name="settings[appointly_blocked_days]"
                                    value='<?= html_escape(json_encode($blocked_days_array)) ?>'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 4. Feedback Settings -->
            <div class="panel panel-default" style="margin-bottom: 15px;">
                <div class="panel-heading" style="cursor: pointer; border-radius:7px;" data-toggle="collapse" data-target="#feedback-content" aria-expanded="false">
                    <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <i class="fa fa-star" style="margin-right: 8px;"></i>
                            <?= _l('appointments_feedback_info') ?>
                        </span>
                        <i class="fa fa-chevron-right collapse-icon" style="transition: transform 0.3s;"></i>
                    </h4>
                </div>
                <div id="feedback-content" class="panel-collapse collapse">
                    <div class="panel-body" style="padding: 20px;">
                        <div class="form-group">
                            <label for="appointly_default_feedbacks" style="font-weight: 600; margin-bottom: 8px;">
                                <?= _l('appointments_feedback_info') ?>
                            </label>
                            <select class="selectpicker"
                                name="settings[appointly_default_feedbacks][]"
                                id="appointly_default_feedbacks" data-width="100%"
                                multiple="true">
                                <?php
                                $appointmentFeedbacks = getAppointmentsFeedbacks();
                                $savedFeedbacks
                                    = json_decode(get_option('appointly_default_feedbacks'));
                                foreach ($appointmentFeedbacks as $feedback) { ?>
                                    <option value="<?= $feedback['value'] ?>"
                                        <?php
                                        if ($savedFeedbacks !== null && in_array($feedback['value'], $savedFeedbacks)) {
                                            echo ' selected';
                                        } ?>>
                                        <?= $feedback['name'] ?>
                                    </option>
                                <?php
                                } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Google Calendar Settings Panel -->
            <div class="panel panel-default" style="margin-bottom: 20px;">
                <div class="panel-heading tw-font-medium" style="cursor: pointer; border-radius: 7px;" data-toggle="collapse" data-target="#google-calendar-settings" aria-expanded="false">
                    <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <i class="fa fa-calendar" style="margin-right: 8px; color: #4285f4;"></i>
                            <?= _l('appointments_google_calendar_settings') ?>
                        </span>
                        <i class="fa fa-chevron-down collapse-icon" style="transition: transform 0.3s;"></i>
                    </h4>
                </div>
                <div id="google-calendar-settings" class="panel-collapse collapse">
                    <div class="panel-body" style="padding: 20px;">
                        <div>
                            <div class="form-group">
                                <label for="google_client_id"><?= _l('appointments_google_calendar_client_id') ?></label>
                                <input type="text" class="form-control"
                                    value="<?= $google_client_id ?>"
                                    id="google_client_id"
                                    name="settings[google_client_id]">
                            </div>
                            <hr />
                            <div class="form-group">
                                <label for="appointly_google_client_secret"><?= _l('appointments_google_calendar_client_secret') ?></label>
                                <input type="text" class="form-control"
                                    value="<?= $appointly_google_client_secret ?>"
                                    id="appointly_google_client_secret"
                                    name="settings[appointly_google_client_secret]">
                            </div>
                            <hr />
                            <div class="alert alert-info alert-dismissible" role="alert">
                                <?= _l('appointments_redirect_url') ?>:
                                <strong><?= base_url()
                                            . 'appointly/google/auth/oauth' ?></strong>
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-label="<?= _l('close') ?>">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>
                        <hr />
                        <div class="form-group">
                            <label class="control-label clearfix"><?= _l('appointments_googlesync_show_in_table_label') ?></label>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio"
                                    id="appointments_googlesync_show_in_table"
                                    name="settings[appointments_googlesync_show_in_table]"
                                    value="1" <?= ($appointments_googlesync_show_in_table
                                                    == '1') ? ' checked' : '' ?>>
                                <label for="y_opt_1_appointments_googlesync_show_in_table"><?= _l('settings_yes') ?></label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio"
                                    id="y_opt_2_appointments_googlesync_show_in_table"
                                    name="settings[appointments_googlesync_show_in_table]"
                                    value="0" <?= ($appointments_googlesync_show_in_table == '0') ? ' checked' : '' ?>>
                                <label for="y_opt_2_appointments_googlesync_show_in_table"><?= _l('settings_no') ?></label>
                            </div>
                            <hr />
                            <div class="form-group">
                                <label class="control-label clearfix"><?= _l('appointments_delete_from_google_label') ?></label>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio"
                                        id="appointly_also_delete_in_google_calendar"
                                        name="settings[appointly_also_delete_in_google_calendar]"
                                        value="1" <?= ($appointly_also_delete_in_google_calendar
                                                        == '1') ? ' checked' : '' ?>>
                                    <label for="y_opt_1_appointly_also_delete_in_google_calendar"><?= _l('settings_yes') ?></label>
                                </div>
                                <div class="radio radio-primary radio-inline">
                                    <input type="radio"
                                        id="y_opt_2_appointly_also_delete_in_google_calendar"
                                        name="settings[appointly_also_delete_in_google_calendar]"
                                        value="0" <?= ($appointly_also_delete_in_google_calendar
                                                        == '0') ? ' checked' : '' ?>>
                                    <label for="y_opt_2_appointly_also_delete_in_google_calendar"><?= _l('settings_no') ?></label>
                                </div>
                            </div>
                            <hr />
                            <?php
                            if (isset($appointments_googlesync_show_in_table)) : ?>
                                <div class="form-group">
                                    <label for="settings[appointments_googlesync_show_from]" class="tw-font-normal"><?= _l('appointly_show_google_appointments_from') ?></label>
                                    <select name="settings[appointments_googlesync_show_from]"
                                        id="settings[appointments_googlesync_show_from]"
                                        class="selectpicker" data-width="100%">
                                        <?php
                                        $dateRanges = [
                                            'today'         => _l('appointment_googlesync_only_today'),
                                            'last_month'    => _l('appointment_googlesync_only_last_month'),
                                            'last_3_months' => _l('appointment_googlesync_only_last_three_months'),
                                            'last_6_months' => _l('appointment_googlesync_only_last_six_months'),
                                            'last_year'     => _l('appointment_googlesync_only_last_year'),
                                            'all'           => _l('appointment_googlesync_show_all'),
                                        ];
                                        foreach ($dateRanges as $key => $label) { ?>
                                            <option value="<?= $key ?>" <?= (get_option('appointments_googlesync_show_from')
                                                                            == $key)
                                                                            ? 'selected' : '' ?>>
                                                <?= $label ?>
                                            </option>
                                        <?php
                                        } ?>
                                    </select>
                                </div>
                            <?php
                            endif; ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Google Meet Settings Panel -->
            <div class="panel panel-default" style="margin-bottom: 20px;">
                <div class="panel-heading tw-font-medium" style="cursor: pointer; border-radius: 7px;" data-toggle="collapse" data-target="#google-meet-settings" aria-expanded="false">
                    <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <i class="fa fa-video-camera" style="margin-right: 8px; color: #4285f4;"></i>
                            <?= _l('appointment_google_meet_enhanced_settings') ?>
                        </span>
                        <i class="fa fa-chevron-down collapse-icon" style="transition: transform 0.3s;"></i>
                    </h4>
                </div>
                <div id="google-meet-settings" class="panel-collapse collapse">
                    <div class="panel-body" style="padding: 20px;">

                        <!-- Auto-enable Google Meet for all appointments -->
                        <div class="form-group">
                            <label class="control-label clearfix" style="font-weight: 600; margin-bottom: 8px;">
                                <?= _l('appointly_auto_enable_google_meet') ?>
                            </label>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="appointly_auto_enable_google_meet_yes"
                                    name="settings[appointly_auto_enable_google_meet]"
                                    value="1" <?= ($appointly_auto_enable_google_meet == '1') ? ' checked' : '' ?>>
                                <label for="appointly_auto_enable_google_meet_yes"><?= _l('settings_yes') ?></label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="appointly_auto_enable_google_meet_no"
                                    name="settings[appointly_auto_enable_google_meet]"
                                    value="0" <?= ($appointly_auto_enable_google_meet == '0' || $appointly_auto_enable_google_meet == '') ? ' checked' : '' ?>>
                                <label for="appointly_auto_enable_google_meet_no"><?= _l('settings_no') ?></label>
                            </div>
                            <div class="mtop5">
                                <small class="text-muted"><?= _l('appointly_auto_enable_google_meet_help') ?></small>
                            </div>
                        </div>

                        <hr style="margin: 20px 0;">

                        <!-- Google Meet default settings -->
                        <div class="form-group">
                            <label class="control-label clearfix" style="font-weight: 600; margin-bottom: 12px;">
                                <?= _l('appointly_google_meet_default_settings') ?>
                            </label>
                            <div style="padding: 10px 0;">
                                <div class="checkbox checkbox-primary" style="margin-bottom: 10px;">
                                    <input type="hidden" name="settings[appointly_google_meet_recording]" value="0">
                                    <input type="checkbox" id="appointly_google_meet_recording"
                                        name="settings[appointly_google_meet_recording]"
                                        value="1" <?= ($appointly_google_meet_recording == '1') ? ' checked' : '' ?>>
                                    <label for="appointly_google_meet_recording" style="font-weight: 500;">
                                        <i class="fa fa-record-vinyl tw-mr-1" style="color: #dc3545;"></i>
                                        <?= _l('appointly_google_meet_enable_recording') ?>
                                    </label>
                                </div>
                                <div class="checkbox checkbox-primary">
                                    <input type="hidden" name="settings[appointly_google_meet_waiting_room]" value="0">
                                    <input type="checkbox" id="appointly_google_meet_waiting_room"
                                        name="settings[appointly_google_meet_waiting_room]"
                                        value="1" <?= ($appointly_google_meet_waiting_room == '1') ? ' checked' : '' ?>>
                                    <label for="appointly_google_meet_waiting_room" style="font-weight: 500;">
                                        <i class="fa fa-hourglass-half tw-mr-1" style="color: #ffc107;"></i>
                                        <?= _l('appointly_google_meet_enable_waiting_room') ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr style="margin: 20px 0;">

                        <!-- Google Meet reminder settings -->
                        <div class="form-group">
                            <label for="appointly_google_meet_reminder_minutes" style="font-weight: 600; margin-bottom: 8px;">
                                <i class="fa fa-bell tw-mr-1" style="color: #17a2b8;"></i>
                                <?= _l('appointly_google_meet_reminder_minutes') ?>
                            </label>
                            <select class="selectpicker" name="settings[appointly_google_meet_reminder_minutes]"
                                id="appointly_google_meet_reminder_minutes" data-width="100%">
                                <?php
                                $reminderOptions = [
                                    '5'   => '5 ' . _l('appointly_minutes'),
                                    '10'  => '10 ' . _l('appointly_minutes'),
                                    '15'  => '15 ' . _l('appointly_minutes'),
                                    '30'  => '30 ' . _l('appointly_minutes'),
                                    '60'  => '1 ' . _l('appointly_hour'),
                                    '120' => '2 ' . _l('appointly_hours'),
                                    '1440' => '1 ' . _l('appointly_day'),
                                ];
                                $selectedReminder = $appointly_google_meet_reminder_minutes ?: '30';
                                foreach ($reminderOptions as $value => $label) { ?>
                                    <option value="<?= $value ?>" <?= ($selectedReminder == $value) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <div class="mtop5">
                                <small class="text-muted"><?= _l('appointly_google_meet_reminder_help') ?></small>
                            </div>
                        </div>

                        <hr style="margin: 20px 0;">

                        <!-- Disable Google Meet email notifications -->
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="control-label clearfix" style="font-weight: 600; margin-bottom: 8px;">
                                <i class="fa fa-envelope-slash tw-mr-1" style="color: #6c757d;"></i>
                                <?= _l('appointly_disable_google_meeting_emails') ?>
                            </label>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="appointly_disable_google_meeting_emails_yes"
                                    name="settings[appointly_disable_google_meeting_emails]"
                                    value="1" <?= ($appointly_disable_google_meeting_emails == '1') ? ' checked' : '' ?>>
                                <label for="appointly_disable_google_meeting_emails_yes"><?= _l('settings_yes') ?></label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="appointly_disable_google_meeting_emails_no"
                                    name="settings[appointly_disable_google_meeting_emails]"
                                    value="0" <?= ($appointly_disable_google_meeting_emails == '0' || $appointly_disable_google_meeting_emails == '') ? ' checked' : '' ?>>
                                <label for="appointly_disable_google_meeting_emails_no"><?= _l('settings_no') ?></label>
                            </div>
                            <div class="mtop5">
                                <small class="text-muted"><?= _l('appointly_disable_google_meeting_emails_help') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Outlook Calendar Settings Panel -->
            <div class="panel panel-default" style="margin-bottom: 20px;">
                <div class="panel-heading tw-font-medium" style="cursor: pointer; border-radius: 7px;" data-toggle="collapse" data-target="#outlook-settings" aria-expanded="false">
                    <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                        <span class="tw-flex tw-items-center">
                            <svg width="20px" height="20px" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
                                <title>file_type_outlook</title>
                                <path d="M19.484,7.937v5.477L21.4,14.619a.489.489,0,0,0,.21,0l8.238-5.554a1.174,1.174,0,0,0-.959-1.128Z" style="fill:#0072c6" />
                                <path d="M19.484,15.457l1.747,1.2a.522.522,0,0,0,.543,0c-.3.181,8.073-5.378,8.073-5.378V21.345a1.408,1.408,0,0,1-1.49,1.555H19.483V15.457Z" style="fill:#0072c6" />
                                <path d="M10.44,12.932a1.609,1.609,0,0,0-1.42.838,4.131,4.131,0,0,0-.526,2.218A4.05,4.05,0,0,0,9.02,18.2a1.6,1.6,0,0,0,2.771.022,4.014,4.014,0,0,0,.515-2.2,4.369,4.369,0,0,0-.5-2.281A1.536,1.536,0,0,0,10.44,12.932Z" style="fill:#0072c6" />
                                <path d="M2.153,5.155V26.582L18.453,30V2ZM13.061,19.491a3.231,3.231,0,0,1-2.7,1.361,3.19,3.19,0,0,1-2.64-1.318A5.459,5.459,0,0,1,6.706,16.1a5.868,5.868,0,0,1,1.036-3.616A3.267,3.267,0,0,1,10.486,11.1a3.116,3.116,0,0,1,2.61,1.321,5.639,5.639,0,0,1,1,3.484A5.763,5.763,0,0,1,13.061,19.491Z" style="fill:#0072c6" />
                            </svg>
                            <?= _l('appointment_outlook_api_label') ?>
                        </span>
                        <i class="fa fa-chevron-down collapse-icon" style="transition: transform 0.3s;"></i>
                    </h4>
                </div>
                <div id="outlook-settings" class="panel-collapse collapse">
                    <div class="panel-body" style="padding: 20px;">
                        <div class="form-group">
                            <label for="appointly_outlook_client_id"><?= _l('appointment_outlook_client_id') ?></label>
                            <input type="text" class="form-control"
                                value="<?= $appointly_outlook_client_id ?>"
                                id="appointly_outlook_client_id"
                                name="settings[appointly_outlook_client_id]">
                        </div>
                        <div class="alert alert-info alert-dismissible" role="alert">
                            <?= _l('appointment_redirect_url_logout') ?>:
                            <strong><?= base_url()
                                        . 'admin/appointly/appointments' ?></strong>
                            <button type="button" class="close" data-dismiss="alert"
                                aria-label="<?= _l('close') ?>">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Widgets Settings Panel -->
            <div class="panel panel-default" style="margin-bottom: 20px;">
                <div class="panel-heading tw-font-medium" style="cursor: pointer; border-radius: 7px;" data-toggle="collapse" data-target="#dashboard-widgets-settings" aria-expanded="false">
                    <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                        <span>
                            <i class="fa fa-dashboard" style="margin-right: 8px; color: #17a2b8;"></i>
                            <?= _l('appointly_dashboard_widgets_settings') ?>
                        </span>
                        <i class="fa fa-chevron-down collapse-icon" style="transition: transform 0.3s;"></i>
                    </h4>
                </div>
                <div id="dashboard-widgets-settings" class="panel-collapse collapse">
                    <div class="panel-body" style="padding: 20px;">

                        <!-- Today's appointments widget settings -->
                        <div class="form-group">
                            <label class="control-label clearfix">
                                <?= _l('appointly_today_widget_enabled') ?>
                            </label>
                            <?php
                            $appointly_today_widget_enabled = get_option('appointly_today_widget_enabled');
                            ?>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="y_opt_1_appointly_today_widget_enabled"
                                    name="settings[appointly_today_widget_enabled]"
                                    value="1" <?= ($appointly_today_widget_enabled == '1')
                                                    ? ' checked' : '' ?>>
                                <label for="y_opt_1_appointly_today_widget_enabled"><?= _l('settings_yes') ?></label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="y_opt_2_appointly_today_widget_enabled"
                                    name="settings[appointly_today_widget_enabled]"
                                    value="0" <?= ($appointly_today_widget_enabled == '0'
                                                    || $appointly_today_widget_enabled == '')
                                                    ? ' checked' : '' ?>>
                                <label for="y_opt_2_appointly_today_widget_enabled">
                                    <?= _l('settings_no') ?>
                                </label>
                            </div>
                        </div>
                        <hr />
                        <!-- Upcoming appointments widget settings -->
                        <div class="form-group">
                            <label class="control-label clearfix">
                                <?= _l('appointly_upcoming_widget_enabled') ?>
                            </label>
                            <?php
                            $appointly_upcoming_widget_enabled = get_option('appointly_upcoming_widget_enabled');
                            ?>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="y_opt_1_appointly_upcoming_widget_enabled"
                                    name="settings[appointly_upcoming_widget_enabled]"
                                    value="1" <?= ($appointly_upcoming_widget_enabled == '1')
                                                    ? ' checked' : '' ?>>
                                <label for="y_opt_1_appointly_upcoming_widget_enabled"><?= _l('settings_yes') ?></label>
                            </div>
                            <div class="radio radio-primary radio-inline">
                                <input type="radio" id="y_opt_2_appointly_upcoming_widget_enabled"
                                    name="settings[appointly_upcoming_widget_enabled]"
                                    value="0" <?= ($appointly_upcoming_widget_enabled == '0'
                                                    || $appointly_upcoming_widget_enabled == '')
                                                    ? ' checked' : '' ?>>
                                <label for="y_opt_2_appointly_upcoming_widget_enabled">
                                    <?= _l('settings_no') ?>
                                </label>
                            </div>
                        </div>
                        <hr />
                        <div class="form-group">
                            <label for="appointly_upcoming_widget_range"><?= _l('appointly_upcoming_widget_range') ?></label>
                            <select class="selectpicker"
                                name="settings[appointly_upcoming_widget_range]"
                                id="appointly_upcoming_widget_range" data-width="100%">
                                <?php
                                $appointly_upcoming_widget_range = get_option('appointly_upcoming_widget_range') ?: '7_days';
                                $rangeOptions                    = [
                                    '7_days'  => _l('appointly_next_7_days'),
                                    '14_days' => _l('appointly_next_14_days'),
                                    '30_days' => _l('appointly_next_30_days'),
                                    '4_weeks' => _l('appointly_next_4_weeks'),
                                ];
                                foreach ($rangeOptions as $value => $label) { ?>
                                    <option value="<?= $value ?>" <?= ($appointly_upcoming_widget_range == $value) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php
                                } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        } ?>
        <div class="mtop10">
            <span class="label label-info"><strong><?= get_appointly_version() ?></strong></span>
        </div>
    </div>

    <!-- Form Tab -->
    <div role="tabpanel" class="tab-pane" id="form">

        <!-- 5. Booking Form Settings -->
        <div class="panel panel-default" style="margin-bottom: 15px;">
            <div class="panel-heading" style="cursor: pointer; border-radius:7px;" data-toggle="collapse" data-target="#booking-form-settings" aria-expanded="false">
                <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        <i class="fa fa-clipboard" style="margin-right: 8px;"></i>
                        Booking Form Settings
                    </span>
                    <i class="fa fa-chevron-down collapse-icon" style="transition: transform 0.3s;"></i>
                </h4>
            </div>
            <div id="booking-form-settings" class="panel-collapse collapse">
                <div class="panel-body" style="padding: 20px;">
                    <!-- Form Info Section -->
                    <h4 class="bold" style="margin-top: 0;">Form Info</h4>
                    <div class="form-group">
                        <label for="external_form_heading"><?= _l('external_form_heading') ?></label>
                        <input type="text" class="form-control"
                            id="external_form_heading"
                            name="settings[external_form_heading]"
                            value="<?= $external_form_heading ?>"
                            placeholder="Schedule a Consultation">
                    </div>
                    <div class="form-group">
                        <label for="external_form_description"><?= _l('external_form_description') ?></label>
                        <input type="text" class="form-control"
                            id="external_form_description"
                            name="settings[external_form_description]"
                            value="<?= $external_form_description ?>"
                            placeholder="Complete the form below to arrange your session with our team">
                    </div>
                    <div class="form-group">
                        <label><b>Form URL:</b></label>
                        <p>
                            <span class="label label-default">
                                <a href="<?= site_url('appointly/appointments_public/book') ?>"
                                    target="_blank">
                                    <?= site_url('appointly/appointments_public/book') ?>
                                </a>
                            </span>
                        </p>
                    </div>
                    <hr />

                    <!-- Booking Form Settings -->
                    <div class="form-group">
                        <label class="control-label clearfix"><?= _l('appointment_terms_link') ?></label>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="y_opt_1_appointments_enable_terms_conditions"
                                name="settings[appointments_enable_terms_conditions]"
                                value="1" <?= ($appointments_enable_terms_conditions == '1')
                                                ? ' checked' : '' ?>>
                            <label for="y_opt_1_appointments_enable_terms_conditions"><?= _l('settings_yes') ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="y_opt_2_appointments_enable_terms_conditions"
                                name="settings[appointments_enable_terms_conditions]"
                                value="0" <?= ($appointments_enable_terms_conditions == '0')
                                                ? ' checked' : '' ?>>
                            <label for="y_opt_2_appointments_enable_terms_conditions"><?= _l('settings_no') ?></label>
                        </div>
                    </div>
                    <hr />
                    <div class="form-group">
                        <label class="control-label clearfix"><?= _l('appointly_recaptcha_enabled') ?></label>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="y_opt_1_appointly_recaptcha"
                                name="settings[appointly_appointments_recaptcha]"
                                value="1" <?= ($appointly_recaptcha == '1')
                                                ? ' checked' : '' ?>>
                            <label for="y_opt_1_appointly_recaptcha"><?= _l('settings_yes') ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="y_opt_2_appointly_recaptcha"
                                name="settings[appointly_appointments_recaptcha]"
                                value="0" <?= ($appointly_recaptcha == '0')
                                                ? ' checked' : '' ?>>
                            <label for="y_opt_2_appointly_recaptcha"><?= _l('settings_no') ?></label>
                        </div>
                    </div>
                    <hr />
                    <?php $appointly_show_staff_email = get_option('appointly_show_staff_email'); ?>
                    <div class="form-group">
                        <label class="control-label clearfix"><?= _l('appointly_show_staff_email_booking_form') ?></label>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="y_opt_1_appointly_show_staff_email"
                                name="settings[appointly_show_staff_email]"
                                value="1" <?= ($appointly_show_staff_email == '1' || $appointly_show_staff_email == '')
                                                ? ' checked' : '' ?>>
                            <label for="y_opt_1_appointly_show_staff_email"><?= _l('settings_yes') ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="y_opt_2_appointly_show_staff_email"
                                name="settings[appointly_show_staff_email]"
                                value="0" <?= ($appointly_show_staff_email == '0')
                                                ? ' checked' : '' ?>>
                            <label for="y_opt_2_appointly_show_staff_email"><?= _l('settings_no') ?></label>
                        </div>
                    </div>
                    <hr />

                    <!-- Services Selection Section -->
                    <div class="form-group">
                        <label class="control-label clearfix">
                            <i class="fa fa-info-circle text-info" style="margin-right: 5px;" data-toggle="tooltip" data-placement="top" title="Select which services to display in the public booking form"></i>
                            <?= _l('appointment_booking_form_services') ?>
                        </label>
                        <select name="appointments_booking_services_availability[]"
                            id="appointments_booking_services_availability"
                            class="selectpicker"
                            data-width="100%"
                            multiple="true"
                            data-live-search="true">
                            <?php
                            // Get all active services
                            $CI = &get_instance();
                            $CI->load->model('appointly/appointly_model');
                            $services = $CI->appointly_model->get_services();

                            // Get saved services (if any)
                            $selected_services = get_option('appointments_booking_services_availability');
                            $selected_services = ! empty($selected_services) ? json_decode($selected_services, true) : [];

                            foreach ($services as $service) {
                                $selected = in_array($service['id'], $selected_services) ? 'selected' : '';
                                echo '<option value="' . $service['id'] . '" ' . $selected . '>' . $service['name'] . '</option>';
                            }
                            ?>
                        </select>
                        <p class="text-muted small"><?= _l('appointment_services_select_all_to_show_all') ?></p>
                    </div>
                    <hr />

                    <h4 class="bold">Embed form</h4>
                    <p><?= _l('form_integration_code_help') ?></p>
                    <textarea class="form-control"
                        rows=2"><iframe width="650" height="850" src="<?= site_url('appointly/appointments_public/book') ?>" frameborder="0" allowfullscreen></iframe></textarea>
                    <hr />
                    <p class="bold mtop15">When placing the iframe snippet code consider the
                        following:</p>
                    <p class="<?php
                                if (strpos(site_url(), 'http://') !== false) {
                                    echo 'bold text-success';
                                } ?>">1. If the protocol of your installation is http, use a http page
                        inside the iframe.</p>
                    <p class="<?php
                                if (strpos(site_url(), 'https://') !== false) {
                                    echo 'bold text-success';
                                } ?>">2. If the protocol of your installation is https, use a https page
                        inside the iframe.</p>
                    <p>Non-SSL installations will need to place the link in a non-SSL
                        landing page and backwards.</p>
                    <hr />
                    <h4 class="bold">Change form container column (Bootstrap)</h4>
                    <p>
                        <span class="label label-default">
                            <a href="<?= site_url('appointly/appointments_public/book?col=col-md-8') ?>"
                                target="_blank">
                                <?= site_url('appointly/appointments_public/book?col=col-md-8') ?>
                            </a>
                        </span>
                    </p>
                    <p>
                        <span class="label label-default">
                            <a href="<?= site_url('appointly/appointments_public/book?col=col-md-8+col-md-offset-2') ?>"
                                target="_blank">
                                <?= site_url('appointly/appointments_public/book?col=col-md-8+col-md-offset-2') ?>
                            </a>
                        </span>
                    </p>
                    <p>
                        <span class="label label-default">
                            <a href="<?= site_url('appointly/appointments_public/book?col=col-md-5') ?>"
                                target="_blank">
                                <?= site_url('appointly/appointments_public/book?col=col-md-5') ?>
                            </a>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Schedule Tab -->
    <div role="tabpanel" class="tab-pane" id="comp_schedule">
        <?php
        // Get company schedule from database
        $CI = &get_instance();
        $CI->load->model('appointly/appointly_model');
        $company_schedule = $CI->appointly_model->get_company_schedule();

        // Get system time format
        $time_format = get_option('time_format');
        $time_format_display = ($time_format == 24) ? 'H:i' : 'g:i A';

        // If no schedule set yet, create default values
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($weekdays as $day) {
            if (! isset($company_schedule[$day])) {
                $company_schedule[$day] = [
                    'start_time' => '09:00',
                    'end_time'   => '17:00',
                    'is_enabled' => in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                ];
            }
        }
        ?>
        <div class="row">
            <div class="col-md-8">
                <div class="schedule-overview">
                    <?php foreach ($weekdays as $day): ?>
                        <div class="schedule-day-row" style="display: flex; align-items: center; padding: 12px 0; border-bottom: 1px solid #eee;">
                            <div class="day-info" style="flex: 1;">
                                <span class="day-name" style="font-weight: 600; font-size: 14px; color: #333; display: inline-block; min-width: 100px;">
                                    <?= _l('appointly_day_' . strtolower($day)); ?>
                                </span>
                                <div class="day-status" style="display: inline-block; margin-left: 20px;">
                                    <?php if ($company_schedule[$day]['is_enabled']): ?>
                                        <span class="label label-success" style="margin-right: 10px;">
                                            <i class="fa fa-check tw-text-success-500 tw-mr-1"></i>
                                            <span class="tw-text-success-700"><?= ucfirst(_l('enabled')); ?></span>
                                        </span>
                                        <span class="schedule-time" style="font-size: 13px; color: #666; font-weight: 500;">
                                            <?= date($time_format_display, strtotime($company_schedule[$day]['start_time'])); ?> -
                                            <?= date($time_format_display, strtotime($company_schedule[$day]['end_time'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="label label-default" style="margin-right: 10px;">
                                            <i class="fa fa-times tw-text-danger-500 tw-mr-1"></i>
                                            <span class="tw-text-danger-500"><?= ucfirst(_l('disabled')); ?></span>
                                        </span>
                                        <span class="schedule-time text-muted" style="font-size: 13px; font-style: italic;">
                                            <?= _l('appointly_closed'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="schedule-actions">
                    <div class="well" style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px;">
                        <h5 style="margin-top: 0; margin-bottom: 15px; color: #495057; font-weight: 600;">
                            <i class="fa fa-info-circle" style="color: #17a2b8; margin-right: 8px;"></i>
                            <?= _l('appointly_manage_company_schedule'); ?>
                        </h5>
                        <p class="text-muted" style="font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
                            <?= _l('appointly_company_schedule_info'); ?>
                        </p>
                        <a href="<?= admin_url('appointly/services/company_schedule'); ?>"
                            class="btn btn-primary btn-block"
                            style="font-weight: 500; padding: 10px 16px; border-radius: 4px; transition: all 0.2s ease;">
                            <i class="fa fa-edit" style="margin-right: 6px;"></i> <?= _l('appointly_manage_company_schedule'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .schedule-day-row:last-child {
                border-bottom: none !important;
            }

            .schedule-day-row:hover {
                background-color: #f8f9fa;
                transition: background-color 0.2s ease;
            }

            .schedule-overview {
                background: #fff;
                border-radius: 4px;
                border: 1px solid #e9ecef;
                padding: 15px;
            }

            .btn-primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
            }

            .label {
                font-size: 11px;
                padding: 4px 8px;
                border-radius: 3px;
            }

            @media (max-width: 768px) {
                .schedule-day-row {
                    flex-direction: column !important;
                    align-items: flex-start !important;
                }

                .day-status {
                    margin-left: 0 !important;
                    margin-top: 8px;
                }
            }
        </style>
    </div>

    <!-- Invoice Settings Tab -->
    <div role="tabpanel" class="tab-pane" id="invoice_settings">
        <?php
        // Load required models for taxes
        $this->load->model('taxes_model');

        // Get taxes for system tax selection
        $taxes = $this->taxes_model->get();

        // Get invoice settings
        $appointly_invoice_tax_type    = get_option('appointly_invoice_tax_type', 'none');
        $appointly_invoice_default_vat = get_option('appointly_invoice_default_vat', 13);
        $appointly_invoice_system_tax  = get_option('appointly_invoice_system_tax');
        $appointly_show_invoice_option = get_option('appointly_show_invoice_option', 0);
        ?>

        <!-- 1. Invoice Automation Settings -->
        <div class="panel panel-default" style="margin-bottom: 15px;">
            <div class="panel-heading" style="cursor: pointer; border-radius:7px;" data-toggle="collapse" data-target="#invoice-automation-settings" aria-expanded="true">
                <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        <i class="fa fa-file-invoice" style="margin-right: 8px;"></i>
                        Invoice Automation
                    </span>
                    <i class="fa fa-chevron-down collapse-icon" style="transition: transform 0.3s;"></i>
                </h4>
            </div>
            <div id="invoice-automation-settings" class="panel-collapse collapse in">
                <div class="panel-body" style="padding: 20px;">
                    <div class="form-group">
                        <label for="appointly_show_invoice_option" class="control-label clearfix">
                            <?= _l('appointly_create_invoice_when_completed'); ?>
                        </label>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="y_opt_1_appointly_show_invoice_option" name="settings[appointly_show_invoice_option]" value="1" <?= $appointly_show_invoice_option == '1' ? 'checked' : ''; ?>>
                            <label for="y_opt_1_appointly_show_invoice_option"><?= _l('settings_yes'); ?></label>
                        </div>
                        <div class="radio radio-primary radio-inline">
                            <input type="radio" id="y_opt_2_appointly_show_invoice_option" name="settings[appointly_show_invoice_option]" value="0" <?= $appointly_show_invoice_option == '0' ? 'checked' : ''; ?>>
                            <label for="y_opt_2_appointly_show_invoice_option"><?= _l('settings_no'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Tax Configuration Settings -->
        <div class="panel panel-default" style="margin-bottom: 15px;">
            <div class="panel-heading" style="cursor: pointer; border-radius:7px;" data-toggle="collapse" data-target="#tax-configuration-settings" aria-expanded="false">
                <h4 class="panel-title tw-font-medium" style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                    <span>
                        <i class="fa fa-percent" style="margin-right: 8px;"></i>
                        Tax Configuration
                    </span>
                    <i class="fa fa-chevron-down collapse-icon" style="transition: transform 0.3s;"></i>
                </h4>
            </div>
            <div id="tax-configuration-settings" class="panel-collapse collapse">
                <div class="panel-body" style="padding: 20px;">
                    <?php
                    $tax_types = [
                        ['id' => 'none', 'name' => _l('appointly_tax_type_none')],
                        ['id' => 'custom', 'name' => _l('appointly_tax_type_custom')],
                        ['id' => 'system', 'name' => _l('appointly_tax_type_system')],
                    ];
                    echo render_select(
                        'settings[appointly_invoice_tax_type]',
                        $tax_types,
                        ['id', 'name'],
                        'appointly_invoice_tax_type_label',
                        $appointly_invoice_tax_type,
                        ['id' => 'appointly_invoice_tax_type']
                    );
                    ?>
                    <small class="text-muted"><?= _l('appointly_invoice_tax_type_help') ?></small>

                    <!-- Custom VAT Percentage -->
                    <div class="form-group mtop15" id="custom_vat_section" style="<?= ($appointly_invoice_tax_type != 'custom') ? 'display:none;' : '' ?>">
                        <?= render_input('settings[appointly_invoice_default_vat]', 'appointly_default_vat_label', $appointly_invoice_default_vat, 'number', [
                            'min'             => '0',
                            'max'             => '100',
                            'step'            => '0.01',
                            'data-ays-ignore' => true,
                        ]); ?>
                        <small class="text-muted"><?= _l('appointly_default_vat_help') ?></small>
                    </div>

                    <!-- System Tax Selection using render_select with Perfex taxes -->
                    <div class="form-group mtop15" id="system_tax_section" style="<?= ($appointly_invoice_tax_type != 'system') ? 'display:none;' : '' ?>">
                        <?php
                        // Prepare taxes for render_select (following Perfex pattern)
                        $tax_options = [];
                        foreach ($taxes as $tax) {
                            $tax_options[] = [
                                'id'      => $tax['id'],
                                'name'    => $tax['taxrate'] . '% - ' . $tax['name'],
                                'subtext' => $tax['name'],
                            ];
                        }

                        echo render_select(
                            'settings[appointly_invoice_system_tax]',
                            $tax_options,
                            ['id', 'name'],
                            'appointly_system_tax_label',
                            $appointly_invoice_system_tax,
                            [
                                'id'                      => 'appointly_invoice_system_tax',
                                'data-none-selected-text' => _l('no_tax'),
                            ]
                        );
                        ?>
                        <small class="text-muted"><?= _l('appointly_system_tax_help') ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    body.admin.settings .panel-default>.panel-heading {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }
</style>
<?php
// Register script for blocked days functionality through the app_admin_footer hook
hooks()->add_action('app_admin_footer', 'blocked_days_settings_js');
hooks()->add_action('app_admin_footer', 'services_selection_settings_js');
hooks()->add_action('app_admin_footer', 'invoice_automation_settings_js');

/**
 * Function to output the JavaScript for blocked days functionality
 * This ensures the script runs after all core scripts are loaded
 */
function blocked_days_settings_js()
{
?>
    <script>
        function initBlockedDays() {
            // Main function that initializes the blocked days functionality
            (function($) {
                "use strict";

                // Use the admin_url function from Perfex
                var admin_url = '<?php echo admin_url('appointly/appointments/save_blocked_days_ajax'); ?>';

                var blockedDates = [];
                var initialDates = []; // Store initial dates for comparison

                // Load existing blocked dates first
                try {
                    let existingDates = $('#blocked_days_input').val();
                    if (existingDates && existingDates !== '[]') {
                        blockedDates = JSON.parse(existingDates);
                        initialDates = [...blockedDates]; // Create a copy of initial dates
                        renderBlockedDates();
                    }
                } catch (e) {
                    console.error('Error loading blocked dates:', e);
                    blockedDates = [];
                }

                // Translation strings for tooltips
                var translations = {
                    blockedDate: '<?= addslashes(_l('appointment_blocked_date_tooltip')) ?>',
                    pastDate: '<?= addslashes(_l('appointment_past_date_tooltip')) ?>',
                    availableDate: '<?= addslashes(_l('appointment_available_date_tooltip')) ?>'
                };

                // Initialize datepicker with blocked dates highlighting
                initBlockedDaysDatepicker();

                // Function to initialize datepicker with blocked dates highlighting
                function initBlockedDaysDatepicker() {
                    // Destroy existing datepicker if it exists
                    if ($('#blocked_dates_picker').data('xdsoft_datetimepicker')) {
                        $('#blocked_dates_picker').datetimepicker('destroy');
                    }

                    // Configure datepicker options with blocked dates highlighting
                    var datePickerOptions = {
                        dayOfWeekStart: <?= get_option('calendar_first_day') ?: 1 ?>,
                        format: '<?= get_option('dateformat') ?: 'd-m-Y' ?>',
                        timepicker: false,
                        scrollInput: false,
                        validateOnBlur: false,
                        minDate: 0, // Disable past dates
                        beforeShowDay: function(date) {
                            // Format date to YYYY-MM-DD for comparison
                            var year = date.getFullYear();
                            var month = ('0' + (date.getMonth() + 1)).slice(-2);
                            var day = ('0' + date.getDate()).slice(-2);
                            var formattedDate = year + '-' + month + '-' + day;

                            // Check if this date is explicitly blocked
                            if (blockedDates.indexOf(formattedDate) !== -1) {
                                return ["blocked-date", translations.blockedDate];
                            }

                            // Check if this is a past date
                            var today = new Date();
                            today.setHours(0, 0, 0, 0);
                            if (date < today) {
                                return ["", translations.pastDate];
                            }

                            // Available date
                            return ["", translations.availableDate];
                        },
                        onGenerate: function(ct) {
                            // Add styling and icons to blocked dates only
                            setTimeout(function() {
                                $('.xdsoft_date').each(function() {
                                    var $this = $(this);
                                    var dateData = $this.data();

                                    if (dateData) {
                                        var month = parseInt(dateData.month) + 1;
                                        var dateStr = dateData.year + '-' +
                                            ('0' + month).slice(-2) + '-' +
                                            ('0' + dateData.date).slice(-2);

                                        var today = new Date();
                                        today.setHours(0, 0, 0, 0);
                                        var cellDate = new Date(dateData.year, dateData.month, dateData.date);

                                        // Handle explicitly blocked dates
                                        if (blockedDates.indexOf(dateStr) !== -1) {
                                            $this.addClass('xdsoft_disabled');
                                            $this.css('cursor', 'not-allowed');
                                            $this.attr('title', translations.blockedDate);

                                            // Add lock icon if not already present
                                            if (!$this.find('.blocked-icon').length) {
                                                $this.append('<i class="fa fa-lock blocked-icon"></i>');
                                            }

                                            // Prevent clicking
                                            $this.off('click').on('click', function(e) {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                return false;
                                            });
                                        }
                                        // Handle past dates
                                        else if (cellDate < today) {
                                            $this.attr('title', translations.pastDate);
                                        }
                                        // Handle available dates
                                        else if (!$this.hasClass('xdsoft_disabled')) {
                                            $this.attr('title', translations.availableDate);
                                        }
                                    }
                                });
                            }, 100);
                        }
                    };

                    // Apply to the date picker
                    $('#blocked_dates_picker').datetimepicker(datePickerOptions);
                }

                // Get the date format from system settings - use the standard DD-MM-YYYY format
                var momentDateFormat = 'DD-MM-YYYY';

                // Handle date selection
                $('#blocked_dates_picker').on('change', function() {
                    var selectedDate = $(this).val();
                    if (!selectedDate) return;

                    try {
                        // Try to parse the date with the default format first
                        var momentDate = moment(selectedDate, momentDateFormat);

                        // If that fails, try other common formats
                        if (!momentDate.isValid()) {
                            var formats = ['DD-MM-YYYY', 'MM-DD-YYYY', 'YYYY-MM-DD', 'DD/MM/YYYY', 'MM/DD/YYYY'];
                            for (var i = 0; i < formats.length; i++) {
                                momentDate = moment(selectedDate, formats[i]);
                                if (momentDate.isValid()) break;
                            }
                        }

                        // Extract date components and format correctly for storage
                        var year = momentDate.year();
                        var month = momentDate.month() + 1; // moment months are 0-indexed
                        var day = momentDate.date();

                        // Create the ISO format (YYYY-MM-DD)
                        var formattedDate = year + '-' +
                            month.toString().padStart(2, '0') + '-' +
                            day.toString().padStart(2, '0');

                        if (!blockedDates.includes(formattedDate)) {
                            blockedDates.push(formattedDate);
                            blockedDates.sort();
                            renderBlockedDates();

                            // Refresh datepicker to show newly blocked date
                            initBlockedDaysDatepicker();

                            saveBlockedDaysToServer('Date blocked successfully');
                            $(this).val(''); // Clear the input after adding
                        } else {
                            alert_float('warning', 'This date is already in the blocked dates list');
                            $(this).val('');
                        }
                    } catch (e) {
                        console.error('Error processing date:', e);
                        alert_float('danger', 'Error processing date: ' + e.message);
                        $(this).val('');
                    }
                });

                // Handle removing dates
                $(document).on('click', '.remove-date', function(e) {
                    e.preventDefault();
                    var dateToRemove = $(this).data('date');

                    // Remove the date from array
                    blockedDates = blockedDates.filter(function(date) {
                        return date !== dateToRemove;
                    });

                    // Immediately remove the element from DOM before full re-render
                    $(this).closest('.blocked-date-tag').fadeOut(200, function() {
                        // Full re-render after animation
                        renderBlockedDates();

                        // Refresh datepicker to remove highlighting from unblocked date
                        initBlockedDaysDatepicker();

                        // Auto-save immediately
                        saveBlockedDaysToServer('Date removed successfully');
                    });
                });

                // Render the blocked dates in the UI
                function renderBlockedDates() {
                    var html = '';

                    if (blockedDates.length === 0) {
                        $('#blocked_dates_container').html('<p class="text-muted">No blocked days selected</p>');
                        $('#blocked_days_input').val('[]');
                        return;
                    }

                    // Filter out any invalid dates
                    blockedDates = blockedDates.filter(function(date) {
                        return /^\d{4}-\d{2}-\d{2}$/.test(date) && moment(date, 'YYYY-MM-DD').isValid();
                    });

                    blockedDates.forEach(function(date) {
                        // Format date for display
                        var displayDate = moment(date, 'YYYY-MM-DD').format('DD MMM YYYY');

                        html += `
                                <div class="blocked-date-tag">
                                    <span>${displayDate}</span>
                                    <button type="button" class="btn btn-xs btn-danger remove-date" data-date="${date}" title="Remove this blocked date">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>`;
                    });

                    $('#blocked_dates_container').html(html);
                    $('#blocked_days_input').val(JSON.stringify(blockedDates));
                }

                // Validate blocked days format
                window.validateBlockedDays = function() {
                    let input = document.getElementById("blocked_days_input");

                    try {
                        let parsed = JSON.parse(input.value);
                        if (!Array.isArray(parsed)) {
                            throw new Error("Input must be an array.");
                        }

                        let datePattern = /^\d{4}-\d{2}-\d{2}$/;
                        for (let date of parsed) {
                            if (!datePattern.test(date)) {
                                throw new Error("Invalid date format");
                            }
                        }
                        return true;
                    } catch (e) {
                        alert_float('danger', 'Invalid date format detected');
                        return false;
                    }
                }

                //  a function to save the blocked days via AJAX
                function saveBlockedDaysToServer(successMessage) {
                    // Create a clean copy of dates to send
                    var datesToSend = JSON.stringify(blockedDates);

                    // Send AJAX request to save changes
                    $.ajax({
                        url: admin_url,
                        type: 'POST',
                        data: {
                            blocked_days: datesToSend
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Update initialDates to match current state since saved
                                initialDates = [...blockedDates];

                                // Show success message
                                alert_float('success', successMessage);
                            } else {
                                // Show error message from server
                                alert_float('danger', response.message || 'Error saving changes');
                            }
                        },
                        error: function(xhr, status, error) {
                            try {
                                // Try to parse response as JSON
                                var errorData = JSON.parse(xhr.responseText);
                                alert_float('danger', errorData.message || 'Error saving changes. Please try again.');
                            } catch (e) {
                                // If not JSON, show generic message with status
                                alert_float('danger', 'Error saving changes. Status: ' + xhr.status);
                            }
                        }
                    });
                }
            })(jQuery);
        }

        $(function() {
            initBlockedDays();
            initCollapsiblePanels();
        });

        // Add CSS for blocked dates styling
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                /* Only style explicitly blocked dates with red background */
                .xdsoft_calendar .xdsoft_date.blocked-date {
                    background: #ffebee !important;
                    color: #c62828 !important;
                    position: relative;
                    font-weight: bold !important;
                }
                .xdsoft_calendar .xdsoft_date.blocked-date:hover {
                    background: #ffcdd2 !important;
                }
                
                /* Past dates should remain normal gray disabled styling */
                .xdsoft_calendar .xdsoft_date.xdsoft_disabled:not(.blocked-date) {
                    background: #f5f5f5 !important;
                    color: #999 !important;
                }
                
                .blocked-icon {
                    position: absolute;
                    top: 2px;
                    right: 2px;
                    font-size: 10px;
                    color: #c62828;
                    pointer-events: none;
                }
                
                .blocked-date-tag {
                    display: inline-block;
                    background-color: #f8d7da;
                    color: #721c24;
                    padding: 5px 10px;
                    margin: 5px 5px 0 0;
                    border-radius: 3px;
                    border: 1px solid #f5c6cb;
                    font-size: 12px;
                }
                .blocked-date-tag .btn {
                    margin-left: 5px;
                    padding: 2px 5px;
                    font-size: 10px;
                    line-height: 1.2;
                }
            `)
            .appendTo('head');

        // Initialize collapsible panels
        function initCollapsiblePanels() {
            $('#booking-form-settings').collapse('show');
            // Handle collapse/expand for all panels
            $('[data-toggle="collapse"]').on('click', function() {
                var target = $($(this).data('target'));
                var icon = $(this).find('.collapse-icon');

                // Toggle the chevron icon
                if (target.hasClass('in')) {
                    icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
                } else {
                    icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                }
            });

            // Handle the collapse events for smooth animation
            $('[data-toggle="collapse"]').each(function() {
                var target = $($(this).data('target'));
                var icon = $(this).find('.collapse-icon');

                target.on('shown.bs.collapse', function() {
                    icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                });

                target.on('hidden.bs.collapse', function() {
                    icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
                });
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        }
    </script>
<?php
}

/**
 * Function to output the JavaScript for services selection functionality
 * This ensures the script runs after all core scripts are loaded
 */
function services_selection_settings_js()
{
?>
    <script>
        function initServicesSelection() {
            // Main function that initializes the services selection functionality
            (function($) {
                "use strict";

                // Use the admin_url function from Perfex
                var admin_url = '<?php echo admin_url('appointly/services/save_service_availability_ajax'); ?>';

                var selectedServices = [];
                var initialServices = []; // Store initial services for comparison

                // Load existing selected services
                try {
                    let existingServices = $('input[name="settings[appointments_booking_services_availability]"]').val();
                    if (existingServices && existingServices !== '[]') {
                        selectedServices = JSON.parse(existingServices);
                        initialServices = [...selectedServices]; // Create a copy of initial services
                    } else {
                        // If no value yet, get from the select element
                        selectedServices = $('#appointments_booking_services_availability').val() || [];
                        initialServices = [...selectedServices];
                    }
                } catch (e) {
                    console.error('Error loading selected services:', e);
                    selectedServices = [];
                }

                // Handle service selection changes
                $('#appointments_booking_services_availability').on('change', function() {
                    selectedServices = $(this).val() || [];
                    saveServicesToServer('Services updated successfully');
                });

                // Function to save the services via AJAX
                function saveServicesToServer(successMessage) {
                    // Create a clean copy of services to send
                    var servicesToSend = JSON.stringify(selectedServices);

                    // Send AJAX request to save changes
                    $.ajax({
                        url: admin_url,
                        type: 'POST',
                        data: {
                            services: servicesToSend
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Update initialServices to match current state since saved
                                initialServices = [...selectedServices];

                                // Show success message
                                alert_float('success', successMessage);
                            } else {
                                // Show error message from server
                                alert_float('danger', response.message || 'Error saving changes');
                            }
                        },
                        error: function(xhr, status, error) {
                            try {
                                // Try to parse response as JSON
                                var errorData = JSON.parse(xhr.responseText);
                                alert_float('danger', errorData.message || 'Error saving services. Please try again.');
                            } catch (e) {
                                // If not JSON, show generic message with status
                                alert_float('danger', 'Error saving services. Status: ' + xhr.status);
                            }
                        }
                    });
                }

                // Handle form submission
                $('form').on('submit', function() {
                    // Get selected services as an array
                    selectedServices = $('#appointments_booking_services_availability').val() || [];

                    // Create a hidden input to store JSON-encoded services if it doesn't exist
                    if ($('input[name="settings[appointments_booking_services_availability]"]').length === 0) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'settings[appointments_booking_services_availability]',
                            value: JSON.stringify(selectedServices)
                        }).appendTo('form');
                    } else {
                        // Update existing hidden field
                        $('input[name="settings[appointments_booking_services_availability]"]').val(JSON.stringify(selectedServices));
                    }

                    return true;
                });
            })(jQuery);
        }

        $(function() {
            initServicesSelection();
        });
    </script>
<?php
}

/**
 * JavaScript for invoice automation settings
 */
function invoice_automation_settings_js()
{ ?>
    <script>
        function toggleTaxSections() {
            var taxType = $('#appointly_invoice_tax_type').val() || $('select[name="settings[appointly_invoice_tax_type]"]').val();

            // Default to 'none' if empty
            if (!taxType || taxType === '' || taxType === null || taxType === undefined) {
                taxType = 'none';
                if ($('#appointly_invoice_tax_type').length) {
                    $('#appointly_invoice_tax_type').val('none').trigger('change');
                }
                if ($('select[name="settings[appointly_invoice_tax_type]"]').length) {
                    $('select[name="settings[appointly_invoice_tax_type]"]').val('none');
                }
            }

            // Hide all sections first
            $('#custom_vat_section').hide();
            $('#system_tax_section').hide();

            // Show appropriate section
            if (taxType === 'custom') {
                $('#custom_vat_section').show();
            } else if (taxType === 'system') {
                $('#system_tax_section').show();
            }
        }

        $(function() {
            function initInvoiceTab() {
                // Initialize tax dropdown to 'none' if empty
                var currentTaxType = $('#appointly_invoice_tax_type').val() || $('select[name="settings[appointly_invoice_tax_type]"]').val();
                if (!currentTaxType || currentTaxType === '' || currentTaxType === null) {
                    if ($('#appointly_invoice_tax_type').length) {
                        $('#appointly_invoice_tax_type').val('none');
                    }
                    if ($('select[name="settings[appointly_invoice_tax_type]"]').length) {
                        $('select[name="settings[appointly_invoice_tax_type]"]').val('none');
                    }
                    // Refresh selectpicker if it exists
                    if ($('#appointly_invoice_tax_type').hasClass('selectpicker')) {
                        $('#appointly_invoice_tax_type').selectpicker('refresh');
                    }
                }

                // Initialize toggles
                toggleTaxSections();

                // Bind event handlers
                $('#appointly_invoice_tax_type, select[name="settings[appointly_invoice_tax_type]"]').off('change').on('change', toggleTaxSections);
            }

            // Initialize immediately if invoice tab is active
            if ($('#invoice_settings').hasClass('active')) {
                initInvoiceTab();
            }

            // Initialize when invoice tab is clicked
            $('a[href="#invoice_settings"]').on('shown.bs.tab', function() {
                setTimeout(initInvoiceTab, 100);
            });

            // Initialize on any tab change to invoice settings
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                if ($(e.target).attr('href') === '#invoice_settings') {
                    setTimeout(initInvoiceTab, 100);
                }
            });
        });
    </script>
<?php
} ?>

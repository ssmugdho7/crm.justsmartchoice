<?php

defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Appointly
Description: Quality ★★★★★.  Appointly helps Smart Choice Contractors USA manage appointment booking, public scheduling forms, staff calendars, customer booking requests, appointment notifications, and CRM scheduling workflows inside Perfex CRM. The module is updated for PHP 8.5 target compatibility, Perfex CRM 3.4.1 usage, safe database upgrades, clean labels, and a polished Smart Choice experience for public booking pages and internal appointment management.
Version: 2.0.0
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
Requires at least: 3.0.0
*/

$CI = &get_instance();

define('APPOINTLY_MODULE_NAME', 'appointly');
define('APPOINTLY_SMS_APPOINTMENT_APPROVED_TO_CLIENT', 'appointly_appointment_approved_send_to_client');
define('APPOINTLY_SMS_APPOINTMENT_CANCELLED_TO_CLIENT', 'appointly_appointment_cancelled_to_client');
define('APPOINTLY_SMS_APPOINTMENT_APPOINTMENT_REMINDER_TO_CLIENT', 'appointly_appointment_reminder_to_client');
define('APPOINTLY_SMS_APPOINTMENT_UPDATED_TO_CLIENT', 'appointly_appointment_updated_to_client');

hooks()->add_action('admin_init', 'appointly_register_permissions');
hooks()->add_action('admin_init', 'appointly_register_menu_items');
hooks()->add_action('clients_init', 'appointly_clients_area_schedule_appointment');
hooks()->add_action('after_cron_run', 'appointly_send_email_templates');
hooks()->add_action('after_cron_run', 'appointly_recurring_events');
hooks()->add_action('app_admin_footer', 'appointly_add_filters_js');
hooks()->add_action('app_admin_footer', 'appointly_get_environment');
hooks()->add_action('after_email_templates', 'appointly_add_email_templates');

// Add appointments permission to client contact permissions
hooks()->add_filter('get_contact_permissions', 'appointly_add_contact_permission');

// Hooks moved from helper file
hooks()->add_action('app_admin_head', 'appointly_head_components');
hooks()->add_action('app_admin_footer', 'appointly_footer_components');
hooks()->add_filter('available_tracking_templates', 'add_appointment_approved_email_tracking');

register_merge_fields('appointly/merge_fields/appointly_merge_fields');

/**
 * Functions moved from helper file for hooks
 */

/**
 * Email tracking
 *
 * @param $slugs
 *
 * @return mixed
 */
if (! function_exists('add_appointment_approved_email_tracking')) {
    function add_appointment_approved_email_tracking($slugs)
    {
        if (! in_array('appointment-approved-to-contact', $slugs)) {
            $slugs[] = 'appointment-approved-to-contact';
        }

        return $slugs;
    }
}

/**
 * Injects theme CSS.
 */
if (! function_exists('appointly_head_components')) {
    function appointly_head_components()
    {
        echo '<link href="' . module_dir_url(APPOINTLY_MODULE_NAME, 'assets/css/styles.css?v=' . time()) . '" rel="stylesheet" type="text/css">';
    }
}

/**
 * Injects theme JS for global modal.
 */
if (! function_exists('appointly_footer_components')) {
    function appointly_footer_components()
    {
        echo '<script src="' . module_dir_url(APPOINTLY_MODULE_NAME, 'assets/js/global.js?v=' . time()) . '" type="text/javascript"></script>';
    }
}

hooks()->add_filter('other_merge_fields_available_for', 'appointly_register_other_merge_fields');
hooks()->add_filter('available_merge_fields', 'appointly_allow_staff_merge_fields_for_appointment_templates');
hooks()->add_filter('get_dashboard_widgets', 'appointly_register_dashboard_widgets');
hooks()->add_filter('calendar_data', 'appointly_register_appointments_on_calendar', 10, 2);


/**
 * Schedule appointment menu items in client area
 */
if (! function_exists('appointly_clients_area_schedule_appointment')) {
    function appointly_clients_area_schedule_appointment()
    {
        if (get_option('appointly_show_clients_schedule_button') == 1 && ! is_client_logged_in()) {
            add_theme_menu_item('schedule-appointment-id', [
                'name'     => _l('appointly_schedule_new_appointment'),
                'href'     => site_url('appointly/appointments_public/book?col=col-md-8+col-md-offset-2'),
                'position' => 10,
            ]);
        }

        // Item is available for logged in clients if enabled in Setup->Settings->Appointment
        if (is_client_logged_in()) {
            if (get_option('appointly_tab_on_clients_page') == 1 && has_contact_permission('appointments')) {
                // Add Appointments menu item instead of just "Schedule"
                add_theme_menu_item('appointments', [
                    'name'     => _l('appointment_appointments'),
                    'href'     => site_url('appointly/appointment_clients/appointments'),
                    'position' => 5,
                    'icon'     => 'fa-regular fa-calendar-check',
                ]);
            }
        }
    }
}

/**
 * Register appointments on staff and clients calendar.
 *
 * @param $data
 * @param $config
 *
 * @return mixed
 */
function appointly_register_appointments_on_calendar($data, $config)
{
    $CI = &get_instance();
    $CI->load->model('appointly/appointly_model', 'apm');

    // Get calendar data from the model
    return $CI->apm->get_calendar_data($config['start'], $config['end'], $data);
}


hooks()->add_action('after_custom_fields_select_options', 'appointly_custom_fields');
/**
 * Register new custom fields for
 *
 * @param $custom_field
 */
function appointly_custom_fields($custom_field)
{
    $selected = (isset($custom_field) && $custom_field->fieldto == 'appointly') ? 'selected' : '';
    echo '<option value="appointly"  ' . ($selected) . '>' . _l('appointment_appointments') . '</option>';
}

/**
 * Get today's appointments to render in dashboard widget.
 *
 * @param  array  $widgets
 *
 * @return array
 */
function appointly_register_dashboard_widgets($widgets)
{
    // Add today's appointments widget if enabled
    if (get_option('appointly_today_widget_enabled') == '1') {
        $widgets[] = [
            'container' => 'left-8',
            'path'      => 'appointly/widgets/today_appointments',
        ];
    }

    // Add upcoming appointments widget if enabled
    if (get_option('appointly_upcoming_widget_enabled') == '1') {
        $widgets[] = [
            'container' => 'left-8',
            'path'      => 'appointly/widgets/upcoming_appointments',
        ];
    }

    return $widgets;
}

/**
 * Get staff fields and insert into email templates for appointly.
 *
 * @param [array] $fields
 *
 * @return array
 */
function appointly_allow_staff_merge_fields_for_appointment_templates($fields)
{
    $appointlyStaffFields = ['{staff_firstname}', '{staff_lastname}'];

    foreach ($fields as $index => $group) {
        foreach ($group as $key => $groupFields) {
            if ($key == 'staff') {
                foreach ($groupFields as $groupIndex => $groupField) {
                    if (in_array(
                        $groupField['key'],
                        $appointlyStaffFields,
                        true
                    )) {
                        $fields[$index][$key][$groupIndex]['available'] = array_merge(
                            $fields[$index][$key][$groupIndex]['available'],
                            ['appointly']
                        );
                    }
                }
                break;
            }
        }
    }

    return $fields;
}

/**
 * Register other merge fields for appointly.
 *
 * @param  array  $for
 *
 * @return array
 */
function appointly_register_other_merge_fields($for)
{
    $for[] = 'appointly';

    return $for;
}

/**
 * Hook for assigning staff permissions for appointments module.
 */
function appointly_register_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'         => _l('permission_view'),
        'create'       => _l('permission_create'),
        'edit'         => _l('permission_edit'),
        'delete'       => _l('permission_delete'),
        'approve'      => _l('permission_approve'),
        'view_reports' => _l('permission_view_reports'),
    ];

    register_staff_capabilities('appointments', $capabilities, _l('appointment_appointments'));
}

function appointly_get_environment()
{
    echo '<script>document.addEventListener("DOMContentLoaded", function() { window.AppointlyEnv = "' . ENVIRONMENT . '"; });</script>';
}

/**
 * Ensure menu item has all required keys to prevent position errors
 */
function appointly_ensure_menu_item_structure($item)
{
    // Ensure all required keys exist with defaults
    $defaults = [
        'position' => 0,
        'icon'     => '',
        'href'     => '#',
    ];

    return array_merge($defaults, $item);
}

/**
 * Register new menu item in sidebar menu.
 */
function appointly_register_menu_items()
{
    $CI = &get_instance();

    if (staff_can('view', 'appointments')) {
        $CI->app_menu->add_sidebar_menu_item(APPOINTLY_MODULE_NAME, appointly_ensure_menu_item_structure([
            'name'     => _l('appointly_module_name'),
            'href'     => admin_url('appointly/appointments'),
            'position' => 20,
            'icon'     => 'fa-solid fa-calendar-check',
        ]));
        $CI->app_menu->add_sidebar_children_item(APPOINTLY_MODULE_NAME, appointly_ensure_menu_item_structure([
            'slug'     => 'appointly-user-dashboard',
            'name'     => _l('appointment_appointments'),
            'href'     => admin_url('appointly/appointments'),
            'position' => 1,
            'icon'     => 'fa-solid fa-table-list',
        ]));
    }


    if (staff_can('edit', 'appointments')) {
        $CI->app_menu->add_sidebar_children_item(APPOINTLY_MODULE_NAME, appointly_ensure_menu_item_structure([
            'slug'     => 'appointly-services',
            'name'     => _l('appointment_services_menu_label'),
            'href'     => admin_url('appointly/services'),
            'position' => 2,
            'icon'     => 'fa-solid fa-briefcase',
        ]));
    }

    if (staff_can('edit', 'appointments')) {
        $CI->app_menu->add_sidebar_children_item(APPOINTLY_MODULE_NAME, appointly_ensure_menu_item_structure([
            'slug'     => 'appointly-company-schedule',
            'name'     => _l('appointly_company_schedule'),
            'href'     => admin_url('appointly/services/company_schedule'),
            'position' => 3,
            'icon'     => 'fa-solid fa-business-time',
        ]));
    }

    if (staff_can('edit', 'appointments')) {
        $position = is_admin() ? 4 : 3;
        $CI->app_menu->add_sidebar_children_item(APPOINTLY_MODULE_NAME, appointly_ensure_menu_item_structure([
            'slug'     => 'appointly-staff-working-hours',
            'name'     => _l('appointly_staff_working_hours'),
            'href'     => admin_url('appointly/services/staff_working_hours'),
            'position' => $position,
            'icon'     => 'fa-solid fa-user-clock',
        ]));
    }

    $position = is_admin() ? 5 : 4;
    $CI->app_menu->add_sidebar_children_item(APPOINTLY_MODULE_NAME, appointly_ensure_menu_item_structure([
        'slug'     => 'appointly-user-history',
        'name'     => _l('appointment_history_label_menu_label'),
        'href'     => admin_url('appointly/appointments_history'),
        'position' => $position,
        'icon'     => 'fa-solid fa-clock-rotate-left',
    ]));

    if (staff_can('view_reports', 'appointments')) {
        $position = is_admin() ? 6 : 5;
        $CI->app_menu->add_sidebar_children_item(APPOINTLY_MODULE_NAME, appointly_ensure_menu_item_structure([
            'slug'     => 'appointly-reports',
            'name'     => _l('appointment_analytics_and_reports_menu_label'),
            'icon'     => 'fa-solid fa-chart-line',
            'href'     => admin_url('appointly/reports'),
            'position' => $position,
        ]));
    }

    $position = is_admin() ? 7 : 6;
    $CI->app_menu->add_sidebar_children_item(APPOINTLY_MODULE_NAME, appointly_ensure_menu_item_structure([
        'slug'            => 'appointly-link-menu-form',
        'name'            => _l('appointment_menu_form_link'),
        'href'            => site_url('appointly/appointments_public/book?col=col-md-8+col-md-offset-2'),
        'href_attributes' => 'target="_blank" rel="noopener noreferrer"',
        'position'        => $position,
        'icon'            => 'fa-solid fa-link',
    ]));

    if (staff_can('edit', 'appointments')) {
        $position = is_admin() ? 8 : 7;
        $CI->app_menu->add_sidebar_children_item(APPOINTLY_MODULE_NAME, appointly_ensure_menu_item_structure([
            'slug'     => 'appointly-settings',
            'name'     => _l('settings'),
            'href'     => admin_url('settings?group=appointly_settings'),
            'position' => $position,
            'icon'     => 'fa-solid fa-sliders',
        ]));
    }
}

/*
 * Register activation hook
 */
register_activation_hook(APPOINTLY_MODULE_NAME, 'appointly_activation_hook');

/**
 * The activation function.
 */
function appointly_activation_hook()
{
    try {
        require_once __DIR__ . '/install.php';
        if (function_exists('update_option')) {
            update_option('aside_menu_active', json_encode([]));
            update_option('appointly_smart_choice_php85_safe_activation', '1');
            if (function_exists('log_message')) {
                log_message('info', 'Appointly: Smart Choice safe activation completed');
            }
        }
    } catch (Throwable $e) {
        if (function_exists('log_message')) {
            log_message('error', 'Appointly activation issue: ' . $e->getMessage());
        }
        if (function_exists('update_option')) {
            update_option('appointly_smart_choice_activation_error', $e->getMessage());
        }
        return true;
    }
}

/*
 * Register module language files
 */
register_language_files(APPOINTLY_MODULE_NAME, ['appointly']);

/*
 * Loads the module function helper
 */
$CI->load->helper([APPOINTLY_MODULE_NAME . '/appointly', APPOINTLY_MODULE_NAME . '/appointly_google']);

/**
 * Register cron email templates.
 */
function appointly_send_email_templates()
{
    $CI = &get_instance();
    $CI->load->model('appointly/appointly_attendees_model', 'atm');

    // User events
    $CI->db->where("(notification_date IS NULL AND reminder_before IS NOT NULL AND status = 'in-progress')");

    $appointments   = $CI->db->get(db_prefix() . 'appointly_appointments')->result_array();
    $notified_users = [];

    foreach ($appointments as $appointment) {
        $date_compare = date('Y-m-d H:i', strtotime('+' . $appointment['reminder_before'] . ' ' . strtoupper($appointment['reminder_before_type'])));

        if ($appointment['date'] . ' ' . $appointment['start_hour'] <= $date_compare) {
            if (date('Y-m-d H:i', strtotime($appointment['date'] . ' ' . $appointment['start_hour'])) < date('Y-m-d H:i')) {
                /*
                 * If appointment is missed then skip
                 */
                continue;
            }

            $attendees = $CI->atm->get($appointment['id']);

            foreach ($attendees as $staff) {
                add_notification([
                    'description' => 'appointment_you_have_new_appointment',
                    'touserid'    => $staff['staffid'],
                    'fromcompany' => true,
                    'link'        => 'appointly/appointments/view?appointment_id=' . $appointment['id'],
                ]);

                $notified_users[] = $staff['staffid'];

                send_mail_template('appointly_appointment_cron_reminder_to_staff', 'appointly', array_to_object($appointment), array_to_object($staff));
            }

            $template = mail_template('appointly_appointment_cron_reminder_to_contact', 'appointly', array_to_object($appointment));

            $merge_fields = $template->get_merge_fields();

            $template->send();

            if ($appointment['by_sms'] == 1 && ! empty($appointment['phone'])) {
                $CI->app_sms->trigger(APPOINTLY_SMS_APPOINTMENT_APPOINTMENT_REMINDER_TO_CLIENT, $appointment['phone'], $merge_fields);
            }

            $CI->db->where('id', $appointment['id']);
            $CI->db->update('appointly_appointments', ['notification_date' => date('Y-m-d H:i:s')]);
        }
    }
    pusher_trigger_notification(array_unique($notified_users));
}

function appointly_recurring_events()
{
    $CI             = &get_instance();
    $tableAttendees = db_prefix() . 'appointly_attendees';
    $table          = db_prefix() . 'appointly_appointments';

    // User events
    $CI->db->where('recurring', 1);
    $CI->db->where('(cycles != total_cycles OR cycles=0)');

    $appointments = $CI->db->get(db_prefix() . 'appointly_appointments')->result_array();


    foreach ($appointments as $appointment) {
        $type                = $appointment['recurring_type'];
        $repeat_every        = $appointment['repeat_every'];
        $last_recurring_date = $appointment['last_recurring_date'];

        $appointment_date = $appointment['date'];

        // Current date Check if it is first recurring
        if (! $last_recurring_date) {
            $last_recurring_date = date('Y-m-d', strtotime($appointment_date));
        } else {
            $last_recurring_date = date('Y-m-d', strtotime($last_recurring_date));
        }

        $re_create_at = date(
            'Y-m-d',
            strtotime('+' . $repeat_every . ' ' . strtoupper($type), strtotime($last_recurring_date))
        );

        if (date('Y-m-d') >= $re_create_at) {
            // Ok, we can repeat the appointment now
            $newAppointmentData = [];

            $newAppointmentData['date'] = $re_create_at;

            $newAppointmentData = array_merge($newAppointmentData, convertDateForDatabase($newAppointmentData['date']));

            $newAppointmentData['google_event_id']       = null;
            $newAppointmentData['google_calendar_link']  = null;
            $newAppointmentData['google_meet_link']      = null;
            $newAppointmentData['google_added_by_id']    = $appointment['google_added_by_id'];
            $newAppointmentData['outlook_event_id']      = $appointment['outlook_event_id'];
            $newAppointmentData['outlook_calendar_link'] = $appointment['outlook_calendar_link'];
            $newAppointmentData['outlook_added_by_id']   = $appointment['outlook_added_by_id'];
            $newAppointmentData['subject']               = $appointment['subject'];
            $newAppointmentData['description']           = $appointment['description'];
            $newAppointmentData['email']                 = $appointment['email'];
            $newAppointmentData['name']                  = $appointment['name'];
            $newAppointmentData['phone']                 = $appointment['phone'];
            $newAppointmentData['address']               = $appointment['address'];
            $newAppointmentData['notes']                 = $appointment['notes'];
            $newAppointmentData['contact_id']            = $appointment['contact_id'];
            $newAppointmentData['by_sms']                = $appointment['by_sms'];
            $newAppointmentData['by_email']              = $appointment['by_email'];
            $newAppointmentData['hash']                  = app_generate_hash();
            $newAppointmentData['start_hour']            = $appointment['start_hour'];
            $newAppointmentData['status']                = 'in-progress';
            $newAppointmentData['created_by']            = $appointment['created_by'];
            $newAppointmentData['reminder_before']       = $appointment['reminder_before'];
            $newAppointmentData['reminder_before_type']  = $appointment['reminder_before_type'];
            $newAppointmentData['cancel_notes']          = $appointment['cancel_notes'];
            $newAppointmentData['source']                = $appointment['source'];
            $newAppointmentData['feedback_comment']      = $appointment['feedback_comment'];
            $newAppointmentData['recurring_type']        = null;
            $newAppointmentData['repeat_every']          = 0;
            $newAppointmentData['recurring']             = 0;
            $newAppointmentData['cycles']                = 0;
            $newAppointmentData['total_cycles']          = 0;
            $newAppointmentData['custom_recurring']      = 0;
            $newAppointmentData['last_recurring_date']   = null;


            $newAppointmentData = handleDataReminderFields($newAppointmentData);

            $CI->db->insert($table, $newAppointmentData);

            $insert_id = $CI->db->insert_id();

            if ($insert_id) {
                // Get the old appointment custom field and add to the new
                $fieldTo       = 'appointly';
                $custom_fields = get_custom_fields($fieldTo);

                foreach ($custom_fields as $field) {
                    $value = get_custom_field_value($appointment['id'], $field['id'], $fieldTo, false);

                    if ($value != '') {
                        $CI->db->insert(db_prefix() . 'customfieldsvalues', [
                            'relid'   => $insert_id,
                            'fieldid' => $field['id'],
                            'fieldto' => $fieldTo,
                            'value'   => $value,
                        ]);
                    }
                }

                // update recurring date for original appointment
                $CI->db->where('id', $appointment['id']);
                $CI->db->update($table, ['last_recurring_date' => $re_create_at]);

                // set total_cycles +1 for original appointment
                $CI->db->where('id', $appointment['id']);
                $CI->db->set('total_cycles', 'total_cycles+1', false);
                $CI->db->update($table);

                $googleAttendees = [];

                // insert attendees for new appointment
                $originalAttendees = $CI->db->where('appointment_id', $appointment['id'])
                    ->get($tableAttendees)->result_array();

                foreach ($originalAttendees as &$attendee) {
                    $googleAttendees[]          = $attendee['staff_id'];
                    $attendee['appointment_id'] = $insert_id;
                }

                $CI->db->insert_batch($tableAttendees, $originalAttendees);


                // google calendar
                if ($appointment['google_event_id'] != '') {
                    $CI->load->model('appointly/appointly_model');

                    $lastInsertedAppointment = $CI->db->where('id', $insert_id)->get($table)->row_array();

                    $googleInsertData = $CI->appointly_model->recurringAddGoogleNewEvent(
                        $lastInsertedAppointment,
                        $googleAttendees
                    );

                    if (! empty($googleInsertData)) {
                        // update appointment wih new google event data
                        $CI->db->where('id', $insert_id);
                        $CI->db->update($table, $googleInsertData);
                    }
                }

                newRecurringAppointmentNotifications($insert_id);


                foreach ($googleAttendees as $googleAttendee) {
                    add_notification([
                        'description' => 'appointment_recurring_re_created',
                        'touserid'    => $googleAttendee['staff_id'],
                        'fromcompany' => true,
                        'link'        => 'appointly/appointments/view?appointment_id=' . $insert_id,
                    ]);

                    pusher_trigger_notification([$googleAttendee['staff_id']]);
                }
            }
        }
    }
}

hooks()->add_filter('sms_gateway_available_triggers', 'appointly_register_sms_triggers');
/**
 * Register SMS Triggers for appointly.
 *
 * @param [array] $triggers
 *
 * @return array
 */
function appointly_register_sms_triggers($triggers)
{
    $triggers[APPOINTLY_SMS_APPOINTMENT_APPROVED_TO_CLIENT] = [
        'merge_fields' => [
            '{appointment_subject}',
            '{appointment_date}',
            '{appointment_client_name}',
        ],
        'label'        => 'Appointment approved (Sent to Contact)',
        'info'         => 'Trigger when appointment is approved, SMS will be sent to the appointment contact number.',
    ];

    $triggers[APPOINTLY_SMS_APPOINTMENT_CANCELLED_TO_CLIENT] = [
        'merge_fields' => [
            '{appointment_subject}',
            '{appointment_date}',
            '{appointment_client_name}',
        ],
        'label'        => 'Appointment cancelled (Sent to Contact)',
        'info'         => 'Trigger when appointment is cancelled, SMS will be sent to the appointment contact number.',
    ];

    $triggers[APPOINTLY_SMS_APPOINTMENT_APPOINTMENT_REMINDER_TO_CLIENT] = [
        'merge_fields' => [
            '{appointment_subject}',
            '{appointment_date}',
            '{appointment_client_name}',
        ],
        'label'        => 'Appointment reminder (Sent to Contact)',
        'info'         => 'Trigger when reminder before date is set when appointment is created, SMS will be sent to the appointment contact number.',
    ];

    $triggers[APPOINTLY_SMS_APPOINTMENT_UPDATED_TO_CLIENT] = [
        'merge_fields' => [
            '{appointment_subject}',
            '{appointment_date}',
            '{appointment_client_name}',
        ],
        'label'        => 'Appointment updated (Sent to Contact)',
        'info'         => 'Trigger when appointment is updated, SMS will be sent to the appointment contact number.',
    ];

    return $triggers;
}


/*
 * Check if can have permissions then apply new tab in settings
 */
hooks()->add_action('admin_init', 'appointly_add_settings_section');

function appointly_add_settings_section()
{
    $CI = &get_instance();
    // Message: app_tabs->add_settings_tab is deprecated since version 3.2.0! Use app->add_settings_section instead.

    if (version_compare(get_app_version(), '3.2.0', '<')) {
        $CI->app->add_settings_section_child('other', 'appointly-settings', [
            'name'     => _l('appointment_appointments'),
            'view'     => 'appointly/appointly_settings',
            'position' => 36,
        ]);
    } else {
        $CI->app->add_settings_section('appointly-settings', [
            'title'    => _l('appointly_module_name'),
            'position' => 36,
            'children' => [
                [
                    'name'     => _l('appointment_appointments'),
                    'view'     => 'appointly/appointly_settings',
                    'icon'     => 'fa-regular fa-calendar fa-fw fa-lg',
                    'position' => 10,
                ],
            ],
        ]);
    }
}

/*
 * Need to change encode array values to string for database before post
 * Intercepting settings-form
 */
hooks()->add_filter('before_settings_updated', 'modify_settings_form_post');

function modify_settings_form_post($form)
{
    if (isset($form['settings']['appointly_default_feedbacks'])) {
        $form['settings']['appointly_default_feedbacks'] = json_encode(
            $form['settings']['appointly_default_feedbacks']
        );
        if ($form['settings']['appointly_default_feedbacks'] == null) {
            $form['settings']['appointly_default_feedbacks'] = json_encode([]);
        }
    }

    return $form;
}

if (! function_exists('appointly_add_filters_js')) {
    function appointly_add_filters_js()
    {
        // Only load on appointment pages
        $CI = &get_instance();

        // Load core script for debugging control
        echo '<script src="' . module_dir_url(APPOINTLY_MODULE_NAME, 'assets/js/appointly-core.js') . '?v=' . time() . '"></script>';

        // Load enhanced calendar tooltips on dashboard and calendar pages
        if (
            strpos($CI->uri->uri_string(), 'admin') !== false ||
            strpos($CI->uri->uri_string(), 'admin/utilities/calendar') !== false ||
            strpos($CI->uri->uri_string(), 'appointly/appointments') !== false
        ) {
            // Load tooltip CSS
            echo '<link rel="stylesheet" href="' . module_dir_url(APPOINTLY_MODULE_NAME, 'assets/css/appointly_calendar_tooltips.css') . '?v=' . time() . '">';

            // Load tooltip JavaScript
            echo '<script src="' . module_dir_url(APPOINTLY_MODULE_NAME, 'assets/js/appointly_calendar_tooltips.js') . '?v=' . time() . '"></script>';
        }

        if (strpos($CI->uri->uri_string(), 'appointly/appointments') !== false) {
            echo '<script src="' . module_dir_url(APPOINTLY_MODULE_NAME, 'assets/js/appointly_filters.js') . '?v=' . time() . '"></script>';
        }
    }
}


// Lead tab hooks
hooks()->add_action('after_lead_lead_tabs', 'appointly_add_appointment_tab_to_lead');
hooks()->add_action('after_lead_tabs_content', 'appointly_add_appointment_content_to_lead');

function appointly_add_appointment_tab_to_lead($lead)
{
?>
    <li role="presentation">
        <a href="#lead_appointments" aria-controls="lead_appointments" role="tab" data-toggle="tab">
            <i class="fa-regular fa-calendar"></i>
            <?php
            echo _l('appointment_appointments'); ?>
        </a>
    </li>
<?php
}

function appointly_add_appointment_content_to_lead($lead)
{
?>
    <div role="tabpanel" class="tab-pane" id="lead_appointments">
        <?php
        $CI = &get_instance();
        $CI->load->model('appointly/appointly_model');
        $appointments = $CI->appointly_model->get_lead_appointments($lead->id);
        $CI->load->view('appointly/lead_appointments', ['appointments' => $appointments, 'lead' => $lead]);
        ?>
    </div>
<?php
}

/*
 * Add appointments tab to client profile
 */
hooks()->add_action('admin_init', 'appointly_add_appointments_tab_to_client_profile');

function appointly_add_appointments_tab_to_client_profile()
{
    $CI = &get_instance();

    $CI->app_tabs->add_customer_profile_tab('appointments', [
        'name'     => _l('appointment_appointments'),
        'icon'     => 'fa-regular fa-calendar',
        'view'     => 'appointly/client_appointments_tab',
        'position' => 6,
    ]);
}

/**
 * Send Pusher notification when appointment invoice is paid
 */
hooks()->add_action('after_payment_added', function ($payment_id) {
    $CI = &get_instance();
    $payment = $CI->payments_model->get($payment_id);

    // Check if payment is for an appointment invoice
    $CI->db->select('provider_id, subject');
    $CI->db->where('invoice_id', $payment->invoiceid);
    $appointment = $CI->db->get(db_prefix() . 'appointly_appointments')->row();

    if ($appointment && $appointment->provider_id) {
        add_notification([
            'description' => _l('payment_received_for_appointment') . ' ' . $appointment->subject,
            'touserid' => $appointment->provider_id,
            'fromcompany' => true,
            'link' => 'appointly/appointments/view?appointment_id=' . $appointment->id,
        ]);

        pusher_trigger_notification([$appointment->provider_id]);
    }
});

/**
 * Add appointly email templates to the admin email templates page
 *
 * @return void
 */
if (! function_exists('appointly_add_email_templates')) {
    function appointly_add_email_templates()
    {
        $CI = &get_instance();

        $data['appointly_templates'] = $CI->emails_model->get(['type' => 'appointly', 'language' => 'english']);
        $data['hasPermissionEdit'] = staff_can('edit', 'email_templates');

        $CI->load->view('appointly/email_templates', $data);
    }
}

/**
 * Add appointments permission to client contact permissions
 *
 * @param array $permissions
 * @return array
 */
function appointly_add_contact_permission($permissions)
{
    $permissions[] = [
        'id'         => $permissions[count($permissions) - 1]['id'] + 1,
        'name'       => _l('customer_permission_appointments'),
        'short_name' => 'appointments',
    ];

    return $permissions;
}


// Smart Choice Appointly Upgrade
hooks()->add_action('app_admin_head', 'appointly_smartchoice_admin_assets');
hooks()->add_action('app_customers_head', 'appointly_smartchoice_public_logo_assets');
hooks()->add_action('customers_navigation_end', 'appointly_smartchoice_public_logo_assets');

function appointly_smartchoice_admin_assets()
{
    echo '<link href="' . module_dir_url('appointly', 'assets/css/appointly_smartchoice.css') . '" rel="stylesheet" type="text/css" />';
}

function appointly_smartchoice_public_logo_assets()
{
    echo '<link href="' . module_dir_url('appointly', 'assets/css/appointly_smartchoice.css') . '" rel="stylesheet" type="text/css" />';
}

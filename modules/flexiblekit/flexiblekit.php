<?php


/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Keep In Touch
Description: Keep In Touch. It reminds you to keep in touch with your customers and leads right from your Perfex CRM
Version: 1.0.0
Requires at least: 2.3.*
*/

use Carbon\Carbon;

define('FLEXIBLEKIT_MODULE_NAME', 'flexiblekit');
define('FLEXIBLEKIT_SCHEDULED_STATUS', 'scheduled');
define('FLEXIBLEKIT_CANCELLED_STATUS', 'cancelled');
define('FLEXIBLEKIT_EXPIRED_STATUS', 'expired');
define('FLEXIBLEKIT_HELD_STATUS', 'held');
define('FLEXIBLEKIT_LEAD_CONTACT_TYPE', 'lead');
define('FLEXIBLEKIT_CUSTOMER_CONTACT_TYPE', 'customer');
define('FLEXIBLEKIT_COLOR', '#092338');
define('FLEXIBLEKIT_TYPE_MEETING', 'meeting');
define('FLEXIBLEKIT_TYPE_CALL', 'call');
define('FLEXIBLEKIT_TYPE_TASK', 'task');
hooks()->add_action('admin_init', FLEXIBLEKIT_MODULE_NAME . '_permissions');
hooks()->add_action('after_lead_lead_tabs', FLEXIBLEKIT_MODULE_NAME . '_get_lead_tab');
hooks()->add_action('after_lead_tabs_content', FLEXIBLEKIT_MODULE_NAME . '_get_lead_content');
hooks()->add_filter('get_dashboard_widgets', FLEXIBLEKIT_MODULE_NAME . '_get_dashboard_content');
hooks()->add_action('after_cron_run', FLEXIBLEKIT_MODULE_NAME . '_add_next_schedules');


/**
 * Register activation module hook
 */
register_activation_hook(FLEXIBLEKIT_MODULE_NAME, FLEXIBLEKIT_MODULE_NAME . '_module_activation_hook');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(FLEXIBLEKIT_MODULE_NAME, [FLEXIBLEKIT_MODULE_NAME]);

function flexiblekit_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

function flexiblekit_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities(FLEXIBLEKIT_MODULE_NAME, $capabilities, _l(FLEXIBLEKIT_MODULE_NAME));
    //let us try to kit menu for customer
    $CI = &get_instance();

    $CI->app_tabs->add_customer_profile_tab('flexiblekit', [
        'name'     => _l('flexiblekit'),
        'icon'     => 'fa fa-clock',
        'view'     => 'flexiblekit/customer_content',
        'position' => 10,
        'badge'    => [],
    ]);
}

function flexiblekit_get_lead_tab($contact)
{
    $CI = &get_instance();
    return $CI->load->view('flexiblekit/lead_tab');
}
function flexiblekit_get_lead_content($contact)
{
    $CI = &get_instance();
    $CI->load->model('flexiblekit/flexiblekit_model');
    $CI->load->model('flexiblekit/flexibleschedule_model');
    
    
    return $CI->load->view('flexiblekit/lead_content', [
        'contact' => $contact,
        'contact_type' => FLEXIBLEKIT_LEAD_CONTACT_TYPE,
    ]);
}

function flexiblekit_get_kit_content($contact, $contact_type){
    $CI = &get_instance();
    $CI->load->model('flexiblekit/flexiblekit_model');
    $CI->load->model('flexiblekit/flexibleschedule_model');

    $flexiblekits = $CI->flexiblekit_model->all([
        'flexiblekit_contact_id' => $contact->id,
        'flexiblekit_userid' => get_staff_user_id(),
        'flexiblekit_contact_type' => $contact_type
    ]);

    $meetingschedules = $CI->flexibleschedule_model->all([
        'flexibleschedule_contact_id' => $contact->id,
        'flexibleschedule_userid' => get_staff_user_id(),
        'flexibleschedule_type' => FLEXIBLEKIT_TYPE_MEETING,
        'flexibleschedule_contact_type' => $contact_type
    ]);

    $callschedules = $CI->flexibleschedule_model->all([
        'flexibleschedule_contact_id' => $contact->id,
        'flexibleschedule_userid' => get_staff_user_id(),
        'flexibleschedule_type' => FLEXIBLEKIT_TYPE_CALL,
        'flexibleschedule_contact_type' => $contact_type
    ]);

    $taskschedules = $CI->flexibleschedule_model->all([
        'flexibleschedule_contact_id' => $contact->id,
        'flexibleschedule_userid' => get_staff_user_id(),
        'flexibleschedule_type' => FLEXIBLEKIT_TYPE_TASK,
        'flexibleschedule_contact_type' => $contact_type
    ]);

    return $CI->load->view('flexiblekit/kit_content', [
        'contact' => $contact,
        'contact_type' => $contact_type,
        'flexiblekits' => $flexiblekits,
        'meetingschedules' => $meetingschedules,
        'callschedules' => $callschedules,
        'taskschedules' => $taskschedules
    ]);
}

function flexiblekit_get_types()
{
    return [
        [
            'id' => FLEXIBLEKIT_TYPE_CALL,
            'label' => _l('flexiblekit_call')
        ],
        [
            'id' => FLEXIBLEKIT_TYPE_MEETING,
            'label' => _l('flexiblekit_meeting')
        ],
        [
            'id' => FLEXIBLEKIT_TYPE_TASK,
            'label' => _l('flexiblekit_task')
        ],
    ];
}

function flexiblekit_get_interval_types()
{
    return [
        [
            'id' => 'day',
            'label' => _l('flexiblekit_day')
        ],
        [
            'id' => 'week',
            'label' => _l('flexiblekit_week')
        ],
        [
            'id' => 'month',
            'label' => _l('flexiblekit_month')
        ],
    ];
}

function flexiblekit_get_schedule_statuses()
{
    return [
        [
            'id' => FLEXIBLEKIT_SCHEDULED_STATUS,
            'label' => _l('flexiblekit_scheduled')
        ],
        [
            'id' => FLEXIBLEKIT_HELD_STATUS,
            'label' => _l('flexiblekit_held')
        ],
        [
            'id' => FLEXIBLEKIT_CANCELLED_STATUS,
            'label' => _l('flexiblekit_cancelled')
        ],
        [
            'id' => FLEXIBLEKIT_EXPIRED_STATUS,
            'label' => _l('flexiblekit_expired')
        ],
    ];
}

function flexiblekit_generate_intervals_for_type($interval_type = 'day')
{
    $intervals = [];

    switch ($interval_type) {
        case 'month':
            $label_string = 'flexiblekit_monthly_interval';
            $max = 12;
            break;
        case 'week':
            $label_string = 'flexiblekit_weekly_interval';
            $max = 4;
            break;

        default:
            $label_string = 'flexiblekit_daily_interval';
            $max = 30;
            break;
    }

    for ($i = 1; $i <= $max; $i++) {
        $intervals[] = [
            'id' => $i,
            'label' => _l($label_string, $i)
        ];
    }

    return $intervals;
}


function flexiblekit_generate_intervals()
{
    $interval_types = flexiblekit_get_interval_types();

    $intervals = [];

    foreach ($interval_types as $interval_type) {
        $intervals[$interval_type['id']] = flexiblekit_generate_intervals_for_type($interval_type['id']);
    }

    return $intervals;
}

function flexiblekit_get_intervals($interval_type = 'day')
{
    $intervals = flexiblekit_generate_intervals();

    return $intervals[$interval_type];
}

function flexiblekit_get_interval_label($interval_type, $interval_id){
    $intervals = flexiblekit_get_intervals($interval_type);

    foreach($intervals as $interval){
        if($interval['id'] == $interval_id){
            return $interval['label'];
        }
    }
}

function flexiblekit_get_lead($lead_id){
    $CI = &get_instance();
    $CI->load->model('Leads_model');
    return $CI->leads_model->get($lead_id);
}

function flexiblekit_get_dashboard_content($widgets)
{
    $widgets[] = [
        'path'      => 'flexiblekit/widget',
        'container' => 'right-4',
    ];

    return $widgets;
}

function flexiblekit_add_next_schedules($cronManuallyInvoked = false)
{
    $CI = &get_instance();
    $CI->load->library(FLEXIBLEKIT_MODULE_NAME . '/' . 'schedules_module');
    $CI->schedules_module->add_next_schedules();
}

function flexiblekit_add_schedule($flexiblekit_id){
    $CI = &get_instance();
    $CI->load->library(FLEXIBLEKIT_MODULE_NAME . '/' . 'schedules_module');
    $CI->schedules_module->add($flexiblekit_id);
}

function flexiblekit_get_next_date($start_date, $increment_string = '+ 30 minutes'){
    return date('Y-m-d H:i:s', strtotime($start_date . ' ' . $increment_string));
}

function flexiblekit_is_weekend($date) {
    $weekDay = date('w', strtotime($date));
    return ($weekDay == 0 || $weekDay == 6);
}

function flexiblekit_get_next_schedule(){
    $CI = &get_instance();
    $CI->load->library(FLEXIBLEKIT_MODULE_NAME . '/' . 'schedules_module');
    
    return $CI->schedules_module->get_next_schedule();
}

function flexiblekit_get_previous_schedules(){
    $CI = &get_instance();
    $CI->load->library(FLEXIBLEKIT_MODULE_NAME . '/' . 'schedules_module');
    
    return $CI->schedules_module->get_previous_schedules();
}

function flexiblekit_get_team_schedules(){
    $CI = &get_instance();
    $CI->load->library(FLEXIBLEKIT_MODULE_NAME . '/' . 'schedules_module');
    
    return $CI->schedules_module->get_team_schedules();
}

function flexiblekit_date_for_humans($date){
    return  Carbon::parse($date)->diffForHumans();
}

function flexiblekit_get_type_icon($type){
    switch($type){
        case 'task': return 'text-secondary fa-tasks';
        case 'call': return 'text-success fa-phone';
        default: return 'text-info fa-users';
    }
}

function flexiblekit_get_status_text_color($status){
    switch ($status) {
        case FLEXIBLEKIT_HELD_STATUS:
            return 'text-success';
        case FLEXIBLEKIT_CANCELLED_STATUS:
            return 'text-danger';
        case FLEXIBLEKIT_EXPIRED_STATUS:
            return 'text-warning';
        
        default:
            return 'text-default';
    }
}

function flexiblekit_schedules_url($schedule_id){
    return admin_url("flexiblekit/schedules/$schedule_id");
}

function flexiblekit_get_customer_url($group, $contact_id)
{
    return current_url() 
    . '?group=' . $group 
    . '&contact_id=' . $contact_id; 
}

function flexiblekit_get_customer_contact($contact_id){
    $CI = &get_instance();
    $CI->load->model('Clients_model');
    return $CI->clients_model->get_contact($contact_id);
}

// Smart Choice structural standard assets and permissions.
if (function_exists('hooks')) {
    hooks()->add_action('app_admin_head', 'flexiblekit_smart_choice_standard_head');
    hooks()->add_action('app_admin_footer', 'flexiblekit_smart_choice_standard_footer');
}
if (!function_exists('flexiblekit_smart_choice_standard_head')) {
    function flexiblekit_smart_choice_standard_head() {
        echo '<link href="' . module_dir_url('flexiblekit', 'assets/css/smart_choice_module_standard.css') . '?v=100" rel="stylesheet" type="text/css" />';
    }
}
if (!function_exists('flexiblekit_smart_choice_standard_footer')) {
    function flexiblekit_smart_choice_standard_footer() {
        echo '<script src="' . module_dir_url('flexiblekit', 'assets/js/smart_choice_module_standard.js') . '?v=100"></script>';
    }
}
if (function_exists('register_staff_capabilities')) {
    register_staff_capabilities('flexiblekit', [
        'view_own' => _l('view_own'),
        'view' => _l('view_global'),
        'create' => _l('create'),
        'edit' => _l('edit'),
        'delete' => _l('delete'),
    ], _l('flexiblekit'));
}

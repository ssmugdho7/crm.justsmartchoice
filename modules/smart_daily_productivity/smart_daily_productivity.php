<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Daily Productivity
Description: Smart Choice Contractors USA productivity module for completed tasks, ticket activity, manual daily work logs, employee scorecards, department reports, and end-of-day reporting inside Perfex CRM.
Version: 1.0.1
Requires at least: 3.4.0
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
*/

define('SMART_DAILY_PRODUCTIVITY_MODULE_NAME', 'smart_daily_productivity');
define('SMART_DAILY_PRODUCTIVITY_VERSION', '1.0.2');

hooks()->add_action('admin_init', 'smart_daily_productivity_admin_init');
hooks()->add_action('admin_init', 'smart_daily_productivity_permissions');
hooks()->add_action('app_admin_head', 'smart_daily_productivity_load_assets');
hooks()->add_action('after_task_status_changed', 'smart_daily_productivity_after_task_status_changed');
hooks()->add_action('ticket_status_changed', 'smart_daily_productivity_after_ticket_status_changed');

register_activation_hook(SMART_DAILY_PRODUCTIVITY_MODULE_NAME, 'smart_daily_productivity_activation_hook');
register_deactivation_hook(SMART_DAILY_PRODUCTIVITY_MODULE_NAME, 'smart_daily_productivity_deactivation_hook');
register_language_files(SMART_DAILY_PRODUCTIVITY_MODULE_NAME, [SMART_DAILY_PRODUCTIVITY_MODULE_NAME]);

function smart_daily_productivity_label(string $key): string
{
    static $labels = [
        'smart_daily_productivity_menu' => 'Smart Daily Productivity',
        'smart_daily_productivity_dashboard' => 'Dashboard',
        'smart_daily_productivity_my_day' => 'My Day Report',
        'smart_daily_productivity_reports' => 'Reports',
        'smart_daily_productivity_settings' => 'Settings',
        'smart_daily_productivity_settings_section' => 'Smart Daily Productivity',
        'smart_daily_productivity_health_checker' => 'Health Checker',
        'smart_daily_productivity_help_guide' => 'Help Guide',
        'smart_daily_productivity_database_checker' => 'Database Checker',
        'smart_daily_productivity_dashboard_help' => 'Review employee productivity, completed tasks, ticket activity, work categories, and daily scorecards.',
        'smart_daily_productivity_my_day_help' => 'Employees can add end-of-day activity, time spent, and work notes.',
        'smart_daily_productivity_settings_help' => 'Configure productivity targets, ticket tracking, employee access, and score thresholds.',
        'smart_daily_productivity_health_help' => 'Check database tables, module files, options, logs, and safe cache cleanup.',
        'smart_daily_productivity_help_intro' => 'This module captures completed CRM tasks and ticket activity while allowing employees to add manual daily work entries.',
        'smart_daily_productivity_help_step_1' => 'Completed Perfex CRM tasks are captured automatically when possible.',
        'smart_daily_productivity_help_step_2' => 'Ticket status activity is captured when ticket tracking is enabled.',
        'smart_daily_productivity_help_step_3' => 'Employees can add manual work entries with minutes spent and category.',
        'smart_daily_productivity_help_step_4' => 'Managers can compare scorecards across employees and date ranges.',
        'smart_daily_productivity_backup_warning' => 'Before repair or cleanup actions, create a full database and file backup. This module does not delete business data.',
        'smart_daily_productivity_enabled' => 'Enable Module',
        'smart_daily_productivity_daily_target_minutes' => 'Daily Target Minutes',
        'smart_daily_productivity_daily_target_tasks' => 'Daily Target Activities',
        'smart_daily_productivity_include_tickets' => 'Include Ticket Activity',
        'smart_daily_productivity_allow_employee_manual_entries' => 'Allow Employee Manual Entries',
        'smart_daily_productivity_allow_employee_reports' => 'Allow Employee Reports',
        'smart_daily_productivity_allow_department_reports' => 'Allow Department Reports',
        'smart_daily_productivity_score_high' => 'High Performance Score',
        'smart_daily_productivity_score_low' => 'Low Performance Score',
        'smart_daily_productivity_date_from' => 'Date From',
        'smart_daily_productivity_date_to' => 'Date To',
        'smart_daily_productivity_refresh' => 'Refresh',
        'smart_daily_productivity_all_staff' => 'All Staff',
        'smart_daily_productivity_entries' => 'Entries',
        'smart_daily_productivity_total_hours' => 'Total Hours',
        'smart_daily_productivity_total_minutes' => 'Total Minutes',
        'smart_daily_productivity_tasks' => 'Tasks',
        'smart_daily_productivity_tickets' => 'Tickets',
        'smart_daily_productivity_manual' => 'Manual',
        'smart_daily_productivity_average_score' => 'Average Score',
        'smart_daily_productivity_employee_scoreboard' => 'Employee Scoreboard',
        'smart_daily_productivity_score' => 'Score',
        'smart_daily_productivity_rating' => 'Rating',
        'smart_daily_productivity_completed_tasks' => 'Completed Tasks',
        'smart_daily_productivity_ticket_closed' => 'Closed Tickets',
        'smart_daily_productivity_add_daily_entry' => 'Add Daily Entry',
        'smart_daily_productivity_entry_date' => 'Entry Date',
        'smart_daily_productivity_activity_title' => 'Activity Title',
        'smart_daily_productivity_minutes_spent' => 'Minutes Spent',
        'smart_daily_productivity_category' => 'Category',
        'smart_daily_productivity_description' => 'Description',
        'smart_daily_productivity_daily_entries' => 'Daily Entries',
        'smart_daily_productivity_source' => 'Source',
        'smart_daily_productivity_title' => 'Title',
        'smart_daily_productivity_minutes' => 'Minutes',
        'smart_daily_productivity_entry_saved' => 'Daily productivity entry saved successfully.',
        'smart_daily_productivity_entry_deleted' => 'Daily productivity entry deleted successfully.',
        'smart_daily_productivity_compare_employees' => 'Compare Employees',
        'smart_daily_productivity_project_tasks' => 'Project Tasks',
        'smart_daily_productivity_contract_tasks' => 'Contract Tasks',
        'smart_daily_productivity_client_tasks' => 'Client Tasks',
        'smart_daily_productivity_research_tasks' => 'Research Tasks',
        'smart_daily_productivity_table' => 'Table',
        'smart_daily_productivity_item' => 'Item',
        'smart_daily_productivity_status' => 'Status',
        'smart_daily_productivity_ok' => 'OK',
        'smart_daily_productivity_missing' => 'Missing',
        'smart_daily_productivity_repair_database' => 'Repair Database Safely',
        'smart_daily_productivity_safe_cache_cleanup' => 'Safe Cache Cleanup',
        'smart_daily_productivity_cache_cleaned' => 'Safe cache cleanup completed.',
        'smart_daily_productivity_database_repaired' => 'Database checker repair completed safely.',
        'smart_daily_productivity_logs' => 'Logs',
        'smart_daily_productivity_action' => 'Action',
        'smart_daily_productivity_message' => 'Message',
        'smart_daily_productivity_module_files' => 'Module Files',
        'smart_daily_productivity_module_options' => 'Module Options',
        'smart_daily_productivity_permission_reports' => 'Reports',
        'smart_daily_productivity_permission_settings' => 'Settings',
        'smart_daily_productivity_permission_health' => 'Health Checker',
        'smart_daily_productivity_permission_department_reports' => 'Department Reports',
    ];

    $translated = _l($key);
    if ($translated !== $key && strpos($translated, '_') === false) {
        return $translated;
    }
    if (isset($labels[$key])) {
        return $labels[$key];
    }
    return ucwords(str_replace('_', ' ', preg_replace('/^smart_daily_productivity_/', '', $key)));
}


function smart_daily_productivity_activation_hook(): void
{
    require_once __DIR__ . '/install.php';
}

function smart_daily_productivity_deactivation_hook(): void
{
    $CI = &get_instance();
    update_option('smart_daily_productivity_enabled', '0');
    smart_daily_productivity_write_log('module_deactivated', 'Module was deactivated.', (int) get_staff_user_id());
}

function smart_daily_productivity_admin_init(): void
{
    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('smart-daily-productivity', [
        'name'     => smart_daily_productivity_label('smart_daily_productivity_menu'),
        'href'     => admin_url('smart_daily_productivity'),
        'position' => 36,
        'icon'     => 'fa fa-line-chart',
    ]);

    $CI->app_menu->add_sidebar_children_item('smart-daily-productivity', [
        'slug'     => 'smart-daily-productivity-dashboard',
        'name'     => smart_daily_productivity_label('smart_daily_productivity_dashboard'),
        'href'     => admin_url('smart_daily_productivity'),
        'position' => 1,
    ]);

    $CI->app_menu->add_sidebar_children_item('smart-daily-productivity', [
        'slug'     => 'smart-daily-productivity-my-day',
        'name'     => smart_daily_productivity_label('smart_daily_productivity_my_day'),
        'href'     => admin_url('smart_daily_productivity/my_day'),
        'position' => 2,
    ]);

    $CI->app_menu->add_sidebar_children_item('smart-daily-productivity', [
        'slug'     => 'smart-daily-productivity-reports',
        'name'     => smart_daily_productivity_label('smart_daily_productivity_reports'),
        'href'     => admin_url('smart_daily_productivity/reports'),
        'position' => 3,
    ]);

    $CI->app_menu->add_sidebar_children_item('smart-daily-productivity', [
        'slug'     => 'smart-daily-productivity-health',
        'name'     => smart_daily_productivity_label('smart_daily_productivity_health_checker'),
        'href'     => admin_url('smart_daily_productivity/health'),
        'position' => 4,
    ]);


    $CI->app_menu->add_sidebar_children_item('smart-daily-productivity', [
        'slug'     => 'smart-daily-productivity-help',
        'name'     => smart_daily_productivity_label('smart_daily_productivity_help_guide'),
        'href'     => admin_url('smart_daily_productivity/help'),
        'position' => 5,
    ]);

    $CI->app->add_settings_section('smart_daily_productivity', [
        'name'     => smart_daily_productivity_label('smart_daily_productivity_settings_section'),
        'view'     => 'smart_daily_productivity/settings',
        'position' => 45,
    ]);
}

function smart_daily_productivity_permissions(): void
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'              => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'view_own'          => _l('permission_view_own'),
        'create'            => _l('permission_create'),
        'edit'              => _l('permission_edit'),
        'delete'            => _l('permission_delete'),
        'reports'           => smart_daily_productivity_label('smart_daily_productivity_permission_reports'),
        'settings'          => smart_daily_productivity_label('smart_daily_productivity_permission_settings'),
        'health'            => smart_daily_productivity_label('smart_daily_productivity_permission_health'),
        'department_reports'=> smart_daily_productivity_label('smart_daily_productivity_permission_department_reports'),
    ];
    register_staff_capabilities('smart_daily_productivity', $capabilities, smart_daily_productivity_label('smart_daily_productivity_menu'));
}

function smart_daily_productivity_load_assets(): void
{
    if (!is_admin()) {
        return;
    }

    $CI = &get_instance();
    $uri = (string) $CI->uri->uri_string();

    if (strpos($uri, 'admin/smart_daily_productivity') !== false || strpos($uri, 'admin/settings') !== false) {
        echo '<link href="' . module_dir_url(SMART_DAILY_PRODUCTIVITY_MODULE_NAME, 'assets/css/smart_daily_productivity.css') . '?v=' . SMART_DAILY_PRODUCTIVITY_VERSION . '" rel="stylesheet" type="text/css" />';
        echo '<script src="' . module_dir_url(SMART_DAILY_PRODUCTIVITY_MODULE_NAME, 'assets/js/smart_daily_productivity.js') . '?v=' . SMART_DAILY_PRODUCTIVITY_VERSION . '"></script>';
    }
}

function smart_daily_productivity_after_task_status_changed($data): void
{
    $CI = &get_instance();
    $CI->load->model('smart_daily_productivity/Smart_daily_productivity_model');
    $CI->Smart_daily_productivity_model->capture_completed_task($data);
}

function smart_daily_productivity_after_ticket_status_changed($data): void
{
    $CI = &get_instance();
    $CI->load->model('smart_daily_productivity/Smart_daily_productivity_model');
    $CI->Smart_daily_productivity_model->capture_ticket_status_change($data);
}

function smart_daily_productivity_write_log(string $action, string $message, int $staffId = 0): void
{
    $CI = &get_instance();
    if (!$CI->db->table_exists(db_prefix() . 'smart_daily_productivity_logs')) {
        return;
    }

    $CI->db->insert(db_prefix() . 'smart_daily_productivity_logs', [
        'staff_id'   => $staffId > 0 ? $staffId : (int) get_staff_user_id(),
        'action'     => $action,
        'message'    => $message,
        'ip_address' => $CI->input->ip_address(),
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

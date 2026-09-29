<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Ticket Feedback
Description: A module to collect customer feedback on support tickets.
Version: 1.0.0
Requires at least: 2.3.*
*/

define('TICKET_FEEDBACK_MODULE_NAME', 'ticket_feedback');

$CI = &get_instance();

/**
 * Register activation module hook
 */
register_activation_hook(TICKET_FEEDBACK_MODULE_NAME, 'ticket_feedback_module_activation_hook');

/**
 * Load the module helper file
 */

$CI->load->helper(TICKET_FEEDBACK_MODULE_NAME . '/' . TICKET_FEEDBACK_MODULE_NAME);



function ticket_feedback_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register deactivation module hook
 */
register_deactivation_hook(TICKET_FEEDBACK_MODULE_NAME, 'ticket_feedback_module_deactivation_hook');

function ticket_feedback_module_deactivation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/uninstall.php');
}

/**
 * Register language files
 */
register_language_files(TICKET_FEEDBACK_MODULE_NAME, [TICKET_FEEDBACK_MODULE_NAME]);



/**
 * Register user permissions
 */
hooks()->add_action('admin_init', 'ticket_feedback_register_user_permissions');

function ticket_feedback_register_user_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view' => _l('ticket_feedback_permission_view'),
            'create' => _l('ticket_feedback_permission_create'),
        ],
    ];

    register_staff_capabilities('ticket_feedback', $capabilities, _l('ticket_feedback_module'));
}



hooks()->add_action('clients_init', 'add_client_feedback_menu');

function add_client_feedback_menu()
{
    if (is_client_logged_in()) {
        // Main Feedback Menu (Links to a central page)
        add_theme_menu_item('feedback_main_menu', [
            'name'     => _l('Feedback'),
            'href'     => site_url('ticket_feedback/client_ticket_feedback/client_feedback_dashboard'), // NEW PAGE
            'position' => 30,
            'icon'     => 'fa fa-star',
        ]);

    
    }
}





/**
 * Initialize Feedback Menu in the Admin Area with Submenus
 */
hooks()->add_action('admin_init', 'ticket_feedback_module_init_menu');

function ticket_feedback_module_init_menu()
{
    $CI = &get_instance();

    // ✅ Add Parent Menu for Feedback
    $CI->app_menu->add_sidebar_menu_item('feedback_main_menu', [
        'name' => _l('Tickets Feedback'),
        'icon' => 'fa fa-comments',
        'position' => 30,
    ]);

    // ✅ Add "Manage Feedback" Submenu
    $CI->app_menu->add_sidebar_children_item('feedback_main_menu', [
        'slug' => 'ticket_feedback_manage',
        'name' => _l('Manage Feedback'),
        'href' => admin_url('ticket_feedback/ticket_feedback_controller/manage'),
        'position' => 1,
        'icon' => 'fa fa-list-alt',
    ]);

    // ✅ Add "Feedback Tracking (Customer-wise)" Submenu
    $CI->app_menu->add_sidebar_children_item('feedback_main_menu', [
        'slug' => 'feedback_tracking_menu',
        'name' => _l('Feedback Tracking'),
        'href' => admin_url('ticket_feedback/ticket_feedback_controller/feedback_by_customer'),
        'position' => 2,
        'icon' => 'fa fa-user',
    ]);


    $CI->app_menu->add_sidebar_children_item('feedback_main_menu', [
        'slug' => 'staff_feedback_report',
        'name' => _l('Staff Feedback Report'),
        'href' => admin_url('ticket_feedback/staff_report'),
        'icon' => 'fa fa-bar-chart',
        'position' => 3,
    ]);

    $CI->app_menu->add_sidebar_menu_item('project_feedback_menu', [
        'name' => _l('Project Feedback'),
        'href' => admin_url('ticket_feedback/project_feedback/index'),
        'position' => 31,
        'icon' => 'fa fa-project-diagram',
    ]);
    

}

hooks()->add_action('after_cron_run', 'ticket_feedback_send_reminder_emails');

function ticket_feedback_send_reminder_emails()
{
   

    $CI = &get_instance();
    $CI->load->model('ticket_feedback/ticket_feedback_model');

    // ✅ Use the correct reference to call the function
    if (method_exists($CI->ticket_feedback_model, 'send_feedback_reminders')) {
        $CI->ticket_feedback_model->send_feedback_reminders();
    } else {
        log_activity('ERROR: Method send_feedback_reminders() not found in ticket_feedback_model');
    }
}









/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

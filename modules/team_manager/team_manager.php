<?php
/**
 * Module Name: Team Manager
 * Description: A module for managing teams and assigning staff members as team members in Perfex CRM.
 *              Provides functionality to add, edit, delete teams and manage their assigned staff.
 * Version: 1.0.0
 * Author: perfexdev360@gmail.com
 */

defined('BASEPATH') or exit('No direct script access allowed');

// Define the module constant used for activation
define('TEAM_MANAGER_MODULE', 'team_manager');

// Register language files for the module
register_language_files(TEAM_MANAGER_MODULE, [TEAM_MANAGER_MODULE]);

// Register the module activation hook to run the install script when the module is activated
register_activation_hook(TEAM_MANAGER_MODULE, 'TEAM_MANAGER_MODULE_ACTIVATION_HOOK');

// Include the installation script to create required tables
require_once(__DIR__ . '/install.php');

// Register the module's sidebar menu item on admin initialization
hooks()->add_action('admin_init', 'TEAM_MANAGER_INIT_MENU_ITEMS');

if (!function_exists('TEAM_MANAGER_INIT_MENU_ITEMS')) {
    /**
     * Adds the Team Manager menu item to the admin sidebar.
     */
    function TEAM_MANAGER_INIT_MENU_ITEMS()
    {
        $CI = &get_instance();
        if (has_permission('team_manager', '', 'view')) {
            $CI->app_menu->add_sidebar_menu_item('team_manager', [
                'name'     => _l('team_manager'),
                'href'     => admin_url('team_manager'),
                'icon'     => 'fa fa-users',
                'position' => 70,
            ]);
        }
    }
}

if (!function_exists('TEAM_MANAGER_MODULE_ACTIVATION_HOOK')) {
    /**
     * Activation hook for the module.
     * This function is triggered when the module is activated.
     * It includes the installation script to set up required database tables.
     */
    function TEAM_MANAGER_MODULE_ACTIVATION_HOOK()
    {
        require_once(__DIR__ . '/install.php');
    }
}

/**
 * Add teams data to lead view using hook.
 * This filter injects all teams (managed via the team_manager module)
 * into the lead view data. It will be applied when calling:
 * $data = hooks()->apply_filters('lead_view_data', $data);
 */
hooks()->add_filter('lead_view_data', 'TEAM_MANAGER_LEAD_VIEW_DATA_FILTER');

    function TEAM_MANAGER_LEAD_VIEW_DATA_FILTER($data)
    {
            $CI = &get_instance();
            $CI->load->model('team_manager/team_manager_model');
            // Retrieve all teams
            $all_teams = $CI->team_manager_model->get_teams();
            $data['user_teams'] = $all_teams;
      
        return $data;
    }


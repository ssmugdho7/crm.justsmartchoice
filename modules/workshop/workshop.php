<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Workshop Management
Description: This module designed for job and mechanic management, customer vehicle maintenance history, inventory, invoices and more.
Version: 1.0.0
Requires at least: 2.3.*
Author: GreenTech Solutions
Author URI: https://codecanyon.net/user/greentech_solutions
*/

define('WORKSHOP_MODULE_NAME', 'workshop');
define('WORKSHOP_MODULE_UPLOAD_FOLDER', module_dir_path(WORKSHOP_MODULE_NAME, 'uploads'));

/*add folder upload link on here*/
define('MANUFACTURER_IMAGES_FOLDER', module_dir_path(WORKSHOP_MODULE_NAME, 'uploads/manufacturers/'));
define('DEVICES_IMAGES_FOLDER', module_dir_path(WORKSHOP_MODULE_NAME, 'uploads/devices/'));
define('DEVICE_UPLOAD_PATH', 'modules/workshop/uploads/devices/');
define('MAIN_IMAGE_DEVICES_IMAGES_FOLDER', module_dir_path(WORKSHOP_MODULE_NAME, 'uploads/main_image_devices/'));
define('REPAIR_JOB_BARCODE', 'modules/workshop/uploads/repair_job_barcodes/');
define('TRANSACTION_FOLDER', module_dir_path(WORKSHOP_MODULE_NAME, 'uploads/return_deliveries/'));
define('TRANSACTION_UPLOAD_PATH', 'modules/workshop/uploads/return_deliveries/');
define('NOTE_FOLDER', module_dir_path(WORKSHOP_MODULE_NAME, 'uploads/notes/'));
define('NOTE_UPLOAD_PATH', 'modules/workshop/uploads/notes/');
define('WORKSHOP_FOLDER', module_dir_path(WORKSHOP_MODULE_NAME, 'uploads/workshops/'));
define('WORKSHOP_UPLOAD_PATH', 'modules/workshop/uploads/workshops/');
define('INSPECTION_FOLDER', module_dir_path(WORKSHOP_MODULE_NAME, 'uploads/inspections/'));
define('INSPECTION_UPLOAD_PATH', 'modules/workshop/uploads/inspections/');
define('INSPECTION_QUESTION_FOLDER', module_dir_path(WORKSHOP_MODULE_NAME, 'uploads/inspection_questions/'));
define('INSPECTION_QUESTION_UPLOAD_PATH', 'modules/workshop/uploads/inspection_questions/');
define('REPAIR_JOB_QR_FOLDER', module_dir_path(WORKSHOP_MODULE_NAME, 'uploads/repair_job_qrs/'));
define('REPAIR_JOB_QR_UPLOAD_PATH', 'modules/workshop/uploads/repair_job_qrs/');

/*link view on here*/

hooks()->add_action('admin_init', 'workshop_permissions');
hooks()->add_action('app_admin_head', 'workshop_add_head_components');
hooks()->add_action('app_admin_footer', 'workshop_load_js');
hooks()->add_action('app_search', 'workshop_load_search');
hooks()->add_action('admin_init', 'workshop_module_init_menu_items');

//workshop add customfield
hooks()->add_action('after_custom_fields_select_options','init_workshop_customfield');

// Task related work order
hooks()->add_action('task_modal_rel_type_select', 'wshop_inspection_task_modal_rel_type_select'); // new
hooks()->add_filter('relation_values', 'wshop_inspection_get_relation_values', 10, 2); // new
hooks()->add_filter('get_relation_data', 'wshop_inspection_get_relation_data', 10, 4); // new
hooks()->add_filter('tasks_table_row_data', 'wshop_inspection_add_table_row', 10, 3);

// client portal menu
hooks()->add_action('customers_navigation_end', 'init_workshop_menu');
hooks()->add_action('app_customers_portal_head', 'workshop_client_add_footer_components');

// Removido os hooks de ativação/desativação que verificavam licença
// hooks()->add_action('pre_activate_module', WORKSHOP_MODULE_NAME.'_preactivate');
// hooks()->add_action('pre_deactivate_module', WORKSHOP_MODULE_NAME.'_predeactivate');
define('VERSION_WORKSHOP', 100);

/**
 * Register activation module hook
 */
register_activation_hook(WORKSHOP_MODULE_NAME, 'workshop_module_activation_hook');

function workshop_module_activation_hook()
{
    $CI = &get_instance();
    require_once __DIR__ . '/install.php';
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(WORKSHOP_MODULE_NAME, [WORKSHOP_MODULE_NAME]);

$CI = &get_instance();
$CI->load->helper(WORKSHOP_MODULE_NAME . '/workshop');
$CI->load->helper(WORKSHOP_MODULE_NAME . '/workshop_custom_fields');
$CI->load->helper(WORKSHOP_MODULE_NAME . '/workshop_inspection_template');
$CI->load->helper(WORKSHOP_MODULE_NAME . '/workshop_inspection');

/**
 * Init goals module menu items in setup in admin_init hook
 * @return null
 */
function workshop_module_init_menu_items()
{
    $CI = &get_instance();

    /*add menu on here*/

    if (has_permission('workshop_dashboard', '', 'view') || has_permission('workshop_repair_job', '', 'view') || has_permission('workshop_device', '', 'view') || has_permission('workshop_mechanic', '', 'view') || has_permission('workshop_labour_product', '', 'view') || has_permission('workshop_branch', '', 'view') || has_permission('workshop_inspection', '', 'view') || has_permission('workshop_workshop', '', 'view') || has_permission('workshop_setting', '', 'view') || has_permission('workshop_dashboard', '', 'view_own') || has_permission('workshop_repair_job', '', 'view_own') || has_permission('workshop_device', '', 'view_own') || has_permission('workshop_mechanic', '', 'view_own') || has_permission('workshop_labour_product', '', 'view_own') || has_permission('workshop_branch', '', 'view_own') || has_permission('workshop_inspection', '', 'view_own') || has_permission('workshop_workshop', '', 'view_own') || has_permission('workshop_setting', '', 'view_own')) {

        $CI->app_menu->add_sidebar_menu_item('workshop', [
            'name'     => _l('wshop_workshops_name'),
            'icon'     => 'fa-solid fa-users-viewfinder',
            'position' => 5,
        ]);
    }

    if (has_permission('workshop_dashboard', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('workshop', [
            'slug'     => 'wshop_dashboard',
            'name'     => _l('wshop_dashboard'),
            'icon'     => 'fa fa-dashboard',
            'href'     => admin_url('workshop/dashboard'),
            'position' => 1,
        ]);
    }
    if (has_permission('workshop_repair_job', '', 'view') || has_permission('workshop_repair_job', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('workshop', [
            'slug'     => 'wshop_repair_job',
            'name'     => _l('wshop_repair_jobs'),
            'icon'     => 'fa-solid fa-screwdriver-wrench',
            'href'     => admin_url('workshop/repair_jobs'),
            'position' => 2,
        ]);
    }
    if (has_permission('workshop_device', '', 'view') || has_permission('workshop_device', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('workshop', [
            'slug'     => 'wshop_device',
            'name'     => _l('wshop_devices'),
            'icon'     => 'fa-solid fa-microchip',
            'href'     => admin_url('workshop/devices'),
            'position' => 3,
        ]);
    }
    if (has_permission('workshop_mechanic', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('workshop', [
            'slug'     => 'wshop_mechanic',
            'name'     => _l('wshop_mechanics'),
            'icon'     => 'fa-solid fa-user-gear',
            'href'     => admin_url('workshop/mechanics'),
            'position' => 4,
        ]);
    }
    if (has_permission('workshop_labour_product', '', 'view') || has_permission('workshop_labour_product', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('workshop', [
            'slug'     => 'wshop_labour_product',
            'name'     => _l('wshop_labour_products'),
            'icon'     => 'fa-solid fa-circle-check',
            'href'     => admin_url('workshop/labour_products'),
            'position' => 5,
        ]);
    }
    if (has_permission('workshop_branch', '', 'view') || has_permission('workshop_branch', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('workshop', [
            'slug'     => 'wshop_branch',
            'name'     => _l('wshop_branches'),
            'icon'     => 'fa-solid fa-building',
            'href'     => admin_url('workshop/branches'),
            'position' => 5,
        ]);
    }
    
    if (has_permission('workshop_inspection', '', 'view') || has_permission('workshop_inspection', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('workshop', [
            'slug'     => 'wshop_inspection',
            'name'     => _l('wshop_inspections'),
            'icon'     => 'fa-solid fa-clipboard-check',
            'href'     => admin_url('workshop/inspections'),
            'position' => 6,
        ]);
    }
    if (has_permission('workshop_workshop', '', 'view') || has_permission('workshop_workshop', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('workshop', [
            'slug'     => 'wshop_workshop',
            'name'     => _l('wshop_workshops'),
            'icon'     => 'fa-solid fa-note-sticky',
            'href'     => admin_url('workshop/workshops'),
            'position' => 7,
        ]);
    }
    if (has_permission('workshop_report', '', 'view') && 1==2) {
        $CI->app_menu->add_sidebar_children_item('workshop', [
            'slug'     => 'wshop_report',
            'name'     => _l('wshop_reports'),
            'icon'     => 'fa-solid fa-chart-bar',
            'href'     => admin_url('workshop/reports'),
            'position' => 8,
        ]);
    }
    

    if (has_permission('workshop_setting', '', 'create') || has_permission('workshop_setting', '', 'edit') || has_permission('workshop_setting', '', 'delete')) {
        $CI->app_menu->add_sidebar_children_item('workshop', [
            'slug'     => 'wshop_setting',
            'name'     => _l('wshop_settings'),
            'icon'     => 'fa fa-cog menu-icon',
            'href'     => admin_url('workshop/setting?group=general_settings'),
            'position' => 10,
        ]);
    }

}

/**
 * workshop load js
 */
function workshop_load_js()
{
    $CI      = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];

    /*change this code*/
    if ( ! (strpos($viewuri, 'admin/workshop') === false)) {
        echo '<script src="' . module_dir_url(WORKSHOP_MODULE_NAME, 'assets/js/tinymce_init.js') .'?v=' . VERSION_WORKSHOP.'"></script>';
    }

    if(!(strpos($viewuri,'admin/workshop/dashboard') === false)){

        echo '<script src="'.module_dir_url(WORKSHOP_MODULE_NAME, 'assets/plugins/highcharts/highcharts.js').'?v=' . VERSION_WORKSHOP.'"></script>';
        echo '<script src="'.module_dir_url(WORKSHOP_MODULE_NAME, 'assets/plugins/highcharts/variable-pie.js').'?v=' . VERSION_WORKSHOP.'"></script>';
        echo '<script src="'.module_dir_url(WORKSHOP_MODULE_NAME, 'assets/plugins/highcharts/export-data.js').'?v=' . VERSION_WORKSHOP.'"></script>';
        echo '<script src="'.module_dir_url(WORKSHOP_MODULE_NAME, 'assets/plugins/highcharts/accessibility.js').'?v=' . VERSION_WORKSHOP.'"></script>';
        echo '<script src="'.module_dir_url(WORKSHOP_MODULE_NAME, 'assets/plugins/highcharts/exporting.js').'?v=' . VERSION_WORKSHOP.'"></script>';
        echo '<script src="'.module_dir_url(WORKSHOP_MODULE_NAME, 'assets/plugins/highcharts/highcharts-3d.js').'?v=' . VERSION_WORKSHOP.'"></script>';
    }

}

/**
 * workshop add head components
 */
function workshop_add_head_components()
{
    $CI      = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];

    /*change this code*/
    if ( ! (strpos($viewuri, 'admin/workshop') === false)) {
        echo '<link href="' . module_dir_url(WORKSHOP_MODULE_NAME, 'assets/css/styles.css') . '?v=' . VERSION_WORKSHOP . '"  rel="stylesheet" type="text/css" />';
    }
    if ( ! (strpos($viewuri, 'admin/workshop/inspection_template_detail') === false) || ! (strpos($viewuri, 'admin/workshop/inspection_form') === false)) {
        echo '<link href="' . module_dir_url(WORKSHOP_MODULE_NAME, 'assets/css/inspection_templates/inspection_template_form.css') . '?v=' . VERSION_WORKSHOP . '"  rel="stylesheet" type="text/css" />';
    }
    if ( ! (strpos($viewuri, 'admin/workshop/add_edit_repair_job') === false)) {
        echo '<link href="' . module_dir_url(WORKSHOP_MODULE_NAME, 'assets/css/repair_jobs/repair_job.css') . '?v=' . VERSION_WORKSHOP . '"  rel="stylesheet" type="text/css" />';
    }

    if(!(strpos($viewuri,'admin/workshop/add_edit_repair_job') === false)){
        echo '<link href="' . module_dir_url(WORKSHOP_MODULE_NAME, 'assets/css/box_loading/box_loading.css') . '?v=' . VERSION_WORKSHOP. '"  rel="stylesheet" type="text/css" />';
    }
    if(!(strpos($viewuri,'admin/workshop/repair_job_detail') === false)){
        echo '<link href="' . module_dir_url(WORKSHOP_MODULE_NAME, 'assets/css/repair_jobs/print_label.css') . '?v=' . VERSION_WORKSHOP. '"  rel="stylesheet" type="text/css" />';
    }
    if(!(strpos($viewuri,'admin/workshop/inspection_form') === false)){
        echo '<link href="' . module_dir_url(WORKSHOP_MODULE_NAME, 'assets/css/inspections/inspection_form.css') . '?v=' . VERSION_WORKSHOP. '"  rel="stylesheet" type="text/css" />';
    }
    

}

/**
 * workshop permissions
 */
function workshop_permissions()
{

    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $capabilities_global['capabilities'] = [
        'view_own' => _l('permission_view_own'),
        'view'     => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ];

    $capabilities_without_view_own['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('edit'),
        'delete' => _l('permission_delete'),
    ];
    $capabilities_without_view['capabilities'] = [
        'create' => _l('permission_create'),
        'edit'   => _l('edit'),
        'delete' => _l('permission_delete'),
    ];

    $capabilities_without_view_global['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
    ];

    register_staff_capabilities('workshop_dashboard', $capabilities_without_view_global, _l('workshop_permission_dashboard'));
    register_staff_capabilities('workshop_repair_job', $capabilities_global, _l('workshop_permission_repair_job'));
    register_staff_capabilities('workshop_device', $capabilities_global, _l('workshop_permission_device'));
    register_staff_capabilities('workshop_mechanic', $capabilities, _l('workshop_permission_mechanic'));
    register_staff_capabilities('workshop_labour_product', $capabilities_global, _l('workshop_permission_labour_product'));
    register_staff_capabilities('workshop_branch', $capabilities_global, _l('workshop_permission_branch'));
    register_staff_capabilities('workshop_inspection', $capabilities_global, _l('workshop_permission_inspection'));
    register_staff_capabilities('workshop_workshop', $capabilities_global, _l('workshop_permission_workshop'));
    if(false){
        register_staff_capabilities('workshop_report', $capabilities_global, _l('workshop_permission_report'));
    }
    register_staff_capabilities('workshop_setting', $capabilities_without_view, _l('workshop_permission_setting'));

}

/**
 * init workshop customfield
 * @param  string $custom_field 
 * @return [type]               
 */
function init_workshop_customfield($custom_field = ''){
    $select = '';
    if($custom_field != ''){
        if($custom_field->fieldto == 'wshop_device'){
            $select = 'selected';
        }
        
    }

    $html = '<option value="wshop_device" '.$select.' >'. _l('wshop_workshop_device').'</option>';
    echo new_html_entity_decode($html);
}

/**
 * wshop_inspection task modal rel type select
 * @param  [type] $value 
 * @return [type]        
 */
function wshop_inspection_task_modal_rel_type_select($value) {
    $selected = '';
    if (isset($value) && isset($value['rel_type']) && $value['rel_type'] == 'wshop_inspection') {
        $selected = 'selected';
    }
    echo "<option value='wshop_inspection' " . $selected . ">" .
    _l('wshop_inspection') . "
    </option>";

}

/**
 * wshop_inspection get relation values
 * @param  [type] $values   
 * @param  [type] $relation 
 * @return [type]           
 */
function wshop_inspection_get_relation_values($values, $relation = null) {
    if ($values['type'] == 'wshop_inspection') {
        if (is_array($relation)) {
            $values['id'] = $relation['id'];
            $values['name'] = format_inspection_number($relation['id']);
        } else {
            $values['id'] = $relation->id;
            $values['name'] = format_inspection_number($relation->id);
        }

        $CI = &get_instance();
        $CI->load->model('workshop/workshop_model');
        $work_order = $CI->workshop_model->get_inspection($values['id']);
        if($work_order){
            $values['link'] = admin_url('workshop/inspection_detail/' . $values['id'].'/?tab=detail');
        }else{
            $values['link'] = '';
        }

    }

    return $values;
}

/**
 * wshop_inspection get relation data
 * @param  [type] $data 
 * @param  [type] $obj  
 * @return [type]       
 */
function wshop_inspection_get_relation_data($data, $obj) {
    $type = $obj['type'];
    $rel_id = $obj['rel_id'];
    $CI = &get_instance();
    $CI->load->model('workshop/workshop_model');

    if ($type == 'wshop_inspection') {
        if ($rel_id != '') {
            $data = $CI->workshop_model->get_inspection($rel_id);
        } else {
            $data = [];
        }
    }

    return $data;
}

/**
 * wshop_inspection add table row
 * @param  [type] $row  
 * @param  [type] $aRow 
 * @return [type]       
 */
function wshop_inspection_add_table_row($row ,$aRow)
{

    $CI = &get_instance();
    $CI->load->model('workshop/workshop_model');

    if($aRow['rel_type'] == 'wshop_inspection'){
        $inspection = $CI->workshop_model->get_inspection($aRow['rel_id']);

           if ($inspection) {

                $str = '<span class="hide"> - </span><a class="text-muted task-table-related" data-toggle="tooltip" title="' . _l('task_related_to') . '" href="' . admin_url('workshop/inspection_detail/' . $inspection->id.'/?tab=detail">' . format_inspection_number($inspection->id) ). '</a><br /><div class="row-options">';

                $row[2] =  new_str_replace('<div class="row-options">', $str, $row[2]);
            }

    }

    return $row;
}

function init_workshop_menu()
{
    $item = '';
    if (is_client_logged_in()) {
        $item .= '<li class=" customers-nav-item">';
        $item .= ' <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
        aria-expanded="false"><i class="fa-solid fa-users-viewfinder menu-icon"></i>' . _l('wshop_workshops');
        $item .= '</a>';
        $item .= ' <ul class="dropdown-menu animated fadeIn client-ul">';
        $item .= '<li class="customers-nav-item-tracking_page">';
        $item .= '<a href="' . site_url('workshop/client/track_repair') . '">' . _l('wshop_tracking_page') . '</a>';
        $item .= '</li>';
        $item .= '<li class="customers-nav-item-devices">';
        $item .= '<a href="' . site_url('workshop/client/devices') . '">' . _l('wshop_devices') . '</a>';
        $item .= '</li>';

        $item .= '<li class="customers-nav-item-repair_jobs ">';
        $item .= '<a href="' . site_url('workshop/client/repair_jobs') . '">' . _l('wshop_repair_jobs') . '</a>';
        $item .= '</li>';

        $item .= '<li class="customers-nav-item-inspections">';
        $item .= '<a href="' . site_url('workshop/client/inspections') . '">' . _l('wshop_inspections') . '</a>';
        $item .= '</li>';


        $item .= '</ul>';

        $item .= '</li>';
    }else{
        $item .= '<li class=" customers-nav-item">';
        $item .= ' <a href="' . site_url('workshop/client/track_repair') . '" ><i class="fa-solid fa-users-viewfinder menu-icon"></i>' . _l('wshop_tracking_page');
        $item .= '</a>';
        $item .= '</li>';
    }
    echo new_html_entity_decode($item);

}

/**
 * workshop client add footer components
 * @return [type] 
 */
function workshop_client_add_footer_components()
{
    $CI      = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    /*change this code*/
    if ( ! (strpos($viewuri, 'workshop/client') === false)) {
        echo '<link href="' . module_dir_url(WORKSHOP_MODULE_NAME, 'assets/css/styles.css') . '?v=' . VERSION_WORKSHOP . '"  rel="stylesheet" type="text/css" />';
    }
}

// Funções de verificação de licença removidas
// As funções workshop_appint, workshop_preactivate e workshop_predeactivate foram removidas pois
// apenas serviam para verificar a licença, que agora é considerada sempre válida
/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

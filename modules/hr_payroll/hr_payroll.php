<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Payroll Hub
Description: Smart Choice construction payroll, attendance, commissions, incentives, insurance, taxes, 1099 payees, job costing, reports, and multilingual payslips.
Version: 1.2.6
Requires at least: 2.3.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/
*/

define('HR_PAYROLL_MODULE_NAME', 'hr_payroll');
define('HR_PAYROLL_MODULE_UPLOAD_FOLDER', module_dir_path(HR_PAYROLL_MODULE_NAME, 'uploads'));
define('HR_PAYROLL_ATTENDANCE_SAMPLE_UPLOAD_FOLDER', module_dir_path(HR_PAYROLL_MODULE_NAME, 'uploads/attendance_sample_file/'));
define('HR_PAYROLL_PAYSLIP_FOLDER', module_dir_path(HR_PAYROLL_MODULE_NAME, 'uploads/payslip/'));

define('HR_PAYROLL_CREATE_PAYSLIP_EXCEL', 'modules/hr_payroll/uploads/payslip_excel_file/');
define('HR_PAYROLL_CREATE_ATTENDANCE_SAMPLE', 'modules/hr_payroll/uploads/attendance_sample_file/');
define('HR_PAYROLL_CREATE_EMPLOYEES_SAMPLE', 'modules/hr_payroll/uploads/employees_sample_file/');
define('HR_PAYROLL_CREATE_COMMISSIONS_SAMPLE', 'modules/hr_payroll/uploads/commissions_sample_file/');
define('HR_PAYROLL_ERROR', 'modules/hr_payroll/uploads/file_error_response/');
define('HR_PAYROLL_PAYSLIP_FILE', 'modules/hr_payroll/uploads/payslip/');
// define('HR_PAYROLL_EXPORT_EMPLOYEE_PAYSLIP', module_dir_path(HR_PAYROLL_MODULE_NAME, 'uploads/export_employee_payslip/'));
define('HR_PAYROLL_EXPORT_EMPLOYEE_PAYSLIP', FCPATH . 'modules/hr_payroll/uploads/export_employee_payslip' . '/');

define('HR_PAYROLL_REVISION', 1260);

//prefix for contract
define('HR_PAYROLL_PREFIX_PROBATIONARY', ' (CT1)');
define('HR_PAYROLL_PREFIX_FORMAL', ' (CT2)');


hooks()->add_action('admin_init', 'hr_payroll_permissions');
hooks()->add_action('app_admin_head', 'hr_payroll_add_head_components');
hooks()->add_action('app_admin_footer', 'hr_payroll_load_js');
hooks()->add_action('admin_init', 'hr_payroll_module_init_menu_items');
hooks()->add_action('admin_init', 'hr_payroll_register_native_settings');
hooks()->add_action('admin_init', 'hr_payroll_register_native_reports_menu');

//hr profile hook
hooks()->add_filter('hr_profile_tab_name', 'hr_payroll_add_tab_name', 10);
hooks()->add_filter('hr_profile_tab_content', 'hr_payroll_add_tab_content', 10);
hooks()->add_action('hr_profile_load_js_file', 'hr_payroll_load_js_file');

register_merge_fields('hr_payroll/merge_fields/hr_payslip_merge_fields');
hooks()->add_filter('other_merge_fields_available_for', 'hrp_pdf_payslip_register_other_merge_fields');
//get currency
hooks()->add_action('after_cron_run', 'hrp_cronjob_currency_rates');
hooks()->add_action('hr_payroll_init',HR_PAYROLL_MODULE_NAME.'_appint');
hooks()->add_action('pre_activate_module', HR_PAYROLL_MODULE_NAME.'_preactivate');
hooks()->add_action('pre_deactivate_module', HR_PAYROLL_MODULE_NAME.'_predeactivate');

/**
* Register activation module hook
*/
register_activation_hook(HR_PAYROLL_MODULE_NAME, 'hr_payroll_module_activation_hook');


/**
 * hr payroll module activation hook
 * @return [type] 
 */
function hr_payroll_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(HR_PAYROLL_MODULE_NAME, [HR_PAYROLL_MODULE_NAME]);


$CI = & get_instance();
$CI->load->helper(HR_PAYROLL_MODULE_NAME . '/hr_payroll');

/**
 * Init hr payroll module menu items in setup in admin_init hook
 * @return null
 */
function hr_payroll_module_init_menu_items()
{
    $CI = &get_instance();

    if(has_permission('hrp_employee','','view') || has_permission('hrp_attendance','','view') || has_permission('hrp_commission','','view') || has_permission('hrp_deduction','','view') || has_permission('hrp_bonus_kpi','','view') || has_permission('hrp_insurrance','','view') || has_permission('hrp_payslip','','view') || has_permission('hrp_payslip_template','','view') || has_permission('hrp_income_tax','','view') || has_permission('hrp_report','','view') || has_permission('hrp_setting','','view') || has_permission('hrp_employee','','view_own') || has_permission('hrp_attendance','','view_own') || has_permission('hrp_commission','','view_own') || has_permission('hrp_deduction','','view_own') || has_permission('hrp_bonus_kpi','','view_own') || has_permission('hrp_insurrance','','view_own') || has_permission('hrp_payslip','','view_own') || has_permission('hrp_payslip_template','','view_own') || has_permission('hrp_income_tax','','view_own')){
       $CI->app_menu->add_sidebar_menu_item('hr_payroll', [
        'name'     => _l('hr_payroll'),
        'icon'     => 'fa fa-users',
        'position' => 30,
    ]);
   }

    if(has_permission('hrp_employee','','view') || has_permission('hrp_employee','','view_own')){
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hr_manage_employees',
            'name'     => _l('hr_manage_employees'),
            'icon'     => 'fa fa-vcard',
            'href'     => admin_url('hr_payroll/manage_employees'),
            'position' => 1,
        ]);
    }

    if(has_permission('hrp_attendance','','view') || has_permission('hrp_attendance','','view_own')){
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hr_manage_attendance',
            'name'     => _l('hr_manage_attendance'),
            'icon'     => 'fa-regular fa-pen-to-square menu-icon',
            'href'     => admin_url('hr_payroll/manage_attendance'),
            'position' => 2,
        ]);
    }

    if(has_permission('hrp_commission','','view') || has_permission('hrp_commission','','view_own')){
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hr_manage_commissions',
            'name'     => _l('hrp_commission_manage'),
            'icon'     => 'fa fa-american-sign-language-interpreting',
            'href'     => admin_url('hr_payroll/manage_commissions'),
            'position' => 3,
        ]);
    }


    if(has_permission('hrp_deduction','','view') || has_permission('hrp_deduction','','view_own')){
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hr_manage_deductions',
            'name'     => _l('hrp_deduction_manage'),
            'icon'     => 'fa fa-cut',
            'href'     => admin_url('hr_payroll/manage_deductions'),
            'position' => 4,
        ]);
    }


    if(has_permission('hrp_bonus_kpi','','view') || has_permission('hrp_bonus_kpi','','view_own')){
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hr_bonus_kpi',
            'name'     => _l('hr_bonus_kpi'),
            'icon'     => 'fa fa-gift',
            'href'     => admin_url('hr_payroll/manage_bonus'),
            'position' => 5,
        ]);
    }

    if(has_permission('hrp_insurrance','','view') || has_permission('hrp_insurrance','','view_own')){
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hrp_insurrance',
            'name'     => _l('hrp_insurrance'),
            'icon'     => 'fa fa-medkit',
            'href'     => admin_url('hr_payroll/manage_insurances'),
            'position' => 6,
        ]);
    }

    if(has_permission('hrp_payslip','','view') || has_permission('hrp_payslip','','view_own')){
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hr_pay_slips',
            'name'     => _l('hr_pay_slips'),
            'icon'     => 'fa-solid fa-money-bill',
            'href'     => admin_url('hr_payroll/payslip_manage'),
            'position' => 7,
        ]);
    }

    if(has_permission('hrp_payslip_template','','view') || has_permission('hrp_payslip_template','','view_own')){
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hrp_payslip_template',
            'name'     => _l('hr_pay_slip_templates'),
            'icon'     => 'fa fa-outdent',
            'href'     => admin_url('hr_payroll/payslip_templates_manage'),
            'position' => 8,
        ]);
    }

    if(has_permission('hrp_income_tax','','view') || has_permission('hrp_income_tax','','view_own')){
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hrp_income_tax',
            'name'     => _l('hrp_income_tax'),
            'icon'     => 'fa fa-calendar-minus',
            'href'     => admin_url('hr_payroll/income_taxs_manage'),
            'position' => 9,
        ]);
    }

    if(has_permission('hrp_report','','view')){
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hr_payroll_reports',
            'name'     => _l('hrp_reports'),
            'icon'     => 'fa fa-list-alt',
            'href'     => admin_url('hr_payroll/reports'),
            'position' => 10,
        ]);
    }

    // Smart Choice: always expose a real Settings destination inside Payroll Hub.
    if (is_admin() || has_permission('hrp_setting', '', 'view') || has_permission('hrp_setting', '', 'edit')) {
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hr_payroll_settings',
            'name'     => _l('setting'),
            'icon'     => 'fa-solid fa-gear',
            'href'     => admin_url('settings?group=payroll_hub'),
            'position' => 11,
        ]);
        $CI->app_menu->add_sidebar_children_item('hr_payroll', [
            'slug'     => 'hr_payroll_settings_direct',
            'name'     => 'Payroll Hub Settings',
            'icon'     => 'fa-solid fa-sliders',
            'href'     => admin_url('hr_payroll/settings'),
            'position' => 12,
        ]);
    }
}

/**
 * hr payroll load js
 * @return library 
 */
function hr_payroll_load_js(){
    $CI = &get_instance();

    $viewuri = $_SERVER['REQUEST_URI'];

    if (!(strpos($viewuri,'admin/hr_payroll') === false)){
        echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/js/deactivate_hotkey.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    }

    if (!(strpos($viewuri, '/admin/hr_payroll/setting') === false) || !(strpos($viewuri, '/admin/hr_payroll/manage_employees') === false) || !(strpos($viewuri, '/admin/hr_payroll/manage_attendance') === false) || !(strpos($viewuri, '/admin/hr_payroll/manage_deductions') === false) || !(strpos($viewuri, '/admin/hr_payroll/manage_commissions') === false) || !(strpos($viewuri, '/admin/hr_payroll/income_taxs_manage') === false) || !(strpos($viewuri, '/admin/hr_payroll/manage_insurances') === false) ) {   
     echo '<script src="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/handsontable/chosen.jquery.js') . '"></script>';
     echo '<script src="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/handsontable/handsontable-chosen-editor.js') . '"></script>';
 }

 if (!(strpos($viewuri,'admin/hr_payroll/view_payslip_templates_detail') === false)){
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/luckysheet.umd_payslip.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';

}

if (!(strpos($viewuri,'admin/hr_payroll/view_payslip_detail') === false)){
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/luckysheet.umd_payslip.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
}



if (!(strpos($viewuri,'admin/hr_payroll/view_payslip_templates_detail') === false) || !(strpos($viewuri,'admin/hr_payroll/view_payslip') === false) ) {

    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/spectrum.min.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/plugin.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/js/manage.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/vue.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/vuex.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/vuexx.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/index.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/echarts.min.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/chartmix.umd.min.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/FileSaver.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script  src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/excel.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script  src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/js/exports.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/js/upload_file.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/luckyexcel.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/js/store.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
}

if(!(strpos($viewuri,'admin/hr_payroll/reports') === false)){

    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/highcharts/highcharts.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/highcharts/exporting.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/highcharts/series-label.js').'?v=' . HR_PAYROLL_REVISION.'"></script>';
}
if(!(strpos($viewuri,'admin/hr_payroll/payslip_manage') === false)){
    echo '<script src="'.module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/daterangepicker/daterangepicker.js').'?v='.HR_PAYROLL_REVISION.'"></script>';
}

}


/**
 * hr payroll add head components
 * @return library 
 */
function hr_payroll_add_head_components(){
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];

    if (!(strpos($viewuri, '/admin/hr_payroll') === false)) { 
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/css/styles.css') . '"  rel="stylesheet" type="text/css" />';
    }

    if (!(strpos($viewuri, '/admin/hr_payroll/setting') === false) || !(strpos($viewuri, '/admin/hr_payroll/manage_employees') === false) || !(strpos($viewuri, '/admin/hr_payroll/manage_attendance') === false) || !(strpos($viewuri, '/admin/hr_payroll/manage_deductions') === false) || !(strpos($viewuri, '/admin/hr_payroll/manage_commissions') === false) || !(strpos($viewuri, '/admin/hr_payroll/income_taxs_manage') === false) || !(strpos($viewuri, '/admin/hr_payroll/manage_insurances') === false) ) { 

        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.css') . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/handsontable/chosen.css') . '"  rel="stylesheet" type="text/css" />';
        echo '<script src="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/handsontable/handsontable.full.min.js') . '"></script>';
    }

    if (!(strpos($viewuri,'admin/hr_payroll/view_payslip_templates_detail') === false) || !(strpos($viewuri,'admin/hr_payroll/view_payslip') === false)  ) {

        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/css/manage.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/iconfont.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/luckysheet.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/plugins.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/pluginsCss.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';

        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/iconCustom.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/luckysheet-cellFormat.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        //not scroll
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/luckysheet-core.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/luckysheet-print.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/luckysheet-protection.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/luckysheet-zoom.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/chartmix.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/spectrum.min.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/luckysheet/css/chartmix.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
    }

    if (!(strpos($viewuri,'admin/hr_payroll/manage_bonus') === false) ) {

    }

    if (!(strpos($viewuri,'admin/hr_payroll/payslip_manage') === false) || !(strpos($viewuri,'admin/hr_payroll/payslip_templates_manage') === false) ) {
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/css/modal_dialog.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/plugins/daterangepicker/css/daterangepicker.css') . '?v=' . HR_PAYROLL_REVISION . '"  rel="stylesheet" type="text/css" />';

    }

    if (!(strpos($viewuri, '/admin/hr_payroll/import_xlsx_attendance') === false) || !(strpos($viewuri, '/admin/hr_payroll/import_xlsx_employees') === false) || !(strpos($viewuri,'admin/hr_payroll/import_xlsx_commissions') === false) || !(strpos($viewuri,'admin/hr_payroll/view_payslip_detail') === false) || !(strpos($viewuri,'admin/hr_payroll/payslip_manage') === false) ) {
       echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/css/box_loading/box_loading.css')  .'?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />'; 
   }

   if (!(strpos($viewuri,'admin/hr_payroll/view_payslip_detail') === false) || !(strpos($viewuri,'admin/hr_payroll/view_payslip_templates_detail') === false) ) {
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/css/luckysheet.css') . '?v=' . HR_PAYROLL_REVISION. '"  rel="stylesheet" type="text/css" />';
    }

}



/**
 * hr payroll permissions
 * @return capabilities 
 */
function hr_payroll_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $dashboard['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        
    ];

    $capabilities_3['capabilities'] = [
        'view_own'   => _l('permission_view_own'),
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $capabilities_4['capabilities'] = [
        'view_own'   => _l('permission_view_own'),
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
    ];

    register_staff_capabilities('hrp_employee', $capabilities_3, _l('hr_payroll_employee'));
    register_staff_capabilities('hrp_attendance', $capabilities_3, _l('hr_payroll_attendance'));
    register_staff_capabilities('hrp_commission', $capabilities_3, _l('hr_payroll_commission'));
    register_staff_capabilities('hrp_deduction', $capabilities_3, _l('hr_payroll_deduction'));
    register_staff_capabilities('hrp_bonus_kpi', $capabilities_3, _l('hr_payroll_bonus_kpi'));
    register_staff_capabilities('hrp_insurrance', $capabilities_3, _l('hr_payroll_insurrance'));
    register_staff_capabilities('hrp_payslip', $capabilities_3, _l('hr_payroll_payslip'));
    register_staff_capabilities('hrp_payslip_template', $capabilities_3, _l('hr_payroll_payslip_template'));
    register_staff_capabilities('hrp_income_tax', $capabilities_4, _l('hr_payroll_income_tax'));
    register_staff_capabilities('hrp_report', $dashboard, _l('hr_payroll_report'));
    register_staff_capabilities('hrp_setting', $capabilities, _l('hr_payroll_setting'));

}


/**
 * hr payroll add tab name
 * @param  [type] $row  
 * @param  [type] $aRow 
 * @return [type]       
 */
function hr_payroll_add_tab_name($tab_names)
{
    $tab_names[] = 'hrp_payslip';
    return $tab_names;
}


/**
 * hr payroll add tab content
 * @param  [type] $tab_content_link 
 * @return [type]                   
 */
function hr_payroll_add_tab_content($tab_content_link)
{
    if(!(strpos($tab_content_link, 'hr_record/includes/hrp_payslip') === false)){
        $tab_content_link = 'hr_payroll/employee_payslip/staff_payslip_tab_content';
    }

    return $tab_content_link;
}


/**
 * hr payroll load js file
 * @param  [type] $group_name 
 * @return [type]             
 */
function hr_payroll_load_js_file($group_name)
{
    echo  require 'modules/hr_payroll/assets/js/employee_payslip/payslip_js.php';

}

/**
 * hrp pdf payslip register other merge fields
 * @param  [type] $for 
 * @return [type]      
 */
function hrp_pdf_payslip_register_other_merge_fields($for)
{
    $for[] = 'hr_payslip';

    return $for;
}

/**
 * hrp cronjob currency rates
 * @param  [type] $manually 
 * @return [type]           
 */
function hrp_cronjob_currency_rates($manually) {
    $CI = &get_instance();
    $CI->load->model('hr_payroll/hr_payroll_model');
    if (date('G') == '16' && get_option('cr_automatically_get_currency_rate') == 1) {
        if(date('Y-m-d') != get_option('cr_date_cronjob_currency_rates')){
            $CI->hr_payroll_model->cronjob_currency_rates($manually);
        }
    }
}

function hr_payroll_appint(){
}

function hr_payroll_preactivate($module_name){
}

function hr_payroll_predeactivate($module_name){
}




/** Smart Choice native settings and reports integration. */
function hr_payroll_register_native_settings()
{
    $CI = &get_instance();
    if (!is_admin() && !has_permission('hrp_setting', '', 'view') && !has_permission('hrp_setting', '', 'edit')) {
        return;
    }

    // Smart Choice CRM 4.x still consumes registered settings sections, but its
    // customized settings navigation may render the section name as plain text.
    // Keep the native registration for direct group routing and pair it with the
    // Settings Bridge loaded below so both rendering paths work.
    $payrollSettingsUrl = admin_url('settings?group=payroll_hub');

    // Smart Choice CRM Settings uses the section metadata to build the left
    // Settings navigation.  Unlike stock Perfex, a module section without an
    // explicit URL may be rendered as plain text.  Supplying href/url here is
    // the same navigation contract used by the working Solar Settings module:
    // click the label -> load ?group=payroll_hub -> render this view in the
    // standard right-side Settings content panel.
    $CI->app->add_settings_section('payroll_hub', [
        'id'       => 'payroll_hub',
        'group'    => 'payroll_hub',
        'slug'     => 'payroll_hub',
        'name'     => _l('payroll_hub_settings'),
        'view'     => 'hr_payroll/payroll_hub_settings',
        'href'     => $payrollSettingsUrl,
        'url'      => $payrollSettingsUrl,
        'position' => 45,
        'icon'     => 'fa-solid fa-money-check-dollar',
    ]);
}

function hr_payroll_register_native_reports_menu()
{
    $CI = &get_instance();
    if (is_admin() || has_permission('hrp_report', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'payroll-hub-reports',
            'name'     => _l('payroll_hub_reports'),
            'href'     => admin_url('hr_payroll/reports'),
            'icon'     => 'fa-solid fa-chart-column',
            'position' => 45,
        ]);
    }
}

function hr_payroll_is_settings_page()
{
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    return strpos($uri, '/admin/settings') !== false;
}

function hr_payroll_is_own_page()
{
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    return strpos($uri, '/admin/hr_payroll') !== false || strpos($uri, 'group=payroll_hub') !== false;
}

hooks()->add_action('app_admin_head', 'hr_payroll_smart_choice_scoped_head');
function hr_payroll_smart_choice_scoped_head()
{
    if (hr_payroll_is_own_page() || hr_payroll_is_settings_page()) {
        echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/css/payroll_hub_v123.css') . '?v=' . HR_PAYROLL_REVISION . '" rel="stylesheet" type="text/css">';
    }

    // Payroll submenu branding must remain scoped to Payroll Hub, but it needs
    // to be available globally so the sidebar is green before a Payroll page opens.
    echo '<link href="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/css/payroll_hub_menu_v123.css') . '?v=' . HR_PAYROLL_REVISION . '" rel="stylesheet" type="text/css">';

    // Smart Choice CRM Settings fix: inline, dependency-free bridge.  The custom
    // Settings shell can render module section names as plain text.  This script
    // runs on every Settings page, finds the visible Payroll Hub label regardless
    // of wrapper markup/icons, makes it keyboard/click actionable, and injects the
    // module settings into the standard right content column.  Keeping this inline
    // avoids failures caused by deferred/external module assets.
    if (hr_payroll_is_settings_page()) {
        $settingsUrl = admin_url('settings?group=payroll_hub');
        $panelUrl = admin_url('hr_payroll/payroll_hub_settings_panel');
        echo '<script>(function(){\n'
            . 'var SETTINGS_URL=' . json_encode($settingsUrl) . ';var PANEL_URL=' . json_encode($panelUrl) . ';\n'
            . 'function norm(v){return (v||\"\").replace(/\\s+/g,\" \").trim().toLowerCase();}\n'
            . 'function isPayrollText(v){v=norm(v);return v===\"payroll hub\"||v===\"payroll hub settings\"||v===\"centro de nómina\"||v===\"centro de nomina\";}\n'
            . 'function payrollNodeFromTarget(t){var n=t;for(var i=0;n&&i<6;i++,n=n.parentElement){if(isPayrollText(n.textContent)){return n;}}return null;}\n'
            . 'function findLabels(){var all=document.querySelectorAll(\"a,button,li,span,div,p\");var out=[];for(var i=0;i<all.length;i++){var e=all[i];if(!e.offsetParent)continue;var txt=norm(e.textContent);if(isPayrollText(txt)){out.push(e);}}return out;}\n'
            . 'function wire(){findLabels().forEach(function(e){var a=e.tagName===\"A\"?e:e.querySelector&&e.querySelector(\"a\");if(a){a.href=SETTINGS_URL;a.setAttribute(\"data-hrp-payroll-settings\",\"1\");a.style.cursor=\"pointer\";a.style.pointerEvents=\"auto\";}else{e.setAttribute(\"data-hrp-payroll-settings\",\"1\");e.setAttribute(\"role\",\"link\");e.setAttribute(\"tabindex\",\"0\");e.style.cursor=\"pointer\";e.style.pointerEvents=\"auto\";}});}\n'
            . 'function rightPanel(){var sels=[\"#settings-group-content\",\".settings-group-content\",\"#settings-content\",\".settings-content\",\".settings-right-panel\",\".settings-right-content\",\".settings-main-content\",\".settings-tab-content\",\"#settings .tab-content\",\".settings-page .tab-content\",\".col-md-9\",\".col-md-10\",\".col-lg-9\",\".col-lg-10\"];for(var i=0;i<sels.length;i++){var xs=document.querySelectorAll(sels[i]);for(var j=0;j<xs.length;j++){var x=xs[j];if(x.offsetParent&&norm(x.textContent)!==\"payroll hub\"){return x;}}}return null;}\n'
            . 'function loadPanel(){var target=rightPanel();if(!target){location.href=SETTINGS_URL;return;}target.innerHTML=\"<div style=\\\"padding:35px;text-align:center\\\"><i class=\\\"fa fa-spinner fa-spin fa-2x\\\"></i><div style=\\\"margin-top:10px\\\">Loading Payroll Hub settings...</div></div>\";fetch(PANEL_URL,{credentials:\"same-origin\"}).then(function(r){if(!r.ok)throw new Error();return r.text();}).then(function(html){target.innerHTML=html;if(history&&history.replaceState)history.replaceState({},\"\",SETTINGS_URL);}).catch(function(){location.href=SETTINGS_URL;});}\n'
            . 'function activate(e){var n=payrollNodeFromTarget(e.target);if(!n)return;var href=(e.target.closest&&e.target.closest(\"a\"));if(n.hasAttribute(\"data-hrp-payroll-settings\")||(href&&href.getAttribute(\"data-hrp-payroll-settings\")==\"1\")||isPayrollText(n.textContent)){e.preventDefault();e.stopPropagation();if(e.stopImmediatePropagation)e.stopImmediatePropagation();loadPanel();}}\n'
            . 'document.addEventListener(\"click\",activate,true);document.addEventListener(\"keydown\",function(e){if((e.key===\"Enter\"||e.key===\" \")&&payrollNodeFromTarget(e.target)){activate(e);}},true);\n'
            . 'function start(){wire();if(location.search.indexOf(\"group=payroll_hub\")!==-1){setTimeout(function(){var existing=document.querySelector(\".hrp-native-settings-panel\");if(!existing)loadPanel();},150);}if(window.MutationObserver){new MutationObserver(wire).observe(document.body,{childList:true,subtree:true});}setTimeout(wire,400);setTimeout(wire,1200);}\n'
            . 'if(document.readyState===\"loading\"){document.addEventListener(\"DOMContentLoaded\",start);}else{start();}\n'
            . '})();</script>';
    }

    // Smart Choice Payroll Hub configurable branding/table/menu settings.
    if (hr_payroll_is_own_page()) {
        $primary = get_option('hrp_ui_primary_color') ?: '#0e6f5b';
        $secondary = get_option('hrp_ui_secondary_color') ?: '#075f8f';
        $thead = get_option('hrp_ui_table_header_bg') ?: '#0e6f5b';
        $theadText = get_option('hrp_ui_table_header_text') ?: '#ffffff';
        $hover = get_option('hrp_ui_table_row_hover') ?: '#eef8f4';
        $border = get_option('hrp_ui_table_border_color') ?: '#d8e2df';
        $font = (int) (get_option('hrp_ui_table_font_size') ?: 13);
        if ($font < 10 || $font > 18) { $font = 13; }
        $compact = get_option('hrp_ui_table_compact') === '1';
        $striped = get_option('hrp_ui_table_striped') === '1';
        $hideSelect = get_option('hrp_ui_hide_select_column') === '1';
        $hideActions = get_option('hrp_ui_hide_actions_column') === '1';
        $pad = $compact ? '5px 7px' : '9px 10px';
        echo '<style id="hrp-configurable-ui">'
            . '.payroll-hub-page .btn-primary,.payroll-hub-settings .btn-primary{background-color:' . html_escape($primary) . '!important;border-color:' . html_escape($primary) . '!important;}'
            . '#side-menu li.hr_payroll a,#side-menu li[data-slug="hr_payroll"]>a{border-left-color:' . html_escape($primary) . '!important;}'
            . 'body [href*="/admin/hr_payroll"]{--hrp-primary:' . html_escape($primary) . ';}'
            . 'body table.dataTable thead th,body .table thead th{background:' . html_escape($thead) . ';color:' . html_escape($theadText) . ';border-color:' . html_escape($border) . ';}'
            . 'body table.dataTable tbody td,body .table tbody td{font-size:' . $font . 'px;padding:' . $pad . ';border-color:' . html_escape($border) . ';}'
            . 'body table.dataTable tbody tr:hover td,body .table tbody tr:hover td{background:' . html_escape($hover) . '!important;}'
            . ($striped ? 'body table.dataTable tbody tr:nth-child(even) td,body .table tbody tr:nth-child(even) td{background:#f8fbfa;}' : '')
            . ($hideSelect ? 'body table.dataTable th:first-child,body table.dataTable td:first-child{display:none!important;}' : '')
            . ($hideActions ? 'body table.dataTable th:last-child,body table.dataTable td:last-child{display:none!important;}' : '')
            . '</style>';

        $menuMap = [
            'hrp_menu_employees' => 'manage_employees',
            'hrp_menu_attendance' => 'manage_attendance',
            'hrp_menu_commissions' => 'manage_commissions',
            'hrp_menu_deductions' => 'manage_deductions',
            'hrp_menu_bonuses' => 'manage_bonus',
            'hrp_menu_insurance' => 'manage_insurances',
            'hrp_menu_payslips' => 'payslip_manage',
            'hrp_menu_templates' => 'payslip_templates_manage',
            'hrp_menu_income_tax' => 'income_taxs_manage',
            'hrp_menu_reports' => '/reports',
        ];
        $hideRules = '';
        foreach ($menuMap as $opt => $segment) {
            if (get_option($opt) === '0') {
                $hideRules .= '#side-menu a[href*="hr_payroll/' . $segment . '"]{display:none!important;}';
            }
        }
        if ($hideRules !== '') { echo '<style id="hrp-configurable-menu">' . $hideRules . '</style>'; }
    }
}

hooks()->add_action('app_admin_footer', 'hr_payroll_smart_choice_scoped_footer');
function hr_payroll_smart_choice_scoped_footer()
{
    if (hr_payroll_is_own_page() || hr_payroll_is_settings_page()) {
        echo '<script src="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/js/payroll_hub_v123.js') . '?v=' . HR_PAYROLL_REVISION . '"></script>';
    }

    if (hr_payroll_is_own_page()) {
        $rows = (int) (get_option('hrp_ui_rows_per_page') ?: 25);
        if ($rows < 5 || $rows > 200) { $rows = 25; }
        echo '<script>(function(){var n=' . $rows . ';function apply(){if(!window.jQuery||!jQuery.fn||!jQuery.fn.dataTable)return;jQuery.tables=jQuery.tables||{};jQuery("table.dataTable").each(function(){try{if(jQuery.fn.dataTable.isDataTable(this)){var api=jQuery(this).DataTable();if(api.page.len()!==n){api.page.len(n).draw(false);}}}catch(e){}});}document.addEventListener("DOMContentLoaded",function(){setTimeout(apply,350);setTimeout(apply,1200);});if(window.jQuery){jQuery(document).on("init.dt",function(e,settings){try{new jQuery.fn.dataTable.Api(settings).page.len(n).draw(false);}catch(ex){}});}})();</script>';
    }

    // CRITICAL: this bridge must load on every /admin/settings page, including
    // the default General Settings screen where Payroll Hub is only plain text.
    if (hr_payroll_is_settings_page()) {
        echo '<script src="' . module_dir_url(HR_PAYROLL_MODULE_NAME, 'assets/js/payroll_hub_settings_bridge_v124.js') . '?v=' . HR_PAYROLL_REVISION . '"></script>';
    }
}


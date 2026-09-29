<?php

defined("BASEPATH") or exit("No direct script access allowed");

/*
Module Name: Company Report
Description: It shows the company's annual activity report collectively for perfex CRM
Author: Halil
Author URI: https://www.fiverr.com/halilaltndg
Version: 1.0.3
*/


define("COMPANY_REPORT_MODULE_NAME", "company_report");

hooks()->add_action('admin_init', 'company_report_module_menu');

// language registered
register_language_files(COMPANY_REPORT_MODULE_NAME, [COMPANY_REPORT_MODULE_NAME]);

/**
 * item menu setting
 *
 * @return void
 */
function company_report_module_menu()
{

    if( has_permission('reports', '', 'view') )
    {

        $CI = & get_instance();

        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug' => 'yearly-activity-reports',
            'name' => _l('yearly_activity_report'),
            'href' => admin_url('company_report/report/year'),
            'position' => 22,
            'badge' => [],
        ]);

        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug' => 'monthly-activity-reports',
            'name' => _l('monthly_activity_report'),
            'href' => admin_url('company_report/report'),
            'position' => 23,
            'badge' => [],
        ]);


    }

}



// Smart Choice Standard Permission Registration
if (!function_exists('company_report_smart_choice_standard_permissions')) {
    function company_report_smart_choice_standard_permissions()
    {
        if (function_exists('register_staff_capabilities')) {
            register_staff_capabilities('company_report', [
                'capabilities' => [
                    'view_own' => _l('permission_view_own'),
                    'view'     => _l('permission_view'),
                    'create'   => _l('permission_create'),
                    'edit'     => _l('permission_edit'),
                    'delete'   => _l('permission_delete'),
                ],
            ], 'Company Report');
        }
    }
}
hooks()->add_action('admin_init', 'company_report_smart_choice_standard_permissions');

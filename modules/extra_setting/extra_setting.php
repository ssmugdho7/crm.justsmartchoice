<?php

defined("BASEPATH") or exit("No direct script access allowed");

/*
Module Name: Perfex Extra Settings
Description: Settings module containing many extra settings to facilitate your use of Perfex CRM.
Author: Halil
Author URI: https://www.fiverr.com/halilaltndg
Version: 1.0.9
*/


define('EXTRA_SETTING_MODULE_NAME', "extra_setting");

$CI = &get_instance();

/**
 * Language
 */
register_language_files(EXTRA_SETTING_MODULE_NAME, [EXTRA_SETTING_MODULE_NAME]);


/**
 * Load the module helper
 */
$CI->load->helper(EXTRA_SETTING_MODULE_NAME . '/extra_setting');





/**
 * @note module permission
 *
 * @return void
 */

hooks()->add_action("admin_init", "extra_setting_module_manage_permission");


function extra_setting_module_manage_permission()
{

    $capabilities = [];

    $capabilities["capabilities"] = [

        "extra_setting"             => _l('es_settings') ,

    ];

    register_staff_capabilities("extra_setting", $capabilities , _l('es_settings') );

}


hooks()->add_action("admin_init", "extra_setting_module_menu_items");

function extra_setting_module_menu_items()
{

    $CI = &get_instance();

    if (staff_can('extra_setting', 'extra_setting' )) {

        $CI->app_menu->add_setup_menu_item('extra_settings', [

            'href'     => admin_url('extra_setting/setting'),

            'name'     => _l('es_settings'),

            'position' => 201,

            'badge'    => [],

        ]);

    }

}


/**
 * Js files include
 */

hooks()->add_action("app_admin_footer", "extra_setting_module_include_footer_static_assets");


function extra_setting_module_include_footer_static_assets()
{

    $es_js_file_version = time();
    $es_default_order = 'asc';


    $extra_setting_sign_option = extra_setting_get_option_value( 'es_add_contract_sign_to_merge_fields' , 0 );
    $es_table_default_order_for_contract = extra_setting_get_option_value( 'es_table_default_order_for_contract' , 0 ) ;
    $es_enable_multi_currency_for_contract = extra_setting_get_option_value( 'es_enable_multi_currency_for_contract' , 0 ) ;

    if ( !empty( $extra_setting_sign_option ) || !empty( $es_table_default_order_for_contract ) || !empty( $es_enable_multi_currency_for_contract ) )
    {

        $es_table_default_order_for_contract_row     = extra_setting_get_option_value( 'es_table_default_order_for_contract_row' , 0 );
        $es_table_default_order_for_contract_order   = extra_setting_get_option_value( 'es_table_default_order_for_contract_order' );

        if ( !in_array( $es_table_default_order_for_contract_order , [ 'asc' , 'desc' ] ) )
            $es_table_default_order_for_contract_order   = $es_default_order;


        echo "
            <script>
                
                var extra_setting_sign_option       = $extra_setting_sign_option;
                var extra_setting_sign_name         = '"._l('es_contract_customer_sign')."';
                var extra_setting_new_line_name     = '"._l('es_new_line')."';
            
                var es_table_default_order_for_contract          = $es_table_default_order_for_contract;
                var es_table_default_order_for_contract_row      = $es_table_default_order_for_contract_row;
                var es_table_default_order_for_contract_order    = '$es_table_default_order_for_contract_order';
                
                var es_enable_multi_currency_for_contract      = $es_enable_multi_currency_for_contract;
            </script>
        
            <script src='" . base_url("modules/".EXTRA_SETTING_MODULE_NAME."/assets/extra_setting_contract.js?v=".$es_js_file_version) ."'></script> ";


    }



    // invoice

    if ( extra_setting_get_option_value( 'es_table_default_order_for_invoice' ) == 1 )
    {

        $es_table_default_order_for_invoice_row     = extra_setting_get_option_value( 'es_table_default_order_for_invoice_row' , 0 );
        $es_table_default_order_for_invoice_order   = extra_setting_get_option_value( 'es_table_default_order_for_invoice_order' );

        if ( !in_array( $es_table_default_order_for_invoice_order , [ 'asc' , 'desc' ] ) )
            $es_table_default_order_for_invoice_order   = $es_default_order;



        echo "<script> 
                
                    var es_table_default_order_for_invoice          = 1;
                    var es_table_default_order_for_invoice_row      = $es_table_default_order_for_invoice_row;
                    var es_table_default_order_for_invoice_order    = '$es_table_default_order_for_invoice_order';
    
            </script> 

            <script src='" . base_url("modules/".EXTRA_SETTING_MODULE_NAME."/assets/extra_setting_invoices.js?v=".$es_js_file_version) ."'></script> ";

    }



    // estimate
    $es_enable_target_currency_for_estimate = extra_setting_get_option_value( 'es_enable_target_currency_for_estimate' , 0 );
    $es_table_default_order_for_estimate = extra_setting_get_option_value( 'es_table_default_order_for_estimate' , 0 ) ;

    if ( !empty( $es_table_default_order_for_estimate ) || !empty( $es_enable_target_currency_for_estimate ) )
    {

        $es_table_default_order_for_estimate_row     = extra_setting_get_option_value( 'es_table_default_order_for_estimate_row' , 0 );
        $es_table_default_order_for_estimate_order   = extra_setting_get_option_value( 'es_table_default_order_for_estimate_order' );

        if ( !in_array( $es_table_default_order_for_estimate_order , [ 'asc' , 'desc' ] ) )
            $es_table_default_order_for_estimate_order   = $es_default_order;



        echo "<script> 
                
                    var es_enable_target_currency_for_estimate          = $es_enable_target_currency_for_estimate;
                    
                    var es_table_default_order_for_estimate          = $es_table_default_order_for_estimate;
                    var es_table_default_order_for_estimate_row      = $es_table_default_order_for_estimate_row;
                    var es_table_default_order_for_estimate_order    = '$es_table_default_order_for_estimate_order';
    
            </script> 

            <script src='" . base_url("modules/".EXTRA_SETTING_MODULE_NAME."/assets/extra_setting_estimates.js?v=".$es_js_file_version) ."'></script> ";

    }



    // proposal
    $es_enable_target_currency_for_proposal = extra_setting_get_option_value( 'es_enable_target_currency_for_proposal' , 0 );
    $es_table_default_order_for_proposal = extra_setting_get_option_value( 'es_table_default_order_for_proposal' , 0 ) ;

    if ( !empty( $es_table_default_order_for_proposal ) || !empty( $es_enable_target_currency_for_proposal )  )
    {

        $es_table_default_order_for_proposal_row     = extra_setting_get_option_value( 'es_table_default_order_for_proposal_row' , 0 );
        $es_table_default_order_for_proposal_order   = extra_setting_get_option_value( 'es_table_default_order_for_proposal_order' );

        if ( !in_array( $es_table_default_order_for_proposal_order , [ 'asc' , 'desc' ] ) )
            $es_table_default_order_for_proposal_order   = $es_default_order;



        echo "<script>
                    var es_enable_target_currency_for_proposal      = $es_enable_target_currency_for_proposal;
                    
                    var es_table_default_order_for_proposal          = $es_table_default_order_for_proposal;
                    var es_table_default_order_for_proposal_row      = $es_table_default_order_for_proposal_row;
                    var es_table_default_order_for_proposal_order    = '$es_table_default_order_for_proposal_order';
    
            </script> 

            <script src='" . base_url("modules/".EXTRA_SETTING_MODULE_NAME."/assets/extra_setting_proposals.js?v=".$es_js_file_version) ."'></script> ";

    }




    // expenses

    if ( extra_setting_get_option_value( 'es_table_default_order_for_expenses' ) == 1 )
    {

        $es_table_default_order_for_expenses_row     = extra_setting_get_option_value( 'es_table_default_order_for_expenses_row' , 0 );
        $es_table_default_order_for_expenses_order   = extra_setting_get_option_value( 'es_table_default_order_for_expenses_order' );

        if ( !in_array( $es_table_default_order_for_expenses_order , [ 'asc' , 'desc' ] ) )
            $es_table_default_order_for_expenses_order   = $es_default_order;



        echo "<script> 
                
                    var es_table_default_order_for_expenses          = 1;
                    var es_table_default_order_for_expenses_row      = $es_table_default_order_for_expenses_row;
                    var es_table_default_order_for_expenses_order    = '$es_table_default_order_for_expenses_order';
    
            </script> 

            <script src='" . base_url("modules/".EXTRA_SETTING_MODULE_NAME."/assets/extra_setting_expenses.js?v=".$es_js_file_version) ."'></script> ";

    }


    // customer

    if ( extra_setting_get_option_value( 'es_table_default_order_for_customer' ) == 1 )
    {

        $es_table_default_order_for_customer_row     = extra_setting_get_option_value( 'es_table_default_order_for_customer_row' , 0 );
        $es_table_default_order_for_customer_order   = extra_setting_get_option_value( 'es_table_default_order_for_customer_order' );

        if ( !in_array( $es_table_default_order_for_customer_order , [ 'asc' , 'desc' ] ) )
            $es_table_default_order_for_customer_order   = $es_default_order;



        echo "<script> 
                
                    var es_table_default_order_for_customer          = 1;
                    var es_table_default_order_for_customer_row      = $es_table_default_order_for_customer_row;
                    var es_table_default_order_for_customer_order    = '$es_table_default_order_for_customer_order';
    
            </script> 

            <script src='" . base_url("modules/".EXTRA_SETTING_MODULE_NAME."/assets/extra_setting_customer.js?v=".$es_js_file_version) ."'></script> ";

    }


    // project

    if ( extra_setting_get_option_value( 'es_table_default_order_for_project' ) == 1 )
    {

        $es_table_default_order_for_project_row     = extra_setting_get_option_value( 'es_table_default_order_for_project_row' , 0 );
        $es_table_default_order_for_project_order   = extra_setting_get_option_value( 'es_table_default_order_for_project_order' );

        if ( !in_array( $es_table_default_order_for_project_order , [ 'asc' , 'desc' ] ) )
            $es_table_default_order_for_project_order   = $es_default_order;



        echo "<script> 
                
                    var es_table_default_order_for_project          = 1;
                    var es_table_default_order_for_project_row      = $es_table_default_order_for_project_row;
                    var es_table_default_order_for_project_order    = '$es_table_default_order_for_project_order';
    
            </script> 

            <script src='" . base_url("modules/".EXTRA_SETTING_MODULE_NAME."/assets/extra_setting_project.js?v=".$es_js_file_version) ."'></script> ";

    }


    // tasks

    if ( extra_setting_get_option_value( 'es_table_default_order_for_tasks' ) == 1 )
    {

        $es_table_default_order_for_tasks_row     = extra_setting_get_option_value( 'es_table_default_order_for_tasks_row' , 0 );
        $es_table_default_order_for_tasks_order   = extra_setting_get_option_value( 'es_table_default_order_for_tasks_order' );

        if ( !in_array( $es_table_default_order_for_tasks_order , [ 'asc' , 'desc' ] ) )
            $es_table_default_order_for_tasks_order   = $es_default_order;



        echo "<script> 
                
                    var es_table_default_order_for_tasks          = 1;
                    var es_table_default_order_for_tasks_row      = $es_table_default_order_for_tasks_row;
                    var es_table_default_order_for_tasks_order    = '$es_table_default_order_for_tasks_order';
    
            </script> 

            <script src='" . base_url("modules/".EXTRA_SETTING_MODULE_NAME."/assets/extra_setting_tasks.js?v=".$es_js_file_version) ."'></script> ";

    }




    // leads
    $es_table_default_order_for_leads = 0;
    $es_table_default_order_for_leads_row = -1;
    $es_table_default_order_for_leads_order = $es_default_order;

    if ( extra_setting_get_option_value( 'es_table_default_order_for_leads' ) == 1 )
    {
        $es_table_default_order_for_leads         = 1;
        $es_table_default_order_for_leads_row     = extra_setting_get_option_value( 'es_table_default_order_for_leads_row' , 0 );
        $es_table_default_order_for_leads_order   = extra_setting_get_option_value( 'es_table_default_order_for_leads_order' );

        if ( !in_array( $es_table_default_order_for_leads_order , [ 'asc' , 'desc' ] ) )
            $es_table_default_order_for_leads_order   = $es_default_order;

    }

    $required_fields = extra_setting_get_option_value( 'es_required_fields_for_leads' );

    if ( !empty( $required_fields ) )
    {

        echo "<script> var es_form_required_lead = ".$required_fields."; </script>";

    }
    else
    {
        echo "<script> var es_form_required_lead = ''; </script>";
    }

    echo "<script> 
                
                var es_table_default_order_for_leads          = $es_table_default_order_for_leads;
                var es_table_default_order_for_leads_row      = $es_table_default_order_for_leads_row;
                var es_table_default_order_for_leads_order    = '$es_table_default_order_for_leads_order';
    
            </script> 

            <script src='" . base_url("modules/".EXTRA_SETTING_MODULE_NAME."/assets/extra_setting_leads.js?v=".$es_js_file_version) ."'></script> ";






    $es_enable_web_whatsapp_link = extra_setting_get_option_value( 'es_enable_web_whatsapp_link' ) ? 1 : 0 ;

    echo "<script>
            
                var es_enable_web_whatsapp_link = $es_enable_web_whatsapp_link;

        </script>";


    echo " <script src='" . base_url("modules/".EXTRA_SETTING_MODULE_NAME."/assets/extra_setting_general.js?v=".$es_js_file_version) ."'></script> ";


}


/**
 * SMS
 */

define("SMS_EXTRA_SETTING_LOGIN_SMS", "es_login_sms");

hooks()->add_filter("sms_triggers", "es_mobil_sms_triggers");

hooks()->add_filter("sms_gateway_available_triggers", "es_mobil_sms_triggers");


function es_mobil_sms_triggers( $triggers )
{

    $option = extra_setting_get_option_value( 'es_enable_contact_login_using_phone' );

    if ( !empty( $option ) )
    {

        $triggers[SMS_EXTRA_SETTING_LOGIN_SMS] = [
            "merge_fields" => [ '{opt_code}' ],
            "label" => "Extra Setting OPT ",
            "info"  => "Code to be sent when OTP is activated",
        ];


    }

    return $triggers;

}


/**
 * All setting hooks include
 */
require_once __DIR__ . '/includes/invoice_settings.php';

require_once __DIR__ . '/includes/estimate_settings.php';

require_once __DIR__ . '/includes/contract_settings.php';

require_once __DIR__ . '/includes/proposal_settings.php';

require_once __DIR__ . '/includes/expense_settings.php';

require_once __DIR__ . '/includes/customer_settings.php';

require_once __DIR__ . '/includes/project_settings.php';

require_once __DIR__ . '/includes/tasks_settings.php';

require_once __DIR__ . '/includes/leads_settings.php';

require_once __DIR__ . '/includes/client_side_settings.php';

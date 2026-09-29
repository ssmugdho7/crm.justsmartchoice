<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Questionnaire
Description: Questionnaire module for sending questions to customers
Version: 2.3.0
Requires at least: 2.3.*
*/

require(__DIR__ . '/vendor/autoload.php');

define('FEEDBACKS_MODULE_NAME', 'feedbacks');

hooks()->add_action('after_cron_run', 'feedbacks_send');
hooks()->add_action('admin_init', 'feedbacks_module_init_menu_items');
hooks()->add_action('admin_init', 'feedbacks_permissions');
hooks()->add_action('after_cron_settings_last_tab', 'feedback_cron_settings_tab');
hooks()->add_action('after_cron_settings_last_tab_content', 'feedback_cron_settings_tab_content');
hooks()->add_action('contact_deleted', 'feedback_contact_deleted_hook', 10, 2);

hooks()->add_filter('numbers_of_features_using_cron_job', 'feedbacks_numbers_of_features_using_cron_job');
hooks()->add_filter('used_cron_features', 'feedbacks_used_cron_features');
hooks()->add_filter('migration_tables_to_replace_old_links', 'feedbacks_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'feedbacks_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'feedbacks_global_search_result_output', 10, 2);


function feedbacks_global_search_result_output($output, $data)
{
    if ($data['type'] == 'feedbacks') {
        $output = '<a href="' . admin_url('feedbacks/feedback/' . $data['result']['feedbackid']) . '">' . $data['result']['subject'] . '</a>';
    }

    return $output;
}

function feedbacks_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('feedbacks', '', 'view')) {
        // feedbacks
        $CI->db->select()
        ->from(db_prefix() . 'feedbacks')
        ->like('subject', $q)
        ->or_like('slug', $q)
        ->or_like('description', $q)
        ->or_like('viewdescription', $q)
        ->limit($limit);

        $CI->db->order_by('subject', 'ASC');

        $result[] = [
            'result'         => $CI->db->get()->result_array(),
            'type'           => 'feedbacks',
            'search_heading' => _l('feedbacks'),
        ];
    }

    return $result;
}

function feedback_contact_deleted_hook($id, $contact)
{
    $CI = &get_instance();
    $CI->db->where('email', $contact->email);
    $CI->db->delete(db_prefix() . 'feedbacksemailsendcron');
    if (is_gdpr()) {
        $CI->db->where('ip', $contact->last_ip);
        $CI->db->delete(db_prefix() . 'feedbackresultsets');
    }
}

function feedback_cron_settings_tab()
{
    get_instance()->load->view('feedbacks/settings_tab');
}

function feedback_cron_settings_tab_content()
{
    get_instance()->load->view('feedbacks/settings_tab_content');
}

function feedbacks_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
        'table' => db_prefix() . 'feedbacks',
        'field' => 'description',
    ];
    $tables[] = [
        'table' => db_prefix() . 'feedbacks',
        'field' => 'viewdescription',
    ];

    return $tables;
}

function feedbacks_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('feedbacks', $capabilities, _l('feedbacks'));
}

function feedbacks_numbers_of_features_using_cron_job($number)
{
    $feature = total_rows(db_prefix() . 'feedbacksemailsendcron');
    $number += $feature;

    return $number;
}

function feedbacks_used_cron_features($features)
{
    $feature = total_rows(db_prefix() . 'feedbacksemailsendcron');
    if ($feature > 0) {
        array_push($features, 'feedbacks');
    }

    return $features;
}

function feedbacks_send($cronManuallyInvoked)
{
    $CI = &get_instance();
    $CI->load->library(FEEDBACKS_MODULE_NAME . '/' . 'feedbacks_module');
    $CI->feedbacks_module->send($cronManuallyInvoked);
}

/**
* Register activation module hook
*/
register_activation_hook(FEEDBACKS_MODULE_NAME, 'feedbacks_module_activation_hook');

function feedbacks_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(FEEDBACKS_MODULE_NAME, [FEEDBACKS_MODULE_NAME]);

/**
* Init feedbacks module menu items in setup in admin_init hook
* @return null
*/
function feedbacks_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
        'name'       => _l('feedback'),
        'permission' => 'feedbacks',
        'url'        => 'feedbacks/feedback',
        'position'   => 69,
        'icon'       => 'fa-regular fa-circle-question',
    ]);

    if (has_permission('feedbacks', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('feedbacks', [
            'slug'     => 'feedbacks',
            'name'     => _l('feedbacks'),
            'href'     => admin_url('feedbacks'),
            'position' => 6,
			'icon'       => 'fa-regular fa-circle-question',
        ]);
    }
}
/**
* Helper function to get text question answers
* @param  integer $questionid
* @param  itneger $feedbackid
* @return array
*/
function feedbacks_get_text_question_answers($questionid, $feedbackid)
{
    $CI = & get_instance();
    $CI->db->select('answer,resultid');
    $CI->db->from(db_prefix() . 'form_results');
    $CI->db->where('questionid', $questionid);
    $CI->db->where('rel_id', $feedbackid);
    $CI->db->where('rel_type', 'feedback');

    return $CI->db->get()->result_array();
}


hooks()->add_action('clients_init', 'feedback_client_module_init_menu_item');

function feedback_client_module_init_menu_item()
{
    $count = '';
	$CI = &get_instance();
	
	
 
     if(is_client_logged_in()) { 
		add_theme_menu_item('feedback', [
				'name'     => 'Questionnaire',
				'href'     => site_url('feedbacks/client/feedback_list'),
				'position' => 4,
			    
			]);
     }
	 
	 
}	

// Smart Choice structural standard assets and permissions.
if (function_exists('hooks')) {
    hooks()->add_action('app_admin_head', 'feedbacks_smart_choice_standard_head');
    hooks()->add_action('app_admin_footer', 'feedbacks_smart_choice_standard_footer');
}
if (!function_exists('feedbacks_smart_choice_standard_head')) {
    function feedbacks_smart_choice_standard_head() {
        echo '<link href="' . module_dir_url('feedbacks', 'assets/css/smart_choice_module_standard.css') . '?v=100" rel="stylesheet" type="text/css" />';
    }
}
if (!function_exists('feedbacks_smart_choice_standard_footer')) {
    function feedbacks_smart_choice_standard_footer() {
        echo '<script src="' . module_dir_url('feedbacks', 'assets/js/smart_choice_module_standard.js') . '?v=100"></script>';
    }
}
if (function_exists('register_staff_capabilities')) {
    register_staff_capabilities('feedbacks', [
        'view_own' => _l('view_own'),
        'view' => _l('view_global'),
        'create' => _l('create'),
        'edit' => _l('edit'),
        'delete' => _l('delete'),
    ], _l('feedbacks'));
}

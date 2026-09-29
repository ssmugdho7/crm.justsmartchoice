<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Opportunity
Description: opportunity module for Perfex CRM will allow you to manage your opportunity and proposals.
Version: 1.0.3
Requires at least: 2.3.*
*/

define('OPPORTUNITY_MODULE', 'opportunity');

$CI = &get_instance();
register_language_files(OPPORTUNITY_MODULE, [OPPORTUNITY_MODULE]);
$CI->load->helper(OPPORTUNITY_MODULE . '/opportunity');
hooks()->add_action('admin_init', 'opportunity_init_menu_items');
register_activation_hook(OPPORTUNITY_MODULE, 'opportunity_activation_hook');
register_deactivation_hook(OPPORTUNITY_MODULE, 'opportunity_deactivation_hook');
register_uninstall_hook(OPPORTUNITY_MODULE, 'opportunity_uninstall_hook');
hooks()->add_action('admin_init', 'opportunity_permissions');

hooks()->add_action('task_modal_rel_type_select', 'opportunity_task_modal_rel_type_select');
hooks()->add_action('task_related_to_select', 'opportunity_related_to_select');
hooks()->add_filter('init_relation_options', 'opportunity_init_relation_options');
hooks()->add_filter('relation_values', 'opportunity_relation_values');
hooks()->add_filter('before_return_relation_data', 'opportunity_relation_data', 10, 4); // old
hooks()->add_filter('get_relation_data', 'opportunity_get_relation_data', 10, 4); // new
hooks()->add_filter('tasks_table_row_data', 'opportunity_add_table_row', 10, 3);

hooks()->add_filter('global_search_result_output', 'opportunity_global_search_result_output', 10, 2);
hooks()->add_filter('global_search_result_query', 'opportunity_global_search_result_query', 10, 3);

hooks()->add_action('after_custom_fields_select_options', 'init_opportunity_custom_fields');


register_merge_fields('opportunity/merge_fields/opportunity_merge_fields');


function opportunity_relation_values($data)
{

    $CI = &get_instance();
    $task_id = $CI->uri->segment(4);
    $rel_type = '';
    $rel_id = '';
    if ($CI->input->get('rel_id') && $CI->input->get('rel_type')) {
        $rel_id = $CI->input->get('rel_id');
        $rel_type = $CI->input->get('rel_type');
    }

    // get id from uri segment
    if ($data['type'] == 'opportunity') {
        if ($task_id != '') {
            $task = $CI->tasks_model->get($task_id);
            $rel_id = $task->rel_id;
            $rel_type = $task->rel_type;
        }
        if ($rel_type == 'opportunity') {
            $CI->db->from(db_prefix() .'opportunity');
            $CI->db->where('id', $rel_id);

            $opportunity = $CI->db->get()->row();
            $data = [
                'id' => $opportunity->id,
                'name' => $opportunity->title,
                'link' => admin_url('opportunity/opportunity/' . $opportunity->id),
                'addedfrom' => get_staff_user_id(),
                'subtext' => '',
                'type' => 'opportunity',
            ];
        }
    }


    return $data;
}

function opportunity_init_relation_options($data)
{
    $CI = &get_instance();
    $type = $CI->input->post('type');
    $rel_id = $CI->input->post('rel_id');
    $q = $CI->input->post('q');
    if ($type == 'opportunity') {
        $CI->db->select('id, title');
        $CI->db->from(db_prefix() .'opportunity');
        $CI->db->where('title LIKE "%' . $q . '%"');
        if ($rel_id != '') {
            $CI->db->where('id != ' . $rel_id);
        }
        $opportunity = $CI->db->get()->result_array();
        $data = [];
        foreach ($opportunity as $opportunity) {
            $data[] = [
                'id' => $opportunity['id'],
                'name' => $opportunity['title'],
                'link' => admin_url('opportunity/opportunity/' . $opportunity['id']),
                'addedfrom' => 0,
                'subtext' => '',
                'type' => 'opportunity',
            ];
        }
    }
    return $data;

}


function opportunity_task_modal_rel_type_select($task)
{
    $type = $task['rel_type'];
    echo ' <option value="opportunity" 
    ' . ($type == 'opportunity' ? 'selected' : '') . '
 >
    ' . _l('opportunity') . '
                    </option>';
}

function opportunity_deactivation_hook()
{
 //   require_once(__DIR__ . '/deactive.php');
}

function opportunity_uninstall_hook()
{
  //  require_once(__DIR__ . '/uninstall.php');
}

function opportunity_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function opportunity_init_menu_items()
{
    /**
     * If the logged in user is administrator, add custom menu in Setup
     */
    $CI = &get_instance();
    $CI->app_menu->add_sidebar_menu_item('opportunity', [
        'name' => _l('opportunity'),
        'position' => 4,
        'icon' => 'fa-solid fa-receipt menu-icon',
        'href' => admin_url('opportunity'),
    ]);
    $CI->app_menu->add_setup_menu_item('opportunity', [
        'collapse' => true,
        'name' => _l('opportunity'),
        'position' => 21,
        'badge' => [],
    ]);
    $CI->app_menu->add_setup_children_item('opportunity', [
        'slug' => 'opportunity-sources',
        'name' => _l('acs_leads_sources_submenu'),
        'href' => admin_url('opportunity/sources'),
        'position' => 5,
        'badge' => [],
    ]);
    $CI->app_menu->add_setup_children_item('opportunity', [
        'slug' => 'opportunity-pipelines',
        'name' => _l('opportunity_pipelines'),
        'href' => admin_url('opportunity/pipelines'),
        'position' => 10,
        'badge' => [],
    ]);
    $CI->app_menu->add_setup_children_item('opportunity', [
        'slug' => 'opportunity-stages',
        'name' => _l('opportunity_stages'),
        'href' => admin_url('opportunity/stages'),
        'position' => 10,
        'badge' => [],
    ]);
    $CI->app_menu->add_setup_children_item('opportunity', [
        'slug' => 'opportunity-settings',
        'name' => _l('opportunity_settings'),
        'href' => admin_url('opportunity/settings'),
        'position' => 10,
        'badge' => [],
    ]);
}

function opportunity_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('opportunity', $capabilities, _l('opportunity'));
}


/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

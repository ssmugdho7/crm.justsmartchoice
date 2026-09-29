<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Performance Center
Description: Goals, KPI tracking, staff performance, and executive progress monitoring for Smart Choice Contractors USA.
Version: 2.5.1
Requires at least: 3.4.*
Author: Smart Choice Contractors USA
Author URI: https://justsmartchoice.com
*/

define('GOALS_MODULE_NAME', 'goals');
define('GOALS_MODULE_VERSION', '2.5.1');

hooks()->add_action('after_cron_run', 'goals_notification');
hooks()->add_action('admin_init', 'goals_module_init_menu_items');
hooks()->add_action('staff_member_deleted', 'goals_staff_member_deleted');
hooks()->add_action('admin_init', 'goals_permissions');
hooks()->add_action('app_admin_head', 'goals_add_scoped_assets');

hooks()->add_filter('migration_tables_to_replace_old_links', 'goals_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'goals_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'goals_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'goals_add_dashboard_widget');

register_activation_hook(GOALS_MODULE_NAME, 'goals_module_activation_hook');
register_deactivation_hook(GOALS_MODULE_NAME, 'goals_module_deactivation_hook');
register_uninstall_hook(GOALS_MODULE_NAME, 'goals_module_uninstall_hook');
register_language_files(GOALS_MODULE_NAME, [GOALS_MODULE_NAME]);

function goals_module_activation_hook()
{
    require_once __DIR__ . '/install.php';
    update_option('goals_smart_choice_enabled', 1);
}

function goals_module_deactivation_hook()
{
    update_option('goals_smart_choice_enabled', 0);
}

function goals_module_uninstall_hook()
{
    // Preserve native goal records. Remove only Smart Choice module options.
    delete_option('goals_smart_choice_enabled');
    delete_option('goals_smart_choice_version');
}

function goals_add_scoped_assets()
{
    echo '<link rel="stylesheet" href="' . module_dir_url(GOALS_MODULE_NAME, 'assets/css/goals-smart-choice.css?v=' . GOALS_MODULE_VERSION) . '">';
}

function goals_add_dashboard_widget($widgets)
{
    $widgets[] = ['path' => 'goals/widgets/progress', 'container' => 'left-8'];
    $widgets[] = ['path' => 'goals/widgets/status', 'container' => 'right-4'];
    $widgets[] = ['path' => 'goals/widgets/overdue', 'container' => 'right-4'];
    $widgets[] = ['path' => 'goals/widgets/department', 'container' => 'left-8'];
    $widgets[] = ['path' => 'goals/widgets/monthly', 'container' => 'left-8'];
    $widgets[] = ['path' => 'goals/widgets/staff', 'container' => 'right-4'];
    return $widgets;
}

function goals_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'goals', ['staff_id' => $data['transfer_data_to']]);
}

function goals_global_search_result_output($output, $data)
{
    if ($data['type'] === 'goals') {
        $output = '<a href="' . admin_url('goals/goal/' . $data['result']['id']) . '">' . html_escape($data['result']['subject']) . '</a>';
    }
    return $output;
}

function goals_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (staff_can('view', 'goals') || staff_can('view_own', 'goals')) {
        $CI->db->select()->from(db_prefix() . 'goals')->group_start()->like('description', $q)->or_like('subject', $q)->group_end();
        if (staff_cant('view', 'goals') && staff_can('view_own', 'goals')) {
            $CI->db->where('staff_id', get_staff_user_id());
        }
        $CI->db->limit($limit)->order_by('subject', 'ASC');
        $result[] = ['result' => $CI->db->get()->result_array(), 'type' => 'goals', 'search_heading' => _l('goals')];
    }
    return $result;
}

function goals_migration_tables_to_replace_old_links($tables)
{
    $tables[] = ['table' => db_prefix() . 'goals', 'field' => 'description'];
    return $tables;
}

function goals_permissions()
{
    register_staff_capabilities('goals', [
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'     => _l('permission_view') . ' (' . _l('permission_global') . ')',
            'create'   => _l('permission_create'),
            'edit'     => _l('permission_edit'),
            'delete'   => _l('permission_delete'),
        ],
    ], _l('goals'));
}

function goals_module_init_menu_items()
{
    $CI = &get_instance();
    $canView = staff_can('view', 'goals') || staff_can('view_own', 'goals');
    if (!$canView) { return; }

    $CI->app->add_quick_actions_link([
        'name' => _l('goal'), 'url' => 'goals/goal', 'permission' => 'goals',
        'position' => 56, 'icon' => 'fa-solid fa-bullseye',
    ]);

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug' => 'goals-tracking', 'name' => _l('goals'),
        'href' => admin_url('goals'), 'position' => 24,
        'icon' => 'fa-solid fa-bullseye',
    ]);
}

function goals_notification()
{
    $CI = &get_instance();
    $CI->load->model('goals/goals_model');
    $goals = $CI->goals_model->get('', true);
    foreach ($goals as $goal) {
        $achievement = $CI->goals_model->calculate_goal_achievement($goal['id']);
        if (!$achievement) { continue; }
        if ($achievement['percent'] >= 100 && date('Y-m-d') >= $goal['end_date']) {
            $goal['notify_when_achieve'] == 1
                ? $CI->goals_model->notify_staff_members($goal['id'], 'success', $achievement)
                : $CI->goals_model->mark_as_notified($goal['id']);
        } elseif ($achievement['percent'] < 100 && date('Y-m-d') > $goal['end_date']) {
            $goal['notify_when_fail'] == 1
                ? $CI->goals_model->notify_staff_members($goal['id'], 'failed', $achievement)
                : $CI->goals_model->mark_as_notified($goal['id']);
        }
    }
}

function get_goal_types()
{
    $types = [
        ['key'=>1,'lang_key'=>'goal_type_total_income','subtext'=>'goal_type_income_subtext','dashboard'=>has_permission('invoices','view')],
        ['key'=>8,'lang_key'=>'goal_type_invoiced_amount','subtext'=>'','dashboard'=>has_permission('invoices','view')],
        ['key'=>2,'lang_key'=>'goal_type_convert_leads','dashboard'=>is_staff_member()],
        ['key'=>3,'lang_key'=>'goal_type_increase_customers_without_leads_conversions','subtext'=>'goal_type_increase_customers_without_leads_conversions_subtext','dashboard'=>has_permission('customers','view')],
        ['key'=>4,'lang_key'=>'goal_type_increase_customers_with_leads_conversions','subtext'=>'goal_type_increase_customers_with_leads_conversions_subtext','dashboard'=>has_permission('customers','view')],
        ['key'=>5,'lang_key'=>'goal_type_make_contracts_by_type_calc_database','subtext'=>'goal_type_make_contracts_by_type_calc_database_subtext','dashboard'=>has_permission('contracts','view')],
        ['key'=>7,'lang_key'=>'goal_type_make_contracts_by_type_calc_date','subtext'=>'goal_type_make_contracts_by_type_calc_date_subtext','dashboard'=>has_permission('contracts','view')],
        ['key'=>6,'lang_key'=>'goal_type_total_estimates_converted','subtext'=>'goal_type_total_estimates_converted_subtext','dashboard'=>has_permission('estimates','view')],
    ];
    return hooks()->apply_filters('get_goal_types', $types);
}

function get_goal_type($key)
{
    foreach (get_goal_types() as $type) { if ((int)$type['key'] === (int)$key) { return $type; } }
    return null;
}

function format_goal_type($key)
{
    $type = get_goal_type($key);
    return $type ? _l($type['lang_key']) : _l('goal_type_unknown');
}

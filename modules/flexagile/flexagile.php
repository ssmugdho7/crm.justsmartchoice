<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Agile Sprint Management Module
Description: Spring planning, tracking and management module for Perfex CRM
Version: 2.3.0
Requires at least: 2.3.*
*/


define('FLEXAGILE_MODULE_NAME', 'flexagile');
define('FLEXAGILE_STATUS_PLANNING', 0);
define('FLEXAGILE_STATUS_ONGOING', 1);
define('FLEXAGILE_STATUS_COMPLETE', 3);
hooks()->add_filter('module_flexagile_action_links', 'module_flexagile_action_links');
hooks()->add_action('admin_init', 'flexagile_module_init_menu_items');
hooks()->add_action('before_task_description_section', 'flexagile_before_task_description_section');
hooks()->add_action('app_admin_footer', 'flexagile_module_admin_footer');
hooks()->add_action('after_add_task',  'flexagile_execute_new_task_actions');
hooks()->add_action('task_deleted',  'flexagile_execute_deleted_task_actions');
/**
* Add additional settings for this module in the module list area
* @param  array $actions current actions
* @return array
*/
function module_flexagile_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('flexagile') . '">' .flexagile_lang('sprint-management') . '</a>';

    return $actions;
}
/**
* Register activation module hook
*/
register_activation_hook(FLEXAGILE_MODULE_NAME, 'flexagile_module_activation_hook');

function flexagile_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(FLEXAGILE_MODULE_NAME, [FLEXAGILE_MODULE_NAME]);

/**
 * Init backup module menu items in setup in admin_init hook
 * @return null
 */
function flexagile_module_init_menu_items(){
    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('flexagile-menu', [
        'name'     => flexagile_lang('sprint-management'), // The name if the item
        'href'     => admin_url('flexagile'), // URL of the item
        'position' => 40, // The menu position, see below for default positions.
        'icon'     => 'fa fa-bolt', // Font awesome icon
    ]);
}

function flexagile_lang($key)
{
    return _l(FLEXAGILE_MODULE_NAME.'_'.$key);
}

function flexagile_status_label($sprint){
    $status = $sprint['status'];
    if($status == FLEXAGILE_STATUS_PLANNING) {
        return '<span class="label label-warning">' . flexagile_lang('planned') . '</span>';
    } elseif($status == FLEXAGILE_STATUS_ONGOING) {
        return '<span class="label label-primary">' . flexagile_lang('active') . '</span>';
    } elseif($status == FLEXAGILE_STATUS_COMPLETE) {
        return '<span class="label label-success">' . flexagile_lang('complete') . '</span>';
    }else{
        return '<span class="label label-default">' . flexagile_lang('unknown') . '</span>';
    }
}

function flexagile_format_human_date($date_time){
    return date("F j, Y, g:i a", strtotime($date_time));
}

function flexagile_format_days($date_time){
    $date = date('Y-m-d', strtotime($date_time));
    $days = date_diff(date_create($date), date_create(date('Y-m-d')))->format('%a');
    if($days == 0){
        return flexagile_lang('today');
    }elseif($days == 1){
        return flexagile_lang('tomorrow');
    }elseif($days == -1){
        return flexagile_lang('yesterday');
    }elseif($days > 1){
        return $days . ' ' . flexagile_lang('days-remaning');
    }elseif($days < -1){
        return abs($days) . ' ' . flexagile_lang('days-ago');
    }
}


//let us hook into project tasks page and show a dropdown of sprints
function flexagile_before_task_description_section($task){
    if(!is_admin()){
        return;
    }
    if($task && $task->rel_type == "project"){
        $project_id = $task->rel_id;
        $CI = &get_instance();
        $CI->load->model('flexagile/Flexagilesprint_model');
        $CI->load->model('flexagile/Flexagiletasks_model');
        $sprints = $CI->Flexagilesprint_model->all(['project_id' => $project_id]); //get all sprints for this project
        $task_sprint = $CI->Flexagiletasks_model->get(['task_id' => $task->id]); //get the sprint for this task if any
        $task_sprint_id = $task_sprint ? $task_sprint['sprint_id'] : 0;
        $active_sprint = null;
        if($task_sprint_id) {
            $active_sprint = $CI->Flexagilesprint_model->get(['id' => $task_sprint_id]);
        }
        //update the sprints name with the status in a bracket
        foreach($sprints as $key => $sprint){
            $sprints[$key]['name'] = $sprint['name'] . ' (' . flexagile_status_label($sprint) . ')';
        }
        $data['sprints'] = $sprints;
        $data['active_sprint'] = $active_sprint;
        $data['project_id'] = (int)$project_id;
        $data['task_id'] = (int)$task->id;
        $data['task_status'] = (int)$task->status;
        echo $CI->load->view('flexagile/partials/project_task_sprint_dropdown', $data, true);
    }
}

//hook into new task creation and add it to active sprint
function flexagile_execute_new_task_actions($task_id){
    //check if the option is enabled
    if(!get_option('flexagile_auto_add_new_task_to_active_sprint')){
        return;
    }
    $CI = &get_instance();
    $CI->load->model('tasks_model');
    $task = $CI->tasks_model->get($task_id);
    //check if the task is a project task
    if($task && $task->rel_type == "project") {
        $project_id = $task->rel_id;
        $CI->load->model('flexagile/Flexagilesprint_model');
        $CI->load->model('flexagile/Flexagiletasks_model');
        $sprints = $CI->Flexagilesprint_model->all(['project_id' => $project_id]); //get all sprints for this project
        //let get the active sprint from the above sprints
        if($sprints){
            foreach($sprints as $sprint){
                if($sprint['status'] == FLEXAGILE_STATUS_ONGOING){
                    //we have found the active sprint, let us add this task to it
                    $CI->Flexagiletasks_model->add([
                        'sprint_id' => $sprint['id'],
                        'task_id' => $task_id,
                        'status' => $task->status,
                        'date_added' => date('Y-m-d H:i:s'),
                        'date_updated' => date('Y-m-d H:i:s')
                    ]);
                    break;
                }
            }
        }
    }
}

//hook when a task is deleted and remove it from the sprint
function flexagile_execute_deleted_task_actions($task_id){
    $CI = &get_instance();
    $CI->load->model('flexagile/Flexagiletasks_model');
    $CI->Flexagiletasks_model->delete($task_id,true);
}

function flexagile_get_base_currency()
{
    $CI = &get_instance();
    $CI->load->model('currencies_model');

    return $CI->currencies_model->get_base_currency();
}

function flexagile_module_admin_footer() { ?>
        <script type="text/javascript" id="flexagile" src="<?php echo module_dir_url(FLEXAGILE_MODULE_NAME, 'assets/js/flexagile.js'); ?>"></script>
<?php }



/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */


// Smart Choice structural standard assets and permissions.
if (function_exists('hooks')) {
    hooks()->add_action('app_admin_head', 'flexagile_smart_choice_standard_head');
    hooks()->add_action('app_admin_footer', 'flexagile_smart_choice_standard_footer');
}
if (!function_exists('flexagile_smart_choice_standard_head')) {
    function flexagile_smart_choice_standard_head() {
        echo '<link href="' . module_dir_url('flexagile', 'assets/css/smart_choice_module_standard.css') . '?v=100" rel="stylesheet" type="text/css" />';
    }
}
if (!function_exists('flexagile_smart_choice_standard_footer')) {
    function flexagile_smart_choice_standard_footer() {
        echo '<script src="' . module_dir_url('flexagile', 'assets/js/smart_choice_module_standard.js') . '?v=100"></script>';
    }
}
if (function_exists('register_staff_capabilities')) {
    register_staff_capabilities('flexagile', [
        'view_own' => _l('view_own'),
        'view' => _l('view_global'),
        'create' => _l('create'),
        'edit' => _l('edit'),
        'delete' => _l('delete'),
    ], _l('flexagile'));
}

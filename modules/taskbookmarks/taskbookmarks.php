<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Task bookmarks
Module URI: https://codecanyon.net/item/tasks-bookmark-module-for-perfex-crm/26413678
Description: Group and bookmark tasks
Version: 1.0.0
Requires at least: 2.3.*
Author: Themesic Interactive
Author URI: https://codecanyon.net/user/themesic/portfolio
*/

define('TASKBOOKMARKS_MODULE', 'taskbookmarks');
require_once __DIR__.'/vendor/autoload.php';
hooks()->add_action('admin_init', 'taskbookmarks_module_init_menu_items');
hooks()->add_action('app_admin_footer', 'taskbookmarks_load_js');
hooks()->add_action('app_admin_head', 'taskbookmarks_add_head_components');
hooks()->add_action('admin_init', 'taskbookmarks_permissions');

hooks()->add_filter('tasks_table_columns', 'taskbookmarks_add_table_column', 10, 2);
hooks()->add_filter('tasks_table_row_data', 'taskbookmarks_add_table_row', 10, 3);

hooks()->add_filter('list_taskbookmarks', 'list_taskbookmarks', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'taskbookmarks_add_dashboard_widget');


/**
* Register activation module hook
*/
register_activation_hook(TASKBOOKMARKS_MODULE, 'taskbookmarks_module_activation_hook');



function taskbookmarks_add_dashboard_widget($widgets)
{
    $widgets[] = [
            'path'      => 'taskbookmarks/taskbookmarks_widget',
            'container' => 'top-12',
        ];

    return $widgets;
}
/**
 * Injects JavaScript
 * @return null
 */
function taskbookmarks_load_js(){
        $CI = &get_instance();
        echo '<script src="'.module_dir_url('taskbookmarks', 'assets/js/taskbookmarks.js').'?v=' . $CI->app_scripts->core_version().'"></script>';

		$loaddepdendencies = $_SERVER['REQUEST_URI'];
		
		if ( strpos($loaddepdendencies,'taskbookmarks') !== false && strpos($loaddepdendencies,'view_task_') == false ) {
		?>
		<script src="<?php echo module_dir_url('menu_setup','assets/jquery-nestable/jquery.nestable.js'); ?>"></script>
		<link href="<?php echo module_dir_url('menu_setup', 'assets/font-awesome-icon-picker/css/fontawesome-iconpicker.min.css'); ?>" rel="stylesheet">
		<script src="<?php echo module_dir_url('menu_setup', 'assets/font-awesome-icon-picker/js/fontawesome-iconpicker.js'); ?>"></script>
		<?php
		}
		
		
}
/**
 * Functions
 */
function taskbookmarks_add_head_components(){
    if (get_option('taskbookmarks_enabled') == '1'){
        $CI = &get_instance();
        echo '<link href="' . module_dir_url('taskbookmarks', 'assets/css/taskbookmarks.css') .'?v=' . $CI->app_scripts->core_version(). '"  rel="stylesheet" type="text/css" />';
    }
}

function list_taskbookmarks($list_taskbookmarks_html, $task_id)
{
    $CI = &get_instance();
    $list_taskbookmarks_html .= '<div class="buttonHolder">';
    $taskbookmarks_id = [];
    $CI->db->where('task_id',$task_id);
    $taskbookmarks = $CI->db->get('tbltaskbookmarks_detail')->result_array();

    $CI->db->where('creator',get_staff_user_id());
    $list_taskbookmarks = $CI->db->get('tbltaskbookmarks')->result_array();
    if($taskbookmarks != false){
        foreach ($taskbookmarks as $value) {
            $taskbookmarks_id[] = $value['taskbookmarks_id'];   
        }
    }
  
    foreach($list_taskbookmarks as $t_bookmarks){ 

        if(in_array($t_bookmarks['id'], $taskbookmarks_id)){ 
            $list_taskbookmarks_html .= '<div class="hi-icon-wrap hi-icon-effect-8 hi-icon-effect-1a tasksinline"><a href="Javascript:void(0);" class="hi-icon fa ' . htmlspecialchars($t_bookmarks['icon']) . '" style="color: '.htmlspecialchars($t_bookmarks['color']).';" onclick="remove_taskbookmarks('.htmlspecialchars($t_bookmarks['id']).','.htmlspecialchars($task_id).')" data-toggle ="tooltip", title="Remove: '.htmlspecialchars($t_bookmarks['name']).'"><i class="fa ' . htmlspecialchars($t_bookmarks['icon']) . '"></i></a></div>';
        }else{
            $list_taskbookmarks_html .= '<div class="hi-icon-wrap hi-icon-effect-8 hi-icon-effect-1a tasksinline"><a href="Javascript:void(0);" class="hi-icon fa ' . htmlspecialchars($t_bookmarks['icon']) . '" onclick="add_taskbookmarks('.htmlspecialchars($t_bookmarks['id']).','.htmlspecialchars($task_id).')" data-toggle ="tooltip", title="Add: '.htmlspecialchars($t_bookmarks['name']).'"><i class="fa ' . htmlspecialchars($t_bookmarks['icon']) . '"></i></a></div>';
        }
    }
    $list_taskbookmarks_html .= '</div>';

    return $list_taskbookmarks_html;
}

function taskbookmarks_module_activation_hook() {
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

function taskbookmarks_add_table_column($table_data) {
    array_push($table_data, _l('taskbookmarks'));
    return $table_data;
}

function taskbookmarks_add_table_row($row ,$aRow) {
    $CI = &get_instance();
    $icon = '';
    $taskbookmarks_id = [];
    $CI->db->where('task_id',$aRow['id']);
    $taskbookmarks = $CI->db->get('tbltaskbookmarks_detail')->result_array();
    if(count($taskbookmarks) > 0){
        foreach ($taskbookmarks as $value) {
            $taskbookmarks_id[] = $value['taskbookmarks_id'];   
        }
    }
    $CI->db->where('creator',get_staff_user_id());
    $list_taskbookmarks = $CI->db->get('tbltaskbookmarks')->result_array();
    foreach($list_taskbookmarks as $t_bookmarks){ 

        if(in_array($t_bookmarks['id'], $taskbookmarks_id)){ 
            $icon .= '<div class="hi-icon-wrap hi-icon-effect-8 hi-icon-effect-1a" style="display: inline-block; margin-top: 10px;"><a href="Javascript:void(0);" class="hi-icon fa ' . htmlspecialchars($t_bookmarks['icon']) . '" style="color: '.htmlspecialchars($t_bookmarks['color']).';" onclick="remove_taskbookmarks('.htmlspecialchars($t_bookmarks['id']).','.htmlspecialchars($aRow['id']).', false)" data-toggle ="tooltip", title="Remove: '.htmlspecialchars($t_bookmarks['name']).'"><i class="fa ' . htmlspecialchars($t_bookmarks['icon']) . '"></i></a></div>';
        }else{
            $icon .= '<div class="hi-icon-wrap hi-icon-effect-8 hi-icon-effect-1a" style="display: inline-block; margin-top: 10px;"><a href="Javascript:void(0);" class="hi-icon fa ' . htmlspecialchars($t_bookmarks['icon']) . '" onclick="add_taskbookmarks('.htmlspecialchars($t_bookmarks['id']).','.htmlspecialchars($aRow['id']).', false)" data-toggle ="tooltip", title="Add: '.htmlspecialchars($t_bookmarks['name']).'"><i class="fa ' . htmlspecialchars($t_bookmarks['icon']) . '"></i></a></div>';
        }
    }
    $row[] = $icon;
    return $row;
}

function taskbookmarks_module_init_menu_items()
{
    if (has_permission('taskbookmarks', '', 'view')) {
        $CI = &get_instance();

        $CI->app_menu->add_sidebar_menu_item('taskbookmarks', [
                'name'     => _l('taskbookmarks'),
                'href'     => admin_url('taskbookmarks'),
                'position' => 31,
                'icon'     => 'fa fa-bookmark menu-icon',
        ]);
    }
}

function taskbookmarks_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view_own'),
    ];

    register_staff_capabilities('taskbookmarks', $capabilities, _l('taskbookmarks'));
	
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(TASKBOOKMARKS_MODULE, [TASKBOOKMARKS_MODULE]);


hooks()->add_action('app_init', TASKBOOKMARKS_MODULE.'_actLib');
function taskbookmarks_actLib()
{

}

hooks()->add_action('pre_activate_module', TASKBOOKMARKS_MODULE.'_sidecheck');
function taskbookmarks_sidecheck($module_name)
{
   
}

hooks()->add_action('pre_deactivate_module', TASKBOOKMARKS_MODULE.'_deregister');
function taskbookmarks_deregister($module_name)
{
}

/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

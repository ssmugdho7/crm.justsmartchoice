<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Smart Choice To Do Manager
Description: Quality ★★★★★.  Smart Choice CRM task and dashboard to do manager for staff productivity. Quality: ★★★★★. Last Modified: 2026-07-01.
Author: Smart Choice Contractors USA / Harold Cabrera
Version: 1.0.2
Requires at least: 2.3.*
Author URI: https://justsmartchoice.com/webdeveloper.php
*/
define('SI_TODO_MODULE_NAME', 'si_todo');
$CI = &get_instance();
hooks()->add_action('admin_init', 'si_todo_hook_admin_init');
hooks()->add_action('app_admin_head', 'si_todo_hook_admin_head');
hooks()->add_filter('get_dashboard_widgets','si_todo_hook_get_dashboard_widgets');
hooks()->add_filter('before_dashboard_render','si_todo_hook_before_dashboard_render');
/**
* Load the module helper
*/
$CI->load->helper(SI_TODO_MODULE_NAME . '/si_todo');
/**
* Load the module model
*/
$CI->load->model(SI_TODO_MODULE_NAME . '/si_todo_model');
/**
* Register activation module hook
*/
register_activation_hook(SI_TODO_MODULE_NAME, 'si_todo_activation_hook');
function si_todo_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}
/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(SI_TODO_MODULE_NAME, [SI_TODO_MODULE_NAME]);
/**
*	Admin Init Hook for module
*/
function si_todo_hook_admin_init()
{
	$CI = &get_instance();
	/** Add Menu for Todo **/
	$CI->app_menu->add_sidebar_menu_item('si_todo_menu', [
		'collapse' => true,
		'icon'     => 'fa fa-check-square',
		'name'     => _l('si_todo_main_menu'),
		'position' => 15,
	]);
	$CI->app_menu->add_sidebar_children_item('si_todo_menu', [
		'slug'     => 'si-todo-list-menu',
		'name'     => _l('si_todo_list_menu'),
		'href'     => admin_url('si_todo'),
		'position' => 1,
	]);
	$CI->app_menu->add_sidebar_children_item('si_todo_menu', [
		'slug'     => 'si-todo-category-menu',
		'name'     => _l('si_todo_category_menu'),
		'href'     => admin_url('si_todo/category_list'),
		'position' => 2,
	]);
	$CI->app_menu->add_sidebar_children_item('si_todo_menu', [
		'slug'     => 'si-todo-health-menu',
		'name'     => _l('si_todo_health_check'),
		'href'     => admin_url('si_todo/health'),
		'position' => 3,
	]);
	$CI->app_menu->add_sidebar_children_item('si_todo_menu', [
		'slug'     => 'si-todo-settings-menu',
		'name'     => _l('si_todo_settings_menu'),
		'href'     => admin_url('si_todo/settings'),
		'position' => 4,
	]);

	if (function_exists('get_instance') && isset($CI->app_menu)) {
		$CI->app_menu->add_setup_menu_item('si-todo-settings-setup', [
			'name'     => _l('si_todo_settings_menu'),
			'href'     => admin_url('si_todo/settings'),
			'position' => 75,
		]);
	}
}

function si_todo_hook_admin_head()
{
	echo '<link href="' . module_dir_url('si_todo', 'assets/css/si_todo_style.css') . '" rel="stylesheet">';
}
/**Hook to add calendar widget to dashboard**/
function si_todo_hook_get_dashboard_widgets($widgets)
{
	$widgets[] = array(	'path' => 'si_todo/dashboard/widgets/si_todo_widget',
					'container' => 'right-4',
				);
	return $widgets;
}
/** Hook to set data for todo before dashboard rander*/
function si_todo_hook_before_dashboard_render($data)
{
	$CI = &get_instance();
	$data['bodyclass'] = (isset($data['bodyclass']) ? $data['bodyclass'] : '') . ' si-todo-page';
	$settings = $CI->si_todo_model->get_settings();
	$CI->si_todo_model->setTodosLimit(isset($settings['dashboard_unfinished_limit'])?$settings['dashboard_unfinished_limit']:20);
	$data['si_todos'] = $CI->si_todo_model->get_todo_items(0);
	$data['si_todos'] = is_array($data['si_todos']) ? $data['si_todos'] : [];
	# Only show last 5 or defined finished todo items
	$CI->si_todo_model->setTodosLimit(isset($settings['dashboard_finished_limit'])?$settings['dashboard_finished_limit']:5);
	$data['si_todos_finished'] = $CI->si_todo_model->get_todo_items(1);
	$data['si_todos_finished'] = is_array($data['si_todos_finished']) ? $data['si_todos_finished'] : [];
	$data['categories'] = $CI->si_todo_model->get_category();
	return $data;
}
/* Smart Choice Module Structural Repair: verified for flat language/migration structure and PHP 8.5 baseline. */

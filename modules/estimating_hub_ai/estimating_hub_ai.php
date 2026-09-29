<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Estimating Hub AI
Description: Estimating Hub AI powered by the Smart Choice Construction Intelligence Engine (SCIE). Creates construction estimate drafts, learns from CRM estimates/invoices/proposals, stores training documents and photos, manages Tampa Bay cost data, imports Home Depot/Homewyse-style pricing records, and provides reporting, health checks, and settings. Quality: ★★★★★.
Version: 1.0.9
Requires at least: 3.4.1
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
*/

define('ESTIMATING_HUB_AI_MODULE_NAME', 'estimating_hub_ai');
define('ESTIMATING_HUB_AI_VERSION', '1.0.9');

register_activation_hook(ESTIMATING_HUB_AI_MODULE_NAME, 'estimating_hub_ai_activation_hook');
register_deactivation_hook(ESTIMATING_HUB_AI_MODULE_NAME, 'estimating_hub_ai_deactivation_hook');
register_uninstall_hook(ESTIMATING_HUB_AI_MODULE_NAME, 'estimating_hub_ai_uninstall_hook');

hooks()->add_action('admin_init', 'estimating_hub_ai_admin_init');
hooks()->add_action('app_admin_head', 'estimating_hub_ai_head_assets');
hooks()->add_action('app_admin_footer', 'estimating_hub_ai_footer_assets');
hooks()->add_filter('module_'.ESTIMATING_HUB_AI_MODULE_NAME.'_action_links', 'estimating_hub_ai_module_action_links');

function estimating_hub_ai_activation_hook(){
    require_once(__DIR__ . '/install.php');
    estimating_hub_ai_install();
}
function estimating_hub_ai_deactivation_hook(){ update_option('estimating_hub_ai_enabled', '0'); }
function estimating_hub_ai_uninstall_hook(){ update_option('estimating_hub_ai_enabled', '0'); }
function estimating_hub_ai_head_assets(){
    echo '<link href="'.module_dir_url(ESTIMATING_HUB_AI_MODULE_NAME, 'assets/css/estimating_hub_ai.css?v='.ESTIMATING_HUB_AI_VERSION).'" rel="stylesheet" type="text/css" />';
}
function estimating_hub_ai_footer_assets(){
    echo '<script src="'.module_dir_url(ESTIMATING_HUB_AI_MODULE_NAME, 'assets/js/estimating_hub_ai.js?v='.ESTIMATING_HUB_AI_VERSION).'"></script>';
}
function estimating_hub_ai_module_action_links($actions){
    $actions[] = '<a href="'.admin_url('estimating_hub_ai/settings').'">Settings</a>';
    $actions[] = '<a href="'.admin_url('estimating_hub_ai/health').'">Health Check</a>';
    return $actions;
}
function estimating_hub_ai_admin_init(){
    $CI = &get_instance();
    register_language_files(ESTIMATING_HUB_AI_MODULE_NAME, [ESTIMATING_HUB_AI_MODULE_NAME]);

    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities('estimating_hub_ai', [
            'capabilities' => [
                'view_own' => 'View Own',
                'view' => 'View Global',
                'create' => 'Create',
                'edit' => 'Edit',
                'delete' => 'Delete',
                'import' => 'Import',
                'export' => 'Export',
                'ai_estimate' => 'AI Estimate',
                'settings' => 'Settings',
                'reports' => 'Reports',
            ]
        ], 'Estimating Hub AI');
    }

    if (is_admin()) {
        $CI->app_menu->add_sidebar_menu_item('estimating-hub-ai', [
            'name' => 'Estimating Hub AI',
            'href' => admin_url('estimating_hub_ai'),
            'icon' => 'fa fa-calculator',
            'position' => 32,
        ]);
        $subs = [
            ['dashboard','fa fa-tachometer-alt', 'Dashboard', 'estimating_hub_ai'],
            ['wizard','fa fa-magic', 'AI Estimate Wizard', 'estimating_hub_ai/wizard'],
            ['cost-database','fa fa-database', 'Cost Database', 'estimating_hub_ai/cost_database'],
            ['camera-estimator','fa fa-camera', 'Camera Estimator', 'estimating_hub_ai/camera'],
            ['training-documents','fa fa-file-alt', 'Training Documents', 'estimating_hub_ai/documents'],
            ['home-depot','fa fa-shopping-cart', 'Home Depot Pro Lists', 'estimating_hub_ai/home_depot'],
            ['material-collector','fa fa-cloud-download-alt', 'Material Price Collector', 'estimating_hub_ai/material_collector'],
            ['reports','fa fa-chart-line', 'Reports', 'estimating_hub_ai/reports'],
            ['search','fa fa-search', 'Search', 'estimating_hub_ai/search'],
            ['settings','fa fa-cog', 'Settings', 'estimating_hub_ai/settings'],
            ['help','fa fa-question-circle', 'Help Guide', 'estimating_hub_ai/help'],
            ['health','fa fa-heartbeat', 'Health Check', 'estimating_hub_ai/health'],
        ];
        foreach($subs as $idx=>$s){
            $CI->app_menu->add_sidebar_children_item('estimating-hub-ai', [
                'slug'=>'estimating-hub-ai-'.$s[0],
                'name'=>$s[2],
                'href'=>admin_url($s[3]),
                'icon'=>$s[1],
                'position'=>$idx+1
            ]);
        }
        if (method_exists($CI->app_menu, 'add_setup_menu_item')) {
            $CI->app_menu->add_setup_menu_item('estimating-hub-ai-settings', [
                'name'=>'Estimating Hub AI Settings',
                'href'=>admin_url('estimating_hub_ai/settings'),
                'position'=>64,
                'icon'=>'fa fa-calculator'
            ]);
        }
        if (function_exists('register_custom_admin_report')) {
            register_custom_admin_report('estimating_hub_ai_reports', 'Estimating Hub AI Reports', admin_url('estimating_hub_ai/reports'));
        }
    }
}

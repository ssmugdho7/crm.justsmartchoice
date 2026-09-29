<?php
/**
 * Module Name: Smart Choice Call Center
 * Description: Advanced IVR, extensions, browser dialer, power dialing, recordings, callbacks, analytics and AI voice integration for Perfex CRM.
 * Version: 1.0.1
 * Requires at least: 3.4.1
 * Author: Smart Choice Contractors USA / Harold Cabrera
 */
defined('BASEPATH') or exit('No direct script access allowed');

define('SCCC_MODULE_NAME', 'smart_choice_call_center');
define('SCCC_VERSION', '1.0.1');

register_language_files(SCCC_MODULE_NAME, [SCCC_MODULE_NAME]);
register_activation_hook(SCCC_MODULE_NAME, 'sccc_activate');
register_deactivation_hook(SCCC_MODULE_NAME, 'sccc_deactivate');
register_uninstall_hook(SCCC_MODULE_NAME, 'sccc_uninstall');
register_cron_task('sccc_cron');
hooks()->add_action('admin_init', 'sccc_admin_init');
hooks()->add_action('app_admin_head', 'sccc_admin_head');
hooks()->add_action('app_admin_footer', 'sccc_admin_footer');

function sccc_activate(){ require_once __DIR__ . '/install.php'; sccc_install(); }
function sccc_deactivate(){}
function sccc_uninstall(){ require_once __DIR__ . '/uninstall.php'; sccc_do_uninstall(); }

function sccc_admin_init(){
    $CI=&get_instance();
    register_staff_capabilities('smart_choice_call_center', [
        'capabilities'=>[
            'view'=>'sccc_perm_view_global','view_own'=>'sccc_perm_view_own','create'=>'sccc_perm_create',
            'edit'=>'sccc_perm_edit','delete'=>'sccc_perm_delete','manage_settings'=>'sccc_perm_manage_settings',
            'manage_campaigns'=>'sccc_perm_manage_campaigns','manage_ivr'=>'sccc_perm_manage_ivr',
            'manage_recordings'=>'sccc_perm_manage_recordings','manage_ai'=>'sccc_perm_manage_ai',
            'manage_extensions'=>'sccc_perm_manage_extensions','import'=>'sccc_perm_import','export'=>'sccc_perm_export',
            'view_reports'=>'sccc_perm_view_reports'
        ]
    ], _l('sccc_permissions_name'));

    if (is_staff_logged_in() && (is_admin() || staff_can('view','smart_choice_call_center') || staff_can('view_own','smart_choice_call_center'))) {
        $CI->app_menu->add_sidebar_menu_item('smart-choice-call-center', ['name'=>_l('sccc_menu'),'icon'=>'fa-solid fa-headset','position'=>22]);
        $items=[
            ['dashboard','sccc_dashboard','fa-solid fa-chart-line'],['dialer','sccc_dialer','fa-solid fa-phone'],
            ['calls','sccc_calls','fa-solid fa-list'],['campaigns','sccc_campaigns','fa-solid fa-bullhorn'],
            ['lists','sccc_call_lists','fa-solid fa-address-book'],['ivr','sccc_ivr_extensions','fa-solid fa-diagram-project'],
            ['greetings','sccc_greetings','fa-solid fa-volume-high'],['callbacks','sccc_callbacks','fa-solid fa-phone-volume'],
            ['recordings','sccc_recordings','fa-solid fa-record-vinyl'],['ai','sccc_ai_voice','fa-solid fa-robot'],
            ['reports','sccc_reports','fa-solid fa-chart-pie']
        ];
        foreach($items as $i=>$it){
            if ($it[0]==='reports' && !is_admin() && !staff_can('view_reports','smart_choice_call_center')) continue;
            $CI->app_menu->add_sidebar_children_item('smart-choice-call-center', ['slug'=>'sccc-'.$it[0],'name'=>_l($it[1]),'href'=>admin_url('smart_choice_call_center/'.$it[0]),'icon'=>$it[2],'position'=>$i+1]);
        }
    }
    if (is_admin() || staff_can('manage_settings','smart_choice_call_center')) {
        $CI->app->add_settings_section_child('general','smart_choice_call_center',[
            'name'=>_l('sccc_settings_title'),'view'=>'smart_choice_call_center/settings/index','icon'=>'fa-solid fa-headset','position'=>2
        ]);
    }
}
function sccc_admin_head(){
    if (!is_staff_logged_in()) return;
    echo '<link rel="stylesheet" href="'.module_dir_url(SCCC_MODULE_NAME,'assets/css/call-center.css?v='.SCCC_VERSION).'">';
    $call = html_escape(get_option('sccc_dialpad_color') ?: '#16a34a');
    $key  = html_escape(get_option('sccc_dialpad_key_color') ?: '#f8fafc');
    $text = html_escape(get_option('sccc_dialpad_text_color') ?: '#0f172a');
    echo '<style>.sccc-app{--sccc-call:'.$call.';--sccc-key:'.$key.';--sccc-text:'.$text.'}</style>';
}
function sccc_admin_footer(){
    if (!is_staff_logged_in()) return;
    if (!(is_admin() || staff_can('view','smart_choice_call_center') || staff_can('view_own','smart_choice_call_center'))) return;
    echo '<script>window.scccTokenUrl='.json_encode(admin_url('smart_choice_call_center/token')).';window.scccBrowserEnabled=true;</script>';
    echo '<script src="https://sdk.twilio.com/js/voice/releases/2.11.0/twilio.min.js"></script>';
    echo '<script src="'.module_dir_url(SCCC_MODULE_NAME,'assets/js/call-center.js?v='.SCCC_VERSION).'"></script>';
}
function sccc_cron(){
    $CI=&get_instance(); $CI->load->model('smart_choice_call_center/Call_center_model','sccc_model');
    $CI->sccc_model->process_due_callbacks();
    $CI->sccc_model->process_active_campaigns(10);
}

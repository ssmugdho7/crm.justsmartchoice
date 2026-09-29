<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Perfex CRM Powerful Chat
Description: Smart Choice CRM messaging, groups, calls, media uploads, project media storage, and AI chatbot integration. Quality: ★★★★★. Last Modified: 2026-07-01.
Version: 2.0.4
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
Requires at least: 3.0.0
*/

define('PR_CHAT_MODULE_NAME', 'prchat');
define('PR_CHAT_MODULE_UPLOAD_FOLDER', module_dir_path(PR_CHAT_MODULE_NAME, 'uploads'));
define('PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER', module_dir_path(PR_CHAT_MODULE_NAME, 'uploads/groups'));
define('PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER', module_dir_path(PR_CHAT_MODULE_NAME, 'uploads/audio'));
define('PR_CHAT_MEDIA_PROJECTS_FOLDER', FCPATH . 'uploads/media/projects');

// Load chat constants
require_once(__DIR__ . '/config/chat_constants.php');

/*
 Defined group chat table names
*/
if (!defined('TABLE_STAFF'))
    define('TABLE_STAFF', db_prefix() . 'staff');
if (!defined('TABLE_CHATMESSAGES'))
    define('TABLE_CHATMESSAGES', db_prefix() . 'chatmessages');
if (!defined('TABLE_CHATSETTINGS'))
    define('TABLE_CHATSETTINGS', db_prefix() . 'chatsettings');
if (!defined('TABLE_CHATGROUPS'))
    define('TABLE_CHATGROUPS', db_prefix() . 'chatgroups');
if (!defined('TABLE_CHATGROUPMEMBERS'))
    define('TABLE_CHATGROUPMEMBERS', db_prefix() . 'chatgroupmembers');
if (!defined('TABLE_CHATGROUPMESSAGES'))
    define('TABLE_CHATGROUPMESSAGES', db_prefix() . 'chatgroupmessages');
if (!defined('TABLE_CHATGROUPSHAREDFILES'))
    define('TABLE_CHATGROUPSHAREDFILES', db_prefix() . 'chatgroupsharedfiles');
if (!defined('TABLE_CHATCLIENTMESSAGES'))
    define('TABLE_CHATCLIENTMESSAGES', db_prefix() . 'chatclientmessages');

$CI = &get_instance();

/**
 * Register the activation chat
 */
register_activation_hook(PR_CHAT_MODULE_NAME, 'prchat_activation_hook');

/**
 * The activation function
 */
function prchat_activation_hook()
{
    require(__DIR__ . '/install.php');
}

/**
 * Register chat language files
 */
register_language_files(PR_CHAT_MODULE_NAME, ['chat']);

/**
 * Register new menu item in sidebar menu
 */
if (staff_can('view', PR_CHAT_MODULE_NAME)) {
    if (get_option('pusher_chat_enabled') == '1') {
        // Messaging menu
        $CI->app_menu->add_sidebar_menu_item('prchat', [
            'name' => 'Messaging',
            'href' => admin_url('prchat/Prchat_Controller/chat_full_view'),
            'icon' => 'fa fa-comment-alt',
            'position' => 2
        ]);

        $CI->app_menu->add_sidebar_children_item('prchat', [
            'slug' => 'widget-conversations',
            'name' => 'Conversations',
            'href' => admin_url('prchat/Prchat_Controller/chat_full_view'),
            'icon' => 'fa fa-comment',
            'position' => 1,
        ]);


        $CI->app_menu->add_sidebar_children_item('prchat', [
            'slug' => 'smart-choice-chat-health',
            'name' => 'Health Check',
            'href' => admin_url('prchat/Prchat_Controller/health_check'),
            'icon' => 'fa fa-heartbeat',
            'position' => 98,
        ]);

        $CI->app_menu->add_sidebar_children_item('prchat', [
            'slug' => 'smart-choice-chat-media-projects',
            'name' => 'Project Media',
            'href' => admin_url('prchat/Prchat_Controller/project_media'),
            'icon' => 'fa fa-folder-open',
            'position' => 99,
        ]);

        // AI Chatbot menu
        if (staff_can('chatbot_support', PR_CHAT_MODULE_NAME) || staff_can('chatbot_manage', PR_CHAT_MODULE_NAME)) {
            $CI->app_menu->add_sidebar_menu_item('prchat-chatbot', [
                'name' => 'AI Chatbot',
                'href' => admin_url('prchat/Chatbot_Admin/live_chat'),
                'icon' => 'fa fa-brain',
                'position' => 3,
                'collapse' => true,
            ]);

            $CI->app_menu->add_sidebar_children_item('prchat-chatbot', [
                'slug' => 'chatbot-support',
                'name' => 'Support',
                'href' => admin_url('prchat/Chatbot_Admin/live_chat'),
                'icon' => 'fa fa-headset',
                'position' => 1,
            ]);

            if (staff_can('chatbot_manage', PR_CHAT_MODULE_NAME)) {
                $CI->app_menu->add_sidebar_children_item('prchat-chatbot', [
                    'slug' => 'chatbot-settings',
                    'name' => 'Settings',
                    'href' => admin_url('prchat/Chatbot_Admin'),
                    'icon' => 'fa fa-cog',
                    'position' => 2,
                ]);

                $CI->app_menu->add_sidebar_children_item('prchat-chatbot', [
                    'slug' => 'chatbot-analytics',
                    'name' => 'Analytics',
                    'href' => admin_url('prchat/Chatbot_Admin/analytics'),
                    'icon' => 'fa fa-bar-chart',
                    'position' => 3,
                ]);
            }
        }
    }
}


/**
 * Hook for assigning staff permissions for chat
 *
 * @return void
 */
hooks()->add_action('admin_init', 'chat_register_staff_permissions');

hooks()->add_action('before_staff_logout', 'prchat_record_staff_logout');
hooks()->add_action('before_cron_run', 'chatbot_auto_close_cron');

function prchat_record_staff_logout($staffId)
{
    update_option('prchat_logout_' . $staffId, time());
}

function chatbot_auto_close_cron()
{
    $CI = &get_instance();
    $CI->load->model('prchat/Chatbot_model');
    $result = $CI->Chatbot_model->auto_close_inactive_conversations();

    if ($result['closed'] > 0) {
        log_activity("[Chat Module - AI Chatbot] Auto-close cron: closed {$result['closed']} conversations across {$result['chatbots_checked']} chatbots", 1);
    }
}

function chat_register_staff_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view' => _l('chat_grant_access_label'),
        'delete' => _l('chat_allow_delete_messages'),
        'chatbot_support' => _l('chat_chatbot_support_access_label'),
        'chatbot_manage' => _l('chat_chatbot_manage_access_label'),
    ];

    $capabilities['help'] = [
        'view' => _l('chat_permission_view_help'),
        'delete' => _l('chat_permission_delete_help'),
        'chatbot_support' => _l('chat_permission_chatbot_support_help'),
        'chatbot_manage' => _l('chat_permission_chatbot_manage_help'),
    ];

    register_staff_capabilities(PR_CHAT_MODULE_NAME, $capabilities, _l('chat_access_label'));
}


/**
 * Load the chat helper
 */
$CI->load->helper(PR_CHAT_MODULE_NAME . '/prchat');
$CI->load->helper(PR_CHAT_MODULE_NAME . '/prchat_turn');

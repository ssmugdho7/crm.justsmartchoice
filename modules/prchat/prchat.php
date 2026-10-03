<?php defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: Perfex CRM Powerful Chat
Description: Smart Choice CRM messaging, groups, calls, media uploads, project media storage, and AI chatbot integration. Quality: ★★★★★. Last Modified: 2026-07-03. Added Smart Choice Template UI controls, admin/client navigation visual repair, table width fixes, dropdown scrollbar cleanup, mobile hamburger color repair, and UI backup/restore settings.
Version: 2.3.2
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/webdeveloper.php
Requires at least: 3.0.0
*/

define('PR_CHAT_MODULE_NAME', 'prchat');
require_once __DIR__ . '/helpers/prchat_permissions_helper.php';
define('PR_CHAT_VERSION', '2.3.2');
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
 * Register PRChat settings using the same native settings-section pattern as
 * the production Appointly module and Perfex CRM 3.4.x.
 */
hooks()->add_action('admin_init', 'prchat_register_settings_section');

function prchat_register_settings_section()
{
    $CI = &get_instance();

    if (!staff_can('view', 'settings')) {
        return;
    }

    if (version_compare(get_app_version(), '3.2.0', '<')) {
        $CI->app->add_settings_section_child('other', 'perfex_chat_settings', [
            'name'     => _l('chat_settings_name'),
            'view'     => 'prchat/perfex_chat_settings',
            'position' => 38,
        ]);
        return;
    }

    $CI->app->add_settings_section('perfex_chat_settings', [
        'title'    => _l('chat_settings_name'),
        'position' => 38,
        'children' => [
            [
                'name'     => _l('chat_settings_name'),
                'view'     => 'prchat/perfex_chat_settings',
                'icon'     => 'fa-solid fa-comments fa-fw fa-lg',
                'position' => 10,
            ],
        ],
    ]);
}


/**
 * Register new menu item in sidebar menu
 */
hooks()->add_action('admin_init', 'prchat_register_admin_menu');

function prchat_register_admin_menu()
{
    $CI = &get_instance();
    if (prchat_staff_can_chat() && get_option('pusher_chat_enabled') == '1') {
        // Messaging menu
        $CI->app_menu->add_sidebar_menu_item('prchat', [
            'name' => 'Messaging Chat',
            'href' => admin_url('prchat/Prchat_Controller/chat_full_view'),
            'icon' => 'fa fa-comment-alt',
            'position' => 2,
            'collapse' => true,
        ]);

        // Parents with children toggle the sidebar; provide an explicit chat destination.
        $CI->app_menu->add_sidebar_children_item('prchat', [
            'slug' => 'prchat-conversations',
            'name' => 'Conversations',
            'href' => admin_url('prchat/Prchat_Controller/chat_full_view'),
            'icon' => 'fa-regular fa-comments',
            'position' => 1,
        ]);

        $CI->app_menu->add_sidebar_children_item('prchat', [
            'slug' => 'prchat-sms-log',
            'name' => 'SMS Log',
            'href' => admin_url('prchat/Prchat_Controller/sms_log'),
            'icon' => 'fa fa-commenting',
            'position' => 90,
        ]);

        if (staff_can('view', 'settings')) {
            $CI->app_menu->add_sidebar_children_item('prchat', [
                'slug' => 'prchat-settings',
                'name' => 'Chat Settings',
                'href' => admin_url('settings?group=perfex_chat_settings'),
                'icon' => 'fa fa-cog',
                'position' => 99,
            ]);
        }
    }

    // Chatbot access is independent of permission to use staff Messaging Chat.
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
    $CI = &get_instance();
    $requirements = $CI->lang->line('chat_access_requirements_help', false);
    if (!$requirements) {
        // Existing module translations may predate this access explanation.
        $english = (static function () {
            $lang = [];
            require __DIR__ . '/language/english/chat_lang.php';
            return $lang;
        })();
        $requirements = $english['chat_access_requirements_help'];
    }

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view_own' => _l('permission_view_own'),
        'view' => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
        'delete_groups' => _l('chat_permission_delete_groups'),
        'chatbot_support' => _l('chat_chatbot_support_access_label'),
        'chatbot_manage' => _l('chat_chatbot_manage_access_label'),
        'ai_assist' => _l('chat_ai_assist_permission'),
    ];
    $capabilities['before'] = '<div class="alert alert-info">' . $requirements . '</div>';
    $capabilities['help'] = [
        'view_own' => $requirements,
        'view' => _l('chat_permission_view_global_help'),
        'create' => _l('chat_permission_create_help'),
        'edit' => _l('chat_permission_edit_help'),
        'delete' => _l('chat_permission_delete_help'),
        'delete_groups' => _l('chat_permission_delete_groups_help'),
        'chatbot_support' => _l('chat_permission_chatbot_support_help'),
        'chatbot_manage' => _l('chat_permission_chatbot_manage_help'),
        'ai_assist' => _l('chat_ai_assist_permission_help'),
    ];
    register_staff_capabilities(PR_CHAT_MODULE_NAME, $capabilities, _l('chat_access_label'));
}


/**
 * Load the chat helper
 */
$CI->load->helper(PR_CHAT_MODULE_NAME . '/prchat');
$CI->load->helper(PR_CHAT_MODULE_NAME . '/prchat_turn');

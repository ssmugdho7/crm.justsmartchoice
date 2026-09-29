<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice AI Video Studio
Description: AI video studio module for Smart Choice Contractors USA. Includes scripts, voices, avatars, custom avatar uploads, video drafts, text overlays, logo watermark options, reports, settings, health, help, permissions, and CRM-ready video library workflows.
Version: 1.0.1
Requires at least: 3.5.0
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
Website: https://justsmartchoice.com
Compatibility: Perfex CRM 3.5.x, PHP 8.5+, MySQL 5.7, Bluehost Shared Hosting
*/

define('SC_AI_VIDEO_STUDIO_MODULE_NAME', 'sc_ai_video_studio');
define('SC_AI_VIDEO_STUDIO_VERSION', '1.0.1');

register_activation_hook(SC_AI_VIDEO_STUDIO_MODULE_NAME, 'sc_ai_video_studio_activation_hook');
register_deactivation_hook(SC_AI_VIDEO_STUDIO_MODULE_NAME, 'sc_ai_video_studio_deactivation_hook');
register_uninstall_hook(SC_AI_VIDEO_STUDIO_MODULE_NAME, 'sc_ai_video_studio_uninstall_hook');

hooks()->add_action('admin_init', 'sc_ai_video_studio_admin_init');
hooks()->add_action('app_admin_head', 'sc_ai_video_studio_add_head_assets');
hooks()->add_action('app_admin_footer', 'sc_ai_video_studio_add_footer_assets');

function sc_ai_video_studio_activation_hook(): void
{
    require_once __DIR__ . '/install.php';
}

function sc_ai_video_studio_deactivation_hook(): void
{
    update_option('sc_ai_video_studio_enabled', '0');
}

function sc_ai_video_studio_uninstall_hook(): void
{
    $CI = &get_instance();
    $CI->load->dbforge();
    $CI->dbforge->drop_table(db_prefix() . 'sc_video_text_layers', true);
    $CI->dbforge->drop_table(db_prefix() . 'sc_video_jobs', true);
    $CI->dbforge->drop_table(db_prefix() . 'sc_video_avatars', true);
    $CI->dbforge->drop_table(db_prefix() . 'sc_video_voices', true);
    delete_option('sc_ai_video_studio_enabled');
    delete_option('sc_ai_video_studio_provider');
    delete_option('sc_ai_video_studio_api_key');
    delete_option('sc_ai_video_studio_logo_enabled');
    delete_option('sc_ai_video_studio_logo_url');
    delete_option('sc_ai_video_studio_default_language');
    delete_option('sc_ai_video_studio_default_resolution');
}

function sc_ai_video_studio_admin_init(): void
{
    $CI = &get_instance();
    require_once __DIR__ . '/helpers/sc_ai_video_studio_helper.php';
    sc_ai_video_studio_ensure_schema();
    $CI->load->language(SC_AI_VIDEO_STUDIO_MODULE_NAME . '/sc_ai_video_studio');

    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities(SC_AI_VIDEO_STUDIO_MODULE_NAME, [
            'capabilities' => [
                'view_own'    => _l('sc_video_permission_view_own'),
                'view_global' => _l('sc_video_permission_view_global'),
                'create'      => _l('sc_video_permission_create'),
                'edit'        => _l('sc_video_permission_edit'),
                'delete'      => _l('sc_video_permission_delete'),
            ],
        ], _l('sc_video_module_name'));
    }

    if (has_permission(SC_AI_VIDEO_STUDIO_MODULE_NAME, '', 'view_own') || has_permission(SC_AI_VIDEO_STUDIO_MODULE_NAME, '', 'view_global')) {
        $CI->app_menu->add_sidebar_menu_item('sc-ai-video-studio', [
            'name'     => _l('sc_video_menu_main'),
            'href'     => admin_url('sc_ai_video_studio'),
            'position' => 36,
            'icon'     => 'fa fa-video-camera',
        ]);
        $items = [
            ['slug' => 'dashboard', 'name' => _l('sc_video_menu_dashboard'), 'href' => admin_url('sc_ai_video_studio')],
            ['slug' => 'videos', 'name' => _l('sc_video_menu_videos'), 'href' => admin_url('sc_ai_video_studio/videos')],
            ['slug' => 'voices', 'name' => _l('sc_video_menu_voices'), 'href' => admin_url('sc_ai_video_studio/voices')],
            ['slug' => 'avatars', 'name' => _l('sc_video_menu_avatars'), 'href' => admin_url('sc_ai_video_studio/avatars')],
            ['slug' => 'reports', 'name' => _l('sc_video_menu_reports'), 'href' => admin_url('sc_ai_video_studio/reports')],
            ['slug' => 'settings', 'name' => _l('sc_video_menu_settings'), 'href' => admin_url('sc_ai_video_studio/settings')],
            ['slug' => 'health', 'name' => _l('sc_video_menu_health'), 'href' => admin_url('sc_ai_video_studio/health')],
            ['slug' => 'help', 'name' => _l('sc_video_menu_help'), 'href' => admin_url('sc_ai_video_studio/help')],
        ];
        foreach ($items as $i => $item) {
            $CI->app_menu->add_sidebar_children_item('sc-ai-video-studio', [
                'slug'     => 'sc-ai-video-studio-' . $item['slug'],
                'name'     => $item['name'],
                'href'     => $item['href'],
                'position' => $i + 1,
            ]);
        }
    }
}

function sc_ai_video_studio_add_head_assets(): void
{
    echo '<link href="' . module_dir_url(SC_AI_VIDEO_STUDIO_MODULE_NAME, 'assets/css/sc_ai_video_studio.css') . '?v=' . SC_AI_VIDEO_STUDIO_VERSION . '" rel="stylesheet" type="text/css">';
}

function sc_ai_video_studio_add_footer_assets(): void
{
    echo '<script src="' . module_dir_url(SC_AI_VIDEO_STUDIO_MODULE_NAME, 'assets/js/sc_ai_video_studio.js') . '?v=' . SC_AI_VIDEO_STUDIO_VERSION . '"></script>';
}

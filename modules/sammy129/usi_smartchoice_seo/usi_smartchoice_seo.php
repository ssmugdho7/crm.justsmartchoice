<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: USI SmartChoice AI CRM
Description: AI CRM control module for Smart Choice Contractors USA. Includes voice command intake, camera/photo estimate intake, AI jobsite estimating records, website SEO operations, reports, settings, health, help, permissions, import/export tools, and Smart Choice UI standards.
Version: 1.2.9
Requires at least: 3.5.0
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
Website: https://justsmartchoice.com
Compatibility: Perfex CRM 3.5.x, PHP 8.5+, MySQL 5.7, Bluehost Shared Hosting
*/

define('USI_SMARTCHOICE_SEO_MODULE_NAME', 'usi_smartchoice_seo');
define('USI_SMARTCHOICE_SEO_VERSION', '1.2.9');

register_activation_hook(USI_SMARTCHOICE_SEO_MODULE_NAME, 'usi_smartchoice_seo_activation_hook');
register_deactivation_hook(USI_SMARTCHOICE_SEO_MODULE_NAME, 'usi_smartchoice_seo_deactivation_hook');
register_uninstall_hook(USI_SMARTCHOICE_SEO_MODULE_NAME, 'usi_smartchoice_seo_uninstall_hook');

hooks()->add_action('admin_init', 'usi_smartchoice_seo_admin_init');
hooks()->add_action('app_admin_head', 'usi_smartchoice_seo_add_head_assets');
hooks()->add_action('app_admin_footer', 'usi_smartchoice_seo_add_footer_assets');

function usi_smartchoice_seo_activation_hook(): void
{
    require_once __DIR__ . '/install.php';
}

function usi_smartchoice_seo_deactivation_hook(): void
{
    update_option('usi_smartchoice_seo_enabled', '0');
}

function usi_smartchoice_seo_uninstall_hook(): void
{
    $CI = &get_instance();
    $CI->load->dbforge();
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_video_text_layers', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_videos', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_avatars', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_voices', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_actions', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_photos', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_estimates', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_commands', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_price_index', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_training', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_seo_pages', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_seo_keywords', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_seo_reports', true);
    delete_option('usi_smartchoice_seo_enabled');
    delete_option('usi_smartchoice_seo_default_status');
    delete_option('usi_smartchoice_seo_primary_domain');
    delete_option('usi_smartchoice_seo_brand_name');
    delete_option('usi_smartchoice_ai_enabled');
    delete_option('usi_smartchoice_ai_voice_enabled');
    delete_option('usi_smartchoice_ai_camera_enabled');
    delete_option('usi_smartchoice_ai_default_estimate_status');
    delete_option('usi_smartchoice_ai_default_currency');
    delete_option('usi_smartchoice_ai_command_mode');
    delete_option('usi_smartchoice_ai_api_provider');
    delete_option('usi_smartchoice_ai_api_key');
    delete_option('usi_smartchoice_ai_voice_continuous');
    delete_option('usi_smartchoice_ai_voice_language');
    delete_option('usi_smartchoice_ai_listen_seconds');
    delete_option('usi_smartchoice_ai_mobile_grid_columns');
    delete_option('usi_smartchoice_ai_video_enabled');
    delete_option('usi_smartchoice_ai_video_provider');
    delete_option('usi_smartchoice_ai_video_api_key');
    delete_option('usi_smartchoice_ai_video_default_voice');
    delete_option('usi_smartchoice_ai_video_logo_enabled');
    delete_option('usi_smartchoice_ai_video_logo_url');
}

function usi_smartchoice_seo_admin_init(): void
{
    $CI = &get_instance();
    require_once __DIR__ . '/helpers/usi_smartchoice_seo_helper.php';
    usi_smartchoice_seo_ensure_schema();
    $CI->load->language(USI_SMARTCHOICE_SEO_MODULE_NAME . '/module', 'english');

    if (function_exists('register_staff_capabilities')) {
        register_staff_capabilities(USI_SMARTCHOICE_SEO_MODULE_NAME, [
            'capabilities' => [
                'view_own'    => _l('usi_smartchoice_seo_permission_view_own'),
                'view_global' => _l('usi_smartchoice_seo_permission_view_global'),
                'create'      => _l('usi_smartchoice_seo_permission_create'),
                'edit'        => _l('usi_smartchoice_seo_permission_edit'),
                'delete'      => _l('usi_smartchoice_seo_permission_delete'),
            ],
        ], _l('usi_smartchoice_seo_module_name'));
    }

    if (has_permission(USI_SMARTCHOICE_SEO_MODULE_NAME, '', 'view_own') || has_permission(USI_SMARTCHOICE_SEO_MODULE_NAME, '', 'view_global')) {
        $CI->app_menu->add_sidebar_menu_item('usi-smartchoice-seo', [
            'name'     => 'Sammy AI',
            'href'     => admin_url('usi_smartchoice_seo/ai_dashboard'),
            'position' => 42,
            'icon'     => 'fa fa-microchip',
        ]);
        $items = [
            ['usi-smartchoice-ai-dashboard', _l('usi_smartchoice_ai_dashboard'), 'usi_smartchoice_seo/ai_dashboard', 1, 'fa fa-dashboard'],
            ['usi-smartchoice-ai-voice', _l('usi_smartchoice_ai_voice_assistant'), 'usi_smartchoice_seo/voice_assistant', 2, 'fa fa-microphone'],
            ['usi-smartchoice-ai-camera', _l('usi_smartchoice_ai_camera_intake'), 'usi_smartchoice_seo/camera_intake', 3, 'fa fa-camera'],
            ['usi-smartchoice-ai-commands', _l('usi_smartchoice_ai_commands'), 'usi_smartchoice_seo/ai_commands', 4, 'fa fa-list-alt'],
            ['usi-smartchoice-ai-estimates', _l('usi_smartchoice_ai_estimates'), 'usi_smartchoice_seo/ai_estimates', 5, 'fa fa-calculator'],
            ['usi-smartchoice-ai-estimate-review', 'Estimate Review', 'usi_smartchoice_seo/estimate_review_queue', 6, 'fa fa-check-square-o'],
            ['usi-smartchoice-ai-customer-packages', 'Customer Packages', 'usi_smartchoice_seo/customer_packages', 6, 'fa fa-folder-open-o'],
            ['usi-smartchoice-ai-field-verification', 'Field Verification', 'usi_smartchoice_seo/field_verifications', 7, 'fa fa-check-circle'],
            ['usi-smartchoice-ai-project-handoff', 'Project Handoff', 'usi_smartchoice_seo/project_handoffs', 8, 'fa fa-tasks'],
            ['usi-smartchoice-ai-pricing-engine', 'Pricing Engine', 'usi_smartchoice_seo/pricing_engine', 6, 'fa fa-database'],
            ['usi-smartchoice-ai-video-studio', _l('usi_smartchoice_ai_video_studio'), 'usi_smartchoice_seo/video_studio', 6, 'fa fa-video-camera'],
            ['usi-smartchoice-ai-avatars', _l('usi_smartchoice_ai_avatars'), 'usi_smartchoice_seo/video_avatars', 7, 'fa fa-user-circle'],
            ['usi-smartchoice-ai-voices', _l('usi_smartchoice_ai_voices'), 'usi_smartchoice_seo/video_voices', 8, 'fa fa-volume-up'],
            ['usi-smartchoice-seo-pages', _l('usi_smartchoice_seo_pages'), 'usi_smartchoice_seo/pages', 6, 'fa fa-file-text-o'],
            ['usi-smartchoice-seo-keywords', _l('usi_smartchoice_seo_keywords'), 'usi_smartchoice_seo/keywords', 10, 'fa fa-search'],
            ['usi-smartchoice-ai-training', _l('usi_smartchoice_ai_training'), 'usi_smartchoice_seo/ai_training', 11, 'fa fa-graduation-cap'],
            ['usi-smartchoice-seo-reports', _l('usi_smartchoice_seo_reports'), 'usi_smartchoice_seo/reports', 12, 'fa fa-bar-chart'],
            ['usi-smartchoice-seo-settings', _l('usi_smartchoice_seo_settings'), 'usi_smartchoice_seo/settings', 13, 'fa fa-cog'],
            ['usi-smartchoice-seo-health', _l('usi_smartchoice_seo_health'), 'usi_smartchoice_seo/health', 14, 'fa fa-heartbeat'],
            ['usi-smartchoice-seo-help', _l('usi_smartchoice_seo_help'), 'usi_smartchoice_seo/help', 15, 'fa fa-question-circle'],
        ];
        foreach ($items as $item) {
            $CI->app_menu->add_sidebar_children_item('usi-smartchoice-seo', [
                'slug' => $item[0], 'name' => $item[1], 'href' => admin_url($item[2]), 'position' => $item[3], 'icon' => $item[4],
            ]);
        }
    }

    if (isset($CI->app) && method_exists($CI->app, 'add_settings_section')) {
        $CI->app->add_settings_section('usi_smartchoice_seo', [
            'name'     => _l('usi_smartchoice_seo_settings'),
            'view'     => 'usi_smartchoice_seo/settings/setup',
            'position' => 55,
            'icon'     => 'fa fa-microchip',
        ]);
    }
}

function usi_smartchoice_seo_add_head_assets(): void
{
    echo '<link href="' . module_dir_url(USI_SMARTCHOICE_SEO_MODULE_NAME, 'assets/css/usi_smartchoice_seo.css') . '?v=' . USI_SMARTCHOICE_SEO_VERSION . '" rel="stylesheet" type="text/css" />';
}

function usi_smartchoice_seo_add_footer_assets(): void
{
    echo '<script src="' . module_dir_url(USI_SMARTCHOICE_SEO_MODULE_NAME, 'assets/js/usi_smartchoice_seo.js') . '?v=' . USI_SMARTCHOICE_SEO_VERSION . '"></script>';
}

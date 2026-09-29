<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Sammy AI
Description: Smart Choice Contractors USA integrated AI CRM command center for voice control, camera intake, estimating, memory, documents, material takeoff, purchasing, scheduling, jobsite operations, closeout, dashboards, workflow, chat, vision, learning, API management, security, and command center operations.
Version: 1.5.6
Requires at least: 3.5.0
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
Website: https://justsmartchoice.com
Compatibility: Perfex CRM 3.5.x, PHP 8.5+, MySQL 5.7, Bluehost Shared Hosting
*/

define('USI_SMARTCHOICE_SEO_MODULE_NAME', 'usi_smartchoice_seo');
define('USI_SMARTCHOICE_SEO_VERSION', '1.5.6');

register_language_files(USI_SMARTCHOICE_SEO_MODULE_NAME, [USI_SMARTCHOICE_SEO_MODULE_NAME]);

register_activation_hook(USI_SMARTCHOICE_SEO_MODULE_NAME, 'usi_smartchoice_seo_activation_hook');
register_deactivation_hook(USI_SMARTCHOICE_SEO_MODULE_NAME, 'usi_smartchoice_seo_deactivation_hook');
register_uninstall_hook(USI_SMARTCHOICE_SEO_MODULE_NAME, 'usi_smartchoice_seo_uninstall_hook');

hooks()->add_action('admin_init', 'usi_smartchoice_seo_admin_init');
hooks()->add_action('app_admin_head', 'usi_smartchoice_seo_add_head_assets');
hooks()->add_action('app_admin_footer', 'usi_smartchoice_seo_add_footer_assets');

function usi_smartchoice_seo_activation_hook(): void
{
    require_once __DIR__ . '/install.php';
    update_option('usi_smartchoice_seo_enabled', '1');
    update_option('usi_smartchoice_ai_enabled', '1');
    update_option('usi_smartchoice_ai_footer_chat_enabled', '1');
}

function usi_smartchoice_seo_deactivation_hook(): void
{
    update_option('usi_smartchoice_seo_enabled', '0');
}

function usi_smartchoice_seo_uninstall_hook(): void
{
    $CI = &get_instance();
    $CI->load->dbforge();
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_core_diagnostics', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_core_search_index', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_core_timeline', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_core_actions', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_core_context', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_command_center_cards', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_security_audit_events', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_security_policies', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_api_health_checks', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_api_keys', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_api_providers', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_learning_assignments', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_learning_lessons', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_learning_courses', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_voice_command_routes', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_voice_transcripts', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_voice_sessions', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_conversation_context', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_conversation_messages', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_conversations', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_workflow_logs', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_workflow_runs', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_workflow_steps', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_workflows', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_automation_queue', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_alerts', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_business_recommendations', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_business_snapshots', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_executive_actions', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_executive_snapshots', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_jobsite_punch_items', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_jobsite_logs', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_schedule_items', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_schedules', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_purchase_order_lines', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_purchase_orders', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_vendor_prices', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_vendors', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_takeoff_lines', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_takeoffs', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_document_chunks', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_documents', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_document_runs', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_video_text_layers', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_videos', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_avatars', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_voices', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_memory_links', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_memory_items', true);
    $CI->dbforge->drop_table(db_prefix() . 'usi_ai_memory_runs', true);
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
    if (function_exists('usi_smartchoice_ai_ensure_core_schema')) { usi_smartchoice_ai_ensure_core_schema(); }
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

    if (is_admin() || has_permission(USI_SMARTCHOICE_SEO_MODULE_NAME, '', 'view_own') || has_permission(USI_SMARTCHOICE_SEO_MODULE_NAME, '', 'view_global')) {
        $CI->app_menu->add_sidebar_menu_item('usi-smartchoice-seo', [
            'name'     => 'Sammy AI',
            'href'     => admin_url('usi_smartchoice_seo/ai_dashboard'),
            'position' => 42,
            'icon'     => 'fa fa-microchip',
        ]);
        $items = [
            ['usi-smartchoice-ai-dashboard', _l('usi_smartchoice_ai_dashboard'), 'usi_smartchoice_seo/ai_dashboard', 1, 'fa fa-dashboard'],
            ['usi-smartchoice-ai-executive-dashboard', 'Executive Dashboard', 'usi_smartchoice_seo/executive_dashboard', 2, 'fa fa-line-chart'],
            ['usi-smartchoice-ai-business-intelligence', 'Business Intelligence', 'usi_smartchoice_seo/business_intelligence', 3, 'fa fa-lightbulb-o'],
            ['usi-smartchoice-ai-automation-center', 'Alerts & Automation', 'usi_smartchoice_seo/automation_center', 4, 'fa fa-bell'],
            ['usi-smartchoice-ai-workflow-engine', 'Workflow Engine', 'usi_smartchoice_seo/workflow_engine', 5, 'fa fa-sitemap'],
            ['usi-smartchoice-ai-chat-engine', 'Conversation & Chat', 'usi_smartchoice_seo/chat_engine', 6, 'fa fa-comments-o'],
            ['usi-smartchoice-ai-voice-orchestrator', 'Voice Orchestrator', 'usi_smartchoice_seo/voice_orchestrator', 7, 'fa fa-podcast'],
            ['usi-smartchoice-ai-vision-engine', 'Vision Engine', 'usi_smartchoice_seo/vision', 8, 'fa fa-eye'],
            ['usi-smartchoice-ai-advanced-vision', 'Advanced Vision Intelligence', 'usi_smartchoice_seo/advanced_vision', 8, 'fa fa-camera-retro'],
            ['usi-smartchoice-ai-multi-agent', 'Multi-Agent AI', 'usi_smartchoice_seo/multi_agent_ai', 9, 'fa fa-users'],
            ['usi-smartchoice-ai-learning-center', 'Learning & Training Center', 'usi_smartchoice_seo/learning_center', 9, 'fa fa-graduation-cap'],
            ['usi-smartchoice-ai-api-manager', 'API Manager', 'usi_smartchoice_seo/api_manager', 10, 'fa fa-plug'],
            ['usi-smartchoice-ai-security-audit', 'Security & Audit Center', 'usi_smartchoice_seo/security_audit', 11, 'fa fa-lock'],
            ['usi-smartchoice-ai-command-center', 'Sammy AI Command Center', 'usi_smartchoice_seo/command_center', 12, 'fa fa-th-large'],
            ['usi-smartchoice-ai-production-core', 'Production AI Core', 'usi_smartchoice_seo/ai_core', 13, 'fa fa-cogs'],
            ['usi-smartchoice-ai-intelligence-engine', 'AI Intelligence Engine', 'usi_smartchoice_seo/intelligence_engine', 14, 'fa fa-bolt'],
            ['usi-smartchoice-ai-voice', _l('usi_smartchoice_ai_voice_assistant'), 'usi_smartchoice_seo/voice_assistant', 2, 'fa fa-microphone'],
            ['usi-smartchoice-ai-camera', _l('usi_smartchoice_ai_camera_intake'), 'usi_smartchoice_seo/camera_intake', 3, 'fa fa-camera'],
            ['usi-smartchoice-ai-commands', _l('usi_smartchoice_ai_commands'), 'usi_smartchoice_seo/ai_commands', 4, 'fa fa-list-alt'],
            ['usi-smartchoice-ai-estimates', _l('usi_smartchoice_ai_estimates'), 'usi_smartchoice_seo/ai_estimates', 5, 'fa fa-calculator'],
            ['usi-smartchoice-ai-estimate-review', 'Estimate Review', 'usi_smartchoice_seo/estimate_review_queue', 6, 'fa fa-check-square-o'],
            ['usi-smartchoice-ai-customer-packages', 'Customer Packages', 'usi_smartchoice_seo/customer_packages', 6, 'fa fa-folder-open-o'],
            ['usi-smartchoice-ai-field-verification', 'Field Verification', 'usi_smartchoice_seo/field_verifications', 7, 'fa fa-check-circle'],
            ['usi-smartchoice-ai-project-handoff', 'Project Handoff', 'usi_smartchoice_seo/project_handoffs', 8, 'fa fa-tasks'],
            ['usi-smartchoice-ai-communications', 'Customer Communications', 'usi_smartchoice_seo/communications', 9, 'fa fa-comments'],
            ['usi-smartchoice-ai-memory-engine', 'AI Memory Engine', 'usi_smartchoice_seo/memory_engine', 10, 'fa fa-brain'],
            ['usi-smartchoice-ai-document-intelligence', 'Document Intelligence', 'usi_smartchoice_seo/document_intelligence', 11, 'fa fa-file-pdf-o'],
            ['usi-smartchoice-ai-material-takeoff', 'Material Takeoff', 'usi_smartchoice_seo/material_takeoffs', 12, 'fa fa-cubes'],
            ['usi-smartchoice-ai-purchasing', 'AI Purchasing', 'usi_smartchoice_seo/purchasing', 13, 'fa fa-shopping-cart'],
            ['usi-smartchoice-ai-scheduling', 'AI Scheduling', 'usi_smartchoice_seo/scheduling', 14, 'fa fa-calendar'],
            ['usi-smartchoice-ai-jobsite-assistant', 'Jobsite Assistant', 'usi_smartchoice_seo/jobsite_assistant', 15, 'fa fa-clipboard'],
            ['usi-smartchoice-ai-closeout-warranty', 'Closeout & Warranty', 'usi_smartchoice_seo/closeout_warranty', 16, 'fa fa-shield'],
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

    if (get_option('usi_smartchoice_seo_enabled') !== '1' || get_option('usi_smartchoice_ai_footer_chat_enabled') === '0') {
        return;
    }
    if (!(is_admin() || has_permission(USI_SMARTCHOICE_SEO_MODULE_NAME, '', 'view_own') || has_permission(USI_SMARTCHOICE_SEO_MODULE_NAME, '', 'view_global'))) {
        return;
    }

    $CI = &get_instance();
    $csrfName = $CI->security->get_csrf_token_name();
    $csrfHash = $CI->security->get_csrf_hash();
    echo '<div id="sammyAiFloatingAssistant" class="sammy-ai-float" data-command-url="' . admin_url('usi_smartchoice_seo/footer_chat_command') . '" data-chat-url="' . admin_url('usi_smartchoice_seo/chat_engine') . '" data-csrf-name="' . html_escape($csrfName) . '" data-csrf-hash="' . html_escape($csrfHash) . '">';
    echo '<button type="button" class="sammy-ai-float-toggle" aria-label="Open Sammy AI"><i class="fa fa-comments"></i></button>';
    echo '<section class="sammy-ai-float-panel" aria-hidden="true">';
    echo '<header><div class="sammy-ai-avatar"><i class="fa fa-user-circle"></i></div><div><strong>Sammy AI</strong><small>CRM Assistant</small></div><button type="button" class="sammy-ai-float-close" aria-label="Close">&times;</button></header>';
    echo '<div class="sammy-ai-float-messages"><div class="sammy-ai-message assistant">Ask a CRM question or enter a command.</div></div>';
    echo '<div class="sammy-ai-quick-actions"><button type="button" data-command="Show my open tasks">My Tasks</button><button type="button" data-command="Find overdue invoices">Overdue Invoices</button><button type="button" data-command="Open AI estimate">AI Estimate</button></div>';
    echo '<div class="sammy-ai-float-input"><textarea rows="2" placeholder="Type or speak a command..."></textarea><button type="button" class="sammy-ai-mic" title="Voice input"><i class="fa fa-microphone"></i></button><button type="button" class="sammy-ai-send" title="Send"><i class="fa fa-paper-plane"></i></button></div>';
    echo '<footer><a href="' . admin_url('usi_smartchoice_seo/chat_engine') . '">Open Message Center</a></footer>';
    echo '</section></div>';
}

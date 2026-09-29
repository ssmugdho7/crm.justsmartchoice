<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Social Lead Funnel
Description: Imports and organizes social media lead opportunities from approved sources such as Facebook Page/Lead Ads, Nextdoor keyword captures, manual posts, and future webhooks. Creates CRM leads, tasks, notifications, and AI reply drafts for staff review.
Version: 1.0.1
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
*/

define('SOCIAL_LEAD_FUNNEL_MODULE_NAME', 'social_lead_funnel');
define('SOCIAL_LEAD_FUNNEL_VERSION', '1.0.1');

hooks()->add_action('admin_init', 'social_lead_funnel_admin_init');
hooks()->add_action('admin_init', 'social_lead_funnel_register_permissions');
hooks()->add_action('app_admin_head', 'social_lead_funnel_load_head_assets');
hooks()->add_action('app_admin_footer', 'social_lead_funnel_load_footer_assets');

register_activation_hook(SOCIAL_LEAD_FUNNEL_MODULE_NAME, 'social_lead_funnel_activation_hook');
register_deactivation_hook(SOCIAL_LEAD_FUNNEL_MODULE_NAME, 'social_lead_funnel_deactivation_hook');
register_language_files(SOCIAL_LEAD_FUNNEL_MODULE_NAME, [SOCIAL_LEAD_FUNNEL_MODULE_NAME]);

function social_lead_funnel_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function social_lead_funnel_deactivation_hook()
{
    // Preserve all data. No destructive cleanup on deactivation.
}

function social_lead_funnel_register_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'     => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
        'settings' => _l('settings'),
    ];
    register_staff_capabilities('social_lead_funnel', $capabilities, _l('social_lead_funnel'));
}

function social_lead_funnel_admin_init()
{
    $CI = &get_instance();

    if (has_permission('social_lead_funnel', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('social-lead-funnel', [
            'name'     => _l('social_lead_funnel'),
            'href'     => admin_url('social_lead_funnel'),
            'position' => 59,
            'icon'     => 'fa fa-bullhorn',
        ]);
    }

    if (has_permission('social_lead_funnel', '', 'settings')) {
        $CI->app->add_settings_section('social_lead_funnel', [
            'name'     => _l('social_lead_funnel_settings'),
            'view'     => 'social_lead_funnel/settings/general',
            'position' => 76,
        ]);
    }
}

function social_lead_funnel_load_head_assets()
{
    echo '<link href="' . module_dir_url(SOCIAL_LEAD_FUNNEL_MODULE_NAME, 'assets/css/social_lead_funnel.css') . '?v=' . SOCIAL_LEAD_FUNNEL_VERSION . '" rel="stylesheet" type="text/css" />';
}

function social_lead_funnel_load_footer_assets()
{
    echo '<script src="' . module_dir_url(SOCIAL_LEAD_FUNNEL_MODULE_NAME, 'assets/js/social_lead_funnel.js') . '?v=' . SOCIAL_LEAD_FUNNEL_VERSION . '"></script>';
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Google Analytics
Description: GA4 tracking, website profiles, CRM dashboards, realtime reports, diagnostics, and controlled event measurement without modifying core tables or global layouts.
Version: 2.0.2
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com/
*/

define('GOOGLE_ANALYTICS_MODULE_NAME', 'google_analytics');
define('GOOGLE_ANALYTICS_MODULE_VERSION', '2.0.2');

register_activation_hook(GOOGLE_ANALYTICS_MODULE_NAME, 'google_analytics_activation_hook');
register_deactivation_hook(GOOGLE_ANALYTICS_MODULE_NAME, 'google_analytics_deactivation_hook');
register_language_files(GOOGLE_ANALYTICS_MODULE_NAME, [GOOGLE_ANALYTICS_MODULE_NAME]);

function google_analytics_activation_hook()
{
    require_once __DIR__ . '/install.php';
}

function google_analytics_deactivation_hook()
{
    // Deliberately non-destructive. Settings and reports remain available after reactivation.
}

hooks()->add_action('admin_init', 'google_analytics_register_permissions');
hooks()->add_action('admin_init', 'google_analytics_register_menu_items');
hooks()->add_action('app_admin_head', 'google_analytics_load_admin_assets');
hooks()->add_action('app_admin_footer', 'google_analytics_inject_admin_tracking');
hooks()->add_action('app_customers_footer', 'google_analytics_inject_client_tracking');
hooks()->add_filter('module_google_analytics_action_links', 'google_analytics_action_links');

function google_analytics_register_permissions()
{
    $capabilities = [
        'view_own' => _l('permission_view_own'),
        'view'     => _l('permission_view') . ' (' . _l('permission_global') . ')',
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ];

    register_staff_capabilities('google_analytics', $capabilities, _l('google_analytics'));
}

function google_analytics_register_menu_items()
{
    if (!has_permission('google_analytics', '', 'view') && !is_admin()) {
        return;
    }

    $CI = &get_instance();
    $CI->app_menu->add_sidebar_menu_item('google-analytics', [
        'name'     => _l('google_analytics'),
        'icon'     => 'fa fa-line-chart',
        'position' => 55,
    ]);
    $CI->app_menu->add_sidebar_children_item('google-analytics', [
        'slug'     => 'google-analytics-dashboard',
        'name'     => _l('ga_dashboard'),
        'href'     => admin_url('google_analytics'),
        'icon'     => 'fa fa-dashboard',
        'position' => 1,
    ]);
    $CI->app_menu->add_sidebar_children_item('google-analytics', [
        'slug'     => 'google-analytics-websites',
        'name'     => _l('ga_websites'),
        'href'     => admin_url('google_analytics/websites'),
        'icon'     => 'fa fa-globe',
        'position' => 2,
    ]);
    $CI->app_menu->add_sidebar_children_item('google-analytics', [
        'slug'     => 'google-analytics-reports',
        'name'     => _l('ga_reports'),
        'href'     => admin_url('google_analytics/reports'),
        'icon'     => 'fa fa-bar-chart',
        'position' => 3,
    ]);
    $CI->app_menu->add_sidebar_children_item('google-analytics', [
        'slug'     => 'google-analytics-health',
        'name'     => _l('ga_health_check'),
        'href'     => admin_url('google_analytics/health'),
        'icon'     => 'fa fa-heartbeat',
        'position' => 4,
    ]);

    if (is_admin() || has_permission('google_analytics', '', 'edit')) {
        $CI->app_menu->add_setup_menu_item('google-analytics-settings', [
            'name'     => _l('google_analytics'),
            'href'     => admin_url('google_analytics/settings'),
            'icon'     => 'fa fa-line-chart',
            'position' => 66,
        ]);
    }
}

function google_analytics_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('google_analytics/settings') . '">' . _l('settings') . '</a>';
    $actions[] = '<a href="' . admin_url('google_analytics/health') . '">' . _l('ga_health_check') . '</a>';
    return $actions;
}

function google_analytics_is_module_page()
{
    $CI = &get_instance();
    return strtolower((string) $CI->uri->segment(1)) === 'google_analytics';
}

function google_analytics_load_admin_assets()
{
    if (!google_analytics_is_module_page()) {
        return;
    }
    echo '<link rel="stylesheet" href="' . module_dir_url(GOOGLE_ANALYTICS_MODULE_NAME, 'assets/css/google_analytics.css') . '?v=' . GOOGLE_ANALYTICS_MODULE_VERSION . '">';
}

function google_analytics_inject_admin_tracking()
{
    google_analytics_render_tracking_tag('admin');
}

function google_analytics_inject_client_tracking()
{
    google_analytics_render_tracking_tag('client');
}

function google_analytics_render_tracking_tag($area)
{
    if (get_option('ga_enabled') !== '1') {
        return;
    }
    if ($area === 'admin' && get_option('ga_track_admin') !== '1') {
        return;
    }
    if ($area === 'client' && get_option('ga_track_client') !== '1') {
        return;
    }

    $measurementId = trim((string) get_option('ga_crm_measurement_id'));
    if (!preg_match('/^G-[A-Z0-9]+$/i', $measurementId)) {
        return;
    }

    $measurementId = strtoupper($measurementId);
    $areaName = $area === 'admin' ? 'crm_admin' : 'crm_client_portal';
    ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo html_escape($measurementId); ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo html_escape($measurementId); ?>', {
    'anonymize_ip': true,
    'cookie_flags': 'SameSite=None;Secure',
    'page_location': window.location.href,
    'page_title': document.title,
    'crm_area': '<?php echo html_escape($areaName); ?>'
});
</script>
    <?php
}

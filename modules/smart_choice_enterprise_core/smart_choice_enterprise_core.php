<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Enterprise Core
Description: Enterprise 360 operating platform for Customer 360, Sales, Field Operations, Dispatch, Equipment, Inventory, Projects, Documents, Communications, Call Center, Marketing, Workflows, Reporting, QR Codes, Security, Performance, global search, audit, queue, and permissions.
Version: 1.5.1
Requires at least: 3.4.*
Author: Smart Choice Contractors USA / Harold Cabrera
Author URI: https://justsmartchoice.com
Quality: ★★★★★
*/

define('SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME', 'smart_choice_enterprise_core');
define('SMART_CHOICE_ENTERPRISE_CORE_VERSION', '1.5.1');

register_activation_hook(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME, 'smart_choice_enterprise_core_activate');
register_deactivation_hook(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME, 'smart_choice_enterprise_core_deactivate');
register_uninstall_hook(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME, 'smart_choice_enterprise_core_uninstall');

hooks()->add_action('admin_init', 'smart_choice_enterprise_core_admin_init');
hooks()->add_action('app_admin_head', 'smart_choice_enterprise_core_admin_head');
hooks()->add_action('app_admin_footer', 'smart_choice_enterprise_core_admin_footer');
hooks()->add_action('before_cron_run', 'smart_choice_enterprise_core_process_queue');
hooks()->add_action('after_staff_login', 'smart_choice_enterprise_core_record_login');

register_language_files(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME, [SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME]);

function smart_choice_enterprise_core_activate()
{
    require_once __DIR__ . '/install.php';
}

function smart_choice_enterprise_core_deactivate()
{
    update_option('smart_choice_enterprise_core_enabled', '0');
}

function smart_choice_enterprise_core_uninstall()
{
    // Enterprise operational and audit data is preserved intentionally.
    update_option('smart_choice_enterprise_core_enabled', '0');
}

function smart_choice_enterprise_core_admin_init()
{
    $CI = &get_instance();

    register_staff_capabilities(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME, [
        'capabilities' => [
            'view'                => _l('permission_view'),
            'view_own'            => _l('permission_view_own'),
            'view_global'         => _l('permission_view_global'),
            'create'              => _l('permission_create'),
            'edit'                => _l('permission_edit'),
            'delete'              => _l('permission_delete'),
            'manage'              => _l('permission_manage'),
            'export'              => _l('permission_export'),
            'manage_queue'        => _l('enterprise_permission_manage_queue'),
            'manage_features'     => _l('enterprise_permission_manage_features'),
            'view_audit'          => _l('enterprise_permission_view_audit'),
            'manage_permissions'  => _l('enterprise_permission_manage_field_permissions'),
            'view_customer_360'  => _l('enterprise_permission_view_customer_360'),
            'manage_customer_360'=> _l('enterprise_permission_manage_customer_360'),
            'use_global_search'   => _l('enterprise_permission_use_global_search'),
            'view_sales'          => _l('enterprise_permission_view_sales'),
            'manage_sales'        => _l('enterprise_permission_manage_sales'),
            'view_field_ops'      => _l('enterprise_permission_view_field_ops'),
            'manage_field_ops'    => _l('enterprise_permission_manage_field_ops'),
            'view_dispatch'       => _l('enterprise_permission_view_dispatch'),
            'manage_dispatch'     => _l('enterprise_permission_manage_dispatch'),
            'view_assets'         => _l('enterprise_permission_view_assets'),
            'manage_assets'       => _l('enterprise_permission_manage_assets'),
            'view_project_controls'=> _l('enterprise_permission_view_project_controls'),
            'manage_project_controls'=> _l('enterprise_permission_manage_project_controls'),
            'view_communications' => _l('enterprise_permission_view_communications'),
            'manage_communications' => _l('enterprise_permission_manage_communications'),
            'view_marketing' => _l('enterprise_permission_view_marketing'),
            'manage_marketing' => _l('enterprise_permission_manage_marketing'),
            'view_workflows' => _l('enterprise_permission_view_workflows'),
            'manage_workflows' => _l('enterprise_permission_manage_workflows'),
            'view_reports' => _l('enterprise_permission_view_reports'),
            'manage_reports' => _l('enterprise_permission_manage_reports'),
            'manage_qr' => _l('enterprise_permission_manage_qr'),
            'manage_security' => _l('enterprise_permission_manage_security'),
            'manage_performance' => _l('enterprise_permission_manage_performance'),
        ],
    ], _l('enterprise_360'));

    if (has_permission(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME, '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('smart-choice-enterprise-core', [
            'name'     => _l('enterprise_360'),
            'href'     => admin_url('smart_choice_enterprise_core'),
            'position' => 8,
            'icon'     => 'fa-solid fa-shield-halved',
        ]);

        $items = [
            ['smart-choice-enterprise-dashboard', 'enterprise_dashboard', '', 1, 'fa-gauge-high'],
            ['smart-choice-enterprise-customer-360', 'enterprise_customer_360', 'customer_360', 2, 'fa-address-card'],
            ['smart-choice-enterprise-sales', 'enterprise_sales_operations', 'sales_operations', 3, 'fa-chart-line'],
            ['smart-choice-enterprise-field-ops', 'enterprise_field_operations', 'field_operations', 4, 'fa-helmet-safety'],
            ['smart-choice-enterprise-dispatch', 'enterprise_dispatch', 'dispatch', 5, 'fa-map-location-dot'],
            ['smart-choice-enterprise-assets', 'enterprise_equipment_inventory', 'equipment_inventory', 6, 'fa-boxes-stacked'],
            ['smart-choice-enterprise-project-controls', 'enterprise_project_controls', 'project_controls', 7, 'fa-diagram-project'],
            ['smart-choice-enterprise-documents', 'enterprise_document_center', 'document_center', 8, 'fa-folder-open'],
            ['smart-choice-enterprise-communications', 'enterprise_communications', 'communications', 9, 'fa-comments'],
            ['smart-choice-enterprise-call-center', 'enterprise_call_center', 'call_center', 10, 'fa-headset'],
            ['smart-choice-enterprise-marketing', 'enterprise_marketing', 'marketing', 11, 'fa-bullhorn'],
            ['smart-choice-enterprise-workflows', 'enterprise_workflows', 'workflows', 12, 'fa-code-branch'],
            ['smart-choice-enterprise-reporting', 'enterprise_reporting', 'reporting', 13, 'fa-chart-pie'],
            ['smart-choice-enterprise-qr', 'enterprise_qr_center', 'qr_center', 14, 'fa-qrcode'],
            ['smart-choice-enterprise-security', 'enterprise_security', 'security_center', 15, 'fa-shield-halved'],
            ['smart-choice-enterprise-performance', 'enterprise_performance', 'performance_center', 16, 'fa-gauge'],
            ['smart-choice-enterprise-global-search', 'enterprise_global_search', 'global_search', 17, 'fa-magnifying-glass'],
            ['smart-choice-enterprise-foundation', 'enterprise_foundation', 'foundation', 18, 'fa-building-shield'],
            ['smart-choice-enterprise-queue', 'enterprise_queue', 'queue', 19, 'fa-list-check'],
            ['smart-choice-enterprise-feature-flags', 'enterprise_feature_flags', 'feature_flags', 20, 'fa-toggle-on'],
            ['smart-choice-enterprise-audit', 'enterprise_audit_log', 'audit_log', 21, 'fa-clipboard-list'],
            ['smart-choice-enterprise-field-permissions', 'enterprise_field_permissions', 'field_permissions', 22, 'fa-user-lock'],
            ['smart-choice-staff-image-health', 'staff_image_health', 'staff_images', 23, 'fa-user-check'],
            ['smart-choice-system-health', 'system_health', 'health', 24, 'fa-heart-pulse'],
        ];
        foreach ($items as $item) {
            $CI->app_menu->add_sidebar_children_item('smart-choice-enterprise-core', [
                'slug' => $item[0], 'name' => _l($item[1]),
                'href' => admin_url('smart_choice_enterprise_core' . ($item[2] ? '/' . $item[2] : '')),
                'position' => $item[3], 'icon' => 'fa-solid ' . $item[4],
            ]);
        }
    }
}

function smart_choice_enterprise_core_admin_head()
{
    if (strpos(uri_string(), 'smart_choice_enterprise_core') !== false) {
        echo '<link href="' . module_dir_url(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME, 'assets/css/enterprise-core.css') . '?v=' . SMART_CHOICE_ENTERPRISE_CORE_VERSION . '" rel="stylesheet" type="text/css">';
    }
}

function smart_choice_enterprise_core_admin_footer()
{
    if (strpos(uri_string(), 'smart_choice_enterprise_core') !== false) {
        echo '<script src="' . module_dir_url(SMART_CHOICE_ENTERPRISE_CORE_MODULE_NAME, 'assets/js/enterprise-core.js') . '?v=' . SMART_CHOICE_ENTERPRISE_CORE_VERSION . '"></script>';
    }
}


function smart_choice_enterprise_core_record_login($staffId)
{
    $CI = &get_instance();
    $table = db_prefix() . 'sce_login_audit';
    if (!$CI->db->table_exists($table)) { return; }
    $fields = $CI->db->list_fields($table);
    $row = [];
    $map = [
        'staff_id' => (int) $staffId,
        'ip_address' => $CI->input->ip_address(),
        'user_agent' => substr((string) $CI->input->user_agent(), 0, 500),
        'status' => 'success',
        'login_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
    ];
    foreach ($map as $key => $value) { if (in_array($key, $fields, true)) { $row[$key] = $value; } }
    if ($row) { $CI->db->insert($table, $row); }
}

function smart_choice_enterprise_core_process_queue()
{
    if (get_option('smart_choice_enterprise_core_enabled') !== '1') {
        return;
    }
    $CI = &get_instance();
    $CI->load->library('smart_choice_enterprise_core/Enterprise_queue');
    $CI->enterprise_queue->process((int) (get_option('smart_choice_enterprise_queue_batch_size') ?: 10));
}

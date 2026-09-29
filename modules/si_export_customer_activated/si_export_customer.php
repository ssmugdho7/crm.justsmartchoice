<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: SI Export Customer Details
Description: Module will Generate Report of Customer Profile and its Matrix/Overview.
Author: Sejal Infotech
Version: 1.1.2
Requires at least: 1.0.*
*/

define('SI_EXPORT_CUSTOMER_MODULE_NAME', 'si_export_customer');

$CI = &get_instance();

hooks()->add_action('admin_init', 'si_export_customer_admin_init_hook');
hooks()->add_filter('module_' . SI_EXPORT_CUSTOMER_MODULE_NAME . '_action_links', 'module_si_export_customer_action_links');
hooks()->add_action('after_customer_admins_tab', 'si_export_customer_profile_preview_tab');
hooks()->add_action('after_custom_profile_tab_content', 'si_export_customer_profile_preview_content');
hooks()->add_action('before_client_updated', 'si_export_customer_profile_preview_save', 1, 2);

function module_si_export_customer_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=si_export_customer_settings') . '">' . _l('settings') . '</a>';
    return $actions;
}

$CI->load->helper(SI_EXPORT_CUSTOMER_MODULE_NAME . '/si_export_customer');
$CI->load->model(SI_EXPORT_CUSTOMER_MODULE_NAME . '/si_export_customer_model');

register_activation_hook(SI_EXPORT_CUSTOMER_MODULE_NAME, 'si_export_customer_activation_hook');

function si_export_customer_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

register_language_files(SI_EXPORT_CUSTOMER_MODULE_NAME, [SI_EXPORT_CUSTOMER_MODULE_NAME]);

function si_export_customer_admin_init_hook()
{
    $CI = &get_instance();

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
    ];
    register_staff_capabilities('si_export_customer', $capabilities, _l('si_export_customer'));

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
    ];
    register_staff_capabilities('si_export_customer_matrix', $capabilities, _l('si_customer_matrix'));

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
    ];
    register_staff_capabilities('si_export_customer_services', $capabilities, _l('si_customer_services'));

    if (is_admin() || has_permission('si_export_customer_matrix', '', 'view')) {
        $CI->app_tabs->add_customer_profile_tab('client_matrix', [
            'name'     => _l('si_customer_matrix'),
            'icon'     => 'fa fa-clipboard menu-icon',
            'view'     => 'si_export_customer/customer_matrix_preview',
            'position' => 15,
        ]);
    }

    if (is_admin()) {
        $CI->app->add_settings_section_child('other', 'si_export_customer_settings', [
            'name'     => _l('si_customer_settings'),
            'view'     => 'si_export_customer/customer_settings',
            'position' => 100,
        ]);
    }

    if (is_admin() || has_permission('si_export_customer_services', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('si_export_customer_menu', [
            'collapse' => true,
            'icon'     => 'fa fa-area-chart',
            'name'     => _l('si_expoert_customer_menu'),
            'position' => 35,
        ]);

        $CI->app_menu->add_sidebar_children_item('si_export_customer_menu', [
            'slug'     => 'client-services-report',
            'name'     => _l('si_customer_services'),
            'href'     => admin_url('si_export_customer/client_services_report'),
            'position' => 10,
        ]);
    }
}

function si_export_customer_profile_preview_tab()
{
    $CI = &get_instance();

    if (is_admin() || has_permission('si_export_customer', '', 'view')) {
        $CI->load->view('si_export_customer/customer_profile_preview_tab');
    }
}

function si_export_customer_profile_preview_content($client)
{
    $CI = &get_instance();
    $CI->load->model('invoice_items_model');

    if ((is_admin() || has_permission('si_export_customer', '', 'view')) && $client) {
        $contact = $CI->clients_model->get_contact(get_primary_contact_user_id($client->userid));

        if ($contact) {
            $data['contact'] = $contact;
        }

        $data['files'] = $CI->clients_model->get_customer_files($client->userid);

        foreach ($data['files'] as $key => $row) {
            if (!isset($row['filetype']) || strpos((string) $row['filetype'], 'image/') === false) {
                unset($data['files'][$key]);
            }
        }

        $data['items'] = $CI->invoice_items_model->get();

        $CI->load->view('si_export_customer/customer_profile_preview_tab_content', $data);
    }
}

function si_export_customer_profile_preview_save($data, $id)
{
    $CI = &get_instance();

    if (!(is_admin() || has_permission('si_export_customer', '', 'view'))) {
        return $data;
    }

    $files_id = [];
    if (isset($data['si_export_customer_files']) && is_array($data['si_export_customer_files'])) {
        $files_id = $data['si_export_customer_files'];
    }

    $profile_file = '';
    if (isset($data['si_export_customer_profile_file'])) {
        $profile_file = $data['si_export_customer_profile_file'];
    }

    $items_id = [];
    if (isset($data['si_export_customer_items']) && is_array($data['si_export_customer_items'])) {
        $items_id = $data['si_export_customer_items'];
    }

    $update_data = [
        'files_id'        => serialize($files_id),
        'profile_file_id' => $profile_file,
    ];

    unset($data['si_export_customer_files']);
    unset($data['si_export_customer_profile_file']);
    unset($data['si_export_customer_items']);

    $CI->si_export_customer_model->update_client_kyc($update_data, $id);
    $CI->si_export_customer_model->update_client_services($items_id, $id);

    return $data;
}
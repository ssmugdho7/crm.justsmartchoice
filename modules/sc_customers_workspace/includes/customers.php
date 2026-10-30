<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_filter('admin_body_class', 'sccw_body_class');
hooks()->add_action('app_admin_head', 'sccw_head');
hooks()->add_action('app_admin_footer', 'sccw_footer');
hooks()->add_filter('all_contacts_table_row', 'sccw_contact_row', 10, 2);
hooks()->add_filter('admin_customer_contacts_table_row', 'sccw_contact_row', 10, 2);

function sccw_context()
{
    return strtolower(get_instance()->router->fetch_class()) === 'clients'
        && function_exists('scl_licensed') && scl_licensed('sc_customers_workspace');
}

function sccw_body_class($classes)
{
    if (!sccw_context()) {
        return $classes;
    }
    $classes[] = 'sccw-customers';
    $classes[] = 'sccw-page-' . strtolower(get_instance()->router->fetch_method());
    return $classes;
}

function sccw_head()
{
    if (!sccw_context()) {
        return;
    }
    $file = 'assets/css/customers.css';
    echo '<link rel="stylesheet" href="' . e(module_dir_url('sc_customers_workspace', $file) . '?v=' . filemtime(__DIR__ . '/../' . $file)) . '">';
}

function sccw_footer()
{
    if (!sccw_context()) {
        return;
    }
    $CI = &get_instance();
    $method = strtolower($CI->router->fetch_method());
    $config = [
        'method' => $method,
        'customersUrl' => admin_url('clients'),
        'contactsUrl' => admin_url('clients/all_contacts'),
        'title' => _l('sccw_title'),
        'subtitle' => _l('sccw_subtitle'),
        'customers' => _l('clients'),
        'contacts' => _l('customer_contacts'),
        'directory' => _l('sccw_directory'),
        'profile' => _l('sccw_profile'),
    ];
    echo '<script>window.scCustomersWorkspace=' . json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';
    $file = 'assets/js/customers.js';
    echo '<script src="' . e(module_dir_url('sc_customers_workspace', $file) . '?v=' . filemtime(__DIR__ . '/../' . $file)) . '"></script>';
}

function sccw_contact_row($row, $contact)
{
    if (!sccw_context()) {
        return $row;
    }
    foreach ($row as $index => $cell) {
        if (is_string($cell) && strpos($cell, '<div class="row-options">') !== false) {
            $row[$index] = str_replace('<div class="row-options">', '<div class="row-options sccw-row-actions">', $cell);
        }
    }
    return $row;
}

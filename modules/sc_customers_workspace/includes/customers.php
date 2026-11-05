<?php

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_filter('admin_body_class', 'sccw_body_class');
hooks()->add_action('app_admin_head', 'sccw_head');
hooks()->add_action('app_admin_footer', 'sccw_footer');
hooks()->add_filter('all_contacts_table_row', 'sccw_contact_row', 10, 2);
hooks()->add_filter('admin_customer_contacts_table_row', 'sccw_contact_row', 10, 2);
hooks()->add_filter('datatables_sql_query_results', 'sccw_contact_notification_data', 10, 2);

function sccw_context()
{
    if (function_exists('smartchoice_admin_theme_is_modern') && !smartchoice_admin_theme_is_modern()) { return false; }
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
        'copy' => _l('sccw_copy'),
        'copied' => _l('sccw_copied'),
        'copyFailed' => _l('sccw_copy_failed'),
        'activeCustomers' => _l('active_customers'),
        'inactiveCustomers' => _l('inactive_active_customers'),
        'allCustomers' => _l('clients'),
        'deleteConfirm' => _l('sccw_delete_confirm'),
        'whatsapp' => _l('sccw_whatsapp'),
        'sections' => [_l('sccw_section_identity'), _l('sccw_section_finance'), _l('sccw_section_ops'), _l('sccw_section_communications')],
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
    // Use only already-authorized row data; no additional contact queries or permission expansion.
    $name = trim($contact['full_name'] ?? (($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? '')));
    $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY);
    $initials = '';
    foreach (array_slice($parts ?: [], 0, 2) as $part) {
        preg_match('/^./us', $part, $first);
        $initials .= $first[0] ?? '';
    }
    $initials = function_exists('mb_strtoupper') ? mb_strtoupper($initials, 'UTF-8') : strtoupper($initials);
    if ($initials !== '' && isset($row[0])) {
        $avatar = '<span aria-hidden="true" class="sccw-contact-initials' . (!empty($contact['is_primary']) ? ' is-primary' : '') . '">' . e($initials) . '</span>';
        // Keep uploaded portraits; replace only the native generic placeholder.
        $row[0] = preg_replace('~<img\b[^>]*src="[^"]*/assets/images/user-placeholder\.jpg[^"]*"[^>]*>~i', $avatar, $row[0], 1);
    }
    if (isset($contact['sccw_notifications']) && isset($row[0])) {
        $badges = '<div class="sccw-notification-badges">';
        foreach (['invoice_emails' => 'invoices', 'estimate_emails' => 'estimates', 'project_emails' => 'projects', 'contract_emails' => 'contracts'] as $field => $label) {
            $enabled = !empty($contact['sccw_notifications'][$field]);
            $badges .= '<span class="sccw-perm-badge' . ($enabled ? ' is-active' : '') . '" title="' . e(_l('email_notifications') . ': ' . _l($label)) . '">' . e(_l($label)) . ' ' . ($enabled ? '✓' : '—') . '</span>';
        }
        $row[0] .= $badges . '</div>';
    }
    foreach ($row as $index => $cell) {
        if (is_string($cell) && strpos($cell, '<div class="row-options">') !== false) {
            $row[$index] = str_replace('<div class="row-options">', '<div class="row-options sccw-row-actions">', $cell);
        }
    }
    return $row;
}

/** Enrich only contacts already returned by native permission-filtered table queries. */
function sccw_contact_notification_data($rows, $context)
{
    if (!sccw_context() || ($context['table'] ?? '') !== db_prefix() . 'contacts' || !$rows) { return $rows; }
    $ids = array_values(array_unique(array_filter(array_map(function ($row) { return (int) ($row['id'] ?? 0); }, $rows))));
    if (!$ids) { return $rows; }
    $CI = &get_instance();
    $records = $CI->db->select('id,invoice_emails,estimate_emails,project_emails,contract_emails')->where_in('id', $ids)->get(db_prefix() . 'contacts')->result_array();
    $byId = [];
    foreach ($records as $record) { $byId[(int) $record['id']] = $record; }
    foreach ($rows as &$row) {
        if (isset($byId[(int) ($row['id'] ?? 0)])) { $row['sccw_notifications'] = $byId[(int) $row['id']]; }
    }
    unset($row);
    return $rows;
}

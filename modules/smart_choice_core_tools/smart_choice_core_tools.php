<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Core Tools
Description: Smart Choice CRM calculator, project profitability, staged sales payments, discount reasons, responsive sales tables, dashboard utilities, and safe module governance.
Version: 1.4.0
Author: Smart Choice Contractors USA / Harold Cabrera
*/

define('SMART_CHOICE_CORE_TOOLS_MODULE_NAME', 'smart_choice_core_tools');
define('SMART_CHOICE_CORE_TOOLS_VERSION', '1.4.0');

register_activation_hook(SMART_CHOICE_CORE_TOOLS_MODULE_NAME, 'smart_choice_core_tools_activate');
register_language_files(SMART_CHOICE_CORE_TOOLS_MODULE_NAME, [SMART_CHOICE_CORE_TOOLS_MODULE_NAME]);

function smart_choice_core_tools_activate()
{
    require_once __DIR__ . '/install.php';

    update_option('clients_default_theme', 'smartchoice');

    $CI = &get_instance();
    if ($CI->db->table_exists(db_prefix() . 'modules')) {
        foreach (['smart_choice_core_tools', 'notes'] as $moduleName) {
            $row = $CI->db->where('module_name', $moduleName)->get(db_prefix() . 'modules')->row();
            if ($row) {
                $CI->db->where('module_name', $moduleName)->update(db_prefix() . 'modules', ['active' => 1]);
            }
        }
    }
}

hooks()->add_action('admin_init', 'smart_choice_core_tools_admin_init');
hooks()->add_action('app_admin_footer', 'smart_choice_core_tools_footer');
hooks()->add_action('app_customers_footer', 'smart_choice_core_tools_customer_footer');
hooks()->add_action('app_admin_head', 'smart_choice_core_tools_head');

hooks()->add_filter('before_estimate_added', 'scct_capture_estimate');
hooks()->add_filter('before_estimate_updated', 'scct_capture_estimate');
hooks()->add_action('after_estimate_added', 'scct_save_estimate');
hooks()->add_action('after_estimate_updated', 'scct_save_estimate');

hooks()->add_filter('before_create_proposal', 'scct_capture_proposal');
hooks()->add_filter('before_proposal_updated', 'scct_capture_proposal');
hooks()->add_action('proposal_created', 'scct_save_proposal');
hooks()->add_action('after_proposal_updated', 'scct_save_proposal');

hooks()->add_filter('before_invoice_added', 'scct_capture_invoice');
hooks()->add_filter('before_update_invoice', 'scct_capture_invoice_update', 10, 2);
hooks()->add_action('after_invoice_added', 'scct_save_invoice');
hooks()->add_action('invoice_updated', 'scct_invoice_updated');

hooks()->add_action('estimate_converted_to_invoice', 'scct_estimate_converted_to_invoice');
hooks()->add_action('after_proposal_converted_to_invoice', 'scct_proposal_converted_to_invoice');
hooks()->add_action('proposal_converted_to_estimate', 'scct_proposal_converted_to_estimate');

function smart_choice_core_tools_admin_init()
{
    $CI = &get_instance();

    if (is_admin() || has_permission('reports', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'smart-choice-calculator',
            'name'     => 'scct_calculator',
            'icon'     => 'fa fa-calculator',
            'href'     => admin_url('smart_choice_core_tools/calculator'),
            'position' => 5,
        ]);

        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug'     => 'project-profitability',
            'name'     => 'scct_project_profitability',
            'icon'     => 'fa fa-chart-column',
            'href'     => admin_url('smart_choice_core_tools/project_profitability'),
            'position' => 40,
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'smart-choice-system-health',
            'name'     => 'scct_system_health',
            'icon'     => 'fa fa-heartbeat',
            'href'     => admin_url('smart_choice_core_tools/system_health'),
            'position' => 6,
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'smart-choice-module-compliance',
            'name'     => 'Module Compliance',
            'icon'     => 'fa fa-shield-halved',
            'href'     => admin_url('smart_choice_core_tools/module_compliance'),
            'position' => 7,
        ]);

        if ($CI->db->table_exists(db_prefix() . 'notes')) {
            $CI->app_menu->add_sidebar_menu_item('smart-choice-notes', [
                'slug'     => 'smart-choice-notes',
                'name'     => 'scct_notes',
                'icon'     => 'fa fa-note-sticky',
                'href'     => admin_url('notes/note'),
                'position' => 31,
            ]);
        }
    }
}

function scct_capture_estimate($hook)
{
    return scct_capture_meta($hook, 'estimate');
}

function scct_capture_proposal($hook)
{
    return scct_capture_meta($hook, 'proposal');
}

function scct_capture_invoice($hook)
{
    return scct_capture_meta($hook, 'invoice');
}

function scct_capture_invoice_update($hook, $id = null)
{
    return scct_capture_meta($hook, 'invoice', $id);
}

function scct_capture_meta($hook, $type, $id = null)
{
    $data = isset($hook['data']) && is_array($hook['data']) ? $hook['data'] : [];

    $GLOBALS['scct_pending_' . $type] = [
        'down_payment_percent' => isset($_POST['sc_down_payment_percent']) ? max(0, min(100, (float) $_POST['sc_down_payment_percent'])) : 0,
        'discount_category'    => isset($_POST['sc_discount_category']) ? trim((string) $_POST['sc_discount_category']) : '',
        'discount_reason'      => isset($_POST['sc_discount_reason']) ? trim((string) $_POST['sc_discount_reason']) : '',
        'payment_link'         => isset($_POST['sc_payment_link']) ? trim((string) $_POST['sc_payment_link']) : '',
        'payment_stage'        => isset($_POST['sc_payment_stage']) ? trim((string) $_POST['sc_payment_stage']) : 'deposit',
        'installment_label'    => isset($_POST['sc_installment_label']) ? trim((string) $_POST['sc_installment_label']) : '',
        'rel_id'               => (int) $id,
    ];

    foreach ([
        'sc_down_payment_percent',
        'sc_discount_category',
        'sc_discount_reason',
        'sc_payment_link',
        'sc_payment_stage',
        'sc_installment_label',
    ] as $field) {
        unset($data[$field]);
    }

    $hook['data'] = $data;

    return $hook;
}

function scct_save_estimate($id)
{
    scct_save_meta('estimate', (int) $id);
}

function scct_save_proposal($id)
{
    scct_save_meta('proposal', (int) $id);
}

function scct_save_invoice($id)
{
    scct_save_meta('invoice', (int) $id, true);
}

function scct_invoice_updated($payload)
{
    $id = is_array($payload) && isset($payload['id']) ? (int) $payload['id'] : (int) $payload;
    if ($id > 0) {
        scct_save_meta('invoice', $id, true);
    }
}

function scct_get_sale_total($type, $id)
{
    $CI = &get_instance();
    $tableMap = [
        'estimate' => 'estimates',
        'proposal' => 'proposals',
        'invoice'  => 'invoices',
    ];
    $idMap = [
        'estimate' => 'id',
        'proposal' => 'id',
        'invoice'  => 'id',
    ];

    if (!isset($tableMap[$type])) {
        return 0.0;
    }

    $row = $CI->db->select('total')->where($idMap[$type], (int) $id)->get(db_prefix() . $tableMap[$type])->row();

    return $row ? (float) $row->total : 0.0;
}

function scct_save_meta($type, $id, $applyInvoiceDeposit = false)
{
    $key = 'scct_pending_' . $type;
    if (empty($GLOBALS[$key]) || $id <= 0) {
        return;
    }

    $CI = &get_instance();
    $data = $GLOBALS[$key];
    $data['rel_type'] = $type;
    $data['rel_id'] = (int) $id;

    $existing = $CI->db->where([
        'rel_type' => $type,
        'rel_id'   => (int) $id,
    ])->get(db_prefix() . 'sc_sales_meta')->row();

    $currentTotal = scct_get_sale_total($type, $id);
    $contractTotal = $existing && (float) $existing->contract_total > 0
        ? (float) $existing->contract_total
        : $currentTotal;

    // If an invoice was previously reduced to its deposit total, preserve the original contract value.
    if ($type === 'invoice' && $existing && (float) $existing->contract_total > 0) {
        $contractTotal = (float) $existing->contract_total;
    }

    $percent = (float) $data['down_payment_percent'];
    $amountDueNow = $percent > 0 ? round($contractTotal * ($percent / 100), 2) : $contractTotal;
    $remaining = max(0, round($contractTotal - $amountDueNow, 2));

    $data['contract_total'] = $contractTotal;
    $data['amount_due_now'] = $amountDueNow;
    $data['remaining_balance'] = $remaining;
    $data['updated_at'] = date('Y-m-d H:i:s');

    if ($existing) {
        $CI->db->where('id', $existing->id)->update(db_prefix() . 'sc_sales_meta', $data);
    } else {
        $data['created_at'] = date('Y-m-d H:i:s');
        $CI->db->insert(db_prefix() . 'sc_sales_meta', $data);
    }

    // Deposit invoices must charge only the current installment. The original contract value remains in metadata.
    if ($type === 'invoice' && $applyInvoiceDeposit && $percent > 0 && $data['payment_stage'] === 'deposit') {
        $CI->db->where('id', (int) $id)->update(db_prefix() . 'invoices', [
            'total' => $amountDueNow,
        ]);
    }

    unset($GLOBALS[$key]);
}

function scct_copy_meta($fromType, $fromId, $toType, $toId)
{
    $CI = &get_instance();
    $source = $CI->db->where([
        'rel_type' => $fromType,
        'rel_id'   => (int) $fromId,
    ])->get(db_prefix() . 'sc_sales_meta')->row_array();

    if (!$source) {
        return;
    }

    unset($source['id']);
    $source['rel_type'] = $toType;
    $source['rel_id'] = (int) $toId;
    $source['created_at'] = date('Y-m-d H:i:s');
    $source['updated_at'] = date('Y-m-d H:i:s');

    $existing = $CI->db->where([
        'rel_type' => $toType,
        'rel_id'   => (int) $toId,
    ])->get(db_prefix() . 'sc_sales_meta')->row();

    if ($existing) {
        $CI->db->where('id', $existing->id)->update(db_prefix() . 'sc_sales_meta', $source);
    } else {
        $CI->db->insert(db_prefix() . 'sc_sales_meta', $source);
    }

    if ($toType === 'invoice' && (float) $source['down_payment_percent'] > 0) {
        $CI->db->where('id', (int) $toId)->update(db_prefix() . 'invoices', [
            'total' => (float) $source['amount_due_now'],
        ]);
    }
}

function scct_estimate_converted_to_invoice($data)
{
    if (isset($data['estimate_id'], $data['invoice_id'])) {
        scct_copy_meta('estimate', (int) $data['estimate_id'], 'invoice', (int) $data['invoice_id']);
    }
}

function scct_proposal_converted_to_invoice($data)
{
    if (isset($data['proposal_id'], $data['invoice_id'])) {
        scct_copy_meta('proposal', (int) $data['proposal_id'], 'invoice', (int) $data['invoice_id']);
    }
}

function scct_proposal_converted_to_estimate($data)
{
    if (isset($data['proposal_id'], $data['estimate_id'])) {
        scct_copy_meta('proposal', (int) $data['proposal_id'], 'estimate', (int) $data['estimate_id']);
    }
}

function smart_choice_core_tools_head()
{
    echo '<link rel="stylesheet" href="' . module_dir_url(SMART_CHOICE_CORE_TOOLS_MODULE_NAME, 'assets/css/core-tools.css?v=130') . '">';
}

function smart_choice_core_tools_footer()
{
    $CI = &get_instance();
    $uri = $CI->uri->uri_string();

    if (!preg_match('#admin/(estimates|invoices|proposals|credit_notes)(/|$)#', $uri)) {
        return;
    }

    $typeMap = [
        'estimates'    => 'estimate',
        'invoices'     => 'invoice',
        'proposals'    => 'proposal',
        'credit_notes' => 'credit_note',
    ];

    $segment = $CI->uri->segment(2);
    $type = $typeMap[$segment] ?? '';
    $id = 0;
    foreach (array_reverse($CI->uri->segment_array()) as $part) {
        if (ctype_digit((string) $part)) {
            $id = (int) $part;
            break;
        }
    }

    $meta = null;
    if ($type && $id > 0 && $CI->db->table_exists(db_prefix() . 'sc_sales_meta')) {
        $meta = $CI->db->where(['rel_type' => $type, 'rel_id' => $id])->get(db_prefix() . 'sc_sales_meta')->row_array();
    }

    echo '<script>window.SmartChoiceSalesMeta=' . json_encode($meta ?: new stdClass()) . ';</script>';
    echo '<script src="' . module_dir_url(SMART_CHOICE_CORE_TOOLS_MODULE_NAME, 'assets/js/core-tools.js?v=130') . '"></script>';
}


function smart_choice_core_tools_customer_footer()
{
    $CI = &get_instance();
    $uri = $CI->uri->uri_string();
    if (!preg_match('#(estimate|invoice|proposal)#i', $uri)) {
        return;
    }

    $type = '';
    if (stripos($uri, 'estimate') !== false) { $type = 'estimate'; }
    elseif (stripos($uri, 'invoice') !== false) { $type = 'invoice'; }
    elseif (stripos($uri, 'proposal') !== false) { $type = 'proposal'; }

    $id = 0;
    foreach (array_reverse($CI->uri->segment_array()) as $part) {
        if (ctype_digit((string) $part)) { $id = (int) $part; break; }
    }

    if (!$type || $id <= 0 || !$CI->db->table_exists(db_prefix() . 'sc_sales_meta')) {
        return;
    }

    $meta = $CI->db->where(['rel_type' => $type, 'rel_id' => $id])->get(db_prefix() . 'sc_sales_meta')->row_array();
    if (!$meta) { return; }

    echo '<script>window.SmartChoiceSalesMeta=' . json_encode($meta) . ';</script>';
    echo '<script src="' . module_dir_url(SMART_CHOICE_CORE_TOOLS_MODULE_NAME, 'assets/js/customer-payment-summary.js?v=130') . '"></script>';
}

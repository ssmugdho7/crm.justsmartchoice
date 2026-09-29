<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Smart Choice Payment Schedules
Description: Deposit, progress-payment, balance-invoice, and discount-reason workflow for estimates, proposals, invoices, and credit notes.
Version: 1.0.0
Requires at least: 3.4.*
Author: Smart Choice Contractors USA
Author URI: https://justsmartchoice.com/
*/

define('SCPS_MODULE_NAME', 'smart_choice_payment_schedule');
define('SCPS_VERSION', '1.0.0');

register_language_files(SCPS_MODULE_NAME, [SCPS_MODULE_NAME]);
register_activation_hook(SCPS_MODULE_NAME, 'scps_activate');
register_deactivation_hook(SCPS_MODULE_NAME, 'scps_deactivate');
register_uninstall_hook(SCPS_MODULE_NAME, 'scps_uninstall');

hooks()->add_action('admin_init', 'scps_admin_init');
hooks()->add_action('app_admin_head', 'scps_admin_assets');
hooks()->add_action('app_admin_footer', 'scps_admin_scripts');

// Core sales model hooks. The same callbacks are registered only where Perfex exposes them.
foreach (['estimate', 'proposal', 'invoice', 'credit_note'] as $type) {
    hooks()->add_filter('before_' . $type . '_added', function ($data) use ($type) {
        return scps_capture_before_save($type, $data, 0);
    });
    hooks()->add_filter('before_' . $type . '_updated', function ($data, $id = 0) use ($type) {
        return scps_capture_before_save($type, $data, (int) $id);
    }, 10, 2);
    hooks()->add_action('after_' . $type . '_added', function ($id) use ($type) {
        scps_after_save($type, (int) $id);
    });
    hooks()->add_action('after_' . $type . '_updated', function ($id) use ($type) {
        scps_after_save($type, (int) $id);
    });
}

// Acceptance/status compatibility hooks used across Perfex releases.
hooks()->add_action('estimate_status_changed', 'scps_estimate_status_changed', 10, 3);
hooks()->add_action('after_estimate_accepted', 'scps_estimate_accepted', 10, 2);
hooks()->add_action('proposal_status_changed', 'scps_proposal_status_changed', 10, 3);
hooks()->add_action('after_proposal_accepted', 'scps_proposal_accepted', 10, 2);

function scps_activate(): void
{
    require_once __DIR__ . '/install.php';
}

function scps_deactivate(): void
{
    // Preserve accounting history and relationships.
}

function scps_uninstall(): void
{
    // Accounting records are intentionally preserved. Controlled cleanup is available in Health Check.
}

function scps_admin_init(): void
{
    $CI = &get_instance();

    register_staff_capabilities('smart_choice_payment_schedule', [
        'capabilities' => [
            'view_own'    => _l('permission_view') . ' (' . _l('permission_own') . ')',
            'view_global' => _l('permission_view') . ' (' . _l('permission_global') . ')',
            'create'      => _l('permission_create'),
            'edit'        => _l('permission_edit'),
            'delete'      => _l('permission_delete'),
        ],
    ], _l('scps_permission_group'));

    if (is_admin() || staff_can('view_global', 'smart_choice_payment_schedule') || staff_can('view_own', 'smart_choice_payment_schedule')) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug'     => 'smart-choice-payment-schedules',
            'name'     => _l('scps_menu'),
            'href'     => admin_url('smart_choice_payment_schedule'),
            'position' => 55,
            'icon'     => 'fa-solid fa-money-check-dollar',
        ]);
    }
}

function scps_admin_assets(): void
{
    $CI = &get_instance();
    $uri = method_exists($CI->uri, 'uri_string') ? $CI->uri->uri_string() : '';
    $salesPages = ['admin/estimates', 'admin/proposals', 'admin/invoices', 'admin/credit_notes', 'admin/smart_choice_payment_schedule'];
    foreach ($salesPages as $page) {
        if (strpos($uri, $page) !== false) {
            echo '<link rel="stylesheet" href="' . module_dir_url(SCPS_MODULE_NAME, 'assets/css/payment_schedule.css?v=100') . '">';
            break;
        }
    }
}

function scps_admin_scripts(): void
{
    $CI = &get_instance();
    $uri = method_exists($CI->uri, 'uri_string') ? $CI->uri->uri_string() : '';
    if (preg_match('#admin/(estimates|proposals|invoices|credit_notes)#', $uri)) {
        echo '<script src="' . module_dir_url(SCPS_MODULE_NAME, 'assets/js/payment_schedule.js?v=100') . '"></script>';
    }
}

function scps_capture_before_save(string $type, array $data, int $id = 0): array
{
    $CI = &get_instance();
    $post = $CI->input->post();
    if (!is_array($post)) {
        return $data;
    }

    $payload = [
        'deposit_percent'       => max(0, min(100, (float) ($post['scps_deposit_percent'] ?? 0))),
        'payment_stage'         => trim((string) ($post['scps_payment_stage'] ?? 'full')),
        'discount_reason'       => trim((string) ($post['scps_discount_reason'] ?? '')),
        'discount_reason_other' => trim((string) ($post['scps_discount_reason_other'] ?? '')),
        'auto_create_balance'   => !empty($post['scps_auto_create_balance']) ? 1 : 0,
        'document_id'           => $id,
    ];
    $GLOBALS['scps_pending_' . $type] = $payload;

    // These fields are not native columns. Always remove them before the core model INSERT/UPDATE.
    foreach (array_keys($payload) as $key) {
        if ($key !== 'document_id') {
            unset($data['scps_' . $key]);
        }
    }

    // Add a clear payment summary to the native note/terms/content so client views, PDFs and emails retain it.
    $summary = scps_build_document_summary($payload);
    if ($summary !== '') {
        if ($type === 'proposal' && isset($data['content'])) {
            $data['content'] = scps_replace_summary_marker((string) $data['content'], $summary);
        } elseif (array_key_exists('clientnote', $data)) {
            $data['clientnote'] = scps_replace_summary_marker((string) $data['clientnote'], $summary);
        } elseif (array_key_exists('terms', $data)) {
            $data['terms'] = scps_replace_summary_marker((string) $data['terms'], $summary);
        }
    }

    return $data;
}

function scps_after_save(string $type, int $documentId): void
{
    if ($documentId <= 0) {
        return;
    }
    $payload = $GLOBALS['scps_pending_' . $type] ?? null;
    if (!is_array($payload)) {
        return;
    }
    $payload['document_id'] = $documentId;
    scps_save_schedule($type, $documentId, $payload);
    unset($GLOBALS['scps_pending_' . $type]);

    if ($type === 'invoice' && $payload['payment_stage'] === 'deposit' && $payload['deposit_percent'] > 0 && $payload['deposit_percent'] < 100) {
        scps_generate_invoice_installments($documentId, $payload);
    }
}

function scps_replace_summary_marker(string $content, string $summary): string
{
    $pattern = '#<!-- SCPS START -->.*?<!-- SCPS END -->#s';
    $content = preg_replace($pattern, '', $content) ?? $content;
    return trim($content) . "\n\n<!-- SCPS START -->\n" . $summary . "\n<!-- SCPS END -->";
}

function scps_build_document_summary(array $payload): string
{
    $deposit = (float) ($payload['deposit_percent'] ?? 0);
    $reason = trim((string) ($payload['discount_reason_other'] ?: $payload['discount_reason']));
    if ($deposit <= 0 && $reason === '') {
        return '';
    }
    $rows = ['<div class="scps-document-summary" style="border:1px solid #d9e2ec;border-radius:8px;padding:12px;margin-top:12px;">'];
    $rows[] = '<strong>Payment and Discount Information</strong><br>';
    if ($deposit > 0) {
        $rows[] = 'Required Down Payment: <strong>' . number_format($deposit, 2) . '%</strong><br>';
        $rows[] = 'The first invoice is limited to the required down payment. The remaining balance is billed separately.<br>';
    }
    if ($reason !== '') {
        $rows[] = 'Discount Reason: <strong>' . html_escape(ucwords(str_replace('_', ' ', $reason))) . '</strong><br>';
    }
    $rows[] = '</div>';
    return implode('', $rows);
}

function scps_save_schedule(string $type, int $documentId, array $payload): void
{
    $CI = &get_instance();
    $table = db_prefix() . 'sc_payment_schedules';
    if (!$CI->db->table_exists($table)) {
        require_once __DIR__ . '/install.php';
    }
    $reason = trim((string) ($payload['discount_reason_other'] ?: $payload['discount_reason']));
    $data = [
        'document_type'      => $type,
        'document_id'        => $documentId,
        'deposit_percent'    => (float) $payload['deposit_percent'],
        'payment_stage'      => (string) $payload['payment_stage'],
        'discount_reason'    => $reason,
        'auto_create_balance'=> (int) $payload['auto_create_balance'],
        'updated_at'         => date('Y-m-d H:i:s'),
    ];
    $existing = $CI->db->where(['document_type' => $type, 'document_id' => $documentId])->get($table)->row();
    if ($existing) {
        $CI->db->where('id', $existing->id)->update($table, $data);
    } else {
        $data['created_at'] = date('Y-m-d H:i:s');
        $CI->db->insert($table, $data);
    }
}

function scps_get_schedule(string $type, int $documentId): ?object
{
    $CI = &get_instance();
    $table = db_prefix() . 'sc_payment_schedules';
    if (!$CI->db->table_exists($table)) {
        return null;
    }
    return $CI->db->where(['document_type' => $type, 'document_id' => $documentId])->get($table)->row();
}

function scps_estimate_status_changed(...$args): void
{
    $id = (int) ($args[0] ?? 0);
    $status = $args[1] ?? ($args[2] ?? null);
    if ($id > 0 && in_array((string) $status, ['4', 'accepted'], true)) {
        scps_create_installments_from_estimate($id);
    }
}

function scps_estimate_accepted($id, $data = null): void
{
    scps_create_installments_from_estimate((int) $id);
}

function scps_proposal_status_changed(...$args): void
{
    $id = (int) ($args[0] ?? 0);
    $status = $args[1] ?? ($args[2] ?? null);
    if ($id > 0 && in_array((string) $status, ['3', 'accepted'], true)) {
        scps_create_installments_from_proposal($id);
    }
}

function scps_proposal_accepted($id, $data = null): void
{
    scps_create_installments_from_proposal((int) $id);
}

function scps_create_installments_from_estimate(int $estimateId): void
{
    $schedule = scps_get_schedule('estimate', $estimateId);
    if (!$schedule || (float) $schedule->deposit_percent <= 0 || (float) $schedule->deposit_percent >= 100) {
        return;
    }
    $CI = &get_instance();
    $estimate = $CI->db->where('id', $estimateId)->get(db_prefix() . 'estimates')->row();
    if (!$estimate) {
        return;
    }
    scps_create_installment_pair('estimate', $estimate, $schedule);
}

function scps_create_installments_from_proposal(int $proposalId): void
{
    $schedule = scps_get_schedule('proposal', $proposalId);
    if (!$schedule || (float) $schedule->deposit_percent <= 0 || (float) $schedule->deposit_percent >= 100) {
        return;
    }
    $CI = &get_instance();
    $proposal = $CI->db->where('id', $proposalId)->get(db_prefix() . 'proposals')->row();
    if (!$proposal || ($proposal->rel_type ?? '') !== 'customer') {
        return;
    }
    // Normalize proposal values into the fields used by the invoice builder.
    $source = (object) [
        'id'               => $proposal->id,
        'clientid'         => (int) $proposal->rel_id,
        'currency'         => (int) $proposal->currency,
        'total'            => (float) $proposal->total,
        'billing_street'   => '', 'billing_city' => '', 'billing_state' => '', 'billing_zip' => '', 'billing_country' => 0,
        'shipping_street'  => '', 'shipping_city' => '', 'shipping_state' => '', 'shipping_zip' => '', 'shipping_country' => 0,
        'project_id'       => (int) ($proposal->project_id ?? 0),
    ];
    scps_create_installment_pair('proposal', $source, $schedule);
}

function scps_generate_invoice_installments(int $invoiceId, array $payload): void
{
    $CI = &get_instance();
    $invoice = $CI->db->where('id', $invoiceId)->get(db_prefix() . 'invoices')->row();
    if (!$invoice) {
        return;
    }
    $schedule = (object) $payload;
    scps_create_installment_pair('invoice', $invoice, $schedule, $invoiceId);
}

function scps_create_installment_pair(string $sourceType, object $source, object $schedule, int $masterInvoiceId = 0): void
{
    static $creating = false;
    if ($creating) {
        return;
    }
    $creating = true;
    try {
        $CI = &get_instance();
        $installmentsTable = db_prefix() . 'sc_payment_installments';
        if (!$CI->db->table_exists($installmentsTable)) {
            require_once __DIR__ . '/install.php';
        }
        $sourceId = (int) $source->id;
        $exists = $CI->db->where(['source_type' => $sourceType, 'source_id' => $sourceId])->count_all_results($installmentsTable);
        if ($exists > 0) {
            return;
        }

        $contractTotal = (float) ($source->total ?? 0);
        $percent = max(0, min(100, (float) $schedule->deposit_percent));
        $depositAmount = round($contractTotal * ($percent / 100), 2);
        $balanceAmount = round($contractTotal - $depositAmount, 2);
        if ($depositAmount <= 0 || $balanceAmount < 0) {
            return;
        }

        $clientId = (int) ($source->clientid ?? 0);
        if ($clientId <= 0) {
            return;
        }

        $depositInvoiceId = scps_create_simple_invoice($clientId, $source, $depositAmount,
            _l('scps_deposit_invoice_item') . ' (' . number_format($percent, 2) . '%)', 1);
        $balanceInvoiceId = 0;
        if (!empty($schedule->auto_create_balance) && $balanceAmount > 0) {
            $balanceInvoiceId = scps_create_simple_invoice($clientId, $source, $balanceAmount,
                _l('scps_balance_invoice_item'), 6);
        }

        $CI->db->insert($installmentsTable, [
            'source_type'       => $sourceType,
            'source_id'         => $sourceId,
            'master_invoice_id' => $masterInvoiceId,
            'deposit_invoice_id'=> $depositInvoiceId,
            'balance_invoice_id'=> $balanceInvoiceId,
            'contract_total'    => $contractTotal,
            'deposit_percent'   => $percent,
            'deposit_amount'    => $depositAmount,
            'balance_amount'    => $balanceAmount,
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        if ($masterInvoiceId > 0 && $depositInvoiceId > 0) {
            // Keep the original complete invoice as the accounting master/draft so the customer pays only the deposit child invoice.
            $CI->db->where('id', $masterInvoiceId)->update(db_prefix() . 'invoices', ['status' => 6]);
        }
    } catch (Throwable $e) {
        log_message('error', 'SCPS installment creation failed: ' . $e->getMessage());
    } finally {
        $creating = false;
    }
}

function scps_create_simple_invoice(int $clientId, object $source, float $amount, string $description, int $status): int
{
    $CI = &get_instance();
    $CI->load->model('invoices_model');
    $today = date('Y-m-d');
    $dueDays = (int) get_option('invoice_due_after');
    $data = [
        'clientid'          => $clientId,
        'date'              => $today,
        'duedate'           => $dueDays > 0 ? date('Y-m-d', strtotime('+' . $dueDays . ' days')) : $today,
        'currency'          => (int) ($source->currency ?? get_base_currency()->id),
        'project_id'        => (int) ($source->project_id ?? 0),
        'billing_street'    => (string) ($source->billing_street ?? ''),
        'billing_city'      => (string) ($source->billing_city ?? ''),
        'billing_state'     => (string) ($source->billing_state ?? ''),
        'billing_zip'       => (string) ($source->billing_zip ?? ''),
        'billing_country'   => (int) ($source->billing_country ?? 0),
        'shipping_street'   => (string) ($source->shipping_street ?? ''),
        'shipping_city'     => (string) ($source->shipping_city ?? ''),
        'shipping_state'    => (string) ($source->shipping_state ?? ''),
        'shipping_zip'      => (string) ($source->shipping_zip ?? ''),
        'shipping_country'  => (int) ($source->shipping_country ?? 0),
        'include_shipping'  => 0,
        'show_shipping_on_invoice' => 0,
        'discount_type'     => '',
        'discount_percent'  => 0,
        'discount_total'    => 0,
        'adjustment'        => 0,
        'clientnote'        => _l('scps_installment_note'),
        'terms'             => get_option('predefined_terms_invoice'),
        'status'            => $status,
        'newitems'          => [[
            'description'      => $description,
            'long_description' => _l('scps_installment_long_description'),
            'qty'              => 1,
            'unit'             => '',
            'rate'             => $amount,
            'order'            => 1,
        ]],
    ];
    $id = $CI->invoices_model->add($data);
    if (!$id) {
        return 0;
    }
    if ($status === 6) {
        $CI->db->where('id', $id)->update(db_prefix() . 'invoices', ['status' => 6]);
    }
    return (int) $id;
}

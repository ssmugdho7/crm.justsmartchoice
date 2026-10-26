<?php

defined('BASEPATH') or exit('No direct script access allowed');

function smart_square_send_admin_receipt($paymentId)
{
    $CI = &get_instance();
    $CI->load->model('payments_model');
    $payment = $CI->payments_model->get($paymentId);
    if (!$payment || (string) $payment->paymentmode !== 'smart_square') { return false; }
    $recipient = trim((string) get_option('paymentmethod_smart_square_notification_email'));
    if ($recipient === '') { $recipient = 'admin@justsmartchoice.com'; }
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) { return false; }
    $CI->load->model('invoices_model');
    $invoice = $CI->invoices_model->get($payment->invoiceid);
    if (!$invoice) { return false; }

    // Native invoice-owner notifications already deliver to these recipients.
    $staff = $CI->db->where('email', $recipient)->get(db_prefix() . 'staff')->row();
    if ($staff && (string) get_option('notification_when_customer_pay_invoice') === '1'
        && in_array((int) $staff->staffid, [(int) $invoice->addedfrom, (int) $invoice->sale_agent], true)
        && !(is_staff_logged_in() && (int) $staff->staffid === (int) get_staff_user_id())
        && user_can_view_invoice($invoice->id, $staff->staffid)) { return true; }

    $table = db_prefix() . 'sc_payment_notification_ledger';
    $hasLedger = $CI->db->table_exists($table);
    if ($hasLedger) {
        $sql = $CI->db->insert_string($table, [
            'payment_id' => (int) $paymentId, 'channel' => 'square_admin_email',
            'recipient' => strtolower($recipient), 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s'),
        ]) . ' ON DUPLICATE KEY UPDATE id=id';
        $CI->db->query($sql);
        if ($CI->db->affected_rows() !== 1) { return false; }
    }
    $payment->invoice_data = $invoice;
    set_mailing_constant();
    $pdf = payment_pdf($payment);
    $attachment = $pdf->Output('', 'S');
    $arguments = [$recipient, $staff ? (int) $staff->staffid : 0, $invoice, $attachment, $paymentId];
    // Resolve/load the native receipt class, then specialize only its event identity.
    mail_template('invoice_payment_recorded_to_staff', ...$arguments);
    require_once __DIR__ . '/../libraries/Smart_square_admin_receipt.php';
    $sent = (new Smart_square_admin_receipt(...$arguments))->send();
    if ($hasLedger) {
        $CI->db->where('payment_id', (int) $paymentId)->where('channel', 'square_admin_email')
            ->where('recipient', strtolower($recipient))->update($table, ['status' => $sent ? 'sent' : 'failed']);
    }
    if (!$sent) { log_message('error', 'Smart Square admin receipt not accepted for CRM payment ' . (int) $paymentId); }
    return (bool) $sent;
}

function smart_square_checkout_customer($invoice, array $contact = [])
{
    $client = $invoice->client ?? (object) [];
    $choose = static function (...$values) {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') { return trim((string) $value); }
        }
        return '';
    };
    return [
        'name' => $choose($invoice->sc_customer_name ?? '', $client->company ?? '', trim(($contact['firstname'] ?? '') . ' ' . ($contact['lastname'] ?? ''))),
        'email' => $choose($invoice->sc_customer_email ?? '', $contact['email'] ?? ''),
        'phone' => $choose($invoice->sc_customer_phone ?? '', $contact['phonenumber'] ?? '', $client->phonenumber ?? ''),
        'address' => $choose($invoice->sc_project_address ?? '', implode(', ', array_filter([
            $invoice->billing_street ?? $client->address ?? '',
            $invoice->billing_city ?? $client->city ?? '',
            $invoice->billing_state ?? $client->state ?? '',
            $invoice->billing_zip ?? $client->zip ?? '',
        ], static fn($value) => trim((string) $value) !== ''))),
    ];
}

function smart_square_idempotency_key($invoiceId, $source, $minorUnits)
{
    // Square permits at most 45 characters; keep 160 bits of the digest.
    return substr(hash('sha256', implode('|', ['smart-square', (string) $invoiceId, $source, (string) $minorUnits])), 0, 40);
}

/**
 * Convert a non-negative decimal currency amount to integer minor units.
 *
 * @param mixed $amount
 * @return int|null
 */
function smart_square_minor_units($amount)
{
    $normalized = trim((string) $amount);
    if (!preg_match('/^(?:0|[1-9][0-9]*)(?:\.([0-9]{1,2}))?$/D', $normalized, $matches)) {
        return null;
    }

    $whole = strstr($normalized, '.', true);
    if ($whole === false) {
        $whole = $normalized;
    }
    $fraction = isset($matches[1]) ? str_pad($matches[1], 2, '0') : '00';

    if (strlen($whole) > 16) {
        return null;
    }

    return ((int) $whole * 100) + (int) $fraction;
}

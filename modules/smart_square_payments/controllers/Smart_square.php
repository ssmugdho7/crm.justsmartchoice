<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_square extends ClientsController
{
    public function charge()
    {
        if (strtoupper($this->input->method()) !== 'POST' || !$this->input->is_ajax_request()) {
            show_404();
        }

        $invoiceId = (int) $this->input->post('invoice_id');
        $invoiceHash = (string) $this->input->post('invoice_hash');

        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($invoiceId);
        if (!$invoice || $invoiceHash === '' || !hash_equals((string) $invoice->hash, $invoiceHash)) {
            return $this->reply(false, _l('smart_square_invoice_unavailable'));
        }
        if (is_client_logged_in() && (int) $invoice->clientid !== (int) get_client_user_id()) {
            return $this->reply(false, _l('smart_square_invoice_unavailable'));
        }
        if ((int) $invoice->status === Invoices_model::STATUS_PAID
            || (int) $invoice->status === Invoices_model::STATUS_CANCELLED) {
            return $this->reply(false, _l('smart_square_invoice_not_payable'));
        }
        if (!is_payment_mode_allowed_for_invoice('smart_square', $invoiceId)) {
            return $this->reply(false, _l('smart_square_not_allowed'));
        }

        $this->load->library('smart_square_payments/smart_square_gateway');
        $gateway = $this->smart_square_gateway;
        if ((string) $gateway->getSetting('active') !== '1') {
            return $this->reply(false, _l('smart_square_disabled'));
        }

        $source = (string) $this->input->post('source_id');
        if (!preg_match('/^[A-Za-z0-9_:\-]{5,2048}$/D', $source)) {
            return $this->reply(false, _l('smart_square_invalid_card_token'));
        }

        $accessToken = trim((string) $gateway->secret('access_token'));
        $locationId = trim((string) $gateway->getSetting('location_id'));
        if ($accessToken === '' || $locationId === '') {
            return $this->reply(false, _l('smart_square_not_configured'));
        }
        if (strtoupper((string) ($invoice->currency_name ?? '')) !== 'USD') {
            return $this->reply(false, _l('smart_square_usd_only'));
        }

        $this->load->helper('smart_square_payments/smart_square_payments');
        $balance = number_format((float) get_invoice_total_left_to_pay($invoiceId, $invoice->total), 2, '.', '');
        $minorUnits = smart_square_minor_units($balance);
        if ($minorUnits === null || $minorUnits <= 0) {
            return $this->reply(false, _l('smart_square_invoice_already_paid'));
        }

        // Retrying the same invoice/token/amount reuses the same Square request.
        $idempotencyKey = hash('sha256', implode('|', [
            'smart-square',
            (string) $invoiceId,
            $source,
            (string) $minorUnits,
        ]));
        $transactionTable = db_prefix() . 'smart_square_transactions';
        $existing = $this->db->where('idempotency_key', $idempotencyKey)->get($transactionTable)->row();
        if ($existing) {
            return $this->reply(
                (string) $existing->status === 'COMPLETED',
                (string) $existing->status === 'COMPLETED'
                    ? _l('smart_square_payment_received')
                    : _l('smart_square_payment_reconciliation', html_escape((string) $existing->payment_id))
            );
        }

        $payload = [
            'source_id'       => $source,
            'idempotency_key' => $idempotencyKey,
            'amount_money'    => ['amount' => $minorUnits, 'currency' => 'USD'],
            'location_id'     => $locationId,
            'reference_id'    => 'perfex-invoice-' . $invoiceId,
            'autocomplete'    => true,
        ];
        $baseUrl = (int) $gateway->getSetting('sandbox') === 1
            ? 'https://connect.squareupsandbox.com'
            : 'https://connect.squareup.com';

        $curl = curl_init($baseUrl . '/v2/payments');
        curl_setopt_array($curl, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Square-Version: 2025-01-23',
                'Content-Type: application/json',
            ],
        ]);
        $rawResponse = curl_exec($curl);
        $curlError = curl_errno($curl);
        $httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($curlError !== 0) {
            log_message('error', 'Smart Square transport failure for invoice ' . $invoiceId);
            return $this->reply(false, _l('smart_square_temporarily_unavailable'));
        }

        $result = json_decode((string) $rawResponse, true);
        $payment = is_array($result) ? ($result['payment'] ?? []) : [];
        if ($httpStatus < 200 || $httpStatus >= 300 || ($payment['status'] ?? '') !== 'COMPLETED') {
            log_message('error', 'Smart Square declined or incomplete payment for invoice ' . $invoiceId . ' (HTTP ' . $httpStatus . ')');
            return $this->reply(false, _l('smart_square_not_confirmed'));
        }

        $paymentId = (string) ($payment['id'] ?? '');
        $paidMinorUnits = (int) ($payment['amount_money']['amount'] ?? 0);
        $paidCurrency = strtoupper((string) ($payment['amount_money']['currency'] ?? ''));
        if ($paymentId === '' || $paidMinorUnits !== $minorUnits || $paidCurrency !== 'USD') {
            log_message('error', 'Smart Square amount verification failed for invoice ' . $invoiceId);
            return $this->reply(false, _l('smart_square_manual_verification'));
        }

        $existing = $this->db->where('payment_id', $paymentId)->get($transactionTable)->row();
        if ($existing) {
            return $this->reply(
                (string) $existing->status === 'COMPLETED',
                (string) $existing->status === 'COMPLETED'
                    ? _l('smart_square_payment_received')
                    : _l('smart_square_payment_reconciliation', html_escape($paymentId))
            );
        }

        $inserted = $this->db->insert($transactionTable, [
            'invoice_id'      => $invoiceId,
            'payment_id'      => $paymentId,
            'idempotency_key' => $idempotencyKey,
            'amount'          => $balance,
            'currency'        => 'USD',
            'status'          => 'RECONCILING',
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
        if (!$inserted) {
            log_message('error', 'Smart Square transaction persistence failed for invoice ' . $invoiceId);
            return $this->reply(false, _l('smart_square_manual_verification'));
        }

        try {
            $recorded = $gateway->addPayment([
                'amount'        => $balance,
                'invoiceid'     => $invoiceId,
                'transactionid' => $paymentId,
                'paymentmethod' => _l('smart_square_gateway_name'),
            ]);
            if ($recorded) {
                $this->db->where('payment_id', $paymentId)->update($transactionTable, ['status' => 'COMPLETED']);

                return $this->reply(true, _l('smart_square_payment_received'));
            }
        } catch (Throwable $exception) {
            log_message('error', 'Smart Square reconciliation failed for payment ' . $paymentId . ': ' . $exception->getMessage());
        }

        log_message('error', 'Smart Square payment requires reconciliation: ' . $paymentId);

        return $this->reply(false, _l('smart_square_payment_reconciliation', html_escape($paymentId)));
    }

    private function reply($success, $message)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => (bool) $success,
                'message' => (string) $message,
            ]));
    }
}

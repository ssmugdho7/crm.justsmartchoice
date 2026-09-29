<?php

use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;

defined('BASEPATH') or exit('No direct script access allowed');

class Ideal extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('ideal/Ideal_gateway', null, 'ideal_gateway');
    }

    public function make_payment($id, $hash)
    {
        check_invoice_restrictions($id, $hash);
        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($id);
        load_client_language($invoice->clientid);
        $data = [
            'invoice' => $invoice,
            'description' => $this->ideal_gateway->getDescription($id),
            'total' => $this->session->userdata('total_amount'),
            'base_amount' => $this->session->userdata('ideal_invoice_payment_amount'),
            'fee' => $this->session->userdata('ideal_processing_fee'),
            'client_secret' => $this->session->userdata('ideal_client_secret'),
        ];
        if (!$data['client_secret']) redirect(site_url("invoice/{$id}/{$hash}"));
        $this->load->view('ideal/payment', $data);
    }

    public function callback($id, $hash)
    {
        check_invoice_restrictions($id, $hash);
        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get($id);
        load_client_language($invoice->clientid);
        $redirectURL = site_url('invoice/' . $id . '/' . $hash);
        $sessionId = (string) $this->input->get('session_id');
        $this->session->unset_userdata(['total_amount','ideal_invoice_payment_amount','ideal_processing_fee','ideal_client_secret']);
        if ($sessionId === '') redirect($redirectURL);

        try {
            $session = $this->ideal_gateway->retrieveSession($sessionId);
            if ($session->status !== Session::STATUS_COMPLETE || empty($session->payment_intent)) {
                set_alert('danger', _l('ideal_payment_failure_message'));
                redirect($redirectURL);
            }
            if (total_rows('invoicepaymentrecords', ['transactionid' => $session->payment_intent]) > 0) redirect($redirectURL);
            $intent = $this->ideal_gateway->retrievePaymentIntent($session->payment_intent);
            $metadata = $intent->metadata->toArray();
            $amount = (float) ($metadata['invoice_payment_amount'] ?? ($intent->amount_received / 100));
            $success = $this->ideal_gateway->addPayment([
                'amount' => $amount,
                'invoiceid' => (int) ($metadata['invoice_id'] ?? $id),
                'transactionid' => $intent->id,
                'paymentmethod' => 'Stripe',
                'payment_attempt_reference' => (string) ($metadata['attempt_reference'] ?? ''),
            ]);
            set_alert($success ? 'success' : 'danger', _l($success ? 'online_payment_recorded_success' : 'online_payment_recorded_success_fail_database'));
        } catch (Throwable $e) {
            log_message('error', 'Stripe callback error: ' . $e->getMessage());
            set_alert('danger', _l('ideal_payment_failure_message'));
        }
        redirect($redirectURL);
    }

    public function create_webhook()
    {
        if (!is_staff_logged_in() || !is_admin()) show_404();
        try {
            foreach ($this->ideal_gateway->getAllWebhookObjects() as $webhook) {
                if (($webhook->metadata?->identification_key ?? '') === $this->ideal_gateway->getIdentificationKey() || $webhook->url === $this->ideal_gateway->getWebhookEndPoint()) {
                    $this->ideal_gateway->deleteWebhook($webhook);
                }
            }
            $this->ideal_gateway->createWebhook();
            set_alert('success', _l('webhook_created'));
        } catch (Throwable $e) {
            set_alert('danger', $e->getMessage());
        }
        redirect(admin_url('settings/?group=payment_gateways&tab=online_payments_Ideal_gateway_tab'));
    }

    public function enable_webhook(): void
    {
        if (is_staff_logged_in() && is_admin()) {
            try { $this->ideal_gateway->enableCurrentWebhookEndpoint(); } catch (Throwable $e) { set_alert('danger', $e->getMessage()); }
        }
        redirect(admin_url('settings/?group=payment_gateways&tab=online_payments_Ideal_gateway_tab'));
    }

    public function webhook(): void
    {
        $payload = (string) file_get_contents('php://input');
        try {
            $event = $this->ideal_gateway->validateAndReturnWebhookEvent($payload);
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            http_response_code(400); echo 'Invalid webhook'; return;
        }
        if ($this->eventAlreadyProcessed($event->id)) { http_response_code(200); return; }
        $this->logEvent($event, 'received');
        try {
            if ($event->type === \Stripe\Event::TYPE_PAYMENT_INTENT_SUCCEEDED) {
                $intent = $event->data->object;
                $metadata = $intent->metadata->toArray();
                if (total_rows('invoicepaymentrecords', ['transactionid' => $intent->id]) === 0 && !empty($metadata['invoice_id'])) {
                    $this->ideal_gateway->addPayment([
                        'amount' => (float) ($metadata['invoice_payment_amount'] ?? ($intent->amount_received / 100)),
                        'invoiceid' => (int) $metadata['invoice_id'],
                        'transactionid' => $intent->id,
                        'paymentmethod' => 'Stripe',
                        'payment_attempt_reference' => (string) ($metadata['attempt_reference'] ?? ''),
                    ]);
                }
            }
            $this->markEvent($event->id, 'processed');
            http_response_code(200);
        } catch (Throwable $e) {
            $this->markEvent($event->id, 'failed');
            log_message('error', 'Stripe webhook processing error: ' . $e->getMessage());
            http_response_code(500);
        }
    }

    private function eventAlreadyProcessed(string $eventId): bool
    {
        return total_rows('ideal_stripe_events', ['stripe_event_id' => $eventId, 'status' => 'processed']) > 0;
    }
    private function logEvent($event, string $status): void
    {
        if (total_rows('ideal_stripe_events', ['stripe_event_id' => $event->id]) === 0) {
            $this->db->insert(db_prefix().'ideal_stripe_events', [
                'stripe_event_id' => $event->id,
                'event_type' => $event->type,
                'object_id' => $event->data->object->id ?? null,
                'status' => $status,
                'payload' => json_encode($event),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
    private function markEvent(string $eventId, string $status): void
    {
        $this->db->where('stripe_event_id', $eventId)->update(db_prefix().'ideal_stripe_events', ['status' => $status]);
    }
}

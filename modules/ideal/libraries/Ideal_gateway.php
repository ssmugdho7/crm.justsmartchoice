<?php

use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\WebhookEndpoint;

defined('BASEPATH') or exit('No direct script access allowed');

class Ideal_gateway extends App_gateway
{
    private static ?StripeClient $stripeClient = null;
    public bool $processingFees = true;
    private string $webhookEndPoint;

    public function __construct()
    {
        parent::__construct();
        $this->setId(IDEAL_MODULE_GATEWAY_ID);
        $this->setName(_l('ideal_gateway_name'));
        $this->setSettings([
            [
                'name'       => 'api_publishable_key',
                'label'      => 'ideal_api_publishable_key',
                'input_type' => 'text',
                'field_attributes' => [
                    'autocomplete'         => 'off',
                    'autocapitalize'       => 'none',
                    'spellcheck'           => 'false',
                    'data-sc-money-ignore' => '1',
                    'placeholder'          => 'pk_live_...',
                ],
                'after' => '<p class="text-muted tw-mt-1">' . _l('ideal_publishable_key_help') . '</p>',
            ],
            [
                'name'       => 'api_secret_key',
                'encrypted'  => true,
                'label'      => 'ideal_api_secret_key',
                'input_type' => 'password',
                'field_attributes' => [
                    'autocomplete'         => 'new-password',
                    'autocapitalize'       => 'none',
                    'spellcheck'           => 'false',
                    'data-sc-money-ignore' => '1',
                ],
                'after' => '<p class="text-muted tw-mt-1">' . _l('ideal_secret_key_help') . '</p>',
            ],
            [
                'name'       => 'webhook_signing_secret',
                'encrypted'  => true,
                'label'      => 'ideal_webhook_signing_secret',
                'input_type' => 'password',
                'field_attributes' => [
                    'autocomplete'         => 'new-password',
                    'autocapitalize'       => 'none',
                    'spellcheck'           => 'false',
                    'data-sc-money-ignore' => '1',
                ],
                'after' => '<p class="text-muted tw-mt-1">' . _l('ideal_webhook_secret_help') . '</p>',
            ],
            ['name' => 'currencies', 'label' => 'settings_paymentmethod_currencies', 'default_value' => $this->getCrmCurrencyCode()],
            ['name' => 'use_crm_currency', 'label' => 'ideal_use_crm_currency', 'type' => 'yes_no', 'default_value' => '1'],
            ['name' => 'fallback_currency', 'label' => 'ideal_fallback_currency', 'type' => 'input', 'default_value' => $this->getCrmCurrencyCode()],
            ['name' => 'payment_methods', 'label' => 'ideal_payment_methods', 'default_value' => 'card,ideal'],
            ['name' => 'description_dashboard', 'label' => 'settings_paymentmethod_description', 'type' => 'textarea', 'default_value' => 'Payment for Invoice {invoice_number}'],
            ['name' => 'statement_descriptor', 'label' => 'ideal_statement_descriptor', 'type' => 'input'],
            ['name' => 'collect_billing_address', 'label' => 'ideal_collect_billing_address', 'type' => 'yes_no', 'default_value' => '1'],
            ['name' => 'allow_promotion_codes', 'label' => 'ideal_allow_promotion_codes', 'type' => 'yes_no', 'default_value' => '0'],
            ['name' => 'fee_enabled', 'label' => 'ideal_fee_enabled', 'type' => 'yes_no', 'default_value' => '0'],
            ['name' => 'fee_percent', 'label' => 'ideal_fee_percent', 'type' => 'input', 'default_value' => '0'],
            ['name' => 'fee_fixed', 'label' => 'ideal_fee_fixed', 'type' => 'input', 'default_value' => '0'],
            ['name' => 'fee_label', 'label' => 'ideal_fee_label', 'type' => 'input', 'default_value' => 'Processing Fee'],
        ]);

        $this->webhookEndPoint = site_url('ideal/webhook');
    }

    public function isGatewayKeyConfigured(): bool
    {
        return $this->hasSecretKey() && trim((string) $this->getSetting('api_publishable_key')) !== '';
    }

    public function hasSecretKey(): bool
    {
        try {
            return trim((string) $this->decryptSetting('api_secret_key')) !== '';
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getClient(): StripeClient
    {
        if (self::$stripeClient === null) {
            self::$stripeClient = new StripeClient(['api_key' => $this->decryptSetting('api_secret_key')]);
        }
        return self::$stripeClient;
    }

    public function process_payment(array $data): void
    {
        if (!$this->isGatewayKeyConfigured()) {
            $this->markAsInactive();
            set_alert('danger', _l('ideal_gateway_keys_not_configured'));
            redirect(site_url('invoice/' . $data['invoice']->id . '/' . $data['invoice']->hash));
        }

        $baseAmount = round((float) $data['amount'], 2);
        $fee = $this->calculateFee($baseAmount, (float) ($data['payment_attempt']->fee ?? 0));
        $chargeAmount = round($baseAmount + $fee, 2);
        $currency = strtolower($this->resolveInvoiceCurrency($data['invoice']));
        $methods = $this->normalizedPaymentMethods($currency);

        $lineItems = [[
            'price_data' => [
                'currency' => $currency,
                'product_data' => ['name' => $this->getDescription($data['invoiceid'])],
                'unit_amount' => (int) round($baseAmount * 100),
            ],
            'quantity' => 1,
        ]];
        if ($fee > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => trim((string) $this->getSetting('fee_label')) ?: 'Processing Fee'],
                    'unit_amount' => (int) round($fee * 100),
                ],
                'quantity' => 1,
            ];
        }

        $sessionData = [
            'payment_method_types' => $methods,
            'line_items' => $lineItems,
            'payment_intent_data' => [
                'capture_method' => 'automatic',
                'metadata' => [
                    'invoice_id' => (string) $data['invoice']->id,
                    'attempt_reference' => (string) $data['payment_attempt']->reference,
                    'invoice_payment_amount' => number_format($baseAmount, 2, '.', ''),
                    'processing_fee' => number_format($fee, 2, '.', ''),
                ],
            ],
            'mode' => 'payment',
            'success_url' => site_url("ideal/callback/{$data['invoiceid']}/{$data['hash']}?session_id={CHECKOUT_SESSION_ID}"),
            'cancel_url' => site_url('invoice/' . $data['invoiceid'] . '/' . $data['hash']),
            'billing_address_collection' => $this->getSetting('collect_billing_address') === '1' ? 'required' : 'auto',
        ];

        if ($this->getSetting('allow_promotion_codes') === '1') {
            $sessionData['allow_promotion_codes'] = true;
        }

        $descriptor = strtoupper(preg_replace('/[^A-Z0-9 ]/i', '', (string) $this->getSetting('statement_descriptor')));
        if ($descriptor !== '') {
            $sessionData['payment_intent_data']['statement_descriptor'] = substr($descriptor, 0, 22);
        }

        if (!empty($data['invoice']->client->stripe_id)) {
            $sessionData['customer'] = $data['invoice']->client->stripe_id;
        } elseif (!empty($data['invoice']->client->email)) {
            $sessionData['customer_email'] = $data['invoice']->client->email;
        }

        try {
            $session = $this->getClient()->checkout->sessions->create($sessionData);
        } catch (Throwable $e) {
            log_message('error', 'Smart Choice Stripe Checkout session error: ' . $e->getMessage());
            set_alert('danger', _l('ideal_payment_failure_message'));
            redirect(site_url('invoice/' . $data['invoice']->id . '/' . $data['invoice']->hash));
        }

        // Hosted Stripe Checkout is the most reliable flow for this CRM build.
        // It avoids browser-side parsing of API credentials and follows the same
        // Checkout Session redirect pattern as the repaired native Stripe gateway.
        if (!empty($session->url)) {
            redirect($session->url);
        }

        log_message('error', 'Smart Choice Stripe Checkout session returned without a hosted URL.');
        set_alert('danger', _l('ideal_payment_failure_message'));
        redirect(site_url('invoice/' . $data['invoice']->id . '/' . $data['invoice']->hash));
    }

    private function calculateFee(float $amount, float $nativeFee): float
    {
        if ($nativeFee > 0) {
            return round($nativeFee, 2);
        }
        if ($this->getSetting('fee_enabled') !== '1') {
            return 0.0;
        }
        $percent = max(0, (float) $this->getSetting('fee_percent'));
        $fixed = max(0, (float) $this->getSetting('fee_fixed'));
        return round(($amount * $percent / 100) + $fixed, 2);
    }

    private function normalizedPaymentMethods(string $currency): array
    {
        $configured = array_filter(array_map('trim', explode(',', strtolower((string) $this->getSetting('payment_methods')))));
        $allowed = ['card', 'ideal', 'us_bank_account', 'cashapp', 'link'];
        $methods = array_values(array_intersect($configured, $allowed));
        if ($currency !== 'eur') {
            $methods = array_values(array_diff($methods, ['ideal']));
        }
        return $methods ?: ['card'];
    }


    public function getCrmCurrencyCode(): string
    {
        try {
            $currency = get_base_currency();
            $code = strtoupper(trim((string) ($currency->name ?? '')));
            if (preg_match('/^[A-Z]{3}$/', $code)) {
                return $code;
            }
        } catch (Throwable $e) {
            log_message('error', 'Stripe CRM currency lookup failed: ' . $e->getMessage());
        }
        return 'USD';
    }

    public function resolveInvoiceCurrency($invoice): string
    {
        if ($this->getSetting('use_crm_currency') === '1') {
            if (!empty($invoice->currency) && is_numeric($invoice->currency)) {
                try {
                    $currency = get_currency((int) $invoice->currency);
                    $code = strtoupper(trim((string) ($currency->name ?? '')));
                    if (preg_match('/^[A-Z]{3}$/', $code)) {
                        return $code;
                    }
                } catch (Throwable $e) {
                    log_message('error', 'Stripe invoice currency lookup failed: ' . $e->getMessage());
                }
            }
            foreach ([(string) ($invoice->currency_name ?? ''), (string) ($invoice->currency ?? '')] as $candidate) {
                $candidate = strtoupper(trim($candidate));
                if (preg_match('/^[A-Z]{3}$/', $candidate)) {
                    return $candidate;
                }
            }
            return $this->getCrmCurrencyCode();
        }

        $fallback = strtoupper(trim((string) $this->getSetting('fallback_currency')));
        return preg_match('/^[A-Z]{3}$/', $fallback) ? $fallback : $this->getCrmCurrencyCode();
    }

    public function getPublishableKey(): string { return (string) $this->getSetting('api_publishable_key'); }
    public function getDescription($invoiceId): string { return str_replace('{invoice_number}', format_invoice_number($invoiceId), (string) $this->getSetting('description_dashboard')); }
    public function retrieveSession($sessionId): Session { return $this->getClient()->checkout->sessions->retrieve($sessionId); }
    public function retrievePaymentIntent($intentId): PaymentIntent { return $this->getClient()->paymentIntents->retrieve($intentId); }
    public function getWebhookEndPoint(): string { return $this->webhookEndPoint; }
    public function environment(): string
    {
        $key = (string) $this->decryptSetting('api_secret_key');
        return (str_starts_with($key, 'sk_test_') || str_starts_with($key, 'rk_test_')) ? 'test' : 'live';
    }

    public function getCurrentWebhookObject(): ?WebhookEndpoint
    {
        foreach ($this->getAllWebhookObjects() as $endpoint) {
            if ((string) $endpoint->url === $this->getWebhookEndPoint()) return $endpoint;
        }
        return null;
    }

    public function getAllWebhookObjects(): array { return $this->getClient()->webhookEndpoints->all(['limit' => 100])->data; }
    public function deleteWebhook(WebhookEndpoint $webhookEndpoint): void { $this->getClient()->webhookEndpoints->delete($webhookEndpoint->id, []); }
    public function getIdentificationKey(): string { return 'smart-choice-stripe-' . get_option('identification_key'); }

    public function createWebhook(): WebhookEndpoint
    {
        $webhook = $this->getClient()->webhookEndpoints->create([
            'url' => $this->webhookEndPoint,
            'enabled_events' => ideal_module_webhook_events(),
            'metadata' => ['identification_key' => $this->getIdentificationKey()],
        ]);
        update_option('ideal_module_stripe_webhook_id', $webhook->id);
        update_option('ideal_module_stripe_webhook_signing_secret', $webhook->secret);
        return $webhook;
    }

    public function enableCurrentWebhookEndpoint(): void
    {
        $current = $this->getCurrentWebhookObject();
        if ($current) $this->getClient()->webhookEndpoints->update($current->id, ['disabled' => false]);
    }

    public function validateAndReturnWebhookEvent(string $payload): \Stripe\Event
    {
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $secret = '';
        try { $secret = (string) $this->decryptSetting('webhook_signing_secret'); } catch (Throwable $e) {}
        if ($secret === '') $secret = (string) get_option('ideal_module_stripe_webhook_signing_secret');
        if ($secret === '') throw new UnexpectedValueException('Stripe webhook signing secret is not configured.');
        return Webhook::constructEvent($payload, $signature, $secret);
    }
}

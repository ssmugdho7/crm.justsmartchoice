<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Stripe_hub_api
{
    protected $ci;
    protected $client;
    protected $secretKey = '';

    public function __construct()
    {
        $this->ci = &get_instance();
        if (!class_exists('Stripe\\StripeClient')) {
            $autoload = APPPATH . 'vendor/autoload.php';
            if (file_exists($autoload)) require_once $autoload;
        }
    }

    public function secret_key()
    {
        if ($this->secretKey !== '') return $this->secretKey;
        if (get_option('stripe_hub_use_core_gateway') == '1') {
            try {
                $this->ci->load->library('gateways/stripe_gateway');
                $this->secretKey = $this->ci->stripe_gateway->decryptSetting('api_secret_key');
            } catch (Throwable $e) {
                $this->secretKey = '';
            }
        } else {
            $encrypted = get_option('stripe_hub_secret_key');
            if ($encrypted) {
                $value = $this->ci->encryption->decrypt($encrypted);
                $this->secretKey = $value ?: '';
            }
        }
        return trim($this->secretKey);
    }

    public function publishable_key()
    {
        if (get_option('stripe_hub_use_core_gateway') == '1') {
            try {
                $this->ci->load->library('gateways/stripe_gateway');
                return $this->ci->stripe_gateway->getSetting('api_publishable_key');
            } catch (Throwable $e) { return ''; }
        }
        return trim(get_option('stripe_hub_publishable_key'));
    }

    public function client()
    {
        if ($this->client) return $this->client;
        $key = $this->secret_key();
        if (!$key) throw new RuntimeException(_l('stripe_hub_missing_key'));
        if (!class_exists('Stripe\\StripeClient')) throw new RuntimeException(_l('stripe_hub_sdk_missing'));
        $this->client = new \Stripe\StripeClient($key);
        return $this->client;
    }

    public function account() { return $this->client()->accounts->retrieve(); }
    public function balance() { return $this->client()->balance->retrieve(); }
    public function payment_intents($limit = 25) { return $this->client()->paymentIntents->all(['limit' => $limit]); }
    public function customers($limit = 25) { return $this->client()->customers->all(['limit' => $limit]); }
    public function payment_links($limit = 25) { return $this->client()->paymentLinks->all(['limit' => $limit]); }
    public function refunds($limit = 25) { return $this->client()->refunds->all(['limit' => $limit]); }

    public function create_refund($paymentIntent, $amount = null, $reason = null)
    {
        $data = ['payment_intent' => $paymentIntent];
        if ($amount !== null) $data['amount'] = (int) $amount;
        if ($reason) $data['reason'] = $reason;
        return $this->client()->refunds->create($data);
    }

    public function create_payment_link($name, $amount, $currency, $description = '')
    {
        $product = $this->client()->products->create([
            'name' => $name,
            'description' => $description ?: null,
            'metadata' => ['source' => 'stripe_hub'],
        ]);
        $price = $this->client()->prices->create([
            'currency' => strtolower($currency),
            'unit_amount' => (int) $amount,
            'product' => $product->id,
        ]);
        return $this->client()->paymentLinks->create([
            'line_items' => [['price' => $price->id, 'quantity' => 1]],
            'metadata' => ['source' => 'stripe_hub'],
        ]);
    }

    public function create_invoice_checkout($invoice)
    {
        $amount = (float) $invoice->total_left_to_pay;
        if ($amount <= 0) throw new RuntimeException(_l('stripe_hub_invoice_paid'));
        $currency = strtolower($invoice->currency_name);
        $unit = strcasecmp($invoice->currency_name, 'JPY') === 0 ? (int) round($amount) : (int) round($amount * 100);
        $description = _l('stripe_hub_invoice_payment') . ' ' . format_invoice_number($invoice->id);
        $success = site_url('invoice/' . $invoice->id . '/' . $invoice->hash . '?stripe_hub=success');
        $cancel = site_url('invoice/' . $invoice->id . '/' . $invoice->hash);
        $sessionData = [
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => ['name' => $description],
                    'unit_amount' => $unit,
                ],
                'quantity' => 1,
            ]],
            'success_url' => $success,
            'cancel_url' => $cancel,
            'customer_creation' => 'always',
            'payment_intent_data' => [
                'description' => $description,
                'metadata' => [
                    'ClientId' => $invoice->clientid,
                    'InvoiceId' => $invoice->id,
                    'InvoiceHash' => $invoice->hash,
                    'source' => 'stripe_hub',
                    'created_by_staff' => get_staff_user_id(),
                ],
            ],
            'metadata' => [
                'InvoiceId' => $invoice->id,
                'source' => 'stripe_hub',
            ],
        ];
        if (!empty($invoice->client->stripe_id)) {
            $sessionData['customer'] = $invoice->client->stripe_id;
            unset($sessionData['customer_creation']);
        }
        return $this->client()->checkout->sessions->create($sessionData);
    }
}

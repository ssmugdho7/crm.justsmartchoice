<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ideal_admin extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        if (!is_admin() && !staff_can('view', 'ideal') && !staff_can('view_own', 'ideal')) access_denied('ideal');
        $this->load->library('ideal/Ideal_gateway', null, 'ideal_gateway');
    }


    public function settings_redirect()
    {
        redirect(admin_url('settings?group=payment_gateways&tab=online_payments_Ideal_gateway_tab'));
    }

    public function index()
    {
        $data['title'] = _l('ideal_dashboard');
        $data['health'] = $this->healthData();
        $data['events'] = $this->db->table_exists(db_prefix().'ideal_stripe_events')
            ? $this->db->order_by('id', 'DESC')->limit(25)->get(db_prefix().'ideal_stripe_events')->result_array()
            : [];
        $data['crm_currency'] = $this->ideal_gateway->getCrmCurrencyCode();
        $data['settings'] = [
            'use_crm_currency' => get_option('paymentmethod_Ideal_gateway_use_crm_currency'),
            'fallback_currency' => get_option('paymentmethod_Ideal_gateway_fallback_currency'),
            'fee_enabled' => get_option('paymentmethod_Ideal_gateway_fee_enabled'),
            'fee_percent' => get_option('paymentmethod_Ideal_gateway_fee_percent'),
            'fee_fixed' => get_option('paymentmethod_Ideal_gateway_fee_fixed'),
            'fee_label' => get_option('paymentmethod_Ideal_gateway_fee_label'),
        ];
        $data['links'] = $this->db->table_exists(db_prefix().'ideal_subscription_links')
            ? $this->db->order_by('id', 'DESC')->limit(25)->get(db_prefix().'ideal_subscription_links')->result_array()
            : [];
        $this->load->view('admin/dashboard', $data);
    }


    public function save_module_settings()
    {
        if (!is_admin() && !staff_can('edit', 'ideal')) {
            access_denied('ideal');
        }

        $useCrmCurrency = $this->input->post('use_crm_currency') ? '1' : '0';
        $fallback = strtoupper(trim((string) $this->input->post('fallback_currency')));
        if (!preg_match('/^[A-Z]{3}$/', $fallback)) {
            $fallback = $this->ideal_gateway->getCrmCurrencyCode();
        }
        $feeEnabled = $this->input->post('fee_enabled') ? '1' : '0';
        $feePercent = max(0, min(100, (float) $this->input->post('fee_percent')));
        $feeFixed = max(0, (float) $this->input->post('fee_fixed'));
        $feeLabel = trim((string) $this->input->post('fee_label')) ?: 'Processing Fee';

        update_option('paymentmethod_Ideal_gateway_use_crm_currency', $useCrmCurrency);
        update_option('paymentmethod_Ideal_gateway_fallback_currency', $fallback);
        update_option('paymentmethod_Ideal_gateway_fee_enabled', $feeEnabled);
        update_option('paymentmethod_Ideal_gateway_fee_percent', number_format($feePercent, 2, '.', ''));
        update_option('paymentmethod_Ideal_gateway_fee_fixed', number_format($feeFixed, 2, '.', ''));
        update_option('paymentmethod_Ideal_gateway_fee_label', $feeLabel);

        if ($useCrmCurrency === '1') {
            update_option('paymentmethod_Ideal_gateway_currencies', $this->ideal_gateway->getCrmCurrencyCode());
        }

        set_alert('success', _l('settings_updated'));
        redirect(admin_url('ideal/ideal_admin'));
    }

    public function create_subscription_link()
    {
        if (!is_admin() && !staff_can('create','ideal')) access_denied('ideal');
        $name = trim((string) $this->input->post('name'));
        $priceId = trim((string) $this->input->post('stripe_price_id'));
        $email = trim((string) $this->input->post('customer_email'));
        if ($name === '' || !preg_match('/^price_[A-Za-z0-9]+$/', $priceId)) {
            set_alert('danger', _l('ideal_invalid_price_id'));
            redirect(admin_url('ideal/ideal_admin'));
        }
        try {
            $session = $this->ideal_gateway->getClient()->checkout->sessions->create([
                'mode' => 'subscription',
                'line_items' => [['price' => $priceId, 'quantity' => 1]],
                'success_url' => site_url('clients') . '?stripe_subscription=success&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => site_url('clients') . '?stripe_subscription=cancelled',
                'customer_email' => $email !== '' ? $email : null,
                'allow_promotion_codes' => true,
            ]);
            $this->db->insert(db_prefix().'ideal_subscription_links', [
                'name' => $name,
                'stripe_price_id' => $priceId,
                'customer_email' => $email ?: null,
                'checkout_url' => $session->url,
                'stripe_session_id' => $session->id,
                'created_by' => get_staff_user_id(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            set_alert('success', _l('ideal_subscription_link_created'));
        } catch (Throwable $e) {
            set_alert('danger', $e->getMessage());
        }
        redirect(admin_url('ideal/ideal_admin'));
    }

    public function test_connection()
    {
        if (!is_admin()) access_denied('ideal');
        try {
            $account = $this->ideal_gateway->getClient()->accounts->retrieve();
            set_alert('success', _l('ideal_connection_success') . ': ' . html_escape($account->id));
        } catch (Throwable $e) {
            set_alert('danger', $e->getMessage());
        }
        redirect(admin_url('ideal/ideal_admin'));
    }

    private function healthData(): array
    {
        $data = [
            'keys' => $this->ideal_gateway->isGatewayKeyConfigured(),
            'environment' => $this->ideal_gateway->hasSecretKey() ? $this->ideal_gateway->environment() : 'not configured',
            'webhook_url' => $this->ideal_gateway->getWebhookEndPoint(),
            'webhook' => false,
            'stripe_sdk' => class_exists('Stripe\\StripeClient'),
            'events_table' => $this->db->table_exists(db_prefix().'ideal_stripe_events'),
            'subscriptions_table' => $this->db->table_exists(db_prefix().'ideal_subscription_links'),
        ];
        if ($data['keys']) {
            try { $data['webhook'] = (bool) $this->ideal_gateway->getCurrentWebhookObject(); } catch (Throwable $e) {}
        }
        return $data;
    }
}

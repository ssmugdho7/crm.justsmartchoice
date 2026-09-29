<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Stripe_hub extends AdminController
{
    protected $api;
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('stripe_hub/stripe_hub');
        if (!stripe_hub_can_view()) access_denied('stripe_hub');
        $this->load->library('stripe_hub/stripe_hub_api');
        $this->api = $this->stripe_hub_api;
        $this->load->model('stripe_hub/stripe_hub_model');
    }

    protected function page($view, $data = [])
    {
        $data['title'] = isset($data['title']) ? $data['title'] : _l('stripe_hub');
        $this->load->view('stripe_hub/' . $view, $data);
    }

    protected function api_safe($callback, $default = null)
    {
        try { return $callback(); }
        catch (Throwable $e) {
            $this->session->set_flashdata('stripe_hub_api_error', $e->getMessage());
            return $default;
        }
    }

    public function index()
    {
        $data = ['title' => _l('stripe_hub_dashboard')];
        $data['account'] = $this->api_safe(function(){ return $this->api->account(); });
        $data['balance'] = $this->api_safe(function(){ return $this->api->balance(); });
        $data['actions'] = $this->stripe_hub_model->get_actions(10);
        $this->page('dashboard', $data);
    }

    public function payments()
    {
        $data['title'] = _l('stripe_hub_payments');
        if (!is_admin() && !staff_can('view', 'stripe_hub')) {
            $data['payments'] = null;
            $data['actions'] = $this->stripe_hub_model->get_actions(100);
        } else {
            $data['payments'] = $this->api_safe(function(){ return $this->api->payment_intents((int)get_option('stripe_hub_items_per_page')); });
            $data['actions'] = [];
        }
        $this->page('payments', $data);
    }

    public function customers()
    {
        $data['title'] = _l('stripe_hub_customers');
        $data['customers'] = (is_admin() || staff_can('view', 'stripe_hub')) ? $this->api_safe(function(){ return $this->api->customers((int)get_option('stripe_hub_items_per_page')); }) : null;
        $this->page('customers', $data);
    }

    public function links()
    {
        $data['title'] = _l('stripe_hub_payment_links');
        $data['links'] = (is_admin() || staff_can('view', 'stripe_hub')) ? $this->api_safe(function(){ return $this->api->payment_links((int)get_option('stripe_hub_items_per_page')); }) : null;
        $data['actions'] = $this->stripe_hub_model->get_actions(100);
        $this->page('links', $data);
    }

    public function create_link()
    {
        stripe_hub_require('create');
        if (!$this->input->post()) redirect(admin_url('stripe_hub/links'));
        $name = trim($this->input->post('name', true));
        $description = trim($this->input->post('description', true));
        $currency = strtoupper(trim($this->input->post('currency', true)) ?: 'USD');
        $amount = (float)$this->input->post('amount');
        if (!$name || $amount <= 0) {
            set_alert('warning', _l('stripe_hub_invalid_amount'));
            redirect(admin_url('stripe_hub/links'));
        }
        try {
            $link = $this->api->create_payment_link($name, (int)round($amount * 100), $currency, $description);
            $this->stripe_hub_model->log_action([
                'action_type' => 'payment_link_created', 'stripe_object_id' => $link->id,
                'amount' => $amount, 'currency' => $currency, 'status' => $link->active ? 'active' : 'inactive',
                'description' => $name, 'metadata' => ['url' => $link->url],
            ]);
            set_alert('success', _l('stripe_hub_link_created'));
        } catch (Throwable $e) { set_alert('danger', $e->getMessage()); }
        redirect(admin_url('stripe_hub/links'));
    }

    public function invoice_checkout($invoiceId)
    {
        stripe_hub_require('create');
        $this->load->model('invoices_model');
        $invoice = $this->invoices_model->get((int)$invoiceId);
        if (!$invoice) show_404();
        try {
            $session = $this->api->create_invoice_checkout($invoice);
            $this->stripe_hub_model->log_action([
                'action_type' => 'invoice_checkout_created', 'stripe_object_id' => $session->id,
                'invoice_id' => $invoice->id, 'amount' => $invoice->total_left_to_pay, 'currency' => $invoice->currency_name,
                'status' => $session->status, 'description' => format_invoice_number($invoice->id),
                'metadata' => ['url' => $session->url],
            ]);
            redirect($session->url);
        } catch (Throwable $e) {
            set_alert('danger', $e->getMessage());
            redirect(admin_url('invoices/list_invoices'));
        }
    }

    public function refunds()
    {
        $data['title'] = _l('stripe_hub_refunds');
        $data['refunds'] = (is_admin() || staff_can('view', 'stripe_hub')) ? $this->api_safe(function(){ return $this->api->refunds((int)get_option('stripe_hub_items_per_page')); }) : null;
        $this->page('refunds', $data);
    }

    public function create_refund()
    {
        stripe_hub_require('delete');
        if (!$this->input->post()) redirect(admin_url('stripe_hub/refunds'));
        $pi = trim($this->input->post('payment_intent', true));
        $amountInput = trim($this->input->post('amount', true));
        $reason = trim($this->input->post('reason', true));
        $amount = $amountInput === '' ? null : (int)round(((float)$amountInput) * 100);
        try {
            $refund = $this->api->create_refund($pi, $amount, in_array($reason, ['duplicate','fraudulent','requested_by_customer'], true) ? $reason : null);
            $this->stripe_hub_model->log_action([
                'action_type' => 'refund_created', 'stripe_object_id' => $refund->id,
                'amount' => ((float)$refund->amount)/100, 'currency' => strtoupper($refund->currency),
                'status' => $refund->status, 'description' => $pi,
            ]);
            set_alert('success', _l('stripe_hub_refund_created'));
        } catch (Throwable $e) { set_alert('danger', $e->getMessage()); }
        redirect(admin_url('stripe_hub/refunds'));
    }

    public function logs()
    {
        $data['title'] = _l('stripe_hub_logs');
        $data['actions'] = $this->stripe_hub_model->get_actions(200);
        $data['events'] = (is_admin() || staff_can('view', 'stripe_hub')) ? $this->stripe_hub_model->get_events(200) : [];
        $this->page('logs', $data);
    }

    public function delete_log($id)
    {
        stripe_hub_require('delete');
        $this->db->where('id', (int)$id)->delete(db_prefix().'stripe_hub_actions');
        set_alert('success', _l('stripe_hub_log_deleted'));
        redirect(admin_url('stripe_hub/logs'));
    }

    public function settings()
    {
        stripe_hub_require('edit');
        if ($this->input->post()) {
            $useCore = $this->input->post('use_core_gateway') ? '1' : '0';
            update_option('stripe_hub_use_core_gateway', $useCore);
            update_option('stripe_hub_publishable_key', trim($this->input->post('publishable_key', true)));
            update_option('stripe_hub_default_currency', strtoupper(trim($this->input->post('default_currency', true)) ?: 'USD'));
            $perPage = max(5, min(100, (int)$this->input->post('items_per_page')));
            update_option('stripe_hub_items_per_page', (string)$perPage);
            $secret = trim($this->input->post('secret_key'));
            if ($secret !== '') update_option('stripe_hub_secret_key', $this->encryption->encrypt($secret));
            $webhook = trim($this->input->post('webhook_secret'));
            if ($webhook !== '') update_option('stripe_hub_webhook_secret', $this->encryption->encrypt($webhook));
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('stripe_hub/settings'));
        }
        $data['title'] = _l('stripe_hub_settings');
        $data['publishable_key'] = get_option('stripe_hub_publishable_key');
        $data['secret_masked'] = get_option('stripe_hub_secret_key') ? _l('stripe_hub_secret_saved') : '';
        $data['webhook_masked'] = get_option('stripe_hub_webhook_secret') ? _l('stripe_hub_secret_saved') : '';
        $this->page('settings', $data);
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Invoice extends ClientsController
{
    public function index($id = '', $hash = '')
    {
        check_invoice_restrictions($id, $hash);
        $invoice = $this->invoices_model->get($id);

        $invoice = hooks()->apply_filters('before_client_view_invoice', $invoice);

        if (!is_client_logged_in()) {
            load_client_language($invoice->clientid);
        }

        // Handle Invoice PDF generator
        if ($this->input->post('invoicepdf')) {
            try {
                $pdf = invoice_pdf($invoice);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            $invoice_number = format_invoice_number($invoice->id);
            $companyname    = get_option('invoice_company_name');
            if ($companyname != '') {
                $invoice_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }
            $pdf->Output(mb_strtoupper(slug_it($invoice_number), 'UTF-8') . '.pdf', 'D');
            die();
        }

        if ($this->input->post('action') === 'invoice_item_selection_changed') {
            $itemId = (int) $this->input->post('item_id');
            $chosen = $this->input->post('choosen') ? 1 : 0;
            if ($itemId > 0) {
                $item = $this->db->where('id', $itemId)->where('rel_id', (int)$id)->where('rel_type', 'invoice')->get(db_prefix().'itemable')->row();
                if ($item && (int)$item->is_optional === 1 && (int)$invoice->status !== Invoices_model::STATUS_PAID) {
                    update_sales_item_post($itemId, ['is_selected' => $chosen], 'is_selected');
                    $totals = calculate_sales_total(get_items_by_type('invoice', $id), [
                        'discount_percent' => $invoice->discount_percent,
                        'discount_total' => $invoice->discount_total,
                        'discount_type' => $invoice->discount_type,
                        'adjustment' => $invoice->adjustment,
                    ]);
                    $this->db->where('id', (int)$id)->update(db_prefix().'invoices', [
                        'subtotal' => $totals['subtotal'], 'total' => $totals['total'],
                        'total_tax' => $totals['total_tax'], 'discount_total' => $totals['discount_calculated'],
                    ]);
                }
            }
            redirect($this->uri->uri_string());
        }

        // Handle $_POST payment. Keep the native Perfex payment flow authoritative,
        // but if Stripe Checkout is the only active customer gateway, do not let a
        // missing browser-side radio selection block checkout.
        if ($this->input->post('make_payment')) {
            $this->load->model('payments_model');
            // Guarantee the native Stripe Checkout gateway is loaded before resolving
            // payment modes. This keeps the customer payment POST independent from
            // theme JavaScript and from any module load order.
            if ((string) get_option('paymentmethod_stripe_active') === '1') {
                $this->load->library('gateways/stripe_gateway');
            }
            if (!$this->input->post('paymentmode')
                && (string) get_option('paymentmethod_stripe_active') === '1'
                && is_payment_mode_allowed_for_invoice('stripe', $id)) {
                $_POST['paymentmode'] = 'stripe';
            }
            if (!$this->input->post('paymentmode')) {
                set_alert('warning', _l('invoice_html_payment_modes_not_selected'));
                redirect(site_url('invoice/' . $id . '/' . $hash));
            } elseif ((!$this->input->post('amount') || $this->input->post('amount') == 0) && get_option('allow_payment_amount_to_be_modified') == 1) {
                set_alert('warning', _l('invoice_html_amount_blank'));
                redirect(site_url('invoice/' . $id . '/' . $hash));
            }
            $this->payments_model->process_payment($this->input->post(), $id);
        }

        if ($this->input->post('paymentpdf')) {
            $payment = $this->payments_model->get($this->input->post('paymentpdf'));
            // Confirm that the payment is related to the invoice.
            if ($payment->invoiceid == $id) {
                $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);
                $paymentpdf            = payment_pdf($payment);
                $paymentpdf->Output(mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf', 'D');
                die;
            }
        }

        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');
        $this->load->library('app_number_to_word', [
            'clientid' => $invoice->clientid,
        ], 'numberword');
        if ((string) get_option('paymentmethod_stripe_active') === '1') {
            $this->load->library('gateways/stripe_gateway');
        }
        $this->load->model('payment_modes_model');
        $this->load->model('payments_model');
        $data['payments']      = $this->payments_model->get_invoice_payments($id);
        $data['payment_modes'] = $this->payment_modes_model->get();

        // Keep the native Perfex payment form authoritative. Repair only legacy/copied unpaid
        // invoices that lost their online gateway list while Stripe Checkout is active.
        if ((string) get_option('paymentmethod_stripe_active') === '1'
            && $invoice->status != Invoices_model::STATUS_PAID
            && $invoice->status != Invoices_model::STATUS_CANCELLED) {
            $stripeCurrenciesRaw = trim((string) get_option('paymentmethod_stripe_currencies'));
            if ($stripeCurrenciesRaw === '') { $stripeCurrenciesRaw = 'USD,CAD'; }
            $currencies = array_filter(array_map('trim', explode(',', $stripeCurrenciesRaw)));
            $currencySupported = false;
            foreach ($currencies as $currency) {
                if (mb_strtoupper($currency) === mb_strtoupper((string) $invoice->currency_name)) { $currencySupported = true; break; }
            }
            if ($currencySupported) {
                $rawAllowed = $invoice->allowed_payment_modes;
                $allowedIds = is_array($rawAllowed) ? $rawAllowed : @unserialize((string) $rawAllowed);
                $allowedIds = is_array($allowedIds) ? $allowedIds : [];
                if (!in_array('stripe', $allowedIds, true)) {
                    $allowedIds[] = 'stripe';
                    $serialized = serialize(array_values(array_unique(array_filter($allowedIds))));
                    $this->db->where('id', (int) $invoice->id)->update(db_prefix() . 'invoices', ['allowed_payment_modes' => $serialized]);
                    $invoice->allowed_payment_modes = $serialized;
                }
            }
        }

        // Compatibility repair for older/copied invoices that were saved without an allowed gateway.
        // Do not alter Stripe configuration; only make the active Stripe Checkout gateway available.
        if ((string) get_option('paymentmethod_stripe_active') === '1') {
            $rawAllowed = $invoice->allowed_payment_modes;
            if (is_array($rawAllowed)) {
                $allowedIds = array_map(static function ($mode) { return is_array($mode) ? ($mode['id'] ?? '') : (is_object($mode) ? ($mode->id ?? '') : $mode); }, $rawAllowed);
            } else {
                $allowedIds = @unserialize((string) $rawAllowed);
                $allowedIds = is_array($allowedIds) ? $allowedIds : [];
            }
            if (!in_array('stripe', $allowedIds, true)) {
                $allowedIds[] = 'stripe';
                $invoice->allowed_payment_modes = serialize(array_values(array_unique(array_filter($allowedIds))));
            }
        }
        $data['title']         = format_invoice_number($invoice->id);
        $this->disableNavigation();
        $this->disableSubMenu();
        $data['hash']      = $hash;
        $data['invoice']   = hooks()->apply_filters('invoice_html_pdf_data', $invoice);
        $data['bodyclass'] = 'viewinvoice';
        $this->data($data);
        $this->view('invoicehtml');
        add_views_tracking('invoice', $id);
        hooks()->do_action('invoice_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
    }
}

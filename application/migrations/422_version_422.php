<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_422 extends CI_Migration
{
    public function up()
    {
        // Smart Choice payment strategy: PayPal Checkout is the primary online gateway.
        // Keep source code/settings for Stripe for rollback, but disable it operationally.
        update_option('paymentmethod_stripe_active', '0');
        update_option('paymentmethod_stripe_default_selected', '0');
        update_option('paymentmethod_paypal_active', '0');
        update_option('paymentmethod_paypal_default_selected', '0');

        update_option('paymentmethod_paypal_checkout_active', '1');
        update_option('paymentmethod_paypal_checkout_label', 'PayPal');
        update_option('paymentmethod_paypal_checkout_default_selected', '1');
        update_option('paymentmethod_paypal_checkout_test_mode_enabled', '0');

        if (trim((string)get_option('paymentmethod_paypal_checkout_client_id')) === '') {
            update_option('paymentmethod_paypal_checkout_client_id', 'BAAEdx4eJ2OTF8su3Lln7HWEJ22WRquL9ICNIf4CuhdOVN5LW06K5g1q2QhtP1p5bc3hd_huS9teKePgxo');
        }
        if (trim((string)get_option('paymentmethod_paypal_checkout_currencies')) === '') {
            update_option('paymentmethod_paypal_checkout_currencies', 'USD,CAD,EUR');
        }
        if (trim((string)get_option('paymentmethod_paypal_checkout_payment_description')) === '') {
            update_option('paymentmethod_paypal_checkout_payment_description', 'Payment for Invoice {invoice_number}');
        }
        add_option('paymentmethod_paypal_checkout_merchant_id', '');
        add_option('paymentmethod_paypal_checkout_webhook_id', '');

        // Preserve offline methods while moving unpaid customer invoices from Stripe/legacy PayPal
        // to PayPal Checkout. Paid/cancelled invoices are not touched.
        $table = db_prefix() . 'invoices';
        if ($this->db->table_exists($table)) {
            $rows = $this->db->select('id,allowed_payment_modes,status')->where_not_in('status', [2, 5])->get($table)->result();
            foreach ($rows as $row) {
                $modes = @unserialize((string)$row->allowed_payment_modes);
                if (!is_array($modes)) {
                    $modes = [];
                }
                $modes = array_values(array_filter($modes, static function ($mode) {
                    return !in_array((string)$mode, ['stripe', 'paypal'], true);
                }));
                if (!in_array('paypal_checkout', $modes, true)) {
                    $modes[] = 'paypal_checkout';
                }
                $this->db->where('id', $row->id)->update($table, ['allowed_payment_modes' => serialize($modes)]);
            }
        }

        update_option('sc_crm_build_version', '4.2.2');
        update_option('smart_choice_crm_build', '4.2.2');
        update_option('smart_choice_crm_current_version', '4.2.2');
    }

    public function down()
    {
        // Non-destructive rollback: do not overwrite gateway credentials or invoice payment history.
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_423 extends CI_Migration
{
    public function up()
    {
        // Smart Choice additive payment strategy: preserve PayPal Checkout and restore
        // Stripe Checkout as an additional credit-card option. Never overwrite keys.
        update_option('paymentmethod_stripe_active', '1');
        if (get_option('paymentmethod_stripe_default_selected') === '') {
            add_option('paymentmethod_stripe_default_selected', '0');
        }
        if (trim((string) get_option('paymentmethod_stripe_currencies')) === '') {
            update_option('paymentmethod_stripe_currencies', 'USD,CAD');
        }

        // Keep PayPal Checkout exactly as configured by the previous release.
        if (get_option('paymentmethod_paypal_checkout_active') === '') {
            add_option('paymentmethod_paypal_checkout_active', '1');
        }

        // Preserve offline methods and add both active online gateways to unpaid invoices.
        $table = db_prefix() . 'invoices';
        if ($this->db->table_exists($table)) {
            $rows = $this->db->select('id,allowed_payment_modes,status')
                ->where_not_in('status', [2, 5])
                ->get($table)->result();
            foreach ($rows as $row) {
                $modes = @unserialize((string) $row->allowed_payment_modes);
                if (!is_array($modes)) {
                    $modes = [];
                }
                if ((string) get_option('paymentmethod_paypal_checkout_active') === '1'
                    && !in_array('paypal_checkout', $modes, true)) {
                    $modes[] = 'paypal_checkout';
                }
                if ((string) get_option('paymentmethod_stripe_active') === '1'
                    && !in_array('stripe', $modes, true)) {
                    $modes[] = 'stripe';
                }
                $this->db->where('id', $row->id)->update($table, [
                    'allowed_payment_modes' => serialize(array_values(array_unique($modes))),
                ]);
            }
        }

        update_option('sc_crm_build_version', '4.2.3');
        update_option('smart_choice_crm_build', '4.2.3');
        update_option('smart_choice_crm_current_version', '4.2.3');
    }

    public function down()
    {
        // Non-destructive rollback: do not disable gateways or alter payment history.
    }
}

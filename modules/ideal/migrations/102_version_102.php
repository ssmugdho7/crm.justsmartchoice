<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        $currencyCode = 'USD';
        try {
            $currency = get_base_currency();
            $candidate = strtoupper(trim((string) ($currency->name ?? '')));
            if (preg_match('/^[A-Z]{3}$/', $candidate)) {
                $currencyCode = $candidate;
            }
        } catch (Throwable $e) {
            log_message('error', 'Stripe currency migration lookup failed: ' . $e->getMessage());
        }

        update_option('paymentmethod_Ideal_gateway_use_crm_currency', '1');
        update_option('paymentmethod_Ideal_gateway_fallback_currency', $currencyCode);
        update_option('paymentmethod_Ideal_gateway_currencies', $currencyCode);
        if (get_option('paymentmethod_Ideal_gateway_fee_enabled') === false) {
            add_option('paymentmethod_Ideal_gateway_fee_enabled', '0');
        }
        if (get_option('paymentmethod_Ideal_gateway_fee_percent') === false) {
            add_option('paymentmethod_Ideal_gateway_fee_percent', '0');
        }
        if (get_option('paymentmethod_Ideal_gateway_fee_fixed') === false) {
            add_option('paymentmethod_Ideal_gateway_fee_fixed', '0');
        }
        if (get_option('paymentmethod_Ideal_gateway_fee_label') === false) {
            add_option('paymentmethod_Ideal_gateway_fee_label', 'Processing Fee');
        }
        update_option('ideal_module_version', '1.1.1');
    }

    public function down()
    {
        return true;
    }
}

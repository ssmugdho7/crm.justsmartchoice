<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_111 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();

        $currencyCode = 'USD';
        try {
            $currency = get_base_currency();
            $candidate = strtoupper(trim((string) ($currency->name ?? '')));
            if (preg_match('/^[A-Z]{3}$/', $candidate)) {
                $currencyCode = $candidate;
            }
        } catch (Throwable $e) {
            log_message('error', 'Smart Choice Stripe currency migration failed to read CRM currency: ' . $e->getMessage());
        }

        $options = [
            'paymentmethod_Ideal_gateway_use_crm_currency' => '1',
            'paymentmethod_Ideal_gateway_fallback_currency' => $currencyCode,
            'paymentmethod_Ideal_gateway_currencies' => $currencyCode,
            'paymentmethod_Ideal_gateway_fee_enabled' => get_option('paymentmethod_Ideal_gateway_fee_enabled') === false ? '0' : get_option('paymentmethod_Ideal_gateway_fee_enabled'),
            'paymentmethod_Ideal_gateway_fee_percent' => get_option('paymentmethod_Ideal_gateway_fee_percent') === false ? '0' : get_option('paymentmethod_Ideal_gateway_fee_percent'),
            'paymentmethod_Ideal_gateway_fee_fixed' => get_option('paymentmethod_Ideal_gateway_fee_fixed') === false ? '0' : get_option('paymentmethod_Ideal_gateway_fee_fixed'),
            'paymentmethod_Ideal_gateway_fee_label' => get_option('paymentmethod_Ideal_gateway_fee_label') === false ? 'Processing Fee' : get_option('paymentmethod_Ideal_gateway_fee_label'),
            'ideal_module_version' => '1.1.1',
        ];

        foreach ($options as $name => $value) {
            if (get_option($name) === false) {
                add_option($name, $value);
            } else {
                update_option($name, $value);
            }
        }

        require module_dir_path('ideal', 'install.php');

        return true;
    }

    public function down()
    {
        return true;
    }
}

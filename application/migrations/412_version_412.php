<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_412 extends CI_Migration
{
    public function up()
    {
        // Smart Choice CRM 4.1.2 payment-gateway cleanup and Stripe Account ID setting.
        update_option('smart_choice_crm_build', '4.1.2');

        // Preserve all existing payment data and gateway source code. Only retire the
        // requested legacy gateways from active use and seed the editable Stripe account ID.
        $retiredGatewayIds = [
            'stripe_ideal',
            'ideal_gateway',
            'ideal',
            'authorize_acceptjs',
            'instamojo',
            'mollie',
            'paypal_braintree',
            'payu_money',
        ];

        foreach ($retiredGatewayIds as $gatewayId) {
            $option = 'paymentmethod_' . $gatewayId . '_active';
            if (get_option($option) !== false) {
                update_option($option, 0);
            }
        }

        add_option('paymentmethod_stripe_account_id', 'acct_1TpVejPmy2tqppMD', 0);
    }
}

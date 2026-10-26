<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Smart_square_gateway extends App_gateway
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('smart_square');
        $this->setName(_l('smart_square_gateway_name'));
        $this->setSettings([
            ['name' => 'application_id', 'label' => 'smart_square_application_id'],
            ['name' => 'access_token', 'label' => 'smart_square_access_token', 'encrypted' => true, 'input_type' => 'password'],
            ['name' => 'location_id', 'label' => 'smart_square_location_id'],
            ['name' => 'currencies', 'label' => 'smart_square_supported_currencies', 'default_value' => 'USD'],
            ['name' => 'sandbox', 'label' => 'smart_square_sandbox_mode', 'type' => 'yes_no', 'default_value' => 1],
            ['name' => 'webhook_signature_key', 'label' => 'smart_square_webhook_signature_key', 'encrypted' => true, 'input_type' => 'password'],
        ]);
    }

    public function getName()
    {
        return _l('smart_square_gateway_name');
    }

    public function secret($name)
    {
        return $this->decryptSetting($name);
    }

    public function process_payment($data)
    {
        $CI = &get_instance();
        echo $CI->load->view('smart_square_payments/checkout', [
            'invoice' => $data['invoice'],
            'amount'  => $data['amount'],
            'gateway' => $this,
        ], true);
        // Checkout is a complete document; do not append the invoice layout.
        exit;
    }
}

<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Stripe_hub_webhook extends App_Controller
{
    public function index()
    {
        $this->load->model('stripe_hub/stripe_hub_model');
        $payload = file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';
        $encrypted = get_option('stripe_hub_webhook_secret');
        $secret = $encrypted ? $this->encryption->decrypt($encrypted) : '';
        try {
            if (!class_exists('Stripe\\Webhook')) {
                $autoload = APPPATH . 'vendor/autoload.php';
                if (file_exists($autoload)) require_once $autoload;
            }
            if (!$secret) throw new RuntimeException(_l('stripe_hub_webhook_secret_missing'));
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
            $this->stripe_hub_model->save_event([
                'stripe_event_id' => $event->id,
                'event_type' => $event->type,
                'livemode' => !empty($event->livemode) ? 1 : 0,
                'payload' => $payload,
                'processed' => 1,
                'error_message' => null,
            ]);
            http_response_code(200); echo 'ok';
        } catch (Throwable $e) {
            $this->stripe_hub_model->save_event([
                'stripe_event_id' => null, 'event_type' => 'invalid_or_unverified', 'livemode' => 0,
                'payload' => $payload, 'processed' => 0, 'error_message' => $e->getMessage(),
            ]);
            http_response_code(400); echo 'invalid';
        }
    }
}

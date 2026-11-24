<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Square Subscriptions API adapter. Provider identifiers are stored separately
 * from Stripe identifiers so existing Stripe subscriptions remain untouched.
 */
class Smart_square_subscriptions
{
    private $CI;
    private $gateway;
    private $baseUrl;
    private $apiVersion = '2025-01-23';

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->library('smart_square_payments/smart_square_gateway');
        $this->gateway = $this->CI->smart_square_gateway;
        $this->baseUrl = (int) $this->gateway->getSetting('sandbox') === 1
            ? 'https://connect.squareupsandbox.com'
            : 'https://connect.squareup.com';
        $this->ensureSchema();
    }

    public function configured(): bool
    {
        return trim((string) $this->gateway->secret('access_token')) !== ''
            && trim((string) $this->gateway->getSetting('application_id')) !== ''
            && trim((string) $this->gateway->getSetting('location_id')) !== '';
    }

    public function applicationId(): string
    {
        return trim((string) $this->gateway->getSetting('application_id'));
    }

    public function locationId(): string
    {
        return trim((string) $this->gateway->getSetting('location_id'));
    }

    public function sandbox(): bool
    {
        return (int) $this->gateway->getSetting('sandbox') === 1;
    }

    public function webhookUrl(): string
    {
        return site_url('smart_square_payments/webhook');
    }

    public function isSquarePlan($planId): bool
    {
        return is_string($planId) && strpos($planId, 'square:') === 0 && strlen($planId) > 7;
    }

    public function rawPlanId($planId): string
    {
        return $this->isSquarePlan($planId) ? substr($planId, 7) : (string) $planId;
    }

    /** Return Stripe-compatible view objects for the existing CRM plan selector. */
    public function get_plans(): object
    {
        $response = $this->request('GET', '/v2/catalog/list?types=SUBSCRIPTION_PLAN');
        $plans = [];
        foreach (($response['objects'] ?? []) as $catalogObject) {
            $plan = $catalogObject['subscription_plan_data'] ?? [];
            foreach (($plan['subscription_plan_variations'] ?? []) as $variation) {
                $variationData = $variation['subscription_plan_variation_data'] ?? [];
                $phases = $variationData['phases'] ?? [];
                $phase = $phases[0] ?? [];
                $pricing = $phase['pricing'] ?? [];
                // Catalog subscription phases use `price`, while older Square
                // responses used `price_money`. Accept both representations.
                $money = $pricing['price'] ?? ($pricing['price_money'] ?? []);
                $cadence = strtoupper((string) ($phase['cadence'] ?? 'MONTHLY'));
                $interval = $cadence === 'YEARLY' ? 'year' : ($cadence === 'WEEKLY' ? 'week' : 'month');
                $intervalCount = max(1, (int) ($phase['periods'] ?? 1));
                $id = (string) ($variation['id'] ?? '');
                if ($id === '' || ($pricing['type'] ?? 'STATIC') !== 'STATIC') {
                    continue;
                }
                $item = new stdClass();
                $item->id = 'square:' . $id;
                $item->active = true;
                $item->currency = strtolower((string) ($money['currency'] ?? 'USD'));
                $item->amount = (int) ($money['amount'] ?? 0);
                $item->interval = $interval;
                $item->interval_count = $intervalCount;
                $item->nickname = (string) ($variationData['name'] ?? $plan['name'] ?? 'Square subscription');
                $item->product = (object) ['name' => (string) ($plan['name'] ?? $item->nickname)];
                $plans[] = $item;
            }
        }
        return (object) ['data' => $plans];
    }

    public function get_plan($planId): ?object
    {
        foreach ($this->get_plans()->data as $plan) {
            if ($plan->id === $planId) {
                return $plan;
            }
        }
        return null;
    }

    public function createSubscription($crmSubscription, string $sourceId, array $billingAddress = []): array
    {
        if (!$this->configured()) {
            throw new RuntimeException('Square subscriptions are not configured.');
        }
        if (!$this->isSquarePlan($crmSubscription->stripe_plan_id ?? '')) {
            throw new RuntimeException('The selected plan is not a Square subscription plan.');
        }
        if (!preg_match('/^[A-Za-z0-9_:\-]{5,2048}$/D', $sourceId)) {
            throw new RuntimeException('Invalid Square card token.');
        }

        $map = $this->findMapping((int) $crmSubscription->id);
        $customerId = $map ? (string) $map->square_customer_id : '';
        if ($customerId === '') {
            $customer = $this->request('POST', '/v2/customers', [
                'idempotency_key' => $this->idempotency('customer', $crmSubscription->id),
                'given_name' => trim((string) ($crmSubscription->company ?? $crmSubscription->name)),
                'email_address' => $this->customerEmail($crmSubscription),
                'reference_id' => 'crm-subscription-' . (int) $crmSubscription->id,
            ]);
            $customerId = (string) ($customer['customer']['id'] ?? '');
            if ($customerId === '') {
                throw new RuntimeException('Square did not return a customer ID.');
            }
        }

        $card = $this->request('POST', '/v2/cards', [
            'idempotency_key' => $this->idempotency('card', $crmSubscription->id),
            'source_id' => $sourceId,
            'card' => ['customer_id' => $customerId],
            'cardholder_name' => trim((string) ($crmSubscription->company ?? $crmSubscription->name)),
            'billing_address' => $billingAddress,
        ]);
        $cardId = (string) ($card['card']['id'] ?? '');
        if ($cardId === '') {
            throw new RuntimeException('Square did not save the payment card.');
        }

        $payload = [
            'idempotency_key' => $this->idempotency('subscription', $crmSubscription->id),
            'location_id' => $this->locationId(),
            'plan_variation_id' => $this->rawPlanId($crmSubscription->stripe_plan_id),
            'customer_id' => $customerId,
            'card_id' => $cardId,
        ];
        if (!empty($crmSubscription->date) && $crmSubscription->date > date('Y-m-d')) {
            $payload['start_date'] = $crmSubscription->date;
        }
        $response = $this->request('POST', '/v2/subscriptions', $payload);
        $squareSubscription = $response['subscription'] ?? [];
        $squareId = (string) ($squareSubscription['id'] ?? '');
        if ($squareId === '') {
            throw new RuntimeException('Square did not return a subscription ID.');
        }
        $this->saveMapping((int) $crmSubscription->id, $customerId, $cardId, $squareId, (string) ($squareSubscription['status'] ?? 'PENDING'));
        return $squareSubscription;
    }

    public function cancel($crmSubscription, bool $immediate = false): void
    {
        $map = $this->findMapping((int) $crmSubscription->id);
        if (!$map || !$map->square_subscription_id) {
            return;
        }
        $path = '/v2/subscriptions/' . rawurlencode($map->square_subscription_id) . '/actions/cancel';
        $this->request('POST', $path, $immediate ? ['cancel_date' => date('Y-m-d')] : []);
        $this->CI->db->where('crm_subscription_id', (int) $crmSubscription->id)->update(db_prefix() . 'smart_square_subscriptions', ['status' => 'CANCELED']);
    }

    public function verifyWebhook(string $rawBody, string $signature): bool
    {
        $key = trim((string) $this->gateway->secret('webhook_signature_key'));
        if ($key === '' || $signature === '') {
            return false;
        }
        $expected = base64_encode(hash_hmac('sha256', $this->webhookUrl() . $rawBody, $key, true));
        return hash_equals($expected, trim($signature));
    }

    public function handleWebhook(array $event): void
    {
        $eventId = trim((string) ($event['event_id'] ?? ''));
        if ($eventId === '') {
            throw new RuntimeException('Square webhook event_id is missing.');
        }
        $insert = $this->CI->db->insert(db_prefix() . 'smart_square_subscription_events', [
            'event_id' => $eventId, 'event_type' => (string) ($event['type'] ?? ''),
            'payload' => json_encode($event), 'created_at' => date('Y-m-d H:i:s'),
        ]);
        if (!$insert && $this->CI->db->error()['code'] === 1062) {
            return;
        }
        if (!$insert) {
            throw new RuntimeException('Unable to record Square webhook event.');
        }

        $type = (string) ($event['type'] ?? '');
        $object = $event['data']['object'] ?? [];
        $squareSubscription = $object['subscription'] ?? [];
        $squareId = (string) ($squareSubscription['id'] ?? $object['subscription_id'] ?? '');
        $mapping = $squareId !== '' ? $this->findMappingBySquareId($squareId) : null;
        if (!$mapping) {
            return;
        }
        if (strpos($type, 'subscription.') === 0) {
            $status = strtoupper((string) ($squareSubscription['status'] ?? ''));
            if ($status !== '') {
                $this->CI->db->where('crm_subscription_id', (int) $mapping->crm_subscription_id)->update(db_prefix() . 'smart_square_subscriptions', ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
                $this->CI->db->where('id', (int) $mapping->crm_subscription_id)->update(db_prefix() . 'subscriptions', ['status' => strtolower($status)]);
            }
            return;
        }
        if ($type === 'invoice.payment_failed') {
            $this->CI->db->where('crm_subscription_id', (int) $mapping->crm_subscription_id)->update(db_prefix() . 'smart_square_subscriptions', ['status' => 'PAST_DUE', 'updated_at' => date('Y-m-d H:i:s')]);
            return;
        }
        if ($type === 'invoice.payment_made') {
            $this->createPaidInvoice($mapping, $object['invoice'] ?? $object);
        }
    }

    private function createPaidInvoice($mapping, array $squareInvoice): void
    {
        $squareInvoiceId = (string) ($squareInvoice['id'] ?? '');
        if ($squareInvoiceId === '') {
            return;
        }
        $exists = $this->CI->db->where('square_invoice_id', $squareInvoiceId)->get(db_prefix() . 'smart_square_subscription_invoices')->row();
        if ($exists) {
            return;
        }
        $subscription = $this->CI->subscriptions_model->get_by_id((int) $mapping->crm_subscription_id);
        if (!$subscription) {
            return;
        }
        $money = $squareInvoice['payment_requests'][0]['computed_amount_money'] ?? $squareInvoice['amount_money'] ?? [];
        $amount = ((int) ($money['amount'] ?? 0)) / 100;
        if ($amount <= 0) {
            return;
        }
        $this->CI->load->model('invoices_model');
        $invoiceId = $this->CI->invoices_model->add([
            'clientid' => (int) $subscription->clientid,
            'number' => get_option('next_invoice_number'), 'date' => _d(date('Y-m-d')), 'duedate' => _d(date('Y-m-d')),
            'currency' => (int) $subscription->currency, 'subtotal' => $amount, 'total' => $amount, 'status' => 2,
            'subscription_id' => (int) $subscription->id, 'allowed_payment_modes' => serialize(['smart_square']),
            'newitems' => [['description' => $subscription->name, 'long_description' => clear_textarea_breaks($subscription->description), 'qty' => 1, 'unit' => '', 'taxname' => [], 'rate' => $amount, 'order' => 1]],
        ]);
        if ($invoiceId) {
            $this->CI->db->insert(db_prefix() . 'smart_square_subscription_invoices', [
                'crm_subscription_id' => (int) $subscription->id, 'square_invoice_id' => $squareInvoiceId,
                'crm_invoice_id' => (int) $invoiceId, 'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        $curl = curl_init($this->baseUrl . $path);
        $options = [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_TIMEOUT => 30, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . trim((string) $this->gateway->secret('access_token')), 'Square-Version: ' . $this->apiVersion, 'Content-Type: application/json']];
        if ($method !== 'GET') { $options[CURLOPT_POSTFIELDS] = json_encode($payload); }
        curl_setopt_array($curl, $options);
        $raw = curl_exec($curl); $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE); $error = curl_error($curl); curl_close($curl);
        $decoded = json_decode((string) $raw, true);
        if ($error !== '' || !is_array($decoded) || $status < 200 || $status >= 300) {
            log_message('error', 'Square subscription API failure HTTP ' . $status . ': ' . ($error ?: 'provider rejected request'));
            throw new RuntimeException('Square could not complete the subscription request.');
        }
        return $decoded;
    }

    private function idempotency(string $kind, int $id): string { return substr(hash('sha256', 'crm-square|' . $kind . '|' . $id), 0, 40); }
    private function customerEmail($subscription): string {
        $contact = $this->CI->clients_model->get_contact(get_primary_contact_user_id((int) $subscription->clientid));
        return trim((string) ($contact->email ?? ''));
    }
    private function findMapping(int $id) { return $this->CI->db->where('crm_subscription_id', $id)->get(db_prefix() . 'smart_square_subscriptions')->row(); }
    private function findMappingBySquareId(string $id) { return $this->CI->db->where('square_subscription_id', $id)->get(db_prefix() . 'smart_square_subscriptions')->row(); }
    private function saveMapping(int $id, string $customer, string $card, string $subscription, string $status): void {
        $data = ['crm_subscription_id'=>$id,'square_customer_id'=>$customer,'square_card_id'=>$card,'square_subscription_id'=>$subscription,'status'=>$status,'updated_at'=>date('Y-m-d H:i:s')];
        $existing = $this->findMapping($id); if ($existing) { $this->CI->db->where('crm_subscription_id',$id)->update(db_prefix().'smart_square_subscriptions',$data); } else { $data['created_at']=date('Y-m-d H:i:s'); $this->CI->db->insert(db_prefix().'smart_square_subscriptions',$data); }
    }
    private function ensureSchema(): void {
        $db = $this->CI->db; $p = db_prefix();
        if (!$db->table_exists($p.'smart_square_subscriptions')) $db->query('CREATE TABLE `'.$p.'smart_square_subscriptions` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `crm_subscription_id` INT NOT NULL, `square_customer_id` VARCHAR(100) NOT NULL, `square_card_id` VARCHAR(100) NOT NULL, `square_subscription_id` VARCHAR(100) NOT NULL, `status` VARCHAR(30) NOT NULL, `created_at` DATETIME NOT NULL, `updated_at` DATETIME NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `crm_subscription_id` (`crm_subscription_id`), UNIQUE KEY `square_subscription_id` (`square_subscription_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        if (!$db->table_exists($p.'smart_square_subscription_events')) $db->query('CREATE TABLE `'.$p.'smart_square_subscription_events` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `event_id` VARCHAR(100) NOT NULL, `event_type` VARCHAR(100) NOT NULL, `payload` LONGTEXT NOT NULL, `created_at` DATETIME NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `event_id` (`event_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        if (!$db->table_exists($p.'smart_square_subscription_invoices')) $db->query('CREATE TABLE `'.$p.'smart_square_subscription_invoices` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `crm_subscription_id` INT NOT NULL, `square_invoice_id` VARCHAR(100) NOT NULL, `crm_invoice_id` INT NOT NULL, `created_at` DATETIME NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `square_invoice_id` (`square_invoice_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }
}

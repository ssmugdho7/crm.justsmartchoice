<?php

declare(strict_types=1);

function check_square(bool $condition, string $message): void
{
    if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
}

$root = dirname(__DIR__);
$library = file_get_contents($root . '/modules/smart_square_payments/libraries/Smart_square_subscriptions.php');
$controller = file_get_contents($root . '/modules/smart_square_payments/controllers/Smart_square.php');
$client = file_get_contents($root . '/application/controllers/Subscription.php');
$view = file_get_contents($root . '/application/views/themes/smartchoice/views/subscriptionhtml.php');
$routes = file_get_contents($root . '/modules/smart_square_payments/config/routes.php');
$migration = file_get_contents($root . '/modules/smart_square_payments/migrations/123_version_123.php');
check_square(strpos($library, "POST', '/v2/subscriptions") !== false, 'Square subscription creation uses the Subscriptions API');
check_square(strpos($library, "'/v2/cards'") !== false, 'Square cards are stored through the Cards API');
check_square(strpos($library, "invoice.payment_made") !== false && strpos($library, "invoice.payment_failed") !== false, 'payment success and failure webhooks are handled');
check_square(strpos($library, "hash_hmac('sha256'") !== false && strpos($library, '$this->webhookUrl() . $rawBody') !== false, 'webhooks validate the raw notification URL and body');
check_square(strpos($library, 'UNIQUE KEY `event_id`') !== false && strpos($library, 'UNIQUE KEY `square_invoice_id`') !== false, 'webhook and invoice processing are idempotent');
check_square(strpos($routes, "smart_square_payments/webhook") !== false && strpos($controller, "set_status_header(401)") !== false, 'webhook route rejects invalid signatures');
check_square(strpos($client, "isSquarePlan") !== false && strpos($client, "createSubscription") !== false, 'customer subscription flow uses Square when a Square plan is selected');
check_square(strpos($view, 'square-card-container') !== false && strpos($view, 'squarecdn.com') !== false, 'customer page tokenizes cards with Square Web Payments');
check_square(strpos($migration, 'Migration_Version_123') !== false, 'Square subscription migration is versioned');
check_square(!preg_match('/(?:EAAA|sq0idp-|sandbox-sq0idb-)[A-Za-z0-9_-]{10,}/', $library . $controller . $client . $view), 'no Square credentials are committed');
echo "Square subscription regression checks passed.\n";

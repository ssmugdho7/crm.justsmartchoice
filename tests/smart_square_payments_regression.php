<?php

declare(strict_types=1);

define('BASEPATH', __DIR__);

$options = [];
function get_option($name)
{
    global $options;
    return $options[$name] ?? '';
}

function check($condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$root = dirname(__DIR__);
require $root . '/application/helpers/invoices_helper.php';
require $root . '/modules/smart_square_payments/helpers/smart_square_payments_helper.php';

$options['paymentmethod_smart_square_active'] = '1';
$options['paymentmethod_smart_square_currencies'] = '';
check(sc_active_online_gateway_supports_currency('smart_square', 'USD'), 'Square defaults to USD when the option is absent');
check(!sc_active_online_gateway_supports_currency('smart_square', 'CAD'), 'Square does not silently allow a non-configured currency');

$options['paymentmethod_smart_square_currencies'] = 'USD, CAD';
check(sc_active_online_gateway_supports_currency('smart_square', 'cad'), 'Configured currencies are case-insensitive');

$options['paymentmethod_smart_square_active'] = '0';
check(!sc_active_online_gateway_supports_currency('smart_square', 'USD'), 'Inactive Square is rejected');
check(!sc_active_online_gateway_supports_currency('unregistered_gateway', 'USD'), 'Unknown gateways are rejected');
$options['paymentmethod_custom_gateway_currencies'] = 'EUR';
check(sc_payment_gateway_supports_currency('custom_gateway', 'EUR'), 'Existing custom gateway currency checks remain supported');

check(smart_square_minor_units('3') === 300, 'Whole dollars convert to minor units');
check(smart_square_minor_units('3.5') === 350, 'One decimal digit converts exactly');
check(smart_square_minor_units('3.05') === 305, 'Two decimal digits convert exactly');
check(smart_square_minor_units('3.005') === null, 'More than two decimal digits are rejected');
check(smart_square_minor_units('-1.00') === null, 'Negative amounts are rejected');
$customer = smart_square_checkout_customer((object) [
    'sc_customer_name' => 'Invoice recipient', 'sc_customer_email' => 'billing@example.test',
    'billing_street' => '10 Test Street', 'billing_city' => 'Tampa',
    'client' => (object) ['company' => 'Fallback company', 'phonenumber' => '7275550100'],
], ['email' => 'primary@example.test']);
check($customer['name'] === 'Invoice recipient' && $customer['email'] === 'billing@example.test', 'Invoice billing identity wins over contact fallbacks');
check($customer['phone'] === '7275550100' && $customer['address'] === '10 Test Street, Tampa', 'Checkout fills missing billing fields from the client');
$requestKey = smart_square_idempotency_key(122, 'test-source', 300);
check(strlen($requestKey) <= 45, 'Idempotency key fits Square API limits');
check($requestKey === smart_square_idempotency_key(122, 'test-source', 300), 'A retry keeps the same key');
check($requestKey !== smart_square_idempotency_key(123, 'test-source', 300), 'Different invoices have different keys');

$route = file_get_contents($root . '/modules/smart_square_payments/config/routes.php');
$checkout = file_get_contents($root . '/modules/smart_square_payments/views/checkout.php');
$gateway = file_get_contents($root . '/modules/smart_square_payments/libraries/Smart_square_gateway.php');
check(strpos($route, "smart_square_payments/charge") !== false, 'Public charge route is registered under the module slug');
check(strpos($checkout, "site_url('smart_square_payments/charge')") !== false, 'Checkout posts to the registered route');
check(strpos($gateway, "'name' => 'currencies'") !== false, 'Gateway registers supported currencies');

$english = file_get_contents($root . '/modules/smart_square_payments/language/english/smart_square_payments_lang.php');
$spanish = file_get_contents($root . '/modules/smart_square_payments/language/spanish/smart_square_payments_lang.php');
preg_match_all('/\\$lang\\[\'([^\']+)\'\\]/', $english, $englishMatches);
preg_match_all('/\\$lang\\[\'([^\']+)\'\\]/', $spanish, $spanishMatches);
sort($englishMatches[1]);
sort($spanishMatches[1]);
check($englishMatches[1] === $spanishMatches[1], 'English and Spanish language keys match');

$moduleFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
    $root . '/modules/smart_square_payments',
    FilesystemIterator::SKIP_DOTS
));
foreach ($moduleFiles as $file) {
    if (!$file->isFile()) {
        continue;
    }
    $contents = file_get_contents($file->getPathname());
    check(!preg_match('/(?:EAAA|sq0idp-|sandbox-sq0idb-)[A-Za-z0-9_-]{10,}/', $contents), 'No Square credentials are committed');
}

$renderProbe = <<<'PHP'
define('BASEPATH', __DIR__);
class App_gateway {}
class TestSquareViewLoader {
    public function helper($name) { require_once $GLOBALS['argv'][2]; }
    public function view($name, $data, $return = false) {
        return $return ? '<html><body>Square checkout</body></html>' : null;
    }
}
class TestSquareDb {
    public function where(...$args) { return $this; }
    public function order_by(...$args) { return $this; }
    public function get($name) { return $this; }
    public function row_array() { return []; }
}
function db_prefix() { return 'test_'; }
function &get_instance() {
    static $ci;
    if (!$ci) { $ci = (object) ['load' => new TestSquareViewLoader, 'db' => new TestSquareDb]; }
    return $ci;
}
require $argv[1];
$gateway = (new ReflectionClass(Smart_square_gateway::class))->newInstanceWithoutConstructor();
$gateway->process_payment(['invoice' => (object) ['id' => 1, 'clientid' => 1], 'amount' => '3.00']);
echo 'unwanted-invoice-layout';
PHP;
// The probe exercises view output and termination without initializing CRM settings.
$rendered = shell_exec(escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg($renderProbe) . ' ' . escapeshellarg($root . '/modules/smart_square_payments/libraries/Smart_square_gateway.php') . ' ' . escapeshellarg($root . '/modules/smart_square_payments/helpers/smart_square_payments_helper.php'));
check($rendered === '<html><body>Square checkout</body></html>', 'Checkout flushes its HTML and does not append the invoice layout');

echo "Smart Square payment regression checks passed.\n";

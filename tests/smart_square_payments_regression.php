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

echo "Smart Square payment regression checks passed.\n";

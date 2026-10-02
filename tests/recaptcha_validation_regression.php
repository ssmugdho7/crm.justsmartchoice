<?php
// Execute the actual helpers with a simulated Google transport; never load CRM data or solve CAPTCHA.
namespace RecaptchaRegression;

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
define('APP_BASE_URL', $argv[1] ?? 'https://crm.justsmartchoice.com/');

$secret = 'fixture-secret&encoded';
$response = json_encode(['success' => true, 'hostname' => parse_url(APP_BASE_URL, PHP_URL_HOST)]);
$httpStatus = 200;
$calls = [];
$messages = [];
function &get_instance() {
    static $ci;
    if (!$ci) {
        $ci = (object) [
            'load' => new class { public function library($name) {} },
            'input' => new class { public function ip_address() { return '192.0.2.10'; } },
            'form_validation' => new class {
                public function set_message($name, $message) { global $messages; $messages[] = [$name, $message]; }
            },
        ];
    }
    return $ci;
}
function get_option($name) { global $secret; return ['recaptcha_secret_key' => $secret, 'recaptcha_site_key' => 'fixture-site', 'recaptcha_ignore_ips' => ''][$name] ?? ''; }
function _l($key) { return $key; }
function curl_init($url) { global $calls; $calls[] = ['url' => $url]; return new \stdClass(); }
function curl_setopt_array($handle, $options) { global $calls; $calls[count($calls) - 1]['options'] = $options; return true; }
function curl_exec($handle) { global $response; return $response; }
function curl_getinfo($handle, $option) { global $httpStatus; return $httpStatus; }
function curl_close($handle) {}
function verify($condition, $message) { if (!$condition) { throw new \RuntimeException($message); } }

$root = dirname(__DIR__);
foreach (['general_helper.php' => ['do_recaptcha_validation'], 'misc_helper.php' => ['is_local_recaptcha_bypass_enabled', 'show_recaptcha']] as $helper => $functions) {
    $source = file_get_contents($root . '/application/helpers/' . $helper);
    foreach ($functions as $function) {
        verify(preg_match('/^function ' . preg_quote($function, '/') . '\\b.*?^}/ms', $source, $match) === 1, 'Helper function not found');
        eval('namespace ' . __NAMESPACE__ . ';' . $match[0]);
    }
}

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$local = in_array(trim((string) parse_url(APP_BASE_URL, PHP_URL_HOST), '[]'), ['localhost', '127.0.0.1', '::1'], true);
verify(is_local_recaptcha_bypass_enabled() === $local, 'Request headers must not enable a production exemption');
verify(show_recaptcha() === !$local, 'Configured production CAPTCHA must stay enabled under spoofed host headers');
echo 'PASS trusted installation URL controls local exemption: ' . APP_BASE_URL . "\n";
if ($local) { exit; }

foreach (['', null, ['forged'], false] as $token) {
    verify(do_recaptcha_validation($token) === false, 'Missing or non-string tokens must be rejected');
}
verify(count($calls) === 0, 'Missing tokens should not make Google requests');
verify(count($messages) === 4, 'Rejected tokens must produce native validation feedback');
$secret = '';
verify(do_recaptcha_validation('sample-token') === false && count($calls) === 0, 'Missing secret must fail closed');
$secret = 'fixture-secret&encoded';
$token = 'sample-token&secret=override';
$expectedHost = parse_url(APP_BASE_URL, PHP_URL_HOST);
verify(do_recaptcha_validation($token, $expectedHost) === true, 'Valid same-site Google result must be accepted');
$request = end($calls);
verify($request['url'] === 'https://www.google.com/recaptcha/api/siteverify', 'Secret must not appear in request URL');
verify($request['options'][CURLOPT_POST] === true, 'Google verification must use POST');
parse_str($request['options'][CURLOPT_POSTFIELDS], $payload);
verify($payload === ['secret' => $secret, 'response' => $token, 'remoteip' => '192.0.2.10'], 'Token must not inject or replace request parameters');
verify($request['options'][CURLOPT_SSL_VERIFYPEER] === true && $request['options'][CURLOPT_SSL_VERIFYHOST] === 2, 'HTTPS verification must remain enabled');

foreach ([
    ['{"success":false,"error-codes":["invalid-input-response"]}', 200],
    ['{"success":false,"error-codes":["timeout-or-duplicate"]}', 200],
    ['{"success":true,"hostname":"other.example"}', 200],
    ['{"success":true}', 200],
    ['{"success":"true","hostname":"crm.justsmartchoice.com"}', 200],
    ['{"success":true,"hostname":["crm.justsmartchoice.com"]}', 200],
    ['not JSON', 200],
    ['"not an object"', 200],
    [false, 0],
    ['{"success":true,"hostname":"crm.justsmartchoice.com"}', 500],
] as [$response, $httpStatus]) {
    verify(do_recaptcha_validation('sample-token', $expectedHost) === false, 'Failed, malformed, expired, duplicate or wrong-site responses must fail closed');
}
$response = '{"success":true,"hostname":"embedded-form.example"}';
$httpStatus = 200;
verify(do_recaptcha_validation('sample-token') === true, 'Existing embedded form callers retain Google domain validation without a new CRM-only host restriction');
$controller = file_get_contents($root . '/application/controllers/Authentication.php');
verify(strpos($controller, 'do_recaptcha_validation($str, parse_url(APP_BASE_URL, PHP_URL_HOST))') !== false, 'Customer authentication must pass the trusted hostname');
echo "PASS token rejection, Google POST encoding, HTTPS, same-site validation and failure feedback\n";

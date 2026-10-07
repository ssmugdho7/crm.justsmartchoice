<?php
// CLI-only real controller/model/merge-field checks with synthetic contacts and intercepted mail.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
error_reporting(E_ALL & ~E_DEPRECATED);
set_error_handler(function ($level, $message, $file, $line) { if (error_reporting() & $level) { throw new ErrorException($message, 0, $level, $file, $line); } return false; });
class ResponseStop extends RuntimeException { public $kind; function __construct($kind, $code, $message) { parent::__construct($message, $code); $this->kind = $kind; } }
class ClientsController { public $clients_model; }
class App_Model { function __construct() {} function __get($key) { return get_instance()->{$key}; } }
function &get_instance() { return $GLOBALS['ci']; }
function _l($key) { return $key; }
function site_url($path = '') { return 'https://example.test/' . $path; }
function admin_url($path = '') { return site_url('admin/' . $path); }
function db_prefix() { return 'fixture_'; }
function redirect($url) { throw new ResponseStop('redirect', 302, $url); }
function show_404() { throw new ResponseStop('error', 404, 'Not found'); }
function show_error($message, $code = 500) { throw new ResponseStop('error', $code, $message); }
function set_alert($type, $message) { $GLOBALS['alerts'][] = [$type, $message]; }
function is_client_logged_in() { return $GLOBALS['loggedIn']; }
function total_rows($table, $where = []) { return $GLOBALS['needsApproval'] ? 1 : 0; }
function app_generate_hash() { return str_repeat('b', 32); }
function e($text, ...$args) { return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8'); }
function contact_consent_url($id) { return site_url('consent/' . $id); }
function get_country_short_name($id) { return 'US'; }
function get_custom_fields($type) { return []; }
function is_gdpr() { return false; }
function hooks() { static $hooks; return $hooks ?? ($hooks = new class {
    function apply_filters($name, $value, ...$args) { return $value; }
    function do_action($name, ...$args) { $GLOBALS['actions'][] = $name; }
}); }
$checks = 0;
function check($value, $message) { if (!$value) { throw new RuntimeException($message); } ++$GLOBALS['checks']; }
function contact($changes = []) { return (object) array_merge(['id' => 137, 'userid' => 8, 'firstname' => 'Test', 'lastname' => 'Customer', 'email' => 'customer@example.test', 'phonenumber' => '', 'title' => '', 'email_verified_at' => null, 'email_verification_key' => str_repeat('a', 32), 'email_verification_sent_at' => date('Y-m-d H:i:s')], $changes); }
require dirname(__DIR__) . '/application/controllers/Verification.php';
$controller = new Verification();
$controller->clients_model = new class {
    public $contact, $marked = [], $markSucceeds = true, $reads = 0;
    function get_contact($id) { ++$this->reads; return $this->contact; }
    function mark_email_as_verified($id) { $this->marked[] = $id; return $this->markSucceeds; }
};
function route($args, $record, $code, $kind = 'error') {
    global $controller, $alerts, $actions, $loggedIn, $needsApproval;
    $controller->clients_model->contact = $record; $controller->clients_model->marked = []; $controller->clients_model->reads = 0;
    $alerts = []; $actions = [];
    try { $controller->verify(...$args); throw new RuntimeException('No response'); }
    catch (ResponseStop $response) { check($response->getCode() === $code && $response->kind === $kind, 'Wrong verification HTTP response'); return $response; }
}
$loggedIn = false; $needsApproval = false;
foreach ([[], [null], ['bad'], ['0'], [-1], [[]]] as $args) {
    route($args, contact(), 404); check($controller->clients_model->reads === 0 && !$controller->clients_model->marked, 'Invalid ID accessed contact');
}
route(['137', str_repeat('a', 32)], null, 404);
foreach ([['137'], ['137', null], ['137', ''], ['137', []], ['137', 'wrong']] as $args) {
    route($args, contact(), 400); check(!$controller->clients_model->marked && !$actions, 'Missing/invalid token verified contact');
}
route(['137', ''], contact(['email_verification_key' => '']), 400); check(!$controller->clients_model->marked, 'Empty stored and supplied keys matched');
foreach ([null, '', 'invalid-date', date('Y-m-d H:i:s', time() - 3 * 86400)] as $date) {
    route(['137', str_repeat('a', 32)], contact(['email_verification_sent_at' => $date]), 410); check(!$controller->clients_model->marked, 'Missing/expired sent timestamp verified contact');
}
for ($i = 0; $i < 2; ++$i) {
    $loggedIn = (bool) $i;
    $response = route(['137'], contact(['email_verified_at' => '2026-10-07 12:00:00', 'email_verification_key' => null]), 302, 'redirect');
    check($alerts === [['info', 'email_already_verified']] && !$controller->clients_model->marked, 'Old keyless verified link not handled safely');
    check($response->getMessage() === site_url($loggedIn ? 'clients' : 'authentication'), 'Already-verified notice consumed by extra login redirect');
}
foreach ([false, true] as $loggedIn) {
    foreach ([false, true] as $needsApproval) {
        $response = route(['137', str_repeat('a', 32)], contact(), 302, 'redirect');
        check($controller->clients_model->marked === [137], 'Valid key must verify only target contact');
        check($response->getMessage() === site_url($loggedIn ? 'clients' : 'authentication'), 'Login redirect changed');
        check($actions === [$needsApproval ? 'contact_email_verified_but_requires_admin_confirmation' : 'contact_email_verified'], 'Approval/hook behavior changed');
    }
}
$controller->clients_model->markSucceeds = false;
route(['137', str_repeat('a', 32)], contact(), 503); check(!$actions && !$alerts, 'Failed database verification reported success');

class FixtureDB {
    public $contact, $filters = [], $updates = [], $fail = false, $race = false;
    function where($name, $value) { $this->filters[$name] = $value; return $this; }
    function or_where(...$args) { return $this; }
    function group_start() { return $this; } function group_end() { return $this; }
    function get($table) { $record = $this->contact && (!isset($this->filters['id']) || (int)$this->filters['id'] === (int)$this->contact->id) ? clone $this->contact : null; $this->filters = []; return new class($record) { private $record; function __construct($record) { $this->record = $record; } function row() { return $this->record; } }; }
    function update($table, $values) {
        $filters = $this->filters; $this->filters = []; if ($this->fail) { return false; }
        if ($this->race && isset($values['email_verification_key'])) { $this->contact->email_verification_key = str_repeat('c', 32); }
        if (isset($values['email_verification_key'])) {
            check(array_key_exists('email_verified_at', $filters) && array_key_exists('email_verification_key', $filters), 'Key repair must be conditional');
            if ($this->contact->email_verified_at !== null || !empty($this->contact->email_verification_key)) { return true; }
        }
        $this->updates[] = $values; foreach ($values as $key => $value) { $this->contact->{$key} = $value; } return true;
    }
}
require dirname(__DIR__) . '/application/models/Clients_model.php';
class SenderModel extends Clients_model {
    function __construct() {}
    function get($id = '', $where = []) { return (object) ['userid' => 8, 'vat' => '', 'company' => 'Sample Customer', 'phonenumber' => '', 'country' => 1, 'city' => '', 'zip' => '', 'state' => '', 'address' => '', 'website' => '']; }
}
$ci = (object) ['db' => new FixtureDB()]; $ci->clients_model = new SenderModel();
require dirname(__DIR__) . '/application/libraries/merge_fields/App_merge_fields.php';
require dirname(__DIR__) . '/application/libraries/merge_fields/Client_merge_fields.php';
class FixtureMerge extends Client_merge_fields { function __construct() { $this->ci = &get_instance(); } }
$mailSuccess = true; $mails = []; $merge = new FixtureMerge();
function send_mail_template($name, $contact) {
    global $merge, $mails, $mailSuccess;
    $url = $merge->format($contact->userid, $contact->id)['{email_verification_url}'];
    check($url === site_url('verification/verify/' . $contact->id . '/' . $contact->email_verification_key) && !empty($contact->email_verification_key), 'Email CTA missing persisted key');
    $mails[] = $url; return $mailSuccess;
}
foreach ([null, contact(['email' => '']), contact(['email_verified_at' => '2026-10-07 12:00:00', 'email_verification_key' => null])] as $record) {
    $ci->db->contact = $record; $ci->db->updates = []; $mails = [];
    check(!$ci->clients_model->send_verification_email(137) && !$mails && !$ci->db->updates, 'Non-actionable verification email sent');
}
foreach ([null, '', str_repeat('a', 32)] as $key) {
    $ci->db->contact = contact(['email_verification_key' => $key]); $ci->db->updates = []; $mails = []; $mailSuccess = true;
    check($ci->clients_model->send_verification_email(137) && count($mails) === 1, 'Pending contact did not receive usable CTA');
    check($ci->db->contact->email_verification_key === ($key ?: str_repeat('b', 32)), 'Existing token rotated');
    check($ci->db->contact->email_verified_at === null, 'Sending email must not verify contact');
    foreach ($ci->db->updates as $update) { check(!array_diff(array_keys($update), ['email_verification_key', 'email_verification_sent_at']), 'Unrelated contact fields changed'); }
}
$ci->db->contact = contact(['email_verification_key' => null]); $ci->db->race = true; $mails = [];
check($ci->clients_model->send_verification_email(137) && $ci->db->contact->email_verification_key === str_repeat('c', 32) && substr($mails[0], -32) === str_repeat('c', 32), 'Concurrent issuance overwrote or sent stale key'); $ci->db->race = false;
$ci->db->contact = contact(['email_verification_key' => null]); $ci->db->fail = true; $mails = [];
check(!$ci->clients_model->send_verification_email(137) && !$mails, 'Failed key persistence sent email'); $ci->db->fail = false;
$oldDate = date('Y-m-d H:i:s', time() - 86400); $ci->db->contact = contact(['email_verification_sent_at' => $oldDate]); $mailSuccess = false;
check(!$ci->clients_model->send_verification_email(137) && $ci->db->contact->email_verification_sent_at === $oldDate, 'Mail failure extended verification expiry');
foreach ([contact(['email_verification_key' => null]), contact(['email_verified_at' => '2026-10-07 12:00:00']), contact(['email_verified_at' => '2026-10-07 12:00:00', 'email_verification_key' => null])] as $record) {
    $ci->db->contact = $record; check($merge->format(8, 137)['{email_verification_url}'] === '', 'Merge fields emitted a keyless/stale verification link');
}
echo "PASS: $checks verification routing, token/expiry, approval, sender, merge fields and failure checks (no mail sent or database used)\n";

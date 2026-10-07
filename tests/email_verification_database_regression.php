<?php
// Every write is to a connection-local TEMPORARY table; emails are intercepted.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__); define('BASEPATH', $root . '/system/'); define('ENVIRONMENT', 'testing');
function log_message(...$args) {} function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function show_error($message) { throw new RuntimeException($message); } function db_prefix() { return 'audit_verification_'; }
function &get_instance() { return $GLOBALS['ci']; }
class App_Model { function __construct() {} function __get($key) { return get_instance()->{$key}; } }
function app_generate_hash() { return bin2hex(random_bytes(16)); }
$checks = 0; $mailSuccess = true; $mails = 0;
function check($value, $message) { if (!$value) { throw new RuntimeException($message); } ++$GLOBALS['checks']; }
function send_mail_template($name, $contact) {
    ++$GLOBALS['mails']; $stored = get_instance()->db->where('id', $contact->id)->get(db_prefix() . 'contacts')->row();
    check(!empty($stored->email_verification_key) && $stored->email_verification_key === $contact->email_verification_key && $stored->email_verified_at === null, 'Sender did not use persisted unverified token');
    return $GLOBALS['mailSuccess'];
}
$site = getenv('CRM_SITE_ROOT'); if (!$site || !is_file($site . '/application/config/app-config.php')) { throw new RuntimeException('Set CRM_SITE_ROOT to a private checkout'); }
require $site . '/application/config/app-config.php'; require BASEPATH . 'database/DB.php';
$db = DB(['hostname' => APP_DB_HOSTNAME, 'username' => APP_DB_USERNAME, 'password' => APP_DB_PASSWORD, 'database' => APP_DB_NAME, 'dbdriver' => 'mysqli', 'dbprefix' => db_prefix(), 'pconnect' => false, 'db_debug' => false, 'char_set' => 'utf8mb4', 'dbcollat' => 'utf8mb4_general_ci', 'save_queries' => false], true);
$ci = (object) ['db' => $db];
$db->query('CREATE TEMPORARY TABLE audit_verification_contacts (id INT PRIMARY KEY, email VARCHAR(191), email_verified_at DATETIME NULL, email_verification_key VARCHAR(191) NULL, email_verification_sent_at DATETIME NULL)');
require $root . '/application/models/Clients_model.php';
class DatabaseSender extends Clients_model {
    public $stale = null;
    function __construct() {}
    function get_contact($id) { if ($this->stale) { $value = $this->stale; $this->stale = null; return $value; } return parent::get_contact($id); }
}
$model = new DatabaseSender();
foreach ([1 => null, 2 => '', 3 => str_repeat('a', 32)] as $id => $key) {
    $db->insert(db_prefix() . 'contacts', ['id' => $id, 'email' => 'fixture@example.test', 'email_verification_key' => $key]);
    check($model->send_verification_email($id), 'Native sender failed'); $stored = $model->get_contact($id);
    check(strlen($stored->email_verification_key) === 32 && ($id !== 3 || $stored->email_verification_key === $key), 'Native key repair/retention failed');
    check($stored->email_verified_at === null && !empty($stored->email_verification_sent_at), 'Native sender changed verified status or omitted sent date');
}
$db->insert(db_prefix() . 'contacts', ['id' => 4, 'email' => 'fixture@example.test', 'email_verified_at' => '2026-10-07 12:00:00']); $beforeMails = $mails;
check(!$model->send_verification_email(4) && $mails === $beforeMails && $model->get_contact(4)->email_verification_key === null, 'Verified contact received mail or token');
$db->insert(db_prefix() . 'contacts', ['id' => 5, 'email' => 'fixture@example.test', 'email_verification_key' => str_repeat('c', 32)]);
$model->stale = (object) ['id' => 5, 'email' => 'fixture@example.test', 'email_verified_at' => null, 'email_verification_key' => null];
check($model->send_verification_email(5) && $model->get_contact(5)->email_verification_key === str_repeat('c', 32), 'Conditional SQL overwrote concurrently issued token');
$db->insert(db_prefix() . 'contacts', ['id' => 6, 'email' => 'fixture@example.test', 'email_verified_at' => '2026-10-07 12:00:00']);
$model->stale = (object) ['id' => 6, 'email' => 'fixture@example.test', 'email_verified_at' => null, 'email_verification_key' => null]; $beforeMails = $mails;
check(!$model->send_verification_email(6) && $mails === $beforeMails && $model->get_contact(6)->email_verification_key === null, 'Concurrent verification issued a new token');
$oldDate = '2026-10-07 12:00:00'; $db->where('id', 3)->update(db_prefix() . 'contacts', ['email_verification_sent_at' => $oldDate]); $mailSuccess = false;
check(!$model->send_verification_email(3) && $model->get_contact(3)->email_verification_sent_at === $oldDate, 'Mail failure extended expiry');
$db->close(); echo "PASS: $checks native SQL sender/key/race checks (temporary tables only; no mail sent)\n";

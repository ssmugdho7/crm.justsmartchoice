<?php
define('BASEPATH', __DIR__);
require dirname(__DIR__) . '/modules/smart_square_payments/helpers/smart_square_payments_helper.php';
function get_option($key) { return ''; }
function db_prefix() { return 'test_'; }
function set_mailing_constant() {}
function log_message($l, $m) {}
function is_staff_logged_in() { return false; }
function payment_pdf($payment) { return new class { public function Output($name, $mode) { return 'fixture-pdf'; } }; }
$sent = [];
class Invoice_payment_recorded_to_staff {
    protected $payment_id;
    protected $args;
    public $rel_type = 'staff', $rel_id;
    public function __construct(...$args) { $this->args = $args; $this->payment_id = $args[4]; }
    public function build() { $this->rel_id = $this->args[1]; }
    public function set_rel_id($id) { $this->rel_id = $id; }
    public function send() { global $sent; $this->build(); $sent[] = $this->args; verify($this->rel_type === 'invoice_payment' && $this->rel_id === $this->payment_id, 'Deduplication identity must be per payment'); return true; }
}
function mail_template($name, ...$args) { return new Invoice_payment_recorded_to_staff(...$args); }
class ReceiptDb {
    public $claimed = false, $affected = 0, $status;
    public function where(...$a) { return $this; }
    public function get($t) { return $this; }
    public function row() { return null; }
    public function table_exists($t) { return true; }
    public function insert_string($t, $r) { return 'claim'; }
    public function query($sql) { $this->affected = $this->claimed ? 0 : 1; $this->claimed = true; }
    public function affected_rows() { return $this->affected; }
    public function update($t, $r) { $this->status = $r['status']; }
}
class ReceiptPayments { public $mode = 'smart_square'; public function get($id) { return (object) ['paymentmode' => $this->mode, 'invoiceid' => 123]; } }
class ReceiptInvoices { public function get($id) { return (object) ['id' => $id, 'addedfrom' => 18, 'sale_agent' => 0]; } }
function &get_instance() { global $ci; return $ci; }
$ci = (object) ['db' => new ReceiptDb, 'payments_model' => new ReceiptPayments, 'invoices_model' => new ReceiptInvoices,
    'load' => new class { public function model($n) {} }];
function verify($ok, $message) { if (!$ok) { throw new RuntimeException($message); } }
verify(smart_square_send_admin_receipt(52), 'Admin receipt should send for confirmed CRM payment');
verify(count($sent) === 1 && $sent[0][0] === 'admin@justsmartchoice.com', 'Receipt must target administration mailbox');
verify($sent[0][3] === 'fixture-pdf' && $ci->db->status === 'sent', 'Receipt should include PDF and record delivery acceptance');
verify(!smart_square_send_admin_receipt(52) && count($sent) === 1, 'Duplicate payment hook must not send twice');
$ci->payments_model->mode = 'other_gateway';
verify(!smart_square_send_admin_receipt(53) && count($sent) === 1, 'Other gateways keep existing notification behavior');
echo "Square admin receipt recipient, PDF, deduplication, and gateway checks passed.\n";

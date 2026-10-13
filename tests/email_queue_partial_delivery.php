<?php
// Reuse the real mailer bootstrap and recording transport; never sends external mail.
require __DIR__ . '/sales_email_delivery_regression.php';
class QueueRecordingDb {
    public $values = [], $updates = [];
    function where($key, $value = null) { return $this; }
    function set($key, $value) { $this->values[$key] = $value; return $this; }
    function update($table) { $this->updates[] = $this->values; $this->values = []; return true; }
}

class QueueDeliveryFixture extends App_Email {
    public $rows = [];
    function clean_up_old_queue() {}
    function get_queue_emails($limit = null, $offset = null) { return $this->rows; }
}
foreach (['cc-only', false, true] as $outcome) {
    $ci->db = new QueueRecordingDb();
    $queue = new QueueDeliveryFixture(['useragent'=>'phpmailer','protocol'=>'smtp','mailtype'=>'html']);
    $queue->phpmailer = new RecordingTransport();
    $queue->phpmailer->outcomes = [$outcome];
    $queue->rows = [(object)[
        'id'=>1, 'engine'=>'phpmailer', 'email'=>'to@example.test', 'cc'=>'CC@example.test',
        'bcc'=>'copy@example.test', 'headers'=>serialize(['from'=>'sender@example.test','fromName'=>'Test','subject'=>'Queue test']),
        'attachments'=>'', 'message'=>'Synthetic test', 'alt_message'=>'Synthetic test',
    ]];
    $queue->send_queue();
    $update = end($ci->db->updates);
    check($update['status'] === ($outcome === true ? 'sent' : 'failed'), 'queue status after '.var_export($outcome, true));
    if ($outcome === 'cc-only') {
        check($update['cc'] === '' && $update['email'] === 'to@example.test' && $update['bcc'] === 'copy@example.test', 'retry only rejected recipients, case insensitive');
    } elseif ($outcome === false) {
        check(!isset($update['email']), 'complete failure retains original recipients');
    }
}

// Follow two actual PHPMailer SMTP attempts: DATA succeeds for CC/BCC while
// the primary recipient is rejected. Neither accepted copy may be sent again.
class QueueRetrySmtpFixture extends PartialSmtpFixture {
    public $accepted = [], $deliveries = [];
    function mail($from) { $this->accepted = []; return parent::mail($from); }
    function recipient($address, $dsn = '') {
        $ok = parent::recipient($address, $dsn);
        if ($ok) $this->accepted[] = $address;
        return $ok;
    }
    function data($message) { $this->deliveries[] = $this->accepted; return parent::data($message); }
}
$ci->db = new QueueRecordingDb();
$queue = new QueueDeliveryFixture(['useragent'=>'phpmailer','protocol'=>'smtp','mailtype'=>'html']);
$queue->phpmailer = new PHPMailer\PHPMailer\PHPMailer();
$queue->initialize(['useragent'=>'phpmailer','protocol'=>'smtp','smtp_host'=>'test.invalid','smtp_auto_tls'=>false,'mailtype'=>'html']);
$smtp = new QueueRetrySmtpFixture();
$queue->phpmailer->setSMTPInstance($smtp);
$queue->rows = [(object)[
    'id'=>2, 'engine'=>'phpmailer', 'email'=>'bad@example.test', 'cc'=>'cc@example.test',
    'bcc'=>'copy@example.test', 'headers'=>serialize(['from'=>'sender@example.test','fromName'=>'Test','subject'=>'One-time event']),
    'attachments'=>'', 'message'=>'Synthetic queue retry test', 'alt_message'=>'Synthetic queue retry test',
]];
$queue->send_queue();
$remaining = end($ci->db->updates);
check($remaining['email'] === 'bad@example.test' && $remaining['cc'] === '' && $remaining['bcc'] === '', 'real SMTP queue preserves rejected primary and removes accepted CC/BCC');
foreach (['email','cc','bcc'] as $field) $queue->rows[0]->$field = $remaining[$field];
$queue->send_queue();
check($smtp->deliveries === [['cc@example.test','copy@example.test']], 'repeated queue processing delivers each accepted copy exactly once');

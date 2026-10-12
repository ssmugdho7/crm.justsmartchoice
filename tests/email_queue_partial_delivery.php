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

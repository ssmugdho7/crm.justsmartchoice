<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
date_default_timezone_set('UTC');
$checks = 0;
function check($value, $label) { if (!$value) { throw new RuntimeException($label); } $GLOBALS['checks']++; }
function db_prefix() { return 'fixture_'; }
function get_option($key) { return $GLOBALS['emailEnabled'] ?? '1'; }
function _d($date) { return $date; }
function get_staff_full_name($id) { return 'Fixture Installer'; }
function log_message(...$args) {}
#[AllowDynamicProperties] class App_Model {}
class FixtureDB {
    public $rows = [], $filters = [], $lock = 1, $releases = 0;
    function table_exists($table) { return true; }
    function field_exists($field, $table) { return true; }
    function query($sql, $args) {
        if (strpos($sql, 'RELEASE_LOCK') !== false) { $this->releases++; }
        return new FixtureResult((object)['acquired'=>$this->lock]);
    }
    function where($key, $value) { $this->filters[$key] = $value; return $this; }
    function order_by(...$args) { return $this; }
    function get($table) {
        $match = null;
        foreach ($this->rows as $row) {
            foreach ($this->filters as $k=>$v) { if (($row[$k] ?? null) != $v) { continue 2; } }
            $match = (object)$row;
        }
        $this->filters = []; return new FixtureResult($match);
    }
    function insert($table, $row) { $row['id']=count($this->rows)+1; $this->rows[]=$row; return true; }
    function insert_id() { return count($this->rows); }
    function update($table, $values) {
        foreach ($this->rows as &$row) { if ($row['id']==($this->filters['id']??0)) { $row=array_merge($row,$values); } }
        $this->filters=[]; return true;
    }
}
class FixtureResult { private $row; function __construct($row){$this->row=$row;} function row(){return $this->row;} }
function mail_template($class, $module, $appointment, $status, $message) {
    check($class==='appointly_dispatch_customer_status', 'Real dispatch mail class is used');
    $GLOBALS['payload'] = compact('appointment','status','message');
    return new class { function send() { $GLOBALS['sends']++; if (!empty($GLOBALS['throwMail'])) { throw new RuntimeException('Transport unavailable'); } return $GLOBALS['sendOK']; } };
}
require dirname(__DIR__).'/modules/appointly/models/Appointly_dispatch_model.php';
$model=new Appointly_dispatch_model(); $model->db=new FixtureDB();
$a=(object)['id'=>7,'provider_id'=>2,'email'=>'test@example.invalid','source'=>'external', 'subject'=>'TEST appointment', 'status'=>'pending','date'=>'2026-10-14','start_hour'=>'10:00','end_hour'=>'11:00'];
$sends=0; $sendOK=true;
check($model->scheduled($a)==='accepted', 'Pending booking sends email');
check(strpos($payload['message'],'awaiting confirmation')!==false, 'Pending does not claim confirmation');
check($model->scheduled($a)==='duplicate' && $sends===1, 'Identical booking is deduplicated');
$a->status='approved'; check($model->scheduled($a)==='accepted', 'Approval sends distinct confirmation');
check(strpos($payload['message'],'2026-10-14 10:00 - 11:00 (UTC)')!==false, 'Email includes schedule and timezone');
$a->start_hour='12:00'; check($model->scheduled($a)==='accepted', 'Rescheduling sends changed time');
check($model->notify($a,'on-the-way',10)==='accepted', 'Staff departure supports ten-minute ETA');
check(strpos($payload['message'],'confirmed departure')!==false && strpos($payload['message'],'10 minutes')!==false, 'Departure and ETA are explicit');
check($model->notify($a,'on-the-way',10)==='duplicate', 'Repeated departure does not resend');
check($model->notify($a,'on-the-way',15)==='accepted', 'Changed ETA sends update');
check($model->notify($a,'15-minutes-away')==='accepted', 'Explicit fifteen-minute action');
foreach ([null,0,-1,241,1.5] as $eta) {
    try { $model->notify($a,'on-the-way',$eta); throw new RuntimeException('Invalid ETA accepted'); }
    catch (InvalidArgumentException $e) { check(true, 'Invalid ETA rejected'); }
}
$a->provider_id=0; check($model->notify($a,'on-the-way',10)==='no-installer', 'Departure needs assigned staff');
$a->provider_id=2; $a->email='not-email'; check($model->scheduled($a)==='no-recipient', 'Invalid recipient blocked');
$a->email='test@example.invalid'; $a->source='internal_staff'; check($model->scheduled($a)==='no-recipient', 'Internal staff appointment never emails customer');
$a->source='external'; $emailEnabled='0'; check($model->scheduled($a)==='disabled', 'Email disable respected'); $emailEnabled='1';
$model->db->lock=0; check($model->notify($a,'arrived')==='busy', 'Concurrent request cannot send without lock'); $model->db->lock=1;
$sendOK=false; check($model->notify($a,'arrived')==='failed', 'Transport failure logged');
check(end($model->db->rows)['status']==='failed', 'Log never reports failed transport as sent');
$sendOK=true; check($model->notify($a,'arrived')==='accepted', 'Failed email can be retried');
$throwMail=true; check($model->notify($a,'completed')==='failed', 'Mailer exception is contained'); $throwMail=false;
check($model->db->releases>0, 'Database locks released on failures');
$model->db->rows[count($model->db->rows)-1]['status']='processing';
check($model->notify($a,'completed')==='processing', 'Ambiguous previous attempt cannot cause blind duplicate');
$a->status='cancelled'; check($model->scheduled($a)==='skipped', 'Cancelled record cannot send scheduling confirmation');
function html_escape($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function staff_profile_image_url($id, $size) { return 'https://example.invalid/installer.png'; }
class App_mail_template {
    public $fields = [], $recipient;
    function __construct() {}
    function set_merge_fields($fields) { $this->fields = $fields; }
    function to($email) { $this->recipient = $email; }
    function immediate() { return $this->skipQueue; }
}
require dirname(__DIR__).'/modules/appointly/libraries/mails/Appointly_dispatch_customer_status.php';
$a->subject='<script>private</script>'; $a->address='123 Example Street';
$mail = new Appointly_dispatch_customer_status($a, 'on-the-way', 'Installer <unsafe> is ten minutes away.');
$mail->build();
check($mail->immediate() === true, 'Dispatch is sent immediately rather than queued');
check($mail->recipient === $a->email, 'Actual template selects customer recipient');
check($mail->fields['{appointment_staff_photo}']==='https://example.invalid/installer.png', 'Assigned installer photo merge field populated');
check(strpos($mail->fields['{appointment_subject}'],'<script>')===false && strpos($mail->fields['{appointment_dispatch_message}'],'<unsafe>')===false, 'Customer content escaped in email');
check(strpos($mail->fields['{appointment_map_link}'],'https://www.google.com/maps/dir/?api=1')===0, 'Address produces encoded map link');
echo "PASS: $checks dispatch scheduling, ETA, retry, recipient and deduplication checks; no emails sent.\n";

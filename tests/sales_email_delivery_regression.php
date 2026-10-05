<?php
// Real mailer, mail templates and sales models; no external mail, database or customer records.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('ENVIRONMENT', 'production');
define('FCPATH', dirname(__DIR__) . '/');
define('APPPATH', FCPATH . 'application/');
define('BASEPATH', FCPATH . 'system/');
require APPPATH . 'vendor/autoload.php';
function config_item($name) { return ['charset'=>'UTF-8','newline'=>"\r\n",'crlf'=>"\r\n"][$name] ?? null; }
function get_option($name) { return ['email_queue_enabled'=>'1','email_queue_skip_with_attachments'=>'0','smtp_email'=>'sender@example.test','companyname'=>'Test CRM','active_language'=>'english'][$name] ?? ''; }
function db_prefix() { return 'tbl'; }
function is_php($v) { return version_compare(PHP_VERSION,$v,'>='); }
function log_message($level,$message) {}
$activities=[];
function log_activity($message) { global $activities; $activities[]=$message; }
function html_escape($s) { return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
function is_staff_logged_in() { return false; }
function total_rows($table,$where=[]) { return 0; }
function base_url($path='') { return 'https://portal.example.test/'.$path; }
function parse_email_template($t,$fields=[]) { return $t; }
function parse_email_template_merge_fields($t,$fields=[]) { return $t; }
function check_for_links($s) { return $s; }
function strip_html_tags($s,$tags='') { return strip_tags($s); }
function clear_textarea_breaks($s,$newline) { return str_replace('<br>',$newline,$s); }
function hooks() { static $h; return $h ??= new class { function apply_filters($name,$value,...$args) { return $value; } function do_action($name,...$args) {} }; }
function &get_instance() { global $ci; return $ci; }
class MailFixtureDb {
    public $queued=[],$updates=[];
    function select($s) { return $this; }
    function where($key,$value=null) { return $this; }
    function get($table) { return new class { function row() { return null; } }; }
    function insert($table,$row) { $this->queued[]=$row; return true; }
    function update($table,$row) { $this->updates[]=[$table,$row]; return true; }
}
$ci=(object)['db'=>new MailFixtureDb(), 'input'=>new class { public $contacts=[]; function post($key) { return $key==='sent_to'?$this->contacts:false; } },
 'load'=>new class { function helper($name) { require_once BASEPATH.'helpers/'.$name.'_helper.php'; } function language($name) {} function config($name) {} },
 'lang'=>new class { function load($name) {} function line($key) { return $key; } },
 'app_merge_fields'=>new class { function format_feature($name,...$args) { return []; } }];
require BASEPATH.'libraries/Email.php';
require APPPATH.'libraries/App_Email.php';
require APPPATH.'libraries/mails/App_mail_template.php';
class RecordingTransport extends PHPMailer\PHPMailer\PHPMailer {
    public $packets=[], $outcomes=[];
    function postSend() {
        $outcome=array_shift($this->outcomes) ?? true;
        $accepted=$outcome===true;
        $acceptedCc=$outcome==='cc-only'? $this->getCcAddresses() : ($accepted?$this->getCcAddresses():[]);
        $this->doCallback($accepted,$accepted?$this->getToAddresses():[],$acceptedCc,[],$this->Subject,$this->Body,$this->From,[]);
        if ($outcome==='cc-only') $this->doCallback(true,[], $acceptedCc, [],$this->Subject,$this->Body,$this->From,[]);
        $this->packets[]=['to'=>array_column($this->getToAddresses(),0),'cc'=>array_column($this->getCcAddresses(),0),'mime'=>$this->getSentMIMEMessage(),'accepted'=>$accepted];
        if (!$accepted) $this->ErrorInfo='SMTP authentication failed';
        return $accepted;
    }
}
$mail = new App_Email(['useragent'=>'phpmailer','protocol'=>'smtp','mailtype'=>'html']);
$mail->phpmailer=new RecordingTransport();
$ci->email=$mail;
$callbackCount=0;
$callback=static function (...$args) use (&$callbackCount) { $callbackCount++; };
$mail->phpmailer->action_function=$callback;
trait SyntheticTemplate {
    public function prepare($email=null,$template=null,$params=[]) {
        return (object)['name'=>'Delivery test','subject'=>'TEST '.$this->slug,'message'=>'SYNTHETIC_PRIVATE_BODY','plaintext'=>0,'active'=>1,'fromname'=>'Test CRM','language'=>'english'];
    }
}
$classes=['Proposal_send_to_customer','Estimate_send_to_customer','Estimate_send_to_customer_already_sent','Invoice_send_to_customer','Invoice_send_to_customer_already_sent'];
foreach($classes as $name) { require APPPATH.'libraries/mails/'.$name.'.php'; eval('class Fixture_'.$name.' extends '.$name.' { use SyntheticTemplate; }'); }
function check($ok,$name) { if (!$ok) throw new RuntimeException($name); echo 'PASS '.$name."\n"; }
function set_mailing_constant() {}
function slug_it($s) { return 'test-document'; }
function proposal_pdf($document) { return new class { function Output($name,$mode) { return "%PDF-1.4\nSynthetic delivery test\n%%EOF"; } }; }
function estimate_pdf($document) { return proposal_pdf($document); }
function invoice_pdf($document) { return proposal_pdf($document); }
$document=(object)['id'=>91,'clientid'=>3,'email'=>'to@example.test','subject'=>'Test proposal','rel_type'=>'customer','status'=>6,'sent'=>0];
$contact=(object)['id'=>8,'email'=>'to@example.test'];
foreach($classes as $name) {
    $class='Fixture_'.$name;
    foreach([false,true] as $accepted) {
        $mail->phpmailer->outcomes=[$accepted];
        $template=$name==='Proposal_send_to_customer'?new $class($document,true,'cc@example.test'):new $class($document,$contact,'cc@example.test');
        if ($name!=='Proposal_send_to_customer') $template->add_attachment(['attachment'=>proposal_pdf($document)->Output('test.pdf','S'),'filename'=>'test.pdf','type'=>'application/pdf']);
        $before=count($ci->db->queued);
        check($template->send()===$accepted,$name.' returns actual transport result '.($accepted?'accepted':'rejected'));
        check(count($ci->db->queued)===$before,$name.' bypasses enabled queue');
        $packet=end($mail->phpmailer->packets);
        check($packet['to']===['to@example.test']&&$packet['cc']===['cc@example.test'], $name.' retains To and CC');
        check(strpos($packet['mime'],'Cc: cc@example.test')!==false && strpos($packet['mime'],'application/pdf')!==false && strpos($packet['mime'],base64_encode("%PDF-1.4\nSynthetic delivery test\n%%EOF"))!==false,$name.' builds CC header and intact PDF');
    }
}
check($callbackCount===10 && $mail->phpmailer->action_function===$callback,'existing mail callbacks run and are restored');
$failed=array_filter($activities,fn($a)=>str_starts_with($a,'Failed to send email template'));
check(count($failed)===5 && strpos(implode('\n',$failed),'SMTP authentication failed')!==false && strpos(implode('\n',$failed),'SYNTHETIC_PRIVATE_BODY')===false,'production failure logging omits body and headers');
$mail->clear(true)->from('sender@example.test')->to(['First Person <first@example.test>','second@example.test'])->cc('cc@example.test')->bcc('bcc@example.test')->subject('Queue preservation')->message('Test');
check($mail->send()===true,'non-document mail still uses enabled queue');
$queued=end($ci->db->queued);
check($queued['email']==='first@example.test, second@example.test' && $queued['cc']==='cc@example.test' && $queued['bcc']==='bcc@example.test','queue serializes every address without display names');
class App_Model { public $db,$input,$clients_model; function __construct() {} }
require APPPATH.'models/Estimates_model.php';
require APPPATH.'models/Invoices_model.php';
require APPPATH.'models/Proposals_model.php';
class FixtureEstimates extends Estimates_model { public $record,$marked=false; function get($id='',$where=[]) { return clone $this->record; } function set_estimate_sent($id,$emails=[]) { $this->marked=true; } }
class FixtureInvoices extends Invoices_model { public $record,$marked=false; function get($id='',$where=[]) { return clone $this->record; } function is_draft($id) { return false; } function set_invoice_sent($id,$manually=false,$emails=[],$is_status_updated=false) { $this->marked=true; } }
class FixtureProposals extends Proposals_model { public $record; function get($id='',$where=[],$for_editor=false) { return clone $this->record; } }
function format_estimate_number($id) { return 'EST-TEST'; }
function format_invoice_number($id) { return 'INV-TEST'; }
function update_invoice_status($id,...$args) { return false; }
function sc_attach_sales_files_to_mail_template($template,$type,$id) {}
function mail_template($name,...$args) { $class='Fixture_'.ucfirst($name); return new $class(...$args); }
$clients=new class { function get_contact($id) { return $id===0?null:(object)['id'=>$id,'email'=>'contact'.$id.'@example.test']; } };
foreach(['estimate','invoice'] as $type) {
    $class=$type==='estimate'?'FixtureEstimates':'FixtureInvoices'; $model=new $class();
    $model->record=clone $document; $model->record->status=2;
    $model->db=$ci->db;$model->input=$ci->input;$model->clients_model=$clients;
    $ci->input->contacts=[0,1,2,3]; $mail->phpmailer->outcomes=[false,true,true];
    $before=count($mail->phpmailer->packets);
    $method='send_'.$type.'_to_client';
    check($model->$method(91,'',true,'cc@example.test')===true && $model->marked,$type.' handles invalid and rejected contacts before success');
    $packets=array_slice($mail->phpmailer->packets,$before);
    check(count($packets)===3 && $packets[0]['cc']===['cc@example.test'] && $packets[1]['cc']===['cc@example.test'] && $packets[2]['cc']===[],$type.' CC retries after rejection and stops after acceptance');
    $ci->input->contacts=[1,2];$mail->phpmailer->outcomes=['cc-only',true];
    $before=count($mail->phpmailer->packets);
    check($model->$method(91,'',true,'cc@example.test')===true,$type.' handles partial SMTP rejection');
    $packets=array_slice($mail->phpmailer->packets,$before);
    check($packets[0]['cc']===['cc@example.test'] && $packets[1]['cc']===[],$type.' avoids duplicate CC after partial acceptance');
    $model->marked=false;$ci->input->contacts=[1];$mail->phpmailer->outcomes=[false];
    check($model->$method(91,'',true,'cc@example.test')===false && !$model->marked,$type.' is not marked sent after SMTP rejection');
}
$proposal=new FixtureProposals();$proposal->record=clone $document;$proposal->db=$ci->db;$ci->db->updates=[];$mail->phpmailer->outcomes=[false];
check($proposal->send_proposal_to_email(91,true,'cc@example.test')===false && $ci->db->updates===[],'rejected proposal preserves draft status');
$mail->phpmailer->outcomes=[true];
check($proposal->send_proposal_to_email(91,true,'cc@example.test')===true && end($ci->db->updates)[1]['status']===4,'accepted proposal updates sent status');
echo "PASS sales email delivery regressions; no external messages sent\n";
// Exercise PHPMailer's real SMTP postSend callbacks with a simulated SMTP server.
class PartialSmtpFixture extends PHPMailer\PHPMailer\SMTP {
    public $open=false,$acceptData=true,$dataAttempts=0;
    function connected() { return $this->open; }
    function connect($host,$port=null,$timeout=30,$options=[]) { return $this->open=true; }
    function hello($host='') { return true; }
    function mail($from) { return true; }
    function recipient($address,$dsn='') {
        if ($address==='bad@example.test') { $this->setError('Recipient rejected','Invalid address','550'); return false; }
        return true;
    }
    function data($message) { $this->dataAttempts++; return $this->acceptData; }
    function quit($close_on_error=true) { $this->open=false; return true; }
    function close() { $this->open=false; }
    function getLastTransactionID() { return 'SYNTHETIC-SMTP-ID'; }
}
$mail->phpmailer=new PHPMailer\PHPMailer\PHPMailer();
$mail->initialize(['useragent'=>'phpmailer','protocol'=>'smtp','smtp_host'=>'test.invalid','smtp_auto_tls'=>false,'mailtype'=>'html']);
$smtp=new PartialSmtpFixture();$mail->phpmailer->setSMTPInstance($smtp);
foreach([true,false] as $acceptData) {
    $smtp->acceptData=$acceptData;
    $mail->clear(true)->from('sender@example.test')->to('bad@example.test')->cc('cc@example.test')->subject('Partial delivery')->message('Synthetic content');
    check($mail->send(true)===false,'real PHPMailer reports partial recipient rejection');
    check($mail->get_accepted_recipients()===($acceptData?['cc@example.test']:[]),'recipient receipt recorded only after successful SMTP DATA');
}
check($smtp->dataAttempts===2,'SMTP transcript exercises both accepted and rejected DATA');

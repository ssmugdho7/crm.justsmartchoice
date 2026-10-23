<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// No application bootstrap or database: exercise filesystem boundaries and authenticated collection.
define('BASEPATH', __DIR__);
require dirname(__DIR__).'/modules/debug_mode/libraries/Debug_mode_module_auditor.php';
$root=sys_get_temp_dir().'/crm-audit-'.bin2hex(random_bytes(5));mkdir($root);mkdir($root.'/example');
function check($ok,$label){if(!$ok)throw new RuntimeException($label);echo "PASS $label\n";}
try {
 file_put_contents($root.'/example/example.php','<?php $safe = 1;');
 file_put_contents($root.'/example/broken.php','<?php function broken( {');
 mkdir($root.'/example/vendor');file_put_contents($root.'/example/vendor/ignored.php','<?php syntax error !');
 symlink('/etc',$root.'/example/external');
 $result=Debug_mode_module_auditor::scan($root,'example');
 check($result['checked']===2 && count($result['issues'])===1 && $result['issues'][0]['file']==='broken.php','Find syntax failure without executing code or following dependencies/symlinks');
 check(isset(Debug_mode_module_auditor::scan($root,'../example')['error']),'Reject traversal');
 check(isset(Debug_mode_module_auditor::scan($root,['bad'])['error']),'Reject malformed module input');
 file_put_contents($root.'/example/huge.php',str_repeat(' ',2097153));
 check(Debug_mode_module_auditor::scan($root,'example')['skipped']===1,'Report oversized files as skipped');
} finally {unlink($root.'/example/external');unlink($root.'/example/example.php');unlink($root.'/example/broken.php');unlink($root.'/example/huge.php');unlink($root.'/example/vendor/ignored.php');rmdir($root.'/example/vendor');rmdir($root.'/example');rmdir($root);}
class App_Controller {}
function is_client_logged_in(){return $GLOBALS['client'];}function get_contact_user_id(){return 2;}function debug_mode_is_enabled(){return $GLOBALS['enabled'];}function get_option($key){return $GLOBALS['capture'];}function show_error($message,$code){throw new RuntimeException('HTTP'.$code);}
require dirname(__DIR__).'/modules/debug_mode/controllers/Client_diagnostics.php';
$GLOBALS['client']=false;$GLOBALS['enabled']=true;$GLOBALS['capture']='1';
foreach(['anonymous','disabled-debug','disabled-capture'] as $case){
 $GLOBALS['client']=$case!=='anonymous';$GLOBALS['enabled']=$case!=='disabled-debug';$GLOBALS['capture']=$case==='disabled-capture'?'0':'1';
 try{(new Client_diagnostics())->collect();throw new LogicException('Missing denial');}catch(RuntimeException $e){check($e->getMessage()==='HTTP403','Deny '.$case.' collection');}
}
require dirname(__DIR__).'/modules/debug_mode/libraries/Debug_mode_browser_receiver.php';
class CaptureInput {public $token='fixture';function method($upper){return 'POST';}function post($key){return ['debug_capture_token'=>$this->token,'kind'=>'javascript','message'=>'Customer private text password=secret','route'=>'/invoices/123?token=secret','file'=>'/invoices/123?token=secret','line'=>9][$key]??null;}}
class CaptureSession {public $values=['debug_mode_capture_token'=>'fixture'];function userdata($key){return $this->values[$key]??null;}function set_userdata($key,$value){$this->values[$key]=$value;}}
class CaptureOutput {public $status;function set_status_header($code){$this->status=$code;}}
function log_message($level,$message){$GLOBALS['captured']=$message;}
$ci=(object)['input'=>new CaptureInput(),'session'=>new CaptureSession(),'output'=>new CaptureOutput()];
Debug_mode_browser_receiver::receive($ci,true);
check($ci->output->status===204 && strpos($GLOBALS['captured'],'[client-portal]')!==false,'Accept session-authorized client diagnostic');
check(strpos($GLOBALS['captured'],'private')===false && strpos($GLOBALS['captured'],'secret')===false && strpos($GLOBALS['captured'],'123')===false,'Suppress client messages, query secrets and record identifiers');
$ci->input->token='invalid';
try{Debug_mode_browser_receiver::receive($ci,true);throw new LogicException('Missing CSRF denial');}catch(RuntimeException $e){check($e->getMessage()==='HTTP403','Reject forged client capture token');}

<?php
if (PHP_SAPI !== 'cli') exit(1);
define('BASEPATH', dirname(__DIR__).'/system/');
define('APPPATH', dirname(__DIR__).'/application/');
function log_message($level,$message){}
function config_item($key){return '';}
require BASEPATH.'libraries/Encryption.php';
require APPPATH.'helpers/google_integration_security_helper.php';
class CredentialTestLoad{public function library($name){}}
$ci=(object)['load'=>new CredentialTestLoad,'encryption'=>new CI_Encryption(['driver'=>'openssl','cipher'=>'aes-128','mode'=>'cbc','key'=>random_bytes(32)])];
function &get_instance(){global $ci;return $ci;}
$plain='synthetic-google-credential-not-a-real-token';
$a=sc_google_secret_encrypt($plain);$b=sc_google_secret_encrypt($plain);
if($a===$plain||$a===$b||sc_google_secret_decrypt($a)!==$plain)throw new RuntimeException('Native encryption round-trip/randomization failed');
$damaged='scg1:'.str_repeat('x',strlen(substr($a,5)));
if(sc_google_secret_decrypt($damaged)!=='')throw new RuntimeException('Corrupt credentials accepted');
if(sc_google_secret_decrypt($plain)!==$plain||sc_google_secret_encrypt('')!=='')throw new RuntimeException('Legacy/empty compatibility');
$ci->encryption=new class{function encrypt($v){return false;}};
try{sc_google_secret_encrypt($plain);throw new LogicException('Unsafe encryption fallback');}catch(RuntimeException $expected){}
echo "PASS native randomized authenticated encryption, corrupted-ciphertext rejection, legacy reads, and fail-closed storage\n";

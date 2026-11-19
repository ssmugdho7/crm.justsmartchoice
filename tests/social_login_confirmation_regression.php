<?php
define('BASEPATH',true);
class App_Controller {}
require dirname(__DIR__).'/modules/sc_social_login/helpers/sc_social_login_helper.php';
require dirname(__DIR__).'/modules/sc_social_login/controllers/Oauth.php';
class ConfirmationSessionFixture {public $pending;function userdata($key){return $key==='scsl_pending'?$this->pending:null;}}
$CI=(object)['session'=>new ConfirmationSessionFixture()];
function &get_instance(){return $GLOBALS['CI'];}
function get_option($name){return $name==='scsl_google_client_id'?'fixture-client':'';}
function site_url($path=''){return 'https://crm.example.test/'.$path;}
function admin_url($path=''){return site_url('admin/'.$path);}
function html_escape($s){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}
function _l($s){return $s;}
$redirected=null;function redirect($url){$GLOBALS['redirected']=$url;}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS $message\n";}
$pending=['audience'=>'staff','provider'=>'google','client_id'=>'fixture-client','email'=>'fixture@example.test','expires'=>time()+600];
$CI->session->pending=$pending;
check(scsl_pending_for('staff')===$pending,'Fresh pending proof is shown only for its staff audience');
check(scsl_pending_for('client')===null,'Staff proof is not exposed on customer login');
foreach ([['expires'=>time()-1],['provider'=>'unknown'],['client_id'=>'old-client']] as $change){$CI->session->pending=array_replace($pending,$change);check(scsl_pending_for('staff')===null,'Expired or changed provider proof is hidden');}
$controller=(new ReflectionClass(Oauth::class))->newInstanceWithoutConstructor();
$method=new ReflectionMethod(Oauth::class,'continueWithPassword');
foreach(['staff','client'] as $audience){$method->invoke($controller,$audience);check($redirected===($audience==='staff'?admin_url('authentication'):site_url('authentication/login')),'First-time '.$audience.' proof uses its normal CRM login URL');}
$pending['email']='<script>alert(1)</script>@example.test';$can_register=false;$audience='staff';
ob_start();require dirname(__DIR__).'/modules/sc_social_login/views/confirmation.php';$html=ob_get_clean();
check(strpos($html,'<script>')===false&&strpos($html,'&lt;script&gt;')!==false,'Provider email is escaped in the native login notice');
$can_register=true;ob_start();require dirname(__DIR__).'/modules/sc_social_login/views/confirmation.php';$html=ob_get_clean();
check(strpos($html,site_url('authentication/register'))!==false,'Eligible customers retain the native registration path');

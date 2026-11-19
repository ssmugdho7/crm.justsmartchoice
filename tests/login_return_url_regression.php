<?php
define('BASEPATH',true);
$source=file_get_contents(dirname(__DIR__).'/application/helpers/general_helper.php');
$start=strpos($source,'function login_redirect_url_allowed(');$end=strpos($source,'/**'."\n".' * Function used to validate all recaptcha',$start);
eval(substr($source,$start,$end-$start));
class ReturnSessionFixture {
 public $data=[];function has_userdata($key){return array_key_exists($key,$this->data);}function userdata($key){return $this->data[$key]??null;}function unset_userdata($key){unset($this->data[$key]);}function set_userdata($data){$this->data=array_merge($this->data,$data);}
}
class ReturnInputFixture {public $ajax=false,$verb='GET';function is_ajax_request(){return $this->ajax;}function method($upper=false){return $upper?$this->verb:strtolower($this->verb);}}
$CI=(object)['session'=>new ReturnSessionFixture(),'input'=>new ReturnInputFixture()];
function &get_instance(){return $GLOBALS['CI'];}
function site_url(){return 'https://crm.example.test/';}
function admin_url(){return site_url().'admin/';}
$staff=false;function is_staff_logged_in(){return $GLOBALS['staff'];}
$current='';function current_full_url(){return $GLOBALS['current'];}
$redirected=null;function redirect($url){$GLOBALS['redirected']=$url;}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS $message\n";}
$good=admin_url().'development';$poll=admin_url().'smartchoice_daily_reminders/reminders/due';
$current=$good;redirect_after_login_to_current_url();check($CI->session->data['red_url']===$good,'A real protected page is remembered');
$current=$poll;redirect_after_login_to_current_url();check($CI->session->data['red_url']===$good,'Reminder polling cannot replace the intended page');
$current=admin_url().'projects';$CI->input->ajax=true;redirect_after_login_to_current_url();check($CI->session->data['red_url']===$good,'XHR cannot replace the intended page');
$CI->input->ajax=false;$_SERVER['HTTP_SEC_FETCH_DEST']='empty';redirect_after_login_to_current_url();check($CI->session->data['red_url']===$good,'Fetch polling cannot replace the intended page');
unset($_SERVER['HTTP_SEC_FETCH_DEST']);$CI->input->verb='POST';redirect_after_login_to_current_url();check($CI->session->data['red_url']===$good,'POST targets are not recorded');$CI->input->verb='GET';
$staff=true;
foreach([$poll,$poll.'/?cache=1',admin_url().'notifications_check','https://attacker.example.test/admin','//attacker.example.test/admin',admin_url().'authentication',site_url().'sc_social_login/oauth/callback/google',admin_url().'../customer',admin_url()."bad\r\nHeader:value"] as $url){
 $CI->session->data['red_url']=$url;$redirected=null;maybe_redirect_to_previous_url();check($redirected===null&&!isset($CI->session->data['red_url']),'Unsafe, expired poll or authentication destination is discarded');
}
$CI->session->data['red_url']=$good;$redirected=null;maybe_redirect_to_previous_url();check($redirected===$good,'Valid staff page still works after login');
$CI->session->data['red_url']=admin_url().'clients';$redirected=null;maybe_redirect_to_previous_url();check($redirected===admin_url().'clients','Staff customer-management page remains a valid destination');
$controller=file_get_contents(dirname(__DIR__).'/application/controllers/admin/Authentication.php');
check(preg_match_all("/hooks\\(\\)->do_action\\('after_staff_login'\\);\\s+maybe_redirect_to_previous_url\\(\\);/",$controller)===3,'Password and both MFA paths run account-linking hooks before redirect');

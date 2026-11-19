<?php
define('BASEPATH', true);
class App_Model {}
require dirname(__DIR__) . '/modules/sc_social_login/models/Sc_identity_model.php';
class LoginSessionFixture {
 public $values = [], $regenerated = false;
 function sess_regenerate($destroy) { $this->regenerated = $destroy; }
 function unset_userdata($keys) { foreach ((array)$keys as $key) unset($this->values[$key]); }
 function set_userdata($key, $value = null) { $this->values = array_merge($this->values, is_array($key) ? $key : [$key=>$value]); }
}
class LoginDbFixture {function where($key,$value){return $this;} function update($table,$data){return true;}}
class LoginLoadFixture {function model($name){}}
class LoginInputFixture {function ip_address(){return '127.0.0.1';}}
class LoginHooksFixture {function do_action($name,$data=null){}}
class LoginAnnouncementsFixture {function set_announcements_as_read_except_last_one($id,$staff){}}
class LoginAuthenticationFixture {function set_two_factor_auth_code($id){}}
class IdentityLoginFixture extends Sc_identity_model {
 public $fixtureUser, $session, $db, $load, $input, $announcements_model, $authentication_model;
 function account($audience,$id){return $this->fixtureUser;}
}
function hooks(){return new LoginHooksFixture();}
function db_prefix(){return 'tbl';}
function site_url(){return 'https://crm.example.test/';}
function admin_url($path=''){return 'https://crm.example.test/admin/'.$path;}
function send_mail_template($template,$user){return true;}
function check($ok,$label){if(!$ok)throw new RuntimeException($label);echo "PASS $label\n";}
$model=(new ReflectionClass(IdentityLoginFixture::class))->newInstanceWithoutConstructor();
$model->db=new LoginDbFixture();$model->load=new LoginLoadFixture();$model->input=new LoginInputFixture();
$model->announcements_model=new LoginAnnouncementsFixture();$model->authentication_model=new LoginAuthenticationFixture();
foreach (['staff','client'] as $audience) {
 $model->session=new LoginSessionFixture();$model->session->values=['staff_logged_in'=>true,'client_logged_in'=>true];
 $model->fixtureUser=(object)['active'=>1,'email'=>'fixture@example.test','userid'=>91,'two_factor_auth_enabled'=>0];
 $url=$model->login($audience,7);
 check($model->session->regenerated,'Session is regenerated for '.$audience);
 check($url===($audience==='staff'?admin_url():site_url()),'Correct destination for '.$audience);
 check($model->session->values[$audience==='staff'?'staff_user_id':'contact_user_id']===7,'Correct identity for '.$audience);
 check(empty($model->session->values[$audience==='staff'?'client_logged_in':'staff_logged_in']),'Other audience session cleared for '.$audience);
}
foreach ([1,2] as $mfa) {
 $model->session=new LoginSessionFixture();$model->fixtureUser->two_factor_auth_enabled=$mfa;
 $url=$model->login('staff',7);
 check($url===admin_url('authentication/two_factor'.($mfa===2?'/app':'')),'Staff MFA destination '.$mfa);
 check(empty($model->session->values['staff_logged_in'])&&$model->session->values['tfa_staffid']===7,'Staff MFA cannot be bypassed '.$mfa);
}
$model->fixtureUser=null;
try {$model->login('staff',7);throw new LogicException('Expected rejection');}catch(RuntimeException $e){check($e->getMessage()==='scsl_failed','Unavailable account fails closed');}

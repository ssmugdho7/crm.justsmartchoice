<?php
// Real models and native customer registration against connection-local MySQL temporary tables.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=dirname(__DIR__);
define('BASEPATH',$root.'/system/'); define('APPPATH',$root.'/application/'); define('FCPATH',$root.'/'); define('ENVIRONMENT','testing');
#[AllowDynamicProperties] class App_Model { function __construct(){} function __get($key){return get_instance()->$key;} }
class App_Controller extends App_Model {}
class AdminController extends App_Model {}
class ClientsController extends App_Model { function data($data){} function view($view){} function layout(){} }
class FlowStop extends RuntimeException {}
function &get_instance(){return $GLOBALS['ci'];}
function db_prefix(){return 'audit_';}
function log_message(...$args){}
function is_php($v){return version_compare(PHP_VERSION,$v,'>=');}
function show_error($message){throw new RuntimeException($message);}
function check($ok,$why){if(!$ok)throw new RuntimeException($why); $GLOBALS['checks']++;}
function denied($action,$why){try{$action();}catch(RuntimeException $e){check(true,$why);return;}throw new LogicException($why);}
function redirect($url){if(!empty($GLOBALS['captureRedirects'])){$GLOBALS['capturedRedirect']=$url;return;}throw new FlowStop($url);}
function site_url($path=''){return 'https://crm.example.test/'.$path;}
function admin_url($path=''){return site_url('admin/'.$path);}
function is_https(){return true;}
function load_admin_language(){}
function get_cookie_fixture(){return str_repeat('b',64);}
function is_admin(){return false;}
function has_permission(...$args){return false;}
function is_staff_logged_in(){return !empty(get_instance()->session->values['staff_logged_in']);}
function is_client_logged_in(){return !empty(get_instance()->session->values['client_logged_in']);}
function get_staff_user_id(){return get_instance()->session->values['staff_user_id']??0;}
function get_contact_user_id(){return get_instance()->session->values['contact_user_id']??0;}
function get_option($key){return $GLOBALS['options'][$key]??'';}
function _l($key,...$args){return $key;}
function html_escape($s){return htmlspecialchars($s??'',ENT_QUOTES,'UTF-8');}
function e($s){return html_escape($s);}
function form_open($url,$attrs=[]){return '<form action="'.html_escape($url).'" method="post">';}
function form_close(){return '</form>';}
function set_value($field,$default=''){return html_escape(get_instance()->input->data[$field]??$default);}
function validation_errors(...$args){return '';}
function get_dark_company_logo(){}

function set_alert(...$args){}
function register_language_files(...$args){}
function register_activation_hook(...$args){}
function is_email_verification_enabled(){return true;}
function is_gdpr(){return true;}
function app_hash_password($p){return password_hash($p,PASSWORD_DEFAULT);}
function app_hasher(){return new class {function CheckPassword($p,$h){return is_string($h)&&password_verify($p,$h);}};}
function app_generate_hash(){return bin2hex(random_bytes(16));}
function normalize_staff_permission_input($p){return $p;}
function slug_it($s){return strtolower(str_replace(' ','-',$s));}
function log_activity(...$args){}
function send_mail_template(...$args){$GLOBALS['mail'][]=$args;return true;}
function clear_contact_permission_cache(...$args){}
function get_contact_permissions(){return [];}
function do_action_deprecated(...$args){}
function get_cookie(...$args){return null;}
function app_set_remember_cookie(...$args){}
function get_required_fields_for_registration(){return ['contact'=>['contact_firstname'=>['is_required'=>true,'label'=>'First name'],'contact_lastname'=>['is_required'=>true,'label'=>'Last name'],'contact_email'=>['is_required'=>true,'label'=>'Email']],'company'=>[]];}
function strafter($s,$needle){return substr($s,strpos($s,$needle)+strlen($needle));}
function show_recaptcha_in_customers_area(){return false;}
function get_custom_fields(...$args){return [];}
function is_automatic_calling_codes_enabled(){return false;}
function get_contact_language(){return 'english';}
function get_primary_contact_user_id($id){return get_instance()->db->where('userid',$id)->where('is_primary',1)->get('contacts')->row()->id;}
function send_customer_registered_email_to_administrators(...$args){}
class FixtureHooks {
 public $actions=[],$filters=[];
 function add_action($key,$fn){$this->actions[$key][]=$fn;}
 function add_filter($key,$fn){$this->filters[$key][]=$fn;}
 function do_action($key,...$args){foreach($this->actions[$key]??[] as $fn)$fn(...$args);}
 function apply_filters($key,$value,...$args){foreach($this->filters[$key]??[] as $fn)$value=$fn($value,...$args);return $value;}
}
function hooks(){return $GLOBALS['hookFixture'];}
class FixtureSession {
 public $values=[];
 function userdata($key){return $this->values[$key]??null;}
 function set_userdata($key,$value=null){$this->values=array_merge($this->values,is_array($key)?$key:[$key=>$value]);}
 function unset_userdata($keys){foreach((array)$keys as $key)unset($this->values[$key]);}
 function sess_regenerate($destroy){}
}
class FixtureInput {
 public $data=[],$query=[],$verb='post';
 function get($key,$clean=null){return $this->query[$key]??null;}
 function cookie($key,$clean=null){return get_cookie_fixture();}
 function post($key=null,$clean=null){return $key===null?$this->data:(is_array($key)?array_intersect_key($this->data,array_flip($key)):($this->data[$key]??null));}
 function method(){return $this->verb;}
 function ip_address(){return '192.0.2.20';}
}
class FixtureValidation {
 public $data,$rules=[],$valid=true;
 function set_data($data){$this->data=$data;}
 function set_rules($field,$label,$rules,...$args){$this->rules[$field]=$rules;}
 function run(){return $this->valid;}
}
$checks=0;$hookFixture=new FixtureHooks();$mail=[];
$options=['scsl_google_staff'=>'1','scsl_google_client'=>'1','scsl_google_client_id'=>'fixture-client','scsl_google_client_secret'=>'fixture-secret',
 'scsl_client_registration'=>'1','allow_registration'=>'1','gdpr_enable_terms_and_conditions'=>'1','default_contact_permissions'=>serialize([])];
$site=getenv('CRM_SITE_ROOT')?:$root;
require $site.'/application/config/app-config.php';
if(!in_array(APP_DB_HOSTNAME,['localhost','127.0.0.1','::1'],true))throw new RuntimeException('This regression requires a local database');
require $root.'/system/database/DB.php';
$db=DB(['hostname'=>APP_DB_HOSTNAME,'username'=>APP_DB_USERNAME,'password'=>APP_DB_PASSWORD,'database'=>APP_DB_NAME,'dbdriver'=>'mysqli',
 'dbprefix'=>'audit_','pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','save_queries'=>false],true);
foreach(['staff','contacts','clients','staff_permissions','announcements','dismissed_announcements','contact_permissions'] as $table){
 $ddl=$db->query('SHOW CREATE TABLE tbl'.$table)->row_array()['Create Table'];
 $ddl=str_replace('CREATE TABLE `tbl'.$table.'`','CREATE TEMPORARY TABLE `audit_'.$table.'`',$ddl);
 $ddl=implode("\n",array_filter(explode("\n",$ddl),fn($line)=>strpos($line,'FULLTEXT KEY')===false));
 $ddl=preg_replace('/,\n\)/',"\n)",$ddl);
 check($db->query($ddl)!==false,'temporary '.$table);
}
check($db->query('CREATE TEMPORARY TABLE audit_scsl_identities (id INT AUTO_INCREMENT PRIMARY KEY, audience VARCHAR(10), user_id INT, provider VARCHAR(10), subject_hash VARCHAR(64), enabled INT, created_at BIGINT, UNIQUE(audience,provider,subject_hash), UNIQUE(audience,user_id,provider)) ENGINE=InnoDB')!==false,'temporary identities');
check($db->query('CREATE TEMPORARY TABLE audit_scsl_transactions (state_hash VARCHAR(64) PRIMARY KEY, browser_hash VARCHAR(64), ip_hash VARCHAR(64), provider VARCHAR(10), audience VARCHAR(10), intent VARCHAR(10), actor_id INT, nonce VARCHAR(64), verifier VARCHAR(128), created_at BIGINT) ENGINE=InnoDB')!==false,'temporary OAuth transactions');
$ci=(object)['db'=>$db,'session'=>new FixtureSession(),'input'=>new FixtureInput(),'form_validation'=>new FixtureValidation(),
 'app_modules'=>new class {public $enabled=true;function is_active($module){return $this->enabled;}},
 'sc_license_model'=>new class {function valid($module){return true;}},
 'output'=>new class {function set_header($header){return $this;}},
 'encryption'=>new class {function decrypt($value){return 'fixture-secret';}},
 'load'=>new class {function __get($key){return get_instance()->$key;} function model($name){} function helper($name){} function library($name){if($name==='form_validation')get_instance()->form_validation=new FixtureValidation();} function view($name,$data=[]){extract($data);if($name==='authentication/includes/head.php')echo '<!DOCTYPE html><html><head></head>';elseif($name==='sc_social_login/register_staff')require FCPATH.'modules/sc_social_login/views/register_staff.php';}},
 'app_object_cache'=>new class {function delete($key){}},
 'announcements_model'=>new class {function set_announcements_as_read_except_last_one(...$args){}}];
require $root.'/application/vendor/autoload.php';
require $root.'/application/services/utilities/Arr.php';
require $root.'/modules/sc_social_login/sc_social_login.php';
require $root.'/modules/sc_social_login/models/Sc_identity_model.php';
require $root.'/application/models/Staff_model.php';
require $root.'/application/models/Clients_model.php';
require $root.'/application/models/Authentication_model.php';
require $root.'/application/controllers/Authentication.php';
require $root.'/modules/sc_social_login/controllers/Oauth.php';
require $root.'/modules/sc_social_login/controllers/Register.php';
// Only the provider exchange is mocked here; signed token validation has its own regression.
class Sc_oauth {
 function __construct($settings){}
 static function b64($bytes){return rtrim(strtr(base64_encode($bytes),'+/','-_'),'=');}
 function exchange(...$args){return $GLOBALS['oauthProfile'];}
}
function callbackDestination($controller,$profile,$audience,$intent='login',$actor=0){
 $GLOBALS['oauthProfile']=$profile;$GLOBALS['captureRedirects']=true;unset($GLOBALS['capturedRedirect']);
 $tx=get_instance()->sc_identity_model->transaction('google',$audience,$intent,$actor,get_cookie_fixture());
 get_instance()->input->verb='get';
 get_instance()->input->query=['state'=>$tx['state'],'code'=>'fixture-code'];
 try{$controller->callback('google');}finally{$GLOBALS['captureRedirects']=false;}
 return $GLOBALS['capturedRedirect']??null;
}
$ci->staff_model=new Staff_model();$ci->sc_identity_model=new Sc_identity_model();$ci->clients_model=new Clients_model();
$ci->authentication_model=(new ReflectionClass(Authentication_model::class))->newInstanceWithoutConstructor();
$pending=['provider'=>'google','audience'=>'staff','subject'=>'staff-fixture','email'=>'staff@example.test','verified'=>true,
 'actor_id'=>0,'expires'=>time()+600,'client_id'=>'fixture-client','firstname'=>'New','lastname'=>'Staff'];
$ci->session->set_userdata('scsl_pending',$pending);
check(scsl_registration_profile('staff')===$pending,'fresh verified staff signup proof');
foreach([['verified'=>false],['expires'=>time()-1],['actor_id'=>4],['client_id'=>'old-client'],['email'=>'invalid'],['provider'=>'facebook']] as $change){
 $ci->session->set_userdata('scsl_pending',array_replace($pending,$change));check(scsl_registration_profile('staff')===null,'invalid signup proof rejected');
}
$ci->session->set_userdata('scsl_pending',$pending);
$ci->input->data=['firstname'=>'New','lastname'=>'Staff','accept_terms_and_conditions'=>'1',
 'email'=>'attacker@example.test','admin'=>'1','administrator'=>'1','role'=>'1','permissions'=>['staff'=>['create']]];
unset($ci->form_validation); // App_Controller does not autoload form validation on public staff routes.
$register=(new ReflectionClass(Register::class))->newInstanceWithoutConstructor();
$GLOBALS['captureRedirects']=true;$register->index();$GLOBALS['captureRedirects']=false;
check($GLOBALS['capturedRedirect']===admin_url(),'public staff controller signs in after signup');
check(isset($ci->form_validation->rules['firstname'],$ci->form_validation->rules['lastname'],$ci->form_validation->rules['accept_terms_and_conditions']),'public staff signup requires names and configured terms');
$id=(int)$ci->sc_identity_model->identity('google','staff','staff-fixture')['user_id'];
$staff=$db->where('staffid',$id)->get('staff')->row();
check($staff && !$staff->admin && !$staff->role && $staff->password==='' && $staff->active,'public staff starts active without admin, role or password');
check($db->where('staff_id',$id)->count_all_results('staff_permissions')===0,'public staff receives no assigned permissions');
check($ci->sc_identity_model->identity('google','staff','staff-fixture')['user_id']==$id,'staff signup atomically connects Google');
$ci->session->set_userdata('scsl_pending',$pending);
denied(fn()=>$ci->sc_identity_model->registerStaff($pending,'Duplicate','Staff'),'duplicate signup rejected');
$ci->session->unset_userdata('scsl_pending');
$ci->sc_identity_model->login('staff',$id);check(get_staff_user_id()===$id,'Google staff login establishes same account');
$identity=$ci->sc_identity_model->identity('google','staff','staff-fixture');
denied(fn()=>$ci->sc_identity_model->remove($identity['id'],'staff',$id),'last sign-in method protected');
check($ci->sc_identity_model->setInitialPassword('staff',$id,'fixture-password'),'Google staff adds password');
check(!$ci->sc_identity_model->setInitialPassword('staff',$id,'replacement-password'),'initial password cannot overwrite existing password');
check($ci->authentication_model->login('staff@example.test','fixture-password',false,true)===true,'native staff password login works');
check($ci->sc_identity_model->identity('google','staff','staff-fixture')['user_id']==$id,'adding password preserves Google login');
denied(fn()=>$ci->sc_identity_model->remove($identity['id'],'staff',$id+100),'another staff cannot disconnect owner');
$ci->sc_identity_model->remove($identity['id'],'staff',$id);
check(!$ci->sc_identity_model->identity('google','staff','staff-fixture'),'owner can disconnect Google after setting password');
// Existing staff can connect a Google account with a different email after signing in, without module permissions.
$ci->session->set_userdata('scsl_pending',array_replace($pending,['subject'=>'different-google','email'=>'different@example.test','actor_id'=>$id]));
$before=$db->where('staffid',$id)->get('staff')->row()->password;
check($ci->sc_identity_model->attachPending('staff',$id),'ordinary staff explicitly links different Google email');
check($db->where('staffid',$id)->get('staff')->row()->password===$before,'linking never changes existing password');
$ci->session->set_userdata('scsl_pending',array_replace($pending,['subject'=>'attacker','actor_id'=>$id+100]));
check(!$ci->sc_identity_model->attachPending('staff',$id),'pending link cannot switch staff actors');
// Exercise the actual customer registration controller and models, including hostile posted email and password.
$pending=array_replace($pending,['audience'=>'client','subject'=>'client-fixture','email'=>'client@example.test']);
$ci->session->values=['scsl_pending'=>$pending];
$ci->input->data=array_fill_keys(['address','city','state','zip','country','contact_phonenumber','website','title','company','phonenumber'],'');
$ci->input->data+=['firstname'=>'New','lastname'=>'Customer','email'=>'attacker@example.test','password'=>'injected-password','passwordr'=>'injected-password','accept_terms_and_conditions'=>'1'];
$ci->form_validation=new FixtureValidation();
$controller=(new ReflectionClass(Authentication::class))->newInstanceWithoutConstructor();
try{$controller->register();throw new LogicException('Expected customer redirect');}catch(FlowStop $e){check($e->getMessage()===site_url(),'Google customer signup signs in');}
$contact=$db->where('email','client@example.test')->get('contacts')->row();
check($contact && $contact->password===null && !empty($contact->email_verified_at),'Google signup uses verified email and no empty-password hash');
check(!$db->where('email','attacker@example.test')->count_all_results('contacts'),'posted email cannot replace verified Google email');
check(!isset($ci->form_validation->rules['password']) && isset($ci->form_validation->rules['accept_terms_and_conditions']),'Google signup preserves terms without password requirement');
check(get_contact_user_id()==$contact->id,'Google customer session uses the new contact');
check($ci->sc_identity_model->setInitialPassword('client',$contact->id,'customer-password'),'Google customer adds a password');
check($ci->authentication_model->login('client@example.test','customer-password',false,false)===true,'native customer password login works');
check($ci->sc_identity_model->identity('google','client','client-fixture')['user_id']==$contact->id,'customer password preserves Google identity');
// Simulate another HTTP request in this process; native registration defines this constant per request.
set_error_handler(function($severity,$message){if($severity===E_WARNING && str_starts_with($message,'Constant CONTACT_REGISTERING already defined'))return true;return false;});
$options['customers_register_require_confirmation']='1';
$ci->session->values=['scsl_pending'=>array_replace($pending,['subject'=>'approval-fixture','email'=>'approval@example.test'])];$ci->form_validation=new FixtureValidation();
try{$controller->register();throw new LogicException('Expected approval redirect');}catch(FlowStop $e){check($e->getMessage()===site_url('authentication/login'),'customer approval remains required');}
$approval=$db->where('email','approval@example.test')->get('contacts')->row();
check($approval && !$ci->sc_identity_model->account('client',$approval->id),'unapproved Google customer cannot sign in');
check(!$ci->session->userdata('client_logged_in'),'approval does not create signed-in session');
// A duplicate provider subject must roll the newly created customer back instead of leaving an orphan.
$beforeCustomers=$db->count_all_results('clients');$beforeContacts=$db->count_all_results('contacts');
$ci->session->values=['scsl_pending'=>array_replace($pending,['email'=>'duplicate-provider@example.test'])];$ci->form_validation=new FixtureValidation();
try{$controller->register();throw new LogicException('Expected duplicate provider redirect');}catch(FlowStop $e){}
check($db->count_all_results('clients')===$beforeCustomers && $db->count_all_results('contacts')===$beforeContacts,'Google identity conflict rolls back both customer and contact');
$ci->session->values=['scsl_pending'=>array_replace($pending,['verified'=>false])];$ci->form_validation=new FixtureValidation();$ci->form_validation->valid=false;$controller->register();
check(isset($ci->form_validation->rules['password']),'unverified Google proof cannot bypass native password registration');
$options['scsl_google_client']='0';check(scsl_registration_profile()===null,'disabled Google cannot start signup');
$options['scsl_google_client']='1';
$oauth=(new ReflectionClass(Oauth::class))->newInstanceWithoutConstructor();
$ci->session->values=[];
check(callbackDestination($oauth,['subject'=>'first-time-staff','email'=>'public@example.test','verified'=>true],'staff')===site_url('sc_social_login/register'),'first Google staff visit goes to public signup');
check(scsl_registration_profile('staff')['email']==='public@example.test','staff callback preserves verified signup proof');
check(callbackDestination($oauth,['subject'=>'first-time-client','email'=>'newclient@example.test','verified'=>true],'client')===site_url('authentication/register'),'first Google customer visit goes to registration');
check(scsl_registration_profile()['email']==='newclient@example.test','customer callback preserves verified signup proof');
check(callbackDestination($oauth,['subject'=>'existing-unlinked','email'=>'staff@example.test','verified'=>true],'staff')===admin_url('authentication'),'existing email requires account proof instead of duplicate signup');
$ci->session->values=['staff_logged_in'=>true,'staff_user_id'=>$id];
check(callbackDestination($oauth,['subject'=>'different-google','email'=>'other@example.test','verified'=>true],'staff','link',$id)===admin_url('sc_social_login/connections'),'ordinary staff can finish explicit Google connection');
// This account already has a different Google identity; it must remain untouched.
check($ci->sc_identity_model->identity('google','staff','different-google')['user_id']==$id,'conflicting Google identity cannot replace existing connection');
$ci->session->values=['staff_logged_in'=>true,'staff_user_id'=>$id+100];
check(callbackDestination($oauth,['subject'=>'wrong-actor','email'=>'other@example.test','verified'=>true],'staff','link',$id)===admin_url('authentication'),'OAuth link rejects a changed signed-in actor');
check(!$ci->sc_identity_model->identity('google','staff','wrong-actor'),'changed actor creates no identity');
// Render the actual settings and staff signup templates for both credential states.
$render=function($file,$data){extract($data);ob_start();require $file;return ob_get_clean();};
foreach(['staff','client'] as $audience){
 $ci->session->values=$audience==='staff'?['staff_logged_in'=>true,'staff_user_id'=>$id]:['client_logged_in'=>true,'contact_user_id'=>$contact->id];
 $html=$render($root.'/modules/sc_social_login/views/connections.php',['audience'=>$audience,'identities'=>[],
  'accountUser'=>(object)['email'=>'fixture@example.test','password'=>null]]);
 check(strpos($html,'name="newpassword"')!==false && strpos($html,'/start/google/'.$audience.'/link')!==false,'passwordless '.$audience.' settings offer password setup and Google connection');
 $html=$render($root.'/modules/sc_social_login/views/connections.php',['audience'=>$audience,'identities'=>[],
  'accountUser'=>(object)['email'=>'fixture@example.test','password'=>'existing-hash']]);
 check(strpos($html,'name="newpassword"')===false && strpos($html,'scsl_password_available')!==false,'existing '.$audience.' password is displayed without initial-password replacement form');
}
$ci->input->data=[];
ob_start();$ci->load->view('sc_social_login/register_staff',['profile'=>array_replace($pending,['email'=>'safe@example.test','firstname'=>'<script>bad</script>']), 'terms'=>true]);$html=ob_get_clean();
check(strpos($html,'&lt;script&gt;bad&lt;/script&gt;')!==false && strpos($html,'<script>bad')===false,'Google profile text is escaped in public signup');
check(strpos($html,'name="accept_terms_and_conditions"')!==false && strpos($html,'type="password"')===false,'public staff signup shows terms and needs no password');
restore_error_handler();
$db->close();echo "PASS $checks real MySQL/model/controller checks; temporary tables only; no external mail or provider requests.\n";

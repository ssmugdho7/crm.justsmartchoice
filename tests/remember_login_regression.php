<?php
// Run actual authentication methods against disposable in-memory sessions/accounts/tokens.
namespace RememberRegression;
if (PHP_SAPI !== 'cli') { exit; }
define('BASEPATH', __DIR__);
define('APP_BASE_URL', 'https://crm.justsmartchoice.com/');
function verify($value, $message) { if (!$value) throw new \RuntimeException($message); }
function config_item($key) { return ['cookie_path'=>'/', 'cookie_domain'=>'', 'cookie_prefix'=>''][$key] ?? null; }
function setcookie($name, $value, $options) { $GLOBALS['cookie_write'] = compact('name','value','options'); return true; }
function get_cookie($name, $clean = true) { return $GLOBALS['cookie'] ?? null; }
function db_prefix() { return 'tbl'; }
function is_logged_in() { $s=$GLOBALS['fixture']->session; return (bool) ($s->userdata('staff_logged_in') || $s->userdata('client_logged_in')); }
function is_client_logged_in() { return (bool) $GLOBALS['fixture']->session->userdata('client_logged_in'); }
function get_client_user_id() { return 11; }
function get_staff_user_id() { return 5; }
function log_activity($message) {}
function hooks() { return new class { function do_action($name, $data) {} }; }
function app_hasher() { return new class { function CheckPassword($plain, $hash) { return $plain === 'fixture-password' && $hash === 'fixture-hash'; } }; }
class App_Model {
    public $db, $load, $session, $config, $input, $user_autologin;
    function __construct() { foreach (get_object_vars($GLOBALS['fixture']) as $key=>$value) $this->$key=$value; }
}
class Session {
    public $data=[], $regenerated=0;
    function userdata($key) { return $this->data[$key] ?? null; }
    function has_userdata($key) { return isset($this->data[$key]); }
    function set_userdata($key, $value=null) { if (is_array($key)) $this->data=array_merge($this->data,$key); else $this->data[$key]=$value; }
    function unset_userdata($keys) { foreach ((array) $keys as $key) unset($this->data[$key]); }
    function sess_regenerate($destroy) { verify($destroy,'Login must replace the pre-auth session'); $this->regenerated++; }
    function sess_destroy() { $this->data=[]; }
}
class Database {
    public $table, $where=[];
    public $password='fixture-hash', $active=1, $twoFactor=0;
    function select($s) { return $this; }
    function set($k,$v) { return $this; }
    function where($k,$v) { $this->where[$k]=$v; return $this; }
    function get($table) { $this->table=$table; return $this; }
    function row() { return (object) ['email'=>'fixture@example.test','password'=>$this->password,'active'=>$this->active,'staffid'=>5,'id'=>5,'userid'=>11,'two_factor_auth_enabled'=>$this->twoFactor]; }
    function update($table) { return true; }
}
class Tokens {
    public $rows=[], $active=true, $password='fixture-hash';
    function set($id,$key,$staff) { $this->rows[$key]=['id'=>$id,'staff'=>$staff ? 1 : 0]; return true; }
    function delete($id,$key,$staff) { if (isset($this->rows[$key]) && $this->rows[$key]['id']==$id && $this->rows[$key]['staff']==$staff) unset($this->rows[$key]); }
    function get($id,$key) { $row=$this->rows[$key] ?? null; return $this->active && $row && $row['id']==$id ? (object) ($row+['password'=>$this->password,'two_factor_auth_enabled'=>$GLOBALS['fixture']->db->twoFactor]) : null; }
}
$root=dirname(__DIR__);
foreach (['application/helpers/remember_login_helper.php','application/models/Authentication_model.php'] as $file) {
    eval('namespace '.__NAMESPACE__.';'.preg_replace('/^<\?php\s*/','',file_get_contents($root.'/'.$file)));
}
function fresh() {
    $GLOBALS['fixture']=(object) [
        'session'=>new Session, 'db'=>new Database, 'user_autologin'=>new Tokens,
        'load'=>new class { function helper($name) {} function model($name) {} },
        'config'=>new class { function item($name) { return 28800; } },
        'input'=>new class { function ip_address() { return '192.0.2.1'; } },
    ];
    $GLOBALS['cookie']=null;
    return new Authentication_model;
}
foreach ([false,true] as $staff) {
    $auth=fresh();
    verify($auth->login('fixture@example.test','fixture-password',true,$staff),'Password login');
    $issued=$GLOBALS['cookie_write'];
    verify($issued['options']['secure'] && $issued['options']['httponly'] && $issued['options']['samesite']==='Lax','Protected cookie');
    verify($issued['options']['expires']>=time()+7*86400-2 && $issued['options']['expires']<=time()+7*86400,'Seven days');
    verify(count($auth->user_autologin->rows)===1,'One database token');
    $data=app_remember_login_data($issued['value']);
    verify($data['staff']===($staff?1:0),'Role bound in cookie');
    $originalExpiry=$data['expires'];
    // Browser/app restart: session gone, persistent cookie retained.
    $auth->session->data=[]; $GLOBALS['cookie']=$issued['value'];
    verify($auth->autologin() && is_logged_in(),'Restore after session loss');
    verify(json_decode($GLOBALS['cookie_write']['value'],true)['expires']===$originalExpiry,'No rolling endless expiry');
    verify($auth->session->regenerated===2,'Fresh session after remembered login');
    $auth->session->data=[]; $tampered=$data; $tampered['expires']--;
    $GLOBALS['cookie']=json_encode($tampered);
    verify(!$auth->autologin() && !is_logged_in(),'Expiry tampering cannot match stored token');
    $GLOBALS['cookie']=$issued['value']; $auth->user_autologin->password='changed-password-hash';
    verify(!$auth->autologin() && !is_logged_in(),'Password changes invalidate remembered login');
    $auth=fresh(); $auth->login('fixture@example.test','fixture-password',true,$staff);
    $GLOBALS['cookie']=$GLOBALS['cookie_write']['value']; $auth->session->data=[]; $auth->user_autologin->active=false;
    verify(!$auth->autologin() && !is_logged_in(),'Inactive accounts cannot restore');
    $auth=fresh(); $auth->login('fixture@example.test','fixture-password',true,$staff);
    $GLOBALS['cookie']=$GLOBALS['cookie_write']['value']; $auth->logout($staff);
    verify(!$auth->user_autologin->rows && $GLOBALS['cookie_write']['options']['domain']==='' && $GLOBALS['cookie_write']['options']['expires']<time(),'Logout revokes token and correct host cookie');
    $auth=fresh(); $auth->login('fixture@example.test','fixture-password',true,$staff);
    $GLOBALS['cookie']=$GLOBALS['cookie_write']['value'];
    $auth->login('fixture@example.test','fixture-password',false,$staff);
    verify(!$auth->user_autologin->rows && $GLOBALS['cookie_write']['value']==='','Unchecked Remember me revokes previous device token');
    $auth->session->data['auth_started_at']=time()-28800;
    $GLOBALS['cookie']=null;
    verify(!$auth->autologin() && !is_logged_in(),'Workday expiry enforced despite polling');
    echo 'PASS '.($staff?'staff':'customer').": restoration, expiry, tampering, password/deactivation, logout and unchecked Remember me\n";
}
foreach ([null,'[]','{}','invalid',json_encode(['user_id'=>5,'key'=>str_repeat('a',64)])] as $cookie) {
    verify(app_remember_login_data($cookie)===null,'Malformed/legacy cookie rejected');
}
$expired=$data; $expired['expires']=time()-1;
verify(app_remember_login_data(json_encode($expired))===null,'Server expiry');
$auth=fresh(); $auth->db->twoFactor=1;
$result=$auth->login('fixture@example.test','fixture-password',true,true);
verify($result['two_factor_auth'] && !is_logged_in() && !$auth->user_autologin->rows,'No token before successful second factor');
$auth->two_factor_auth_login($auth->db->row());
verify(is_logged_in() && count($auth->user_autologin->rows)===1,'Remembered login issued only after second factor');
$auth=fresh(); $auth->db->twoFactor=1; $auth->session->set_userdata('tfa_remember',true);
$auth->login('fixture@example.test','fixture-password',false,true);
$auth->two_factor_auth_login($auth->db->row());
verify(!$auth->user_autologin->rows,'Unchecked second-factor login clears stale Remember me intent');
echo "PASS malformed and legacy cookies fail closed; no credentials or database used\n";

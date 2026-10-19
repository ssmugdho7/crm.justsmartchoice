<?php
// Real CodeIgniter/MySQL behavior with connection-local TEMPORARY tables only. No CRM mail is sent.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=dirname(__DIR__);define('BASEPATH',$root.'/system/');define('APPPATH',$root.'/application/');define('FCPATH',$root.'/');
define('APP_MODULES_PATH',$root.'/modules/');define('ENVIRONMENT','testing');
function log_message(...$args){}function is_php($v){return version_compare(PHP_VERSION,$v,'>=');}
function db_prefix(){return 'audit_sc_';}function &get_instance(){return $GLOBALS['ci'];}
function show_error($s){throw new RuntimeException($s);}function show_404(){throw new RuntimeException('not found');}
function access_denied($s){throw new RuntimeException('access denied');}
function redirect($url){throw new RuntimeException('redirect:'.$url);}function load_admin_language(){}
function _l($s){return $s;}function set_alert(...$args){}
function config_item($s){return null;}function is_https(){return true;}
function base_url(){return 'https://crm.example.test/path/';}function site_url($p=''){return base_url().$p;}
function admin_url($p=''){return base_url().'admin/'.$p;}
$options=[];$staff=42;$client=7;$loggedStaff=true;$loggedClient=false;$admin=true;$permissions=[];
function get_option($k){return $GLOBALS['options'][$k]??'';}function add_option($k,$v){if(!isset($GLOBALS['options'][$k]))$GLOBALS['options'][$k]=$v;}
function update_option($k,$v){$GLOBALS['options'][$k]=$v;}function is_admin(){return $GLOBALS['admin'];}
function is_staff_logged_in(){return $GLOBALS['loggedStaff'];}function is_client_logged_in(){return $GLOBALS['loggedClient'];}
function get_staff_user_id(){return $GLOBALS['staff'];}function get_contact_user_id(){return $GLOBALS['client'];}
function has_permission($m,$unused,$cap){return in_array($m.':'.$cap,$GLOBALS['permissions'],true);}
function is_email_verification_enabled(){return true;}
$checks=0;function check($ok,$label){if(!$ok)throw new RuntimeException($label);$GLOBALS['checks']++;}
function denied($fn,$label){try{$fn();}catch(Throwable $e){check(true,$label);return;}throw new RuntimeException($label);}
class CI_Model { public function __get($k){return get_instance()->{$k};} }
class App_Model extends CI_Model {public function __construct(){}}
class App_Controller extends CI_Model {public function __construct(){}}
class AdminController extends App_Controller {}
class ClientsController extends App_Controller {}
class FixtureHooks {
    public $actions=[];function add_action($n,$fn){$this->actions[$n][]=$fn;}
    function do_action($n,...$args){foreach($this->actions[$n]??[] as $fn)$fn(...$args);}
    function apply_filters($n,$v){return $v;}
}
function hooks(){static $h;return $h??=new FixtureHooks();}
function register_activation_hook($m,$fn){hooks()->add_action('activate_'.$m.'_module',$fn);}
function register_language_files(...$args){}function register_staff_capabilities($m,$caps,$label){$GLOBALS['caps'][$m]=$caps['capabilities'];}
require BASEPATH.'database/DB.php';
$params=['hostname'=>getenv('SC_TEST_MYSQL_HOST')?:'localhost','username'=>getenv('SC_TEST_MYSQL_USER')?:'root',
    'password'=>getenv('SC_TEST_MYSQL_PASSWORD')?:'','database'=>getenv('SC_TEST_MYSQL_DATABASE')?:'mysql',
    'dbdriver'=>'mysqli','dbprefix'=>db_prefix(),'pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','save_queries'=>false];
DB($params,true)->close();
class FixtureDb extends CI_DB_mysqli_driver {
    function query($sql,$binds=false,$return_object=null){
        if(preg_match('/^CREATE TABLE(?: IF NOT EXISTS)? `?(audit_sc_\w+)/i',$sql,$match)){
            $sql=preg_replace('/^CREATE TABLE /i','CREATE TEMPORARY TABLE ',$sql);
            $this->data_cache['table_names'][]=$match[1];
        }
        if(preg_match('/^(?:INSERT|REPLACE|UPDATE|DELETE|CREATE|ALTER|DROP)/i',$sql) && strpos($sql,'audit_sc_')===false){throw new RuntimeException('Mutation outside fixture tables rejected');}
        return parent::query($sql,$binds,$return_object);
    }
}
$db=new FixtureDb($params);$db->initialize();$db->data_cache['table_names']=[];
require BASEPATH.'database/DB_forge.php';require BASEPATH.'database/drivers/mysqli/mysqli_forge.php';
require BASEPATH.'libraries/Encryption.php';
$ci=(object)['db'=>$db,'dbforge'=>new CI_DB_mysqli_forge($db),'encryption'=>new CI_Encryption(['key'=>random_bytes(32)]),
    'input'=>new class {
        public $body='',$headers=[],$posts=[],$gets=[],$method='post';
        function ip_address(){return '192.0.2.1';}function method(){return $this->method;}
        function post($k,$clean=false){return $this->posts[$k]??null;}
        function get($k,$clean=false){return $this->gets[$k]??null;}
        function get_request_header($k,$clean=false){return $this->headers[$k]??'';}
        function __get($k){return $k==='raw_input_stream'?$this->body:null;}
    },'output'=>new class {
        public $body='',$status=200;
        function set_header($s){return $this;}function set_status_header($s){$this->status=$s;return $this;}
        function set_content_type($s){return $this;}function set_output($s){$this->body=$s;return $this;}
    },'session'=>new class {
        public $data=[],$rotations=0;function userdata($k){return $this->data[$k]??null;}
        function set_userdata($k,$v=null){if(is_array($k))$this->data=array_replace($this->data,$k);else $this->data[$k]=$v;}
        function unset_userdata($keys){foreach((array)$keys as $k)unset($this->data[$k]);}
        function sess_regenerate($destroy){$this->rotations++;}
    },'load'=>new class {
        function dbforge(){}function library($s){}
        function model($path){$name=basename($path);if(!isset(get_instance()->{$name}))get_instance()->{$name}=new $name();}
        function view($name,$data){$GLOBALS['last_view']=[$name,$data];}
    },'app_menu'=>new class {function add_setup_menu_item(...$args){}},
    'lang'=>new class {function load($k){}function line($k){return $k.' %s';}}];
$privateDir=sys_get_temp_dir().'/sc-license-fixture-'.bin2hex(random_bytes(8));mkdir($privateDir,0700);
register_shutdown_function(function()use($privateDir){foreach(glob($privateDir.'/*') as $file)unlink($file);rmdir($privateDir);});
$key=openssl_pkey_new(['private_key_type'=>OPENSSL_KEYTYPE_RSA,'private_key_bits'=>2048]);openssl_pkey_export($key,$pem);
$public=openssl_pkey_get_details($key)['key'];file_put_contents($privateDir.'/private.pem',$pem);chmod($privateDir.'/private.pem',0600);
file_put_contents($privateDir.'/config.php','<?php return '.var_export(['mode'=>'issuer','server'=>'https://licenses.example.test','public_key'=>$public,'private_key_file'=>$privateDir.'/private.pem'],true).';');chmod($privateDir.'/config.php',0600);
putenv('SC_LICENSING_CONFIG='.$privateDir.'/config.php');
require_once $root.'/modules/sc_module_licensing/models/Sc_license_model.php';
require_once $root.'/modules/sc_module_licensing/controllers/License_api.php';
class LoopbackLicenseModel extends Sc_license_model {
    public $calls=0;
    protected function fetch($server,$key,array $p){
        $this->calls++;$ci=get_instance();$ci->input->body=json_encode($p);$ci->input->headers=['X-SC-License'=>$key];$ci->output->status=200;
        (new License_api())->check();if($ci->output->status!==200)throw new RuntimeException('license refused');
        return json_decode($ci->output->body,true);
    }
}
require $root.'/application/libraries/App_modules.php';
$reflect=new ReflectionClass('App_modules');$modules=$reflect->newInstanceWithoutConstructor();
$info=[];foreach(['sc_module_licensing','sc_social_login'] as $m)$info[$m]=['system_name'=>$m,'path'=>$root.'/modules/'.$m.'/','init_file'=>$root.'/modules/'.$m.'/'.$m.'.php','headers'=>['version'=>'1.0.0'],'activated'=>false];
foreach(['ci'=>$ci,'modules'=>$info,'db_modules'=>[],'active_modules'=>['sc_module_licensing'=>$info['sc_module_licensing']]] as $name=>$value){$prop=$reflect->getProperty($name);$prop->setValue($modules,$value);}
$ci->app_modules=$modules;
$db->query('CREATE TABLE audit_sc_modules (module_name VARCHAR(100) PRIMARY KEY, installed_version VARCHAR(30), active TINYINT DEFAULT 0)');
check($modules->activate('sc_module_licensing'),'Native Perfex license-module activation');
$model=new LoopbackLicenseModel();$ci->sc_license_model=$model;
$license=$model->issue('Fixture company','sc_social_login',base_url(),1);
check(strlen($license)===68,'256-bit activation key');
check($db->get(db_prefix().'scl_licenses')->row()->key_hash===hash('sha256',$license),'Issuer stores hash only');
check($model->activate('sc_social_login',$license),'Issuer API to customer activation');
check($model->valid('sc_social_login'),'Signed cached lease');check($model->calls===1,'Cached lease avoids per-request HTTP');
$activation=$db->get(db_prefix().'scl_activations')->row();check(strpos($activation->key_encrypted,$license)===false,'Native encrypted activation key storage');
check($modules->activate('sc_social_login'),'Native licensed social-module activation');
$reflect->getProperty('active_modules')->setValue($modules,$info);
require_once $root.'/modules/sc_social_login/models/Sc_identity_model.php';require_once $root.'/modules/sc_social_login/libraries/Sc_oauth.php';
$identity=new Sc_identity_model();$ci->sc_identity_model=$identity;
require $root.'/modules/sc_module_licensing/install.php';require $root.'/modules/sc_social_login/install.php';
check($db->count_all_results(db_prefix().'scl_licenses')===1,'Repeated activation installer preserves licenses');
$token=$options['scl_installation'];require $root.'/modules/sc_module_licensing/install.php';check(get_option('scl_installation')===$token,'Installation identity preserved');
scl_admin_init();hooks()->do_action('admin_init');
foreach(['sc_module_licensing','sc_social_login'] as $m)check(count($GLOBALS['caps'][$m])===6,'Five basic permissions and Manage Settings');
$admin=false;$permissions=[];denied(function()use($model){$model->issue('Unauthorized','sc_social_login',base_url(),1);},'Denied license creation');
$permissions=['sc_module_licensing:view_own'];check(count($model->scoped())===1,'License own scope');
$staff=43;check(count($model->scoped())===0,'Other employee license hidden');
$permissions[]='sc_module_licensing:edit';denied(function()use($model){$model->change(1,'revoke');},'IDOR revoke denied');
$admin=true;$staff=42;$model->change(1,'revoke');
check($model->valid('sc_social_login'),'Revocation honors current bounded lease');
check(!$model->activate('sc_social_login',$license),'Revoked issuer refuses new confirmation');
$db->where('module','sc_social_login')->update(db_prefix().'scl_activations',['certificate'=>'invalid','checked_at'=>0]);
check(!$model->valid('sc_social_login'),'No extension after expired or revoked confirmation');
$model->change(1,'enable');check($model->activate('sc_social_login',$license),'License reactivation');
check(!$model->activate('other_module',$license),'Key cannot activate another module');
$db->query('CREATE TABLE audit_sc_staff (staffid INT PRIMARY KEY,email VARCHAR(191),active TINYINT,password VARCHAR(100),two_factor_auth_enabled INT DEFAULT 0,last_ip VARCHAR(50),last_login DATETIME)');
$db->query('CREATE TABLE audit_sc_contacts (id INT PRIMARY KEY,userid INT,email VARCHAR(191),active TINYINT,password VARCHAR(100),email_verified_at DATETIME,last_ip VARCHAR(50),last_login DATETIME)');
$db->query('CREATE TABLE audit_sc_clients (userid INT PRIMARY KEY,active TINYINT,registration_confirmed TINYINT)');
$db->insert(db_prefix().'staff',['staffid'=>42,'email'=>'staff@example.test','active'=>1,'password'=>'fixture-hash']);
$db->insert(db_prefix().'staff',['staffid'=>43,'email'=>'other@example.test','active'=>0,'password'=>'fixture-hash']);
$db->insert(db_prefix().'contacts',['id'=>7,'userid'=>9,'email'=>'client@example.test','active'=>1,'password'=>'fixture-hash','email_verified_at'=>date('Y-m-d H:i:s')]);
$db->insert(db_prefix().'clients',['userid'=>9,'active'=>1,'registration_confirmed'=>1]);
update_option('scsl_google_client_id','fixture-client');
check($identity->attach('google','staff','fixture-subject',42),'Link staff provider');
check(!$identity->attach('google','staff','fixture-subject',43),'Identity cannot move between accounts');
check($identity->attach('google','client','fixture-subject',7),'Staff and client namespaces separate');
check($identity->identity('google','staff','fixture-subject')['user_id']==42,'Identity login uses provider subject');
update_option('scsl_google_client_id','other-app');check(!$identity->identity('google','staff','fixture-subject'),'OAuth app change cannot reuse old identity');update_option('scsl_google_client_id','fixture-client');
$browser=str_repeat('a',64);$tx=$identity->transaction('google','staff','login',0,$browser);
denied(function()use($identity,$tx){$identity->consume($tx['state'],str_repeat('b',64),'google');},'Browser binding enforced');
check($identity->consume($tx['state'],$browser,'google')['audience']==='staff','Single-use transaction consumed');
denied(function()use($identity,$tx,$browser){$identity->consume($tx['state'],$browser,'google');},'OAuth replay rejected');
check($identity->account('staff',43)===null,'Inactive employee rejected');
$db->where('userid',9)->update(db_prefix().'clients',['active'=>0]);check($identity->account('client',7)===null,'Inactive company rejected');
$db->where('userid',9)->update(db_prefix().'clients',['active'=>1,'registration_confirmed'=>0]);check($identity->account('client',7)===null,'Unapproved registration rejected');
$db->where('userid',9)->update(db_prefix().'clients',['registration_confirmed'=>1]);
$db->where('id',7)->update(db_prefix().'contacts',['email_verified_at'=>null]);check($identity->account('client',7)===null,'Required email verification retained');
$db->where('id',7)->update(db_prefix().'contacts',['email_verified_at'=>date('Y-m-d H:i:s')]);
$ci->authentication_model=new class {}; $ci->announcements_model=new class {function set_announcements_as_read_except_last_one(...$args){}};
$loggedStaff=false;
check($identity->login('client',7)===site_url(),'Client social login');check((int)$ci->session->data['client_user_id']===9,'Correct company session');
check($ci->session->rotations===1 && isset($ci->session->data['auth_started_at']),'Fresh session and workday deadline');
$db->where('staffid',42)->update(db_prefix().'staff',['two_factor_auth_enabled'=>2]);
check($identity->login('staff',42)===admin_url('authentication/two_factor/app'),'Staff native two-factor challenge');
check(!isset($ci->session->data['staff_logged_in']) && $ci->session->data['tfa_staffid']===42,'No staff session before MFA');
denied(function()use($identity){$identity->remove(1,'client',7);},'Client cannot remove staff connection');
$identity->remove(2,'client',7);check(!$identity->identity('google','client','fixture-subject'),'Client disconnects own connection');
check($identity->attach('apple','staff','apple-fixture',42),'Second provider can be connected');
$db->where('staffid',42)->update(db_prefix().'staff',['password'=>'']);
$identity->remove(1,'staff',42);
denied(function()use($identity){$identity->remove(3,'staff',42);},'Last sign-in method protected');
$db->where('staffid',42)->update(db_prefix().'staff',['password'=>'fixture-hash']);
$identity->remove(3,'staff',42);
update_option('scsl_apple_client_id','apple-fixture-client');
$ci->session->set_userdata('scsl_pending',['provider'=>'apple','subject'=>'hidden-apple-subject','email'=>'',
    'audience'=>'staff','expires'=>time()+600,'actor_id'=>42,'client_id'=>'apple-fixture-client']);
$loggedStaff=true;$admin=false;$permissions=['sc_social_login:create'];
check(!$identity->attachPending('staff',43),'Apple handoff rejects a different native account');
check($identity->attachPending('staff',42),'Apple handoff links only the original authenticated actor');
check(!$ci->session->userdata('scsl_pending'),'Successful link consumes pending identity');
$ci->session->set_userdata('scsl_pending',['expires'=>time()-1]);
check(!$identity->attachPending('staff',42) && !$ci->session->userdata('scsl_pending'),'Expired pending identity cleared');
$loggedStaff=true;$admin=false;$permissions=[];
require $root.'/modules/sc_module_licensing/controllers/Sc_module_licensing.php';
require $root.'/modules/sc_social_login/controllers/Sc_social_login.php';
require $root.'/modules/sc_social_login/controllers/Oauth.php';
require $root.'/modules/sc_social_login/controllers/Account.php';
$licenseController=new Sc_module_licensing();$socialController=new Sc_social_login();
denied(function()use($licenseController){$licenseController->activation();},'Activation settings route denied');
denied(function()use($licenseController){$licenseController->licenses();},'License list route denied');
denied(function()use($licenseController){$licenseController->help();},'License help route denied');
denied(function()use($socialController){$socialController->connections();},'Identity list route denied');
denied(function()use($socialController){$socialController->settings();},'Provider secrets route denied');
denied(function()use($socialController){$socialController->help();},'Social help route denied');
denied(function()use($socialController){$socialController->remove(3);},'Identity mutation route denied');
$ci->input->method='get';$admin=true;
denied(function()use($socialController){$socialController->remove(3);},'GET cannot mutate identities');
denied(function()use($licenseController){$licenseController->change(1);},'GET cannot mutate licenses');
$ci->input->method='post';$admin=false;$permissions=['sc_social_login:view_own'];$socialController->connections();
check(count($GLOBALS['last_view'][1]['identities'])===1,'Own-only controller list');
$staff=43;$socialController->connections();check(count($GLOBALS['last_view'][1]['identities'])===0,'Own-only controller hides other records');$staff=42;
$loggedClient=false;denied(function(){new Account();},'Client connections require authentication');
update_option('scsl_google_staff','1');update_option('scsl_google_client_secret',$ci->encryption->encrypt('fixture-secret'));
$oauthController=new Oauth();$ci->input->method='get';
denied(function()use($oauthController){$oauthController->start('google','staff','link');},'Linking cannot be initiated through GET');
$ci->input->method='post';$loggedStaff=false;
denied(function()use($oauthController){$oauthController->start('google','staff','link');},'Anonymous linking rejected');
$loggedStaff=true;$permissions=[];
denied(function()use($oauthController){$oauthController->start('google','staff','link');},'Staff linking requires Create permission');
$admin=true;
for($i=0;$i<202;$i++)$db->insert(db_prefix().'scl_licenses',['key_hash'=>hash('sha256','pagination-fixture-'.$i),
    'company'=>'Pagination fixture','module'=>'sc_social_login','site'=>base_url(),'expires'=>time()+86400,
    'created_by'=>$i%2?42:43,'created_at'=>time(),'active'=>1]);
$first=$model->scoped();$second=$model->scoped(null,(int)end($first)['id']);$third=$model->scoped(null,(int)end($second)['id']);
check(count($first)===100 && count($second)===100 && count($third)===3,'Global licenses accessible across cursor pages');
check(count(array_unique(array_column(array_merge($first,$second,$third),'id')))===203,'Pagination has no duplicates or omissions');
$admin=false;$permissions=['sc_module_licensing:view_own'];
$own=$model->scoped();$rest=$model->scoped(null,(int)end($own)['id']);
check(count($own)+count($rest)===102,'Own-scope pagination');
check(count(array_filter(array_merge($own,$rest),function($r){return (int)$r['created_by']!==42;}))===0,'Cursor cannot leak another issuer employee licenses');
$permissions=['sc_social_login:view'];$ci->input->gets=['scope'=>'all'];$socialController->connections();
check($GLOBALS['last_view'][1]['global']===true,'Global connection view is explicit');
$ci->input->gets=[];$socialController->connections();check($GLOBALS['last_view'][1]['global']===false,'Own connections remain default for global viewers');
$migrationModule=($argv[1]??'sc_module_licensing');
if(!in_array($migrationModule,['sc_module_licensing','sc_social_login'],true))throw new RuntimeException('invalid fixture module');
$db->where('module_name',$migrationModule)->update(db_prefix().'modules',['installed_version'=>'0.9.9']);
require $root.'/application/libraries/App_module_migration.php';$migration=new App_module_migration($migrationModule);
check((string)$migration->to_latest()==='100','Native module upgrade to 1.0.0');check($migration->to_latest()!==false,'Native upgrade is repeatable');
echo "$checks native activation, migration, MySQL, licensing, permission, session and MFA checks passed; only temporary tables used.\n";
$db->close();

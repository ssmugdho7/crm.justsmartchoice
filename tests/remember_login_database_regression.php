<?php
// Native MySQL/CodeIgniter SQL against connection-local temporary tables only.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=dirname(__DIR__);
define('BASEPATH',$root.'/system/'); define('APPPATH',$root.'/application/'); define('ENVIRONMENT','testing');
function log_message(...$args) {}
function is_php($version) { return version_compare(PHP_VERSION,$version,'>='); }
function show_error($message) { throw new RuntimeException($message); }
function db_prefix() { return 'audit_'; }
function &get_instance() { return $GLOBALS['ci']; }
class App_Model { public function __construct() {} public function __get($key) { return get_instance()->$key; } }
function verify($ok,$why) { if (!$ok) throw new RuntimeException($why); }
$site=getenv('CRM_SITE_ROOT');
if (!$site || !is_file($site.'/application/config/app-config.php')) throw new RuntimeException('CRM_SITE_ROOT must point to a private checkout');
require $site.'/application/config/app-config.php';
require $root.'/system/database/DB.php';
$db=DB(['hostname'=>APP_DB_HOSTNAME,'username'=>APP_DB_USERNAME,'password'=>APP_DB_PASSWORD,'database'=>APP_DB_NAME,'dbdriver'=>'mysqli','pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','save_queries'=>false],true);
$GLOBALS['ci']=(object) ['db'=>$db,'input'=>new class { function user_agent() { return 'Fixture'; } function ip_address() { return '192.0.2.1'; } }];
foreach ([
    'CREATE TEMPORARY TABLE audit_user_auto_login (user_id INT, key_id VARCHAR(64), staff INT, user_agent VARCHAR(150), last_ip VARCHAR(50))',
    'CREATE TEMPORARY TABLE audit_staff (staffid INT PRIMARY KEY, active INT, password VARCHAR(255), two_factor_auth_enabled INT DEFAULT 0)',
    'CREATE TEMPORARY TABLE audit_contacts (id INT PRIMARY KEY, userid INT, active INT, password VARCHAR(255))',
    'CREATE TEMPORARY TABLE audit_clients (userid INT PRIMARY KEY, active INT)',
] as $sql) verify($db->query($sql),'Temporary schema');
$db->insert('audit_staff',['staffid'=>5,'active'=>1,'password'=>'fixture-staff-hash']);
$db->insert('audit_clients',['userid'=>11,'active'=>1]);
$db->insert('audit_contacts',['id'=>5,'userid'=>11,'active'=>1,'password'=>'fixture-contact-hash']);
require $root.'/application/models/User_autologin.php';
$model=new User_Autologin;
$staffKey=str_repeat('a',64); $contactKey=str_repeat('b',64);
verify($model->set(5,$staffKey,1) && $model->set(5,$contactKey,0),'Both role tokens');
verify($model->get(5,$staffKey)->staff===true,'Staff restore');
verify($model->get(5,$contactKey)->staff===false,'Contact restore with overlapping numeric ID');
verify($model->get(5,$contactKey)->password==='fixture-contact-hash','Current credential version available to authentication');
verify($model->get(5,"' OR 1=1 --")===null,'Token query remains bound');
$db->where('id',5)->update('audit_contacts',['active'=>0]);
verify($model->get(5,$contactKey)===null,'Inactive contact denied');
$db->where('id',5)->update('audit_contacts',['active'=>1]);
$db->where('userid',11)->update('audit_clients',['active'=>0]);
verify($model->get(5,$contactKey)===null,'Inactive customer denied');
verify($model->get(5,$staffKey)!==null,'Customer state cannot revoke unrelated staff');
$db->where('staffid',5)->update('audit_staff',['active'=>0]);
verify($model->get(5,$staffKey)===null,'Inactive staff denied');
$db->where('userid',11)->update('audit_clients',['active'=>1]);
$model->delete(5,$contactKey,1);
verify($model->get(5,$contactKey)!==null,'Wrong role cannot delete another token');
$model->delete(5,$contactKey,0);
verify($model->get(5,$contactKey)===null,'Logout revocation');
$db->close();
echo "PASS actual MySQL token lookups: staff/contact identity, active accounts, active customers, literal token binding and role-scoped revocation; temporary tables only\n";

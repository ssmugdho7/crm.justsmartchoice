<?php
// CLI only. Fixture writes are isolated to connection-local TEMPORARY tables.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
define('BASEPATH', $root.'/system/'); define('APPPATH', $root.'/application/'); define('ENVIRONMENT', 'testing'); define('FCPATH', $root.'/');
define('CHAT_DEFAULT_USER_STATUS', 'online'); define('TABLE_CHATGROUPS', 'audit_chatgroups'); define('TABLE_CHATGROUPMEMBERS', 'audit_chatgroupmembers');
function log_message(...$args) {} function is_php($v) { return version_compare(PHP_VERSION, $v, '>='); }
function show_error($m) { throw new RuntimeException($m); } function db_prefix() { return 'audit_'; }
function &get_instance() { return $GLOBALS['ci']; }
function get_staff_user_id() { return 7; } function is_admin($id = '') { return true; }
function is_staff_logged_in() { return $GLOBALS['fixtureStaffLoggedIn'] ?? true; } function _l($key) { return $key; }
function get_option($key) { return $GLOBALS['fixtureOptions'][$key] ?? ''; } function base_url($path = '') { return '/'.$path; }
function time_ago($time) { return $time; } function _dt($time) { return $time; }
function get_staff_userrole($id) { return get_instance()->db->where('roleid', $id)->get('audit_roles')->row('name'); }
function staff_can($capability, $feature = null, $id = '') { return $GLOBALS['fixtureCapabilities'][$feature][$capability] ?? true; }
function is_client_logged_in() { return $GLOBALS['fixtureClientLoggedIn'] ?? false; }
function get_contact_user_id() { return 33; }
function get_client_user_id() { return 100; }
require $root.'/modules/prchat/helpers/prchat_permissions_helper.php';
class CI_Model { function __get($key) { return get_instance()->{$key}; } }
class App_Model extends CI_Model {}
function check($ok, $message) { if (!$ok) { throw new RuntimeException($message); } }
require $root.'/application/config/app-config.php'; require $root.'/system/database/DB.php';
$db = DB(['hostname'=>APP_DB_HOSTNAME,'username'=>APP_DB_USERNAME,'password'=>APP_DB_PASSWORD,'database'=>APP_DB_NAME,'dbdriver'=>'mysqli','dbprefix'=>'audit_','pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','save_queries'=>true], true);
$ci = (object) ['db'=>$db, 'session'=>new class { function userdata($key) { return 7; } }];
foreach ([
 'staff'=>'staffid INT PRIMARY KEY, firstname VARCHAR(60), lastname VARCHAR(60), email VARCHAR(100), phonenumber VARCHAR(30), profile_image VARCHAR(100), last_login DATETIME, last_activity DATETIME, facebook TEXT, linkedin TEXT, skype TEXT, admin INT DEFAULT 0, role INT DEFAULT 0, is_not_staff INT DEFAULT 0, active INT DEFAULT 1',
 'roles'=>'roleid INT PRIMARY KEY, name VARCHAR(60)',
 'chatsettings'=>'id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, name VARCHAR(60), value TEXT',
 'chatmessages'=>'id INT AUTO_INCREMENT PRIMARY KEY, sender_id INT, reciever_id INT, message TEXT, viewed INT DEFAULT 0, time_sent DATETIME, viewed_at DATETIME',
 'chatgroups'=>'id INT AUTO_INCREMENT PRIMARY KEY, created_by_id INT, group_name VARCHAR(191)',
 'chatgroupmembers'=>'id INT AUTO_INCREMENT PRIMARY KEY, group_name VARCHAR(191), member_id INT, group_id INT',
] as $name=>$schema) { check($db->query('CREATE TEMPORARY TABLE audit_'.$name.' ('.$schema.')'), 'Fixture '.$name); }
require $root.'/modules/prchat/models/Prchat_model.php'; $model = new Prchat_model();
$db->insert('audit_roles', ['roleid'=>1,'name'=>'Engineer']);
for ($id=1; $id<=40; $id++) { $db->insert('audit_staff', ['staffid'=>$id,'firstname'=>'Fixture','lastname'=>(string)$id,'role'=>1]); }
$db->insert('audit_chatsettings', ['user_id'=>2,'name'=>'chat_status','value'=>'busy']);
foreach ([[1,2,7,0],[2,7,2,0],[3,3,7,0],[4,3,99,0],[5,2,7,1]] as [$id,$from,$to,$viewed]) {
 $db->insert('audit_chatmessages', ['id'=>$id,'sender_id'=>$from,'reciever_id'=>$to,'message'=>'Fixture '.$id,'viewed'=>$viewed,'time_sent'=>'2026-10-01 12:00:00']);
}
$before=count($db->queries); $started=microtime(true); $users=$model->getUsers(); $reads=count($db->queries)-$before;
$byId=array_column($users,null,'staffid');
check(count($users)===40 && $byId[2]['status']==='busy' && $byId[2]['role']==='Engineer', 'Staff/role/status contracts');
check($byId[2]['message']==='Fixture 5' && $byId[3]['message']==='Fixture 3', 'Latest messages scoped to current staff');
check((int)$byId[2]['unread_count']===1 && (int)$byId[3]['unread_count']===1, 'Unread counts scoped and exclude seen/sent messages');
echo 'Staff list: '.$reads.' queries for 40 users, '.round((microtime(true)-$started)*1000,2)." ms\n";
if (getenv('CHAT_BASELINE')) { exit; }
check($reads<=6, 'Staff list must use bounded queries');
$events=[]; check($model->updateUnread(null,2), 'Read receipt persists without realtime');
$rows=array_column($db->get('audit_chatmessages')->result_array(),null,'id');
check((int)$rows[1]['viewed']===1 && $rows[1]['viewed_at']!==null && (int)$rows[2]['viewed']===0 && (int)$rows[3]['viewed']===0, 'Read receipts affect only incoming selected conversation');
$seenAt=$rows[1]['viewed_at']; $model->updateUnread(null,2);
check($db->where('id',1)->get('audit_chatmessages')->row('viewed_at')===$seenAt, 'Repeated read preserves first seen time');
date_default_timezone_set('America/New_York');
$timestamp=new ReflectionMethod(Prchat_model::class,'chatTimestamp');$timestamp->setAccessible(true);
check($timestamp->invoke($model,'2026-07-01 12:00:00')==='2026-07-01T12:00:00-04:00','Summer date offset retained');
check($timestamp->invoke($model,'2026-01-01 12:00:00')==='2026-01-01T12:00:00-05:00','Winter date offset retained');
check($timestamp->invoke($model,'0000-00-00 00:00:00')===null,'Missing timestamp never renders an invalid date');
// Two readers may select the same unread snapshot. Emit the first saved receipt, not the later attempt time.
class ReceiptCaptureModel extends Prchat_model {
 public $receipts = [];
 public function setMessageAsViewed($pusher, $messages) { $this->receipts=$messages; }
}
$ci->db=new class($db) {
 private $db; private $injected=[];
 function __construct($db) { $this->db=$db; }
 function __get($key) { return $this->db->$key; }
 function __call($method,$args) {
  if ($method==='update' && !isset($this->injected[$args[0]])) {
   $this->injected[$args[0]]=true;
   $id=$args[0]==='audit_chatmessages'?3:8;
   check($this->db->conn_id->query("UPDATE ".$args[0]." SET viewed=1,viewed_at='2026-07-01 12:00:00' WHERE id=".$id), 'Competing fixture reader commits first');
  }
  return $this->db->$method(...$args);
 }
};
$race=new ReceiptCaptureModel();
check($race->updateUnread(null,3), 'Concurrent staff read succeeds');
check($race->receipts[0]->viewed_at==='2026-07-01T12:00:00-04:00', 'Concurrent staff receipt retains first committed seen time');
check($db->query('CREATE TEMPORARY TABLE audit_chatclientmessages (id INT PRIMARY KEY,sender_id VARCHAR(30),reciever_id VARCHAR(30),viewed INT,viewed_at DATETIME)'), 'Client receipt fixture');
$db->insert('audit_chatclientmessages',['id'=>8,'sender_id'=>'staff_7','reciever_id'=>'client_33','viewed'=>0]);
check($race->updateClientUnreadMessages('staff_7',null,true,33), 'Concurrent client read succeeds');
check($race->receipts[0]->viewed_at==='2026-07-01T12:00:00-04:00', 'Concurrent client receipt retains first committed seen time');
$ci->db=$db;
echo "PASS staff lookup, scoped and concurrent read receipts, first-seen retention, and DST timestamps\n";

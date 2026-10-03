<?php
// CLI only. Uses connection-local TEMPORARY tables; never modifies application tables.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
define('BASEPATH', $root . '/system/');
define('APPPATH', $root . '/application/');
define('ENVIRONMENT', 'testing');
function log_message(...$args) {}
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function log_activity(...$args) {}
function show_error($message) { throw new RuntimeException($message); }
function db_prefix() { return 'audit_'; }
function get_staff_user_id() { return 2; }
function is_staff_logged_in() { return false; }
function app_hash_password($value) { return password_hash($value, PASSWORD_DEFAULT); }
function slug_it($value) { return strtolower(str_replace(' ', '-', $value)); }
function check($value, $label) { if (!$value) { throw new RuntimeException($label); } $GLOBALS['checks']++; }
$GLOBALS['checks'] = 0;
class FixtureHooks { public function apply_filters($hook, $value, ...$args) { return $value; } public function do_action(...$args) {} }
function hooks() { static $h; return $h ?? ($h = new FixtureHooks()); }
class FixtureCache { public $data = []; public function get($key) { return $this->data[$key] ?? null; } public function add($key, $value) { $this->data[$key] = $value; } public function delete($key) { unset($this->data[$key]); } }
class App_Model { public function __get($key) { return get_instance()->{$key}; } }
class FixtureLoader { public function model($name) {} }
function &get_instance() { return $GLOBALS['ci']; }
require $root . '/system/database/DB.php';
// Load credentials without printing them or placing them in shell arguments.
$siteRoot = getenv('CRM_SITE_ROOT');
if (!$siteRoot || !is_file($siteRoot . '/application/config/app-config.php')) { throw new RuntimeException('CRM_SITE_ROOT must point to a private site checkout'); }
require $siteRoot . '/application/config/app-config.php';
$database = DB(['hostname' => APP_DB_HOSTNAME, 'username' => APP_DB_USERNAME, 'password' => APP_DB_PASSWORD, 'database' => APP_DB_NAME, 'dbdriver' => 'mysqli', 'dbprefix' => 'audit_', 'pconnect' => false, 'db_debug' => false, 'char_set' => 'utf8mb4', 'dbcollat' => 'utf8mb4_general_ci', 'save_queries' => false], true);
$ci = (object) ['db' => $database, 'app_object_cache' => new FixtureCache(), 'load' => new FixtureLoader(), 'departments_model' => new class { public function get_staff_departments($id) { return []; } }];
require $root . '/application/helpers/admin_helper.php';
require $root . '/application/models/Staff_model.php';
require $root . '/application/models/Roles_model.php';
$ci->staff_model = new Staff_model();
$ci->roles_model = new Roles_model();
$database->query("SET SESSION sql_mode='STRICT_ALL_TABLES'");
$schema = [
 'CREATE TEMPORARY TABLE audit_staff (staffid INT PRIMARY KEY AUTO_INCREMENT, firstname VARCHAR(100), lastname VARCHAR(100), role INT DEFAULT 0, admin INT DEFAULT 0, is_not_staff INT DEFAULT 0, email VARCHAR(200), password VARCHAR(255), datecreated DATETIME, media_path_slug VARCHAR(255), active INT DEFAULT 1) ENGINE=InnoDB',
 "CREATE TEMPORARY TABLE audit_staff_permissions (staff_id INT, feature VARCHAR(100), capability VARCHAR(9)) ENGINE=InnoDB",
 'CREATE TEMPORARY TABLE audit_roles (roleid INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(100), permissions TEXT) ENGINE=InnoDB',
 'CREATE TEMPORARY TABLE audit_announcements (announcementid INT, showtostaff INT) ENGINE=InnoDB',
];
foreach ($schema as $sql) { check($database->query($sql), 'Temporary fixture schema'); }
foreach ([2,3,4] as $id) { $database->insert('audit_staff', ['staffid' => $id, 'firstname' => 'Fixture', 'lastname' => (string) $id, 'role' => 10, 'admin' => 0, 'is_not_staff' => $id === 4 ? 1 : 0]); }
$database->insert('audit_roles', ['roleid'=>10, 'name'=>'Fixture role', 'permissions'=>serialize(['prchat'=>['view']])]);
$GLOBALS['current_user'] = (object) ['staffid'=>2, 'admin'=>'0', 'is_not_staff'=>'0', 'permissions'=>[]];
check(!staff_can('view', 'prchat'), 'Initial deny');
check($ci->staff_model->update_permissions(['prchat'=>['view','view'], 'invoices'=>['view_own']],2), 'Grant saves');
check(staff_can('view','prchat'), 'Grant invalidates current-user cache');
check(count($ci->staff_model->get_staff_permissions(2)) === 2, 'Duplicate grants normalized');
check(!$ci->staff_model->update_permissions(['prchat'=>'view'],2), 'Malformed payload rejected');
check(staff_can('view','prchat'), 'Malformed payload preserves rights');
check($ci->staff_model->update_permissions([],2), 'Explicit clear saves');
check(!staff_can('view','prchat'), 'Revocation invalidates current-user cache');
check($ci->staff_model->update_permissions(['prchat'=>['edit']],3), 'Staff override');
check($ci->roles_model->update(['name'=>'Updated role','permissions'=>['prchat'=>['view','create']]],10), 'Role update');
check(!staff_can('view','prchat',3) && staff_can('edit','prchat',3), 'Role update without apply preserves staff override');
check($ci->roles_model->get(10)->name === 'Updated role', 'Role cache invalidated');
check($ci->roles_model->update(['name'=>'Applied role','permissions'=>['prchat'=>['view_own']], 'update_staff_permissions'=>'on'],10), 'Apply role to staff');
check(staff_can('view_own','prchat',3) && !staff_can('edit','prchat',3), 'Role application replaces override');
check(staff_can('view_own','prchat'), 'Role application refreshes current-user cache');
check($ci->roles_model->get(999) === null && !has_role_permission(999,'view','prchat'), 'Missing role safely denied');
check(!$ci->roles_model->update(['name'=>'Missing','permissions'=>['prchat'=>['view']],'update_staff_permissions'=>'on'],999), 'Missing role cannot assign permissions');
check($ci->staff_model->update(['firstname'=>'Changed','lastname'=>'Fixture'],2), 'Ordinary profile update');
check(staff_can('view_own','prchat'), 'Profile update preserves permissions');
check($ci->staff_model->update(['firstname'=>'Changed','lastname'=>'Fixture','permissions_submitted'=>1],2), 'Form explicitly clears rights');
check(!staff_can('view_own','prchat'), 'Form clear effective');
check($ci->staff_model->update_permissions(['leads'=>['view'],'prchat'=>['view']],4), 'Non-staff replacement');
check(!staff_can('view','leads',4) && staff_can('view','prchat',4), 'Non-staff leads restriction retained');
check($ci->staff_model->update(['firstname'=>'Non-staff','lastname'=>'Fixture'],4), 'Non-staff partial profile update');
check((int)$database->where('staffid',4)->get('audit_staff')->row('is_not_staff') === 1, 'Partial update preserves non-staff status');
check(!$ci->staff_model->update(['firstname'=>'Bad','lastname'=>'Role','role'=>999,'permissions'=>['prchat'=>['view']]],2), 'Invalid role rejected before update');
check($ci->roles_model->update(['name'=>'Renamed only'],10) && has_role_permission(10,'view_own','prchat'), 'Partial role update preserves rights');
$id = $ci->staff_model->add(['firstname'=>'New','lastname'=>'Fixture','email'=>'fixture@example.invalid','password'=>'fixture-pass','role'=>10]);
check($id && staff_can('view_own','prchat',$id), 'Server-side role fallback for new staff');
$id2 = $ci->staff_model->add(['firstname'=>'Empty','lastname'=>'Fixture','email'=>'empty@example.invalid','password'=>'fixture-pass','role'=>10,'permissions_submitted'=>1]);
check($id2 && !staff_can('view_own','prchat',$id2), 'Explicit empty rights override role');
$before = $ci->staff_model->get_staff_permissions(3);
check(!$ci->staff_model->update_permissions(['prchat'=>['audit_fail']],3), 'Failed insert reported');
check($ci->staff_model->get_staff_permissions(3) === $before, 'Failed permission replacement rolls back previous rights');
// Reset transaction status using a new driver wrapper on the same connection after a deliberately failed transaction.
$reset = new ReflectionProperty(CI_DB_driver::class, '_trans_status'); $reset->setAccessible(true); $reset->setValue($database,true);
$roleBefore = $database->where('roleid',10)->get('audit_roles')->row_array();
check(!$ci->roles_model->update(['name'=>'Must roll back','permissions'=>['prchat'=>['audit_fail']],'update_staff_permissions'=>'on'],10), 'Failed role fan-out reported');
check($database->where('roleid',10)->get('audit_roles')->row_array() === $roleBefore, 'Failed role fan-out rolls back role');
check($ci->staff_model->get_staff_permissions(3) === $before, 'Failed role fan-out preserves staff rights');
$reset->setValue($database,true);
$profileBefore = $database->where('staffid',3)->get('audit_staff')->row_array();
check(!$ci->staff_model->update(['firstname'=>'Must roll back','lastname'=>'Fixture','permissions'=>['prchat'=>['audit_fail']]],3), 'Failed staff save reported');
check($database->where('staffid',3)->get('audit_staff')->row_array() === $profileBefore, 'Failed staff permissions roll back identity changes');
// Exercise the actual Chat ACL and event router against isolated ownership fixtures.
function get_option($key) { return $GLOBALS['fixtureOptions'][$key] ?? ''; }
$GLOBALS['fixtureOptions'] = ['pusher_chat_enabled'=>'1','chat_client_enabled'=>'1','chat_staff_can_access_clients'=>'1'];
require $root . '/modules/prchat/helpers/prchat_permissions_helper.php';
foreach ([
 'CREATE TEMPORARY TABLE audit_chatgroups (id INT PRIMARY KEY, created_by_id INT, group_name VARCHAR(100)) ENGINE=InnoDB',
 'CREATE TEMPORARY TABLE audit_chatgroupmembers (group_id INT, member_id INT) ENGINE=InnoDB',
 'CREATE TEMPORARY TABLE audit_chatmessages (id INT PRIMARY KEY, sender_id VARCHAR(40), reciever_id VARCHAR(40)) ENGINE=InnoDB',
 'CREATE TEMPORARY TABLE audit_chatclientmessages (id INT PRIMARY KEY, sender_id VARCHAR(40), reciever_id VARCHAR(40)) ENGINE=InnoDB',
 'CREATE TEMPORARY TABLE audit_chatgroupmessages (id INT PRIMARY KEY, sender_id INT, group_id INT) ENGINE=InnoDB',
 'CREATE TEMPORARY TABLE audit_clients (userid INT PRIMARY KEY, active INT) ENGINE=InnoDB',
 'CREATE TEMPORARY TABLE audit_contacts (id INT PRIMARY KEY, userid INT, active INT) ENGINE=InnoDB',
 'CREATE TEMPORARY TABLE audit_customer_admins (customer_id INT, staff_id INT) ENGINE=InnoDB'
] as $sql) { check($database->query($sql), 'Temporary chat schema'); }
$reset->setValue($database,true);
$ci->staff_model->update_permissions(['prchat'=>['view_own']],2);
$database->insert('audit_chatgroups',['id'=>1,'created_by_id'=>3,'group_name'=>'presence-group-one']);
$database->insert('audit_chatgroups',['id'=>2,'created_by_id'=>3,'group_name'=>'presence-group-two']);
$database->insert('audit_chatgroupmembers',['group_id'=>1,'member_id'=>2]);
$database->insert('audit_chatgroupmembers',['group_id'=>1,'member_id'=>3]);
$database->insert('audit_chatgroupmembers',['group_id'=>2,'member_id'=>3]);
foreach ([10,11] as $id) { $database->insert('audit_clients',['userid'=>$id,'active'=>1]); $database->insert('audit_contacts',['id'=>$id+10,'userid'=>$id,'active'=>1]); }
$database->insert('audit_customer_admins',['customer_id'=>10,'staff_id'=>2]);
$database->insert('audit_chatmessages',['id'=>1,'sender_id'=>'2','reciever_id'=>'3']);
$database->insert('audit_chatmessages',['id'=>2,'sender_id'=>'3','reciever_id'=>'4']);
$database->insert('audit_chatclientmessages',['id'=>1,'sender_id'=>'staff_2','reciever_id'=>'client_20']);
$database->insert('audit_chatclientmessages',['id'=>2,'sender_id'=>'staff_2','reciever_id'=>'client_21']);
$database->insert('audit_chatgroupmessages',['id'=>1,'sender_id'=>3,'group_id'=>1]);
$database->insert('audit_chatgroupmessages',['id'=>2,'sender_id'=>3,'group_id'=>2]);
class ChatFixtureInput {
 public $getData; public $postData;
 public function __construct($get=[], $post=[]) { $this->getData=$get; $this->postData=$post; }
 public function get($key) { return $this->getData[$key] ?? null; }
 public function post($key) { return $this->postData[$key] ?? null; }
 public function get_post($key) { return $this->get($key) ?? $this->post($key); }
}
define('TABLE_CHATGROUPS', 'audit_chatgroups');
define('TABLE_CHATGROUPMEMBERS', 'audit_chatgroupmembers');
define('TABLE_STAFF', 'audit_staff');
require $root . '/modules/prchat/models/Prchat_model.php';
$chatModel = (new ReflectionClass(Prchat_model::class))->newInstanceWithoutConstructor();
ob_start(); $chatModel->getMyGroups(); $groupPayload = json_decode(ob_get_clean(), true);
check(array_column($groupPayload['groups'], 'id') === ['1'] || array_column($groupPayload['groups'], 'id') === [1], 'Native group loader excludes other users groups server-side');
check(prchat_staff_can_chat() && prchat_staff_own_scope(), 'View own opens scoped chat');
check(prchat_staff_can_group(1) && !prchat_staff_can_group(2), 'Joined group allowed and other group denied');
check(prchat_staff_can_contact(20) && !prchat_staff_can_contact(21), 'Assigned customer allowed and other customer denied');
check(prchat_staff_can_message('staff',1) && !prchat_staff_can_message('staff',2), 'Message participant ownership');
check(prchat_staff_can_message('group',1) && !prchat_staff_can_message('group',2), 'Group message ownership');
check(prchat_staff_can_message('client',1) && !prchat_staff_can_message('client',2), 'Client message requires current customer access');
check(prchat_authorize_staff_request('getMessages',new ChatFixtureInput(['from'=>2,'to'=>3])), 'Own direct conversation allowed');
check(!prchat_authorize_staff_request('getMessages',new ChatFixtureInput(['from'=>3,'to'=>4])), 'Cross-user direct history denied');
check(prchat_authorize_staff_request('getGroupMessages',new ChatFixtureInput(['group_id'=>1])), 'Own group history allowed');
check(!prchat_authorize_staff_request('getGroupMessages',new ChatFixtureInput(['group_id'=>2])), 'Cross-user group history denied');
check(!prchat_authorize_staff_request('addChatGroup',new ChatFixtureInput([],['members'=>[2,3]])), 'View own does not grant Create');
check(!prchat_authorize_staff_request('editMessage',new ChatFixtureInput([],['id'=>1])), 'View own does not grant Edit');
check(!prchat_authorize_staff_request('deleteMessage',new ChatFixtureInput([],['id'=>1])), 'View own does not grant Delete');
check(!prchat_authorize_staff_request('purgeConversations',new ChatFixtureInput()), 'Own scope cannot purge all conversations');
check(!prchat_authorize_staff_request('getSharedFiles',new ChatFixtureInput([],['own_id'=>3,'contact_id'=>4])), 'Spoofed shared-file owner denied');
check(!prchat_authorize_staff_request('addReaction',new ChatFixtureInput([],['message_type'=>'staff','message_id'=>2])), 'Cross-user reaction denied');
check(!prchat_authorize_staff_request('getGroupMessages',new ChatFixtureInput(['group_id'=>[1]])), 'Malformed group ID denied');
check(prchat_staff_can_subscribe('private-prchat-staff-2') && !prchat_staff_can_subscribe('private-prchat-staff-3'), 'Private staff channel identity');
check(prchat_staff_can_subscribe('presence-group-one') && !prchat_staff_can_subscribe('presence-group-two'), 'Group channel membership');
check(!prchat_staff_can_subscribe('private-unrelated-secret'), 'Unknown private channel denied');
check(prchat_event_channels('presence-mychanel','send-event',['from'=>2,'to'=>3]) === ['private-prchat-staff-2','private-prchat-staff-3'], 'Direct message reaches only participants');
check(prchat_event_channels('presence-clients','send-event',['from'=>'staff_2','to'=>'client_20']) === ['private-prchat-clients-staff-2','private-prchat-clients-contact-20'], 'Client message reaches only participants');
check(prchat_event_channels('presence-mychanel','message-reaction',['message_id'=>1]) === ['private-prchat-staff-2','private-prchat-staff-3'], 'Reaction recipients derive from database');
check(prchat_event_channels('presence-mychanel','message-hidden',['viewer_id'=>2]) === ['private-prchat-staff-2'], 'Private hide event reaches one viewer');
check(prchat_event_channels('group-chat','group-renamed',['group_id'=>1]) === ['private-prchat-groups-2','private-prchat-groups-3'], 'Group notification reaches members only');
$ci->staff_model->update_permissions(['prchat'=>['view_own','create','edit','delete']],2);
check(prchat_authorize_staff_request('addChatGroup',new ChatFixtureInput([],['members'=>[2,3]])), 'Granted Create usable');
check(prchat_authorize_staff_request('editMessage',new ChatFixtureInput([],['id'=>1])), 'Granted Edit usable for own conversation');
check(!prchat_authorize_staff_request('editMessage',new ChatFixtureInput([],['id'=>2])), 'Granted Edit does not bypass ownership');
check(!prchat_authorize_staff_request('addChatGroupMembers',new ChatFixtureInput([],['group_id'=>1,'group_name'=>'presence-group-two','members'=>[3]])), 'Spoofed group name denied');
check(prchat_authorize_staff_request('deleteMessage',new ChatFixtureInput([],['id'=>1])), 'Granted Delete usable for own conversation');
$database->where('staffid',3)->update('audit_staff',['active'=>0]);
check(prchat_event_channels('presence-mychanel','send-event',['from'=>2,'to'=>3]) === ['private-prchat-staff-2'], 'Inactive recipient excluded');
$ci->staff_model->update_permissions([],2);
check(!prchat_staff_can_chat() && !prchat_staff_can_subscribe('private-prchat-staff-2'), 'Revoked Chat permission removes access');
echo 'PASS ' . $GLOBALS['checks'] . " database checks using native models and CodeIgniter driver; temporary tables only\n";

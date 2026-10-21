<?php
// Only the CLI test's loopback PHP server can run this fixture. It never uses
// live chat tables or uploads. The environment path is an ephemeral directory.
$storage = getenv('PRCHAT_UPLOAD_FIXTURE_ROOT');
if (PHP_SAPI !== 'cli-server' || ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1'
    || !$storage || !is_dir($storage) || !is_file($storage.'/fixture.marker')) {
    http_response_code(404); exit;
}
error_reporting(E_ALL & ~E_DEPRECATED);
define('PRCHAT_LOOPBACK_UPLOAD_FIXTURE', true);
define('FCPATH', $storage.'/');
define('PR_CHAT_MODULE_NAME', 'prchat');
define('PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER', $storage.'/audio');
define('PR_CHAT_MODULE_UPLOAD_FOLDER', $storage.'/staff');
define('PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER', $storage.'/groups');
ob_start();
require dirname(__DIR__).'/chat_group_endpoints_regression.php';
ob_end_clean();
require $root.'/system/core/Common.php';
require $root.'/system/core/Security.php';
require $root.'/system/libraries/Upload.php';
// Load only the production upload helpers; the rest register CRM browser hooks.
$helpers = file_get_contents($root.'/modules/prchat/helpers/prchat_helper.php');
foreach (['pr_chat_patch_upload_mimes_for_voice','pr_chat_voice_upload_extensions_string',
 'pr_chat_normalize_allowed_file_types','pr_chat_patch_upload_mimes_for_crm_files',
 'pr_chat_client_crm_attachment_types_string','pr_chat_max_upload_size_kb'] as $fn) {
 $start = strpos($helpers, 'function '.$fn.'(');
 $end = strpos($helpers, "\n}\n", $start) + 2;
 eval(substr($helpers, $start, $end-$start));
}
function &get_mimes() { static $map; if (!$map) $map=require dirname(__DIR__,2).'/application/config/mimes.php'; return $map; }
function module_dir_url($name,$path='') { return '/modules/'.$name.'/'.$path; }
$ci->security=(new ReflectionClass(CI_Security::class))->newInstanceWithoutConstructor();
$ci->lang=new class { function load(...$args) {} function line($key) { return $key; } };
class UploadFixtureLoader {
 private $owner;
 function __construct($owner) { $this->owner=$owner; }
 function library($name,$config) { $this->owner->upload=new CI_Upload($config); }
}
$method=$_GET['action']??'uploadMethod';
$isAvatar=in_array($method,['uploadGroupAvatar','removeGroupAvatar'],true);
$c=($isAvatar || ($_GET['scope']??'')==='staff' || ($_GET['scope']??'')==='group') ? $groupsController : $controller;
$c->input=new class extends ChatFixtureInput {
 function post($key=null,...$rest) { return $key===null?$_POST:($_POST[$key]??null); }
 function get($key=null,...$rest) { return $key===null?$_GET:($_GET[$key]??null); }
 function method($upper=false) { return $upper?'POST':'post'; }
};
$c->output=new ChatFixtureOutput();$c->load=new UploadFixtureLoader($c);
$fixtureOptions['prchat_max_upload_size_kb']=10240;
$db->query('ALTER TABLE audit_chatgroups ADD group_image VARCHAR(255) NULL');
$db->query('CREATE TEMPORARY TABLE audit_chatgroupsharedfiles (sender_id INT, group_id INT, file_name VARCHAR(255))');
$db->where('id',$groupId)->update('audit_chatgroups',['group_image'=>is_file($storage.'/avatar.json')?json_decode(file_get_contents($storage.'/avatar.json'),true):null]);
// Each request recreates TEMPORARY tables; preserve only the fixture photo reference.
if (isset($_POST['group_id']) && $_POST['group_id']==='fixture') $_POST['group_id']=$groupId;
if (isset($_POST['to_group']) && $_POST['to_group']==='fixture') $_POST['to_group']=$groupId;
if (($_GET['scope']??'')==='client-portal') { $fixtureStaffLoggedIn=false;$fixtureClientLoggedIn=true; }
if (($_GET['fail']??'')==='db') {
 $db->query('ALTER TABLE audit_chatgroups ADD CONSTRAINT fixture_reject_photo CHECK (group_image IS NULL)');
}
$startFiles=count(glob($storage.'/groups/avatars/*')?:[]);
if ($isAvatar || ($_GET['scope']??'')==='group') {
 if (!prchat_authorize_staff_request($method,$c->input)) {
  http_response_code(403);echo json_encode(['error'=>'Forbidden']);exit;
 }
}
if (!in_array($method,['uploadMethod','groupUploadMethod','uploadGroupAvatar','removeGroupAvatar'],true)) {http_response_code(404);exit;}
$c->$method();
if ($isAvatar) file_put_contents($storage.'/avatar.json',json_encode($db->where('id',$groupId)->get('audit_chatgroups')->row('group_image')));
http_response_code($c->output->status);
header('Content-Type: application/json');echo json_encode($c->output->data);

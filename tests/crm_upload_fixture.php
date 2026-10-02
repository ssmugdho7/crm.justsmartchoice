<?php
// Loopback-only multipart regression harness. No CRM database or notifications.
if (PHP_SAPI !== 'cli-server' || ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
$root = sys_get_temp_dir() . '/crm-upload-regression-' . getmypid() . '/';
define('PROJECT_ATTACHMENTS_FOLDER', $root . 'new-parent/projects/');
define('EXPENSE_ATTACHMENTS_FOLDER', $root . 'new-parent/expenses/');
class FixtureHooks { function apply_filters($name,$value){return $value;} function do_action(...$args){} }
function hooks(){static $h;return $h ?? ($h=new FixtureHooks());}
function log_message(...$args){}
function sanitize_file_name($name){return basename(str_replace('\\','/',$name));}
function unique_filename($path,$name){return $name;}
function get_option($name){return '.png, .txt';}
function get_allowed_mime_types(){return ['text/plain','image/png'];}
function is_client_logged_in(){return false;}
function get_staff_user_id(){return 1;}
function db_prefix(){return 'fixture_';}
function is_image($path){return false;}
function _l($name){return $name;}
class FixtureDb {
 public $rows=[];
 function insert($table,$data){$this->rows[]=$data;return true;}
 function insert_id(){return count($this->rows);}
 function where_in(...$args){return $this;}
 function get(...$args){return $this;}
 function result_array(){return $this->rows;}
}
class FixtureMisc { function add_attachment_to_database($id,$type,$data){$GLOBALS['fixture']->db->insert('files',$data[0]);} }
class FixtureAgent { function browser(){return "Chrome";} }
class FixtureInput { function post($name){return false;} }
class FixtureLoad { function model($name){} }
class FixtureProjects { function new_project_file_notification(...$args){} }
$fixture=(object)['agent'=>new FixtureAgent(),'db'=>new FixtureDb(),'misc_model'=>new FixtureMisc(),'input'=>new FixtureInput(),'load'=>new FixtureLoad(),'projects_model'=>new FixtureProjects()];
function &get_instance(){return $GLOBALS['fixture'];}
require dirname(__DIR__) . '/application/helpers/upload_helper.php';
$type=$_GET['type']??'expense';
$result=$type==='project'?handle_project_file_uploads(999999):handle_expense_attachments(999999);
$path=($type==='project'?PROJECT_ATTACHMENTS_FOLDER:EXPENSE_ATTACHMENTS_FOLDER).'999999/';
$rows=$fixture->db->rows;$file=$rows ? $path.$rows[0]['file_name'] : '';
header('Content-Type: application/json');
echo json_encode(['success'=>is_array($result)?$result['success']:$result,'saved'=>is_file($file),'index'=>is_file($path.'index.html'),'contents'=>$file && is_file($file)?file_get_contents($file):null]);
foreach(glob($path.'*')?:[] as $f)unlink($f);
if(is_dir($path))rmdir($path);
foreach([PROJECT_ATTACHMENTS_FOLDER,EXPENSE_ATTACHMENTS_FOLDER,$root.'new-parent/',$root] as $dir)if(is_dir($dir))@rmdir($dir);

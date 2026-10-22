<?php
// SQL fixtures use connection-local temporary tables only; no customer rows are read or changed.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$site = getenv('CRM_SITE_ROOT') ?: $root;
define('BASEPATH', $site . '/system/'); define('APPPATH', $site . '/application/'); define('ENVIRONMENT', 'testing');
function log_message(...$args) {} function is_php($v) { return version_compare(PHP_VERSION, $v, '>='); }
function show_error($m) { throw new RuntimeException($m); }
function db_prefix() { return 'audit_scpr_'; }
function &get_instance() { return $GLOBALS['ci']; }
function scl_licensed($module) { return $GLOBALS['fixtureLicensed'] ?? true; }
function get_staff_user_id() { return 42; }
function staff_can($permission, $feature) { return in_array($feature . ':' . $permission, $GLOBALS['permissions'], true); }
function _l($key) { return $key; } function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function admin_url($path='') { return 'https://fixture.invalid/admin/' . $path; }
function get_option($name) { return 0; }
class ProjectHooks { public $names=[]; function add_filter($name,...$args) { $this->names[]=$name; } function add_action($name,...$args) { $this->names[]=$name; } }
function hooks() { static $h; return $h ?: $h=new ProjectHooks; }
class ProjectRouter { public $class='Projects', $method='index'; function fetch_class() {return $this->class;} function fetch_method(){return $this->method;} }
$checks=0; function check($value,$label) { global $checks; if (!$value) throw new RuntimeException($label); $checks++; }
require $site . '/system/database/DB.php'; require $site . '/application/config/app-config.php';
$db=DB(['hostname'=>APP_DB_HOSTNAME,'username'=>APP_DB_USERNAME,'password'=>APP_DB_PASSWORD,'database'=>APP_DB_NAME,'dbdriver'=>'mysqli','dbprefix'=>'audit_scpr_','pconnect'=>false,'db_debug'=>false,'char_set'=>'utf8mb4','dbcollat'=>'utf8mb4_general_ci','save_queries'=>true],true);
$ci=(object)['db'=>$db,'router'=>new ProjectRouter,'currencies_model'=>new class {function get_base_currency(){return (object)['id'=>1];}}];
$permissions=['projects:view','projects:create'];
require $root . '/modules/sc_projects_workspace/includes/projects.php';
foreach ([
 'projects (id INT PRIMARY KEY, status INT, progress_from_tasks INT, progress DECIMAL(8,2), project_cost DECIMAL(24,8), billing_type INT, clientid INT)',
 'project_members (project_id INT, staff_id INT)',
 'tasks (id INT PRIMARY KEY, rel_id INT, rel_type VARCHAR(20), status INT)',
 'taskstimers (task_id INT, start_time BIGINT, end_time BIGINT NULL)',
 'clients (userid INT PRIMARY KEY, default_currency INT)'
] as $schema) check((bool)$db->query('CREATE TEMPORARY TABLE audit_scpr_'.$schema),'Temporary schema');
check((bool)$db->query("INSERT INTO audit_scpr_projects VALUES (1,2,0,40,100.10,1,1),(2,2,1,0,200.20,1,1),(3,2,1,0,99,1,2),(4,4,0,0,0,1,1),(5,5,0,0,0,1,1),(6,2,0,25,1000,2,1)"),'Project fixtures');
$db->query('INSERT INTO audit_scpr_project_members VALUES (1,42),(2,99)');
$db->query("INSERT INTO audit_scpr_tasks VALUES (1,2,'project',5),(2,2,'project',1),(3,2,'project',5),(4,2,'project',1),(5,1,'project',5)");
$db->query('INSERT INTO audit_scpr_taskstimers VALUES (1,1000,4600),(5,5000,6800)');
$db->query('INSERT INTO audit_scpr_clients VALUES (1,0),(2,2)');
$result=scpr_portfolio();
check($result['active']===4,'Global accessible counts');
check((float)$result['average']===53.8,'Manual and task progress; empty task project = complete');
check($result['seconds']===5400,'Logged seconds');
$values=array_column($result['values'],'amount','currency_id');
check($values[1]==='300.30000000','Exact decimal addition; exclude hourly contract rate');
check($values[2]==='99.00000000','Currencies remain separate');
$permissions=['projects:create']; $own=scpr_portfolio();
check($own['active']===1,'Membership scope without global View');
check((float)$own['average']===40.0,'No unassigned progress leakage');
check($own['seconds']===1800,'No unassigned timer leakage');
check(count($own['values'])===1 && $own['values'][0]['amount']==='100.10000000','Membership financial scope');
$permissions=[];check(scpr_portfolio()['values']===[],'Hide financial aggregate without native Create grant');
$progress=$db->query('SELECT id,'.scpr_progress_sql('p').' AS progress FROM audit_scpr_projects p')->result_array();
$p=array_column($progress,'progress','id');check((float)$p[4]===100.0,'Native finished ID = 4');check((float)$p[5]===0.0,'Cancelled ID = 5 not forced complete');
$task=['addedfrom'=>1,'current_user_is_assigned'=>0,'is_added_from_contact'=>0];
check(!scpr_can_change_task($task),'Unassigned non-creator cannot change task');
$task['current_user_is_assigned']=42;check(scpr_can_change_task($task),'Native assignee may change task');
$task['current_user_is_assigned']=0;$task['addedfrom']=42;check(scpr_can_change_task($task),'Native staff creator may change task');
$task['is_added_from_contact']=1;check(!scpr_can_change_task($task),'Matching contact ID is not a staff creator');
$permissions=['tasks:edit'];check(scpr_can_change_task($task),'Task Edit grant');
$ci->router->class='Clients';check(scpr_table_headers(['native'])===['native'],'Client tables untouched');
$ci->router->class='Projects';check(count(scpr_table_headers(['native']))===2,'Progress header added');
$input=['id'=>7,'scpr_progress'=>150];
$row=scpr_table_row(['7','<div class="row-options"><a href="'.admin_url('projects/view/7').'">view</a> | <a href="'.admin_url('projects/project/7').'">edit</a></div>','DT_RowClass'=>'has-row-options'],$input);
check(strpos($row[1],'prj-btn-view-pill')!==false && strpos($row[1],'prj-btn-edit-pill')!==false,'Pill URLs retained');
check(strpos($row[2],'aria-valuenow="100"')!==false,'Clamp progress display');
check($row['DT_RowClass']==='has-row-options','Keep native row metadata');
foreach(['english','spanish'] as $locale) { $lang=[]; require $root.'/modules/sc_projects_workspace/language/'.$locale.'/sc_projects_workspace_lang.php';$keys[$locale]=array_keys($lang); }
check($keys['english']===$keys['spanish'],'Bilingual key parity');
check(scpr_view(['path'=>'admin/tasks/manage','data'=>[]])===['path'=>'admin/tasks/manage','data'=>[]],'Other views unchanged');
$fixtureLicensed=false;check(scpr_view(['path'=>'admin/projects/manage','data'=>[]])===['path'=>'admin/projects/manage','data'=>[]],'Unlicensed workspace preserves native Projects');
check(scpr_table_headers(['native'])===['native'],'Unlicensed workspace leaves table schema intact');
echo "PASS: $checks Projects SQL, membership, money, progress, action and translation checks\n";

<?php
// Exercise actual helper and row hook without loading the application or customer data.
define('BASEPATH', __DIR__);
class BoardHooks { function add_action(...$args) {} function add_filter(...$args) {} }
function hooks() { static $h; return $h ??= new BoardHooks; }
function _l($key) { return $key; }
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function admin_url($path) { return 'https://example.invalid/admin/' . $path; }
function scl_licensed($module) { return true; }
class projects_model { function get_project_statuses() { return [['id'=>2,'color'=>'#059669','name'=>'In Progress','order'=>1]]; } }
$ci=(object)['projects_model'=>new projects_model, 'router'=>new class { function fetch_class() { return 'projects'; } }];
function &get_instance() { return $GLOBALS['ci']; }
require dirname(__DIR__).'/application/helpers/projects_helper.php';
require dirname(__DIR__).'/modules/sc_projects_workspace/includes/projects.php';
function check($condition,$message) { if (!$condition) throw new RuntimeException($message); }
check(get_project_status_by_id(2)['name']==='In Progress','Configured status preserved');
$unknown=get_project_status_by_id(91);
check($unknown['id']===91 && $unknown['name']==='project_status #91','Unknown status remains truthful and identifiable');
$row=array_fill(0,8,''); $row[1]='Project';
for($i=1;$i<=5;$i++) $row[6].='<a href="https://example.invalid/admin/profile/'.$i.'"><img src="staff'.$i.'"></a>';
$row[6].='<span class="hide">Alice, Bob, Carol, David, Elena</span>';
$result=scpr_table_row($row,['id'=>1,'scpr_progress'=>55]);
check(substr_count($result[6],'<a ')===3,'Crew limited to three avatars');
check(strpos($result[6],'>+2</span>')!==false,'Overflow count accurate');
check(strpos($result[6],'Alice, Bob, Carol, David, Elena')!==false,'All crew names preserved for export');
check(strpos(end($result),'aria-valuenow="55"')!==false,'Native progress kept');
$scopeRow=scpr_table_row($row,['id'=>1,'scpr_progress'=>55,'name'=>'<Project>','description'=>'<p>Day 1 &amp; prep</p><img src=x onerror=alert(1)><p>Day 2</p>']);
check(strpos($scopeRow[1],'<img src=x')===false,'Scope cannot inject HTML');
check(strpos($scopeRow[1],'data-title="&lt;Project&gt;"')!==false,'Scope title escaped');
check(strpos($scopeRow[1],'Day 1 &amp; prep')!==false && strpos($scopeRow[1],'Day 2')!==false,'Full chronological scope retained');
echo "PASS project status identity, crew density/export and progress\n";

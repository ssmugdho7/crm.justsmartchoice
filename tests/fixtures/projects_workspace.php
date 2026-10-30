<?php
// Synthetic component renderer, deliberately inaccessible from a web server.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=dirname(__DIR__,2); define('BASEPATH',$root.'/system/');
function e($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
function _l($key){return $GLOBALS['lang'][$key]??ucwords(str_replace('_',' ',$key));}
function admin_url($p=''){return 'https://fixture.invalid/admin/'.$p;}
function staff_can($p,$feature){return !in_array('--restricted',$GLOBALS['argv'],true);}
function scl_licensed($module) { return $GLOBALS['fixtureLicensed'] ?? true; }
function get_staff_user_id(){return 42;}
function task_priority($id){return ['','Low','Medium','High','Urgent'][$id];}
function get_task_status_by_id($id){return ['name'=>$id===5?'Complete':'In Progress'];}
function _d($v){return $v;}
function seconds_to_time_format($s){return sprintf('%02d:%02d',floor($s/3600),floor($s%3600/60));}
function get_currency($id){return $id===1?'USD':'EUR';}
function app_format_money($v,$c){return $c.' '.number_format((float)$v,2);}
class FixtureHooks {function add_filter(...$a){}function add_action(...$a){}function apply_filters($n,$v){return $v;}}
function hooks(){return new FixtureHooks;}
class FixtureCI { public $router,$input; function __construct(){ $this->router=new class{function fetch_class(){return 'Projects';}function fetch_method(){return ($GLOBALS['argv'][1]??'directory')==='directory'?'index':'view';}};$this->input=new class{function get($k){return $k==='group'?'project_overview':null;}};}}
function get_instance(){return $GLOBALS['ci'];}
$ci=new FixtureCI;
require $root.'/application/helpers/projects_helper.php';
require $root.'/modules/sc_projects_workspace/includes/projects.php';
$lang=[];require $root.'/modules/sc_projects_workspace/language/'.(in_array('--spanish',$argv,true)?'spanish':'english').'/sc_projects_workspace_lang.php';
$project=(object)['id'=>108,'name'=>'Highland solar array & battery backup','client_data'=>(object)['company'=>'Fixture Construction'],'billing_type'=>1,'project_cost'=>'18400.00','deadline'=>'2026-11-20','settings'=>(object)['available_features'=>['project_files'=>!in_array('--restricted',$argv,true)]]];
$percent=75;$currency='USD';$project_total_logged_time=304200;
$scpr_portfolio=['active'=>19,'average'=>68,'seconds'=>2311200,'values'=>[['amount'=>'342850.00','currency_id'=>1]]];
$scpr_tasks=[['id'=>1,'status'=>2,'name'=>'Review structural drawing & schedule inspection','priority'=>4,'duedate'=>'2026-11-02','milestone_name'=>'Permitting','addedfrom'=>42,'is_added_from_contact'=>0],['id'=>2,'status'=>5,'name'=>'Confirm site access','priority'=>2,'duedate'=>'2026-11-01','addedfrom'=>1,'current_user_is_assigned'=>42]];
$base=$root.'/modules/sc_projects_workspace/views/projects/';
$tabs=[];foreach(['project_overview','project_tasks','project_milestones','project_files','project_discussions','project_gantt','project_invoices','project_timesheets','project_activity','project_notes'] as $slug)$tabs[$slug]=['slug'=>$slug,'name'=>ucwords(str_replace('_',' ',$slug)),'icon'=>'fa fa-folder'];
(function() use ($root,$argv,$project,$percent,$currency,$project_total_logged_time,$scpr_portfolio,$scpr_tasks,$base,$tabs) { ?><!doctype html><html lang="<?=in_array('--spanish',$argv,true)?'es':'en';?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>
<?=file_get_contents($root.'/assets/plugins/bootstrap/css/bootstrap.min.css');?>
body{font:14px Arial,sans-serif}#wrapper{padding:24px}.panel-body{background:white;padding:20px}.panel_s{margin-bottom:20px}
<?=file_get_contents($root.'/modules/sc_projects_workspace/assets/css/projects.css');?>
</style></head><body class="scpr-projects"><div id="wrapper"><div class="content">
<?php include $base.'navigation.php';
if(($argv[1]??'directory')==='directory'){
 echo '<h1 class="scpr-page-title">'.e(_l('scpr_title')).'</h1><div class="prj-kpi-grid">';
 foreach(['Not Started'=>3,'In Progress'=>19,'On Hold'=>2,'Cancelled'=>1,'Finished'=>24] as $label=>$count) echo '<a class="prj-kpi-card"><span class="prj-kpi-label">'.$label.'</span><strong class="prj-kpi-count">'.$count.'</strong></a>';
 echo '</div>';include $base.'portfolio.php';
 echo '<div class="panel_s"><div class="panel-body"><div class="table-responsive"><table class="table table-projects"><thead><tr><th>#</th><th>Project Name</th><th>Customer</th><th>Members</th><th>Status</th><th>Progress</th></tr></thead><tbody>';
 for($i=0;$i<6;$i++){ $row=scpr_table_row(['108','<a>Highland solar array &amp; battery backup</a><div class="row-options"><a href="'.admin_url('projects/view/108').'">View</a> | <a href="'.admin_url('projects/project/108').'">Edit</a></div>','Fixture Construction','AM / HR','In Progress'],['id'=>108,'scpr_progress'=>75]);echo '<tr><td>'.implode('</td><td>',$row).'</td></tr>';}
 echo '</tbody></table></div></div></div>';
}else{
 include $base.'header.php';echo '<div class="scpr-project-layout"><aside class="scpr-project-sidebar">';include $base.'project_tabs.php';echo '</aside><section class="scpr-project-panel">';include $base.'punchlist.php';echo '</section></div>';
}?></div></div></body></html>

<?php })->call($ci);

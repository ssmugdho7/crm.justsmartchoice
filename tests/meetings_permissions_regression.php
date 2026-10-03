<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
$root = getenv('CRM_SOURCE_ROOT') ?: dirname(__DIR__);
#[AllowDynamicProperties] class AdminController { function __construct() {} }
#[AllowDynamicProperties] class App_Model { public $db; function __construct() {} }
class Flow extends RuntimeException {} class Denied extends Flow {} class Missing extends Flow {}
$checks=0;$permissions=[];$alerts=[];
function check($value,$label){global $checks;if(!$value){throw new RuntimeException($label);}++$checks;}
function has_permission($feature,$staff='',$cap='view'){return in_array($cap,$GLOBALS['permissions'][$feature]??[],true);}
function get_staff_user_id(){return 42;}
function is_staff_logged_in(){return true;}
function access_denied($feature=null){throw new Denied();}
function show_404(){throw new Missing();}
function set_alert($type,$message){$GLOBALS['alerts'][]=[$type,$message];}
function redirect($path,$type=null){throw new Flow($path);}
function admin_url($path=''){return $path;}
function _dt($date){return $date;}
function _l($key){return $key;}
function db_prefix(){return 'tbl';}
function get_option($key){return '0';}
function &get_instance(){return $GLOBALS['ci'];}
function register_staff_capabilities($feature,$definition,$name){$GLOBALS['registered'][$feature]=$definition;}
function load_function($path,$name){
    // Extract the actual named module callback without running activation/upgrader hooks.
    $tokens=token_get_all(file_get_contents($path));$capturing=false;$depth=0;$seenBrace=false;$source='';
    for($i=0;$i<count($tokens);$i++){
        $token=$tokens[$i];
        if(!$capturing && is_array($token) && $token[0]===T_FUNCTION){
            $j=$i+1;while(isset($tokens[$j])&&is_array($tokens[$j])&&$tokens[$j][0]===T_WHITESPACE){$j++;}
            if(isset($tokens[$j])&&is_array($tokens[$j])&&$tokens[$j][1]===$name){$capturing=true;}
        }
        if($capturing){$source.=is_array($token)?$token[1]:$token;if($token==='{'){$depth++;$seenBrace=true;}if($token==='}'&&--$depth===0&&$seenBrace){eval($source);return;}}
    }
    throw new RuntimeException('Missing module callback '.$name);
}
require $root.'/modules/google_meet/controllers/Google_meet.php';
require $root.'/modules/google_meet/models/Google_meet_model.php';
load_function($root.'/modules/google_meet/google_meet.php','google_meet_admin_init');
load_function($root.'/modules/products/products.php','products_module_permissions_for_staff');
define('GOOGLE_MEET_MODULE_NAME','google_meet');
class MenuFixture {
    public $items=[];public $children=[];
    function add_sidebar_menu_item($slug,$item){$this->items[$slug]=$item;}
    function add_sidebar_children_item($slug,$item){$this->children[]=$item['slug'];}
}
$ci=(object)['app_menu'=>new MenuFixture()];google_meet_admin_init();
check(isset($GLOBALS['registered']['google_meet']['capabilities']['view_own']),'Meet capabilities use native role-editor schema');
check($ci->app_menu->items===[],'Meet menu hidden without access');
$permissions=['google_meet'=>['view_own']];google_meet_admin_init();
check(in_array('google-meet-dashboard',$ci->app_menu->children,true) && !in_array('google-meet-settings',$ci->app_menu->children,true) && !in_array('google-meet-new-meeting',$ci->app_menu->children,true),'View Own menu excludes create and settings');
$ci->app_menu=new MenuFixture();$permissions=['google_meet'=>['create']];google_meet_admin_init();
check($ci->app_menu->items['google-meet']['href']==='google_meet/create' && $ci->app_menu->children===['google-meet-new-meeting'],'Create-only menu opens allowed form');
$definition=products_module_permissions_for_staff([]);
check(array_keys($definition['products']['capabilities'])===['view','create','edit','delete'],'Product Edit/Delete are assignable');
class InputFixture {public $posted=[],$query=[];function post($key=null,$xss=false){return $key===null?$this->posted:($this->posted[$key]??null);}function get($key,$xss=false){return $this->query[$key]??null;}}
class LoadFixture {public $data=[];function view($view,$data){$this->data=$data;throw new Flow('render '.$view);}}
class MeetingFixture {
    public $filters=[],$summaryFilters=[],$deleted=[],$updateResult=false;
    function get($id=null){return $id===404?null:(object)['id'=>$id,'created_by'=>$id===7?42:99,'assigned_staff_id'=>99];}
    function staff_has_meeting($meeting,$staff){return $meeting->created_by===$staff;}
    function report_summary($filters=[]){$this->summaryFilters=$filters;return [];}
    function report_meetings($filters=[]){$this->filters=$filters;return [];}
    function active_staff(){return [];}
    function active_contacts(){return [];}
    function projects_for_select(){return [];}
    function attendees($id){return [];}
    function comments($id){return [];}
    function update($id,$post){return $this->updateResult;}
    function start($id){return $this->updateResult;}
    function finish($id){return $this->updateResult;}
    function add_comment($id,$comment){return $this->updateResult;}
    function delete_many($ids){$this->deleted=$ids;return count($ids);}
}
$c=(new ReflectionClass('Google_meet'))->newInstanceWithoutConstructor();$c->input=new InputFixture();$c->load=new LoadFixture();$c->google_meet_model=new MeetingFixture();
$permissions=[];
foreach(['index','create','view','add_comment','start','finish','notify','save_shared_link','calendar','settings','reports','join','health','help','mass_delete','export_csv','meeting_modal','test_notifications','send_test_notifications','fix_health','delete'] as $method){
    try{$c->$method(7);throw new RuntimeException('Unguarded meeting route '.$method);}catch(Denied $e){check(true,'Route denied before read/write: '.$method);}
}
$permissions=['google_meet'=>['view_own']];
foreach(['index','reports','join'] as $method){$c->input->query=['staff_id'=>99];try{$c->$method();}catch(Flow $e){}
    check(($c->google_meet_model->filters['staff_id']??null)===42,'Own meeting lists ignore foreign staff query: '.$method);
    if($method!=='join'){check(($c->google_meet_model->summaryFilters['staff_id']??null)===42,'Own summary uses same scope');}
}
foreach(['view','meeting_modal','add_comment','calendar'] as $method){
    try{$c->$method(8);throw new RuntimeException('Foreign record read allowed');}catch(Denied $e){check(true,'Direct foreign record denied: '.$method);}
}
try{$c->view(7);}catch(Flow $e){check(!($e instanceof Denied),'Own meeting detail available');}
$permissions=['google_meet'=>['view']];$c->input->query=['staff_id'=>99];try{$c->reports();}catch(Flow $e){}
check($c->google_meet_model->filters['staff_id']===99,'Global viewers retain requested staff filter');
foreach(['start','finish','notify','save_shared_link','delete'] as $method){try{$c->$method(8);throw new RuntimeException('Read-only user mutated meeting');}catch(Denied $e){check(true,'Read does not grant write: '.$method);}}
$permissions=['google_meet'=>['view_own','edit','delete']];
foreach(['create','start','finish','notify','save_shared_link','delete'] as $method){try{$c->$method(8);throw new RuntimeException('Foreign write allowed');}catch(Denied $e){check(true,'Foreign record cannot be changed: '.$method);}}
$c->input->posted=['ids'=>[7,8]];try{$c->mass_delete();throw new RuntimeException('Mixed batch deleted');}catch(Denied $e){check($c->google_meet_model->deleted===[],'Mixed deletion batch denied before any deletion');}
$c->input->posted=['subject'=>'Updated'];$alerts=[];try{$c->create(7);}catch(Flow $e){}
check(count($alerts)===1&&$alerts[0][0]==='danger','Failed update never claims success');
$c->google_meet_model->updateResult=true;$alerts=[];try{$c->create(7);}catch(Flow $e){}
check(count($alerts)===1&&$alerts[0][0]==='success','Successful update still reports success');
foreach (['start','finish','add_comment'] as $method) {
    foreach ([false,true] as $saved) {
        $c->google_meet_model->updateResult=$saved;$alerts=[];
        try {$c->$method(7);}catch(Flow $e){}
        check(count($alerts)===1 && $alerts[0][0]===($saved?'success':'danger'),'Status/comment feedback matches actual result');
    }
}
$permissions=['settings'=>['view']];$c->input->posted=['google_meet_enabled'=>1];try{$c->settings();throw new RuntimeException('Settings viewer wrote config');}catch(Denied $e){check(true,'Settings writes require native Edit Settings');}
$c->input->posted=[];try{$c->settings();}catch(Flow $e){check(!($e instanceof Denied),'Settings viewers can read settings');}
class DbFixture {
    public $calls=[],$write=false,$attends=0;
    function __call($name,$args){$this->calls[]=[$name,$args];return $this;}
    function count_all_results($table){$this->calls[]=['count_all_results',[$table]];return $this->attends;}
    function update($table,$values){return $this->write;}
    function insert($table,$values){return $this->write;}
    function get($table=null){return new class {function row(){return (object)['total'=>2,'completed'=>1,'minutes'=>60];}function result_array(){return [];}};}
}
class UpdateFixture extends Google_meet_model {
    public $sideEffects=0;
    function get($id=null){return $id===404?null:(object)['id'=>7,'created_by'=>42,'assigned_staff_id'=>99,'subject'=>'Existing','start_time'=>'2026-10-03 10:00:00','end_time'=>'2026-10-03 11:00:00','meet_link'=>'https://meet.google.com/abc-defg-hij'];}
    function sync_attendees($id,$data){$this->sideEffects++;}
    function add_log($id,$action,$message){$this->sideEffects++;}
    function notify_attendees($id){$this->sideEffects++;return true;}
}
set_error_handler(function($level,$message,$file,$line){if($level&error_reporting()){throw new ErrorException($message,0,$level,$file,$line);}});
$model=new UpdateFixture();$model->db=new DbFixture();
check(!$model->update(7,['subject'=>'Updated'])&&$model->sideEffects===0,'DB failure does not sync attendees/log/notify');
check(!$model->update_meet_link(404,'https://meet.google.com/abc-defg-hij')&&$model->sideEffects===0,'Missing record link update fails safely');
check(!$model->update_meet_link(7,'https://meet.google.com/abc-defg-hij')&&$model->sideEffects===0,'Link DB failure reports false');
foreach(['start','finish'] as $method){check(!$model->$method(7),'Status DB failure reports false');check(!$model->$method(404),'Missing meeting status update reports false');}
check(!$model->add_comment(7,''),'Empty comment rejected');
check(!$model->add_comment(7,'Fixture comment'),'Comment DB failure reports false');
$model->db->write=true;check($model->update_meet_link(7,'https://meet.google.com/abc-defg-hij'),'Valid link write succeeds');
$model=new Google_meet_model();$model->db=new DbFixture();
check($model->staff_has_meeting((object)['id'=>7,'created_by'=>42,'assigned_staff_id'=>99],42),'Creator owns meeting');
check($model->staff_has_meeting((object)['id'=>7,'created_by'=>99,'assigned_staff_id'=>42],42),'Assigned staff owns meeting');
$model->db->attends=1;check($model->staff_has_meeting((object)['id'=>7,'created_by'=>99,'assigned_staff_id'=>99],42),'Invited staff owns meeting');
$model->db->attends=0;check(!$model->staff_has_meeting((object)['id'=>7,'created_by'=>99,'assigned_staff_id'=>99],42),'Unrelated staff not owner');
foreach(['report_summary','report_meetings'] as $method){$model->db->calls=[];$model->$method(['staff_id'=>42]);
    check(in_array(['where',['gm.created_by',42]],$model->db->calls,true)&&in_array(['or_where',['gm.assigned_staff_id',42]],$model->db->calls,true),'Native query builder scopes '.$method.' to owner/assignee');
    check(in_array(['or_where',['gm.id IN (SELECT meeting_id FROM tblgoogle_meet_attendees WHERE staff_id=42)',null,false]],$model->db->calls,true),'Native query builder includes invited staff');
}
echo "PASS: $checks meeting permissions, ownership, menu, reporting and failure-feedback checks\n";

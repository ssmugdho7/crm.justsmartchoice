<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('BASEPATH',__DIR__);$root=dirname(__DIR__);$checks=0;$canDelete=true;$canEdit=true;
function check($value,$label){if(!$value)throw new RuntimeException($label);$GLOBALS['checks']++;}
function get_option($name){return ['jitsi_server_domain'=>'meet.jit.si','google_meet_timezone'=>'America/New_York'][$name]??'';}
function site_url($path=''){return '/'.$path;}function admin_url($path=''){return '/admin/'.$path;}
function module_dir_url($module,$path=''){return '/modules/'.$module.'/'.$path;}
function html_escape($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function has_permission($feature,$staff='',$cap='view'){return $cap==='delete'?$GLOBALS['canDelete']:($cap==='edit'?$GLOBALS['canEdit']:true);}
function google_meet_lang($key,$fallback){return $fallback;}function _l($key){return $key;}
function _dt($value){return $value;}
function init_head(){}function init_tail(){}
function form_open($url,$attributes=[]){$html='<form method="post" action="'.html_escape($url).'"';foreach($attributes as $key=>$value){$html.=' '.html_escape($key).'="'.html_escape($value).'"';}return $html.'><input type="hidden" name="csrf" value="fixture-token">';}
function form_close(){return '</form>';}
function render_textarea($name,$label,$value=''){return '<label>'.html_escape($label).'<textarea name="'.html_escape($name).'">'.html_escape($value).'</textarea></label>';}
class App_Model {function __construct(){}}
require $root.'/modules/google_meet/models/Google_meet_model.php';
class FixtureSecurity {function get_csrf_token_name(){return 'csrf';}function get_csrf_hash(){return 'fixture-token';}}
class ViewRenderer {
    public $security,$load,$google_meet_model;
    function __construct(){ $this->security=new FixtureSecurity();$this->google_meet_model=new Google_meet_model();$this->load=new class($this){private $renderer;function __construct($renderer){$this->renderer=$renderer;}function view($name,$data=[]){if(strpos($name,'google_meet/')===0)$name=substr($name,12);echo $this->renderer->render($name,$data);}}; }
    function render($view,$data){extract($data);ob_start();include $GLOBALS['root'].'/modules/google_meet/views/'.$view.'.php';return ob_get_clean();}
}
set_error_handler(function($level,$message,$file,$line){if($level&error_reporting())throw new ErrorException($message,0,$level,$file,$line);});
$renderer=new ViewRenderer();$values=jitsi_prepare_meeting_link('');
$meeting=(object)($values+['id'=>7,'subject'=>'Planning review','title'=>'Planning review','description'=>'Review scope and next steps','status'=>'scheduled','start_time'=>'2026-10-07 10:00:00','end_time'=>'2026-10-07 10:30:00','duration_minutes'=>30,'notes'=>'STAFF_PRIVATE_SENTINEL']);
$data=['title'=>$meeting->subject,'meeting'=>$meeting,'room'=>jitsi_meeting_room($meeting),'current_user'=>['name'=>'Test Host','email'=>'host@example.test','is_host'=>true],'note_key'=>str_repeat('a',32),'attendees'=>[['name'=>'Test Customer','email'=>'customer@example.test']],'comments'=>[['id'=>1,'staff_name'=>'Test Host','comment'=>'STAFF_PRIVATE_SENTINEL']]];
$admin=$renderer->render('room',$data);$clientData=$data;$clientData['current_user']=['name'=>'Test Customer','email'=>'customer@example.test','is_host'=>false];$client=$renderer->render('client/room',$clientData);
check(strpos($admin,'gm-room-note')!==false&&strpos($admin,'STAFF_PRIVATE_SENTINEL')!==false,'Admin room renders notes');
check(strpos($client,'STAFF_PRIVATE_SENTINEL')===false&&strpos($client,'noteUrl')===false&&strpos($client,'gm-room-note')===false,'Customer has no private note payload');
check(strpos($admin,'method="post" action="/admin/google_meet/delete/7"')!==false&&strpos($admin,'name="csrf"')!==false,'Confirmed CSRF POST delete');
$canDelete=false;check(strpos($renderer->render('room',$data),'google_meet/delete')===false,'Delete permission respected');$canDelete=true;
$data['title']='</script><script>alert(1)</script>';$data['current_user']['name']=$data['title'];$data['comments'][0]['comment']='<img src=x onerror=alert(1)>';
$unsafe=$renderer->render('room',$data);
check(strpos($unsafe,'<script>alert(1)</script>')===false&&strpos($unsafe,'<img src=x')===false,'Room and timeline output escaped');
preg_match('~<script type="application/json" id="gm-room-config">(.*?)</script>~s',$unsafe,$match);$config=json_decode($match[1],true,512,JSON_THROW_ON_ERROR);
check($config['userInfo']['displayName']===$data['title'],'Encoded user info round trips without script injection');
$data['title']=$meeting->subject;$data['current_user']['name']='Test Host';$data['comments'][0]['comment']='Review scope and next steps';
$data['meetings']=[(array)$meeting+['assigned_staff_name'=>'Test Host','created_by_name'=>'Test Host','attendee_count'=>1,'created_by'=>42,'assigned_staff_id'=>42]];$data['summary']=['total'=>1,'completed'=>0,'minutes'=>30];
$manage=$renderer->render('manage',$data);check(strpos($manage,'href="/admin/google_meet/room/7"')!==false&&strpos($manage,'data-link="'.html_escape($meeting->meet_link).'"')!==false,'Admin list joins embedded and copies shared provider URL');
$details=$renderer->render('client/view',$data);check(strpos($details,'Share meeting')!==false,'Customer details share modal');
$meeting->meet_link='';$meeting->room_name=null;$pending=$renderer->render('client/view',$data);
check(strpos($pending,'disabled>Share meeting')!==false&&strpos($pending,'>Join meeting</a>')===false,'Pending client room cannot join/share');
$dashboard=$renderer->render('dashboard',$data);$join=$renderer->render('join',$data);$help=$renderer->render('help',$data);
check(strpos($dashboard,'Video Meeting Dashboard')!==false&&strpos($dashboard,'Google Meet')===false,'Dashboard uses video meeting branding');
check(strpos($join,'href="/admin/google_meet/room/7"')!==false,'Join screen opens the authorized CRM room');
check(strpos($help,'host must sign in')!==false&&strpos($help,'Google Calendar API')===false,'Help explains Jitsi host workflow');
// Detail-page presentation must retain the existing room, POST forms and permission gates.
foreach($values as $key=>$value)$meeting->{$key}=$value;
$data['attendees']=[['name'=>'Test Customer','email'=>'customer@example.test','attendee_type'=>'customer','notified'=>1]];
$adminDetails=$renderer->render('view',$data);
$document=new DOMDocument();$document->loadHTML($adminDetails,LIBXML_NOERROR|LIBXML_NOWARNING);$xpath=new DOMXPath($document);
check($xpath->query('//div[@id="wrapper"]//section[@aria-labelledby="gm-detail-room-title"]')->length===1,'Shared-room section is inside the admin content layout');
check(strpos($adminDetails,'href="/admin/google_meet/room/7"')!==false&&strpos($adminDetails,'data-target="#shareMeetingModal"')!==false,'Detail Join and Share retain authorized CRM room and existing modal');
check(strpos($adminDetails,html_escape($meeting->meet_link))!==false,'Detail and invitation share exact saved room');
check($xpath->query('//form[@action="/admin/google_meet/delete/7"][@method="post"]/input[@name="csrf"]')->length===1,'Detail delete retains CSRF POST and confirmation');
check(strpos($xpath->query('//form[@action="/admin/google_meet/delete/7"]')->item(0)->getAttribute('onsubmit'),"return confirm('Delete this meeting")!==false,'Delete still requires explicit browser confirmation');
check($xpath->query('//form[@action="/admin/google_meet/add_comment/7"][@method="post"]//textarea[@name="comment"]')->length===1,'Comment field name and existing POST handler retained');
check(strpos($adminDetails,'google_meet/start/7')!==false&&strpos($adminDetails,'google_meet/finish/7')!==false&&strpos($adminDetails,'google_meet/notify/7')!==false&&strpos($adminDetails,'google_meet/create/7')!==false&&strpos($adminDetails,'google_meet/calendar/7')!==false,'Management and calendar routes preserved');
check($xpath->query('//table[contains(@class,"gm-detail-attendees")]//tbody/tr')->length===1&&strpos($adminDetails,'is-notified')!==false,'Populated attendees retain notification flags');
$canEdit=false;$canDelete=false;$readOnlyDetails=$renderer->render('view',$data);
check(strpos($readOnlyDetails,'google_meet/delete/7')===false&&strpos($readOnlyDetails,'google_meet/notify/7')===false&&strpos($readOnlyDetails,'google_meet/start/7')===false&&strpos($readOnlyDetails,'google_meet/create/7')===false,'Read-only staff do not gain management controls');
check(strpos($readOnlyDetails,'google_meet/add_comment/7')!==false,'Existing viewer comment workflow remains available');$canEdit=true;$canDelete=true;
$emptyData=$data;$emptyData['attendees']=[];$emptyData['comments']=[];$emptyDetails=$renderer->render('view',$emptyData);
check(strpos($emptyDetails,'No attendees yet')!==false&&strpos($emptyDetails,'Add attendees')!==false&&strpos($emptyDetails,'No notes yet')!==false,'Empty states explain next steps');
$savedRoom=$meeting->meet_link;$meeting->meet_link='';$meeting->room_name=null;$pendingDetails=$renderer->render('view',$emptyData);
check(strpos($pendingDetails,'href="/admin/google_meet/room/7"')===false&&strpos($pendingDetails,'disabled><i class="fa fa-share-alt"')!==false,'Pending room cannot join or share');
check(strpos($pendingDetails,'method="post" action="/admin/google_meet/generate_room/7"')!==false&&strpos($pendingDetails,'method="post" action="/admin/google_meet/save_shared_link/7"')!==false,'Room generation and manual link retain protected POST forms');
$canEdit=false;$pendingReadOnly=$renderer->render('view',$emptyData);
check(strpos($pendingReadOnly,'google_meet/generate_room/7')===false&&strpos($pendingReadOnly,'google_meet/save_shared_link/7')===false,'Read-only staff cannot generate or change rooms');$canEdit=true;
$meeting->meet_link=$savedRoom;foreach($values as $key=>$value)$meeting->{$key}=$value;
$savedSubject=$meeting->subject;$savedDescription=$meeting->description;
$meeting->subject='<img src=x onerror=alert(1)>';$meeting->description='<script>alert(1)</script>';
$maliciousData=$data;$maliciousData['comments'][0]['comment']='<img src=x onerror=alert(1)>';$maliciousData['attendees'][0]['name']='<script>alert(1)</script>';
$unsafeDetails=$renderer->render('view',$maliciousData);
check(strpos($unsafeDetails,'<img src=x')===false&&strpos($unsafeDetails,'<script>alert(1)</script>')===false,'Subject, description, attendees and notes are escaped');
$meeting->subject=$savedSubject;$meeting->description=$savedDescription;
$directory=getenv('JITSI_QA_DIR');
if($directory){if(!is_dir($directory))mkdir($directory,0700,true);$head='<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Video meeting QA</title><link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css"><style>body{background:#f3f7f5;padding:24px;font-family:Arial,sans-serif}.content{max-width:1500px;margin:auto}.mtop10{margin-top:10px}.mtop15{margin-top:15px}</style><script src="/jquery.js"></script><script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script></head><body>';
    file_put_contents($directory.'/dashboard.html',$head.'<link rel="stylesheet" href="/modules/google_meet/assets/css/smart_choice_module_standard.css"><link rel="stylesheet" href="/modules/google_meet/assets/css/google_meet_smartchoice.css">'.$dashboard.'</body></html>');file_put_contents($directory.'/help.html',$head.'<link rel="stylesheet" href="/modules/google_meet/assets/css/google_meet_smartchoice.css">'.$help.'</body></html>');
    file_put_contents($directory.'/admin.html',$head.$admin.'</body></html>');file_put_contents($directory.'/client.html',$head.$client.'</body></html>');
    $detailHead=$head.'<link rel="stylesheet" href="/assets/plugins/font-awesome/css/all.min.css"><link rel="stylesheet" href="/assets/plugins/font-awesome/css/v4-shims.min.css"><link rel="stylesheet" href="/modules/google_meet/assets/css/smart_choice_module_standard.css"><link rel="stylesheet" href="/modules/google_meet/assets/css/google_meet_smartchoice.css">';
    file_put_contents($directory.'/details.html',$detailHead.$adminDetails.'</body></html>');file_put_contents($directory.'/details-empty.html',$detailHead.$emptyDetails.'</body></html>');file_put_contents($directory.'/details-pending.html',$detailHead.$pendingDetails.'</body></html>');}
echo "PASS: $checks Jitsi room, share, privacy, permission and escaping render checks\n";

<?php
// CLI-only isolated fixtures. Never boot the CRM or touch its real database/entrypoint.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
define('ENVIRONMENT', 'development');
date_default_timezone_set('UTC'); // Fixture timestamps below intentionally use UTC on every host.
$fixture = sys_get_temp_dir() . '/crm-diagnostics-' . bin2hex(random_bytes(8)); mkdir($fixture, 0700);
$checks = 0;
function verify_diagnostic($condition, $message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
function remove_diagnostic_fixture($path) {
    if (!is_dir($path)) { if (is_file($path) || is_link($path)) @unlink($path); return; }
    foreach (scandir($path) ?: [] as $name) {
        if ($name !== '.' && $name !== '..') remove_diagnostic_fixture($path . '/' . $name);
    }
    @rmdir($path);
}
require dirname(__DIR__) . '/modules/debug_mode/libraries/Debug_mode_log_reader.php';
try {
    $old = $fixture.'/older.php'; $new = $fixture.'/newer.php'; $php = $fixture.'/php.log';
    file_put_contents($old,"ERROR - 2026-10-22 11:00:00 --> Old issue\nERROR - 2026-10-22 11:10:00 --> Another old issue\n");
    file_put_contents($new,"ERROR - 2026-10-23 11:00:00 --> Severity: Warning --> Latest /modules/appointly/example.php\nStack trace:\n#0 handler()\nERROR - 2026-10-23 10:00:00 --> A second issue\nDEBUG - 2026-10-23 12:00:00 --> not an error\n");
    file_put_contents($php,"[23-Oct-2026 11:20:00 UTC] PHP Fatal error: Uncaught Error\nStack trace:\n#0 example.php\n");
    $sources=[['path'=>$old,'label'=>'older.php','type'=>'CRM'],['path'=>$new,'label'=>'newer.php','type'=>'CRM'],['path'=>$php,'label'=>'PHP server log','type'=>'PHP']];
    $rows=Debug_mode_log_reader::read($sources,500,strtotime('2026-10-20'));
    verify_diagnostic(count($rows)===5,'Only error records are reported');
    verify_diagnostic($rows[0]['source']==='PHP' && strpos($rows[0]['line'],'#0 example.php')!==false,'Include early PHP fatal and its multiline trace');
    verify_diagnostic($rows[1]['module']==='appointly' && $rows[1]['severity']==='warning','Module and severity classification');
    verify_diagnostic(strpos($rows[1]['line'],'#0 handler()')!==false,'Preserve the trace with its event');
    verify_diagnostic($rows[3]['file']==='older.php','Sort across sources by actual event timestamp');
    date_default_timezone_set('America/Boise');
    $zoned=Debug_mode_log_reader::read($sources,500,strtotime('2026-10-20'));
    verify_diagnostic($zoned[0]['source']==='CRM','Normalize explicit PHP timezones against the application timezone');
    date_default_timezone_set('UTC');
    verify_diagnostic(count(Debug_mode_log_reader::read($sources,1,strtotime('2026-10-20')))===1,'Apply event limit after sorting');
    verify_diagnostic($rows[1]['route']==='', 'Rows without request context keep an empty route');
    verify_diagnostic(Debug_mode_log_reader::read($sources,500,strtotime('2026-10-24'))===[],'Exclude events outside the reporting window');
    $secret='password=private_password token=private_token Authorization: Bearer privateBearer https://example.test/?api_key=private_query';
    $redacted=Debug_mode_log_reader::redact($secret);
    foreach(['private_password','private_token','privateBearer','private_query'] as $value)verify_diagnostic(strpos($redacted,$value)===false,'Redact credentials and URL queries');
    $large=$fixture.'/large.php';file_put_contents($large,str_repeat('x',2000000)."\nERROR - 2026-10-23 13:00:00 --> Tail failure\n");
    $tail=Debug_mode_log_reader::read([['path'=>$large,'label'=>'large','type'=>'CRM']],10,0,4096);
    verify_diagnostic(count($tail)===1 && strpos($tail[0]['line'],'Tail failure')!==false,'Read a bounded tail of a large file');
    verify_diagnostic(Debug_mode_log_reader::read([['path'=>$fixture.'/missing','label'=>'missing','type'=>'CRM']])===[],'Unavailable log is safe');
    require dirname(__DIR__) . '/modules/debug_mode/libraries/Debug_mode_console_formatter.php';
    mkdir($fixture.'/modules',0700);mkdir($fixture.'/modules/appointly',0700);
    $moduleFile=$fixture.'/modules/appointly/example.php';
    file_put_contents($moduleFile,"<?php\n\$one = 1;\n\$two = 2;\n\$password = 'private_source_secret';\n\$four = 4;\n\$five = 5;\n");
    $consoleRows=[
        ['file'=>'CRM','line'=>'ERROR - 2026-10-23 13:10:00 --> Severity: Warning --> Fixture password=private_log_secret '.$moduleFile.' 4','time'=>strtotime('2026-10-23 13:10:00'),'module'=>'appointly','severity'=>'warning'],
        ['file'=>'CRM','line'=>'ERROR - 2026-10-23 13:11:00 --> Other module','time'=>strtotime('2026-10-23 13:11:00'),'module'=>'other','severity'=>'error'],
        ['file'=>'CRM','line'=>'ERROR - 2026-10-23 13:12:00 --> Shared CRM failure [route=/admin/appointly/appointments] [module=core]','time'=>strtotime('2026-10-23 13:12:00'),'module'=>'core','route'=>'/admin/appointly/appointments','severity'=>'error'],
        ['file'=>'CRM','line'=>'ERROR - 2026-10-23 13:13:00 --> Different page [route=/admin/appointly/services] [module=appointly]','time'=>strtotime('2026-10-23 13:13:00'),'module'=>'appointly','route'=>'/admin/appointly/services','severity'=>'error'],
    ];
    $console=Debug_mode_console_formatter::format($consoleRows,'appointly',$fixture,'/admin/appointly/appointments');
    verify_diagnostic(count($console)===2 && $console[0]['severity']==='warning','Show only unrouted source errors and errors for the exact current page');
    verify_diagnostic($console[0]['location']['file']==='modules/appointly/example.php' && $console[0]['location']['line']===4,'Resolve source only inside the current module');
    verify_diagnostic(count($console[0]['location']['excerpt'])===6 && $console[0]['location']['excerpt'][3]['current']===true,'Include a bounded source excerpt around the error line');
    verify_diagnostic(strpos(json_encode($console),'private_log_secret')===false && strpos(json_encode($console),'private_source_secret')===false,'Redact secrets from messages and source excerpts');
    require dirname(__DIR__) . '/modules/debug_mode/libraries/Debug_mode_issue_tracker.php';
    $repeated=[
        ['severity'=>'error','summary'=>'Repeated failure [request=aaaaaaaaaaaaaaaa]','source'=>'CRM','trace'=>[],'location'=>null,'occurred_at_unix'=>100],
        ['severity'=>'error','summary'=>'Repeated failure [request=bbbbbbbbbbbbbbbb]','source'=>'CRM','trace'=>[],'location'=>null,'occurred_at_unix'=>120],
    ];
    $collapsed=Debug_mode_issue_tracker::collapse($repeated,'expenses','/admin/expenses');
    verify_diagnostic(count($collapsed)===1 && $collapsed[0]['occurrences']===2 && $collapsed[0]['first_seen_unix']===100 && $collapsed[0]['last_seen_unix']===120,'Collapse repeated page failures into one stable issue');
    $state=Debug_mode_issue_tracker::synchronize([],$collapsed,'expenses','/admin/expenses');
    $fingerprint=$collapsed[0]['fingerprint'];
    verify_diagnostic(count(Debug_mode_issue_tracker::openForRoute($state,'expenses','/admin/expenses'))===1,'New page issue remains open in persistent state');
    $state=Debug_mode_issue_tracker::resolve($state,$fingerprint,'expenses','/admin/expenses',130);
    verify_diagnostic($state!==false && Debug_mode_issue_tracker::openForRoute($state,'expenses','/admin/expenses')===[],'Verified code fix resolves the retained issue');
    $state=Debug_mode_issue_tracker::synchronize($state,$collapsed,'expenses','/admin/expenses');
    verify_diagnostic(Debug_mode_issue_tracker::openForRoute($state,'expenses','/admin/expenses')===[],'Old retained log evidence does not reopen a resolved issue');
    $repeated[1]['occurred_at_unix']=140;
    $recurrence=Debug_mode_issue_tracker::collapse($repeated,'expenses','/admin/expenses');
    $state=Debug_mode_issue_tracker::synchronize($state,$recurrence,'expenses','/admin/expenses');
    verify_diagnostic(count(Debug_mode_issue_tracker::openForRoute($state,'expenses','/admin/expenses'))===1,'A recurrence after resolution reopens the issue');
    verify_diagnostic(count(Debug_mode_issue_tracker::decode(json_encode($state)))===1,'Persistent issue registry survives serialization');
    unlink($moduleFile);rmdir($fixture.'/modules/appointly');rmdir($fixture.'/modules');
    class CI_Exceptions { public $logged; public function log_exception($severity,$message,$file,$line){$this->logged=[$severity,$message,$file,$line];} }
    require dirname(__DIR__).'/application/core/App_Exceptions.php';
    $_SERVER['REQUEST_URI']='/admin/appointly/index?token=private_value';
    $exceptions=new App_Exceptions();$exceptions->log_exception('Warning','Fixture issue','/site/modules/appointly/example.php',12);
    verify_diagnostic(strpos($exceptions->logged[1],'[route=/admin/appointly/index]')!==false,'Attach route context');
    verify_diagnostic(strpos($exceptions->logged[1],'[module=appointly]')!==false,'Attach module context');
    verify_diagnostic(strpos($exceptions->logged[1],'private_value')===false,'Never attach request query parameters');
    verify_diagnostic(preg_match('/\[request=[a-f0-9]{16}\]/',$exceptions->logged[1])===1,'Attach per-request correlation identifier');
    $request=preg_match('/\[request=([^\]]+)\]/',$exceptions->logged[1],$m)?$m[1]:'';
    $exceptions->log_exception('Warning','Second issue','/site/core.php',1);
    verify_diagnostic(strpos($exceptions->logged[1],'[request='.$request.']')!==false,'Reuse correlation identifier within a request');
    $filters=[]; $actions=[]; $canDelete=true;
    class BulkFixtureHooks {function add_filter($name,$callback){global $filters;$filters[$name]=$callback;} function add_action($name,$callback){global $actions;$actions[$name]=$callback;}}
    function hooks(){return new BulkFixtureHooks();} function db_prefix(){return 'tbl';} function staff_can($p,$f){global $canDelete;return $canDelete;} function staff_cant($p,$f){return !staff_can($p,$f);} function e($v){return htmlspecialchars((string)$v,ENT_QUOTES);} function base_url($p){return '/'.$p;}
    require dirname(__DIR__).'/application/helpers/smart_choice_bulk_helper.php';
    $row=$filters['proposals_table_row_data'](['Proposal'],['tblproposals.id'=>42]);
    verify_diagnostic(strpos($row[0],'value="42"')!==false,'Use qualified proposal identifier for bulk checkbox');
    verify_diagnostic($filters['proposals_table_row_data'](['Proposal'],[])===['Proposal'],'Never create a zero-ID checkbox');
    $canDelete=false;verify_diagnostic($filters['proposals_table_row_data'](['Proposal'],['id'=>42])===['Proposal'],'Preserve delete permission guard');
    ob_start();$actions['app_admin_footer']();$tag=ob_get_clean();verify_diagnostic(strpos($tag,'\\')===false,'Bulk script URL has no stray slash escape');
    // Endpoint guard and payload handling without authenticating or changing real CRM state.
    $admin=true; $enabled=true; $messages=[];
    function is_admin(){global $admin;return $admin;} function debug_mode_is_enabled(){global $enabled;return $enabled;}
    function has_permission(){return false;} function access_denied($name){throw new RuntimeException('DENIED');} function ajax_access_denied(){throw new RuntimeException('DENIED');} function show_error($message,$status){throw new RuntimeException('HTTP'.$status);}
    function log_message($level,$message){global $messages;$messages[]=$message;}
    class AdminController {function __construct(){}}
    class InputFixture {public $data=[];public $method='POST';function post($key){return $this->data[$key]??null;}function method($upper){return $this->method;}}
    class SessionFixture {public $data=[];function userdata($key){return $this->data[$key]??null;}function set_userdata($key,$value){$this->data[$key]=$value;}}
    class OutputFixture {public $status;function set_status_header($value){$this->status=$value;}}
    require dirname(__DIR__).'/modules/debug_mode/controllers/Debug_mode.php';
    #[AllowDynamicProperties] class ControllerFixture extends Debug_mode {}
    class SecurityFixture {function get_csrf_token_name(){return 'csrf';}function get_csrf_hash(){return 'fixture';}}
    $controller=new ControllerFixture();$controller->security=new SecurityFixture();$controller->input=new InputFixture();$controller->session=new SessionFixture();$controller->session->data['debug_mode_capture_token']='fixture';$controller->output=new OutputFixture();
    $controller->input->data=['debug_capture_token'=>'fixture','kind'=>'javascript','message'=>"Test token=private_endpoint\nforged line",'file'=>'https://crm.example/modules/appointly/a.js?key=private_query','route'=>'/admin/appointly?token=private_route','line'=>42];
    $controller->browser_error();
    verify_diagnostic($controller->output->status===204 && count($messages)===1,'Capture valid admin diagnostic');
    verify_diagnostic(strpos($messages[0],'private_')===false && strpos($messages[0],"\n")===false,'Redact endpoint fields and prevent log-line injection');
    $controller->input->data['debug_capture_token']='wrong';try{$controller->browser_error();throw new LogicException('Expected CSRF denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='HTTP403' && count($messages)===1,'Reject invalid CSRF token without logging');}$controller->input->data['debug_capture_token']='fixture';
    $admin=false;try{$controller->browser_error();throw new LogicException('Expected denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='DENIED','Deny non-admin collection');}$admin=true;
    $enabled=false;try{$controller->browser_error();throw new LogicException('Expected denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='DENIED','Deny disabled capture');}$enabled=true;
    $controller->session->data['debug_mode_capture_window']=['start'=>time(),'count'=>40];$controller->browser_error();verify_diagnostic($controller->output->status===429 && count($messages)===1,'Bound server ingestion per session');
    $controller->session->data=['debug_mode_capture_token'=>'fixture'];$controller->input->method='GET';try{$controller->browser_error();throw new LogicException('Expected POST');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='HTTP405','Require POST');}
    $consoleToken=str_repeat('a',64);$controller->session->data=['debug_mode_capture_token'=>$consoleToken];
    $controller->input->method='POST';$controller->input->data=['debug_capture_token'=>'wrong','scope'=>'appointly','route'=>'/admin/appointly/appointments','since'=>time()];
    try{$controller->module_console();throw new LogicException('Expected token denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='HTTP403','Reject a forged module-console token');}
    $controller->input->data['debug_capture_token']=$consoleToken;$controller->input->data['scope']='../appointly';
    try{$controller->module_console();throw new LogicException('Expected scope denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='HTTP400','Reject scope traversal in the console endpoint');}
    $controller->input->data['scope']='projects';$controller->input->data['route']='/admin/tasks';
    try{$controller->module_console();throw new LogicException('Expected mismatch denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='HTTP400','Require the page scope to match its route');}
    $admin=false;try{$controller->module_console();throw new LogicException('Expected console denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='DENIED','Deny non-admin module-console access');}$admin=true;
    $enabled=false;try{$controller->module_console();throw new LogicException('Expected disabled console denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='DENIED','Deny module-console access outside Debug Mode');}$enabled=true;
    $controller->input->data=['debug_capture_token'=>$consoleToken,'scope'=>'expenses','route'=>'/admin/expenses','fingerprint'=>'invalid'];
    try{$controller->resolve_console_issue();throw new LogicException('Expected fingerprint denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='HTTP400','Reject malformed persistent-issue fingerprints');}
    $admin=false;try{$controller->resolve_console_issue();throw new LogicException('Expected resolution denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='DENIED','Deny non-admin persistent-issue resolution');}$admin=true;
    $enabled=false;try{$controller->resolve_console_issue();throw new LogicException('Expected disabled resolution denial');}catch(RuntimeException $e){verify_diagnostic($e->getMessage()==='DENIED','Deny persistent-issue resolution outside Debug Mode');}$enabled=true;
    echo 'PASS '.$checks." diagnostic assertions\n";
} finally { remove_diagnostic_fixture($fixture); }

<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', true);
require dirname(__DIR__).'/application/libraries/Google_calendar_feed_health.php';
class App_Model {public function __get($key){return get_instance()->$key;}}
require dirname(__DIR__).'/application/models/Misc_model.php';
class FeedHealthFixture extends Google_calendar_feed_health {
 public $store=[],$statuses=[],$requests=[],$ttls=[];
 protected function cachedStatus(string $key){return $this->store[$key]??false;}
 protected function saveStatus(string $key,int $status,int $ttl):void{$this->store[$key]=$status;$this->ttls[$key]=$ttl;}
 protected function probe(array $ids,string $key):array{$this->requests[]=$ids;return array_intersect_key($this->statuses,array_flip($ids));}
}
$checks=0;function check($ok,$why){if(!$ok)throw new RuntimeException($why);$GLOBALS['checks']++;}
$health=new FeedHealthFixture();$health->statuses=['working@example.test'=>200,'missing@example.test'=>404,'denied@example.test'=>403,'timeout@example.test'=>0];
$ids=['working@example.test','missing@example.test','working@example.test','denied@example.test','timeout@example.test'];
$result=$health->check($ids,'fixture-key');
check($result===['ids'=>['working@example.test'],'unavailable'=>3],'valid feed is preserved; inaccessible feeds are reported');
check(count($health->requests[0])===4,'duplicate calendars are checked only once');
$health->check($ids,'fixture-key');check(count($health->requests)===2&&$health->requests[1]===[],'cached failures and successes do not repeat provider requests');
check(in_array(60,$health->ttls,true)&&in_array(300,$health->ttls,true),'network errors expire sooner than access results');
$health->check($ids,'changed-key');check(count($health->requests[2])===4,'credential changes invalidate prior access checks');
check($health->check($ids,'')===['ids'=>[],'unavailable'=>4],'missing API key produces an explicit unavailable state');
check($health->check([],'fixture-key')===['ids'=>[],'unavailable'=>0],'no configuration makes no provider requests');
// Exercise native calendar selection: unassigned department IDs must never be probed or returned.
$admin=false;function is_admin(){return $GLOBALS['admin'];}
function get_option($key){return $key==='google_api_key'?'fixture-key':($key==='google_calendar_main_calendar'?'working@example.test':'');}
function &get_instance(){return $GLOBALS['ci'];}
$ci=(object)['google_calendar_feed_health'=>new FeedHealthFixture(),'load'=>new class {function model($name){}function library($name){}},
 'departments_model'=>new class {
  function get(){return [['departmentid'=>1,'calendar_id'=>'missing@example.test'],['departmentid'=>2,'calendar_id'=>'working@example.test'],['departmentid'=>3,'calendar_id'=>'unassigned@example.test']];}
  function get_staff_departments(...$args){return [1,2];}
 }];
$ci->google_calendar_feed_health->statuses=['missing@example.test'=>404,'working@example.test'=>200,'unassigned@example.test'=>200];
$model=(new ReflectionClass(Misc_model::class))->newInstanceWithoutConstructor();
check($model->get_google_calendar_ids()===['working@example.test'],'native calendar loader keeps a dense array of accessible assigned calendars');
check($model->google_calendar_unavailable===1,'calendar UI receives an unavailable-feed count');
check(!in_array('unassigned@example.test',$ci->google_calendar_feed_health->requests[0],true),'other departments are excluded before provider checks');
$admin=true;
check($model->get_google_calendar_ids()===['working@example.test','unassigned@example.test'],'administrator can still load all accessible department feeds');
check($ci->google_calendar_feed_health->requests[1]===['unassigned@example.test'],'only newly authorized calendars require another check');
echo "PASS $checks calendar health/cache/department authorization checks; no external requests.\n";

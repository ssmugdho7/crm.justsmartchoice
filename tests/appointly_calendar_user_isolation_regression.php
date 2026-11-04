<?php
define('BASEPATH', __DIR__);
require dirname(__DIR__).'/modules/appointly/vendor/autoload.php';
class App_Model { public $db; }
$actor=7;$options=['appointly_google_client_id'=>'synthetic-client','appointly_google_calendar_staff_id'=>7,'appointly_google_calendar_id'=>'original@group.calendar.google.com'];
function get_staff_user_id(){global $actor;return $actor;}
function get_option($key){global $options;return $options[$key]??'';}
function update_option($key,$value){global $options;$options[$key]=$value;return true;}
function sc_google_secret_encrypt($v){return 'synthetic-encrypted:'.$v;}
function sc_google_secret_decrypt($v){return substr($v,20);}
function base_url(){return 'https://crm.justsmartchoice.com/';}
class CalendarMemoryDb {
 public $rows=[],$where=[];
 function select(){return $this;}
 function where($key,$value){$this->where[$key]=$value;return $this;}
 function get($table){$where=$this->where;$this->where=[];$rows=array_values(array_filter($this->rows,static function($r)use($where){foreach($where as $k=>$v)if($r[$k]!=$v)return false;return true;}));return new class($rows){private $rows;function __construct($rows){$this->rows=$rows;}function result(){return array_map(static fn($r)=>(object)$r,$this->rows);}};}
 function update($table,$values){$where=$this->where;$this->where=[];foreach($this->rows as &$row){$match=true;foreach($where as $key=>$value)if($row[$key]!=$value)$match=false;if($match)$row=array_merge($row,$values);}return true;}
}
require dirname(__DIR__).'/modules/appointly/models/Googlecalendar.php';
class UserCalendarProbe extends Googlecalendar {function __construct($db){$this->db=$db;$this->table='calendar_tokens';}}
$db=new CalendarMemoryDb;$m=new UserCalendarProbe($db);
$target=new ReflectionMethod(Googlecalendar::class,'syncCalendar');
$assertEvent=new ReflectionMethod(Googlecalendar::class,'assertManagedEvent');
function rejects($callback){try{$callback();}catch(RuntimeException|InvalidArgumentException $e){return;}throw new Exception('Unsafe operation accepted');}
if($m->calendarId()!=='original@group.calendar.google.com')throw new Exception('Original owner lost fallback');
$actor=8;if($m->calendarId()!=='')throw new Exception('Another staff inherited shared calendar');
rejects(fn()=>$target->invoke($m));
$m->saveCalendarId('staff8@group.calendar.google.com');
$actor=7;$m->saveCalendarId('staff7@group.calendar.google.com');
if($target->invoke($m)!=='staff7@group.calendar.google.com')throw new Exception('Staff7 target mismatch');
$actor=8;if($target->invoke($m)!=='staff8@group.calendar.google.com')throw new Exception('Staff8 target mismatch');
foreach(['primary','other@gmail.com',[],str_repeat('a',200).'@group.calendar.google.com'] as $invalid)rejects(fn()=>$m->saveCalendarId($invalid));
$db->rows=[['staff_id'=>7,'access_token'=>sc_google_secret_encrypt('access7'),'refresh_token'=>sc_google_secret_encrypt('refresh7'),'expires_in'=>9999999999],['staff_id'=>8,'access_token'=>sc_google_secret_encrypt('access8'),'refresh_token'=>sc_google_secret_encrypt('refresh8'),'expires_in'=>9999999999]];
foreach([7,8] as $id){$actor=$id;$account=$m->getAccountDetails();if(count($account)!==1||$account[0]->access_token!=='access'.$id)throw new Exception('Cross-staff token read');}
rejects(fn()=>$m->saveCalendarId('changed@group.calendar.google.com'));
$m->saveNewTokenValues(['access_token'=>'renewed8','expires_in'=>3600]);
if($db->rows[0]['access_token']!==sc_google_secret_encrypt('access7')||$db->rows[1]['refresh_token']!==sc_google_secret_encrypt('refresh8'))throw new Exception('Refresh changed another user or erased grant');
$actor=7;
$event=$m->fillGoogleCalendarEvent(['summary'=>'Synthetic','description'=>'No customer data','location'=>'','start'=>['dateTime'=>'2026-11-05T10:00:00-05:00'],'end'=>['dateTime'=>'2026-11-05T10:15:00-05:00'],'attendees'=>[]]);
$assertEvent->invoke($m,$event);$actor=8;rejects(fn()=>$assertEvent->invoke($m,$event));
$legacy=new Google\Service\Calendar\Event(['extendedProperties'=>['private'=>['smartchoice_source'=>hash('sha256',base_url())]]]);
rejects(fn()=>$assertEvent->invoke($m,$legacy));
$actor=7;$options['appointly_google_calendar_id_staff_7']='original@group.calendar.google.com';$assertEvent->invoke($m,$legacy);
$actor=8;rejects(fn()=>$assertEvent->invoke($m,$legacy));
$actor=0;if($m->getAccountDetails()!==false)throw new Exception('Anonymous token read');rejects(fn()=>$m->saveCalendarId('anonymous@group.calendar.google.com'));
echo "PASS independent staff targets, original-owner fallback only, token read/write isolation, invalid target rejection, connected target protection, and cross-staff event denial\n";

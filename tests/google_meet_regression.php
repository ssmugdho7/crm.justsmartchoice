<?php
if (PHP_SAPI !== 'cli') exit;
define('BASEPATH',__DIR__);
class App_Model { public $db; function __construct(){} }
$options=[];$pushes=[];
function get_option($key){return $GLOBALS['options'][$key]??'0';}
function get_current_date_format($php=false){return 'd/m/Y';}
function _dt($value){return $value;}
function is_staff_logged_in(){return true;}
function get_staff_user_id(){return 1;}
function db_prefix(){return 'fixture_';}
function html_escape($s){return htmlspecialchars((string)$s,ENT_QUOTES);}
function log_message(...$args){}
function pusher_trigger_notification($ids){$GLOBALS['pushes'][]=$ids;}
require dirname(__DIR__).'/modules/google_meet/models/Google_meet_model.php';
function expect($value,$message){if(!$value)throw new Exception($message);}
foreach(['2026-10-03T09:30','2026-10-03 09:30:00','03/10/2026 09:30'] as $value)expect(google_meet_parse_datetime($value)==='2026-10-03 09:30:00','Date normalization');
foreach(['nonsense','2026-02-30T09:30','0000-00-00 00:00:00','1970-01-01 00:00:00','1969-12-31 19:00:00'] as $value)expect(google_meet_parse_datetime($value)===null,'Invalid date accepted');
expect(google_meet_form_datetime('2026-10-03 09:30:00')==='2026-10-03T09:30','HTML date input');
expect(google_meet_display_datetime('0000-00-00 00:00:00')==='Time needs correction','Epoch displayed');
class DbFixture {
 public $rows=[];public $updates=[];
 function table_exists($table){return $table==='fixture_notifications';}
 function field_exists(...$args){return false;}
 function insert($table,$data){$this->rows[]=$data;return true;}
 function insert_id(){return count($this->rows);}
 function where(...$args){return $this;}
 function update($table,$data){$this->updates[]=$data;return true;}
}
class MeetingFixture extends Google_meet_model {
 public $record;
 function get($id=null){return $this->record;}
 function attendees($id){return [['id'=>7,'name'=>'Test','email'=>'','staff_id'=>2]];}
 function add_log($id,$action,$message){}
}
$m=new MeetingFixture();$m->db=new DbFixture();$m->record=(object)['subject'=>'Test','meet_link'=>'https://meet.google.com/abc-defg-hij','start_time'=>'2026-10-03 09:30:00'];
expect(!$m->notify_attendees(1),'Disabled channels reported success');expect(end($m->db->updates)['notified']===0,'Failed attendee reported notified');
$options['google_meet_push_enabled']='1';
expect($m->notify_attendees(1),'CRM invitation failed');expect($pushes===[[2]],'Realtime event missing');expect(end($m->db->updates)['notified']===1,'Accepted notification not recorded');
foreach([['start_time'=>'bad','end_time'=>'2026-10-03T10:00'],['start_time'=>'2026-10-03T10:00','end_time'=>'2026-10-03T09:00']] as $data){
 $before=count($m->db->rows);try{$m->create($data);throw new RuntimeException('Bad meeting was saved');}catch(InvalidArgumentException $e){}expect(count($m->db->rows)===$before,'Invalid dates wrote data');
}
echo "PASS: date formats/invalid dates/epoch, invalid save prevention, failed notification flags and realtime trigger\n";

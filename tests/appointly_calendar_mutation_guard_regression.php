<?php
define('BASEPATH',__DIR__);define('APPOINTLY_MODULE_NAME','appointly');
#[AllowDynamicProperties] class AdminController {}
class CalendarAccessDenied extends RuntimeException {}
function get_staff_user_id(){return 7;}
function db_prefix(){return 'tbl';}
$canEdit=true;
function staff_can($capability,$module){global $canEdit;return $canEdit;}
function access_denied($module){throw new CalendarAccessDenied('denied');}
function show_404(){throw new CalendarAccessDenied('not found');}
function show_error($message,$status){throw new CalendarAccessDenied((string)$status);}
$authenticated=false;
function appointlyGoogleAuth(){global $authenticated;return $authenticated;}
function _l($key){return $key;}
require dirname(__DIR__).'/modules/appointly/controllers/Appointments.php';
class MutationInput {
 public $verb='post',$event='event7';
 function method(){return $this->verb;}
 function is_ajax_request(){return true;}
 function post($key){return $key==='appointment_id'?12:$this->event;}
}
class MutationDb {
 public $row,$writes=[];
 function get_where($table,$where){return new class($this->row){private $row;function __construct($row){$this->row=$row;}function row(){return (object)$this->row;}};}
 function where($key,$value){return $this;}
 function update($table,$values){$this->writes[]=$values;return true;}
}
class MutationProbe extends Appointments {function __construct(){$this->input=new MutationInput;$this->db=new MutationDb;$this->load=new class{function model($name){throw new Exception('Provider must not be called');}};}}
function denied($callback){try{$callback();}catch(CalendarAccessDenied $e){return;}throw new Exception('Unauthorized mutation accepted');}
$p=new MutationProbe;$p->db->row=['id'=>12,'google_added_by_id'=>8,'google_event_id'=>'event8'];
denied(fn()=>$p->deleteGoogleEvent());denied(fn()=>$p->deleteGoogleSyncedAppointment('event8'));
if($p->db->writes)throw new Exception('Cross-staff unlink changed CRM');
$p->db->row=['id'=>12,'google_added_by_id'=>7,'google_event_id'=>'event7'];$p->input->event='forged-event';
denied(fn()=>$p->deleteGoogleEvent());
$p->input->event='event7';$p->input->verb='get';denied(fn()=>$p->deleteGoogleEvent());denied(fn()=>$p->deleteGoogleSyncedAppointment('event7'));
$p->input->verb='post';$canEdit=false;denied(fn()=>$p->deleteGoogleEvent());$canEdit=true;
// A local unlink of this user's own connection must preserve the Jitsi meeting link.
$p->load=new class{function model($name){}};
ob_start();$p->deleteGoogleEvent();$result=json_decode(ob_get_clean(),true);
if(!$result['success']||count($p->db->writes)!==1||array_key_exists('google_meet_link',$p->db->writes[0]))throw new Exception('Own unlink failed or removed video meeting');
require dirname(__DIR__).'/modules/appointly/controllers/google/Auth.php';
class LogoutProbe extends Auth {function __construct(){$this->input=new MutationInput;$this->input->verb='get';$this->googleplus=new class{function revokeToken(){throw new Exception('GET revoked grant');}};}}
denied(fn()=>(new LogoutProbe)->logout());
class App_Model {}
function log_message($level,$message){}
function insertAppointmentToGoogleCalendar($data,$attendees){throw new Exception('Duplicate or foreign event insertion attempted');}
require dirname(__DIR__).'/modules/appointly/models/Appointly_model.php';
class ExistingCalendarLinkProbe extends Appointly_model {
 public $fixture=['google_event_id'=>'existing-event','google_calendar_link'=>'https://calendar.google.com/synthetic','google_added_by_id'=>8];
 function __construct(){}
 function get_appointment_data($id){return $this->fixture;}
}
$authenticated=true;$model=new ExistingCalendarLinkProbe;
if($model->add_event_to_google_calendar(['appointment_id'=>12])['result']!=='error')throw new Exception('Another staff mapping overwritten');
$model->fixture['google_added_by_id']=7;
$existing=$model->add_event_to_google_calendar(['appointment_id'=>12]);
if($existing['result']!=='success'||$existing['google_event_id']!=='existing-event')throw new Exception('Existing own link was not reused');
echo "PASS cross-staff and forged-event unlink denied, POST/edit permission required, own unlink preserves video rooms, and GET cannot revoke OAuth\n";

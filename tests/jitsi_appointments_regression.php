<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('BASEPATH',__DIR__);$root=dirname(__DIR__);$checks=0;
function check($value,$label){if(!$value)throw new RuntimeException($label);$GLOBALS['checks']++;}
function get_option($key){return ['video_meeting_provider'=>'jitsi','jitsi_server_domain'=>'meet.jit.si','appointly_auto_enable_google_meet'=>'1','default_timezone'=>'UTC'][$key]??'';}
function html_escape($text){return htmlspecialchars((string)$text,ENT_QUOTES,'UTF-8');}
function &get_instance(){return $GLOBALS['ci'];}
function app_generate_hash(){return bin2hex(random_bytes(16));}function to_sql_date($date,$time=false){return $date;}
function get_staff_user_id(){return 42;}function db_prefix(){return 'fixture_';}
function appointlyGoogleAuth(){throw new RuntimeException('Unexpected OAuth call for automatic Jitsi creation');}
#[AllowDynamicProperties]class App_Model {function __construct(){}}
require $root.'/modules/appointly/models/Appointly_model.php';require $root.'/modules/appointly/helpers/appointly_helper.php';
class InsertRecorder {public $data;function insert($table,$data){check($table==='fixture_appointly_appointments','Appointment insert target');$this->data=$data;return false;}function insert_id(){return 0;}}
$model=(new ReflectionClass('Appointly_model'))->newInstanceWithoutConstructor();$model->db=new InsertRecorder();
$model->create_appointment(['rel_type'=>'internal_staff','subject'=>'Staff fixture','date'=>'2026-10-07','start_hour'=>'10:00','duration'=>30,'attendees'=>[42]]);
$staff=$model->db->data;check(strpos($staff['google_meet_link'],'https://meet.jit.si/Appt-')===0,'Real staff create path prepares room before insert/notifications');
$model->insert_external_appointment(['subject'=>'Customer fixture','date'=>'2026-10-07','start_hour'=>'11:00','duration'=>30]);
$external=$model->db->data;check(strpos($external['google_meet_link'],'https://meet.jit.si/Appt-')===0&&$external['google_meet_link']!==$staff['google_meet_link'],'Real external create path prepares distinct room');
$model->create_appointment(['rel_type'=>'internal_staff','subject'=>'Existing fixture','date'=>'2026-10-07','start_hour'=>'10:00','duration'=>30,'attendees'=>[42],'google_meet_link'=>$staff['google_meet_link']]);
check($model->db->data['google_meet_link']===$staff['google_meet_link'],'Provided appointment room is preserved');
$ci=(object)['load'=>new class{function model(...$args){}},'atm'=>new class{function get($id){return [];}}];$_SERVER['HTTP_HOST']='portal.example';
$ics=generate_appointment_ics_content(['id'=>7,'subject'=>'Appointment','date'=>'2026-10-07','start_hour'=>'10:00','duration'=>30,'description'=>'Review scope','google_meet_link'=>$staff['google_meet_link'],'source'=>'internal_staff']);
$unfolded=preg_replace('/\r\n[ \t]/','',$ics);
check(strpos($unfolded,'DESCRIPTION:Review scope\\nJoin video meeting:')!==false && strpos($unfolded,$staff['google_meet_link'])!==false,'Appointment ICS includes saved link in description');
$mailer=new class{public $slug='appointment-submitted-to-contact';function get_merge_fields(){return ['{appointment_google_meet_link}'=>$GLOBALS['staff']['google_meet_link']];}};
$GLOBALS['SENDING_EMAIL_TEMPLATE_CLASS']=$mailer;$template=(object)['message'=>'Custom confirmation','plaintext'=>0];
$result=appointly_jitsi_invitation_link($template);check(strpos($result->message,$staff['google_meet_link'])!==false&&$template->message==='Custom confirmation','Confirmation gets link without rewriting stored custom template');
check(appointly_jitsi_invitation_link($result)->message===$result->message,'Repeated filter never duplicates link');
$template->message='{appointment_google_meet_link}';check(appointly_jitsi_invitation_link($template)===$template,'Existing merge token preserved');
$mailer->slug='invoice-send-to-client';$template->message='Invoice';check(appointly_jitsi_invitation_link($template)===$template,'Unrelated email untouched');
$mailer->slug='appointment-submitted-to-contact';$template->plaintext=1;$result=appointly_jitsi_invitation_link($template);check(strpos($result->message,'<a')===false&&strpos($result->message,"\nJoin video meeting:")!==false,'Plaintext invitation remains plaintext');
$template=(object)['message'=>'Join Google Meet: {appointment_google_meet_link}', 'subject'=>'Google Meeting Invitation', 'plaintext'=>0];
$result=appointly_jitsi_invitation_link($template);
check($result->subject==='Video Meeting Invitation'&&strpos($result->message,'Join Video Meeting:')!==false&&$template->subject==='Google Meeting Invitation','Existing branded templates normalized without modifying stored copy');
$description=appointly_video_calendar_description(['description'=>'Review scope','google_meet_link'=>$staff['google_meet_link']]);
check(strpos($description,$staff['google_meet_link'])!==false,'Calendar description uses shared saved Jitsi URL');
check(appointly_video_calendar_description(['description'=>$description,'google_meet_link'=>$staff['google_meet_link']])===$description,'Calendar update does not duplicate video URL');
eval('namespace Google\\Service\\Calendar; class Event { public $values; public function __construct($values) { $this->values=$values; } public function setConferenceData($value) { throw new \\RuntimeException("Unexpected Google video room creation"); } }');
require $root.'/modules/appointly/models/Googlecalendar.php';
$calendar=(new ReflectionClass('Googlecalendar'))->newInstanceWithoutConstructor();
$event=$calendar->fillGoogleCalendarEvent(['summary'=>'Planning','description'=>$description,'location'=>'','start'=>[],'end'=>[],'attendees'=>[]]);
check($event->values['description']===$description&&$event->values['summary']==='Planning','Google calendar keeps Jitsi description and never creates a competing room');
echo "PASS: $checks Appointly create-path, OAuth independence, calendar and email payload checks (no notifications sent)\n";

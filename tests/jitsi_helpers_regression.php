<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', __DIR__);
$root = dirname(__DIR__);
$options = ['jitsi_server_domain'=>'meet.jit.si','jitsi_room_prefix'=>'SC','jitsi_require_pin'=>'1','google_meet_timezone'=>'America/New_York'];
function get_option($name){return $GLOBALS['options'][$name]??'';}
function site_url($path=''){return 'https://portal.example/'.$path;}
function admin_url($path=''){return site_url('admin/'.$path);}
function check($value,$label){if(!$value)throw new RuntimeException($label);$GLOBALS['checks']++;}
$checks=0;
require $root.'/modules/google_meet/helpers/jitsi_helper.php';
check(jitsi_server_domain('https://MEET.JIT.SI/')==='meet.jit.si','Hostname normalized');
check(jitsi_server_domain('video.example.com:8443')==='video.example.com:8443','Explicit valid port');
foreach(['javascript:alert(1)','meet.jit.si/evil','user@meet.jit.si','meet.jit.si?x=1','meet.jit.si#x','meet.jit.si:99999','localhost','-bad.example','meet.jit.si:0'] as $domain){try{jitsi_server_domain($domain);throw new RuntimeException('Invalid domain allowed: '.$domain);}catch(InvalidArgumentException $e){check(true,'Domain rejected');}}
$rooms=[];
for($i=0;$i<1000;$i++){$name=jitsi_generate_room_name();check(preg_match('/^SC-\d{6}-[a-f0-9]{16}-[a-f0-9]{16}$/D',$name)===1,'128-bit random room format');$rooms[$name]=true;check(preg_match('/^[1-9][0-9]{5}$/D',jitsi_generate_room_pin())===1,'Six-digit PIN');}
check(count($rooms)===1000,'Room uniqueness');
$new=jitsi_prepare_meeting_link('');$meeting=(object)($new+['id'=>7,'subject'=>"Planning, review;\nATTENDEE:evil","start_time"=>'2026-10-07 10:00:00','duration_minutes'=>30]);
check($new['provider']==='jitsi'&&strlen($new['room_pin'])===6,'Automatic PIN and provider');
check(jitsi_prepare_meeting_link('', $meeting)===$new,'Existing room/PIN preserved');
$options['jitsi_server_domain']='video.example.com';$options['jitsi_room_prefix']='New';
check(jitsi_prepare_meeting_link('', $meeting)===$new,'Settings change does not rotate rooms');
$custom=jitsi_prepare_meeting_link('');$options['jitsi_server_domain']='another.example.com';
check(jitsi_prepare_meeting_link('',(object)$custom)===$custom,'Stored custom domain survives domain change');
check(jitsi_meeting_room((object)['meet_link'=>'https://untrusted.example/SC-123456789'])===null,'Unapproved manual embed rejected');
foreach(['https://meet.google.com/new','https://meet.google.com/new/'] as $link){check(jitsi_prepare_meeting_link($link)['provider']==='jitsi','Google new URL replaced');}
check(jitsi_prepare_meeting_link('https://meet.google.com/abc-defg-hij',(object)['meet_link'=>'https://meet.google.com/abc-defg-hij'])['provider']==='google_meet','Existing Google room retained');
try { jitsi_prepare_meeting_link('https://meet.google.com/abc-defg-hij'); throw new RuntimeException('New Google room accepted'); } catch (InvalidArgumentException $e) { check(true,'New rooms use Jitsi only'); }
$payload=jitsi_build_invitation_payload($meeting,true);
parse_str(parse_url($payload['whatsapp_url'],PHP_URL_QUERY),$wa);
check($wa['text']===$payload['plain_text']&&strpos($payload['plain_text'],$new['meet_link'])!==false&&strpos($payload['plain_text'],$new['room_pin'])!==false,'Share payload includes exact room and PIN');
check($payload['ics_url']==='https://portal.example/google_meet/meeting_clients/calendar/7','Customer ICS uses scoped endpoint');
$ics=jitsi_calendar_content($meeting);
check(strpos($ics,'DTSTART:20261007T140000Z')!==false&&strpos($ics,'DTEND:20261007T143000Z')!==false,'Calendar timezone and duration');
check(strpos($ics,"\r\nATTENDEE:")===false&&strpos($ics,'\\nATTENDEE:evil')!==false,'Calendar property injection escaped');
$meeting->subject=str_repeat('レビュー',40);$ics=jitsi_calendar_content($meeting);
foreach(explode("\r\n",$ics) as $line){check(strlen($line)<=75&&preg_match('//u',$line)===1,'UTF-8 calendar folding');}
class App_Model {function __construct(){}}
require $root.'/modules/appointly/models/Appointly_model.php';
$model=(new ReflectionClass('Appointly_model'))->newInstanceWithoutConstructor();
$prepare=new ReflectionMethod($model,'prepare_jitsi_appointment');
$appt=$prepare->invoke($model,['subject'=>'Appointment']);
check(strpos($appt['google_meet_link'],'https://another.example.com/Appt-')===0,'Appointment automatic room');
check($prepare->invoke($model,$appt)['google_meet_link']===$appt['google_meet_link'],'Appointment existing room retained');
check($prepare->invoke($model,[],'https://meet.google.com/abc-defg-hij')['google_meet_link']==='https://meet.google.com/abc-defg-hij','Existing appointment Google room retained');
check(strpos($prepare->invoke($model,[],'https://meet.google.com/new')['google_meet_link'],'/Appt-')!==false,'Appointment placeholder repaired');
foreach(['javascript:alert(1)','https://meet.jit.si/abc\"onload=1','http://meet.jit.si/abcdefghij'] as $unsafe){try{$prepare->invoke($model,[],$unsafe);throw new RuntimeException('Unsafe appointment URL');}catch(InvalidArgumentException $e){check(true,'Unsafe appointment URL rejected');}}
echo "PASS: $checks Jitsi helper, invitation, ICS and appointment room checks\n";

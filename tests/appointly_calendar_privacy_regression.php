<?php
define('BASEPATH', __DIR__);
error_reporting(E_ALL);
set_error_handler(static function($level,$message){throw new RuntimeException($message);});
require dirname(__DIR__).'/modules/appointly/vendor/autoload.php';
class App_Model{}
$options=['appointly_google_calendar_id'=>'dedicated-calendar@group.calendar.google.com','appointly_google_calendar_staff_id'=>7];
function get_option($k){global $options;return $options[$k]??'';}
function get_staff_user_id(){return 7;}
function base_url(){return 'https://crm.justsmartchoice.com/';}
require dirname(__DIR__).'/modules/appointly/models/Googlecalendar.php';
class PrivacyProbe extends Googlecalendar{public function __construct(){$this->googleReminders=['useDefault'=>true];}}
$p=new PrivacyProbe();
$event=$p->fillGoogleCalendarEvent(['summary'=>'Synthetic QA','description'=>'No customer information','location'=>'','start'=>['dateTime'=>'2026-11-05T10:00:00-05:00'],'end'=>['dateTime'=>'2026-11-05T10:15:00-05:00'],'attendees'=>[['email'=>'must-not-be-invited@example.com']]]);
if($event->getVisibility()!=='private'||$event->getAttendees()!==[]||$event->getReminders()->getUseDefault()!==false)throw new Exception('Privacy contract failed');
if($event->getStart()->getTimeZone()!=='America/New_York'||$event->getStart()->getDateTime()!=='2026-11-05T10:00:00-05:00')throw new Exception('Calendar time zone not preserved');
if($p->getEvents()!==[])throw new Exception('One-way sync imported events');
$guard=new ReflectionMethod(Googlecalendar::class,'assertManagedEvent');$guard->setAccessible(true);
try{$guard->invoke($p,new Google\Service\Calendar\Event());throw new Exception('Foreign event allowed');}catch(RuntimeException $expected){}
$guard->invoke($p,$event);
$target=new ReflectionMethod(Googlecalendar::class,'syncCalendar');$target->setAccessible(true);
if($target->invoke($p)!==$options['appointly_google_calendar_id'])throw new Exception('Wrong target');
$options['appointly_google_calendar_staff_id']=8;
try{$target->invoke($p);throw new Exception('Wrong owner allowed');}catch(RuntimeException $expected){}
$options['appointly_google_calendar_staff_id']=7;
$options['appointly_google_calendar_id']='haroldcabrerausa@gmail.com';
try{$target->invoke($p);throw new Exception('Personal calendar allowed');}catch(RuntimeException $expected){}
$options['appointly_google_client_id']='synthetic-dedicated-client';$options['appointly_google_calendar_id']='';
try{$target->invoke($p);throw new Exception('Missing target fell back to primary');}catch(RuntimeException $expected){}
echo "PASS private/no-attendee/no-reminder event; one-way import guard; managed event ownership; dedicated target; wrong staff denied\n";

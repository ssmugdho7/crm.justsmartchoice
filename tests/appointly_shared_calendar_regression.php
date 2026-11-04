<?php
$admin=false;$appointmentPermission=true;
function is_admin(){global $admin;return $admin;}
function staff_can($capability,$module){global $appointmentPermission;return $appointmentPermission;}
require __DIR__.'/appointly_calendar_user_isolation_regression.php';
$actor=8;
$options['appointly_google_team_enabled']='1';
$options['appointly_google_team_owner_id']='7';
$options['appointly_google_team_calendar_id']='company@group.calendar.google.com';
if($m->syncOwnerId()!==7||$target->invoke($m)!=='company@group.calendar.google.com')throw new Exception('Shared routing failed');
if($m->getAccountDetails(true)[0]->staff_id!==7||$m->getAccountDetails()[0]->staff_id!==8)throw new Exception('Team and personal grants mixed');
$ownBefore=$db->rows[1];
$m->saveNewTokenValues(['access_token'=>'renewed-company','expires_in'=>3600],true);
if($db->rows[0]['access_token']!==sc_google_secret_encrypt('renewed-company')||$db->rows[1]!==$ownBefore)throw new Exception('Team refresh overwrote personal grant');
$teamEvent=$m->fillGoogleCalendarEvent(['summary'=>'Synthetic shared','description'=>'No customer data','location'=>'','start'=>['dateTime'=>'2026-11-05T10:00:00-05:00'],'end'=>['dateTime'=>'2026-11-05T10:15:00-05:00'],'attendees'=>[]]);
$assertEvent->invoke($m,$teamEvent);
if($teamEvent->getVisibility()!=='private'||$teamEvent->getAttendees()!==[]||$m->getEvents()!==[])throw new Exception('Shared privacy contract failed');
$actor=7;rejects(fn()=>$assertEvent->invoke($m,$teamEvent));
$actor=8;rejects(fn()=>$assertEvent->invoke($m,$event));
$options['appointly_google_team_calendar_id']='other-company@group.calendar.google.com';rejects(fn()=>$assertEvent->invoke($m,$teamEvent));
$appointmentPermission=false;
if($m->getAccountDetails(true)!==false||$m->syncOwnerId()!==0)throw new Exception('Unauthorized staff read team grant');
rejects(fn()=>$target->invoke($m));rejects(fn()=>$m->saveTeamSettings(true,7));
$admin=true;if($m->syncOwnerId()!==7)throw new Exception('Administrator cannot use configured company grant');
$m->saveTeamSettings(false,7);if($m->sharedMode()||$m->syncOwnerId()!==8||$m->calendarId()!=='staff8@group.calendar.google.com')throw new Exception('Disabling shared mode lost personal configuration');
echo "PASS shared target and central token routing, refresh isolation, permission denial, admin-only settings, team event ownership, private one-way sync, and personal fallback\n";

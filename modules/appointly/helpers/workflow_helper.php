<?php
defined('BASEPATH') or exit('No direct script access allowed');
function appointly_workflow_visible(array $appointment) {
    if(staff_can('view','appointments') || is_admin() || (int)$appointment['created_by']===(int)get_staff_user_id())return true;
    $CI=&get_instance();return (bool)$CI->db->where('appointment_id',$appointment['id'])->where('staff_id',get_staff_user_id())->get(db_prefix().'appointly_attendees')->row();
}
function appointly_schedule_version(array $a) {return hash('sha256',implode('|',array_map(static function($key)use($a){return (string)($a[$key]??'');},['id','date','start_hour','end_hour','provider_id','service_id','status','timezone'])));}
function appointly_valid_schedule($date,$time) {
    $day=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$date);
    return $day && $day->format('Y-m-d')===$date && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D',(string)$time);
}
function appointly_workflow_telemetry(array $a,array $emails,array $sms) {
    $opened=null;foreach($emails as $email)if(!empty($email['opened']) && !empty($email['date_opened']))$opened=$email['date_opened'];
    $smsStatus=$sms ? (string)($sms[0]['status']??'Unknown') : 'Not tracked';
    return ['email'=>$opened ? 'Opened · '._dt($opened) : ($emails?'No open recorded':'Not tracked'),'sms'=>$smsStatus,'sms_enabled'=>!empty($a['by_sms'])];
}

<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI=&get_instance();$sms=[];$table=db_prefix().'appointly_smartchoice_notifications';
if($CI->db->table_exists($table) && $CI->db->field_exists('channel',$table) && $CI->db->field_exists('appointment_id',$table))$sms=$CI->db->where('appointment_id',(int)$appointment['id'])->where('channel','sms')->order_by('id','DESC')->limit(1)->get($table)->result_array();
$telemetry=appointly_workflow_telemetry($appointment,$appointment['email_tracking']??[],$sms);
?>
<section class="scaf-appointment-telemetry" aria-label="Notification tracking">
<div>Email read receipt<strong><?= html_escape($telemetry['email']) ?></strong></div>
<div>SMS confirmation<strong><?= html_escape($telemetry['sms']) ?></strong><small><?= $telemetry['sms_enabled']?'SMS enabled for this appointment':'SMS not enabled for this appointment' ?></small></div>
<div>Calendar link<strong><?= !empty($appointment['google_event_id'])?'Linked to Google Calendar':'No Google Calendar link' ?></strong></div>
</section>

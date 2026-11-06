<?php defined('BASEPATH') or exit('No direct script access allowed');
$CI=&get_instance();$workflowToken=$CI->session->userdata('appointly_workflow_token');if(!$workflowToken){$workflowToken=bin2hex(random_bytes(32));$CI->session->set_userdata('appointly_workflow_token',$workflowToken);}
$dispatch_week = $dispatch_week ?? date('Y-m-d');
?>
<section class="scaf-dispatch-calendar" aria-label="Appointment dispatch calendar" data-dispatch-token="<?= html_escape($workflowToken) ?>">
<div class="scaf-dispatch-heading"><h4>Seven-Day Dispatch Timeline</h4><a class="btn btn-default" href="<?= admin_url('appointly/services/company_schedule') ?>">Company operating hours</a></div>
<form method="get" action="<?= admin_url('appointly/smartchoice/dispatch') ?>" class="form-inline"><label>Week starting <input type="date" name="week" value="<?= html_escape($dispatch_week) ?>" required class="form-control"></label> <button class="btn btn-default" type="submit">Show week</button> <a class="btn btn-default" href="<?= admin_url('appointly/smartchoice/dispatch?week='.date('Y-m-d',strtotime($dispatch_week.' -7 days'))) ?>">Previous week</a> <a class="btn btn-default" href="<?= admin_url('appointly/smartchoice/dispatch?week='.date('Y-m-d',strtotime($dispatch_week.' +7 days'))) ?>">Next week</a></form>
<label>Capacity date <input type="date" value="<?= html_escape($dispatch_week) ?>" data-dispatch-capacity-date class="form-control"></label>
<div class="scaf-capacity-ribbon" aria-live="polite"></div>
<p class="text-muted">Drag an appointment to another day, or use its Reschedule button. Times use each appointment's saved timezone. Availability is checked before saving. Location badges show actual scheduled addresses.</p>
<div class="scaf-dispatch-week">
<?php for($i=0;$i<7;$i++):$day=date('Y-m-d',strtotime($dispatch_week.' +'.$i.' days')); ?>
<div class="scaf-dispatch-day" data-dispatch-day="<?= $day ?>"><h5><?= _d($day) ?></h5>
<?php foreach($events as $a):if($a['date']!==$day)continue;$canEdit=staff_can('edit','appointments') && appointly_workflow_visible($a) && !in_array($a['status'],['completed','cancelled','no-show'],true);$color=preg_match('/^#[a-f0-9]{6}$/i',$a['service_color']??'')?$a['service_color']:'#059669'; ?>
<article class="scaf-dispatch-event" draggable="<?= $canEdit?'true':'false' ?>" data-dispatch-event="<?= (int)$a['id'] ?>" data-version="<?= appointly_schedule_version($a) ?>" data-date="<?= $a['date'] ?>" data-start="<?= html_escape(substr($a['start_hour'],0,5)) ?>" data-end="<?= html_escape(substr($a['end_hour'],0,5)) ?>" data-provider="<?= html_escape(trim(($a['firstname']??'').' '.($a['lastname']??''))?:'Unassigned') ?>" data-address="<?= html_escape($a['address']??'') ?>" style="border-left-color:<?= $color ?>">
<span class="scaf-dispatch-trade" style="border-color:<?= $color ?>"><?= html_escape($a['service_name']??'No service') ?></span>
<a href="<?= admin_url('appointly/appointments/view?appointment_id='.(int)$a['id']) ?>"><strong><?= html_escape($a['subject']) ?></strong></a>
<small data-dispatch-time><?= html_escape(substr($a['start_hour'],0,5).' – '.substr($a['end_hour'],0,5)) ?></small><small><?= html_escape($a['timezone']?:'America/New_York') ?></small><small><?= html_escape(trim(($a['firstname']??'').' '.($a['lastname']??''))) ?></small>
<?php if($canEdit): ?><button type="button" class="btn btn-default btn-xs" data-dispatch-reschedule>Reschedule</button><?php endif; ?>
</article><?php endforeach; ?></div><?php endfor; ?>
</div></section>

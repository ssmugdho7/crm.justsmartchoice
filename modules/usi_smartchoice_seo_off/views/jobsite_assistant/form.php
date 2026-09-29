<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<h4><?php echo html_escape($title); ?></h4><hr class="hr-panel-heading" />
<?php echo form_open(current_url()); ?>
<div class="row">
  <div class="col-md-6"><?php echo render_input('title','Title',$log['title'] ?? ''); ?></div>
  <div class="col-md-3"><?php echo render_input('log_date','Log Date',$log['log_date'] ?? date('Y-m-d'),'date'); ?></div>
  <div class="col-md-3"><label>Status</label><select name="status" class="form-control"><?php foreach(['draft','in_progress','needs_attention','ready_for_closeout','completed'] as $st){ ?><option value="<?php echo $st; ?>" <?php echo (($log['status']??'draft')===$st?'selected':''); ?>><?php echo ucwords(str_replace('_',' ',$st)); ?></option><?php } ?></select></div>
</div>
<div class="row">
  <div class="col-md-4"><label>AI Estimate</label><select name="ai_estimate_id" class="form-control"><option value="0">None</option><?php foreach($estimates as $estimate){ ?><option value="<?php echo (int)$estimate['id']; ?>" <?php echo ((int)($log['ai_estimate_id']??0)===(int)$estimate['id']?'selected':''); ?>><?php echo html_escape($estimate['title']); ?></option><?php } ?></select></div>
  <div class="col-md-4"><label>Schedule</label><select name="schedule_id" class="form-control"><option value="0">None</option><?php foreach($schedules as $schedule){ ?><option value="<?php echo (int)$schedule['id']; ?>" <?php echo ((int)($log['schedule_id']??0)===(int)$schedule['id']?'selected':''); ?>><?php echo html_escape($schedule['title']); ?></option><?php } ?></select></div>
  <div class="col-md-4"><?php echo render_input('project_id','CRM Project ID',$log['project_id'] ?? 0,'number'); ?></div>
</div>
<?php echo render_input('weather_note','Weather / Site Condition',$log['weather_note'] ?? ''); ?>
<?php echo render_textarea('crew_note','Crew Note',$log['crew_note'] ?? '', ['rows'=>4]); ?>
<?php echo render_textarea('work_completed','Work Completed',$log['work_completed'] ?? '', ['rows'=>5]); ?>
<?php echo render_textarea('materials_used','Materials Used',$log['materials_used'] ?? '', ['rows'=>4]); ?>
<?php echo render_textarea('safety_checklist','Safety Checklist',$log['safety_checklist'] ?? '', ['rows'=>5]); ?>
<?php echo render_textarea('photo_summary','Photo Summary',$log['photo_summary'] ?? '', ['rows'=>4]); ?>
<?php echo render_textarea('customer_note','Customer Note',$log['customer_note'] ?? '', ['rows'=>4]); ?>
<button type="submit" class="btn btn-primary btn-sm">Save Jobsite Log</button>
<a href="<?php echo admin_url('usi_smartchoice_seo/jobsite_assistant'); ?>" class="btn btn-default btn-sm">Back</a>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>

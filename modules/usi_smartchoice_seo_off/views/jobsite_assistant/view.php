<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<?php if(!$log){ ?><div class="alert alert-warning">Jobsite log not found.</div><?php } else { ?>
<div class="_buttons mbottom15">
  <a href="<?php echo admin_url('usi_smartchoice_seo/jobsite_log/'.(int)$log['id']); ?>" class="btn btn-default btn-sm"><i class="fa fa-pencil"></i> Edit</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/generate_punch_items/'.(int)$log['id']); ?>" class="btn btn-default btn-sm"><i class="fa fa-list"></i> Generate Punch List</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/mark_jobsite_ready/'.(int)$log['id']); ?>" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Mark Ready For Closeout</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/jobsite_assistant'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
</div>
<h4><?php echo html_escape($log['title']); ?> <span class="label label-default"><?php echo html_escape(ucwords(str_replace('_',' ',(string)$log['status']))); ?></span></h4>
<table class="table table-bordered sc-table-compact">
<tr><th>Date</th><td><?php echo html_escape($log['log_date']); ?></td><th>AI Estimate</th><td><?php echo (int)$log['ai_estimate_id']; ?></td></tr>
<tr><th>Schedule</th><td><?php echo (int)$log['schedule_id']; ?></td><th>Project</th><td><?php echo (int)$log['project_id']; ?></td></tr>
<tr><th>Weather</th><td colspan="3"><?php echo html_escape($log['weather_note']); ?></td></tr>
</table>
<div class="row">
  <div class="col-md-6"><div class="sc-card"><strong>Crew Note</strong><br><?php echo nl2br(html_escape($log['crew_note'])); ?></div></div>
  <div class="col-md-6"><div class="sc-card"><strong>Work Completed</strong><br><?php echo nl2br(html_escape($log['work_completed'])); ?></div></div>
  <div class="col-md-6"><div class="sc-card"><strong>Materials Used</strong><br><?php echo nl2br(html_escape($log['materials_used'])); ?></div></div>
  <div class="col-md-6"><div class="sc-card"><strong>Safety Checklist</strong><br><?php echo nl2br(html_escape($log['safety_checklist'])); ?></div></div>
  <div class="col-md-6"><div class="sc-card"><strong>Photo Summary</strong><br><?php echo nl2br(html_escape($log['photo_summary'])); ?></div></div>
  <div class="col-md-6"><div class="sc-card"><strong>Customer Note</strong><br><?php echo nl2br(html_escape($log['customer_note'])); ?></div></div>
</div>
<h4 class="mtop20">Punch List / Deficiency Items</h4>
<div class="table-responsive"><table class="table table-bordered table-striped sc-table-compact"><thead><tr><th>Title</th><th>Type</th><th>Priority</th><th>Status</th><th>Description</th></tr></thead><tbody>
<?php foreach($items as $item){ ?><tr><td><?php echo html_escape($item['item_title']); ?></td><td><?php echo html_escape($item['item_type']); ?></td><td><?php echo html_escape($item['priority']); ?></td><td><?php echo html_escape($item['status']); ?></td><td><?php echo html_escape($item['description']); ?></td></tr><?php } if(empty($items)){ ?><tr><td colspan="5" class="text-center">No punch items generated.</td></tr><?php } ?>
</tbody></table></div>
<?php } ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>

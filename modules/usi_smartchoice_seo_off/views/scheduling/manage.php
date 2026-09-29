<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<div class="_buttons mbottom15">
  <a href="<?php echo admin_url('usi_smartchoice_seo/schedule_plan'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> New Schedule</a>
  <a href="<?php echo admin_url('usi_smartchoice_seo/scheduling'); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reload</a>
  <button type="button" class="btn btn-default btn-sm" onclick="window.print();"><i class="fa fa-download"></i> Export</button>
</div>
<h4 class="no-margin">AI Scheduling</h4>
<hr class="hr-panel-heading" />
<div class="row sc-ai-kpi-row">
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Draft</strong><br><?php echo (int)$counts['draft']; ?></div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Generated</strong><br><?php echo (int)$counts['generated']; ?></div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Ready</strong><br><?php echo (int)$counts['ready_for_calendar']; ?></div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Active</strong><br><?php echo (int)$counts['active']; ?></div></div>
  <div class="col-md-2 col-xs-6"><div class="sc-card"><strong>Completed</strong><br><?php echo (int)$counts['completed']; ?></div></div>
</div>
<form method="get" class="row mtop15">
  <div class="col-md-3"><select name="status" class="form-control"><option value="">All Statuses</option><?php foreach(['draft','generated','ready_for_calendar','active','completed'] as $st){ ?><option value="<?php echo $st; ?>" <?php echo (($filters['status']??'')===$st?'selected':''); ?>><?php echo ucwords(str_replace('_',' ',$st)); ?></option><?php } ?></select></div>
  <div class="col-md-2"><input type="date" name="date_from" value="<?php echo html_escape($filters['date_from'] ?? ''); ?>" class="form-control"></div>
  <div class="col-md-2"><input type="date" name="date_to" value="<?php echo html_escape($filters['date_to'] ?? ''); ?>" class="form-control"></div>
  <div class="col-md-2"><button class="btn btn-default btn-sm btn-block">Filter</button></div>
</form>
<?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_schedules')); ?>
<div class="table-responsive mtop15"><table class="table table-bordered table-striped sc-table-compact">
<thead><tr><th><input type="checkbox" onclick="$('.sc-schedule-id').prop('checked', this.checked);"></th><th>Title</th><th>Start</th><th>End</th><th>Status</th><th>Duration</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach($schedules as $row){ ?>
<tr>
<td><input type="checkbox" class="sc-schedule-id" name="ids[]" value="<?php echo (int)$row['id']; ?>"></td>
<td><?php echo html_escape($row['title']); ?></td>
<td><?php echo html_escape($row['start_date']); ?></td>
<td><?php echo html_escape($row['end_date']); ?></td>
<td><span class="label label-default"><?php echo html_escape(ucwords(str_replace('_',' ',(string)$row['status']))); ?></span></td>
<td><?php echo (int)$row['duration_days']; ?> days</td>
<td>
<a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_schedule/'.(int)$row['id']); ?>">View</a>
<a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/schedule_plan/'.(int)$row['id']); ?>">Edit</a>
<a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_schedule/'.(int)$row['id']); ?>">Delete</a>
</td>
</tr>
<?php } if(empty($schedules)){ ?><tr><td colspan="7" class="text-center">No schedules found.</td></tr><?php } ?>
</tbody></table></div>
<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Mass Delete</button>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>

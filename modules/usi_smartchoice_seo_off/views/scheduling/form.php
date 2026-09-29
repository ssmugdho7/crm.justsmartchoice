<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<h4><?php echo html_escape($title); ?></h4><hr />
<?php echo form_open(current_url()); ?>
<div class="row">
<div class="col-md-6"><?php echo render_input('title','Schedule Title',$schedule['title'] ?? ''); ?></div>
<div class="col-md-3"><?php echo render_select('project_handoff_id',$handoffs,['id','title'],'Project Handoff',$schedule['project_handoff_id'] ?? ''); ?></div>
<div class="col-md-3"><?php echo render_select('status',[[ 'id'=>'draft','name'=>'Draft'],['id'=>'generated','name'=>'Generated'],['id'=>'ready_for_calendar','name'=>'Ready For Calendar'],['id'=>'active','name'=>'Active'],['id'=>'completed','name'=>'Completed']],['id','name'],'Status',$schedule['status'] ?? 'draft'); ?></div>
</div>
<div class="row">
<div class="col-md-3"><?php echo render_date_input('start_date','Start Date',$schedule['start_date'] ?? ''); ?></div>
<div class="col-md-3"><?php echo render_date_input('end_date','End Date',$schedule['end_date'] ?? ''); ?></div>
<div class="col-md-3"><?php echo render_input('duration_days','Duration Days',$schedule['duration_days'] ?? '0','number'); ?></div>
<div class="col-md-3"><?php echo render_input('schedule_type','Schedule Type',$schedule['schedule_type'] ?? 'production'); ?></div>
</div>
<?php echo render_textarea('schedule_summary','Schedule Summary',$schedule['schedule_summary'] ?? '', ['rows'=>4]); ?>
<?php echo render_textarea('conflict_notes','Conflict Notes',$schedule['conflict_notes'] ?? '', ['rows'=>4]); ?>
<button class="btn btn-primary btn-sm" type="submit">Save</button>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/scheduling'); ?>">Back</a>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>

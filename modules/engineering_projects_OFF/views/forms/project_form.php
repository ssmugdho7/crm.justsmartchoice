<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $ep = $engineering_project ?? null; ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-10 col-md-offset-1">
<div class="panel_s"><div class="panel-body">
<h4 class="no-margin"><i class="fa fa-cubes"></i> <?php echo html_escape($title); ?></h4>
<p class="text-muted mtop10">Create or update an Engineering Hub project linked to an existing CRM project and customer.</p>
<hr class="hr-panel-heading" />
<?php echo form_open(admin_url('engineering_projects/engineering_project' . (!empty($id) ? '/' . (int)$id : '')), ['id'=>'engineering-project-form']); ?>
<div class="row">
<div class="col-md-6"><?php echo render_select('project_id', $projects ?? [], ['id','name'], 'CRM Project', $ep->project_id ?? '', ['data-live-search'=>'true']); ?></div>
<div class="col-md-6"><?php echo render_select('customer_id', $customers ?? [], ['userid','company'], 'Customer', $ep->customer_id ?? '', ['data-live-search'=>'true']); ?></div>
</div>
<?php echo render_input('name', 'Engineering Job Name', $ep->name ?? ''); ?>
<div class="row">
<div class="col-md-6"><?php echo render_date_input('start_date', 'Start Date', isset($ep->start_date) ? _d($ep->start_date) : ''); ?></div>
<div class="col-md-6"><?php echo render_date_input('end_date', 'End Date', isset($ep->end_date) ? _d($ep->end_date) : ''); ?></div>
</div>
<div class="text-right"><a href="<?php echo admin_url('engineering_projects'); ?>" class="btn btn-default">Cancel</a> <button type="submit" class="btn btn-info">Save Engineering Project</button></div>
<?php echo form_close(); ?>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>
<script>$(function(){ appValidateForm($('#engineering-project-form'), {name:'required'}); });</script>
</body></html>

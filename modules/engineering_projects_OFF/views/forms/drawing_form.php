<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $drawing = $drawing ?? null; ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-10 col-md-offset-1">
<div class="panel_s"><div class="panel-body">
<h4 class="no-margin"><i class="fa fa-file-image-o"></i> <?php echo html_escape($title); ?></h4>
<p class="text-muted mtop10">Upload and track draft and final drawing files inside the linked customer/project folder.</p>
<hr class="hr-panel-heading" />
<?php echo form_open_multipart(admin_url('engineering_projects/drawings/drawing' . (!empty($id) ? '/' . (int)$id : '')), ['id'=>'engineering-drawing-form']); ?>
<?php echo form_hidden('id', (int)($id ?? 0)); ?>
<div class="row">
<div class="col-md-4"><?php echo render_select('engineering_project_id', $engineering_projects ?? [], ['engg_proj_id','name'], 'Engineering Project', $drawing->engineering_project_id ?? '', ['data-live-search'=>'true']); ?></div>
<div class="col-md-4"><?php echo render_select('project_id', $projects ?? [], ['id','name'], 'CRM Project', $drawing->project_id ?? '', ['data-live-search'=>'true']); ?></div>
<div class="col-md-4"><?php echo render_select('customer_id', $customers ?? [], ['userid','company'], 'Customer', $drawing->customer_id ?? '', ['data-live-search'=>'true']); ?></div>
</div>
<?php echo render_input('name', 'Drawing Name', $drawing->name ?? ''); ?>
<?php echo render_select('type', drawing_type(false, null), ['id','name'], 'Drawing Type', $drawing->type ?? ''); ?>
<div class="row">
<div class="col-md-6"><label class="control-label">Draft File</label><input type="file" name="draf" class="form-control"></div>
<div class="col-md-6"><label class="control-label">Final File</label><input type="file" name="final_doc" class="form-control"></div>
</div>
<div class="text-right mtop20"><a href="<?php echo admin_url('engineering_projects/drawings'); ?>" class="btn btn-default">Cancel</a> <button type="submit" class="btn btn-info">Save Drawing</button></div>
<?php echo form_close(); ?>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>
<script>$(function(){ appValidateForm($('#engineering-drawing-form'), {name:'required'}); });</script>
</body></html>

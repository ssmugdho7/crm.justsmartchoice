<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $document = $document ?? null; ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-10 col-md-offset-1">
<div class="panel_s"><div class="panel-body">
<h4 class="no-margin"><i class="fa fa-folder-open"></i> <?php echo html_escape($title); ?></h4>
<p class="text-muted mtop10">Create an engineering document record and upload permit, inspection, NOC, and engineering letter files.</p>
<hr class="hr-panel-heading" />
<?php echo form_open_multipart(admin_url('engineering_projects/documents/document' . (!empty($id) ? '/' . (int)$id : '')), ['id'=>'engineering-document-form']); ?>
<?php echo form_hidden('id', (int)($id ?? 0)); ?>
<div class="row">
<div class="col-md-4"><?php echo render_select('engineering_project_id', $engineering_projects ?? [], ['engg_proj_id','name'], 'Engineering Project', $document->engineering_project_id ?? '', ['data-live-search'=>'true']); ?></div>
<div class="col-md-4"><?php echo render_select('project_id', $projects ?? [], ['id','name'], 'CRM Project', $document->project_id ?? '', ['data-live-search'=>'true']); ?></div>
<div class="col-md-4"><?php echo render_select('customer_id', $customers ?? [], ['userid','company'], 'Customer', $document->customer_id ?? '', ['data-live-search'=>'true']); ?></div>
</div>
<?php echo render_input('name', 'Document Name', $document->name ?? ''); ?>
<div class="row">
<div class="col-md-6"><label class="control-label">Notice of Commencement</label><input type="file" name="noc" class="form-control"></div>
<div class="col-md-6"><label class="control-label">Engineering Letter</label><input type="file" name="eng_letter" class="form-control"></div>
</div>
<div class="row mtop15">
<div class="col-md-6"><label class="control-label">Site Inspection</label><input type="file" name="site_insp" class="form-control"></div>
<div class="col-md-6"><label class="control-label">Permit File</label><input type="file" name="permit" class="form-control"></div>
</div>
<div class="text-right mtop20"><a href="<?php echo admin_url('engineering_projects/documents'); ?>" class="btn btn-default">Cancel</a> <button type="submit" class="btn btn-info">Save Document</button></div>
<?php echo form_close(); ?>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>
<script>$(function(){ appValidateForm($('#engineering-document-form'), {name:'required'}); });</script>
</body></html>

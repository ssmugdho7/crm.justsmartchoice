<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); $estimate = $estimate ?? []; ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?><h4><?php echo html_escape($title); ?></h4>
<?php echo form_open_multipart(current_full_url()); ?>
<?php echo render_input('title', 'Estimate Title', $estimate['title'] ?? 'AI Jobsite Estimate'); ?>
<div class="row"><div class="col-md-3"><?php echo render_input('customer_id', 'Customer ID', $estimate['customer_id'] ?? 0, 'number'); ?></div><div class="col-md-3"><?php echo render_input('lead_id', 'Lead ID', $estimate['lead_id'] ?? 0, 'number'); ?></div><div class="col-md-3"><?php echo render_input('project_id', 'Project ID', $estimate['project_id'] ?? 0, 'number'); ?></div><div class="col-md-3"><?php echo render_select('status', [['id'=>'draft','name'=>'Draft'],['id'=>'photo_review','name'=>'Photo Review'],['id'=>'priced','name'=>'Priced'],['id'=>'ready_for_crm','name'=>'Ready For CRM'],['id'=>'converted','name'=>'Converted']], ['id','name'], 'Status', $estimate['status'] ?? 'draft'); ?></div></div>
<?php echo render_input('location', 'Jobsite Location', $estimate['location'] ?? ''); ?>
<?php echo render_textarea('scope_summary', 'Scope Summary', $estimate['scope_summary'] ?? '', ['rows'=>4]); ?>
<?php echo render_textarea('measurement_notes', 'Measurement Notes', $estimate['measurement_notes'] ?? '', ['rows'=>4]); ?>
<?php echo render_textarea('materials_summary', 'Materials Summary', $estimate['materials_summary'] ?? '', ['rows'=>4]); ?>
<?php echo render_textarea('labor_summary', 'Labor Summary', $estimate['labor_summary'] ?? '', ['rows'=>4]); ?>
<?php echo render_textarea('ai_observations', 'AI Observations', $estimate['ai_observations'] ?? '', ['rows'=>4]); ?>
<div class="row"><div class="col-md-4"><?php echo render_input('subtotal', 'Subtotal', $estimate['subtotal'] ?? 0, 'number'); ?></div><div class="col-md-4"><?php echo render_input('tax_total', 'Tax Total', $estimate['tax_total'] ?? 0, 'number'); ?></div><div class="col-md-4"><?php echo render_input('total', 'Total', $estimate['total'] ?? 0, 'number'); ?></div></div>
<div class="form-group"><label>Upload Jobsite Photo</label><input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
<div class="row"><div class="col-md-6"><?php echo render_input('photo_type', 'Photo Type', 'jobsite'); ?></div><div class="col-md-6"><?php echo render_input('measurement_note', 'Photo Measurement Note', ''); ?></div></div>
<?php echo render_textarea('ai_caption', 'Photo AI Caption', '', ['rows'=>2]); ?>
<button class="btn btn-primary btn-sm" type="submit">Save</button> <button class="btn btn-success btn-sm" type="submit" name="run_estimate" value="1"><i class="fa fa-calculator"></i> Run Estimate</button> <a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_estimates'); ?>">Back</a>
<?php echo form_close(); ?></div></div></div></div><?php init_tail(); ?>

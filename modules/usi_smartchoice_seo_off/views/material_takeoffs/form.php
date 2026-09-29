<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<h4 class="no-margin"><i class="fa fa-cubes"></i> <?php echo html_escape($title); ?></h4><hr>
<?php echo form_open(admin_url('usi_smartchoice_seo/material_takeoff/' . (int)($takeoff['id'] ?? 0))); ?>
<div class="row">
  <div class="col-md-6"><?php echo render_input('title', 'Title', $takeoff['title'] ?? ''); ?></div>
  <div class="col-md-3"><?php echo render_input('ai_estimate_id', 'AI Estimate ID', $takeoff['ai_estimate_id'] ?? '0', 'number'); ?></div>
  <div class="col-md-3"><?php echo render_input('waste_percent', 'Waste Percent', $takeoff['waste_percent'] ?? '10', 'number'); ?></div>
</div>
<div class="row">
  <div class="col-md-3"><?php echo render_input('customer_id', 'Customer ID', $takeoff['customer_id'] ?? '0', 'number'); ?></div>
  <div class="col-md-3"><?php echo render_input('project_id', 'Project ID', $takeoff['project_id'] ?? '0', 'number'); ?></div>
  <div class="col-md-3"><?php echo render_input('service_category', 'Service Category', $takeoff['service_category'] ?? 'general'); ?></div>
  <div class="col-md-3"><?php echo render_input('room_area', 'Room / Area', $takeoff['room_area'] ?? ''); ?></div>
</div>
<?php echo render_textarea('measurement_notes', 'Measurement Notes', $takeoff['measurement_notes'] ?? '', ['rows'=>5]); ?>
<?php echo render_textarea('takeoff_summary', 'Takeoff Summary / Scope', $takeoff['takeoff_summary'] ?? '', ['rows'=>5]); ?>
<div class="row"><div class="col-md-4"><select name="status" class="form-control"><option value="draft">Draft</option><option value="calculated" <?php echo (($takeoff['status'] ?? '')==='calculated'?'selected':''); ?>>Calculated</option><option value="ready_for_purchasing" <?php echo (($takeoff['status'] ?? '')==='ready_for_purchasing'?'selected':''); ?>>Ready For Purchasing</option></select></div></div>
<div class="mtop20"><button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save</button> <a href="<?php echo admin_url('usi_smartchoice_seo/material_takeoffs'); ?>" class="btn btn-default btn-sm">Back</a></div>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>

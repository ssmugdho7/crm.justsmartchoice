<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<h4><i class="fa fa-database"></i> <?php echo html_escape($title); ?></h4>
<?php echo form_open(admin_url('usi_smartchoice_seo/memory_item/' . (int)($item['id'] ?? 0))); ?>
<div class="row">
  <div class="col-md-4"><?php echo render_input('source_title', 'Title', $item['source_title'] ?? ''); ?></div>
  <div class="col-md-2"><?php echo render_input('source_type', 'Source Type', $item['source_type'] ?? 'manual'); ?></div>
  <div class="col-md-2"><?php echo render_input('source_id', 'Source ID', $item['source_id'] ?? 0, 'number'); ?></div>
  <div class="col-md-2"><?php echo render_input('customer_id', 'Customer ID', $item['customer_id'] ?? 0, 'number'); ?></div>
  <div class="col-md-2"><?php echo render_input('project_id', 'Project ID', $item['project_id'] ?? 0, 'number'); ?></div>
</div>
<div class="row">
  <div class="col-md-3"><?php echo render_input('city', 'City', $item['city'] ?? ''); ?></div>
  <div class="col-md-3"><?php echo render_input('service_category', 'Service Category', $item['service_category'] ?? 'general'); ?></div>
  <div class="col-md-3"><?php echo render_input('amount_total', 'Amount Total', $item['amount_total'] ?? '0.00', 'number'); ?></div>
  <div class="col-md-3"><?php echo render_input('record_date', 'Record Date', $item['record_date'] ?? '', 'date'); ?></div>
</div>
<?php echo render_textarea('memory_text', 'Memory Text', $item['memory_text'] ?? '', ['rows' => 8]); ?>
<?php echo render_textarea('summary', 'Summary', $item['summary'] ?? '', ['rows' => 3]); ?>
<?php echo render_textarea('keywords', 'Keywords', $item['keywords'] ?? '', ['rows' => 2]); ?>
<div class="row"><div class="col-md-3">
<label>Status</label>
<select name="memory_status" class="form-control"><option value="active" <?php echo (($item['memory_status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option><option value="review" <?php echo (($item['memory_status'] ?? '') === 'review') ? 'selected' : ''; ?>>Review</option><option value="archived" <?php echo (($item['memory_status'] ?? '') === 'archived') ? 'selected' : ''; ?>>Archived</option></select>
</div></div>
<div class="mtop20"><button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Save</button><a href="<?php echo admin_url('usi_smartchoice_seo/memory_engine'); ?>" class="btn btn-default">Back</a></div>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<h4><?php echo html_escape($title); ?></h4>
<?php echo form_open(current_full_url()); ?>
<div class="row">
<div class="col-md-6">
<?php echo render_input('title', 'Title', $closeout['title'] ?? ''); ?>
<?php echo render_input('closeout_date', 'Closeout Date', $closeout['closeout_date'] ?? date('Y-m-d'), 'date'); ?>
<?php echo render_input('jobsite_log_id', 'Jobsite Log ID', $closeout['jobsite_log_id'] ?? '0', 'number'); ?>
<?php echo render_input('ai_estimate_id', 'AI Estimate ID', $closeout['ai_estimate_id'] ?? '0', 'number'); ?>
<?php echo render_input('project_id', 'Project ID', $closeout['project_id'] ?? '0', 'number'); ?>
<select name="status" class="form-control"><option value="draft">Draft</option><option value="in_review">In Review</option><option value="ready_for_customer">Ready For Customer</option><option value="completed">Completed</option></select>
<div class="checkbox checkbox-primary"><input type="checkbox" name="ready_for_customer" value="1" <?php echo !empty($closeout['ready_for_customer']) ? 'checked' : ''; ?>><label>Ready For Customer</label></div>
</div>
<div class="col-md-6">
<?php echo render_textarea('final_photo_checklist', 'Final Photo Checklist', $closeout['final_photo_checklist'] ?? '', ['rows'=>5]); ?>
<?php echo render_textarea('punch_summary', 'Punch Summary', $closeout['punch_summary'] ?? '', ['rows'=>5]); ?>
<?php echo render_textarea('customer_walkthrough_notes', 'Customer Walkthrough Notes', $closeout['customer_walkthrough_notes'] ?? '', ['rows'=>5]); ?>
<?php echo render_textarea('warranty_summary', 'Warranty Summary', $closeout['warranty_summary'] ?? '', ['rows'=>5]); ?>
<?php echo render_textarea('closeout_package_notes', 'Closeout Package Notes', $closeout['closeout_package_notes'] ?? '', ['rows'=>5]); ?>
</div>
</div>
<button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save</button>
<a href="<?php echo admin_url('usi_smartchoice_seo/closeout_warranty'); ?>" class="btn btn-default btn-sm">Cancel</a>
<?php echo form_close(); ?>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<h4 class="no-margin"><i class="fa fa-truck"></i> <?php echo html_escape($title); ?></h4><hr>
<?php echo form_open(current_url()); ?>
<div class="row">
  <div class="col-md-6"><?php echo render_input('vendor_name', 'Vendor Name', $vendor['vendor_name'] ?? ''); ?></div>
  <div class="col-md-6"><?php echo render_input('contact_name', 'Contact Name', $vendor['contact_name'] ?? ''); ?></div>
</div>
<div class="row">
  <div class="col-md-4"><?php echo render_input('email', 'Email', $vendor['email'] ?? ''); ?></div>
  <div class="col-md-4"><?php echo render_input('phone', 'Phone', $vendor['phone'] ?? ''); ?></div>
  <div class="col-md-4"><?php echo render_input('website', 'Website', $vendor['website'] ?? ''); ?></div>
</div>
<div class="row">
  <div class="col-md-4"><?php echo render_input('category', 'Category', $vendor['category'] ?? 'general'); ?></div>
  <div class="col-md-4"><?php echo render_input('rating', 'Rating', $vendor['rating'] ?? '0.00', 'number', ['step'=>'0.01','min'=>'0','max'=>'5']); ?></div>
  <div class="col-md-4"><label>Status</label><select name="status" class="form-control"><?php foreach(['active'=>'Active','inactive'=>'Inactive','preferred_review'=>'Preferred Review'] as $k=>$v){ ?><option value="<?php echo $k; ?>" <?php echo (($vendor['status'] ?? 'active')===$k?'selected':''); ?>><?php echo $v; ?></option><?php } ?></select></div>
</div>
<div class="checkbox checkbox-primary"><input type="checkbox" name="preferred" id="preferred" value="1" <?php echo !empty($vendor['preferred']) ? 'checked' : ''; ?>><label for="preferred">Preferred Vendor</label></div>
<?php echo render_textarea('notes', 'Notes', $vendor['notes'] ?? '', ['rows'=>6]); ?>
<button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save Vendor</button>
<a href="<?php echo admin_url('usi_smartchoice_seo/purchasing'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>

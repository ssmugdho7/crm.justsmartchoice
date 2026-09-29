<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <?php echo form_open(admin_url('purchasing_hub/vendor/'.($vendor['id'] ?? ''))); ?>
  <div class="row">
    <div class="col-md-6"><?php echo render_input('vendor_name', 'Vendor Name', $vendor['vendor_name'] ?? '', 'text', ['required'=>true]); ?></div>
    <div class="col-md-6"><?php echo render_input('contact_name', 'Contact Name', $vendor['contact_name'] ?? ''); ?></div>
    <div class="col-md-4"><?php echo render_input('email', 'Email', $vendor['email'] ?? '', 'email'); ?></div>
    <div class="col-md-4"><?php echo render_input('phone', 'Phone', $vendor['phone'] ?? ''); ?></div>
    <div class="col-md-4"><?php echo render_input('trade', 'Trade', $vendor['trade'] ?? ''); ?></div>
    <div class="col-md-4"><?php echo render_input('status', 'Status', $vendor['status'] ?? 'Active'); ?></div>
    <div class="col-md-4"><?php echo render_input('insurance_status', 'Insurance Status', $vendor['insurance_status'] ?? 'Not Verified'); ?></div>
    <div class="col-md-12"><?php echo render_textarea('address', 'Address', $vendor['address'] ?? ''); ?></div>
    <div class="col-md-12"><?php echo render_textarea('notes', 'Notes', $vendor['notes'] ?? ''); ?></div>
  </div>
  <div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" id="is_active" <?php echo !isset($vendor['is_active']) || $vendor['is_active'] ? 'checked' : ''; ?>><label for="is_active">Active</label></div>
  <button type="submit" class="btn btn-primary">Save Vendor</button>
  <a href="<?php echo admin_url('purchasing_hub/vendors'); ?>" class="btn btn-default">Cancel</a>
  <?php echo form_close(); ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <div class="ph-action-row purchasing-hub-action-row ph-primary-action-row"><a href="<?php echo admin_url('purchasing_hub/vendor'); ?>" class="btn btn-primary btn-sm ph-btn-compact">Add Vendor</a> <a href="<?php echo admin_url('purchasing_hub/sample/vendors'); ?>" class="btn btn-info btn-sm ph-btn-compact">Sample Import File</a></div>
  <?php echo form_open(admin_url('purchasing_hub/vendors'), ['method'=>'get','class'=>'form-inline m-b-md ph-filter-row']); ?>
    <input type="text" name="q" class="form-control input-sm" placeholder="Search vendors" value="<?php echo html_escape($this->input->get('q')); ?>">
    <input type="text" name="status" class="form-control input-sm" placeholder="Status" value="<?php echo html_escape($this->input->get('status')); ?>">
    <input type="text" name="created_from" class="form-control input-sm datepicker" placeholder="Created From" value="<?php echo html_escape($this->input->get('created_from')); ?>">
    <input type="text" name="created_to" class="form-control input-sm datepicker" placeholder="Created To" value="<?php echo html_escape($this->input->get('created_to')); ?>">
    <button type="submit" class="btn btn-default btn-sm ph-btn-compact">Filter</button>
  <?php echo form_close(); ?>
  <?php echo form_open(admin_url('purchasing_hub/massive_delete/vendors'), ['id'=>'vendors-mass-delete']); ?>
  <div class="ph-dt-actions" data-ph-dt-actions="1" aria-label="Table actions">
    <a href="<?php echo admin_url('purchasing_hub/import/vendors'); ?>" class="btn btn-default btn-sm ph-btn-compact">Import</a>
    <a href="<?php echo admin_url('purchasing_hub/vendors'); ?>" class="btn btn-default btn-sm ph-btn-compact">Refresh</a>
    <button type="submit" form="vendors-mass-delete" class="btn btn-danger btn-sm ph-btn-compact" onclick="return confirm('Delete selected vendors?');">Massive Delete</button>
  </div>
  <div class="table-responsive">
    <table class="table table-bordered dt-table purchasing-hub-table">
      <thead><tr><th><input type="checkbox" onclick="$('.ph-vendor-check').prop('checked', this.checked);"></th><th>Vendor</th><th>Contact</th><th>Email</th><th>Phone</th><th>Trade</th><th>Status</th><th>Insurance</th><th>Options</th></tr></thead>
      <tbody><?php foreach($vendors as $v){ ?><tr>
        <td><input class="ph-vendor-check" type="checkbox" name="ids[]" value="<?php echo (int)$v['id']; ?>"></td>
        <td><?php echo html_escape($v['vendor_name']); ?></td><td><?php echo html_escape($v['contact_name']); ?></td><td><?php echo html_escape($v['email']); ?></td><td><?php echo html_escape($v['phone']); ?></td><td><?php echo html_escape($v['trade']); ?></td><td><?php echo html_escape($v['status']); ?></td><td><?php echo html_escape($v['insurance_status']); ?></td>
        <td><a class="btn btn-default btn-xs" href="<?php echo admin_url('purchasing_hub/vendor/'.$v['id']); ?>">Edit</a> <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('purchasing_hub/delete/vendors/'.$v['id']); ?>">Delete</a></td>
      </tr><?php } ?></tbody>
    </table>
  </div>
<?php echo form_close(); ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>

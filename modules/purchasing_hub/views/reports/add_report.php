<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('purchasing_hub/_header'); ?>
<div class="panel_s"><div class="panel-body">
  <h3>Add Purchasing Report</h3>
  <?php echo form_open(admin_url('purchasing_hub/add_report')); ?>
    <div class="form-group">
      <label for="report_name">Report Name</label>
      <input type="text" name="report_name" id="report_name" class="form-control" required value="">
    </div>
    <div class="form-group">
      <label for="report_type">Report Type</label>
      <select name="report_type" id="report_type" class="form-control selectpicker" data-live-search="true" required>
        <option value="">Select Report Type</option>
        <?php foreach($available_reports as $key => $label){ ?>
          <option value="<?php echo html_escape($key); ?>"><?php echo html_escape($label); ?></option>
        <?php } ?>
      </select>
      <p class="text-muted mtop10">Available report sources include Purchasing Hub, Estimates, Proposals, Invoices, Payments, Credit Notes, Sales, Projects, project costs, and project profit/loss.</p>
    </div>
    <div class="form-group">
      <label for="description">Description</label>
      <textarea name="description" id="description" class="form-control" rows="4"></textarea>
    </div>
    <div class="checkbox checkbox-primary">
      <input type="checkbox" name="is_active" id="is_active" checked>
      <label for="is_active">Active</label>
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save Report</button>
    <a href="<?php echo admin_url('purchasing_hub/reports'); ?>" class="btn btn-default btn-sm">Cancel</a>
  <?php echo form_close(); ?>
</div></div>
<?php $this->load->view('purchasing_hub/_footer'); ?>

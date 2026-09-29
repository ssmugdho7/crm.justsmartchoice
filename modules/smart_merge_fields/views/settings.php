<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s smf-panel"><div class="panel-body">
<h4 class="smf-title">Merge Fields Settings</h4>
<?php $this->load->view('smart_merge_fields/_nav'); ?>
<?php echo form_open(admin_url('smart_merge_fields/settings')); ?>
<div class="row">
<div class="col-md-4"><label>Automatic Database Scan</label><select name="auto_scan_enabled" class="form-control"><option value="1" <?php echo (($settings['auto_scan_enabled'] ?? '1')==='1')?'selected':''; ?>>Enabled</option><option value="0" <?php echo (($settings['auto_scan_enabled'] ?? '1')==='0')?'selected':''; ?>>Disabled</option></select></div>
<div class="col-md-4"><label>Database Synchronization</label><select name="allow_database_sync" class="form-control"><option value="1" <?php echo (($settings['allow_database_sync'] ?? '1')==='1')?'selected':''; ?>>Enabled</option><option value="0" <?php echo (($settings['allow_database_sync'] ?? '1')==='0')?'selected':''; ?>>Disabled</option></select></div>
<div class="col-md-4"><label>Safe Mode</label><select name="safe_mode" class="form-control"><option value="1" <?php echo (($settings['safe_mode'] ?? '1')==='1')?'selected':''; ?>>Enabled</option><option value="0" <?php echo (($settings['safe_mode'] ?? '1')==='0')?'selected':''; ?>>Disabled</option></select></div>
</div>
<div class="smf-form-actions"><button class="btn btn-info smf-btn" type="submit">Save Settings</button></div>
<?php echo form_close(); ?>
<hr>
<h4 class="smf-section-title">Create Lead Custom Field</h4>
<p class="text-muted">Use this when a missing field must be added to Leads before mapping it to customers, projects, contracts, invoices, or other CRM records.</p>
<?php echo form_open(admin_url('smart_merge_fields/lead_field')); ?>
<div class="row"><div class="col-md-6"><?php echo render_input('field_name', 'Lead Field Name'); ?></div><div class="col-md-3"><label>Field Type</label><select name="field_type" class="form-control"><option value="input">Text</option><option value="textarea">Long Text</option><option value="date_picker">Date</option><option value="number">Number</option></select></div><div class="col-md-3 smf-create-field"><button class="btn btn-info smf-btn" type="submit">Create Field</button></div></div>
<?php echo form_close(); ?>
</div></div></div></div>
<?php init_tail(); ?>

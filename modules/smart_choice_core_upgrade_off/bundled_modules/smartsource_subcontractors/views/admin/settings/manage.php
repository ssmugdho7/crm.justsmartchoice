<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo smartsource_admin_submenu('settings'); ?>
<div class="row">
<div class="col-md-12"><div class="panel_s smartsource-panel"><div class="panel-body">
<h4>Subcontractor Settings</h4><p class="text-muted">Manage module activation, categories, statuses, uninstall cleanup, and health verification.</p><hr>
<?php echo form_open(admin_url('smartsource_subcontractors/settings')); ?><input type="hidden" name="settings" value="1">
<div class="checkbox checkbox-primary"><input type="checkbox" name="smartsource_subcontractors_enabled" id="smartsource_subcontractors_enabled" value="1" <?php echo get_option('smartsource_subcontractors_enabled') === '1' ? 'checked' : ''; ?>><label for="smartsource_subcontractors_enabled">Module Enabled</label></div>
<div class="checkbox checkbox-danger"><input type="checkbox" name="smartsource_subcontractors_delete_data_on_uninstall" id="smartsource_subcontractors_delete_data_on_uninstall" value="1" <?php echo get_option('smartsource_subcontractors_delete_data_on_uninstall') === '1' ? 'checked' : ''; ?>><label for="smartsource_subcontractors_delete_data_on_uninstall">Delete Module Tables And Uploaded Files On Uninstall</label></div>
<button class="btn btn-primary" type="submit"><?php echo _l('save'); ?></button>
<?php echo form_close(); ?>
</div></div></div>
<div class="col-md-12"><div class="panel_s smartsource-panel smartsource-test-panel"><div class="panel-body">
<h4>Email And SMS Testing</h4>
<p class="text-muted">Use this area to verify that subcontractor portal registration, document upload alerts, and contract notifications can send messages through your CRM email/SMS configuration.</p>
<div class="row">
<div class="col-md-6">
<?php echo form_open(admin_url('smartsource_subcontractors/settings')); ?>
<input type="hidden" name="smartsource_test_email" value="1">
<?php echo render_input('test_email_to','Test Email Address','', 'email'); ?>
<button class="btn btn-info" type="submit"><i class="fa fa-envelope"></i> Send Test Email</button>
<?php echo form_close(); ?>
</div>
<div class="col-md-6">
<?php echo form_open(admin_url('smartsource_subcontractors/settings')); ?>
<input type="hidden" name="smartsource_test_sms" value="1">
<?php echo render_input('test_sms_to','Test Phone Number','', 'text'); ?>
<?php echo render_textarea('test_sms_message','Test SMS Message','SmartSource Subcontractors SMS test from Smart Choice CRM.'); ?>
<button class="btn btn-warning" type="submit"><i class="fa fa-mobile"></i> Send Test SMS</button>
<p class="text-muted">SMS requires an existing CRM SMS/Twilio provider configuration.</p>
<?php echo form_close(); ?>
</div>
</div>
</div></div></div>
<div class="col-md-4"><div class="panel_s smartsource-panel"><div class="panel-body"><h4>Categories</h4>
<?php echo form_open(admin_url('smartsource_subcontractors/settings')); ?><?php echo render_input('category_name','name'); ?><?php echo render_input('category_color','color','#169179','color'); ?><button class="btn btn-primary"><?php echo _l('add'); ?></button><?php echo form_close(); ?><hr>
<?php foreach ($categories as $row) { ?><p><span class="label" style="background:<?php echo html_escape($row['color']); ?>"><?php echo html_escape($row['name']); ?></span> <a class="text-danger _delete" href="<?php echo admin_url('smartsource_subcontractors/delete_setting_record/smartsource_subcontractor_categories/' . $row['id']); ?>"><i class="fa fa-trash"></i></a></p><?php } ?>
</div></div></div>
<div class="col-md-4"><div class="panel_s smartsource-panel"><div class="panel-body"><h4>Subcontractor Statuses</h4>
<?php echo form_open(admin_url('smartsource_subcontractors/settings')); ?><?php echo render_input('status_name','name'); ?><?php echo render_input('status_color','color','#169179','color'); ?><button class="btn btn-primary"><?php echo _l('add'); ?></button><?php echo form_close(); ?><hr>
<?php foreach ($statuses as $row) { ?><p><span class="label" style="background:<?php echo html_escape($row['color']); ?>"><?php echo html_escape($row['name']); ?></span> <a class="text-danger _delete" href="<?php echo admin_url('smartsource_subcontractors/delete_setting_record/smartsource_subcontractor_statuses/' . $row['id']); ?>"><i class="fa fa-trash"></i></a></p><?php } ?>
</div></div></div>
<div class="col-md-4"><div class="panel_s smartsource-panel"><div class="panel-body"><h4>Contract Statuses</h4>
<?php echo form_open(admin_url('smartsource_subcontractors/settings')); ?><?php echo render_input('contract_status_name','name'); ?><?php echo render_input('contract_status_color','color','#169179','color'); ?><button class="btn btn-primary"><?php echo _l('add'); ?></button><?php echo form_close(); ?><hr>
<?php foreach ($contract_statuses as $row) { ?><p><span class="label" style="background:<?php echo html_escape($row['color']); ?>"><?php echo html_escape($row['name']); ?></span> <a class="text-danger _delete" href="<?php echo admin_url('smartsource_subcontractors/delete_setting_record/smartsource_subcontractor_contract_statuses/' . $row['id']); ?>"><i class="fa fa-trash"></i></a></p><?php } ?>
</div></div></div>
<div class="col-md-12"><div class="panel_s smartsource-panel"><div class="panel-body"><h4>Health Verification</h4><p><a href="<?php echo admin_url('smartsource_subcontractors/repair_database'); ?>" class="btn btn-warning"><i class="fa fa-database"></i> Repair Database Tables</a> <a href="<?php echo admin_url('smartsource_subcontractors/settings'); ?>" class="btn btn-default"><i class="fa fa-heartbeat"></i> Run Health Check</a></p><table class="table table-bordered"><thead><tr><th><?php echo _l('name'); ?></th><th><?php echo _l('status'); ?></th></tr></thead><tbody><?php foreach ($health as $check) { ?><tr><td><?php echo html_escape($check['name']); ?></td><td><?php echo $check['ok'] ? '<span class="label label-success">OK</span>' : '<span class="label label-danger">ERROR</span>'; ?></td></tr><?php } ?></tbody></table></div></div></div>
</div></div></div>
<?php init_tail(); ?>

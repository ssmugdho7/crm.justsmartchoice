<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo sales_center_admin_submenu('settings'); ?>
<div class="row">
<div class="col-md-12"><div class="panel_s smartsource-panel"><div class="panel-body">
<h4>Sales Center Settings</h4><p class="text-muted">Manage module activation, sales categories, salesperson statuses, agreement statuses, uninstall cleanup, email/SMS testing, and health verification.</p><hr>
<?php echo form_open(admin_url('sales_center/settings')); ?><input type="hidden" name="settings" value="1">
<div class="checkbox checkbox-primary"><input type="checkbox" name="sales_center_enabled" id="sales_center_enabled" value="1" <?php echo get_option('sales_center_enabled') === '1' ? 'checked' : ''; ?>><label for="sales_center_enabled">Module Enabled</label></div>
<div class="checkbox checkbox-danger"><input type="checkbox" name="sales_center_delete_data_on_uninstall" id="sales_center_delete_data_on_uninstall" value="1" <?php echo get_option('sales_center_delete_data_on_uninstall') === '1' ? 'checked' : ''; ?>><label for="sales_center_delete_data_on_uninstall">Delete Module Tables And Uploaded Files On Uninstall</label></div>
<hr>
<h4>Native CRM Sales Menu Visibility</h4>
<p class="text-muted">Use Sales Hub as the sales department command center. You can hide the original CRM Sales menu completely, or hide individual native sales entries while keeping their data synchronized inside Sales Hub.</p>
<div class="checkbox checkbox-primary"><input type="checkbox" name="sales_center_hide_core_sales_menu" id="sales_center_hide_core_sales_menu" value="1" <?php echo get_option('sales_center_hide_core_sales_menu') === '1' ? 'checked' : ''; ?>><label for="sales_center_hide_core_sales_menu">Hide Original CRM Sales Menu</label></div>
<div class="row">
  <div class="col-md-4"><div class="checkbox checkbox-primary"><input type="checkbox" name="sales_center_hide_core_proposals" id="sales_center_hide_core_proposals" value="1" <?php echo get_option('sales_center_hide_core_proposals') === '1' ? 'checked' : ''; ?>><label for="sales_center_hide_core_proposals">Hide Proposals From Original Sales Menu</label></div></div>
  <div class="col-md-4"><div class="checkbox checkbox-primary"><input type="checkbox" name="sales_center_hide_core_estimates" id="sales_center_hide_core_estimates" value="1" <?php echo get_option('sales_center_hide_core_estimates') === '1' ? 'checked' : ''; ?>><label for="sales_center_hide_core_estimates">Hide Estimates From Original Sales Menu</label></div></div>
  <div class="col-md-4"><div class="checkbox checkbox-primary"><input type="checkbox" name="sales_center_hide_core_invoices" id="sales_center_hide_core_invoices" value="1" <?php echo get_option('sales_center_hide_core_invoices') === '1' ? 'checked' : ''; ?>><label for="sales_center_hide_core_invoices">Hide Invoices From Original Sales Menu</label></div></div>
  <div class="col-md-4"><div class="checkbox checkbox-primary"><input type="checkbox" name="sales_center_hide_core_payments" id="sales_center_hide_core_payments" value="1" <?php echo get_option('sales_center_hide_core_payments') === '1' ? 'checked' : ''; ?>><label for="sales_center_hide_core_payments">Hide Payments From Original Sales Menu</label></div></div>
  <div class="col-md-4"><div class="checkbox checkbox-primary"><input type="checkbox" name="sales_center_hide_core_credit_notes" id="sales_center_hide_core_credit_notes" value="1" <?php echo get_option('sales_center_hide_core_credit_notes') === '1' ? 'checked' : ''; ?>><label for="sales_center_hide_core_credit_notes">Hide Credit Notes From Original Sales Menu</label></div></div>
  <div class="col-md-4"><div class="checkbox checkbox-primary"><input type="checkbox" name="sales_center_hide_core_items" id="sales_center_hide_core_items" value="1" <?php echo get_option('sales_center_hide_core_items') === '1' ? 'checked' : ''; ?>><label for="sales_center_hide_core_items">Hide Items From Original Sales Menu</label></div></div>
</div>
<hr>
<?php echo render_select('sales_center_view_mode', [
    ['id' => 'combined', 'name' => 'Combined Sales Hub And Native CRM Views'],
    ['id' => 'hub_only', 'name' => 'Sales Hub First'],
    ['id' => 'native_links', 'name' => 'Native CRM Pages With Sales Hub Reports'],
], ['id', 'name'], 'Sales Hub View Mode', get_option('sales_center_view_mode') ?: 'combined'); ?>
<button class="btn btn-primary" type="submit"><?php echo _l('save'); ?></button>
<?php echo form_close(); ?>
</div></div></div>
<div class="col-md-12"><div class="panel_s smartsource-panel smartsource-test-panel"><div class="panel-body">
<h4>Email And SMS Testing</h4>
<p class="text-muted">Use this area to verify that salesperson portal registration, document upload alerts, and contract notifications can send messages through your CRM email/SMS configuration.</p>
<div class="row">
<div class="col-md-6">
<?php echo form_open(admin_url('sales_center/settings')); ?>
<input type="hidden" name="sales_center_test_email" value="1">
<?php echo render_input('test_email_to','Test Email Address','', 'email'); ?>
<button class="btn btn-info" type="submit"><i class="fa fa-envelope"></i> Send Test Email</button>
<?php echo form_close(); ?>
</div>
<div class="col-md-6">
<?php echo form_open(admin_url('sales_center/settings')); ?>
<input type="hidden" name="sales_center_test_sms" value="1">
<?php echo render_input('test_sms_to','Test Phone Number','', 'text'); ?>
<?php echo render_textarea('test_sms_message','Test SMS Message','SmartSource Salespersons SMS test from Smart Choice CRM.'); ?>
<button class="btn btn-warning" type="submit"><i class="fa fa-mobile"></i> Send Test SMS</button>
<p class="text-muted">SMS requires an existing CRM SMS/Twilio provider configuration.</p>
<?php echo form_close(); ?>
</div>
</div>
</div></div></div>
<div class="col-md-4"><div class="panel_s smartsource-panel"><div class="panel-body"><h4>Categories</h4>
<?php echo form_open(admin_url('sales_center/settings')); ?><?php echo render_input('category_name','name'); ?><?php echo render_input('category_color','color','#169179','color'); ?><button class="btn btn-primary"><?php echo _l('add'); ?></button><?php echo form_close(); ?><hr>
<?php foreach ($categories as $row) { ?><p><span class="label" style="background:<?php echo html_escape($row['color']); ?>"><?php echo html_escape($row['name']); ?></span> <a class="text-danger _delete" href="<?php echo admin_url('sales_center/delete_setting_record/sales_center_categories/' . $row['id']); ?>"><i class="fa fa-trash"></i></a></p><?php } ?>
</div></div></div>
<div class="col-md-4"><div class="panel_s smartsource-panel"><div class="panel-body"><h4>Salesperson Statuses</h4>
<?php echo form_open(admin_url('sales_center/settings')); ?><?php echo render_input('status_name','name'); ?><?php echo render_input('status_color','color','#169179','color'); ?><button class="btn btn-primary"><?php echo _l('add'); ?></button><?php echo form_close(); ?><hr>
<?php foreach ($statuses as $row) { ?><p><span class="label" style="background:<?php echo html_escape($row['color']); ?>"><?php echo html_escape($row['name']); ?></span> <a class="text-danger _delete" href="<?php echo admin_url('sales_center/delete_setting_record/sales_center_statuses/' . $row['id']); ?>"><i class="fa fa-trash"></i></a></p><?php } ?>
</div></div></div>
<div class="col-md-4"><div class="panel_s smartsource-panel"><div class="panel-body"><h4>Contract Statuses</h4>
<?php echo form_open(admin_url('sales_center/settings')); ?><?php echo render_input('contract_status_name','name'); ?><?php echo render_input('contract_status_color','color','#169179','color'); ?><button class="btn btn-primary"><?php echo _l('add'); ?></button><?php echo form_close(); ?><hr>
<?php foreach ($contract_statuses as $row) { ?><p><span class="label" style="background:<?php echo html_escape($row['color']); ?>"><?php echo html_escape($row['name']); ?></span> <a class="text-danger _delete" href="<?php echo admin_url('sales_center/delete_setting_record/sales_center_contract_statuses/' . $row['id']); ?>"><i class="fa fa-trash"></i></a></p><?php } ?>
</div></div></div>
<div class="col-md-12" id="sales-center-health-check"><div class="panel_s smartsource-panel"><div class="panel-body"><h4>Health Verification</h4><p><a href="<?php echo admin_url('sales_center/repair_database'); ?>" class="btn btn-warning"><i class="fa fa-database"></i> Repair Database Tables</a> <a href="<?php echo admin_url('sales_center/settings'); ?>" class="btn btn-default"><i class="fa fa-heartbeat"></i> Run Health Check</a></p><table class="table table-bordered"><thead><tr><th><?php echo _l('name'); ?></th><th><?php echo _l('status'); ?></th></tr></thead><tbody><?php foreach ($health as $check) { ?><tr><td><?php echo html_escape($check['name']); ?></td><td><?php echo $check['ok'] ? '<span class="label label-success">OK</span>' : '<span class="label label-danger">ERROR</span>'; ?></td></tr><?php } ?></tbody></table></div></div></div>
</div></div></div>
<?php init_tail(); ?>

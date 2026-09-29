<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-installer-tracking"><div class="panel_s"><div class="panel-body">
<h3 class="sit-page-title"><?php echo html_escape($title); ?></h3>
<div class="alert alert-warning">Backup warning: run a database backup before repair or cleanup actions. This module never drops tables, truncates data, or deletes business records.</div>
<div class="table-responsive"><table class="table table-striped sit-table"><thead><tr><th>Check</th><th>Status</th><th>Details</th></tr></thead><tbody>
<?php foreach ($checks as $check) { ?><tr><td><?php echo html_escape($check['item']); ?></td><td><?php echo html_escape($check['status']); ?></td><td class="sit-wrap"><?php echo html_escape($check['details']); ?></td></tr><?php } ?>
</tbody></table></div>
<?php if (has_permission('smart_installer_tracking','','settings')) { ?>
<div class="tw-mb-3"><?php echo form_open(admin_url('smart_installer_tracking/repair'), ['class'=>'sit-inline-form']); ?><button class="btn btn-warning btn-sm" type="submit">Repair Database Safely</button><?php echo form_close(); ?> <?php echo form_open(admin_url('smart_installer_tracking/clear_safe_cache'), ['class'=>'sit-inline-form']); ?><button class="btn btn-default btn-sm" type="submit">Safe Cache Cleanup</button><?php echo form_close(); ?></div>
<?php } ?>
<h4>Module Logs</h4><div class="table-responsive"><table class="table table-striped sit-table"><thead><tr><th>Date</th><th>Staff</th><th>Action</th><th>Description</th></tr></thead><tbody><?php foreach ($logs as $log) { ?><tr><td><?php echo html_escape($log['created_at']); ?></td><td><?php echo html_escape($log['staff_name'] ?? 'System'); ?></td><td><?php echo html_escape($log['action']); ?></td><td class="sit-wrap"><?php echo html_escape($log['description']); ?></td></tr><?php } ?></tbody></table></div>
</div></div></div></div>
<?php init_tail(); ?>

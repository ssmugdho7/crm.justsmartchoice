<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-installer-tracking"><div class="panel_s"><div class="panel-body">
<div class="sit-header-actions"><h3 class="sit-page-title"><?php echo html_escape($title); ?></h3><a class="btn btn-primary btn-sm" href="<?php echo admin_url('smart_installer_tracking/create'); ?>">New Trip</a></div>
<div class="table-responsive"><table class="table table-striped sit-table"><thead><tr><th>Trip</th><th>Installer</th><th>Customer</th><th>Project</th><th>Appointment</th><th>Status</th><th>Address</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($trips as $trip) { ?>
<tr><td><?php echo (int)$trip['id']; ?></td><td><?php echo html_escape(trim(($trip['firstname'] ?? '').' '.($trip['lastname'] ?? ''))); ?></td><td><?php echo html_escape($trip['client_name'] ?: ($trip['company'] ?? '')); ?></td><td><?php echo html_escape($trip['project_name'] ?: ('Project '.$trip['project_id'])); ?></td><td><?php echo (int)($trip['appointment_id'] ?? 0); ?></td><td><?php echo html_escape(smart_installer_tracking_clean_text($trip['status'])); ?></td><td class="sit-wrap"><?php echo html_escape($trip['destination_address'] ?? ''); ?></td><td><a class="btn btn-default btn-xs" href="<?php echo admin_url('smart_installer_tracking/view/'.$trip['id']); ?>">View</a> <a class="btn btn-info btn-xs" href="<?php echo admin_url('smart_installer_tracking/live_map'); ?>">Map</a></td></tr>
<?php } ?>
</tbody></table></div>
</div></div></div></div>
<?php init_tail(); ?>

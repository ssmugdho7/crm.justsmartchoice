<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-installer-tracking">
<div class="sit-header-actions"><h3 class="sit-page-title"><?php echo html_escape($title); ?></h3><div><a class="btn btn-info btn-sm" href="<?php echo admin_url('smart_installer_tracking/live_map'); ?>">Show Live Map</a> <a class="btn btn-primary btn-sm" href="<?php echo admin_url('smart_installer_tracking/create'); ?>">New Trip</a></div></div>
<div class="row">
  <div class="col-md-3"><div class="sit-card"><span>Active Trips</span><strong><?php echo (int)$stats['active_trips']; ?></strong></div></div>
  <div class="col-md-3"><div class="sit-card"><span>Arrived Today</span><strong><?php echo (int)$stats['arrived_today']; ?></strong></div></div>
  <div class="col-md-3"><div class="sit-card"><span>Completed Today</span><strong><?php echo (int)$stats['completed_today']; ?></strong></div></div>
  <div class="col-md-3"><div class="sit-card"><span>Notifications Today</span><strong><?php echo (int)$stats['notifications_today']; ?></strong></div></div>
</div>
<div class="panel_s"><div class="panel-body"><h4>Recent Trips</h4>
<div class="table-responsive"><table class="table table-striped sit-table"><thead><tr><th>Trip</th><th>Installer</th><th>Customer</th><th>Project</th><th>Status</th><th>Destination</th><th>Action</th></tr></thead><tbody>
<?php foreach ($trips as $trip) { ?>
<tr><td><?php echo (int)$trip['id']; ?></td><td><?php echo html_escape(trim(($trip['firstname'] ?? '').' '.($trip['lastname'] ?? ''))); ?></td><td><?php echo html_escape($trip['client_name'] ?: ($trip['company'] ?? '')); ?></td><td><?php echo html_escape($trip['project_name'] ?? ''); ?></td><td><?php echo html_escape(smart_installer_tracking_clean_text($trip['status'])); ?></td><td class="sit-wrap"><?php echo html_escape($trip['destination_address'] ?? ''); ?></td><td><a class="btn btn-default btn-xs" href="<?php echo admin_url('smart_installer_tracking/view/'.$trip['id']); ?>">View</a></td></tr>
<?php } ?>
</tbody></table></div></div></div>
</div></div>
<?php init_tail(); ?>

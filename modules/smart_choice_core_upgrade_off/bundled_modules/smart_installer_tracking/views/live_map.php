<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-installer-tracking"><div class="panel_s"><div class="panel-body">
<div class="sit-header-actions"><h3 class="sit-page-title"><?php echo html_escape($title); ?></h3><a class="btn btn-success btn-sm" href="<?php echo admin_url('smart_installer_tracking/create'); ?>">New Trip</a></div>
<p class="text-muted">This map uses the Google Maps API key already saved in the CRM settings or in this module settings. Installers must start live location from their phone and allow GPS access.</p>
<?php if (trim((string)$google_maps_api_key) === '') { ?>
<div class="alert alert-warning">Google Maps API key is missing. Open Settings and connect the CRM Google API key.</div>
<?php } ?>
<div id="sit-admin-live-map" class="sit-google-map" data-trips='<?php echo html_escape(json_encode($trips)); ?>'></div>
<div class="table-responsive mtop15"><table class="table table-striped sit-table"><thead><tr><th>Installer</th><th>Status</th><th>Latitude</th><th>Longitude</th><th>Updated</th><th>View</th></tr></thead><tbody>
<?php foreach ($trips as $trip) { ?>
<tr><td><?php echo html_escape(trim(($trip['firstname'] ?? '').' '.($trip['lastname'] ?? ''))); ?></td><td><?php echo html_escape(smart_installer_tracking_clean_text($trip['status'])); ?></td><td><?php echo html_escape($trip['last_lat'] ?? ''); ?></td><td><?php echo html_escape($trip['last_lng'] ?? ''); ?></td><td><?php echo html_escape($trip['last_location_at'] ?? ''); ?></td><td><a class="btn btn-default btn-xs" href="<?php echo admin_url('smart_installer_tracking/view/'.$trip['id']); ?>">View</a></td></tr>
<?php } ?>
</tbody></table></div>
</div></div></div></div>
<?php if (trim((string)$google_maps_api_key) !== '') { ?><script src="https://maps.googleapis.com/maps/api/js?key=<?php echo html_escape($google_maps_api_key); ?>"></script><?php } ?>
<?php init_tail(); ?>

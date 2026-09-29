<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-installer-tracking"><div class="row"><div class="col-md-8"><div class="panel_s"><div class="panel-body">
<div class="sit-header-actions"><h3 class="sit-page-title"><?php echo html_escape($title); ?> #<?php echo (int)$trip['id']; ?></h3><a class="btn btn-info btn-sm" href="<?php echo admin_url('smart_installer_tracking/live_map'); ?>">Show Live Map</a></div>
<div class="sit-trip-header"><div><strong><?php echo html_escape(trim(($trip['firstname'] ?? '').' '.($trip['lastname'] ?? ''))); ?></strong><br><span><?php echo html_escape($trip['client_name'] ?: ($trip['company'] ?? '')); ?></span><br><span><?php echo html_escape($trip['project_name'] ?? ''); ?></span></div><span class="label label-info"><?php echo html_escape(smart_installer_tracking_clean_text($trip['status'])); ?></span></div>
<p><strong>Destination:</strong> <?php echo html_escape($trip['destination_address'] ?? ''); ?></p>
<p><strong>ETA:</strong> <?php echo html_escape($trip['eta_text'] ?? 'Pending'); ?></p>
<p><strong>Public Tracking Link:</strong><br><input class="form-control sit-copy-input" readonly value="<?php echo html_escape($public_url); ?>"></p>
<div class="btn-group tw-mb-3">
<?php foreach (['accepted'=>'Accept','on_the_way'=>'On The Way','arrived'=>'Arrived','completed'=>'Completed','cancelled'=>'Cancelled'] as $status=>$label) { echo form_open(admin_url('smart_installer_tracking/update_status/'.$trip['id'].'/'.$status), ['class'=>'sit-inline-form']); echo '<button class="btn btn-default btn-sm" type="submit">'.html_escape($label).'</button>'; echo form_close(); } ?>
</div>
<div id="sit-location-panel" data-trip-id="<?php echo (int)$trip['id']; ?>"><button class="btn btn-success btn-sm" id="sit-start-location">Start Live Location</button><span class="sit-location-status">Location sharing is off.</span></div>
<div id="sit-single-live-map" class="sit-google-map mtop15" data-lat="<?php echo html_escape($trip['last_lat'] ?? ''); ?>" data-lng="<?php echo html_escape($trip['last_lng'] ?? ''); ?>" data-title="<?php echo html_escape(trim(($trip['firstname'] ?? '').' '.($trip['lastname'] ?? ''))); ?>"></div>
</div></div></div><div class="col-md-4"><div class="panel_s"><div class="panel-body"><h4>Last Location</h4><p>Latitude: <?php echo html_escape($trip['last_lat'] ?? 'Pending'); ?></p><p>Longitude: <?php echo html_escape($trip['last_lng'] ?? 'Pending'); ?></p><p>Updated: <?php echo html_escape($trip['last_location_at'] ?? 'Pending'); ?></p><a class="btn btn-default btn-sm" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?php echo rawurlencode(($trip['last_lat'] ?? '') . ',' . ($trip['last_lng'] ?? '')); ?>">Open In Google Maps</a></div></div></div></div></div></div>
<?php if (trim((string)$google_maps_api_key) !== '') { ?><script src="https://maps.googleapis.com/maps/api/js?key=<?php echo html_escape($google_maps_api_key); ?>"></script><?php } ?>
<?php init_tail(); ?>

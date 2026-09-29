<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo html_escape($title); ?></title><link href="<?php echo module_dir_url('smart_installer_tracking','assets/css/smart_installer_tracking.css'); ?>" rel="stylesheet"></head><body class="sit-client-page">
<div class="sit-client-card"><div class="sit-client-header"><div class="sit-photo-circle"><?php echo strtoupper(substr((string)($trip['firstname'] ?? 'I'),0,1)); ?></div><div><h2>Your installer is on the way</h2><p><?php echo html_escape(trim(($trip['firstname'] ?? '').' '.($trip['lastname'] ?? ''))); ?></p></div></div>
<div class="sit-client-status"><strong>Status:</strong> <span id="sit-client-status-text"><?php echo html_escape(smart_installer_tracking_clean_text($trip['status'])); ?></span></div>
<div class="sit-client-status"><strong>ETA:</strong> <span id="sit-client-eta"><?php echo html_escape($trip['eta_text'] ?: 'ETA pending'); ?></span></div>
<div class="sit-map-placeholder" id="sit-client-map" data-token="<?php echo html_escape($trip['tracking_token']); ?>" data-refresh="<?php echo (int)get_option('smart_installer_tracking_client_refresh_seconds'); ?>">Live location map</div>
<p class="sit-small">Last update: <span id="sit-client-last-update"><?php echo html_escape($trip['last_location_at'] ?: 'Pending'); ?></span></p>
<p class="sit-small">Smart Choice Contractors USA · <?php echo html_escape(get_option('smart_installer_tracking_company_phone')); ?></p></div>
<script src="<?php echo module_dir_url('smart_installer_tracking','assets/js/smart_installer_tracking.js'); ?>"></script>
</body></html>

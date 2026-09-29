<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="smart-installer-tracking settings-section">
<h3 class="sit-page-title">SMART INSTALLER TRACKING SETTINGS</h3>
<p class="text-muted">Configure live installer tracking, Google Maps, ETA notifications, client visibility, and network controls. The module can reuse the CRM Google Maps API key when it is already saved.</p>
<?php echo render_yes_no_option('smart_installer_tracking_enabled','Enable Installer Tracking'); ?>
<?php echo render_input('settings[smart_installer_tracking_google_maps_api_key]','Google Maps API Key', smart_installer_tracking_mask_api_key(get_option('smart_installer_tracking_google_maps_api_key'))); ?>
<p class="text-muted small">Leave blank to use the existing CRM Google Maps API key option when available.</p>
<?php echo render_yes_no_option('smart_installer_tracking_google_distance_api_enabled','Enable Google Distance Matrix'); ?>
<?php echo render_input('settings[smart_installer_tracking_default_eta_minutes]','Default ETA Minutes', get_option('smart_installer_tracking_default_eta_minutes'), 'number'); ?>
<?php echo render_input('settings[smart_installer_tracking_location_refresh_seconds]','Installer Location Refresh Seconds', get_option('smart_installer_tracking_location_refresh_seconds'), 'number'); ?>
<?php echo render_input('settings[smart_installer_tracking_client_refresh_seconds]','Client Map Refresh Seconds', get_option('smart_installer_tracking_client_refresh_seconds'), 'number'); ?>
<?php echo render_yes_no_option('smart_installer_tracking_allow_client_live_map','Allow Client Live Map'); ?>
<?php echo render_yes_no_option('smart_installer_tracking_notify_by_email','Notify Client By Email'); ?>
<?php echo render_yes_no_option('smart_installer_tracking_notify_by_sms','Notify Client By SMS'); ?>
<?php echo render_input('settings[smart_installer_tracking_company_phone]','Company Phone', get_option('smart_installer_tracking_company_phone')); ?>
<?php echo render_input('settings[smart_installer_tracking_public_link_hours]','Public Link Valid Hours', get_option('smart_installer_tracking_public_link_hours'), 'number'); ?>
<?php echo render_textarea('settings[smart_installer_tracking_ip_allowlist]','IP Allowlist', get_option('smart_installer_tracking_ip_allowlist'), ['placeholder'=>'One IP per line. Leave blank to allow all.']); ?>
<a class="btn btn-info btn-sm" href="<?php echo admin_url('smart_installer_tracking/health'); ?>">Open Health Checker</a>
<a class="btn btn-success btn-sm" href="<?php echo admin_url('smart_installer_tracking/live_map'); ?>">Show Live Map</a>
</div>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s google-meet-card"><div class="panel-body">
    <div class="google-meet-settings-heading">
        <h4><i class="fa fa-video-camera"></i> Google Meet Settings</h4>
        <p>Configure Google Meet creation, default invitations, notifications, and calendar options.</p>
    </div>

    <div class="checkbox checkbox-primary">
        <input type="checkbox" id="google_meet_enabled" name="settings[google_meet_enabled]" value="1" <?php echo get_option('google_meet_enabled') == '1' ? 'checked' : ''; ?>>
        <label for="google_meet_enabled">Activate Google Meet Module</label>
    </div>
    <div class="checkbox checkbox-primary">
        <input type="checkbox" id="google_meet_auto_create_link" name="settings[google_meet_auto_create_link]" value="1" <?php echo get_option('google_meet_auto_create_link') == '1' ? 'checked' : ''; ?>>
        <label for="google_meet_auto_create_link">Create Google Meet Link Automatically When Blank</label>
    </div>

    <?php echo render_input('settings[google_meet_google_api_key]', 'Google API Key', get_option('google_meet_google_api_key')); ?>
    <?php echo render_input('settings[google_meet_calendar_id]', 'Google Calendar ID', get_option('google_meet_calendar_id') ?: 'primary'); ?>
    <?php echo render_input('settings[google_meet_default_duration]', 'Default Duration Minutes', get_option('google_meet_default_duration') ?: '30', 'number'); ?>

    <div class="checkbox checkbox-primary">
        <input type="checkbox" id="google_meet_notify_staff_default" name="settings[google_meet_notify_staff_default]" value="1" <?php echo get_option('google_meet_notify_staff_default') == '1' ? 'checked' : ''; ?>>
        <label for="google_meet_notify_staff_default">Notify Staff by Default</label>
    </div>
    <div class="checkbox checkbox-primary">
        <input type="checkbox" id="google_meet_notify_customers_default" name="settings[google_meet_notify_customers_default]" value="1" <?php echo get_option('google_meet_notify_customers_default') == '1' ? 'checked' : ''; ?>>
        <label for="google_meet_notify_customers_default">Notify Customers by Default</label>
    </div>
    <div class="checkbox checkbox-primary">
        <input type="checkbox" id="google_meet_send_invitations_default" name="settings[google_meet_send_invitations_default]" value="1" <?php echo get_option('google_meet_send_invitations_default') == '1' ? 'checked' : ''; ?>>
        <label for="google_meet_send_invitations_default">Send Invitations Now by Default</label>
    </div>
</div></div>

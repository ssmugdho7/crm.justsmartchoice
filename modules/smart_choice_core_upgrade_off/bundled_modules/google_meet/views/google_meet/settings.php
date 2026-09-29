<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open(admin_url('google_meet/settings')); ?>
        <div class="panel_s google-meet-card">
            <div class="panel-body">
                <div class="google-meet-settings-heading">
                    <h4><i class="fa fa-video-camera"></i> Google Meet Settings</h4>
                    <p>Configure manual links, safe automatic fallback links, and optional Google Calendar API creation.</p>
                </div>

                <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_enabled" name="google_meet_enabled" value="1" <?php echo get_option('google_meet_enabled') == '1' ? 'checked' : ''; ?>><label for="google_meet_enabled">Activate Google Meet Module</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_auto_create_link" name="google_meet_auto_create_link" value="1" <?php echo get_option('google_meet_auto_create_link') == '1' ? 'checked' : ''; ?>><label for="google_meet_auto_create_link">Create link automatically when blank</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_use_google_calendar_api" name="google_meet_use_google_calendar_api" value="1" <?php echo get_option('google_meet_use_google_calendar_api') == '1' ? 'checked' : ''; ?>><label for="google_meet_use_google_calendar_api">Use Google Calendar API for real Meet links</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_allow_placeholder_links" name="google_meet_allow_placeholder_links" value="1" <?php echo get_option('google_meet_allow_placeholder_links') == '1' ? 'checked' : ''; ?>><label for="google_meet_allow_placeholder_links">Use safe placeholder link if API is unavailable</label></div>

                <hr>
                <?php echo render_input('google_meet_google_api_key', 'Google API Key', get_option('google_meet_google_api_key')); ?>
                <?php echo render_input('google_meet_google_access_token', 'Google OAuth Access Token', get_option('google_meet_google_access_token')); ?>
                <?php echo render_input('google_meet_calendar_id', 'Google Calendar ID', get_option('google_meet_calendar_id') ?: 'primary'); ?>
                <?php echo render_input('google_meet_timezone', 'Timezone', get_option('google_meet_timezone') ?: 'America/New_York'); ?>
                <?php echo render_input('google_meet_default_duration', 'Default Duration Minutes', get_option('google_meet_default_duration') ?: '30', 'number'); ?>

                <hr>
                <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_notify_staff_default" name="google_meet_notify_staff_default" value="1" <?php echo get_option('google_meet_notify_staff_default') == '1' ? 'checked' : ''; ?>><label for="google_meet_notify_staff_default">Notify Staff by Default</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_notify_customers_default" name="google_meet_notify_customers_default" value="1" <?php echo get_option('google_meet_notify_customers_default') == '1' ? 'checked' : ''; ?>><label for="google_meet_notify_customers_default">Notify Customers by Default</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_send_invitations_default" name="google_meet_send_invitations_default" value="1" <?php echo get_option('google_meet_send_invitations_default') == '1' ? 'checked' : ''; ?>><label for="google_meet_send_invitations_default">Send Invitations Now by Default</label></div>

                <div class="alert alert-info">If the API is not fully connected, the module will still create the meeting record and use <strong>https://meet.google.com/new</strong>. This prevents the blank automatic meeting workflow from failing.</div>

                <button type="submit" class="btn btn-primary">Save Google Meet Settings</button>
                <a href="<?php echo admin_url('google_meet'); ?>" class="btn btn-default">Back</a>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>

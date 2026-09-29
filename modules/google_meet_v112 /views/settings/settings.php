<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open(admin_url('google_meet/save_settings')); ?>
<div class="google-meet-wrap">
  <div class="gm-settings-card">
    <h4>Google Meet Settings</h4>
    <p>This module can create an automatic Google Meet link from inside the CRM when Google Calendar API access is enabled. If not configured, the module still creates and tracks meetings with a manual or fallback Meet link.</p>
    <?php echo render_yes_no_option('google_meet_use_google_calendar_api', 'Create Google Meet links automatically with Google Calendar API'); ?>
    <?php echo render_input('google_meet_google_access_token','Google OAuth Access Token',get_option('google_meet_google_access_token'),'password'); ?>
    <?php echo render_input('google_meet_google_api_key','Google API Key / Project Key',get_option('google_meet_google_api_key')); ?>
    <?php echo render_input('google_meet_calendar_id','Google Calendar ID',get_option('google_meet_calendar_id')); ?>
    <?php echo render_input('google_meet_timezone','Timezone',get_option('google_meet_timezone')); ?>
    <?php echo render_input('google_meet_default_duration','Default Meeting Duration Minutes',get_option('google_meet_default_duration'),'number'); ?>
    <?php echo render_input('google_meet_notify_minutes_before','Reminder Minutes Before Meeting',get_option('google_meet_notify_minutes_before'),'number'); ?>
    <?php echo render_input('google_meet_company_name','Company Name',get_option('google_meet_company_name')); ?>
    <div class="alert alert-info mtop15">For full automatic Meet creation, connect a Google Cloud project with Calendar API enabled and provide an OAuth access token for the calendar account. The module will create a Calendar event with conferenceData and return the Meet link directly in Perfex.</div>
    <button class="btn btn-info" type="submit">Save Settings</button>
  </div>
</div>
<?php echo form_close(); ?>

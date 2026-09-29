<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="google-meet-wrap smart-choice-normalized-module">
  <div class="google-meet-header"><div><h1><i class="fa fa-cog"></i> Google Meet Settings</h1><p>Control Google Calendar, customer portal, staff/customer invitations, CRM popups, email, Twilio SMS bridge, and AI-ready meeting notes.</p></div></div>
  <?php $this->load->view('google_meet/_nav'); ?>
  <?php echo form_open(admin_url('google_meet/settings')); ?>
  <div class="gm-instruction-banner">
    <h3><i class="fa fa-info-circle"></i> How Google Meet works in the CRM</h3>
    <p>Create or schedule a meeting from this module, assign the appropriate staff members and customer contacts, and save the record. Logged-in customers only see meetings specifically assigned to their own contact account. The customer navigation link is hidden from visitors and from anyone who is not authenticated.</p>
    <ul>
      <li><strong>Google Calendar API enabled:</strong> the module can request a real Google Calendar event and Google Meet conference link using the saved Google credentials.</li>
      <li><strong>Notifications enabled:</strong> assigned participants can receive CRM, email, popup, or available SMS notifications according to these settings.</li>
      <li><strong>No assigned meeting:</strong> the customer portal clearly reports that no meeting is currently scheduled instead of exposing an empty or public page.</li>
    </ul>
  </div>
  <div class="panel_s google-meet-card"><div class="panel-body gm-form-panel">
    <div class="google-meet-settings-heading"><h4>Core Setup</h4><p>Keep the module active and configure Google Calendar API fallback behavior.</p></div>
    <div class="row"><div class="col-md-6">
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_enabled" name="google_meet_enabled" value="1" <?php echo get_option('google_meet_enabled') == '1' ? 'checked' : ''; ?>><label for="google_meet_enabled">Enable Google Meet Module</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_auto_create_link" name="google_meet_auto_create_link" value="1" <?php echo get_option('google_meet_auto_create_link') == '1' ? 'checked' : ''; ?>><label for="google_meet_auto_create_link">Auto Create Meet Link When Possible</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_use_google_calendar_api" name="google_meet_use_google_calendar_api" value="1" <?php echo get_option('google_meet_use_google_calendar_api') == '1' ? 'checked' : ''; ?>><label for="google_meet_use_google_calendar_api">Use Google Calendar API</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_allow_placeholder_links" name="google_meet_allow_placeholder_links" value="1" <?php echo get_option('google_meet_allow_placeholder_links') == '1' ? 'checked' : ''; ?>><label for="google_meet_allow_placeholder_links">Allow Safe Placeholder Link</label></div>
    </div><div class="col-md-6">
      <?php echo render_input('google_meet_calendar_id', 'Google Calendar ID', get_option('google_meet_calendar_id') ?: 'primary'); ?>
      <?php echo render_input('google_meet_timezone', 'Timezone', get_option('google_meet_timezone') ?: 'America/New_York'); ?>
      <?php echo render_input('google_meet_default_duration', 'Default Duration Minutes', get_option('google_meet_default_duration') ?: '30', 'number'); ?>
    </div></div>

    <hr><div class="google-meet-settings-heading"><h4>Google API Credentials</h4><p>Google Meet links are created through Google Calendar events with conference data.</p></div>
    <div class="row"><div class="col-md-6"><?php echo render_input('google_meet_google_api_key','Google API Key / Project Key',get_option('google_meet_google_api_key')); ?></div><div class="col-md-6"><?php echo render_input('google_meet_google_access_token','Google OAuth Access Token',get_option('google_meet_google_access_token'),'password'); ?></div></div>

    <hr><div class="google-meet-settings-heading"><h4>Meeting Options</h4><p>These preferences are stored in the CRM and included in meeting records. Some final enforcement is controlled by Google Workspace account permissions.</p></div>
    <div class="row"><div class="col-md-6 gm-checkbox-grid">
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_quick_access" name="google_meet_quick_access" value="1" <?php echo get_option('google_meet_quick_access') == '1' ? 'checked' : ''; ?>><label for="google_meet_quick_access">Quick Access Preference</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_waiting_room" name="google_meet_waiting_room" value="1" <?php echo get_option('google_meet_waiting_room') == '1' ? 'checked' : ''; ?>><label for="google_meet_waiting_room">Waiting Room Preference</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_allow_chat" name="google_meet_allow_chat" value="1" <?php echo get_option('google_meet_allow_chat') == '1' ? 'checked' : ''; ?>><label for="google_meet_allow_chat">Allow Chat Preference</label></div>
    </div><div class="col-md-6 gm-checkbox-grid">
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_allow_screen_sharing" name="google_meet_allow_screen_sharing" value="1" <?php echo get_option('google_meet_allow_screen_sharing') == '1' ? 'checked' : ''; ?>><label for="google_meet_allow_screen_sharing">Allow Screen Sharing Preference</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_allow_recording" name="google_meet_allow_recording" value="1" <?php echo get_option('google_meet_allow_recording') == '1' ? 'checked' : ''; ?>><label for="google_meet_allow_recording">Allow Recording Preference</label></div>
      <?php echo render_select('google_meet_access_mode', [['id'=>'trusted','name'=>'Trusted Access'],['id'=>'restricted','name'=>'Restricted Access'],['id'=>'open','name'=>'Open Access']], ['id','name'], 'Access Mode', get_option('google_meet_access_mode') ?: 'trusted'); ?>
    </div></div>
    <?php echo render_textarea('google_meet_recording_instruction', 'Recording Instructions', get_option('google_meet_recording_instruction'), ['rows'=>3]); ?>

    <hr><div class="google-meet-settings-heading"><h4>Notifications</h4><p>Send meeting invitations through CRM email, CRM screen notifications, and Twilio SMS bridge when available.</p></div>
    <div class="row"><div class="col-md-6 gm-checkbox-grid">
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_notify_staff_default" name="google_meet_notify_staff_default" value="1" <?php echo get_option('google_meet_notify_staff_default') == '1' ? 'checked' : ''; ?>><label for="google_meet_notify_staff_default">Notify Staff by Default</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_notify_customers_default" name="google_meet_notify_customers_default" value="1" <?php echo get_option('google_meet_notify_customers_default') == '1' ? 'checked' : ''; ?>><label for="google_meet_notify_customers_default">Notify Customers by Default</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_send_invitations_default" name="google_meet_send_invitations_default" value="1" <?php echo get_option('google_meet_send_invitations_default') == '1' ? 'checked' : ''; ?>><label for="google_meet_send_invitations_default">Send Invitations Immediately</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_email_enabled" name="google_meet_email_enabled" value="1" <?php echo get_option('google_meet_email_enabled') == '1' ? 'checked' : ''; ?>><label for="google_meet_email_enabled">Email Notifications</label></div>
    </div><div class="col-md-6 gm-checkbox-grid">
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_push_enabled" name="google_meet_push_enabled" value="1" <?php echo get_option('google_meet_push_enabled') == '1' ? 'checked' : ''; ?>><label for="google_meet_push_enabled">CRM Screen Notifications</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_popup_enabled" name="google_meet_popup_enabled" value="1" <?php echo get_option('google_meet_popup_enabled') == '1' ? 'checked' : ''; ?>><label for="google_meet_popup_enabled">Popup Notifications</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_twilio_enabled" name="google_meet_twilio_enabled" value="1" <?php echo get_option('google_meet_twilio_enabled') == '1' ? 'checked' : ''; ?>><label for="google_meet_twilio_enabled">Twilio SMS Bridge</label></div>
      <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_browser_sound_enabled" name="google_meet_browser_sound_enabled" value="1" <?php echo get_option('google_meet_browser_sound_enabled') == '1' ? 'checked' : ''; ?>><label for="google_meet_browser_sound_enabled">Browser Sound Alert</label></div>
    </div></div>
    <?php echo render_input('google_meet_sound_volume', 'Sound Volume 0.1 to 1.0', get_option('google_meet_sound_volume') ?: '0.85', 'number', ['step'=>'0.05','min'=>'0.1','max'=>'1']); ?>

    <hr><div class="google-meet-settings-heading"><h4>AI Meeting Support</h4><p>AI summary settings are stored here for the current or future SAMI/AI meeting workflow. This module does not modify SAMI AI.</p></div>
    <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_ai_notes_enabled" name="google_meet_ai_notes_enabled" value="1" <?php echo get_option('google_meet_ai_notes_enabled') == '1' ? 'checked' : ''; ?>><label for="google_meet_ai_notes_enabled">Enable AI Meeting Notes Preference</label></div>
    <?php echo render_textarea('google_meet_ai_summary_prompt', 'AI Summary Prompt', get_option('google_meet_ai_summary_prompt'), ['rows'=>3]); ?>

    <hr><div class="google-meet-settings-heading"><h4>Customer Portal</h4><p>This link is available only after a customer contact successfully signs in. Each contact can view only meetings assigned to that contact record. Visitors and logged-out users are redirected to the standard CRM customer login and cannot access meeting data.</p></div>
    <div class="checkbox checkbox-primary"><input type="checkbox" id="google_meet_client_portal_enabled" name="google_meet_client_portal_enabled" value="1" <?php echo get_option('google_meet_client_portal_enabled') == '1' ? 'checked' : ''; ?>><label for="google_meet_client_portal_enabled">Show Google Meet in Customer Portal</label></div>
    <p><strong>Client Link:</strong> <a href="<?php echo site_url('google-meet-client'); ?>"><?php echo site_url('google-meet-client'); ?></a></p>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save Settings</button>
    <a href="<?php echo admin_url('google_meet'); ?>" class="btn btn-default btn-sm">Back</a>
  </div></div><?php echo form_close(); ?>
</div></div></div><?php init_tail(); ?>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="google-meet-wrap smart-choice-normalized-module gm-help">
  <div class="google-meet-header"><div><h1><i class="fa fa-question-circle"></i> Google Meet Help</h1><p>Smart Choice guide for meetings, employee notifications, customer portal links, Twilio SMS, Google API, and recording notes.</p></div></div>
  <?php $this->load->view('google_meet/_nav'); ?>
  <div class="gm-help-grid">
    <div class="gm-help-card"><h3><span>1</span> Basic Workflow</h3><p>Create a new meeting, select employees and customers, choose whether to send invitations immediately, then save. The module stores the meeting, attendees, Meet link, and notification log.</p></div>
    <div class="gm-help-card"><h3><span>2</span> Google Meet Link Creation</h3><p>If Google Calendar API is enabled and a valid OAuth access token exists, the module requests a Google Calendar event with conference data. If API setup is incomplete, the module saves a safe fallback link so the meeting still works.</p></div>
    <div class="gm-help-card"><h3><span>3</span> Employee Notifications</h3><p><strong>Email</strong> uses the CRM email library. <strong>CRM Popup</strong> creates staff notifications. <strong>Twilio/SMS</strong> uses available CRM SMS infrastructure and fires a hook for Twilio modules.</p></div>
    <div class="gm-help-card"><h3><span>4</span> Customer Portal</h3><p>Customers can open <strong><?php echo site_url('google-meet-client'); ?></strong> to view meetings assigned to their contact record and join from the portal.</p></div>
    <div class="gm-help-card"><h3><span>5</span> Recording and AI Notes</h3><p>Google Meet recording is controlled by Google Workspace permissions. AI summary preferences are stored for future SAMI/AI workflow integration without modifying SAMI AI.</p></div>
    <div class="gm-help-card"><h3><span>6</span> Testing and Troubleshooting</h3><p>Run <strong>Health</strong>, click <strong>Fix Issues</strong>, then use <strong>Test Notifications</strong> to verify email, SMS, and CRM popup delivery before sending a real meeting invitation.</p></div>
  </div>
</div></div></div>
<?php init_tail(); ?>

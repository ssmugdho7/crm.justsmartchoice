<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="google-meet-wrap smart-choice-normalized-module">
  <div class="google-meet-header"><div><h1><i class="fa fa-bell"></i> Google Meet Test Notifications</h1><p>Test CRM popup notifications, CRM email, and Twilio/SMS bridge before sending real meeting invitations.</p></div></div>
  <?php $this->load->view('google_meet/_nav'); ?>
  <div class="panel_s google-meet-card"><div class="panel-body">
    <?php echo form_open(admin_url('google_meet/send_test_notifications')); ?>
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="staff_id">CRM Screen Notification Staff</label>
            <select name="staff_id" id="staff_id" class="selectpicker" data-width="100%" data-live-search="true">
              <option value="">No Staff Notification</option>
              <?php foreach((array)$staff as $s){ $sid=(int)$s['staffid']; ?>
                <option value="<?php echo $sid; ?>"><?php echo html_escape(trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? ''))); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="col-md-4"><?php echo render_input('email', 'Test Email Address', '', 'email', ['placeholder'=>'name@example.com']); ?></div>
        <div class="col-md-4"><?php echo render_input('phone', 'Test SMS Phone Number', '', 'text', ['placeholder'=>'+17275553786']); ?></div>
      </div>
      <?php echo render_input('meet_link', 'Test Meet Link', 'https://meet.google.com/new', 'url'); ?>
      <?php echo render_textarea('message', 'Test Message', 'This is a Smart Choice Google Meet notification test. Please confirm that email, SMS, and CRM popup notifications are working.', ['rows'=>4]); ?>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-paper-plane"></i> Send Test</button>
      <a href="<?php echo admin_url('google_meet/settings'); ?>" class="btn btn-default btn-sm"><i class="fa fa-cog"></i> Settings</a>
    <?php echo form_close(); ?>
  </div></div>
</div></div></div>
<?php init_tail(); ?>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$meeting = isset($meeting) ? $meeting : null;
$attendees = isset($attendees) && is_array($attendees) ? $attendees : [];
$selectedStaff = [];
$selectedContacts = [];
foreach ($attendees as $a) {
    if (!empty($a['staff_id'])) { $selectedStaff[] = (int)$a['staff_id']; }
    if (!empty($a['contact_id'])) { $selectedContacts[] = (int)$a['contact_id']; }
}
$start = $meeting && !empty($meeting->start_time) ? google_meet_form_datetime($meeting->start_time) : date('Y-m-d\TH:i');
$duration = $meeting->duration_minutes ?? (get_option('google_meet_default_duration') ?: 30);
$end = $meeting && !empty($meeting->end_time) ? google_meet_form_datetime($meeting->end_time) : date('Y-m-d\TH:i', strtotime('+' . (int)$duration . ' minutes'));
$subject = $meeting->subject ?? $meeting->title ?? '';
?>
<div id="wrapper">
  <div class="content">
    <div class="google-meet-wrap">
      <div class="gm-hero">
        <div>
          <h1><?php echo html_escape($title); ?></h1>
          <p>Leave the link blank to generate one private Jitsi room automatically. Every attendee receives the same saved room link. Previously saved external meeting links remain available.</p>
        </div>
      </div>

      <?php $this->load->view('google_meet/_nav'); ?>

      <?php echo form_open(admin_url('google_meet/create' . ($meeting ? '/' . (int)$meeting->id : ''))); ?>
      <div class="panel_s gm-form-panel">
        <div class="panel-body">
          <div class="row">
            <div class="col-md-8">
              <?php echo render_input('subject', 'Meeting Topic / Name', $subject, 'text', ['placeholder' => 'Project meeting, estimate review, customer call']); ?>
              <?php echo render_textarea('description', 'Meeting Description', $meeting->description ?? '', ['rows' => 4]); ?>
              <div class="row">
                <div class="col-md-6"><?php echo render_input('start_time', 'Start Time', $start, 'datetime-local'); ?></div>
                <div class="col-md-6"><?php echo render_input('end_time', 'End Time', $end, 'datetime-local'); ?></div>
              </div>
              <?php echo render_input('duration_minutes', 'Duration Minutes', $duration, 'number'); ?>

              <div class="form-group">
                <label for="assigned_staff_id">Responsible Employee</label>
                <select name="assigned_staff_id" id="assigned_staff_id" class="selectpicker google-meet-selectpicker" data-width="100%" data-live-search="true">
                  <option value=""></option>
                  <?php foreach ((array)$staff as $s) { $sid = (int)$s['staffid']; ?>
                    <option value="<?php echo $sid; ?>" <?php echo ((int)($meeting->assigned_staff_id ?? get_staff_user_id()) === $sid) ? 'selected' : ''; ?>><?php echo html_escape(trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? ''))); ?></option>
                  <?php } ?>
                </select>
              </div>

              <div class="form-group">
                <label for="project_id">Project</label>
                <select name="project_id" id="project_id" class="selectpicker google-meet-selectpicker" data-width="100%" data-live-search="true">
                  <option value=""></option>
                  <?php foreach ((array)$projects as $p) { $pid = (int)$p['id']; $pname = $p['name'] ?? ('Project #' . $pid); ?>
                    <option value="<?php echo $pid; ?>" <?php echo ((int)($meeting->project_id ?? 0) === $pid) ? 'selected' : ''; ?>><?php echo html_escape($pname); ?></option>
                  <?php } ?>
                </select>
              </div>
              <?php echo render_input('appointment_id', 'Appointment ID', $meeting->appointment_id ?? '', 'number'); ?>
              <?php echo render_input('meet_link', 'Shared Video Meeting Link', $meeting->meet_link ?? '', 'text', ['placeholder' => 'Leave blank for an automatic Jitsi room']); ?>
              <?php echo render_textarea('notes', 'Internal Notes', $meeting->notes ?? '', ['rows' => 3]); ?>
            </div>
            <div class="col-md-4">
              <div class="gm-side-card">
                <h4>Meeting Controls</h4>
                <div class="form-group">
                  <label for="meeting_type">Meeting Type</label>
                  <select name="meeting_type" id="meeting_type" class="form-control">
                    <option value="instant" <?php echo (($meeting->meeting_type ?? '') === 'instant') ? 'selected' : ''; ?>>Instant</option>
                    <option value="scheduled" <?php echo (($meeting->meeting_type ?? 'scheduled') === 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="access_mode">Meeting Access</label>
                  <select name="access_mode" id="access_mode" class="form-control">
                    <?php $accessMode = $meeting->access_mode ?? get_option('google_meet_access_mode') ?: 'trusted'; ?>
                    <option value="trusted" <?php echo $accessMode === 'trusted' ? 'selected' : ''; ?>>Trusted users can join</option>
                    <option value="ask_to_join" <?php echo $accessMode === 'ask_to_join' ? 'selected' : ''; ?>>Guests ask to join</option>
                    <option value="restricted" <?php echo $accessMode === 'restricted' ? 'selected' : ''; ?>>Restricted / host approval</option>
                  </select>
                  <small class="text-muted">The video provider controls its lobby and recording features. CRM access and participant permissions remain enforced separately.</small>
                </div>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="quick_access" id="quick_access" value="1" <?php echo (($meeting->quick_access ?? get_option('google_meet_quick_access')) == '1') ? 'checked' : ''; ?>><label for="quick_access">Allow quick access when supported</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="waiting_room" id="waiting_room" value="1" <?php echo (($meeting->waiting_room ?? get_option('google_meet_waiting_room')) == '1') ? 'checked' : ''; ?>><label for="waiting_room">Use waiting room / ask to join when supported</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="allow_chat" id="allow_chat" value="1" <?php echo (($meeting->allow_chat ?? get_option('google_meet_allow_chat')) == '1') ? 'checked' : ''; ?>><label for="allow_chat">Allow meeting chat when supported</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="allow_screen_sharing" id="allow_screen_sharing" value="1" <?php echo (($meeting->allow_screen_sharing ?? get_option('google_meet_allow_screen_sharing')) == '1') ? 'checked' : ''; ?>><label for="allow_screen_sharing">Allow screen sharing when supported</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="allow_recording" id="allow_recording" value="1" <?php echo (($meeting->allow_recording ?? get_option('google_meet_allow_recording')) == '1') ? 'checked' : ''; ?>><label for="allow_recording">Allow recording preference when supported</label></div>
                <label>Staff Attendees</label>
                <select name="staff_ids[]" id="google_meet_staff_ids" class="selectpicker google-meet-selectpicker" multiple data-width="100%" data-live-search="true">
                  <?php foreach ((array)$staff as $s) { $sid = (int)$s['staffid']; ?>
                    <option value="<?php echo $sid; ?>" <?php echo in_array($sid, $selectedStaff, true) ? 'selected' : ''; ?>><?php echo html_escape(trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? ''))); ?></option>
                  <?php } ?>
                </select>
                <br><br>
                <label>Customer Contacts</label>
                <select name="contact_ids[]" id="google_meet_contact_ids" class="selectpicker google-meet-selectpicker" multiple data-width="100%" data-live-search="true">
                  <?php foreach ((array)$contacts as $c) { $cid = (int)$c['id']; ?>
                    <option value="<?php echo $cid; ?>" <?php echo in_array($cid, $selectedContacts, true) ? 'selected' : ''; ?>><?php echo html_escape(trim(($c['firstname'] ?? '') . ' ' . ($c['lastname'] ?? '') . ' - ' . ($c['email'] ?? ''))); ?></option>
                  <?php } ?>
                </select>
                <hr>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="notify_staff" id="notify_staff" value="1" <?php echo get_option('google_meet_notify_staff_default') == '1' ? 'checked' : ''; ?>><label for="notify_staff">Notify Staff</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="notify_customer" id="notify_customer" value="1" <?php echo get_option('google_meet_notify_customers_default') == '1' ? 'checked' : ''; ?>><label for="notify_customer">Notify Customers</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="send_notifications" id="send_notifications" value="1"><label for="send_notifications">Send invitations now</label></div>
                <div class="alert alert-info">A shared Jitsi room is saved automatically before invitations are sent. No video-provider API keys or OAuth tokens are needed by this CRM.</div>
                <button class="btn btn-info btn-block" type="submit">Save Meeting</button>
                <a class="btn btn-default btn-block" href="<?php echo admin_url('google_meet'); ?>">Cancel</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php echo form_close(); ?>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script>
(function($){
    'use strict';
    function initGoogleMeetSelects(){
        if (!$ || !$.fn) { return; }
        var $selects = $('.google-meet-selectpicker');
        $selects.each(function(){
            var $select = $(this);
            try {
                if ($.fn.selectpicker) {
                    if (!$select.parent().hasClass('bootstrap-select')) {
                        $select.selectpicker({liveSearch:true, width:'100%'});
                    } else {
                        $select.selectpicker('refresh');
                    }
                    $select.selectpicker('render');
                }
            } catch (e) {
                // Never leave a native select hidden if another CRM asset
                // prevents bootstrap-select from initializing.
                $select.css({display:'block', width:'100%', visibility:'visible', opacity:1});
                if (window.console && console.warn) {
                    console.warn('Video Meeting selector fallback:', e);
                }
            }
        });
    }
    $(initGoogleMeetSelects);
    $(window).on('load', initGoogleMeetSelects);
    setTimeout(initGoogleMeetSelects, 250);
})(window.jQuery);
</script>

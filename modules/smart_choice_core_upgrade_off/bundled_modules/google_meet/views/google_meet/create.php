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
$start = $meeting && !empty($meeting->start_time) ? date('Y-m-d H:i', strtotime($meeting->start_time)) : date('Y-m-d H:i');
$duration = $meeting->duration_minutes ?? (get_option('google_meet_default_duration') ?: 30);
$end = $meeting && !empty($meeting->end_time) ? date('Y-m-d H:i', strtotime($meeting->end_time)) : date('Y-m-d H:i', strtotime('+' . (int)$duration . ' minutes'));
$subject = $meeting->subject ?? $meeting->title ?? '';
?>
<div id="wrapper">
  <div class="content">
    <div class="google-meet-wrap">
      <div class="gm-hero">
        <div>
          <h1><?php echo html_escape($title); ?></h1>
          <p>Leave the Google Meet link blank to create it automatically. If the Google API is not connected, the module will save a safe placeholder link instead of failing.</p>
        </div>
        <a href="<?php echo admin_url('google_meet'); ?>" class="btn btn-default">Dashboard</a>
      </div>

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
                <select name="assigned_staff_id" id="assigned_staff_id" class="selectpicker" data-width="100%" data-live-search="true">
                  <option value=""></option>
                  <?php foreach ((array)$staff as $s) { $sid = (int)$s['staffid']; ?>
                    <option value="<?php echo $sid; ?>" <?php echo ((int)($meeting->assigned_staff_id ?? get_staff_user_id()) === $sid) ? 'selected' : ''; ?>><?php echo html_escape(trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? ''))); ?></option>
                  <?php } ?>
                </select>
              </div>

              <div class="form-group">
                <label for="project_id">Project</label>
                <select name="project_id" id="project_id" class="selectpicker" data-width="100%" data-live-search="true">
                  <option value=""></option>
                  <?php foreach ((array)$projects as $p) { $pid = (int)$p['id']; $pname = $p['name'] ?? ('Project #' . $pid); ?>
                    <option value="<?php echo $pid; ?>" <?php echo ((int)($meeting->project_id ?? 0) === $pid) ? 'selected' : ''; ?>><?php echo html_escape($pname); ?></option>
                  <?php } ?>
                </select>
              </div>
              <?php echo render_input('appointment_id', 'Appointment ID', $meeting->appointment_id ?? '', 'number'); ?>
              <?php echo render_input('meet_link', 'Google Meet Link', $meeting->meet_link ?? '', 'text', ['placeholder' => 'Leave blank for automatic Google Meet link']); ?>
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
                <label>Staff Attendees</label>
                <select name="staff_ids[]" class="selectpicker" multiple data-width="100%" data-live-search="true">
                  <?php foreach ((array)$staff as $s) { $sid = (int)$s['staffid']; ?>
                    <option value="<?php echo $sid; ?>" <?php echo in_array($sid, $selectedStaff, true) ? 'selected' : ''; ?>><?php echo html_escape(trim(($s['firstname'] ?? '') . ' ' . ($s['lastname'] ?? ''))); ?></option>
                  <?php } ?>
                </select>
                <br><br>
                <label>Customer Contacts</label>
                <select name="contact_ids[]" class="selectpicker" multiple data-width="100%" data-live-search="true">
                  <?php foreach ((array)$contacts as $c) { $cid = (int)$c['id']; ?>
                    <option value="<?php echo $cid; ?>" <?php echo in_array($cid, $selectedContacts, true) ? 'selected' : ''; ?>><?php echo html_escape(trim(($c['firstname'] ?? '') . ' ' . ($c['lastname'] ?? '') . ' - ' . ($c['email'] ?? ''))); ?></option>
                  <?php } ?>
                </select>
                <hr>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="notify_staff" id="notify_staff" value="1" <?php echo get_option('google_meet_notify_staff_default') == '1' ? 'checked' : ''; ?>><label for="notify_staff">Notify Staff</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="notify_customer" id="notify_customer" value="1" <?php echo get_option('google_meet_notify_customers_default') == '1' ? 'checked' : ''; ?>><label for="notify_customer">Notify Customers</label></div>
                <div class="checkbox checkbox-primary"><input type="checkbox" name="send_notifications" id="send_notifications" value="1"><label for="send_notifications">Send invitations now</label></div>
                <div class="alert alert-info">Automatic links require Google Calendar API + access token. If not connected, the module saves <strong>https://meet.google.com/new</strong> so the meeting still creates.</div>
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

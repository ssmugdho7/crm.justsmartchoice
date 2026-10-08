<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$hasRoomLink = $this->google_meet_model->is_real_meet_link($meeting->meet_link ?? '', $meeting);
$subject = $meeting->subject ?? $meeting->title ?? 'Video Meeting';
?>
<div id="wrapper">
  <div class="content">
    <div class="google-meet-wrap">
      <div class="gm-hero">
        <div>
          <h1><?php echo html_escape($subject); ?></h1>
          <p><?php echo html_escape($meeting->description ?? ''); ?></p>
        </div>
        <?php if ($hasRoomLink) { ?><a class="btn btn-success btn-sm"  href="<?php echo admin_url('google_meet/room/'.(int)$meeting->id); ?>">Join meeting</a><?php } else { ?><span class="label label-default">Meeting link pending</span><?php } ?>
      </div>

      <?php $this->load->view('google_meet/_nav'); ?>

      <div class="panel_s google-meet-card">
        <div class="panel-body">
          <div class="row">
            <div class="col-md-8">
              <h4>Meeting Details</h4>
              <p><b>Start:</b> <?php echo !empty($meeting->start_time) ? google_meet_display_datetime($meeting->start_time) : ''; ?></p>
              <p><b>End:</b> <?php echo !empty($meeting->end_time) ? google_meet_display_datetime($meeting->end_time) : ''; ?></p>
              <p><b>Status:</b> <?php echo html_escape($meeting->status ?? ''); ?></p>
              <p><b>Duration:</b> <?php echo (int)($meeting->duration_minutes ?? 0); ?> minutes</p>
              <p><b>Link status:</b> <?php echo html_escape(($meeting->provider ?? '') === 'jitsi' ? 'Shared Jitsi room ready' : ($hasRoomLink ? 'Existing meeting link' : 'Meeting link pending')); ?></p>
              <p><b>Link:</b> <a  href="<?php echo html_escape($meeting->meet_link); ?>"><?php echo html_escape($meeting->meet_link); ?></a></p>
              <?php if (!empty($meeting->notes)) { ?><p><b>Internal Notes:</b><br><?php echo nl2br(html_escape($meeting->notes)); ?></p><?php } ?>
            </div>
            <div class="col-md-4">
              <button class="btn btn-info btn-block" type="button" data-toggle="modal" data-target="#shareMeetingModal" <?= $hasRoomLink ? '' : 'disabled'; ?>>Share meeting</button>
              <?php if (has_permission('google_meet', '', 'delete')) { echo form_open(admin_url('google_meet/delete/' . (int)$meeting->id), ['onsubmit' => "return confirm('Delete this meeting and all its attendees, notes and notification history?');", 'class' => 'mtop10']); ?><button class="btn btn-danger btn-block" type="submit">Delete meeting</button><?= form_close(); } ?>

              <?php if (has_permission('google_meet', '', 'edit')) { ?>
              <a class="btn btn-info btn-block" href="<?php echo admin_url('google_meet/start/'.$meeting->id); ?>">Mark Started</a>
              <a class="btn btn-success btn-block" href="<?php echo admin_url('google_meet/finish/'.$meeting->id); ?>">Mark Completed</a>
              <a class="btn btn-warning btn-block" href="<?php echo admin_url('google_meet/notify/'.$meeting->id); ?>">Send Notifications</a>
              <a class="btn btn-default btn-block" href="<?php echo admin_url('google_meet/create/'.$meeting->id); ?>">Edit</a>
              <?php } ?>
              <a class="btn btn-default btn-block" href="<?php echo admin_url('google_meet/reports'); ?>">Reports</a>
            </div>
          </div>

          <hr>
          <h4>Attendees</h4>
          <div class="table-responsive">
            <table class="table table-striped gm-compact-table">
              <thead><tr><th>Name</th><th>Email</th><th>Type</th><th>Notified</th></tr></thead>
              <tbody>
              <?php foreach((array)$attendees as $a){ ?>
                <tr><td><?php echo html_escape($a['name']); ?></td><td><?php echo html_escape($a['email']); ?></td><td><?php echo html_escape($a['attendee_type']); ?></td><td><?php echo !empty($a['notified']) ? 'Yes' : 'No'; ?></td></tr>
              <?php } ?>
              </tbody>
            </table>
          </div>

          <hr>
          <h4>Meeting Comments</h4>
          <?php echo form_open(admin_url('google_meet/add_comment/' . (int)$meeting->id)); ?>
            <?php echo render_textarea('comment', 'Add Comment / Meeting Note', '', ['rows' => 3]); ?>
            <button type="submit" class="btn btn-primary">Add Comment</button>
          <?php echo form_close(); ?>

          <div class="gm-comments mtop20">
            <?php foreach ((array)$comments as $comment) { ?>
              <div class="gm-comment-box">
                <strong><?php echo html_escape($comment['staff_name'] ?: 'Staff'); ?></strong>
                <small><?php echo !empty($comment['created_at']) ? google_meet_display_datetime($comment['created_at']) : ''; ?></small>
                <p><?php echo nl2br(html_escape($comment['comment'])); ?></p>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="panel_s google-meet-card"><div class="panel-body">
<h4><i class="fa fa-link"></i> Shared Meeting Room</h4>
<?php if (empty($meeting->meet_link) || rtrim($meeting->meet_link,'/') === 'https://meet.google.com/new') { ?>
<div class="alert alert-warning">No shared room has been saved. Generate a shared Jitsi room before inviting attendees so everyone joins the same room.</div>
<?php if (has_permission('google_meet', '', 'edit')) { ?>
<?php echo form_open(admin_url('google_meet/generate_room/' . (int)$meeting->id)); ?><button class="btn btn-info btn-sm" type="submit">Create shared Jitsi room</button><?= form_close(); ?>
<?php echo form_open(admin_url('google_meet/save_shared_link/'.(int)$meeting->id), ['style'=>'margin-top:12px']); ?>
<div class="input-group"><input class="form-control" type="url" name="meet_link" placeholder="Shared Jitsi or existing Video Meeting room URL" required><span class="input-group-btn"><button class="btn btn-primary" type="submit">Save Shared Link</button></span></div>
<?php echo form_close(); ?>
<?php } ?>
<?php } else { ?><p><strong>Every invitation uses this same room:</strong><br><a target="_blank" rel="noopener" href="<?php echo html_escape($meeting->meet_link); ?>"><?php echo html_escape($meeting->meet_link); ?></a></p><?php } ?>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/calendar/'.(int)$meeting->id); ?>"><i class="fa fa-calendar"></i> Download Calendar File</a>
</div></div>
<?php $this->load->view('modals/share_meeting_modal', ['meeting' => $meeting]); init_tail(); ?>

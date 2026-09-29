<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head();
$subject = $meeting->subject ?? $meeting->title ?? 'Google Meet Meeting';
?>
<div id="wrapper">
  <div class="content">
    <div class="google-meet-wrap">
      <div class="gm-hero">
        <div>
          <h1><?php echo html_escape($subject); ?></h1>
          <p><?php echo html_escape($meeting->description ?? ''); ?></p>
        </div>
        <a class="btn btn-success btn-sm"  href="<?php echo html_escape($meeting->meet_link); ?>">Join Google Meet</a>
      </div>

      <?php $this->load->view('google_meet/_nav'); ?>

      <div class="panel_s google-meet-card">
        <div class="panel-body">
          <div class="row">
            <div class="col-md-8">
              <h4>Meeting Details</h4>
              <p><b>Start:</b> <?php echo !empty($meeting->start_time) ? _dt($meeting->start_time) : ''; ?></p>
              <p><b>End:</b> <?php echo !empty($meeting->end_time) ? _dt($meeting->end_time) : ''; ?></p>
              <p><b>Status:</b> <?php echo html_escape($meeting->status ?? ''); ?></p>
              <p><b>Duration:</b> <?php echo (int)($meeting->duration_minutes ?? 0); ?> minutes</p>
              <p><b>Google API Status:</b> <?php echo html_escape($meeting->google_api_status ?? 'manual'); ?></p>
              <p><b>Link:</b> <a  href="<?php echo html_escape($meeting->meet_link); ?>"><?php echo html_escape($meeting->meet_link); ?></a></p>
              <?php if (!empty($meeting->notes)) { ?><p><b>Internal Notes:</b><br><?php echo nl2br(html_escape($meeting->notes)); ?></p><?php } ?>
            </div>
            <div class="col-md-4">
              <a class="btn btn-info btn-block" href="<?php echo admin_url('google_meet/start/'.$meeting->id); ?>">Mark Started</a>
              <a class="btn btn-success btn-block" href="<?php echo admin_url('google_meet/finish/'.$meeting->id); ?>">Mark Completed</a>
              <a class="btn btn-warning btn-block" href="<?php echo admin_url('google_meet/notify/'.$meeting->id); ?>">Send Notifications</a>
              <a class="btn btn-default btn-block" href="<?php echo admin_url('google_meet/create/'.$meeting->id); ?>">Edit</a>
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
                <small><?php echo !empty($comment['created_at']) ? _dt($comment['created_at']) : ''; ?></small>
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
<div class="alert alert-warning">No shared room has been saved. Do not send <strong>meet.google.com/new</strong> to attendees because each person can receive a different room.</div>
<a class="btn btn-info btn-sm" target="_blank" rel="noopener" href="https://meet.google.com/new"><i class="fa fa-video-camera"></i> Host: Create Google Meet</a>
<?php echo form_open(admin_url('google_meet/save_shared_link/'.(int)$meeting->id), ['style'=>'margin-top:12px']); ?>
<div class="input-group"><input class="form-control" type="url" name="meet_link" placeholder="https://meet.google.com/abc-defg-hij" required><span class="input-group-btn"><button class="btn btn-primary" type="submit">Save Shared Link</button></span></div>
<?php echo form_close(); ?>
<?php } else { ?><p><strong>Every invitation uses this same room:</strong><br><a target="_blank" rel="noopener" href="<?php echo html_escape($meeting->meet_link); ?>"><?php echo html_escape($meeting->meet_link); ?></a></p><?php } ?>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('google_meet/calendar/'.(int)$meeting->id); ?>"><i class="fa fa-calendar"></i> Download Calendar File</a>
</div></div>
<?php init_tail(); ?>

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
        <a class="btn btn-success" target="_blank" href="<?php echo html_escape($meeting->meet_link); ?>">Join Google Meet</a>
      </div>

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
              <p><b>Link:</b> <a target="_blank" href="<?php echo html_escape($meeting->meet_link); ?>"><?php echo html_escape($meeting->meet_link); ?></a></p>
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
            <table class="table table-striped">
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
<?php init_tail(); ?>

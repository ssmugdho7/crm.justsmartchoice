<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $meetingTitle = !empty($meeting->subject) ? $meeting->subject : (!empty($meeting->title) ? $meeting->title : 'Google Meet Meeting'); ?>
<div class="google-meet-wrap smart-choice-normalized-module">
  <div class="gm-hero">
    <div>
      <h2><i class="fa fa-video-camera" aria-hidden="true"></i> <?php echo html_escape($meetingTitle); ?></h2>
      <p><?php echo html_escape(isset($meeting->description) ? $meeting->description : ''); ?></p>
    </div>
    <?php if ($this->google_meet_model->is_real_meet_link(isset($meeting->meet_link) ? $meeting->meet_link : '')) { ?>
      <a class="btn btn-success btn-sm" href="<?php echo site_url('google_meet/meeting_clients/join/' . (int) $meeting->id); ?>">Join Google Meet</a>
    <?php } else { ?>
      <span class="label label-default"><?php echo html_escape(google_meet_lang('google_meet_link_pending', 'Meeting link pending')); ?></span>
    <?php } ?>
  </div>

  <div class="panel_s google-meet-card">
    <div class="panel-body">
      <p><strong><?php echo html_escape(google_meet_lang('google_meet_start_time', 'Start')); ?>:</strong> <?php echo !empty($meeting->start_time) ? _dt($meeting->start_time) : ''; ?></p>
      <p><strong><?php echo html_escape(google_meet_lang('google_meet_end_time', 'End')); ?>:</strong> <?php echo !empty($meeting->end_time) ? _dt($meeting->end_time) : ''; ?></p>
      <p><strong><?php echo html_escape(google_meet_lang('google_meet_status', 'Status')); ?>:</strong> <?php echo html_escape(ucfirst(isset($meeting->status) ? $meeting->status : 'scheduled')); ?></p>
      <p><strong>Google Meet:</strong>
        <?php if ($this->google_meet_model->is_real_meet_link(isset($meeting->meet_link) ? $meeting->meet_link : '')) { ?>
          <a href="<?php echo site_url('google_meet/meeting_clients/join/' . (int) $meeting->id); ?>">Open assigned meeting</a>
        <?php } else { ?>
          <span class="text-muted"><?php echo html_escape(google_meet_lang('google_meet_link_not_available', 'The Google Meet link has not been added yet.')); ?></span>
        <?php } ?>
      </p>
      <a class="btn btn-default btn-sm" href="<?php echo site_url('google_meet/meeting_clients/meetings'); ?>"><?php echo html_escape(google_meet_lang('google_meet_back_to_meetings', 'Back to Meetings')); ?></a>
    </div>
  </div>
</div>

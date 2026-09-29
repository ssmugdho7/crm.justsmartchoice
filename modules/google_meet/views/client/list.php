<?php defined('BASEPATH') or exit('No direct script access allowed');
$summary = isset($summary) && is_array($summary) ? $summary : ['total'=>count((array)$meetings),'upcoming'=>0,'available_now'=>0,'completed'=>0];
?>
<div class="google-meet-wrap smart-choice-normalized-module gm-client-portal-page">
  <div class="gm-hero gm-client-hero">
    <div>
      <h2><i class="fa fa-video-camera" aria-hidden="true"></i> <?php echo html_escape(google_meet_lang('google_meet_my_meetings', 'Google Meet Meetings')); ?></h2>
      <p><?php echo html_escape(google_meet_lang('google_meet_customer_dashboard_intro', 'See every Google Meet assigned to your account, including upcoming meetings, meetings available now, and meeting history.')); ?></p>
    </div>
  </div>

  <div class="row gm-client-summary-row">
    <div class="col-md-3 col-sm-6"><div class="gm-client-stat"><span><?php echo html_escape(google_meet_lang('google_meet_total_meetings', 'Total Meetings')); ?></span><strong><?php echo (int)$summary['total']; ?></strong></div></div>
    <div class="col-md-3 col-sm-6"><div class="gm-client-stat"><span><?php echo html_escape(google_meet_lang('google_meet_upcoming', 'Upcoming')); ?></span><strong><?php echo (int)$summary['upcoming']; ?></strong></div></div>
    <div class="col-md-3 col-sm-6"><div class="gm-client-stat gm-client-stat-live"><span><?php echo html_escape(google_meet_lang('google_meet_available_now', 'Available Now')); ?></span><strong><?php echo (int)$summary['available_now']; ?></strong></div></div>
    <div class="col-md-3 col-sm-6"><div class="gm-client-stat"><span><?php echo html_escape(google_meet_lang('google_meet_meeting_history', 'Meeting History')); ?></span><strong><?php echo (int)$summary['completed']; ?></strong></div></div>
  </div>

  <?php if (empty($meetings)) { ?>
    <div class="panel_s google-meet-card gm-client-empty-panel">
      <div class="panel-body">
        <div class="gm-client-empty-state">
          <div class="gm-empty-icon"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></div>
          <h3><?php echo html_escape(google_meet_lang('google_meet_no_meeting_scheduled', 'You do not have any Google Meet meetings yet')); ?></h3>
          <p><?php echo html_escape(google_meet_lang('google_meet_no_meeting_explanation', 'There are no Google Meet meetings assigned to your customer account right now. When our team schedules or starts a meeting for you, it will appear on this page automatically.')); ?></p>
          <button type="button" class="btn btn-info" onclick="window.location.reload();"><i class="fa fa-refresh"></i> <?php echo html_escape(google_meet_lang('google_meet_check_again', 'Check Again')); ?></button>
        </div>
      </div>
    </div>
  <?php } else { ?>
    <div class="panel_s google-meet-card gm-client-list-panel">
      <div class="panel-body">
        <div class="gm-client-section-head">
          <h3><i class="fa fa-list"></i> <?php echo html_escape(google_meet_lang('google_meet_all_customer_meetings', 'All Your Google Meet Meetings')); ?></h3>
          <span><?php echo (int)$summary['total']; ?> <?php echo html_escape(google_meet_lang('google_meet_meetings_assigned', 'meeting(s) assigned')); ?></span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover gm-client-meetings-table">
            <thead><tr>
              <th><?php echo html_escape(google_meet_lang('google_meet_meeting_name', 'Meeting')); ?></th>
              <th><?php echo html_escape(google_meet_lang('google_meet_date_time', 'Date & Time')); ?></th>
              <th><?php echo html_escape(google_meet_lang('google_meet_status', 'Status')); ?></th>
              <th class="text-right"><?php echo html_escape(google_meet_lang('google_meet_actions', 'Actions')); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ((array)$meetings as $meeting) {
                $meetingId = isset($meeting['id']) ? (int)$meeting['id'] : 0;
                $meetingTitle = !empty($meeting['subject']) ? $meeting['subject'] : (!empty($meeting['title']) ? $meeting['title'] : 'Google Meet Meeting');
                $status = strtolower(trim((string)($meeting['status'] ?? 'scheduled')));
                $hasLink = $this->google_meet_model->is_real_meet_link($meeting['meet_link'] ?? '');
                $statusClass = $status === 'live' ? 'success' : (in_array($status,['completed','cancelled','canceled'],true) ? 'default' : 'info');
            ?>
              <tr>
                <td><strong><?php echo html_escape($meetingTitle); ?></strong><?php if (!empty($meeting['description'])) { ?><div class="text-muted gm-client-description"><?php echo html_escape(mb_strimwidth(strip_tags((string)$meeting['description']),0,120,'...')); ?></div><?php } ?></td>
                <td><?php echo !empty($meeting['start_time']) ? _dt($meeting['start_time']) : html_escape(google_meet_lang('google_meet_time_pending', 'Time pending')); ?></td>
                <td><span class="label label-<?php echo $statusClass; ?> gm-status-label"><?php echo html_escape(ucwords(str_replace('_',' ', $status ?: 'scheduled'))); ?></span></td>
                <td class="text-right gm-client-actions">
                  <a class="btn btn-default btn-sm" href="<?php echo site_url('google_meet/meeting_clients/view/' . $meetingId); ?>"><i class="fa fa-eye"></i> <?php echo html_escape(google_meet_lang('google_meet_view', 'View')); ?></a>
                  <?php if ($hasLink && !in_array($status,['completed','cancelled','canceled'],true)) { ?>
                    <a class="btn btn-success btn-sm" href="<?php echo site_url('google_meet/meeting_clients/join/' . $meetingId); ?>"><i class="fa fa-video-camera"></i> <?php echo html_escape(google_meet_lang('google_meet_join_now', 'Join Now')); ?></a>
                  <?php } else if (!$hasLink && !in_array($status,['completed','cancelled','canceled'],true)) { ?>
                    <span class="text-muted gm-link-pending"><i class="fa fa-clock-o"></i> <?php echo html_escape(google_meet_lang('google_meet_link_pending', 'Meeting link pending')); ?></span>
                  <?php } ?>
                </td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php } ?>
</div>

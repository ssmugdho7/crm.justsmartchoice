<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="google-meet-wrap gm-meetings-page">
      <div class="gm-hero">
        <div>
          <h1><?php echo google_meet_lang('google_meet', 'Google Meet'); ?></h1>
          <p><?php echo google_meet_lang('google_meet_dashboard_description', 'Schedule shared Google Meet sessions, invite participants, and manage every meeting from one table.'); ?></p>
        </div>
        <a href="<?php echo admin_url('google_meet/create'); ?>" class="btn btn-info btn-sm">
          <i class="fa fa-plus-circle"></i> <?php echo google_meet_lang('google_meet_new_meeting', 'New Meeting'); ?>
        </a>
      </div>

      <div class="row gm-stats">
        <div class="col-md-4"><div class="gm-card"><span><?php echo google_meet_lang('google_meet_total_meetings', 'Total Meetings'); ?></span><strong><?php echo (int) $summary['total']; ?></strong></div></div>
        <div class="col-md-4"><div class="gm-card"><span><?php echo google_meet_lang('google_meet_completed', 'Completed'); ?></span><strong><?php echo (int) $summary['completed']; ?></strong></div></div>
        <div class="col-md-4"><div class="gm-card"><span><?php echo google_meet_lang('google_meet_total_minutes', 'Total Minutes'); ?></span><strong><?php echo (int) $summary['minutes']; ?></strong></div></div>
      </div>

      <div class="panel_s">
        <div class="panel-body">
          <div class="gm-table-shell">
            <table class="table dt-table gm-meetings-table" data-order-col="3" data-order-type="desc">
              <thead>
                <tr>
                  <th><?php echo google_meet_lang('google_meet_meeting_name', 'Meeting Name'); ?></th>
                  <th><?php echo google_meet_lang('google_meet_organizer', 'Organizer'); ?></th>
                  <th><?php echo google_meet_lang('google_meet_participants', 'Participants'); ?></th>
                  <th><?php echo google_meet_lang('google_meet_date_time', 'Date And Time'); ?></th>
                  <th><?php echo google_meet_lang('google_meet_status', 'Status'); ?></th>
                  <th><?php echo google_meet_lang('google_meet_actions', 'Actions'); ?></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($meetings as $m) {
                  $meetingId = (int) ($m['id'] ?? 0);
                  $meetingName = $m['subject'] ?: ($m['title'] ?? google_meet_lang('google_meet', 'Google Meet'));
                  $organizer = $m['assigned_staff_name'] ?: ($m['created_by_name'] ?? '—');
                  $status = strtolower((string) ($m['status'] ?? 'scheduled'));
                  $statusClass = [
                      'live' => 'success',
                      'completed' => 'default',
                      'cancelled' => 'danger',
                      'link_required' => 'warning',
                      'waiting' => 'warning',
                      'scheduled' => 'info',
                  ][$status] ?? 'info';
                  $attendeeCount = isset($m['attendee_count']) ? (int) $m['attendee_count'] : 0;
                  $meetLink = trim((string) ($m['meet_link'] ?? ''));
              ?>
                <tr>
                  <td class="gm-meeting-name-cell">
                    <a href="<?php echo admin_url('google_meet/view/' . $meetingId); ?>" class="gm-meeting-title">
                      <?php echo html_escape($meetingName); ?>
                    </a>
                    <?php if (!empty($m['description'])) { ?>
                      <div class="gm-meeting-subtitle"><?php echo html_escape(mb_strimwidth(strip_tags($m['description']), 0, 90, '…')); ?></div>
                    <?php } ?>
                  </td>
                  <td><?php echo html_escape($organizer); ?></td>
                  <td><span class="badge gm-participant-badge"><i class="fa fa-users"></i> <?php echo $attendeeCount; ?></span></td>
                  <td class="gm-date-cell"><?php echo !empty($m['start_time']) ? google_meet_display_datetime($m['start_time']) : '—'; ?></td>
                  <td><span class="label label-<?php echo $statusClass; ?> gm-status-badge"><?php echo html_escape(ucwords(str_replace('_', ' ', $status))); ?></span></td>
                  <td class="gm-actions-cell">
                    <div class="gm-action-buttons">
                      <a class="btn btn-default btn-xs" href="<?php echo admin_url('google_meet/view/' . $meetingId); ?>" title="<?php echo _l('view'); ?>">
                        <i class="fa fa-eye"></i> <span><?php echo _l('view'); ?></span>
                      </a>
                      <?php if (has_permission('google_meet', '', 'edit')) { ?>
                      <a class="btn btn-default btn-xs" href="<?php echo admin_url('google_meet/create/' . $meetingId); ?>" title="<?php echo _l('edit'); ?>">
                        <i class="fa fa-pencil"></i> <span><?php echo _l('edit'); ?></span>
                      </a>
                      <?php } ?>
                      <?php if ($meetLink !== '') { ?>
                      <a class="btn btn-success btn-xs" href="<?php echo admin_url('google_meet/room/' . $meetingId); ?>" title="<?php echo google_meet_lang('google_meet_join', 'Join'); ?>">
                        <i class="fa fa-video-camera"></i> <span><?php echo google_meet_lang('google_meet_join', 'Join'); ?></span>
                      </a>
                      <?php } ?>
                      <div class="btn-group gm-more-actions">
                        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="fa fa-ellipsis-v"></i> <span><?php echo google_meet_lang('google_meet_more', 'More'); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                          <?php if ($meetLink !== '') { ?>
                          <li><a href="#" class="gm-copy-link" data-link="<?php echo html_escape($meetLink); ?>"><i class="fa fa-copy"></i> <?php echo google_meet_lang('google_meet_copy_link', 'Copy Meeting Link'); ?></a></li>
                          <?php } ?>
                          <li><a href="<?php echo admin_url('google_meet/notify/' . $meetingId); ?>"><i class="fa fa-envelope"></i> <?php echo google_meet_lang('google_meet_send_invitations_again', 'Send Invitations Again'); ?></a></li>
                          <li><a href="<?php echo admin_url('google_meet/calendar/' . $meetingId); ?>"><i class="fa fa-calendar-plus-o"></i> <?php echo google_meet_lang('google_meet_download_calendar', 'Download Calendar'); ?></a></li>
                          <li><a href="<?php echo admin_url('google_meet/view/' . $meetingId); ?>#attendance"><i class="fa fa-users"></i> <?php echo google_meet_lang('google_meet_attendance', 'Meeting Attendance'); ?></a></li>
                          <?php if (has_permission('google_meet', '', 'delete')) { ?>
                          <li role="separator" class="divider"></li>
                          <li><?php echo form_open(admin_url('google_meet/delete/' . (int)$meetingId), ['onsubmit'=>"return confirm('Delete this meeting and its history?');"]); ?><button type="submit" class="btn btn-link text-danger"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></button><?= form_close(); ?></li>
                          <?php } ?>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  function copyText(text) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    }
    return new Promise(function (resolve, reject) {
      var area = document.createElement('textarea');
      area.value = text;
      area.style.position = 'fixed';
      area.style.opacity = '0';
      document.body.appendChild(area);
      area.focus();
      area.select();
      try { document.execCommand('copy') ? resolve() : reject(); } catch (e) { reject(e); }
      document.body.removeChild(area);
    });
  }
  document.addEventListener('click', function (event) {
    var link = event.target.closest('.gm-copy-link');
    if (!link) return;
    event.preventDefault();
    copyText(link.getAttribute('data-link') || '').then(function () {
      if (typeof alert_float === 'function') alert_float('success', <?php echo json_encode(google_meet_lang('google_meet_link_copied', 'Meeting link copied.')); ?>);
    });
  });
})();
</script>
<?php init_tail(); ?>

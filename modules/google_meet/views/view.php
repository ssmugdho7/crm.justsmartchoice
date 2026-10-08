<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
init_head();
$meetingId = (int) $meeting->id;
$hasRoomLink = $this->google_meet_model->is_real_meet_link($meeting->meet_link ?? '', $meeting);
$subject = trim((string) ($meeting->subject ?? $meeting->title ?? '')) ?: 'Video Meeting';
$canEdit = has_permission('google_meet', '', 'edit');
$canDelete = has_permission('google_meet', '', 'delete');
$status = (string) ($meeting->status ?? 'scheduled');
$statusStyle = in_array($status, ['scheduled', 'live', 'completed', 'cancelled'], true) ? $status : 'scheduled';
$attendeeList = (array) $attendees;
$commentList = (array) $comments;
?>
<link rel="stylesheet" href="<?= module_dir_url('google_meet', 'assets/css/meeting_details.css'); ?>?v=1">
<div id="wrapper">
  <div class="content">
    <div class="google-meet-wrap gm-detail-page">
      <nav class="gm-detail-breadcrumb" aria-label="Meeting navigation">
        <a href="<?= admin_url('google_meet'); ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i> Meetings</a>
        <span aria-hidden="true">/</span><span aria-current="page">Meeting details</span>
      </nav>

      <header class="gm-detail-header">
        <div class="gm-detail-heading">
          <span class="gm-detail-icon"><i class="fa fa-video-camera" aria-hidden="true"></i></span>
          <div>
            <div class="gm-detail-eyebrow">Video Meeting <span class="gm-detail-status gm-detail-status--<?= $statusStyle; ?>"><?= html_escape(ucfirst($status)); ?></span></div>
            <h1><?= html_escape($subject); ?></h1>
            <?php if (trim((string) ($meeting->description ?? '')) !== '') { ?><p><?= nl2br(html_escape($meeting->description)); ?></p><?php } ?>
          </div>
        </div>
        <div class="gm-detail-header-actions">
          <?php if ($hasRoomLink) { ?>
          <a class="btn gm-detail-primary" href="<?= admin_url('google_meet/room/' . $meetingId); ?>"><i class="fa fa-video-camera" aria-hidden="true"></i> Join meeting</a>
          <?php } else { ?>
          <button class="btn gm-detail-primary" type="button" disabled><i class="fa fa-video-camera" aria-hidden="true"></i> Room not ready</button>
          <?php } ?>
          <button class="btn gm-detail-secondary" type="button" data-toggle="modal" data-target="#shareMeetingModal" <?= $hasRoomLink ? '' : 'disabled'; ?>><i class="fa fa-share-alt" aria-hidden="true"></i> Share meeting</button>
        </div>
      </header>

      <div class="gm-detail-navigation"><?php $this->load->view('google_meet/_nav'); ?></div>

      <div class="gm-detail-layout">
        <main class="gm-detail-main">
          <section class="gm-detail-card" aria-labelledby="gm-detail-schedule-title">
            <div class="gm-detail-card-heading"><h2 id="gm-detail-schedule-title"><i class="fa fa-calendar" aria-hidden="true"></i> Meeting schedule</h2><span class="gm-detail-muted"><?= (int) ($meeting->duration_minutes ?? 0); ?> minutes</span></div>
            <dl class="gm-detail-facts">
              <div><dt>Starts</dt><dd><?= !empty($meeting->start_time) ? html_escape(google_meet_display_datetime($meeting->start_time)) : 'Not scheduled'; ?></dd></div>
              <div><dt>Ends</dt><dd><?= !empty($meeting->end_time) ? html_escape(google_meet_display_datetime($meeting->end_time)) : 'Not scheduled'; ?></dd></div>
              <div><dt>Meeting platform</dt><dd><?= ($meeting->provider ?? '') === 'jitsi' ? 'Jitsi' : 'Video Meeting'; ?></dd></div>
              <div><dt>Room status</dt><dd><span class="gm-detail-room-state<?= $hasRoomLink ? ' is-ready' : ''; ?>"><?= $hasRoomLink ? 'Shared room ready' : 'Meeting link pending'; ?></span></dd></div>
            </dl>
            <a class="gm-detail-text-link" href="<?= admin_url('google_meet/calendar/' . $meetingId); ?>"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i> Download calendar file</a>
          </section>

          <section class="gm-detail-card" aria-labelledby="gm-detail-room-title">
            <div class="gm-detail-card-heading"><h2 id="gm-detail-room-title"><i class="fa fa-link" aria-hidden="true"></i> Shared meeting room</h2></div>
            <?php if ($hasRoomLink) { ?>
            <p class="gm-detail-muted">Every invitation uses this same room, so everyone joins the same meeting.</p>
            <a class="gm-detail-room-link" href="<?= html_escape($meeting->meet_link); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-external-link" aria-hidden="true"></i><span><?= html_escape($meeting->meet_link); ?></span></a>
            <?php } else { ?>
            <div class="gm-detail-notice"><i class="fa fa-info-circle" aria-hidden="true"></i><p>No shared room has been saved. Create a Jitsi room before inviting attendees.</p></div>
            <?php if ($canEdit) { ?>
            <?= form_open(admin_url('google_meet/generate_room/' . $meetingId)); ?>
              <button class="btn gm-detail-primary" type="submit"><i class="fa fa-plus" aria-hidden="true"></i> Create shared Jitsi room</button>
            <?= form_close(); ?>
            <details class="gm-detail-manual-link">
              <summary>Use an existing room link</summary>
              <?= form_open(admin_url('google_meet/save_shared_link/' . $meetingId)); ?>
                <label for="gm-detail-shared-link">Shared room URL</label>
                <div class="gm-detail-link-form"><input id="gm-detail-shared-link" class="form-control" type="url" name="meet_link" placeholder="https://meet.jit.si/your-room" required><button class="btn gm-detail-secondary" type="submit">Save shared link</button></div>
              <?= form_close(); ?>
            </details>
            <?php } ?>
            <?php } ?>
          </section>

          <section class="gm-detail-card" aria-labelledby="gm-detail-attendees-title">
            <div class="gm-detail-card-heading"><h2 id="gm-detail-attendees-title"><i class="fa fa-users" aria-hidden="true"></i> Attendees <span class="gm-detail-count"><?= count($attendeeList); ?></span></h2><?php if ($canEdit) { ?><a class="gm-detail-text-link" href="<?= admin_url('google_meet/create/' . $meetingId); ?>">Manage attendees</a><?php } ?></div>
            <?php if ($attendeeList) { ?>
            <div class="gm-detail-table-wrap">
              <table class="table gm-detail-attendees">
                <caption class="sr-only">Meeting attendees and notification status</caption>
                <thead><tr><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Type</th><th scope="col">Notified</th></tr></thead>
                <tbody><?php foreach ($attendeeList as $attendee) { ?>
                  <tr><td><strong><?= html_escape($attendee['name'] ?? 'Attendee'); ?></strong></td><td><?= html_escape($attendee['email'] ?? ''); ?></td><td><?= html_escape(ucfirst((string) ($attendee['attendee_type'] ?? ''))); ?></td><td><span class="gm-detail-notified<?= !empty($attendee['notified']) ? ' is-notified' : ''; ?>"><?= !empty($attendee['notified']) ? 'Yes' : 'No'; ?></span></td></tr>
                <?php } ?></tbody>
              </table>
            </div>
            <?php } else { ?>
            <div class="gm-detail-empty"><span class="gm-detail-empty-icon"><i class="fa fa-user-plus" aria-hidden="true"></i></span><h3>No attendees yet</h3><p>Add the staff or customers who should receive this meeting invitation.</p><?php if ($canEdit) { ?><a class="btn gm-detail-secondary" href="<?= admin_url('google_meet/create/' . $meetingId); ?>">Add attendees</a><?php } ?></div>
            <?php } ?>
          </section>

          <section class="gm-detail-card" aria-labelledby="gm-detail-notes-title">
            <div class="gm-detail-card-heading"><h2 id="gm-detail-notes-title"><i class="fa fa-comment-o" aria-hidden="true"></i> Meeting notes</h2><span class="gm-detail-count"><?= count($commentList); ?></span></div>
            <p id="gm-detail-note-help" class="gm-detail-muted">Notes here are visible to staff with access to this meeting.</p>
            <?= form_open(admin_url('google_meet/add_comment/' . $meetingId), ['class' => 'gm-detail-note-form']); ?>
              <?= render_textarea('comment', 'Add a meeting note', '', ['rows' => 3, 'placeholder' => 'Add an update, discussion point or next step…', 'aria-describedby' => 'gm-detail-note-help']); ?>
              <button type="submit" class="btn gm-detail-primary"><i class="fa fa-plus" aria-hidden="true"></i> Add note</button>
            <?= form_close(); ?>
            <div class="gm-detail-timeline">
              <?php foreach ($commentList as $comment) { ?>
              <article class="gm-detail-note"><div class="gm-detail-note-heading"><strong><?= html_escape(($comment['staff_name'] ?? '') ?: 'Staff'); ?></strong><span><?= !empty($comment['created_at']) ? html_escape(google_meet_display_datetime($comment['created_at'])) : ''; ?></span></div><p><?= nl2br(html_escape($comment['comment'] ?? '')); ?></p></article>
              <?php } ?>
              <?php if (!$commentList) { ?><p class="gm-detail-muted gm-detail-no-notes">No notes yet. Add the first update above.</p><?php } ?>
            </div>
          </section>
        </main>

        <aside class="gm-detail-sidebar" aria-label="Meeting management">
          <section class="gm-detail-card gm-detail-actions" aria-labelledby="gm-detail-actions-title">
            <div class="gm-detail-card-heading"><h2 id="gm-detail-actions-title">Meeting actions</h2></div>
            <?php if ($canEdit) { ?>
            <a class="btn gm-detail-secondary" href="<?= admin_url('google_meet/notify/' . $meetingId); ?>"><i class="fa fa-bell-o" aria-hidden="true"></i> Send notifications</a>
            <a class="btn gm-detail-secondary" href="<?= admin_url('google_meet/create/' . $meetingId); ?>"><i class="fa fa-pencil" aria-hidden="true"></i> Edit meeting</a>
            <div class="gm-detail-action-divider"></div>
            <a class="gm-detail-action-link" href="<?= admin_url('google_meet/start/' . $meetingId); ?>"><i class="fa fa-play-circle-o" aria-hidden="true"></i> Mark started</a>
            <a class="gm-detail-action-link" href="<?= admin_url('google_meet/finish/' . $meetingId); ?>"><i class="fa fa-check-circle-o" aria-hidden="true"></i> Mark completed</a>
            <?php } ?>
            <a class="gm-detail-action-link" href="<?= admin_url('google_meet/reports'); ?>"><i class="fa fa-bar-chart" aria-hidden="true"></i> Meeting reports</a>
            <?php if ($canDelete) { ?>
            <details class="gm-detail-delete"><summary>Delete meeting</summary><p>Deletes this meeting, its attendees, notes and notification history.</p>
              <?= form_open(admin_url('google_meet/delete/' . $meetingId), ['onsubmit' => "return confirm('Delete this meeting and all its attendees, notes and notification history?');"]); ?>
                <button class="btn gm-detail-danger" type="submit"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete meeting</button>
              <?= form_close(); ?>
            </details>
            <?php } ?>
          </section>
          <?php if (!empty($meeting->notes)) { ?>
          <section class="gm-detail-card" aria-labelledby="gm-detail-internal-title"><div class="gm-detail-card-heading"><h2 id="gm-detail-internal-title"><i class="fa fa-lock" aria-hidden="true"></i> Internal notes</h2></div><p class="gm-detail-internal-notes"><?= nl2br(html_escape($meeting->notes)); ?></p></section>
          <?php } ?>
        </aside>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('modals/share_meeting_modal', ['meeting' => $meeting]); init_tail(); ?>

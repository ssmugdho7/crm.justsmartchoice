<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<link rel="stylesheet" href="<?= module_dir_url('google_meet', 'assets/css/jitsi_meeting.css'); ?>?v=130">
<div id="wrapper"><div class="content"><section class="gm-embedded-room">
  <header class="gm-room-heading"><div><h1><?= html_escape($title); ?></h1><p>Video meeting · <?= html_escape($meeting->status); ?></p></div><div class="gm-room-actions">
    <button class="btn btn-info" type="button" data-toggle="modal" data-target="#shareMeetingModal">Share meeting</button>
    <button class="btn btn-default" type="button" data-gm-fullscreen>Full screen</button>
    <a class="btn btn-default" href="<?= html_escape($meeting->meet_link); ?>" target="_blank" rel="noopener noreferrer">Open in new window</a>
    <?php if ($current_user['is_host']) { ?><button class="btn btn-warning" type="button" id="gm-finish-meeting">Finish meeting</button><?php } ?>
    <?php if (has_permission('google_meet', '', 'delete')) { echo form_open(admin_url('google_meet/delete/' . (int)$meeting->id), ['onsubmit' => "return confirm('Delete this meeting and all its attendees, notes and notification history?');"]); ?><button class="btn btn-danger" type="submit">Delete</button><?= form_close(); } ?>
    <a class="btn btn-default" href="<?= admin_url('google_meet/view/' . (int)$meeting->id); ?>">Back to details</a>
  </div></header>
  <?php if ($room['domain'] === 'meet.jit.si') { ?><p class="alert alert-info">The host must sign in to Jitsi to start the room. Guests join after the host starts it. No Google credentials are needed by this CRM.</p><?php } ?>
  <?php if ($room['room_pin'] !== '') { ?><p class="alert alert-info">PIN protection is applied when the host becomes a Jitsi moderator. Share the full invitation with participants who open the room outside the CRM.</p><?php } ?>
  <div class="gm-room-grid"><section aria-label="Video conference"><div id="jitsi-meet-viewport"><p class="gm-room-loading">Loading your video room…</p></div><p id="gm-room-feedback" role="status" aria-live="polite"></p></section>
    <aside class="gm-notes-dock" aria-label="Private staff meeting notes"><h2>Meeting notes</h2><p>Notes stay in the CRM and are visible to authorized staff.</p>
      <label for="gm-room-note">Live note</label><textarea id="gm-room-note" class="form-control" rows="6" maxlength="20000" placeholder="Capture decisions and next steps…"></textarea>
      <button class="btn btn-primary mtop10" id="gm-save-note" type="button">Save to timeline</button><p id="gm-note-feedback" role="status" aria-live="polite">Changes save automatically.</p>
      <h3>Attendees</h3><ul><?php foreach ((array)$attendees as $attendee) { ?><li><?= html_escape($attendee['name'] ?: $attendee['email']); ?></li><?php } ?></ul>
      <h3>Timeline</h3><div id="gm-note-timeline"><?php foreach ((array)$comments as $comment) { ?><article data-comment-id="<?= (int)$comment['id']; ?>"><strong><?= html_escape($comment['staff_name'] ?? 'Staff'); ?></strong><p><?= nl2br(html_escape($comment['comment'])); ?></p></article><?php } ?></div>
    </aside>
  </div>
  <?php $config = ['domain' => $room['domain'], 'roomName' => $room['room_name'], 'pin' => $room['room_pin'], 'meetingId' => (int)$meeting->id, 'subject' => $title,
    'userInfo' => ['displayName' => $current_user['name'], 'email' => $current_user['email']], 'isHost' => $current_user['is_host'], 'noteKey' => $note_key,
    'lifecycleUrl' => admin_url('google_meet/ajax_lifecycle'), 'noteUrl' => admin_url('google_meet/save_room_note/' . (int)$meeting->id),
    'csrf' => ['token_name' => $this->security->get_csrf_token_name(), 'hash' => $this->security->get_csrf_hash()]]; ?>
  <script type="application/json" id="gm-room-config"><?= json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?></script>
</section></div></div>
<?php $this->load->view('modals/share_meeting_modal', ['meeting' => $meeting]); init_tail(); ?>

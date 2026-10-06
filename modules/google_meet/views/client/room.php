<?php defined('BASEPATH') or exit('No direct script access allowed'); $clientRoom = true; ?>
<link rel="stylesheet" href="<?= module_dir_url('google_meet', 'assets/css/jitsi_meeting.css'); ?>?v=130">
<section class="gm-embedded-room gm-client-room">
  <header class="gm-room-heading"><div><h1><?= html_escape($title); ?></h1><p>Join your assigned video meeting.</p></div><div class="gm-room-actions">
    <button class="btn btn-info" type="button" data-toggle="modal" data-target="#shareMeetingModal">Share meeting</button>
    <button class="btn btn-default" type="button" data-gm-fullscreen>Full screen</button>
    <a class="btn btn-default" href="<?= html_escape($meeting->meet_link); ?>" target="_blank" rel="noopener noreferrer">Open in new window</a>
    <a class="btn btn-default" href="<?= site_url('google_meet/meeting_clients/view/' . (int)$meeting->id); ?>">Back to details</a>
  </div></header>
  <?php if ($room['domain'] === 'meet.jit.si') { ?><p class="alert alert-info">If the room is waiting for a moderator, your host needs to sign in to Jitsi and start the meeting.</p><?php } ?>
  <div id="jitsi-meet-viewport"><p class="gm-room-loading">Loading your video room…</p></div><p id="gm-room-feedback" role="status" aria-live="polite"></p>
  <?php $config = ['domain' => $room['domain'], 'roomName' => $room['room_name'], 'pin' => $room['room_pin'], 'meetingId' => (int)$meeting->id, 'subject' => $title,
    'userInfo' => ['displayName' => $current_user['name'], 'email' => $current_user['email']], 'isHost' => false,
    'lifecycleUrl' => site_url('google_meet/meeting_clients/ajax_lifecycle'),
    'csrf' => ['token_name' => $this->security->get_csrf_token_name(), 'hash' => $this->security->get_csrf_hash()]]; ?>
  <script type="application/json" id="gm-room-config"><?= json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?></script>
</section>
<?php $this->load->view('modals/share_meeting_modal', ['meeting' => $meeting, 'clientRoom' => true]); ?>

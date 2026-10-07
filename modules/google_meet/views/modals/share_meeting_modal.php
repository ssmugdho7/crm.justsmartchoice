<?php defined('BASEPATH') or exit('No direct script access allowed');
$share = jitsi_build_invitation_payload($meeting, !empty($clientRoom)); ?>
<link rel="stylesheet" href="<?= module_dir_url('google_meet', 'assets/css/jitsi_meeting.css'); ?>?v=130">
<div class="modal fade gm-share-modal" id="shareMeetingModal" tabindex="-1" role="dialog" aria-labelledby="gm-share-title">
  <div class="modal-dialog" role="document"><div class="modal-content">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button><h4 id="gm-share-title">Share meeting</h4></div>
    <div class="modal-body">
      <label for="gm-share-link">Meeting link</label><div class="input-group"><input id="gm-share-link" class="form-control" readonly value="<?= html_escape($share['link']); ?>"><span class="input-group-btn"><button class="btn btn-default" type="button" data-gm-copy="gm-share-link">Copy link</button></span></div>
      <?php if (!empty($meeting->room_pin)) { ?><p class="mtop15"><strong>Room PIN:</strong> <span class="label label-info"><?= html_escape($meeting->room_pin); ?></span></p><?php } ?>
      <label class="mtop15" for="gm-share-invitation">Full invitation</label><textarea id="gm-share-invitation" class="form-control" readonly rows="7"><?= html_escape($share['plain_text']); ?></textarea>
      <button class="btn btn-default mtop10" type="button" data-gm-copy="gm-share-invitation">Copy full invitation</button>
      <div class="gm-share-actions mtop15">
        <a class="btn btn-success" href="<?= html_escape($share['whatsapp_url']); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
        <a class="btn btn-default" href="<?= html_escape($share['mailto_url']); ?>">Email draft</a>
        <a class="btn btn-default" href="<?= html_escape($share['ics_url']); ?>">Download calendar</a>
        <button class="btn btn-info" type="button" id="gm-device-share" hidden>Share with device</button>
      </div>
      <p id="gm-share-feedback" class="mtop15" role="status" aria-live="polite"></p>
    </div>
  </div></div>
</div>
<script src="<?= module_dir_url('google_meet', 'assets/js/jitsi_meeting.js'); ?>?v=130" defer></script>

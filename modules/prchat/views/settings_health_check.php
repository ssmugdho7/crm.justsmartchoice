<?php defined('BASEPATH') or exit('No direct script access allowed');
$checks = [
  _l('chat_health_upload_folder') => is_dir(PR_CHAT_MODULE_UPLOAD_FOLDER) && is_writable(PR_CHAT_MODULE_UPLOAD_FOLDER),
  _l('chat_health_group_upload_folder') => is_dir(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER) && is_writable(PR_CHAT_MODULE_GROUPS_UPLOAD_FOLDER),
  _l('chat_health_audio_folder') => is_dir(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER) && is_writable(PR_CHAT_MODULE_AUDIO_UPLOAD_FOLDER),
  _l('chat_health_project_media_folder') => is_dir(PR_CHAT_MEDIA_PROJECTS_FOLDER) || @mkdir(PR_CHAT_MEDIA_PROJECTS_FOLDER, 0755, true),
  _l('chat_health_pusher') => get_option('pusher_chat_enabled') == '1',
  _l('chat_health_staff_calls') => get_option('chat_staff_calls_enabled') == '1',
  _l('chat_health_video_calls') => get_option('chat_calls_video_enabled') == '1',
]; ?>
<div class="prchat-settings-panel">
  <h4><i class="fa fa-heartbeat"></i> <?php echo _l('chat_health_check'); ?></h4>
  <p class="text-muted"><?php echo _l('chat_health_check_description'); ?></p>
  <table class="table table-bordered table-striped prchat-settings-table">
    <thead><tr><th><?php echo _l('chat_health_check_item'); ?></th><th><?php echo _l('chat_health_status'); ?></th></tr></thead>
    <tbody><?php foreach ($checks as $label => $ok) { ?><tr><td><?php echo html_escape($label); ?></td><td><span class="label label-<?php echo $ok ? 'success' : 'danger'; ?>"><?php echo $ok ? _l('chat_health_passed') : _l('chat_health_review'); ?></span></td></tr><?php } ?></tbody>
  </table>
  <div class="alert alert-info"><strong><?php echo _l('chat_project_media_path'); ?>:</strong> <?php echo html_escape(PR_CHAT_MEDIA_PROJECTS_FOLDER); ?></div>
</div>
<style>.prchat-settings-panel{max-width:100%;}.prchat-settings-table{table-layout:auto!important;width:100%!important}.prchat-settings-table th,.prchat-settings-table td{white-space:normal!important;vertical-align:middle!important}.prchat-settings-panel .btn{font-size:12px;padding:5px 9px;border-radius:6px}</style>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (function_exists('init_head')) { init_head(); } ?>
<div id="wrapper"><div class="content"><div class="google-meet-wrap">
  <div class="gm-hero"><div><h2><?php echo html_escape($meeting->subject); ?></h2><p><?php echo html_escape($meeting->description); ?></p></div><a class="btn btn-success" target="_blank" href="<?php echo html_escape($meeting->meet_link); ?>"><?php echo html_escape(google_meet_lang('google_meet_join', 'Join Google Meet')); ?></a></div>
  <div class="panel_s"><div class="panel-body"><p><b><?php echo html_escape(google_meet_lang('google_meet_start_time', 'Start Time')); ?>:</b> <?php echo _dt($meeting->start_time); ?></p><p><b><?php echo html_escape(google_meet_lang('google_meet_status', 'Status')); ?>:</b> <?php echo html_escape(ucfirst($meeting->status)); ?></p><p><b><?php echo html_escape(google_meet_lang('google_meet_description', 'Description')); ?>:</b> <?php echo html_escape($meeting->description); ?></p></div></div>
</div></div></div>
<?php if (function_exists('init_tail')) { init_tail(); } ?>

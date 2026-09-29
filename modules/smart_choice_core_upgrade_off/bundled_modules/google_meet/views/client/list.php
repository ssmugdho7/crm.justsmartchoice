<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (function_exists('init_head')) { init_head(); } ?>
<div id="wrapper"><div class="content"><div class="google-meet-wrap">
  <div class="gm-hero"><div><h2><?php echo html_escape(google_meet_lang('google_meet_my_meetings', 'My Meetings')); ?></h2><p>Your scheduled customer meetings.</p></div></div>
  <div class="panel_s"><div class="panel-body">
    <table class="table table-striped"><thead><tr><th><?php echo html_escape(google_meet_lang('google_meet_subject', 'Subject')); ?></th><th><?php echo html_escape(google_meet_lang('google_meet_start_time', 'Start Time')); ?></th><th><?php echo html_escape(google_meet_lang('google_meet_status', 'Status')); ?></th><th><?php echo html_escape(google_meet_lang('google_meet_join', 'Join Google Meet')); ?></th></tr></thead><tbody>
    <?php if (empty($meetings)) { ?><tr><td colspan="4" class="text-center text-muted"><?php echo html_escape(google_meet_lang('google_meet_no_meetings', 'No Google Meet meetings found.')); ?></td></tr><?php } ?>
    <?php foreach ((array)$meetings as $m) { ?><tr><td><a href="<?php echo site_url('google-meet-client/'.$m['id']); ?>"><?php echo html_escape($m['subject']); ?></a></td><td><?php echo _dt($m['start_time']); ?></td><td><?php echo html_escape(ucfirst($m['status'])); ?></td><td><a class="btn btn-info btn-xs" target="_blank" href="<?php echo html_escape($m['meet_link']); ?>"><?php echo html_escape(google_meet_lang('google_meet_join', 'Join Google Meet')); ?></a></td></tr><?php } ?>
    </tbody></table>
  </div></div>
</div></div></div>
<?php if (function_exists('init_tail')) { init_tail(); } ?>

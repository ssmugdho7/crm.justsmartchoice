<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (function_exists('init_head')) { init_head(); } $title = $meeting->subject ?? $meeting->title ?? 'Google Meet Meeting'; ?>
<div id="wrapper"><div class="content"><div class="google-meet-wrap smart-choice-normalized-module">
  <div class="gm-hero"><div><h2><?php echo html_escape($title); ?></h2><p><?php echo html_escape($meeting->description ?? ''); ?></p></div><?php if (!empty($meeting->meet_link)) { ?><a class="btn btn-success btn-sm" target="_blank" rel="noopener" href="<?php echo html_escape($meeting->meet_link); ?>">Join Google Meet</a><?php } ?></div>
  <div class="panel_s google-meet-card"><div class="panel-body">
    <p><strong>Start:</strong> <?php echo !empty($meeting->start_time) ? _dt($meeting->start_time) : ''; ?></p>
    <p><strong>End:</strong> <?php echo !empty($meeting->end_time) ? _dt($meeting->end_time) : ''; ?></p>
    <p><strong>Status:</strong> <?php echo html_escape(ucfirst($meeting->status ?? 'scheduled')); ?></p>
    <p><strong>Google Meet Link:</strong> <a target="_blank" rel="noopener" href="<?php echo html_escape($meeting->meet_link ?? ''); ?>"><?php echo html_escape($meeting->meet_link ?? ''); ?></a></p>
    <a class="btn btn-default btn-sm" href="<?php echo site_url('google-meet-client'); ?>">Back to Meetings</a>
  </div></div>
</div></div></div>
<?php if (function_exists('init_tail')) { init_tail(); } ?>

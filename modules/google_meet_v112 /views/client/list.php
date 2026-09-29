<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (function_exists('init_head')) { init_head(); } ?>
<div id="wrapper"><div class="content"><div class="google-meet-wrap smart-choice-normalized-module">
  <div class="gm-hero"><div><h2><?php echo html_escape(google_meet_lang('google_meet_my_meetings', 'My Video Meetings')); ?></h2><p>Your scheduled Smart Choice Contractors USA video meetings.</p></div></div>
  <div class="panel_s google-meet-card"><div class="panel-body">
    <div class="table-responsive"><table class="table table-striped gm-compact-table">
      <thead><tr><th>Meeting</th><th style="width:135px">Start</th><th style="width:90px">Status</th><th style="width:90px">Join</th><th style="width:90px">View</th></tr></thead><tbody>
      <?php if (empty($meetings)) { ?><tr><td colspan="5" class="text-center text-muted">No Google Meet meetings found.</td></tr><?php } ?>
      <?php foreach ((array)$meetings as $m) { $title = $m['subject'] ?? $m['title'] ?? 'Google Meet Meeting'; ?><tr>
        <td class="gm-meeting-cell"><strong><?php echo html_escape($title); ?></strong><small><?php echo html_escape($m['meet_link'] ?? ''); ?></small></td>
        <td><?php echo !empty($m['start_time']) ? _dt($m['start_time']) : ''; ?></td>
        <td><span class="gm-badge gm-badge-blue"><?php echo html_escape(ucfirst($m['status'] ?? 'scheduled')); ?></span></td>
        <td><?php if (!empty($m['meet_link'])) { ?><a class="btn btn-success btn-xs" target="_blank" rel="noopener" href="<?php echo html_escape($m['meet_link']); ?>">Join</a><?php } ?></td>
        <td><a class="btn btn-default btn-xs" href="<?php echo site_url('google-meet-client/'.$m['id']); ?>">View</a></td>
      </tr><?php } ?>
      </tbody></table></div>
  </div></div>
</div></div></div>
<?php if (function_exists('init_tail')) { init_tail(); } ?>

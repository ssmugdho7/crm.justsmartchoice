<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="sc-auto-page-heading"><i class="fa-solid fa-video"></i><span>Video Meeting</span></div>
<div class="panel_s">
  <div class="panel-body">
    <?php if (empty($meetings)) { ?>
      <div class="text-center tw-py-8">
        <i class="fa-solid fa-video tw-text-4xl" style="color:#3598DB"></i>
        <h4 class="tw-font-semibold tw-mt-3">No Video Meeting sessions are currently available</h4>
        <p class="text-muted">Scheduled meetings assigned to your account will appear here.</p>
      </div>
    <?php } else { ?>
      <div class="table-responsive"><table class="table table-hover">
        <thead><tr><th>Meeting</th><th>Date</th><th>Status</th><th class="text-right">Action</th></tr></thead><tbody>
        <?php foreach ($meetings as $meeting) {
          $title = $meeting['title'] ?? $meeting['subject'] ?? $meeting['name'] ?? 'Video Meeting';
          $date = $meeting['start_time'] ?? $meeting['start_date'] ?? $meeting['meeting_date'] ?? '';
          $url = $meeting['meet_url'] ?? $meeting['meeting_url'] ?? $meeting['url'] ?? $meeting['link'] ?? '';
          $status = $meeting['status'] ?? 'Scheduled'; ?>
          <tr><td><?= e($title); ?></td><td><?= e($date); ?></td><td><span class="label label-info"><?= e(ucwords((string)$status)); ?></span></td><td class="text-right"><?php if ($url) { ?><a class="btn btn-primary btn-sm" target="_blank" rel="noopener" href="<?= e($url); ?>"><i class="fa-solid fa-video"></i> Join</a><?php } ?></td></tr>
        <?php } ?>
        </tbody></table></div>
    <?php } ?>
  </div>
</div>

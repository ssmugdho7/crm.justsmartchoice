<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php echo sc_ai_video_studio_nav(); ?>

<div class="panel_s"><div class="panel-body">
<div class="scv-toolbar"><h4>Video Studio Reports</h4><div><a class="btn btn-default btn-sm" href="<?php echo current_url(); ?>">Reload</a><a class="btn btn-default btn-sm" onclick="window.print();return false;">Export</a></div></div>
<div class="scv-stats"><div><strong><?php echo (int)$counts['videos']; ?></strong><span>Videos</span></div><div><strong><?php echo (int)$counts['voices']; ?></strong><span>Voices</span></div><div><strong><?php echo (int)$counts['avatars']; ?></strong><span>Avatars</span></div></div>
<table class="table scv-table"><thead><tr><th>Video</th><th>Status</th><th>Language</th><th>Created</th><th>View</th></tr></thead><tbody><?php foreach ($videos as $video) { ?><tr><td><?php echo html_escape($video['title']); ?></td><td><?php echo html_escape($video['status']); ?></td><td><?php echo html_escape($video['language']); ?></td><td><?php echo html_escape($video['datecreated']); ?></td><td><a class="btn btn-default btn-xs" href="<?php echo admin_url('sc_ai_video_studio/view_video/' . $video['id']); ?>">View</a></td></tr><?php } ?></tbody></table>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>

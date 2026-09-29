<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php echo sc_ai_video_studio_nav(); ?>

<div class="panel_s"><div class="panel-body">
<div class="scv-toolbar"><h4><?php echo html_escape($video['title']); ?></h4><div><a class="btn btn-success btn-sm" href="<?php echo admin_url('sc_ai_video_studio/generate_video/' . $video['id']); ?>">Generate Video</a><a class="btn btn-default btn-sm" href="<?php echo admin_url('sc_ai_video_studio/video/' . $video['id']); ?>">Edit</a><a class="btn btn-default btn-sm" href="<?php echo admin_url('sc_ai_video_studio/videos'); ?>">Back</a></div></div>
<table class="table scv-table"><tbody><tr><th>Status</th><td><?php echo html_escape($video['status']); ?></td></tr><tr><th>Language</th><td><?php echo html_escape($video['language']); ?></td></tr><tr><th>Logo</th><td><?php echo ((int)$video['logo_enabled'] === 1) ? 'Enabled - ' . html_escape($video['logo_position']) : 'Disabled'; ?></td></tr><tr><th>Video URL</th><td><?php echo html_escape($video['video_url']); ?></td></tr></tbody></table>
<h5>Script</h5><div class="scv-script"><?php echo nl2br(html_escape($video['script_text'])); ?></div>
<h5>Text Layers</h5><table class="table scv-table"><thead><tr><th>Layer</th><th>Text</th><th>Start</th><th>Duration</th><th>Animation</th></tr></thead><tbody><?php foreach ($layers as $layer) { ?><tr><td><?php echo (int)$layer['layer_order']; ?></td><td><?php echo html_escape($layer['text_value']); ?></td><td><?php echo html_escape($layer['start_second']); ?></td><td><?php echo html_escape($layer['duration_second']); ?></td><td><?php echo html_escape($layer['animation']); ?></td></tr><?php } ?></tbody></table>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>

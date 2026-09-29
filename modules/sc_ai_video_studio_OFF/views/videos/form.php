<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php echo sc_ai_video_studio_nav(); ?>

<?php echo form_open(current_url()); ?>
<div class="panel_s"><div class="panel-body">
<div class="scv-toolbar"><h4><?php echo html_escape($title); ?></h4><div><button type="submit" class="btn btn-success btn-sm">Save Video</button><a class="btn btn-default btn-sm" href="<?php echo admin_url('sc_ai_video_studio/videos'); ?>">Back</a></div></div>
<div class="row">
<div class="col-md-6"><?php echo render_input('title', 'Video Title', $video['title'] ?? ''); ?></div>
<div class="col-md-3"><?php echo render_select('language', [['id'=>'English','name'=>'English'],['id'=>'Spanish','name'=>'Spanish']], ['id','name'], 'Language', $video['language'] ?? get_option('sc_ai_video_studio_default_language')); ?></div>
<div class="col-md-3"><?php echo render_select('status', [['id'=>'Draft','name'=>'Draft'],['id'=>'Ready for API','name'=>'Ready for API'],['id'=>'Generating','name'=>'Generating'],['id'=>'Completed','name'=>'Completed'],['id'=>'Failed','name'=>'Failed']], ['id','name'], 'Status', $video['status'] ?? 'Draft'); ?></div>
</div>
<div class="row">
<div class="col-md-6"><?php echo render_select('voice_id', $voices, ['id','voice_name'], 'Voice', $video['voice_id'] ?? ''); ?></div>
<div class="col-md-6"><?php echo render_select('avatar_id', $avatars, ['id','avatar_name'], 'Avatar', $video['avatar_id'] ?? ''); ?></div>
</div>
<?php echo render_textarea('script_text', 'Script Text', $video['script_text'] ?? '', ['rows'=>8]); ?>
<div class="row"><div class="col-md-3"><div class="checkbox checkbox-primary"><input type="checkbox" id="logo_enabled" name="logo_enabled" value="1" <?php echo !isset($video['logo_enabled']) || (int)$video['logo_enabled'] === 1 ? 'checked' : ''; ?>><label for="logo_enabled">Show Logo Watermark</label></div></div><div class="col-md-3"><?php echo render_select('logo_position', [['id'=>'Top Right','name'=>'Top Right'],['id'=>'Top Left','name'=>'Top Left'],['id'=>'Bottom Right','name'=>'Bottom Right'],['id'=>'Bottom Left','name'=>'Bottom Left']], ['id','name'], 'Logo Position', $video['logo_position'] ?? 'Top Right'); ?></div><div class="col-md-3"><?php echo render_input('intro_thumbnail_url', 'Intro Thumbnail URL', $video['intro_thumbnail_url'] ?? ''); ?></div><div class="col-md-3"><?php echo render_input('outro_thumbnail_url', 'Outro Thumbnail URL', $video['outro_thumbnail_url'] ?? ''); ?></div></div>
<h5>Text Overlays</h5>
<?php $layerMap=[]; foreach (($layers ?? []) as $layer) { $layerMap[(int)$layer['layer_order']]=$layer; } for ($i=1;$i<=4;$i++) { $layer=$layerMap[$i] ?? []; ?>
<div class="row scv-layer"><div class="col-md-5"><?php echo render_input('text_layer_'.$i, 'Text Layer '.$i, $layer['text_value'] ?? ''); ?></div><div class="col-md-2"><?php echo render_input('text_start_'.$i, 'Start Second', $layer['start_second'] ?? '0'); ?></div><div class="col-md-2"><?php echo render_input('text_duration_'.$i, 'Duration', $layer['duration_second'] ?? '3'); ?></div><div class="col-md-3"><?php echo render_select('text_animation_'.$i, [['id'=>'Fade In','name'=>'Fade In'],['id'=>'Fade Out','name'=>'Fade Out'],['id'=>'Slide Up','name'=>'Slide Up'],['id'=>'Zoom In','name'=>'Zoom In'],['id'=>'Roll In','name'=>'Roll In']], ['id','name'], 'Animation', $layer['animation'] ?? 'Fade In'); ?></div></div>
<?php } ?>
<div class="row"><div class="col-md-6"><?php echo render_input('video_url', 'Generated Video URL', $video['video_url'] ?? ''); ?></div><div class="col-md-6"><?php echo render_textarea('embed_code', 'Embed Code', $video['embed_code'] ?? '', ['rows'=>3]); ?></div></div>
</div></div>
<?php echo form_close(); ?>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>

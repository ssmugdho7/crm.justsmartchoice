<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php echo sc_ai_video_studio_nav(); ?>

<div class="panel_s"><div class="panel-body"><h4>Avatars</h4>
<?php echo form_open(current_url()); ?><div class="row"><div class="col-md-3"><?php echo render_input('avatar_name','Avatar Name'); ?></div><div class="col-md-2"><?php echo render_select('avatar_type', [['id'=>'Preset','name'=>'Preset'],['id'=>'Custom Upload','name'=>'Custom Upload']], ['id','name'], 'Type'); ?></div><div class="col-md-2"><?php echo render_input('position_name','Position','Front'); ?></div><div class="col-md-3"><?php echo render_input('image_url','Photo / Avatar Image URL'); ?></div><div class="col-md-1"><?php echo render_input('provider_avatar_id','Provider ID'); ?></div><div class="col-md-1"><button class="btn btn-success btn-sm scv-submit">Add</button></div></div><input type="hidden" name="is_active" value="1"><?php echo form_close(); ?>
<div class="table-responsive"><table class="table scv-table"><thead><tr><th>Name</th><th>Type</th><th>Position</th><th>Image</th><th>Provider ID</th><th>Active</th></tr></thead><tbody><?php foreach ($avatars as $avatar) { ?><tr><td><?php echo html_escape($avatar['avatar_name']); ?></td><td><?php echo html_escape($avatar['avatar_type']); ?></td><td><?php echo html_escape($avatar['position_name']); ?></td><td><?php echo html_escape($avatar['image_url']); ?></td><td><?php echo html_escape($avatar['provider_avatar_id']); ?></td><td><?php echo ((int)$avatar['is_active'] === 1) ? 'Yes' : 'No'; ?></td></tr><?php } ?></tbody></table></div></div></div>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>

<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php echo sc_ai_video_studio_nav(); ?>

<div class="panel_s"><div class="panel-body"><h4>Voices</h4>
<?php echo form_open(current_url()); ?><div class="row"><div class="col-md-3"><?php echo render_input('voice_name','Voice Name'); ?></div><div class="col-md-2"><?php echo render_select('language', [['id'=>'English','name'=>'English'],['id'=>'Spanish','name'=>'Spanish']], ['id','name'], 'Language'); ?></div><div class="col-md-2"><?php echo render_select('gender', [['id'=>'Male','name'=>'Male'],['id'=>'Female','name'=>'Female'],['id'=>'Neutral','name'=>'Neutral']], ['id','name'], 'Gender'); ?></div><div class="col-md-2"><?php echo render_input('tone','Tone','Professional'); ?></div><div class="col-md-2"><?php echo render_input('provider_voice_id','Provider Voice ID'); ?></div><div class="col-md-1"><button class="btn btn-success btn-sm scv-submit">Add</button></div></div><input type="hidden" name="is_active" value="1"><?php echo form_close(); ?>
<div class="table-responsive"><table class="table scv-table"><thead><tr><th>Name</th><th>Language</th><th>Gender</th><th>Tone</th><th>Provider ID</th><th>Active</th></tr></thead><tbody><?php foreach ($voices as $voice) { ?><tr><td><?php echo html_escape($voice['voice_name']); ?></td><td><?php echo html_escape($voice['language']); ?></td><td><?php echo html_escape($voice['gender']); ?></td><td><?php echo html_escape($voice['tone']); ?></td><td><?php echo html_escape($voice['provider_voice_id']); ?></td><td><?php echo ((int)$voice['is_active'] === 1) ? 'Yes' : 'No'; ?></td></tr><?php } ?></tbody></table></div></div></div>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>

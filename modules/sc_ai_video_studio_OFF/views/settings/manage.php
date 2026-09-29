<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php echo sc_ai_video_studio_nav(); ?>

<?php echo form_open(current_url()); ?><div class="panel_s"><div class="panel-body">
<div class="scv-toolbar"><h4>Settings</h4><div><button class="btn btn-success btn-sm">Save Settings</button><a class="btn btn-default btn-sm" href="<?php echo admin_url('sc_ai_video_studio/check_api_key'); ?>">Check API Key</a></div></div>
<div class="row"><div class="col-md-4"><?php echo render_input('provider','Video Provider',get_option('sc_ai_video_studio_provider')); ?></div><div class="col-md-8"><?php echo render_input('api_key','API Key',get_option('sc_ai_video_studio_api_key'),'text'); ?></div></div>
<div class="row"><div class="col-md-3"><?php echo render_select('logo_enabled', [['id'=>'1','name'=>'Yes'],['id'=>'0','name'=>'No']], ['id','name'], 'Show Logo by Default', get_option('sc_ai_video_studio_logo_enabled')); ?></div><div class="col-md-5"><?php echo render_input('logo_url','Logo URL',get_option('sc_ai_video_studio_logo_url')); ?></div><div class="col-md-2"><?php echo render_select('default_language', [['id'=>'English','name'=>'English'],['id'=>'Spanish','name'=>'Spanish']], ['id','name'], 'Default Language', get_option('sc_ai_video_studio_default_language')); ?></div><div class="col-md-2"><?php echo render_select('default_resolution', [['id'=>'720p','name'=>'720p'],['id'=>'1080p','name'=>'1080p'],['id'=>'4K','name'=>'4K']], ['id','name'], 'Resolution', get_option('sc_ai_video_studio_default_resolution')); ?></div></div>
<div class="alert alert-info">This module is API-ready. Add your selected avatar/video/TTS provider key here, then connect the provider endpoint in the generation service when selected.</div>
</div></div><?php echo form_close(); ?>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>

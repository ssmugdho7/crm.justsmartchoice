<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/css/training_manual_styles.css'); ?>">
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h4>Training Manual Settings</h4><hr>
<?php echo form_open(admin_url('training_manual/settings')); ?>
<div class="row">
<div class="col-md-4"><?php echo render_select('training_manual_default_language', [['id'=>'en','name'=>'English'],['id'=>'es','name'=>'Spanish'],['id'=>'mixed','name'=>'Mixed']], ['id',['name']], 'Default Language', get_option('training_manual_default_language')); ?></div>
<div class="col-md-4"><?php echo render_select('training_manual_default_style', [['id'=>'smart_choice','name'=>'Smart Choice'],['id'=>'clean','name'=>'Clean'],['id'=>'field','name'=>'Field Operations']], ['id',['name']], 'Default Style Preset', get_option('training_manual_default_style')); ?></div>
<div class="col-md-4"><div class="checkbox checkbox-primary mtop25"><input type="checkbox" name="training_manual_external_css_enabled" id="training_manual_external_css_enabled" value="1" <?php echo get_option('training_manual_external_css_enabled') == '1' ? 'checked' : ''; ?>><label for="training_manual_external_css_enabled">Enable External CSS Training Article Styling</label></div></div>
</div>
<div class="alert alert-info">For designed training articles, paste only the HTML body into the article editor and keep the CSS in <strong>modules/training_manual/assets/css/smart_choice_training_manual_content.css</strong>. This prevents the CRM editor from stripping colors and layout.</div>
<button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
<a href="<?php echo admin_url('training_manual/settings/health'); ?>" class="btn btn-default btn-sm">Health Check</a>
<?php echo form_close(); ?>
</div></div></div></div><?php init_tail(); ?></body></html>

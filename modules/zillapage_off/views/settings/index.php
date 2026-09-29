<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h4 class="tw-font-semibold tw-text-lg"><?php echo html_escape($title); ?></h4><hr class="hr-panel-heading">
<?php echo form_open($this->uri->uri_string(), ['id'=>'settings_form']); ?>
<div class="sc-preset-grid">
<?php $active = isset($settings['design_preset']) ? $settings['design_preset'] : 'default'; foreach($presets as $key=>$preset): ?>
<label class="sc-preset-card <?php echo $active===$key?'active':''; ?>"><input type="radio" name="design_preset" value="<?php echo html_escape($key); ?>" <?php echo $active===$key?'checked':''; ?>><span class="sc-preset-preview sc-<?php echo html_escape($key); ?>"></span><strong><?php echo html_escape($preset['name']); ?></strong></label>
<?php endforeach; ?>
</div>
<div class="tw-flex tw-gap-2 tw-mb-4"><button name="apply_preset" value="1" class="btn btn-success btn-sm"><?php echo _l('zillapage_apply_design'); ?></button><button name="reset_default" value="1" class="btn btn-default btn-sm"><?php echo _l('zillapage_reset_default'); ?></button></div>
<?php echo render_textarea('blockscss','blockscss',isset($blockscss->value)?$blockscss->value:'',['rows'=>12]); ?>
<div class="row"><div class="col-md-6"><?php echo render_input('header_library_url','zillapage_header_library_url',$settings['header_library_url']??'','url'); ?></div><div class="col-md-6"><?php echo render_input('footer_library_url','zillapage_footer_library_url',$settings['footer_library_url']??'','url'); ?></div></div>
<div class="row"><div class="col-md-4"><?php echo render_input('google_analytics_id','zillapage_google_analytics',$settings['google_analytics_id']??''); ?></div><div class="col-md-4"><?php echo render_input('google_tag_manager_id','zillapage_google_tag_manager',$settings['google_tag_manager_id']??''); ?></div><div class="col-md-4"><?php echo render_input('facebook_pixel_id','zillapage_facebook_pixel',$settings['facebook_pixel_id']??''); ?></div></div>
<div class="alert alert-info"><?php echo _l('zillapage_tracking_hidden_note'); ?></div>
<button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button><?php echo form_close(); ?>
</div></div></div></div>
<style>.sc-preset-grid{display:grid;grid-template-columns:repeat(4,minmax(150px,1fr));gap:14px;margin:18px 0}.sc-preset-card{border:1px solid #dce4ec;border-radius:10px;padding:10px;cursor:pointer;background:#fff}.sc-preset-card.active{border-color:#169179;box-shadow:0 0 0 2px rgba(22,145,121,.12)}.sc-preset-card input{margin-right:7px}.sc-preset-preview{display:block;height:76px;border-radius:8px;margin-bottom:8px}.sc-default{background:linear-gradient(135deg,#3598db,#169179)}.sc-executive{background:linear-gradient(135deg,#102d5c,#d96b00)}.sc-modern{background:linear-gradient(135deg,#1f3c88,#6dd5ed)}.sc-luxury{background:linear-gradient(135deg,#0d1b2a,#c89b3c)}@media(max-width:767px){.sc-preset-grid{grid-template-columns:1fr 1fr}}</style>
<?php init_tail(); ?>
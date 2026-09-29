<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content ga-module"><div class="row"><div class="col-md-8 col-md-offset-2"><div class="panel_s"><div class="panel-body"><h4><?php echo html_escape($title); ?></h4><hr><?php echo form_open(current_url()); ?>
<?php echo render_input('name','ga_website_name',$website->name??''); ?>
<?php echo render_input('website_url','ga_website_url',$website->website_url??'','url'); ?>
<?php echo render_input('measurement_id','ga_measurement_id',$website->measurement_id??'','text',['placeholder'=>'G-XXXXXXXXXX']); ?>
<?php echo render_input('property_id','ga_property_id',$website->property_id??'','text',['placeholder'=>'123456789']); ?>
<?php echo render_input('stream_id','ga_stream_id',$website->stream_id??''); ?>
<?php echo render_input('api_secret','ga_measurement_protocol_secret','','password',['autocomplete'=>'new-password']); ?>
<?php echo render_input('timezone','ga_timezone',$website->timezone??'America/New_York'); ?>
<div class="checkbox checkbox-primary"><input type="checkbox" name="active" id="active" value="1" <?php echo !isset($website)||$website->active?'checked':''; ?>><label for="active"><?php echo _l('active'); ?></label></div>
<div class="checkbox checkbox-primary"><input type="checkbox" name="is_default" id="is_default" value="1" <?php echo isset($website)&&$website->is_default?'checked':''; ?>><label for="is_default"><?php echo _l('ga_default_website'); ?></label></div>
<button class="btn btn-info"><i class="fa fa-check"></i> <?php echo _l('save'); ?></button> <a class="btn btn-default" href="<?php echo admin_url('google_analytics/websites'); ?>"><?php echo _l('cancel'); ?></a><?php echo form_close(); ?></div></div></div></div></div></div><?php init_tail(); ?></body></html>

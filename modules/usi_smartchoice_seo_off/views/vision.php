<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo sammy_ai_nav(); ?>
<div class="row">
<div class="col-md-4">
<div class="panel_s"><div class="panel-body">
<h4><?php echo _l('sammy_ai_new_vision_session'); ?></h4>
<?php echo form_open(admin_url('usi_smartchoice_seo/vision')); ?>
<div class="form-group"><label><?php echo _l('sammy_ai_title'); ?></label><input type="text" name="title" class="form-control" required></div>
<div class="form-group"><label><?php echo _l('sammy_ai_related_type'); ?></label><select name="related_type" class="form-control"><option value="lead">Lead</option><option value="customer">Customer</option><option value="project">Project</option><option value="estimate">Estimate</option></select></div>
<div class="form-group"><label><?php echo _l('sammy_ai_related_id'); ?></label><input type="number" name="related_id" class="form-control"></div>
<div class="form-group"><label><?php echo _l('sammy_ai_service_type'); ?></label><input type="text" name="service_type" class="form-control"></div>
<div class="form-group"><label><?php echo _l('sammy_ai_photo_notes'); ?></label><textarea name="photo_notes" rows="4" class="form-control"></textarea></div>
<div class="form-group"><label><?php echo _l('sammy_ai_measurement_notes'); ?></label><textarea name="measurement_notes" rows="4" class="form-control"></textarea></div>
<button type="submit" class="btn btn-primary btn-sm"><?php echo _l('sammy_ai_save'); ?></button>
<?php echo form_close(); ?>
</div></div>
</div>
<div class="col-md-8">
<div class="panel_s"><div class="panel-body">
<div class="clearfix m-b-sm"><h4 class="pull-left"><?php echo _l('sammy_ai_vision_engine'); ?></h4><div class="pull-right"><a href="<?php echo admin_url('usi_smartchoice_seo/vision'); ?>" class="btn btn-default btn-sm"><?php echo _l('sammy_ai_reload'); ?></a></div></div>
<div class="table-responsive"><table class="table table-striped table-condensed"><thead><tr><th><?php echo _l('sammy_ai_title'); ?></th><th><?php echo _l('sammy_ai_service_type'); ?></th><th><?php echo _l('sammy_ai_status'); ?></th><th><?php echo _l('sammy_ai_confidence'); ?></th><th><?php echo _l('sammy_ai_actions'); ?></th></tr></thead><tbody>
<?php foreach ($sessions as $session) { ?>
<tr><td><?php echo html_escape($session['title']); ?></td><td><?php echo html_escape($session['service_type']); ?></td><td><?php echo html_escape($session['status']); ?></td><td><?php echo html_escape($session['confidence']); ?>%</td><td><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_vision/' . (int) $session['id']); ?>"><?php echo _l('sammy_ai_view'); ?></a> <a class="btn btn-info btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/analyze_vision/' . (int) $session['id']); ?>"><?php echo _l('sammy_ai_analyze_photos'); ?></a></td></tr>
<?php } ?>
</tbody></table></div>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>

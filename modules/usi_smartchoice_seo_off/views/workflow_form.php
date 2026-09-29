<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-8 col-md-offset-2"><div class="panel_s"><div class="panel-body">
<h4><?php echo html_escape($title); ?></h4><hr class="hr-panel-heading" />
<?php echo form_open(current_url()); ?>
<div class="form-group"><label>Workflow Name</label><input type="text" name="workflow_name" class="form-control" value="<?php echo html_escape($workflow['workflow_name'] ?? ''); ?>" required></div>
<div class="row"><div class="col-md-6"><div class="form-group"><label>Workflow Type</label><input type="text" name="workflow_type" class="form-control" value="<?php echo html_escape($workflow['workflow_type'] ?? 'manual'); ?>"></div></div><div class="col-md-6"><div class="form-group"><label>Trigger Area</label><input type="text" name="trigger_area" class="form-control" value="<?php echo html_escape($workflow['trigger_area'] ?? 'manual'); ?>"></div></div></div>
<div class="row"><div class="col-md-6"><div class="form-group"><label>Trigger Event</label><input type="text" name="trigger_event" class="form-control" value="<?php echo html_escape($workflow['trigger_event'] ?? 'manual_run'); ?>"></div></div><div class="col-md-6"><div class="checkbox checkbox-primary mtop25"><input type="checkbox" name="is_active" id="is_active" <?php echo !isset($workflow['is_active']) || (int)$workflow['is_active'] === 1 ? 'checked' : ''; ?>><label for="is_active">Enabled</label></div></div></div>
<div class="form-group"><label>Conditions JSON</label><textarea name="conditions_json" class="form-control" rows="4"><?php echo html_escape($workflow['conditions_json'] ?? ''); ?></textarea></div>
<div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="4"><?php echo html_escape($workflow['description'] ?? ''); ?></textarea></div>
<button type="submit" class="btn btn-success btn-sm">Save Workflow</button> <a href="<?php echo admin_url('usi_smartchoice_seo/workflow_engine'); ?>" class="btn btn-default btn-sm">Cancel</a>
<?php echo form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?></body></html>

<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<h4><?php echo _l('smart_choice_crm_manager'); ?></h4><p class="text-muted"><?php echo _l('smart_choice_crm_manager_description'); ?></p>
<div class="alert alert-warning"><?php echo _l('smart_choice_crm_manager_warning'); ?></div>
<?php echo form_open_multipart(admin_url('smart_choice_crm_manager/upload')); ?><div class="form-group"><label><?php echo _l('smart_choice_crm_manager_package'); ?></label><input type="file" name="package" class="form-control" accept=".zip" required></div>
<button class="btn btn-primary" type="submit" onclick="return confirm('Create backup and apply this approved CRM update package?');"><i class="fa-solid fa-upload"></i> <?php echo _l('smart_choice_crm_manager_apply'); ?></button><?php echo form_close(); ?>
</div></div></div></div></div></div><?php init_tail(); ?>

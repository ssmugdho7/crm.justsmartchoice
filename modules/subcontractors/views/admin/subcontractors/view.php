<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo smartsource_admin_submenu('subcontractors'); ?>
<div class="row">
<div class="col-md-4"><div class="panel_s smartsource-panel"><div class="panel-body">
<?php if (!empty($subcontractor->profile_image)) { ?><p class="text-center"><img src="<?php echo base_url($subcontractor->profile_image); ?>" class="smartsource-profile-photo" alt="Subcontractor Profile Picture"></p><?php } ?>
<h4><?php echo html_escape($subcontractor->company); ?></h4>
<p><strong><?php echo _l('subcontractor_contact_name'); ?>:</strong> <?php echo html_escape($subcontractor->contact_name); ?></p>
<p><strong><?php echo _l('email'); ?>:</strong> <?php echo html_escape($subcontractor->email); ?></p>
<p><strong><?php echo _l('phone'); ?>:</strong> <?php echo html_escape($subcontractor->phone); ?></p>
<p><strong><?php echo _l('subcontractor_trade'); ?>:</strong> <?php echo html_escape($subcontractor->trade); ?></p>
<p><strong><?php echo _l('smartsource_category'); ?>:</strong> <?php echo html_escape($subcontractor->category); ?></p>
<p><strong><?php echo _l('subcontractor_license_number'); ?>:</strong> <?php echo html_escape($subcontractor->license_number); ?></p>
<?php if (!empty($subcontractor->dbpr_link)) { ?><p><a class="btn btn-default btn-block" target="_blank" href="<?php echo html_escape($subcontractor->dbpr_link); ?>"><i class="fa fa-external-link"></i> <?php echo _l('smartsource_check_dbpr_license'); ?></a></p><?php } ?>
<?php if (!empty($subcontractor->county_license_link)) { ?><p><a class="btn btn-default btn-block" target="_blank" href="<?php echo html_escape($subcontractor->county_license_link); ?>"><i class="fa fa-external-link"></i> <?php echo _l('smartsource_check_county_license'); ?></a></p><?php } ?>
<?php if (!empty($subcontractor->portal_token)) { ?><p><a class="btn btn-info btn-block" target="_blank" href="<?php echo smartsource_portal_profile_url($subcontractor->portal_token); ?>"><i class="fa fa-user"></i> Subcontractor Portal</a></p><?php } ?>
<hr><h5><?php echo _l('smartsource_company_documents'); ?></h5><?php if (empty($files)) { ?><p class="text-muted"><?php echo _l('no_files_found'); ?></p><?php } else { ?><ul class="smartsource-document-list"><?php foreach ($files as $file) { ?><li><a target="_blank" href="<?php echo smartsource_file_url($file); ?>"><?php echo html_escape($file['original_file_name'] ?: $file['file_name']); ?></a> <small><?php echo html_escape($file['dateadded']); ?></small></li><?php } ?></ul><?php } ?>
<?php if (function_exists('render_custom_fields_view')) { echo render_custom_fields_view('smartsource_subcontractors', $subcontractor->id); } ?>
<hr>
<a href="<?php echo admin_url('subcontractors/subcontractor/' . $subcontractor->id); ?>" class="btn btn-default"><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></a>
<a href="<?php echo admin_url('subcontractors/contract?subcontractor_id=' . $subcontractor->id); ?>" class="btn btn-primary"><i class="fa fa-file-text"></i> <?php echo _l('new_subcontractor_contract'); ?></a>
</div></div></div>
<div class="col-md-8">
<div class="panel_s smartsource-panel"><div class="panel-body">
<h4><?php echo _l('smartsource_subcontractor_contracts'); ?></h4>
<table class="table smartsource-data-table"><thead><tr><th><?php echo _l('subject'); ?></th><th><?php echo _l('project'); ?></th><th><?php echo _l('status'); ?></th><th><?php echo _l('value'); ?></th><th><?php echo _l('options'); ?></th></tr></thead><tbody>
<?php foreach ($contracts as $contract) { ?>
<tr><td><a href="<?php echo admin_url('subcontractors/contract_view/' . $contract['id']); ?>"><?php echo html_escape($contract['subject']); ?></a></td><td><?php echo !empty($contract['project_name']) ? html_escape($contract['project_name']) : (int)$contract['project_id']; ?></td><td><?php echo html_escape($contract['status']); ?></td><td><?php echo app_format_money($contract['contract_value'], get_base_currency()); ?></td><td><a class="btn btn-default btn-sm" href="<?php echo admin_url('subcontractors/contract_view/' . $contract['id']); ?>"><?php echo _l('view'); ?></a> <a class="btn btn-info btn-sm" href="<?php echo admin_url('subcontractors/contract/' . $contract['id']); ?>"><?php echo _l('edit'); ?></a></td></tr>
<?php } ?>
</tbody></table>
</div></div>
<div class="panel_s smartsource-panel"><div class="panel-body">
<h4><?php echo _l('subcontractor_files'); ?></h4>
<p class="text-muted"><?php echo _l('smartsource_uploaded_documents_notice'); ?></p>
<?php echo form_open_multipart(admin_url('subcontractors/upload_file/subcontractor/' . $subcontractor->id)); ?>
<input type="file" name="file[]" class="form-control" multiple required><br><div class="checkbox checkbox-primary"><input type="checkbox" name="visible_to_customer" id="visible_to_customer_subcontractor" value="1"><label for="visible_to_customer_subcontractor"><?php echo _l('smartsource_visible_to_customer'); ?></label></div><button class="btn btn-primary" type="submit"><i class="fa fa-upload"></i> <?php echo _l('smartsource_upload_file'); ?></button>
<?php echo form_close(); ?><hr>
<?php if (empty($files)) { ?><p class="text-muted"><?php echo _l('no_files_found'); ?></p><?php } ?>
<?php foreach ($files as $file) { ?>
<p><a target="_blank" href="<?php echo smartsource_file_url($file); ?>"><i class="fa fa-paperclip"></i> <?php echo html_escape($file['original_file_name'] ?: $file['file_name']); ?></a> <small class="text-muted"><?php echo html_escape($file['dateadded']); ?></small> <a href="<?php echo admin_url('subcontractors/delete_file/' . $file['id']); ?>" class="text-danger _delete"><i class="fa fa-remove"></i></a></p>
<?php } ?>
</div></div>
</div>
</div></div></div>
<?php init_tail(); ?>

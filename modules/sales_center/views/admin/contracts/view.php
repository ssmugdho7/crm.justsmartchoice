<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo sales_center_admin_submenu('contracts'); ?>

<div class="row">
<div class="col-md-8">
<div class="panel_s smartsource-panel"><div class="panel-body">
<div class="tw-flex tw-justify-between tw-items-center smartsource-action-header">
<h4><?php echo html_escape($contract->subject); ?></h4>
<div class="smartsource-action-buttons">
<a href="<?php echo admin_url('sales_center/contract_view/' . $contract->id); ?>" class="btn btn-default"><i class="fa fa-eye"></i> <?php echo _l('sales_center_view_contract'); ?></a>
<a href="<?php echo admin_url('sales_center/contract_pdf/' . $contract->id); ?>" class="btn btn-default" target="_blank"><i class="fa fa-file-pdf-o"></i> <?php echo _l('sales_center_pdf_new_tab'); ?></a>
<a href="<?php echo admin_url('sales_center/contract_print/' . $contract->id); ?>" class="btn btn-default" target="_blank"><i class="fa fa-print"></i> <?php echo _l('sales_center_download_print'); ?></a>
<a href="mailto:?subject=<?php echo rawurlencode($contract->subject); ?>&body=<?php echo rawurlencode(admin_url('sales_center/contract_view/' . $contract->id)); ?>" class="btn btn-success"><i class="fa fa-envelope"></i> <?php echo _l('sales_center_email_contract'); ?></a>
<a href="<?php echo admin_url('sales_center/contract/' . $contract->id); ?>" class="btn btn-info"><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></a>
<a href="<?php echo admin_url('sales_center/contract_delete/' . $contract->id); ?>" class="btn btn-danger _delete"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a>
</div>
</div><hr>
<ul class="nav nav-tabs" role="tablist">
<li role="presentation" class="active"><a href="#ss-content" aria-controls="ss-content" role="tab" data-toggle="tab"><?php echo _l('sales_center_contract_content'); ?></a></li>
<li role="presentation"><a href="#ss-signatures" aria-controls="ss-signatures" role="tab" data-toggle="tab"><?php echo _l('sales_center_signatures_initials'); ?></a></li>
<li role="presentation"><a href="#ss-merge" aria-controls="ss-merge" role="tab" data-toggle="tab"><?php echo _l('sales_center_merge_fields'); ?></a></li>
<li role="presentation"><a href="#ss-files" aria-controls="ss-files" role="tab" data-toggle="tab"><?php echo _l('salesperson_contract_files'); ?></a></li>
</ul>
<div class="tab-content mtop20">
<div role="tabpanel" class="tab-pane active" id="ss-content">
<p><strong><?php echo _l('sales_center_salesperson'); ?>:</strong> <a href="<?php echo admin_url('sales_center/view/' . $contract->salesperson_id); ?>"><?php echo html_escape($contract->salesperson_company); ?></a></p>
<p><strong><?php echo _l('project'); ?>:</strong> <?php echo !empty($contract->project_name) ? html_escape($contract->project_name) : (int)$contract->project_id; ?></p>
<p><strong><?php echo _l('sales_center_contract_type'); ?>:</strong> <?php echo html_escape($contract->contract_type); ?></p>
<p><strong><?php echo _l('status'); ?>:</strong> <?php echo html_escape(ucwords(str_replace('_',' ', $contract->status))); ?></p>
<p><strong><?php echo _l('sales_center_contract_value'); ?>:</strong> <?php echo app_format_money($contract->contract_value, get_base_currency()); ?></p>
<?php if (!empty($contract->hidden_from_customer)) { ?><p><span class="label label-warning"><?php echo _l('sales_center_hidden_from_customer'); ?></span></p><?php } ?>
<?php if (!empty($contract->is_trash)) { ?><p><span class="label label-danger"><?php echo _l('sales_center_trash'); ?></span></p><?php } ?>
<hr>
<div class="smartsource-contract-content"><?php echo $this->sales_center_model->render_contract_content($contract); ?></div>
<?php if (function_exists('render_custom_fields_view')) { echo render_custom_fields_view('sales_center_contracts', $contract->id); } ?>
</div>
<div role="tabpanel" class="tab-pane" id="ss-signatures">
<div class="row">
<div class="col-md-6"><div class="smartsource-signature-box"><h4><?php echo _l('sales_center_company_signature'); ?></h4><?php echo sales_center_signature_img($contract->company_signature ?? '', 'Company Signature'); ?><p><strong><?php echo _l('sales_center_initials'); ?>:</strong> <?php echo html_escape($contract->company_initials ?? ''); ?></p><p><small><?php echo html_escape($contract->company_signed_at ?? ''); ?> <?php echo html_escape($contract->company_signed_ip ?? ''); ?></small></p></div></div>
<div class="col-md-6"><div class="smartsource-signature-box"><h4><?php echo _l('sales_center_salesperson_signature'); ?></h4><?php echo sales_center_signature_img($contract->salesperson_signature ?? '', 'Salesperson Signature'); ?><p><strong><?php echo _l('sales_center_initials'); ?>:</strong> <?php echo html_escape($contract->salesperson_initials ?? ''); ?></p><p><small><?php echo html_escape($contract->salesperson_signed_at ?? ''); ?> <?php echo html_escape($contract->salesperson_signed_ip ?? ''); ?></small></p></div></div>
</div><hr>
<?php echo form_open(admin_url('sales_center/sign_contract/' . $contract->id)); ?>
<div class="row"><div class="col-md-4"><?php echo render_select('signature_role', [['id'=>'company','name'=>_l('sales_center_company_signature')], ['id'=>'salesperson','name'=>_l('sales_center_salesperson_signature')]], ['id','name'], 'sales_center_signature_role'); ?></div><div class="col-md-4"><?php echo render_input('initials', 'sales_center_initials', '', 'text', ['maxlength'=>'10']); ?></div></div>
<label><?php echo _l('sales_center_draw_signature'); ?></label>
<div class="smartsource-signature-pad"><canvas id="smartsourceSignatureCanvas" width="650" height="180"></canvas></div>
<input type="hidden" name="signature_data" id="signature_data">
<button type="button" class="btn btn-default" id="smartsourceClearSignature"><?php echo _l('clear'); ?></button>
<button type="submit" class="btn btn-primary" id="smartsourceSaveSignature"><i class="fa fa-check"></i> <?php echo _l('sales_center_save_signature'); ?></button>
<?php echo form_close(); ?>
</div>
<div role="tabpanel" class="tab-pane" id="ss-merge">
<p class="text-muted"><?php echo _l('sales_center_merge_fields_description'); ?></p>
<div class="smartsource-merge-grid">
<?php $fields = ['{salesperson_name}','{salesperson_email}','{salesperson_phone}','{salesperson_initials}','{salesperson_signature}','{company_initials}','{company_signature}','{contract_signed_stamp}','{contract_id}','{contract_subject}','{contract_value}','{project_name}','{companyname}','{crm_url}','{admin_url}','{logo_url}','{contact_firstname}','{contact_lastname}','{client_company}','{client_phone_number}','{contract_link}']; foreach($fields as $field){ echo '<code>'.html_escape($field).'</code>'; } ?>
</div>
</div>
<div role="tabpanel" class="tab-pane" id="ss-files">
<?php echo form_open_multipart(admin_url('sales_center/upload_file/contract/' . $contract->id)); ?>
<input type="file" name="file[]" class="form-control" multiple required><br>
<div class="checkbox checkbox-primary"><input type="checkbox" name="visible_to_customer" id="visible_to_customer_contract" value="1"><label for="visible_to_customer_contract"><?php echo _l('sales_center_visible_to_customer'); ?></label></div>
<button class="btn btn-primary" type="submit"><i class="fa fa-upload"></i> <?php echo _l('sales_center_upload_file'); ?></button>
<?php echo form_close(); ?><hr>
<?php if (empty($files)) { ?><p class="text-muted"><?php echo _l('no_files_found'); ?></p><?php } ?>
<?php foreach ($files as $file) { ?>
<p><a target="_blank" href="<?php echo sales_center_file_url($file); ?>"><i class="fa fa-paperclip"></i> <?php echo html_escape($file['original_file_name'] ?: $file['file_name']); ?></a> <small class="text-muted"><?php echo html_escape($file['dateadded']); ?></small> <a href="<?php echo admin_url('sales_center/delete_file/' . $file['id']); ?>" class="text-danger _delete"><i class="fa fa-remove"></i></a></p>
<?php } ?>
</div>
</div>
</div></div>
</div>
<div class="col-md-4"><div class="panel_s smartsource-panel"><div class="panel-body"><h4><?php echo _l('sales_center_quick_actions'); ?></h4><a class="btn btn-default btn-block" href="<?php echo admin_url('sales_center/contract_view/' . $contract->id); ?>"><?php echo _l('sales_center_view_contract'); ?></a><a class="btn btn-default btn-block" target="_blank" href="<?php echo admin_url('sales_center/contract_pdf/' . $contract->id); ?>"><?php echo _l('sales_center_view_pdf'); ?></a><a class="btn btn-default btn-block" target="_blank" href="<?php echo admin_url('sales_center/contract_print/' . $contract->id); ?>"><?php echo _l('sales_center_download_print'); ?></a><a class="btn btn-info btn-block" href="<?php echo admin_url('sales_center/contract/' . $contract->id); ?>"><?php echo _l('edit'); ?></a></div></div></div>
</div>
</div></div>
<?php init_tail(); ?>

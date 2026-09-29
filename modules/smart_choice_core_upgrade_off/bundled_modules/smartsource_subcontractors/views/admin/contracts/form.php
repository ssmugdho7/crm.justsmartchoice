<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo smartsource_admin_submenu('contracts'); ?>

<?php echo form_open($contract ? admin_url('smartsource_subcontractors/contract/' . $contract->id) : admin_url('smartsource_subcontractors/contract')); ?>
<div class="panel_s smartsource-panel"><div class="panel-body">
<h4><?php echo html_escape($title); ?></h4><hr>
<div class="row">
<div class="col-md-12"><?php echo render_input('subject', 'subject', $contract->subject ?? '', 'text', ['required'=>true]); ?></div>
<div class="col-md-6"><?php echo render_select('subcontractor_id', $subcontractors, ['id','company'], 'smartsource_subcontractor', $contract->subcontractor_id ?? $this->input->get('subcontractor_id'), ['required'=>true]); ?></div>
<div class="col-md-6"><?php echo render_select('project_id', $projects, ['id','name'], 'project', $contract->project_id ?? $this->input->get('project_id')); ?></div>
<div class="col-md-4"><?php echo render_input('contract_type', 'smartsource_contract_type', $contract->contract_type ?? '', 'text'); ?></div>
<div class="col-md-4"><?php echo render_input('contract_value', 'smartsource_contract_value', $contract->contract_value ?? '0.00', 'number', ['step'=>'0.01']); ?></div>
<div class="col-md-4"><?php echo render_select('status', $statuses, ['slug','name'], 'status', $contract->status ?? 'draft'); ?></div>
<div class="col-md-4"><?php echo render_date_input('start_date', 'start_date', $contract->start_date ?? ''); ?></div>
<div class="col-md-4"><?php echo render_date_input('end_date', 'end_date', $contract->end_date ?? ''); ?></div>
<div class="col-md-4"><?php echo render_date_input('signed_date', 'contract_signed_date', $contract->signed_date ?? ''); ?></div>
<div class="col-md-6"><?php echo render_input('company_initials', 'smartsource_company_initials', $contract->company_initials ?? '', 'text'); ?></div>
<div class="col-md-6"><?php echo render_input('subcontractor_initials', 'smartsource_subcontractor_initials', $contract->subcontractor_initials ?? '', 'text'); ?></div>
<div class="col-md-6"><div class="checkbox checkbox-primary"><input type="checkbox" name="hidden_from_customer" id="hidden_from_customer" value="1" <?php echo !empty($contract->hidden_from_customer) ? 'checked' : ''; ?>><label for="hidden_from_customer"><?php echo _l('smartsource_hidden_from_customer'); ?></label></div></div>
<div class="col-md-6"><div class="checkbox checkbox-danger"><input type="checkbox" name="is_trash" id="is_trash" value="1" <?php echo !empty($contract->is_trash) ? 'checked' : ''; ?>><label for="is_trash"><?php echo _l('smartsource_send_to_trash'); ?></label></div></div>
<div class="col-md-12"><?php echo render_textarea('description', 'description', $contract->description ?? '', ['rows'=>4]); ?></div>
<div class="col-md-12">
<label><?php echo _l('smartsource_insert_template'); ?></label>
<select id="smartsource-template-select" class="form-control">
<option value=""><?php echo _l('smartsource_select_template'); ?></option>
<?php foreach ($templates as $template) { ?><option value="<?php echo (int)$template['id']; ?>" data-content="<?php echo html_escape($template['content']); ?>"><?php echo html_escape($template['name']); ?></option><?php } ?>
</select><br>
</div>
<div class="col-md-12"><div class="alert alert-info"><strong><?php echo _l('smartsource_merge_fields'); ?>:</strong> <code>{subcontractor_name}</code> <code>{subcontractor_initials}</code> <code>{subcontractor_signature}</code> <code>{company_initials}</code> <code>{company_signature}</code> <code>{contract_signed_stamp}</code> <code>{project_name}</code></div></div>
<div class="col-md-12">
<label for="content"><?php echo _l('smartsource_contract_content'); ?></label>
<textarea name="content" id="content" class="tinymce" rows="18"><?php echo html_escape($contract->content ?? '<h2>Subcontractor Agreement</h2><p>This agreement is between Smart Choice Contractors USA and {subcontractor_name}.</p><p>Subcontractor Initials: {subcontractor_initials}</p><p>Company Initials: {company_initials}</p><h3>Signatures</h3><table style="width:100%;"><tr><td><strong>Company Signature</strong><br>{company_signature}</td><td><strong>Subcontractor Signature</strong><br>{subcontractor_signature}</td></tr></table>{contract_signed_stamp}'); ?></textarea>
</div>

<div class="col-md-12">
    <hr>
    <h5>Send Contract Notification</h5>
    <p class="text-muted">Choose who should receive a notification after this subcontractor contract is saved.</p>
    <div class="checkbox checkbox-primary"><input type="checkbox" name="send_to_subcontractor" id="send_to_subcontractor" value="1"><label for="send_to_subcontractor">Send To Subcontractor</label></div>
    <div class="checkbox checkbox-primary"><input type="checkbox" name="send_to_staff" id="send_to_staff" value="1"><label for="send_to_staff">Send To Employee / Staff</label></div>
    <div class="checkbox checkbox-primary"><input type="checkbox" name="send_to_customer" id="send_to_customer" value="1"><label for="send_to_customer">Send To Customer / Client</label></div>
</div>

<?php if (function_exists('render_custom_fields')) { ?>
<div class="col-md-12"><hr><?php echo render_custom_fields('smartsource_subcontractor_contracts', $contract->id ?? false); ?></div>
<?php } ?>
</div>
<button type="submit" class="btn btn-primary pull-right"><?php echo _l('submit'); ?></button>
</div></div>
<?php echo form_close(); ?>
</div></div>
<?php init_tail(); ?>

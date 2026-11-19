<?php defined('BASEPATH') or exit('No direct script access allowed');
$status = scaf_contract_workflow($contract);
?>
<?php if (is_admin() || staff_can('edit','contracts')): ?>
<li class="scaf-contract-action"><a href="#attachments" data-toggle="sc-contract-attachments" onclick="event.preventDefault();var tab=document.querySelector('a[aria-controls=attachments]');if(tab)tab.click();"><i class="fa fa-paperclip" aria-hidden="true"></i> <?= html_escape(_l('scaf_contract_attach_file')); ?></a></li>
<?php endif; ?>
<?php if (is_admin() || (staff_can('create','projects') && staff_can('edit','contracts'))): ?>
<li class="scaf-contract-action"><a href="<?= !empty($contract->project_id) ? admin_url('projects/view/'.(int)$contract->project_id) : admin_url('projects/project?via_contract_id='.(int)$contract->id); ?>"><?= html_escape(_l(!empty($contract->project_id)?'scaf_contract_view_project':'scaf_contract_convert_project')); ?></a></li>
<?php endif; ?>
<li class="divider" role="separator"></li>
<li class="dropdown-header scaf-contract-status"><?= html_escape(_l('scaf_contract_workflow')); ?><?= $status ? ': ' . html_escape(_l('scaf_contract_state_'.$status)) : ''; ?></li>
<?php if ((is_admin() || staff_can('edit','contracts')) && $status !== 'signed'): ?>
<?php foreach (scaf_contract_workflow_states() as $state): ?>
<li class="scaf-contract-action"><?= form_open(admin_url('sc_admin_fixes/contract_workflow/'.(int)$contract->id), ['class'=>'scaf-contract-action-form']); ?>
<input type="hidden" name="status" value="<?= html_escape($state); ?>">
<button type="submit" class="scaf-contract-action-button" <?= $status === $state ? 'disabled' : ''; ?>><?= html_escape(_l('scaf_contract_mark_'.$state)); ?></button>
<?= form_close(); ?></li>
<?php endforeach; ?>
<?php endif; ?>
<li class="scaf-contract-help"><?= html_escape(_l('scaf_contract_workflow_help')); ?></li>
<li class="divider" role="separator"></li>

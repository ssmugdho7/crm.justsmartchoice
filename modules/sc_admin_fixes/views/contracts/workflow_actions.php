<?php defined('BASEPATH') or exit('No direct script access allowed');
$status = scaf_contract_workflow($contract);
?>
<?php if (is_admin() || staff_can('edit','contracts')): ?>
<li><a href="#attachments" data-toggle="sc-contract-attachments" onclick="event.preventDefault();var tab=document.querySelector('a[aria-controls=attachments]');if(tab)tab.click();"><i class="fa fa-paperclip" aria-hidden="true"></i> <?= html_escape(_l('scaf_contract_attach_file')); ?></a></li>
<?php endif; ?>
<li class="divider" role="separator"></li>
<li class="dropdown-header"><?= html_escape(_l('scaf_contract_workflow')); ?><?= $status ? ': ' . html_escape(_l('scaf_contract_state_'.$status)) : ''; ?></li>
<?php if ((is_admin() || staff_can('edit','contracts')) && $status !== 'signed'): ?>
<?php foreach (scaf_contract_workflow_states() as $state): ?>
<li><?= form_open(admin_url('sc_admin_fixes/contract_workflow/'.(int)$contract->id), ['style'=>'margin:0']); ?>
<input type="hidden" name="status" value="<?= html_escape($state); ?>">
<button type="submit" class="btn btn-link" style="display:block;width:100%;text-align:left;padding:6px 20px" <?= $status === $state ? 'disabled' : ''; ?>><?= html_escape(_l('scaf_contract_mark_'.$state)); ?></button>
<?= form_close(); ?></li>
<?php endforeach; ?>
<?php endif; ?>
<li class="dropdown-header" style="max-width:280px;white-space:normal"><?= html_escape(_l('scaf_contract_workflow_help')); ?></li>
<li class="divider" role="separator"></li>

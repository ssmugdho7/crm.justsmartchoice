<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body scps-page">
<h4 class="no-margin"><?php echo html_escape($title); ?></h4><hr class="hr-panel-heading">
<div class="table-responsive"><table class="table dt-table"><thead><tr>
<th><?php echo _l('scps_source'); ?></th><th><?php echo _l('scps_contract_total'); ?></th><th><?php echo _l('scps_deposit'); ?></th><th><?php echo _l('scps_balance'); ?></th><th><?php echo _l('scps_deposit_invoice'); ?></th><th><?php echo _l('scps_balance_invoice'); ?></th><th><?php echo _l('options'); ?></th>
</tr></thead><tbody>
<?php foreach ($installments as $row) { ?><tr>
<td><?php echo html_escape(ucfirst($row->source_type) . ' #' . $row->source_id); ?></td>
<td><?php echo app_format_money($row->contract_total, get_base_currency()); ?></td>
<td><?php echo app_format_money($row->deposit_amount, get_base_currency()); ?> (<?php echo number_format($row->deposit_percent,2); ?>%)</td>
<td><?php echo app_format_money($row->balance_amount, get_base_currency()); ?></td>
<td><?php echo $row->deposit_invoice_id ? '<a href="'.admin_url('invoices/list_invoices/'.$row->deposit_invoice_id).'">#'.$row->deposit_invoice_id.'</a>' : '-'; ?></td>
<td><?php echo $row->balance_invoice_id ? '<a href="'.admin_url('invoices/list_invoices/'.$row->balance_invoice_id).'">#'.$row->balance_invoice_id.'</a>' : '-'; ?></td>
<td><?php if (!$row->balance_invoice_id && $row->balance_amount > 0) { ?><a class="btn btn-default btn-xs" href="<?php echo admin_url('smart_choice_payment_schedule/create_balance/'.$row->id); ?>"><?php echo _l('scps_create_balance'); ?></a><?php } ?></td>
</tr><?php } ?>
</tbody></table></div>
</div></div></div></div>
<?php init_tail(); ?>

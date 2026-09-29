<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo sales_center_admin_submenu('salespersons'); ?>
<div class="panel_s smartsource-panel smartsource-filter-scope"><div class="panel-body">
<div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
<div><h4 class="tw-m-0">Salespersons</h4><p class="text-muted tw-mb-0">Manage salesperson companies, trades, licenses, insurance, documents, contacts, categories, and project relationships.</p></div>
<div><a href="<?php echo admin_url('sales_center'); ?>" class="btn btn-default"><i class="fa fa-refresh"></i> <?php echo _l('refresh'); ?></a><?php if (has_permission('sales_center', '', 'create')) { ?> <a href="<?php echo admin_url('sales_center/salesperson'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> New Salesperson</a><?php } ?></div>
</div>
<div class="row smartsource-stats-row">
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('sales_center?status=active'); ?>"><div class="smartsource-stat"><strong><?php echo (int)$stats['active_salespersons']; ?></strong><span>Active Salespersons</span></div></a></div>
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('sales_center'); ?>"><div class="smartsource-stat"><strong><?php echo (int)$stats['total_salespersons']; ?></strong><span>Total Salespersons</span></div></a></div>
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('sales_center?insurance=expiring'); ?>"><div class="smartsource-stat warning"><strong><?php echo (int)$stats['expiring_insurance']; ?></strong><span>Insurance Expiring</span></div></a></div>
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('sales_center/contracts?include_trash=1&stat=trash'); ?>"><div class="smartsource-stat muted"><strong><?php echo (int)$stats['trash_contracts']; ?></strong><span>Contracts In Trash</span></div></a></div>
</div><hr>
<div class="smartsource-filter-box">
    <div class="row">
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Company" data-smartsource-filter="company"></div>
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Contact" data-smartsource-filter="contact"></div>
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Trade" data-smartsource-filter="trade"></div>
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Status" data-smartsource-filter="status"></div>
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Insurance" data-smartsource-filter="insurance"></div>
        <div class="col-md-2"><button type="button" class="btn btn-default btn-block smartsource-clear-filters"><i class="fa fa-eraser"></i> Clear</button></div>
    </div>
</div>

<table class="table smartsource-data-table smartsource-table"><thead><tr><th>Company</th><th>Contact Name</th><th>Trade</th><th><?php echo _l('status'); ?></th><th>Insurance Expiration</th><th><?php echo _l('options'); ?></th></tr></thead><tbody>
<?php foreach ($salespersons as $salesperson) { ?>
<tr data-company="<?php echo html_escape($salesperson['company']); ?>" data-contact="<?php echo html_escape($salesperson['contact_name']); ?>" data-trade="<?php echo html_escape($salesperson['trade']); ?>" data-status="<?php echo html_escape($salesperson['status']); ?>" data-insurance="<?php echo html_escape($salesperson['insurance_expiration']); ?>"><td><a href="<?php echo admin_url('sales_center/view/' . $salesperson['id']); ?>"><?php echo html_escape($salesperson['company']); ?></a></td><td><?php echo html_escape($salesperson['contact_name']); ?></td><td><?php echo html_escape($salesperson['trade']); ?></td><td><span class="label label-info"><?php echo html_escape(ucwords(str_replace('_',' ', $salesperson['status']))); ?></span></td><td><?php echo html_escape($salesperson['insurance_expiration']); ?></td><td><div class="smartsource-actions"><a class="btn btn-default btn-sm" href="<?php echo admin_url('sales_center/view/' . $salesperson['id']); ?>"><i class="fa fa-eye"></i> <?php echo _l('view'); ?></a> <?php if (has_permission('sales_center', '', 'edit')) { ?><a class="btn btn-info btn-sm" href="<?php echo admin_url('sales_center/salesperson/' . $salesperson['id']); ?>"><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></a><?php } ?> <?php if (has_permission('sales_center', '', 'delete')) { ?><a class="btn btn-danger btn-sm _delete" href="<?php echo admin_url('sales_center/delete/' . $salesperson['id']); ?>"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a><?php } ?></div></td></tr>
<?php } ?>
</tbody></table>
</div></div></div></div>
<?php init_tail(); ?>

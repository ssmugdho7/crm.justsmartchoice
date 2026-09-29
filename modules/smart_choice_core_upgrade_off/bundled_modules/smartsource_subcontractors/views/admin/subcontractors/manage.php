<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo smartsource_admin_submenu('subcontractors'); ?>
<div class="panel_s smartsource-panel smartsource-filter-scope"><div class="panel-body">
<div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
<div><h4 class="tw-m-0">Subcontractors</h4><p class="text-muted tw-mb-0">Manage subcontractor companies, trades, licenses, insurance, documents, contacts, categories, and project relationships.</p></div>
<div><a href="<?php echo admin_url('smartsource_subcontractors'); ?>" class="btn btn-default"><i class="fa fa-refresh"></i> <?php echo _l('refresh'); ?></a><?php if (has_permission('smartsource_subcontractors', '', 'create')) { ?> <a href="<?php echo admin_url('smartsource_subcontractors/subcontractor'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> New Subcontractor</a><?php } ?></div>
</div>
<div class="row smartsource-stats-row">
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('smartsource_subcontractors?status=active'); ?>"><div class="smartsource-stat"><strong><?php echo (int)$stats['active_subcontractors']; ?></strong><span>Active Subcontractors</span></div></a></div>
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('smartsource_subcontractors'); ?>"><div class="smartsource-stat"><strong><?php echo (int)$stats['total_subcontractors']; ?></strong><span>Total Subcontractors</span></div></a></div>
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('smartsource_subcontractors?insurance=expiring'); ?>"><div class="smartsource-stat warning"><strong><?php echo (int)$stats['expiring_insurance']; ?></strong><span>Insurance Expiring</span></div></a></div>
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('smartsource_subcontractors/contracts?include_trash=1&stat=trash'); ?>"><div class="smartsource-stat muted"><strong><?php echo (int)$stats['trash_contracts']; ?></strong><span>Contracts In Trash</span></div></a></div>
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
<?php foreach ($subcontractors as $subcontractor) { ?>
<tr data-company="<?php echo html_escape($subcontractor['company']); ?>" data-contact="<?php echo html_escape($subcontractor['contact_name']); ?>" data-trade="<?php echo html_escape($subcontractor['trade']); ?>" data-status="<?php echo html_escape($subcontractor['status']); ?>" data-insurance="<?php echo html_escape($subcontractor['insurance_expiration']); ?>"><td><a href="<?php echo admin_url('smartsource_subcontractors/view/' . $subcontractor['id']); ?>"><?php echo html_escape($subcontractor['company']); ?></a></td><td><?php echo html_escape($subcontractor['contact_name']); ?></td><td><?php echo html_escape($subcontractor['trade']); ?></td><td><span class="label label-info"><?php echo html_escape(ucwords(str_replace('_',' ', $subcontractor['status']))); ?></span></td><td><?php echo html_escape($subcontractor['insurance_expiration']); ?></td><td><div class="smartsource-actions"><a class="btn btn-default btn-sm" href="<?php echo admin_url('smartsource_subcontractors/view/' . $subcontractor['id']); ?>"><i class="fa fa-eye"></i> <?php echo _l('view'); ?></a> <?php if (has_permission('smartsource_subcontractors', '', 'edit')) { ?><a class="btn btn-info btn-sm" href="<?php echo admin_url('smartsource_subcontractors/subcontractor/' . $subcontractor['id']); ?>"><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></a><?php } ?> <?php if (has_permission('smartsource_subcontractors', '', 'delete')) { ?><a class="btn btn-danger btn-sm _delete" href="<?php echo admin_url('smartsource_subcontractors/delete/' . $subcontractor['id']); ?>"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a><?php } ?></div></td></tr>
<?php } ?>
</tbody></table>
</div></div></div></div>
<?php init_tail(); ?>

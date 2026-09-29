<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smartsource-contracts-page"><?php echo smartsource_admin_submenu('contracts'); ?>
<div class="panel_s"><div class="panel-body">
    <div class="smartsource-action-header">
        <div><h4 class="bold">Subcontractor Contracts</h4><p class="text-muted">Integrated contract list with batch actions, filters, import tools, export, refresh, and mass delete.</p></div>
        <div class="smartsource-action-buttons">
            <a href="<?php echo admin_url('subcontractors/contracts_sample_header'); ?>" class="btn btn-default"><i class="fa fa-download"></i> Sample Header</a>
            <a href="<?php echo admin_url('subcontractors/contracts_export_csv'); ?>" class="btn btn-default"><i class="fa fa-file-excel-o"></i> Export</a>
            <button class="btn btn-default" type="button" onclick="window.location.reload();"><i class="fa fa-refresh"></i> Reload</button>
            <button class="btn btn-default" type="button" id="smartsourceToggleContracts"><i class="fa fa-columns"></i> Toggle Table</button>
            <button class="btn btn-default" type="button" disabled title="Import shell ready for next upgrade"><i class="fa fa-upload"></i> Import</button>
            <?php if (has_permission('smartsource_subcontractor_contracts', '', 'create')) { ?><a href="<?php echo admin_url('subcontractors/contract'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> New Contract</a><?php } ?>
        </div>
    </div>
    <hr>
    <div class="row smartsource-stats-row">
        <div class="col-md-2"><a class="smartsource-stat-link" href="<?php echo admin_url('subcontractors/contracts?stat=active'); ?>"><div class="smartsource-stat"><strong><?php echo (int)$stats['active_contracts']; ?></strong><span>Active Contracts</span></div></a></div>
        <div class="col-md-2"><a class="smartsource-stat-link" href="<?php echo admin_url('subcontractors/contracts?stat=expired'); ?>"><div class="smartsource-stat danger"><strong><?php echo (int)$stats['expired_contracts']; ?></strong><span>Expired</span></div></a></div>
        <div class="col-md-2"><a class="smartsource-stat-link" href="<?php echo admin_url('subcontractors/contracts?stat=expiring'); ?>"><div class="smartsource-stat warning"><strong><?php echo (int)$stats['expiring_contracts']; ?></strong><span>Expiring Soon</span></div></a></div>
        <div class="col-md-2"><div class="smartsource-stat warning"><strong><?php echo (int)$stats['expiring_insurance']; ?></strong><span>Insurance Expiring</span></div></div>
        <div class="col-md-2"><div class="smartsource-stat"><strong><?php echo app_format_money($stats['total_contract_value'], get_base_currency()); ?></strong><span>Total Value</span></div></div>
        <div class="col-md-2"><a class="smartsource-stat-link" href="<?php echo admin_url('subcontractors/contracts?stat=trash&include_trash=1'); ?>"><div class="smartsource-stat muted"><strong><?php echo (int)$stats['trash_contracts']; ?></strong><span>Trash</span></div></a></div>
    </div>
    <hr>
    <form method="get" class="row smartsource-filter-box">
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Search Subject" data-smartsource-filter="subject"></div>
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Search Subcontractor" data-smartsource-filter="subcontractor"></div>
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Search Project" data-smartsource-filter="project"></div>
        <div class="col-md-2"><input type="text" name="contract_type" class="form-control" placeholder="Contract Type" data-smartsource-filter="type" value="<?php echo html_escape($this->input->get('contract_type')); ?>"></div>
        <div class="col-md-2"><select name="status" class="form-control" data-smartsource-filter="status"><option value="">All Statuses</option><?php foreach ($statuses as $status) { ?><option value="<?php echo html_escape($status['slug']); ?>" <?php echo $this->input->get('status') === $status['slug'] ? 'selected' : ''; ?>><?php echo html_escape($status['name']); ?></option><?php } ?></select></div>
        <div class="col-md-2"><select name="include_trash" class="form-control" data-smartsource-filter="trash"><option value="0">Hide Trash</option><option value="1" <?php echo $this->input->get('include_trash') === '1' ? 'selected' : ''; ?>>Show Trash</option></select></div>
        <div class="col-md-12 mtop10"><button class="btn btn-info" type="submit"><i class="fa fa-filter"></i> Filter</button> <button type="button" class="btn btn-default smartsource-clear-filters"><i class="fa fa-eraser"></i> Clear Table Filters</button></div>
    </form>
    <hr>
    <?php echo form_open(admin_url('subcontractors/contract_bulk_action'), ['id'=>'smartsource-contract-bulk-form']); ?>
    <div class="smartsource-bulk-toolbar">
        <select name="bulk_action" class="form-control input-sm" style="width:220px;display:inline-block"><option value="">Bulk Action</option><option value="mark_sent">Mark As Sent</option><option value="mark_cancelled">Mark As Cancelled</option><option value="delete">Mass Delete</option></select>
        <button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-check"></i> Apply</button>
        <span class="text-muted">Select rows with checkmarks before applying a batch action.</span>
    </div>
    <div class="table-responsive" id="smartsourceContractsTableWrap"><table class="table smartsource-data-table smartsource-table">
        <thead><tr><th width="35"><input type="checkbox" id="smartsource-check-all"></th><th>Subject</th><th>Subcontractor</th><th>Project</th><th>Contract Type</th><th>Status</th><th>Value</th><th>Options</th></tr></thead>
        <tbody><?php foreach ($contracts as $contract) { ?><tr class="<?php echo !empty($contract['is_trash']) ? 'text-muted smartsource-trash-row' : ''; ?>" data-subject="<?php echo html_escape($contract['subject']); ?>" data-subcontractor="<?php echo html_escape($contract['subcontractor_company']); ?>" data-project="<?php echo !empty($contract['project_name']) ? html_escape($contract['project_name']) : (int)$contract['project_id']; ?>" data-type="<?php echo html_escape($contract['contract_type']); ?>" data-status="<?php echo html_escape($contract['status']); ?>" data-trash="<?php echo !empty($contract['is_trash']) ? '1' : '0'; ?>">
            <td><input type="checkbox" name="ids[]" value="<?php echo (int)$contract['id']; ?>" class="smartsource-row-check"></td>
            <td><a href="<?php echo admin_url('subcontractors/contract_view/' . $contract['id']); ?>"><?php echo html_escape($contract['subject']); ?></a><?php if (!empty($contract['hidden_from_customer'])) { ?> <span class="label label-warning">Hidden</span><?php } ?></td>
            <td class="text-left"><a href="<?php echo admin_url('subcontractors/view/' . $contract['subcontractor_id']); ?>"><?php echo html_escape($contract['subcontractor_company']); ?></a></td>
            <td><?php echo !empty($contract['project_name']) ? html_escape($contract['project_name']) : (int)$contract['project_id']; ?></td>
            <td><?php echo html_escape($contract['contract_type']); ?></td>
            <td><span class="label label-info"><?php echo html_escape(ucwords(str_replace('_', ' ', $contract['status']))); ?></span></td>
            <td><?php echo app_format_money($contract['contract_value'], get_base_currency()); ?></td>
            <td><div class="smartsource-contract-actions"><a class="btn btn-default btn-sm" href="<?php echo admin_url('subcontractors/contract_view/' . $contract['id']); ?>"><i class="fa fa-eye"></i> View</a><?php if (has_permission('smartsource_subcontractor_contracts', '', 'edit')) { ?><a class="btn btn-info btn-sm" href="<?php echo admin_url('subcontractors/contract/' . $contract['id']); ?>"><i class="fa fa-pencil"></i> Edit</a><?php } ?><div class="btn-group"><button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">More <span class="caret"></span></button><ul class="dropdown-menu dropdown-menu-right"><li><a target="_blank" href="<?php echo admin_url('subcontractors/contract_client_view/' . $contract['id']); ?>">View as a Customer</a></li><li><a href="<?php echo admin_url('subcontractors/contract_copy/' . $contract['id']); ?>">Copy Contract</a></li><li><a href="<?php echo admin_url('subcontractors/contract_mark_sent/' . $contract['id']); ?>">Mark as Sent</a></li><li><a href="<?php echo admin_url('subcontractors/contract_mark_cancelled/' . $contract['id']); ?>">Mark as Cancelled</a></li><?php if (has_permission('smartsource_subcontractor_contracts', '', 'delete')) { ?><li class="divider"></li><li><a class="_delete text-danger" href="<?php echo admin_url('subcontractors/contract_delete/' . $contract['id']); ?>">Delete</a></li><?php } ?></ul></div></div></td>
        </tr><?php } ?></tbody>
    </table></div><?php echo form_close(); ?>
</div></div></div></div>
<script>
(function(){
    document.addEventListener('change', function(e){ if(e.target && e.target.id === 'smartsource-check-all'){ document.querySelectorAll('.smartsource-row-check').forEach(function(cb){ cb.checked = e.target.checked; }); }});
    document.addEventListener('submit', function(e){ if(e.target && e.target.id === 'smartsource-contract-bulk-form'){ if(!document.querySelector('.smartsource-row-check:checked')){ e.preventDefault(); alert('Select at least one contract first.'); } }});
    var toggle=document.getElementById('smartsourceToggleContracts'); if(toggle){ toggle.addEventListener('click', function(){ var w=document.getElementById('smartsourceContractsTableWrap'); if(w){ w.style.display = w.style.display === 'none' ? '' : 'none'; }}); }
})();
window.smartsourceChartData = <?php echo json_encode($chart_data ?? []); ?>;
</script>
<?php init_tail(); ?>

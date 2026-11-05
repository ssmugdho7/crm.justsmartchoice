<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo sales_center_admin_submenu('salespersons'); ?>
<div class="panel_s smartsource-panel smartsource-filter-scope"><div class="panel-body">
<div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
<div><h4 class="tw-m-0">Salespersons</h4><p class="text-muted tw-mb-0">Manage sales representatives, markets, commission rates, goals, agreements, and project relationships.</p></div>
<div><a href="<?php echo admin_url('sales_center'); ?>" class="btn btn-default"><i class="fa fa-refresh"></i> <?php echo _l('refresh'); ?></a><?php if (has_permission('sales_center', '', 'create')) { ?> <a href="<?php echo admin_url('sales_center/salesperson'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> New Salesperson</a><?php } ?></div>
</div>
<div class="row smartsource-stats-row">
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('sales_center?status=active'); ?>"><div class="smartsource-stat"><strong><?php echo (int)$stats['active_salespersons']; ?></strong><span>Active Closers</span></div></a></div>
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('sales_center'); ?>"><div class="smartsource-stat"><strong><?php echo (int)$stats['total_salespersons']; ?></strong><span>Total Salespersons</span></div></a></div>
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('sales_center'); ?>"><div class="smartsource-stat warning"><strong><?php $rates = array_column($salespersons, 'commission_rate'); echo number_format($rates ? array_sum($rates) / count($rates) : 0, 2); ?>%</strong><span>Average Commission Rate</span></div></a></div>
<div class="col-md-3"><a class="smartsource-stat-link" href="<?php echo admin_url('sales_center'); ?>"><div class="smartsource-stat muted"><strong><?php echo app_format_money(array_sum(array_column($salespersons, 'sales_goal')), get_base_currency()); ?></strong><span>Configured Sales Goals</span></div></a></div>
</div><hr>
<div class="smartsource-filter-box">
    <div class="row">
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Company" data-smartsource-filter="company"></div>
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Contact" data-smartsource-filter="contact"></div>
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Trade" data-smartsource-filter="trade"></div>
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Status" data-smartsource-filter="status"></div>
        <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Market" data-smartsource-filter="market"></div>
        <div class="col-md-2"><button type="button" class="btn btn-default btn-block smartsource-clear-filters"><i class="fa fa-eraser"></i> Clear</button></div>
    </div>
</div>

<table class="table smartsource-data-table smartsource-table"><thead><tr><th>Salesperson &amp; Firm</th><th>Commission Rate</th><th>Focus Trade</th><th><?php echo _l('status'); ?></th><th>Market / Sales Goal</th><th><?php echo _l('options'); ?></th></tr></thead><tbody>
<?php foreach ($salespersons as $salesperson) { ?>
<tr data-company="<?php echo html_escape($salesperson['company']); ?>" data-contact="<?php echo html_escape($salesperson['contact_name']); ?>" data-trade="<?php echo html_escape($salesperson['trade']); ?>" data-status="<?php echo html_escape($salesperson['status']); ?>" data-market="<?php echo html_escape(trim(($salesperson['city'] ?? '') . ' ' . ($salesperson['state'] ?? ''))); ?>"><td><a href="<?php echo admin_url('sales_center/view/' . $salesperson['id']); ?>"><?php $repName = trim($salesperson['contact_name']) ?: $salesperson['company']; $parts = preg_split('/\s+/', $repName); ?><span class="sc-rep-avatar" aria-hidden="true"><?php echo html_escape(mb_substr($parts[0], 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '')); ?></span> <?php echo html_escape($repName); ?></a><small class="display-block text-muted"><?php echo html_escape($salesperson['company']); ?></small></td><td><?php echo number_format((float)($salesperson['commission_rate'] ?? 0), 2); ?>%</td><td><?php echo html_escape($salesperson['trade']); ?></td><td><span class="label label-info"><?php echo html_escape(ucwords(str_replace('_',' ', $salesperson['status']))); ?></span></td><td><?php echo html_escape(trim(($salesperson['city'] ?? '') . ' ' . ($salesperson['state'] ?? '')) ?: 'Not configured'); ?><small class="display-block text-muted">Goal: <?php echo app_format_money($salesperson['sales_goal'] ?? 0, get_base_currency()); ?></small></td><td><div class="smartsource-actions"><a class="btn btn-default btn-sm" href="<?php echo admin_url('sales_center/view/' . $salesperson['id']); ?>"><i class="fa fa-eye"></i> <?php echo _l('view'); ?></a> <?php if (has_permission('sales_center', '', 'edit')) { ?><a class="btn btn-info btn-sm" href="<?php echo admin_url('sales_center/salesperson/' . $salesperson['id']); ?>"><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></a><?php } ?> <?php if (has_permission('sales_center', '', 'delete')) { ?><a class="btn btn-danger btn-sm _delete" href="<?php echo admin_url('sales_center/delete/' . $salesperson['id']); ?>"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a><?php } ?></div></td></tr>
<?php } ?>
</tbody></table>
</div></div></div></div>
<?php init_tail(); ?>

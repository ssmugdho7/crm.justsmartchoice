<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo sales_center_admin_submenu('reports'); ?>
        <div class="panel_s smartsource-panel smartsource-filter-scope">
            <div class="panel-body">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 smartsource-header-row">
                    <div>
                        <h4 class="tw-m-0">Sales Reports</h4>
                        <p class="text-muted tw-mb-0">Review sales totals, collected payments, unpaid balances, commission earned, commission paid, and commission owed by sales representative.</p>
                    </div>
                    <div>
                        <a href="<?php echo admin_url('sales_center/reports'); ?>" class="btn btn-default"><i class="fa fa-refresh"></i> Refresh</a>
                        <button type="button" class="btn btn-default" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
                    </div>
                </div>

                <?php echo form_open(admin_url('sales_center/reports'), ['method' => 'get', 'class' => 'smartsource-table-filters']); ?>
                <div class="row">
                    <div class="col-md-3">
                        <?php echo render_select('salesperson_id', $salespeople, ['id', 'company'], 'Sales Representative', $filters['salesperson_id'] ?? ''); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_select('manager_id', $sales_managers, ['id', 'company'], 'Sales Manager / Director', $filters['manager_id'] ?? ''); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_select('department_id', $departments, ['id', 'name'], 'Department', $filters['department_id'] ?? ''); ?>
                    </div>
                    <div class="col-md-3">
                        <?php echo render_select('status', [
                            ['id' => 'pending', 'name' => 'Pending'],
                            ['id' => 'open', 'name' => 'Open'],
                            ['id' => 'partial', 'name' => 'Partial'],
                            ['id' => 'paid', 'name' => 'Paid'],
                            ['id' => 'completed', 'name' => 'Completed'],
                            ['id' => 'cancelled', 'name' => 'Cancelled'],
                        ], ['id', 'name'], 'Status', $filters['status'] ?? ''); ?>
                    </div>
                    <div class="col-md-2"><?php echo render_date_input('date_from', 'From Date', $filters['date_from'] ?? ''); ?></div>
                    <div class="col-md-2"><?php echo render_date_input('date_to', 'To Date', $filters['date_to'] ?? ''); ?></div>
                    <div class="col-md-2 smartsource-filter-actions">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Filter</button>
                        <a href="<?php echo admin_url('sales_center/reports'); ?>" class="btn btn-default btn-block">Clear</a>
                    </div>
                </div>
                <?php echo form_close(); ?>

                <div class="panel_s smartsource-commission-assignment">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Assign Invoice To Sales Representative</h4>
                        <p class="text-muted">Use this area to connect CRM invoices to sales representatives, managers, departments, commission rates, paid commission, and issue notes.</p>
                        <?php echo form_open(admin_url('sales_center/save_commission')); ?>
                        <div class="row">
                            <div class="col-md-3"><?php echo render_select('invoice_id', $invoices, ['id', 'display_name'], 'Invoice', ''); ?></div>
                            <div class="col-md-3"><?php echo render_select('salesperson_id', $salespeople, ['id', 'company'], 'Sales Representative', ''); ?></div>
                            <div class="col-md-3"><?php echo render_select('manager_id', $sales_managers, ['id', 'company'], 'Sales Manager / Director', ''); ?></div>
                            <div class="col-md-3"><?php echo render_select('department_id', $departments, ['id', 'name'], 'Department', ''); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-2"><?php echo render_input('invoice_total', 'Invoice Total', '', 'number', ['step' => '0.01']); ?></div>
                            <div class="col-md-2"><?php echo render_input('amount_collected', 'Amount Collected', '', 'number', ['step' => '0.01']); ?></div>
                            <div class="col-md-2"><?php echo render_input('commission_rate', 'Commission %', '', 'number', ['step' => '0.01']); ?></div>
                            <div class="col-md-2"><?php echo render_input('commission_paid', 'Commission Paid', '', 'number', ['step' => '0.01']); ?></div>
                            <div class="col-md-2"><?php echo render_select('status', [
                                ['id' => 'pending', 'name' => 'Pending'],
                                ['id' => 'open', 'name' => 'Open'],
                                ['id' => 'partial', 'name' => 'Partial'],
                                ['id' => 'paid', 'name' => 'Paid'],
                                ['id' => 'cancelled', 'name' => 'Cancelled'],
                            ], ['id', 'name'], 'Status', 'pending'); ?></div>
                            <div class="col-md-2" style="padding-top:25px;"><button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i> Save</button></div>
                        </div>
                        <?php echo render_textarea('issue_notes', 'Issue Notes / Sales Rep Notes', '', ['rows' => 3]); ?>
                        <?php echo form_close(); ?>
                    </div>
                </div>

                <hr>
                <div class="row smartsource-stats-row">
                    <div class="col-md-3"><div class="smartsource-stat"><strong><?php echo app_format_money($summary['invoice_total'] ?? 0, get_base_currency()); ?></strong><span>Total Sold</span></div></div>
                    <div class="col-md-3"><div class="smartsource-stat"><strong><?php echo app_format_money($summary['collected'] ?? 0, get_base_currency()); ?></strong><span>Collected</span></div></div>
                    <div class="col-md-3"><div class="smartsource-stat"><strong><?php echo app_format_money($summary['commission_earned'] ?? 0, get_base_currency()); ?></strong><span>Commission Earned</span></div></div>
                    <div class="col-md-3"><div class="smartsource-stat"><strong><?php echo app_format_money($summary['commission_owed'] ?? 0, get_base_currency()); ?></strong><span>Commission Owed</span></div></div>
                </div>

                <div class="row smartsource-stats-row">
                    <div class="col-md-4"><div class="smartsource-stat small"><strong><?php echo (int)($summary['open_invoices'] ?? 0); ?></strong><span>Open / Pending Invoices</span></div></div>
                    <div class="col-md-4"><div class="smartsource-stat small"><strong><?php echo (int)($summary['paid_invoices'] ?? 0); ?></strong><span>Paid Invoices</span></div></div>
                    <div class="col-md-4"><div class="smartsource-stat small"><strong><?php echo (int)($summary['cancelled_invoices'] ?? 0); ?></strong><span>Cancelled / Void Invoices</span></div></div>
                </div>

                <hr>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dt-table smartsource-table">
                        <thead>
                            <tr>
                                <th>Sales Representative</th>
                                <th>Manager / Director</th>
                                <th>Invoice / Customer</th>
                                <th>Invoice Status</th>
                                <th>Total Sold</th>
                                <th>Collected</th>
                                <th>Commission Rate</th>
                                <th>Commission Earned</th>
                                <th>Commission Paid</th>
                                <th>Commission Owed</th>
                                <th>Status</th>
                                <th>Issue Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row) { ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['salesperson_id'])) { ?>
                                            <a href="<?php echo admin_url('sales_center/view/' . (int)$row['salesperson_id']); ?>"><?php echo html_escape($row['salesperson_company'] ?? ''); ?></a>
                                        <?php } else { ?>
                                            <?php echo html_escape($row['salesperson_company'] ?? 'Unassigned'); ?>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo html_escape($row['manager_name'] ?? '-'); ?></td>
                                    <td>
                                        <?php if (!empty($row['invoice_id'])) { ?>
                                            <a href="<?php echo admin_url('invoices/list_invoices/' . (int)$row['invoice_id']); ?>">#<?php echo html_escape($row['invoice_number'] ?? $row['invoice_id']); ?></a><br><?php if (!empty($row['customer_name'])) { ?><small class="text-muted"><?php echo html_escape($row['customer_name']); ?></small><br><?php } ?><small><a href="<?php echo site_url('invoice/' . (int)$row['invoice_id']); ?>" target="_blank">View As Customer</a> | <a href="<?php echo admin_url('invoices/pdf/' . (int)$row['invoice_id']); ?>" target="_blank">PDF</a></small>
                                        <?php } else { ?>
                                            -
                                        <?php } ?>
                                    </td>
                                    <td><?php echo html_escape($row['invoice_status'] ?? '-'); ?></td>
                                    <td><?php echo app_format_money($row['invoice_total'] ?? 0, get_base_currency()); ?></td>
                                    <td><?php echo app_format_money($row['amount_collected'] ?? 0, get_base_currency()); ?></td>
                                    <td><?php echo html_escape($row['commission_rate'] ?? 0); ?>%</td>
                                    <td><?php echo app_format_money($row['commission_earned'] ?? 0, get_base_currency()); ?></td>
                                    <td><?php echo app_format_money($row['commission_paid'] ?? 0, get_base_currency()); ?></td>
                                    <td><?php echo app_format_money($row['commission_owed'] ?? 0, get_base_currency()); ?></td>
                                    <td><span class="label label-info"><?php echo ucwords(str_replace('_', ' ', (string)($row['status'] ?? 'pending'))); ?></span></td>
                                    <td><?php echo nl2br(html_escape($row['issue_notes'] ?? '')); ?></td>
                                    <td class="smartsource-actions">
                                        <a class="btn btn-default btn-xs" href="<?php echo admin_url('invoices/list_invoices/' . (int)$row['invoice_id']); ?>">Open Invoice</a>
                                        <a class="btn btn-info btn-xs" href="<?php echo admin_url('invoices/invoice/' . (int)$row['invoice_id']); ?>">Edit</a>
                                        <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('sales_center/delete_commission/' . (int)$row['id']); ?>">Delete Assignment</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

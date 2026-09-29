<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
<?php echo smartsource_admin_submenu('contracts'); ?>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s smartsource-panel smartsource-filter-scope">
                    <div class="panel-body">
                        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
                            <div>
                                <h4 class="tw-m-0">Subcontractor Contracts</h4>
                                <p class="text-muted tw-mb-0">Manage subcontractor agreements, contract values, contract types, statuses, files, project links, and visibility controls.</p>
                            </div>
                            <div>
                                <a href="<?php echo admin_url('smartsource_subcontractors/contracts'); ?>" class="btn btn-default"><i class="fa fa-refresh"></i> <?php echo _l('refresh'); ?></a>
                                <button class="btn btn-default" onclick="window.print();"><i class="fa fa-print"></i> Print</button>
                                <?php if (has_permission('smartsource_subcontractor_contracts', '', 'create')) { ?>
                                    <a href="<?php echo admin_url('smartsource_subcontractors/contract'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> New Subcontractor Contract</a>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="row smartsource-stats-row">
                            <div class="col-md-2"><a class="smartsource-stat-link" href="<?php echo admin_url('smartsource_subcontractors/contracts?stat=active'); ?>"><div class="smartsource-stat"><strong><?php echo (int)$stats['active_contracts']; ?></strong><span>Active Contracts</span></div></a></div>
                            <div class="col-md-2"><a class="smartsource-stat-link" href="<?php echo admin_url('smartsource_subcontractors/contracts?stat=expired'); ?>"><div class="smartsource-stat danger"><strong><?php echo (int)$stats['expired_contracts']; ?></strong><span>Expired Contracts</span></div></a></div>
                            <div class="col-md-2"><a class="smartsource-stat-link" href="<?php echo admin_url('smartsource_subcontractors/contracts?stat=expiring'); ?>"><div class="smartsource-stat warning"><strong><?php echo (int)$stats['expiring_contracts']; ?></strong><span>Expiring Soon</span></div></a></div>
                            <div class="col-md-2"><div class="smartsource-stat warning"><strong><?php echo (int)$stats['expiring_insurance']; ?></strong><span>Insurance Expiring</span></div></div>
                            <div class="col-md-2"><div class="smartsource-stat"><strong><?php echo app_format_money($stats['total_contract_value'], get_base_currency()); ?></strong><span>Total Value</span></div></div>
                            <div class="col-md-2"><a class="smartsource-stat-link" href="<?php echo admin_url('smartsource_subcontractors/contracts?stat=trash&include_trash=1'); ?>"><div class="smartsource-stat muted"><strong><?php echo (int)$stats['trash_contracts']; ?></strong><span>Trash</span></div></a></div>
                        </div>

                        <hr>
                        <form method="get" class="row smartsource-filter-box">
                            <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Subject" data-smartsource-filter="subject"></div>
                            <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Subcontractor" data-smartsource-filter="subcontractor"></div>
                            <div class="col-md-2"><input type="text" class="form-control" placeholder="Filter Project" data-smartsource-filter="project"></div>
                            <div class="col-md-2"><input type="text" name="contract_type" class="form-control" placeholder="Contract Type" data-smartsource-filter="type" value="<?php echo html_escape($this->input->get('contract_type')); ?>"></div>
                            <div class="col-md-2">
                                <select name="status" class="form-control" data-smartsource-filter="status">
                                    <option value=""><?php echo _l('filter_by_status'); ?></option>
                                    <?php foreach ($statuses as $status) { ?>
                                        <option value="<?php echo html_escape($status['slug']); ?>" <?php echo $this->input->get('status') === $status['slug'] ? 'selected' : ''; ?>><?php echo html_escape($status['name']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="include_trash" class="form-control" data-smartsource-filter="trash">
                                    <option value="0">Hide Trash</option>
                                    <option value="1" <?php echo $this->input->get('include_trash') === '1' ? 'selected' : ''; ?>>Show Trash</option>
                                </select>
                            </div>
                            <div class="col-md-12 tw-mt-2">
                                <button class="btn btn-info" type="submit"><i class="fa fa-filter"></i> <?php echo _l('filter'); ?></button>
                                <button type="button" class="btn btn-default smartsource-clear-filters"><i class="fa fa-eraser"></i> Clear Table Filters</button>
                            </div>
                        </form>
                        <hr>

                        <div class="row">
                            <div class="col-md-6"><canvas id="smartsourceTypeChart" height="130"></canvas></div>
                            <div class="col-md-6"><canvas id="smartsourceValueChart" height="130"></canvas></div>
                        </div>
                        <hr>

                        <table class="table smartsource-data-table smartsource-table">
                            <thead>
                                <tr>
                                    <th><?php echo _l('subject'); ?></th>
                                    <th>Subcontractor</th>
                                    <th><?php echo _l('project'); ?></th>
                                    <th>Contract Type</th>
                                    <th><?php echo _l('status'); ?></th>
                                    <th><?php echo _l('value'); ?></th>
                                    <th><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contracts as $contract) { ?>
                                    <tr class="<?php echo !empty($contract['is_trash']) ? 'text-muted smartsource-trash-row' : ''; ?>" data-subject="<?php echo html_escape($contract['subject']); ?>" data-subcontractor="<?php echo html_escape($contract['subcontractor_company']); ?>" data-project="<?php echo !empty($contract['project_name']) ? html_escape($contract['project_name']) : (int)$contract['project_id']; ?>" data-type="<?php echo html_escape($contract['contract_type']); ?>" data-status="<?php echo html_escape($contract['status']); ?>" data-trash="<?php echo !empty($contract['is_trash']) ? '1' : '0'; ?>">
                                        <td><a href="<?php echo admin_url('smartsource_subcontractors/contract_view/' . $contract['id']); ?>"><?php echo html_escape($contract['subject']); ?></a><?php if (!empty($contract['hidden_from_customer'])) { ?> <span class="label label-warning">Hidden From Customer</span><?php } ?></td>
                                        <td><a href="<?php echo admin_url('smartsource_subcontractors/view/' . $contract['subcontractor_id']); ?>"><?php echo html_escape($contract['subcontractor_company']); ?></a></td>
                                        <td><?php echo !empty($contract['project_name']) ? html_escape($contract['project_name']) : (int)$contract['project_id']; ?></td>
                                        <td><?php echo html_escape($contract['contract_type']); ?></td>
                                        <td><span class="label label-info"><?php echo html_escape(ucwords(str_replace('_', ' ', $contract['status']))); ?></span></td>
                                        <td><?php echo app_format_money($contract['contract_value'], get_base_currency()); ?></td>
                                        <td><div class="smartsource-contract-actions">
                                            <a class="btn btn-default btn-sm" href="<?php echo admin_url('smartsource_subcontractors/contract_view/' . $contract['id']); ?>"><i class="fa fa-eye"></i> <?php echo _l('view'); ?></a>
                                            <?php if (has_permission('smartsource_subcontractor_contracts', '', 'edit')) { ?><a class="btn btn-info btn-sm" href="<?php echo admin_url('smartsource_subcontractors/contract/' . $contract['id']); ?>"><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></a><?php } ?>
                                            <?php if (has_permission('smartsource_subcontractor_contracts', '', 'delete')) { ?>
                                                <?php if (!empty($contract['is_trash'])) { ?>
                                                    <a class="btn btn-success btn-sm" href="<?php echo admin_url('smartsource_subcontractors/restore_contract/' . $contract['id']); ?>"><i class="fa fa-undo"></i> <?php echo _l('restore'); ?></a>
                                                    <a class="btn btn-danger btn-sm _delete" href="<?php echo admin_url('smartsource_subcontractors/permanent_delete_contract/' . $contract['id']); ?>"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a>
                                                <?php } else { ?>
                                                    <a class="btn btn-danger btn-sm _delete" href="<?php echo admin_url('smartsource_subcontractors/contract_delete/' . $contract['id']); ?>"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a>
                                                <?php } ?>
                                            <?php } ?>
                                            </div>
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
</div>
<script>
window.smartsourceChartData = <?php echo json_encode($chart_data); ?>;
</script>
<?php init_tail(); ?>

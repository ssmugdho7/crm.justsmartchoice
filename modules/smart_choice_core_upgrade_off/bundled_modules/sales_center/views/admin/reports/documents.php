<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo function_exists('sales_center_admin_submenu') ? sales_center_admin_submenu('documents') : ''; ?>
        <div class="panel_s smartsource-panel smartsource-filter-scope">
            <div class="panel-body">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 smartsource-header-row">
                    <div>
                        <h4 class="tw-m-0">Sales Documents</h4>
                        <p class="text-muted tw-mb-0">Connect CRM proposals, estimates, invoices, payments, and credit notes to sales representatives without duplicating CRM business records.</p>
                    </div>
                    <div>
                        <a href="<?php echo admin_url('sales_center/documents'); ?>" class="btn btn-default"><i class="fa fa-refresh"></i> Refresh</a>
                    </div>
                </div>

                <?php echo form_open(admin_url('sales_center/documents'), ['method' => 'get', 'class' => 'smartsource-table-filters']); ?>
                <div class="row">
                    <div class="col-md-3"><?php echo render_select('salesperson_id', $salespeople, ['id', 'company'], 'Sales Representative', $filters['salesperson_id'] ?? ''); ?></div>
                    <div class="col-md-3"><?php echo render_select('rel_type', [
                        ['id' => 'proposal', 'name' => 'Proposals'],
                        ['id' => 'estimate', 'name' => 'Estimates'],
                        ['id' => 'invoice', 'name' => 'Invoices'],
                        ['id' => 'payment', 'name' => 'Payments'],
                        ['id' => 'credit_note', 'name' => 'Credit Notes'],
                    ], ['id', 'name'], 'Document Type', $filters['rel_type'] ?? ''); ?></div>
                    <div class="col-md-3"><?php echo render_select('status', [
                        ['id' => 'pending', 'name' => 'Pending'],
                        ['id' => 'open', 'name' => 'Open'],
                        ['id' => 'partial', 'name' => 'Partial'],
                        ['id' => 'paid', 'name' => 'Paid'],
                        ['id' => 'completed', 'name' => 'Completed'],
                        ['id' => 'cancelled', 'name' => 'Cancelled'],
                    ], ['id', 'name'], 'Assignment Status', $filters['status'] ?? ''); ?></div>
                    <div class="col-md-3 smartsource-filter-actions" style="padding-top:25px;">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                        <a href="<?php echo admin_url('sales_center/documents'); ?>" class="btn btn-default">Clear</a>
                    </div>
                </div>
                <?php echo form_close(); ?>

                <div class="panel_s smartsource-commission-assignment">
                    <div class="panel-body">
                        <h4 class="tw-mt-0">Assign CRM Sales Document</h4>
                        <p class="text-muted">This links an existing CRM proposal, estimate, invoice, payment, or credit note to a sales representative. It does not change or duplicate the original CRM record.</p>
                        <?php echo form_open(admin_url('sales_center/save_document_link')); ?>
                        <div class="row">
                            <div class="col-md-4"><?php echo render_select('sales_document', $document_options, ['id', 'display_name'], 'CRM Sales Document', ''); ?></div>
                            <div class="col-md-2"><?php echo render_select('salesperson_id', $salespeople, ['id', 'company'], 'Sales Representative', ''); ?></div>
                            <div class="col-md-2"><?php echo render_select('manager_id', $sales_managers, ['id', 'company'], 'Manager', ''); ?></div>
                            <div class="col-md-2"><?php echo render_select('department_id', $departments, ['id', 'name'], 'Department', ''); ?></div>
                            <div class="col-md-2"><?php echo render_select('status', [
                                ['id' => 'pending', 'name' => 'Pending'],
                                ['id' => 'open', 'name' => 'Open'],
                                ['id' => 'partial', 'name' => 'Partial'],
                                ['id' => 'paid', 'name' => 'Paid'],
                                ['id' => 'completed', 'name' => 'Completed'],
                                ['id' => 'cancelled', 'name' => 'Cancelled'],
                            ], ['id', 'name'], 'Status', 'pending'); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-2"><?php echo render_input('document_total', 'Document Total', '', 'number', ['step' => '0.01']); ?></div>
                            <div class="col-md-2"><?php echo render_input('amount_collected', 'Amount Collected', '', 'number', ['step' => '0.01']); ?></div>
                            <div class="col-md-2"><?php echo render_input('commission_rate', 'Commission Percent', '', 'number', ['step' => '0.01']); ?></div>
                            <div class="col-md-2"><?php echo render_input('commission_paid', 'Commission Paid', '', 'number', ['step' => '0.01']); ?></div>
                            <div class="col-md-4" style="padding-top:25px;"><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Assignment</button></div>
                        </div>
                        <?php echo render_textarea('issue_notes', 'Issue Notes', '', ['rows' => 3]); ?>
                        <?php echo form_close(); ?>
                    </div>
                </div>

                <h4>Assigned Sales Documents</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dt-table smartsource-table">
                        <thead>
                            <tr>
                                <th>Sales Representative</th>
                                <th>Document</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Collected</th>
                                <th>Commission Owed</th>
                                <th>Status</th>
                                <th>Issue Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($links as $link) { $doc = $link['document'] ?? []; ?>
                                <tr>
                                    <td><?php echo html_escape($link['salesperson_company'] ?? ''); ?></td>
                                    <td><strong><?php echo html_escape(ucwords(str_replace('_', ' ', $link['rel_type']))); ?> #<?php echo html_escape($doc['number'] ?? $link['rel_id']); ?></strong></td>
                                    <td><?php echo html_escape($doc['customer_name'] ?? ''); ?></td>
                                    <td><?php echo app_format_money($link['document_total'] ?? 0, get_base_currency()); ?></td>
                                    <td><?php echo app_format_money($link['amount_collected'] ?? 0, get_base_currency()); ?></td>
                                    <td><?php echo app_format_money($link['commission_owed'] ?? 0, get_base_currency()); ?></td>
                                    <td><span class="label label-info"><?php echo html_escape(ucwords(str_replace('_', ' ', (string)($link['status'] ?? 'pending')))); ?></span></td>
                                    <td><?php echo nl2br(html_escape($link['issue_notes'] ?? '')); ?></td>
                                    <td class="smartsource-actions">
                                        <?php if (!empty($doc['admin_url'])) { ?><a class="btn btn-default btn-xs" href="<?php echo html_escape($doc['admin_url']); ?>" target="_blank">Open</a><?php } ?>
                                        <?php if (!empty($doc['public_url'])) { ?><a class="btn btn-info btn-xs" href="<?php echo html_escape($doc['public_url']); ?>" target="_blank">Public View</a><?php } ?>
                                        <?php if (!empty($doc['pdf_url'])) { ?><a class="btn btn-default btn-xs" href="<?php echo html_escape($doc['pdf_url']); ?>" target="_blank">PDF</a><?php } ?>
                                        <?php if (!empty($doc['download_url'])) { ?><a class="btn btn-success btn-xs" href="<?php echo html_escape($doc['download_url']); ?>" target="_blank">Download</a><?php } ?>
                                        <?php if (!empty($doc['email_url'])) { ?>
                                            <?php if (!empty($doc['email_js_function'])) { ?>
                                                <a class="btn btn-warning btn-xs" href="<?php echo html_escape($doc['email_url']); ?>" target="_blank" onclick="if (typeof <?php echo html_escape($doc['email_js_function']); ?> === 'function') { <?php echo html_escape($doc['email_js_function']); ?>(<?php echo (int)($doc['rel_id'] ?? $link['rel_id']); ?>); return false; } return true;">Send Email</a>
                                            <?php } else { ?>
                                                <a class="btn btn-warning btn-xs" href="<?php echo html_escape($doc['email_url']); ?>" target="_blank">Send Email</a>
                                            <?php } ?>
                                        <?php } ?>
                                        <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('sales_center/delete_document_link/' . (int)$link['id']); ?>">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <h4>CRM Sales Documents</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered dt-table smartsource-table">
                        <thead>
                            <tr><th>Type</th><th>Number</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc) { ?>
                                <tr>
                                    <td><?php echo html_escape(ucwords(str_replace('_', ' ', $doc['rel_type']))); ?></td>
                                    <td><?php echo html_escape($doc['number']); ?></td>
                                    <td><?php echo html_escape($doc['customer_name']); ?></td>
                                    <td><?php echo html_escape(ucwords(str_replace('_', ' ', (string)$doc['status']))); ?></td>
                                    <td><?php echo app_format_money($doc['total'], get_base_currency()); ?></td>
                                    <td><?php echo html_escape($doc['document_date'] ?: $doc['datecreated']); ?></td>
                                    <td class="smartsource-actions">
                                        <a class="btn btn-default btn-xs" href="<?php echo html_escape($doc['admin_url']); ?>" target="_blank">Open</a>
                                        <?php if (!empty($doc['public_url'])) { ?><a class="btn btn-info btn-xs" href="<?php echo html_escape($doc['public_url']); ?>" target="_blank">Public View</a><?php } ?>
                                        <a class="btn btn-default btn-xs" href="<?php echo html_escape($doc['pdf_url']); ?>" target="_blank">PDF</a>
                                        <a class="btn btn-success btn-xs" href="<?php echo html_escape($doc['download_url']); ?>" target="_blank">Download</a>
                                        <?php if (!empty($doc['email_url'])) { ?>
                                            <?php if (!empty($doc['email_js_function'])) { ?>
                                                <a class="btn btn-warning btn-xs" href="<?php echo html_escape($doc['email_url']); ?>" target="_blank" onclick="if (typeof <?php echo html_escape($doc['email_js_function']); ?> === 'function') { <?php echo html_escape($doc['email_js_function']); ?>(<?php echo (int)$doc['rel_id']; ?>); return false; } return true;">Send Email</a>
                                            <?php } else { ?>
                                                <a class="btn btn-warning btn-xs" href="<?php echo html_escape($doc['email_url']); ?>" target="_blank">Send Email</a>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info mtop20">PDF generation and email delivery use native Perfex CRM settings. The Send Email button opens the same CRM email modal used by proposals, estimates, invoices, and credit notes. If delivery still fails after the modal sends successfully, check Setup &gt; Settings &gt; Email and application logs.</div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

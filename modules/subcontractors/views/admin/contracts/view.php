<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content smartsource-contract-workspace">
        <?php echo smartsource_admin_submenu('contracts'); ?>
        <?php $public_url = isset($public_url) ? $public_url : site_url('subcontractors/subcontractor_contract/index/' . rawurlencode($contract->public_token ?? '')); ?>

        <div class="row smartsource-split-workspace">
            <div class="col-md-4 smartsource-left-list hidden-sm hidden-xs">
                <div class="panel_s"><div class="panel-body">
                    <h4 class="bold">Contract Workspace</h4>
                    <p class="text-muted">Table stays on the left. The selected contract opens on the right, similar to invoices and estimates.</p>
                    <a class="btn btn-default btn-block" href="<?php echo admin_url('subcontractors/contracts'); ?>"><i class="fa fa-list"></i> Back To Contract List</a>
                    <a class="btn btn-primary btn-block" href="<?php echo admin_url('subcontractors/contract'); ?>"><i class="fa fa-plus"></i> New Contract</a>
                </div></div>
            </div>
            <div class="col-md-8 smartsource-right-preview">
                <div class="smartsource-contract-topbar panel_s">
                    <div class="panel-body">
                        <div class="smartsource-contract-topbar-inner">
                            <div class="smartsource-contract-title-block">
                                <h4><?php echo html_escape($contract->subject); ?></h4>
                                <p class="text-muted">Status: <strong><?php echo html_escape(ucwords(str_replace('_',' ', $contract->status ?? 'draft'))); ?></strong></p>
                            </div>
                            <div class="smartsource-contract-quick-actions-top">
                                <a href="<?php echo admin_url('subcontractors/contract/' . $contract->id); ?>" class="btn btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                <a href="<?php echo admin_url('subcontractors/contract_pdf/' . $contract->id); ?>" class="btn btn-default" target="_blank"><i class="fa fa-file-pdf-o"></i> View PDF</a>
                                <a href="<?php echo admin_url('subcontractors/contract_pdf/' . $contract->id); ?>" class="btn btn-default" target="_blank"><i class="fa fa-external-link"></i> PDF New Window</a>
                                <a href="<?php echo admin_url('subcontractors/contract_pdf/' . $contract->id); ?>" class="btn btn-default" target="_blank"><i class="fa fa-download"></i> Download And Print</a>
                                <a href="<?php echo admin_url('subcontractors/contract_xml/' . $contract->id); ?>" class="btn btn-default"><i class="fa fa-code"></i> XML Export</a>
                                <a href="<?php echo admin_url('subcontractors/email_contract/' . $contract->id); ?>" class="btn btn-success"><i class="fa fa-envelope"></i> Send Email</a>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">More <span class="caret"></span></button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a target="_blank" href="<?php echo admin_url('subcontractors/contract_client_view/' . $contract->id); ?>"><i class="fa fa-user"></i> View as a Customer</a></li>
                                        <li><a target="_blank" href="<?php echo $public_url; ?>"><i class="fa fa-desktop"></i> Digital View</a></li>
                                        <li><a href="<?php echo admin_url('subcontractors/contract_mark_sent/' . $contract->id); ?>"><i class="fa fa-paper-plane"></i> Mark as Sent</a></li>
                                        <li><a href="<?php echo admin_url('subcontractors/contract_mark_signed/' . $contract->id); ?>"><i class="fa fa-check"></i> Mark as Signed</a></li>
                                        <li><a href="<?php echo admin_url('subcontractors/contract_mark_cancelled/' . $contract->id); ?>"><i class="fa fa-ban"></i> Mark as Cancelled</a></li>
                                        <li><a href="<?php echo admin_url('subcontractors/contract_copy/' . $contract->id); ?>"><i class="fa fa-copy"></i> Copy Contract</a></li>
                                        <li><a href="#ss-files" data-toggle="tab"><i class="fa fa-paperclip"></i> Attachments</a></li>
                                        <?php if (has_permission('smartsource_subcontractor_contracts', '', 'delete')) { ?><li class="divider"></li><li><a class="_delete text-danger" href="<?php echo admin_url('subcontractors/contract_delete/' . $contract->id); ?>"><i class="fa fa-trash"></i> Delete</a></li><?php } ?>
                                    </ul>
                                </div>
                                <a href="<?php echo admin_url('subcontractors/contracts'); ?>" class="btn btn-default"><i class="fa fa-compress"></i> Back</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel_s smartsource-panel"><div class="panel-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#ss-summary" role="tab" data-toggle="tab">Summary</a></li>
                        <li role="presentation"><a href="#ss-digital" role="tab" data-toggle="tab">Digital View</a></li>
                        <li role="presentation"><a href="#ss-tasks" role="tab" data-toggle="tab">Tasks</a></li>
                        <li role="presentation"><a href="#ss-activity" role="tab" data-toggle="tab">Activity Log</a></li>
                        <li role="presentation"><a href="#ss-reminders" role="tab" data-toggle="tab">Reminders</a></li>
                        <li role="presentation"><a href="#ss-notes" role="tab" data-toggle="tab">Internal Notes</a></li>
                        <li role="presentation"><a href="#ss-discussion" role="tab" data-toggle="tab">Discussion</a></li>
                        <li role="presentation"><a href="#ss-files" role="tab" data-toggle="tab">Attachments</a></li>
                    </ul>
                    <div class="tab-content mtop20">
                        <div role="tabpanel" class="tab-pane active" id="ss-summary">
                            <div class="row">
                                <div class="col-md-6"><div class="smartsource-summary-card"><h4>Contract Information</h4><p><strong>Contract Number:</strong> #<?php echo (int)$contract->id; ?></p><p><strong>Type:</strong> <?php echo html_escape($contract->contract_type ?? ''); ?></p><p><strong>Status:</strong> <?php echo html_escape(ucwords(str_replace('_',' ', $contract->status ?? 'draft'))); ?></p><p><strong>Value:</strong> <?php echo app_format_money($contract->contract_value, get_base_currency()); ?></p><p><strong>Start Date:</strong> <?php echo html_escape($contract->start_date ?? ''); ?></p><p><strong>End Date:</strong> <?php echo html_escape($contract->end_date ?? ''); ?></p></div></div>
                                <div class="col-md-6"><div class="smartsource-summary-card"><h4>Subcontractor</h4><p><strong>Name:</strong> <?php echo html_escape($contract->subcontractor_company ?? ''); ?></p><p><strong>Contact:</strong> <?php echo html_escape($contract->contact_name ?? ''); ?></p><p><strong>Email:</strong> <?php echo html_escape($contract->email ?? ''); ?></p><p><strong>Phone:</strong> <?php echo html_escape($contract->phone ?? ''); ?></p><p><strong>Project:</strong> <?php echo html_escape($contract->project_name ?? $contract->project_id); ?></p><p><strong>Digital Link:</strong> <a target="_blank" href="<?php echo $public_url; ?>">Open</a></p></div></div>
                            </div>
                            <?php if (function_exists('render_custom_fields_view')) { echo render_custom_fields_view('smartsource_subcontractor_contracts', $contract->id); } ?>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="ss-digital"><div class="smartsource-contract-paper smartsource-admin-digital-paper"><?php echo $this->smartsource_subcontractors_model->render_contract_cover($contract); ?><div class="smartsource-page-break"></div><?php echo $this->smartsource_subcontractors_model->render_contract_content($contract); ?></div></div>
                        <div role="tabpanel" class="tab-pane" id="ss-tasks"><p class="text-muted">Use CRM tasks for this contract from the main task system. This tab keeps the workspace aligned with invoices, estimates, and projects.</p><button class="btn btn-primary" onclick="new_task();return false;"><i class="fa fa-plus"></i> Add Task</button></div>
                        <div role="tabpanel" class="tab-pane" id="ss-activity"><p class="text-muted">Activity is tracked in the CRM activity log when the contract is created, edited, copied, signed, emailed, exported, or deleted.</p></div>
                        <div role="tabpanel" class="tab-pane" id="ss-reminders"><p class="text-muted">Use CRM reminders from the customer, project, or task workspace when follow-up is required.</p></div>
                        <div role="tabpanel" class="tab-pane" id="ss-notes">
                            <?php echo form_open(admin_url('subcontractors/add_contract_comment/' . $contract->id)); ?><input type="hidden" name="is_internal" value="1"><textarea name="comment" class="form-control" rows="4" placeholder="Internal Notes"></textarea><br><button class="btn btn-primary" type="submit">Add Internal Note</button><?php echo form_close(); ?><hr>
                            <?php foreach (($comments ?? []) as $comment) { if (!empty($comment['is_internal'])) { ?><div class="smartsource-comment smartsource-internal-note"><strong><?php echo html_escape(trim(($comment['firstname'] ?? '') . ' ' . ($comment['lastname'] ?? '')) ?: 'Staff'); ?></strong><br><small><?php echo html_escape($comment['dateadded'] ?? ''); ?></small><p><?php echo nl2br(html_escape($comment['comment'] ?? '')); ?></p></div><?php }} ?>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="ss-discussion">
                            <?php echo form_open(admin_url('subcontractors/add_contract_comment/' . $contract->id)); ?><input type="hidden" name="is_internal" value="0"><textarea name="comment" class="form-control" rows="4" placeholder="Customer-visible discussion comment"></textarea><br><button class="btn btn-success" type="submit">Add Discussion Comment</button><?php echo form_close(); ?><hr>
                            <?php foreach (($comments ?? []) as $comment) { if (empty($comment['is_internal'])) { ?><div class="smartsource-comment smartsource-customer-comment"><strong><?php echo html_escape(($comment['customer_name'] ?? '') ?: trim(($comment['firstname'] ?? '') . ' ' . ($comment['lastname'] ?? '')) ?: 'Customer'); ?></strong><br><small><?php echo html_escape($comment['dateadded'] ?? ''); ?></small><p><?php echo nl2br(html_escape($comment['comment'] ?? '')); ?></p></div><?php }} ?>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="ss-files">
                            <?php echo form_open_multipart(admin_url('subcontractors/upload_file/contract/' . $contract->id)); ?><input type="file" name="file[]" class="form-control" multiple required><br><div class="checkbox checkbox-primary"><input type="checkbox" name="visible_to_customer" id="visible_to_customer_contract" value="1"><label for="visible_to_customer_contract">Visible To Customer</label></div><button class="btn btn-primary" type="submit"><i class="fa fa-upload"></i> Upload Attachment</button><?php echo form_close(); ?><hr>
                            <?php if (empty($files)) { ?><p class="text-muted">No attachments found.</p><?php } ?><?php foreach (($files ?? []) as $file) { ?><p><a target="_blank" href="<?php echo smartsource_file_url($file); ?>"><i class="fa fa-paperclip"></i> <?php echo html_escape($file['original_file_name'] ?: $file['file_name']); ?></a> <small class="text-muted"><?php echo html_escape($file['dateadded'] ?? ''); ?></small></p><?php } ?>
                        </div>
                    </div>
                </div></div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

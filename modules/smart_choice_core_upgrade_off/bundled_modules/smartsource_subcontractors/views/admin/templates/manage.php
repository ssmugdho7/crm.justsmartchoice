<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
<?php echo smartsource_admin_submenu('templates'); ?>

        <div class="panel_s smartsource-panel">
            <div class="panel-body">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 smartsource-header-row">
                    <div>
                        <h4 class="tw-m-0">Subcontractor Templates</h4>
                        <p class="text-muted tw-mb-0">Create, edit, view, copy, delete, and reuse subcontractor contract templates.</p>
                    </div>
                    <div>
                        <a href="<?php echo admin_url('smartsource_subcontractors/templates'); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> New Template</a>
                    </div>
                </div>
                <hr>
                <?php echo form_open($template ? admin_url('smartsource_subcontractors/templates/' . $template->id) : admin_url('smartsource_subcontractors/templates')); ?>
                <div class="row">
                    <div class="col-md-6"><?php echo render_input('name', 'name', $template->name ?? '', 'text', ['required' => true]); ?></div>
                    <div class="col-md-6"><?php echo render_input('contract_type', 'smartsource_contract_type', $template->contract_type ?? ''); ?></div>
                    <div class="col-md-12 smartsource-template-editor-wrap">
                        <label>Template Content</label>
                        <?php $default_template_content = '<h1 style="text-align:center;">SMART CHOICE CONTRACTORS USA</h1><h2 style="text-align:center;">MASTER SUBCONTRACTOR AGREEMENT</h2><p>This subcontractor agreement is entered into between Smart Choice Contractors USA and {subcontractor_name}. This agreement should be reviewed carefully before signing. It may be customized for trade-specific work, project-specific scope, insurance requirements, payment terms, safety requirements, lien release requirements, documentation obligations, and Florida construction compliance.</p><h2>Subcontractor Information</h2><p><strong>Subcontractor:</strong> {subcontractor_name}<br><strong>Email:</strong> {subcontractor_email}<br><strong>Phone:</strong> {subcontractor_phone}<br><strong>Project:</strong> {project_name}<br><strong>Contract Subject:</strong> {contract_subject}<br><strong>Contract Value:</strong> ${contract_value}</p><h2>Scope Of Work</h2><p>The subcontractor shall provide labor, supervision, tools, equipment, materials when assigned, project documentation, photos, cleanup, coordination, and all work required by the assigned trade scope, drawings, project notes, attachments, work orders, and written instructions issued by Smart Choice Contractors USA.</p><h2>Payment And Documentation</h2><p>Payment is subject to completed work, project manager approval, inspection approval when applicable, required photos, invoices, lien releases, document uploads, and confirmation that work is complete and acceptable.</p><h2>Initials And Signatures</h2><table width="100%" cellpadding="8" cellspacing="0" border="1"><tr><td><strong>Company Initials:</strong> {company_initials}</td><td><strong>Subcontractor Initials:</strong> {subcontractor_initials}</td></tr><tr><td>{company_signature}</td><td>{subcontractor_signature}</td></tr><tr><td colspan="2">{contract_signed_stamp}</td></tr></table>'; ?>
                        <textarea name="content" class="tinymce" rows="24"><?php echo html_escape($template->content ?? $default_template_content); ?></textarea>
                    </div>
                </div>
                <div class="text-right tw-mt-4">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>

        <div class="panel_s smartsource-panel smartsource-filter-scope">
            <div class="panel-body">
                <h4>Saved Subcontractor Templates</h4>
                <p class="text-muted">Click the template name or the View button to preview the full template before editing or copying.</p>
                <hr>
                <div class="smartsource-filter-box">
                    <div class="row">
                        <div class="col-md-4"><input type="text" class="form-control" placeholder="Filter Template Name" data-smartsource-filter="name"></div>
                        <div class="col-md-4"><input type="text" class="form-control" placeholder="Filter Contract Type" data-smartsource-filter="type"></div>
                        <div class="col-md-4"><button type="button" class="btn btn-default btn-block smartsource-clear-filters"><i class="fa fa-eraser"></i> Clear</button></div>
                    </div>
                </div>
                <table class="table smartsource-data-table smartsource-table">
                    <thead>
                        <tr>
                            <th><?php echo _l('name'); ?></th>
                            <th>Contract Type</th>
                            <th><?php echo _l('options'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $row) { ?>
                            <tr data-name="<?php echo html_escape($row['name']); ?>" data-type="<?php echo html_escape($row['contract_type']); ?>">
                                <td><a href="<?php echo admin_url('smartsource_subcontractors/template_view/' . $row['id']); ?>"><?php echo html_escape($row['name']); ?></a></td>
                                <td><?php echo html_escape($row['contract_type']); ?></td>
                                <td><div class="smartsource-actions">
                                    <a class="btn btn-default btn-sm" href="<?php echo admin_url('smartsource_subcontractors/template_view/' . $row['id']); ?>"><i class="fa fa-eye"></i> <?php echo _l('view'); ?></a>
                                    <a class="btn btn-info btn-sm" href="<?php echo admin_url('smartsource_subcontractors/templates/' . $row['id']); ?>"><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></a>
                                    <a class="btn btn-primary btn-sm" href="<?php echo admin_url('smartsource_subcontractors/copy_template/' . $row['id']); ?>"><i class="fa fa-copy"></i> <?php echo _l('copy'); ?></a>
                                    <a class="btn btn-danger btn-sm _delete" href="<?php echo admin_url('smartsource_subcontractors/delete_template/' . $row['id']); ?>"><i class="fa fa-trash"></i> <?php echo _l('delete'); ?></a>
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
<?php init_tail(); ?>

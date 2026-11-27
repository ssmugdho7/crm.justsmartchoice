<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo smartsource_admin_submenu('help'); ?>

        <div class="panel_s smartsource-panel">
            <div class="panel-body smartsource-help">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 smartsource-header-row">
                    <div>
                        <h3 class="smartsource-help-title">Subcontractors Help Guide</h3>
                        <p class="text-muted tw-mb-0">Training guide for the SmartSource Subcontractors module. Sections stay closed until you click Edit.</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" id="smartsource-add-help-section"><i class="fa fa-plus"></i> Add Instruction Section</button>
                    </div>
                </div>
                <hr>
                <div class="alert alert-info"><strong><?php echo _l('smartsource_help_quick_start_title'); ?></strong><ol><li><?php echo _l('smartsource_help_step_1'); ?></li><li><?php echo _l('smartsource_help_step_2'); ?></li><li><?php echo _l('smartsource_help_step_3'); ?></li><li><?php echo _l('smartsource_help_step_4'); ?></li><li><?php echo _l('smartsource_help_step_5'); ?></li><li><?php echo _l('smartsource_help_step_6'); ?></li></ol><p><?php echo _l('smartsource_help_troubleshooting'); ?></p></div>

                <div class="panel panel-default"><div class="panel-heading"><strong>Step-by-step portal and CRM guide / Guía del portal y CRM</strong></div><div class="panel-body">
                <h4>1. Subcontractor records</h4><p>Open <strong>Subcontractors</strong>. Use the list to find a company. Open its record to inspect contact information, trade, insurance, license, uploaded files and project associations. Use New to create a record manually. Save before leaving the page.</p>
                <h4>2. Public registration</h4><p>Open <strong>Portal</strong> and copy the registration link. Send it to the subcontractor. The subcontractor chooses English or Spanish, completes each required field, uploads documents, checks the security verification and selects Create Subcontractor Profile. Staff can then review the submitted record. A missing required field or failed CAPTCHA prevents submission.</p>
                <h4>3. Uploading documents</h4><p>Ask the subcontractor to upload a clear license, insurance certificate, W-9 or other requested document. Select the files and save the form. If a validation error occurs, the browser may clear the file selector: select the files again before resubmitting. Staff should check the files listed on the subcontractor record.</p>
                <h4>4. Contract templates</h4><p>Open <strong>Templates</strong>. Review an existing template before changing it. Create a new template when a different agreement format is needed. Use placeholders only when they are supported by the module. Save, then preview the rendered agreement before sending it. Template changes affect future documents; check existing agreements separately.</p>
                <h4>5. Contracts</h4><p>Open <strong>Contracts</strong>, create an agreement, select the subcontractor and relevant project, complete the contract fields, and save. Open the saved agreement to preview it, send its secure review link, and track the status. Do not mark a contract signed unless the signature is actually recorded.</p>
                <h4>6. Projects</h4><p>Link a subcontractor to the correct CRM project from the available project-linking controls. Verify the project name and subcontractor before saving. The project widget shows linked subcontractors and contracts to staff with access.</p>
                <h4>7. Notifications and email templates</h4><p>New applications can create CRM staff notifications and send confirmation email when enabled in Settings. Edit the subcontractor email templates from the CRM email template configuration. Test both English and Spanish applications and check the email header, logo, footer, and recipient.</p>
                <h4>8. Settings and permissions</h4><p>Open <strong>Settings</strong> to control portal appearance, registration options and alerts. Review staff roles in Perfex CRM. Only grant access to employees who should manage subcontractors. Test restricted access using a non-administrator employee account.</p>
                <h4>9. Troubleshooting</h4><p>If a form does not submit, check required fields, CAPTCHA and file size. If email does not arrive, check CRM SMTP settings, the enabled email template, the recipient address and mail logs. If a project association is missing, confirm the correct project was selected and saved. Never delete the module or its tables as a troubleshooting step.</p>
                </div></div>
                <?php echo form_open(admin_url('subcontractors/help')); ?>
                <input type="hidden" name="save_help_sections" value="1">
                <div id="smartsource-help-sections">
                    <?php foreach ($help_sections as $index => $section) { ?>
                        <div class="smartsource-help-view-card" data-index="<?php echo (int) $index; ?>">
                            <div class="smartsource-help-view-header">
                                <div>
                                    <h4><?php echo html_escape($section['title'] ?? 'Instruction Section'); ?></h4>
                                </div>
                                <div class="smartsource-help-actions">
                                    <button type="button" class="btn btn-info btn-sm smartsource-toggle-help-edit"><i class="fa fa-pencil"></i> Edit</button>
                                    <button type="button" class="btn btn-danger btn-sm smartsource-remove-help-section"><i class="fa fa-trash"></i> Delete</button>
                                </div>
                            </div>
                            <div class="smartsource-help-preview-section">
                                <div><?php echo $section['content'] ?? ''; ?></div>
                            </div>
                            <div class="smartsource-help-edit-card" style="display:none;">
                                <?php echo render_input('help_title[]', 'Title', $section['title'] ?? '', 'text'); ?>
                                <div class="form-group">
                                    <label>Instructions</label>
                                    <textarea name="help_content[]" class="tinymce" rows="8"><?php echo html_escape($section['content'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="text-right tw-mt-4">
                    <button class="btn btn-success" type="submit"><i class="fa fa-save"></i> Save Help Guide</button>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    var addBtn = document.getElementById('smartsource-add-help-section');
    var wrap = document.getElementById('smartsource-help-sections');
    if(!addBtn || !wrap){ return; }

    function nextIndex(){ return wrap.querySelectorAll('.smartsource-help-view-card').length; }

    addBtn.addEventListener('click', function(){
        var i = nextIndex();
        var div = document.createElement('div');
        div.className = 'smartsource-help-view-card';
        div.setAttribute('data-index', i);
        div.innerHTML = ''+
            '<div class="smartsource-help-view-header"><div><h4>New Instruction Section</h4></div><div class="smartsource-help-actions"><button type="button" class="btn btn-info btn-sm smartsource-toggle-help-edit"><i class="fa fa-pencil"></i> Edit</button><button type="button" class="btn btn-danger btn-sm smartsource-remove-help-section"><i class="fa fa-trash"></i> Delete</button></div></div>'+            
            '<div class="smartsource-help-preview-section"><div><p>Enter the new instruction details here.</p></div></div>'+            
            '<div class="smartsource-help-edit-card" style="display:block;"><div class="form-group"><label for="help_title_'+i+'">Title</label><input type="text" id="help_title_'+i+'" name="help_title[]" class="form-control" value="New Instruction Section"></div><div class="form-group"><label>Instructions</label><textarea name="help_content[]" class="tinymce" rows="8"><p>Enter the new instruction details here.</p></textarea></div></div>';
        wrap.appendChild(div);
        if(typeof init_editor === 'function'){
            init_editor();
        }
    });

    document.addEventListener('click', function(e){
        var editBtn = e.target.closest ? e.target.closest('.smartsource-toggle-help-edit') : null;
        if(editBtn){
            var editCard = editBtn.closest('.smartsource-help-view-card').querySelector('.smartsource-help-edit-card');
            if(editCard){
                editCard.style.display = editCard.style.display === 'none' || editCard.style.display === '' ? 'block' : 'none';
                editBtn.innerHTML = editCard.style.display === 'block' ? '<i class="fa fa-eye"></i> Hide Editor' : '<i class="fa fa-pencil"></i> Edit';
                if(typeof init_editor === 'function'){
                    init_editor();
                }
            }
            return;
        }

        var btn = e.target.closest ? e.target.closest('.smartsource-remove-help-section') : null;
        if(!btn){ return; }
        var card = btn.closest('.smartsource-help-view-card');
        if(card && confirm('Delete this instruction section?')){
            card.parentNode.removeChild(card);
        }
    });
})();
</script>
<div class="smartsource-module-footer"><?php echo html_escape(get_option('companyname')); ?> &mdash; <?php echo _l('smartsource_module_name'); ?></div>
<?php init_tail(); ?>

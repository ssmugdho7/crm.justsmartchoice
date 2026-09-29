<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="dm-enterprise-wrap">
    <div class="dm-card dm-hero-card">
        <div>
            <h4><i class="fa fa-folder-open"></i> <?php echo _l('dmg_enterprise_settings'); ?></h4>
            <p><?php echo _l('dmg_enterprise_settings_description'); ?></p>
        </div>
        <div class="dm-rating">★★★★★</div>
    </div>

    <?php echo form_open(admin_url('document_management/save_enterprise_settings')); ?>
    <div class="row">
        <div class="col-md-6">
            <div class="panel_s dm-card">
                <div class="panel-body">
                    <h4><i class="fa fa-sliders"></i> <?php echo _l('dmg_general'); ?></h4>
                    <?php
                    $selected_language = get_option('document_management_default_language');
                    if ($selected_language == '') { $selected_language = 'english'; }
                    echo render_select('document_management_default_language', [
                        ['id' => 'english', 'name' => 'English'],
                        ['id' => 'spanish', 'name' => 'Spanish'],
                    ], ['id','name'], _l('dmg_default_language'), $selected_language);
                    echo render_yes_no_option('document_management_allow_customer_portal', _l('dmg_customer_portal_access'));
                    echo render_yes_no_option('document_management_enable_watermark', _l('dmg_pdf_watermark'));
                    echo render_yes_no_option('document_management_enable_audit_log', _l('dmg_audit_log'));
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel_s dm-card">
                <div class="panel-body">
                    <h4><i class="fa fa-paint-brush"></i> <?php echo _l('dmg_branding'); ?></h4>
                    <?php
                    echo render_input('document_management_primary_color', _l('dmg_primary_color'), get_option('document_management_primary_color') ?: '#169179', 'text');
                    echo render_input('document_management_accent_color', _l('dmg_accent_color'), get_option('document_management_accent_color') ?: '#f47c20', 'text');
                    echo render_input('document_management_default_storage_folder', _l('dmg_default_storage_folder'), get_option('document_management_default_storage_folder') ?: 'modules/document_management/uploads/files', 'text');
                    ?>
                    <p class="text-muted"><?php echo _l('dmg_storage_warning'); ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="dm-actions">
        <button type="submit" class="btn btn-info"><i class="fa fa-save"></i> <?php echo _l('dmg_save'); ?></button>
        <a href="<?php echo admin_url('document_management/health'); ?>" class="btn btn-default"><i class="fa fa-heartbeat"></i> <?php echo _l('dmg_health_checker'); ?></a>
        <a href="<?php echo admin_url('document_management/help'); ?>" class="btn btn-default"><i class="fa fa-question-circle"></i> <?php echo _l('dmg_help_guide'); ?></a>
    </div>
    <?php echo form_close(); ?>
</div>

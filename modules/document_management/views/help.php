<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
    <div class="dm-card dm-hero-card">
        <div><h3><i class="fa fa-question-circle"></i> <?php echo _l('dmg_help_guide'); ?></h3><p><?php echo _l('dmg_help_description'); ?></p></div>
        <a href="<?php echo admin_url('document_management'); ?>" class="btn btn-info btn-sm"><i class="fa fa-folder-open"></i> <?php echo _l('dmg_file_management'); ?></a>
    </div>
    <div class="row">
        <div class="col-md-4"><div class="panel_s dm-card"><div class="panel-body"><h4><i class="fa fa-upload"></i> <?php echo _l('dmg_upload_documents'); ?></h4><p><?php echo _l('dmg_help_upload'); ?></p></div></div></div>
        <div class="col-md-4"><div class="panel_s dm-card"><div class="panel-body"><h4><i class="fa fa-share-alt"></i> <?php echo _l('dmg_share_documents'); ?></h4><p><?php echo _l('dmg_help_share'); ?></p></div></div></div>
        <div class="col-md-4"><div class="panel_s dm-card"><div class="panel-body"><h4><i class="fa fa-check-square"></i> <?php echo _l('dmg_approvals'); ?></h4><p><?php echo _l('dmg_help_approvals'); ?></p></div></div></div>
    </div>
    <div class="panel_s dm-card"><div class="panel-body">
        <h4><i class="fa fa-sitemap"></i> <?php echo _l('dmg_integrations'); ?></h4>
        <p><?php echo _l('dmg_help_integrations'); ?></p>
        <ul>
            <li><?php echo _l('dmg_help_customers'); ?></li>
            <li><?php echo _l('dmg_help_projects'); ?></li>
            <li><?php echo _l('dmg_help_contracts'); ?></li>
            <li><?php echo _l('dmg_help_permissions'); ?></li>
        </ul>
    </div></div>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>

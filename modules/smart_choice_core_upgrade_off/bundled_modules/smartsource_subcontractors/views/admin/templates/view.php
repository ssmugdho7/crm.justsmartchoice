<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
<?php echo smartsource_admin_submenu('templates'); ?>

        <div class="panel_s smartsource-panel">
            <div class="panel-body">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-4 smartsource-header-row">
                    <div>
                        <h4 class="tw-m-0"><?php echo html_escape($template->name); ?></h4>
                        <p class="text-muted tw-mb-0">Subcontractor Template Preview</p>
                    </div>
                    <div>
                        <a href="<?php echo admin_url('smartsource_subcontractors/templates/' . $template->id); ?>" class="btn btn-info"><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></a>
                        <a href="<?php echo admin_url('smartsource_subcontractors/copy_template/' . $template->id); ?>" class="btn btn-primary"><i class="fa fa-copy"></i> <?php echo _l('copy'); ?></a>
                        <a href="<?php echo admin_url('smartsource_subcontractors/templates'); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                <hr>
                <p><strong>Contract Type:</strong> <?php echo html_escape($template->contract_type); ?></p>
                <div class="smartsource-contract-preview">
                    <?php echo $template->content; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

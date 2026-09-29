<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s smartsource-panel smartsource-project-widget">
    <div class="panel-body">
        <h4><?php echo _l('smartsource_project_integration'); ?></h4>
        <h5><?php echo _l('smartsource_related_subcontractors'); ?></h5>
        <?php if (empty($subcontractors)) { ?>
            <p class="text-muted">No subcontractors linked to this project yet.</p>
        <?php } else { ?>
            <ul class="list-unstyled">
                <?php foreach ($subcontractors as $subcontractor) { ?>
                    <li><a href="<?php echo admin_url('smartsource_subcontractors/view/' . $subcontractor['id']); ?>"><i class="fa fa-user"></i> <?php echo html_escape($subcontractor['company']); ?></a> - <?php echo html_escape($subcontractor['project_trade']); ?></li>
                <?php } ?>
            </ul>
        <?php } ?>
        <hr>
        <h5><?php echo _l('smartsource_related_contracts'); ?></h5>
        <?php if (empty($contracts)) { ?>
            <p class="text-muted">No subcontractor contracts linked to this project yet.</p>
        <?php } else { ?>
            <ul class="list-unstyled">
                <?php foreach ($contracts as $contract) { ?>
                    <li><a href="<?php echo admin_url('smartsource_subcontractors/contract_view/' . $contract['id']); ?>"><i class="fa fa-file-text"></i> <?php echo html_escape($contract['subject']); ?></a> - <?php echo html_escape($contract['subcontractor_company']); ?></li>
                <?php } ?>
            </ul>
        <?php } ?>
        <a href="<?php echo admin_url('smartsource_subcontractors/contract?project_id=' . $project_id); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo _l('new_subcontractor_contract'); ?></a>
    </div>
</div>

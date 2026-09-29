<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s smartsource-panel smartsource-project-widget">
    <div class="panel-body">
        <h4><?php echo _l('sales_center_project_integration'); ?></h4>
        <h5><?php echo _l('sales_center_related_salespersons'); ?></h5>
        <?php if (empty($salespersons)) { ?>
            <p class="text-muted">No salespersons linked to this project yet.</p>
        <?php } else { ?>
            <ul class="list-unstyled">
                <?php foreach ($salespersons as $salesperson) { ?>
                    <li><a href="<?php echo admin_url('sales_center/view/' . $salesperson['id']); ?>"><i class="fa fa-user"></i> <?php echo html_escape($salesperson['company']); ?></a> - <?php echo html_escape($salesperson['project_trade']); ?></li>
                <?php } ?>
            </ul>
        <?php } ?>
        <hr>
        <h5><?php echo _l('sales_center_related_contracts'); ?></h5>
        <?php if (empty($contracts)) { ?>
            <p class="text-muted">No salesperson contracts linked to this project yet.</p>
        <?php } else { ?>
            <ul class="list-unstyled">
                <?php foreach ($contracts as $contract) { ?>
                    <li><a href="<?php echo admin_url('sales_center/contract_view/' . $contract['id']); ?>"><i class="fa fa-file-text"></i> <?php echo html_escape($contract['subject']); ?></a> - <?php echo html_escape($contract['salesperson_company']); ?></li>
                <?php } ?>
            </ul>
        <?php } ?>
        <a href="<?php echo admin_url('sales_center/contract?project_id=' . $project_id); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo _l('new_salesperson_contract'); ?></a>
    </div>
</div>

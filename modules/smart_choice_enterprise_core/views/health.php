<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div id="sc-enterprise-app"><div class="content"><div class="panel_s"><div class="panel-body">
<div class="sc-enterprise-header"><div><h1><?php echo html_escape($title); ?></h1><p><?php echo _l('system_health_description'); ?></p></div><div class="sc-toolbar"><a href="<?php echo admin_url('smart_choice_enterprise_core/repair_schema'); ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-screwdriver-wrench"></i> <?php echo _l('enterprise_schema_repair'); ?></a><a href="<?php echo admin_url('smart_choice_enterprise_core/health'); ?>" class="btn btn-default btn-sm"><i class="fa-solid fa-rotate"></i> <?php echo _l('reload'); ?></a></div></div>
<div class="table-responsive"><table class="table table-striped table-hover"><thead><tr><th><?php echo _l('check'); ?></th><th><?php echo _l('status'); ?></th><th><?php echo _l('details'); ?></th></tr></thead><tbody>
<?php foreach ($checks as $check) { ?><tr><td><?php echo html_escape($check['name']); ?></td><td><span class="label <?php echo $check['passed'] ? 'label-success' : 'label-danger'; ?>"><?php echo $check['passed'] ? _l('passed') : _l('action_required'); ?></span></td><td><?php echo html_escape($check['detail']); ?></td></tr><?php } ?>
</tbody></table></div>
</div></div></div></div>
</div><?php init_tail(); ?>

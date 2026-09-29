<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h4 class="tw-mt-0"><i class="fa-solid fa-heart-pulse text-success"></i> <?php echo _l('appointly_health_check'); ?></h4>
<p class="text-muted"><?php echo _l('appointly_health_check_help'); ?></p>
<table class="table table-striped"><thead><tr><th><?php echo _l('name'); ?></th><th><?php echo _l('status'); ?></th><th><?php echo _l('details'); ?></th></tr></thead><tbody>
<?php foreach ($checks as $check) { ?>
<tr><td><?php echo html_escape($check['name']); ?></td><td><?php if (($check['status'] ?? '') === 'optional') { echo '<span class="label label-default">Optional</span>'; } else { echo $check['passed'] ? '<span class="label label-success">Pass</span>' : '<span class="label label-danger">Fail</span>'; } ?></td><td><?php echo html_escape($check['details']); ?></td></tr>
<?php } ?>
</tbody></table>
</div></div></div></div>
<?php init_tail(); ?>

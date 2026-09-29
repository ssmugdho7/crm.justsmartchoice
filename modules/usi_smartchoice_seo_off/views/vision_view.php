<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<?php echo sammy_ai_nav(); ?>
<div class="panel_s"><div class="panel-body">
<?php if ($session) { ?>
<h4><?php echo html_escape($session['title']); ?></h4>
<p><strong><?php echo _l('sammy_ai_status'); ?>:</strong> <?php echo html_escape($session['status']); ?> | <strong><?php echo _l('sammy_ai_confidence'); ?>:</strong> <?php echo html_escape($session['confidence']); ?>%</p>
<a class="btn btn-info btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/analyze_vision/' . (int) $session['id']); ?>"><?php echo _l('sammy_ai_analyze_photos'); ?></a>
<hr>
<h4><?php echo _l('sammy_ai_findings'); ?></h4>
<div class="table-responsive"><table class="table table-striped table-condensed"><thead><tr><th><?php echo _l('sammy_ai_finding_type'); ?></th><th><?php echo _l('sammy_ai_description'); ?></th><th><?php echo _l('sammy_ai_quantity'); ?></th><th><?php echo _l('sammy_ai_unit'); ?></th><th><?php echo _l('sammy_ai_estimate_impact'); ?></th></tr></thead><tbody>
<?php foreach ($findings as $finding) { ?>
<tr><td><?php echo html_escape($finding['finding_type']); ?></td><td><?php echo html_escape($finding['description']); ?></td><td><?php echo html_escape($finding['quantity']); ?></td><td><?php echo html_escape($finding['unit']); ?></td><td><?php echo app_format_money($finding['estimate_impact'], get_base_currency()); ?></td></tr>
<?php } ?>
</tbody></table></div>
<?php } else { ?><p>Vision session not found.</p><?php } ?>
</div></div></div></div>
<?php init_tail(); ?>

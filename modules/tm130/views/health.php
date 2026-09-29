<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(TRAINING_MANUAL_ASSETS_PATH.'/css/training_manual_styles.css'); ?>">
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h4>Training Manual Health Check</h4><hr>
<table class="table table-bordered"><thead><tr><th>Check</th><th>Status</th><th>Message</th></tr></thead><tbody>
<?php foreach($checks as $check){ ?>
<tr><td><?php echo html_escape($check['name']); ?></td><td><span class="label label-<?php echo $check['status'] == 'ok' ? 'success' : ($check['status'] == 'bad' ? 'danger' : 'warning'); ?>"><?php echo strtoupper($check['status']); ?></span></td><td><?php echo html_escape($check['message']); ?></td></tr>
<?php } ?>
</tbody></table>
<a href="<?php echo admin_url('training_manual/settings'); ?>" class="btn btn-default btn-sm">Back To Settings</a>
<a href="<?php echo current_url(); ?>" class="btn btn-default btn-sm">Refresh</a>
</div></div></div></div><?php init_tail(); ?></body></html>

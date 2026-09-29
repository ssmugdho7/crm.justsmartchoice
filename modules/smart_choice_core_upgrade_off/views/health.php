<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-choice-core-page"><div class="row"><div class="col-md-12">

<div class="panel_s"><div class="panel-body"><h3>Smart Choice Health Check</h3><p>Checks core options, module folders, database tables, localization, and reports.</p><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Check</th><th>Status</th><th>Message</th></tr></thead><tbody><?php foreach ($health as $row) { $class = $row['status']==='ok'?'success':($row['status']==='warning'?'warning':'danger'); ?><tr><td><?php echo html_escape($row['name']); ?></td><td><span class="label label-<?php echo $class; ?>"><?php echo ucfirst($row['status']); ?></span></td><td><?php echo html_escape($row['message']); ?></td></tr><?php } ?></tbody></table></div></div></div>
</div></div></div></div><?php init_tail(); ?>

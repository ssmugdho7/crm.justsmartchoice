<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<?php echo sc_ai_video_studio_nav(); ?>

<div class="panel_s"><div class="panel-body"><div class="scv-toolbar"><h4>Health</h4><a class="btn btn-default btn-sm" href="<?php echo admin_url('sc_ai_video_studio/repair_database'); ?>">Repair Database</a></div>
<table class="table scv-table"><thead><tr><th>Name</th><th>Status</th></tr></thead><tbody><?php foreach ($checks as $check) { ?><tr><td><?php echo html_escape($check[0]); ?></td><td><span class="label label-<?php echo $check[1] ? 'success' : 'danger'; ?>"><?php echo $check[1] ? 'OK' : 'Fail'; ?></span></td></tr><?php } ?></tbody></table>
</div></div>
</div></div></div></div>
<?php init_tail(); ?>
</body></html>

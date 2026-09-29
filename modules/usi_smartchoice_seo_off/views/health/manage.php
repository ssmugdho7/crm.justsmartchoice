<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content usi-seo"><div class="row"><div class="col-md-12">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="panel_s"><div class="panel-body">
<div class="usi-toolbar"><h4><?php echo _l('usi_smartchoice_seo_health'); ?></h4><div class="usi-actions"><a href="<?php echo admin_url('usi_smartchoice_seo/repair_database'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-database"></i> <?php echo _l('usi_smartchoice_seo_repair_database'); ?></a><a href="<?php echo admin_url('usi_smartchoice_seo/health'); ?>" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reload</a></div></div>
<div class="table-responsive"><table class="table table-striped table-condensed usi-compact-table"><thead><tr><th><?php echo _l('name'); ?></th><th><?php echo _l('status'); ?></th></tr></thead><tbody><?php foreach ($checks as $check) { ?><tr><td><?php echo html_escape($check['label']); ?></td><td><?php echo $check['status'] ? '<span class="label label-success">OK</span>' : '<span class="label label-danger">Fail</span>'; ?></td></tr><?php } ?></tbody></table></div></div></div>
</div></div></div></div>
<?php init_tail(); ?>
</body>
</html>

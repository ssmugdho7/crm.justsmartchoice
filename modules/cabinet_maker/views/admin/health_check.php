<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><?php $this->load->view('admin/_nav'); ?>
<div class="content"><div class="panel_s"><div class="panel-body">
<h4><?= _l('cabinet_maker_health_check'); ?></h4>
<table class="table table-striped"><thead><tr><th><?= _l('cabinet_maker_check'); ?></th><th><?= _l('cabinet_maker_status'); ?></th></tr></thead><tbody>
<?php foreach($checks as $check){ ?><tr><td><?= html_escape($check['label']); ?></td><td><span class="label label-<?= strpos($check['status'],'ok')===0?'success':'danger'; ?>"><?= html_escape(ucfirst($check['status'])); ?></span></td></tr><?php } ?>
</tbody></table>
<a class="btn btn-primary btn-sm" href="<?= admin_url('cabinet_maker/upgrade_database'); ?>"><i class="fa fa-database"></i> <?= _l('cabinet_maker_repair_database'); ?></a>
</div></div></div></div><?php init_tail(); ?>

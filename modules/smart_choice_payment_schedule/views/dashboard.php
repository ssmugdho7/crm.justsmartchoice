<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
<div class="panel_s"><div class="panel-body">
<h4 class="no-margin"><i class="fa-solid fa-money-check-dollar"></i> <?= _l('scps_sales_tools'); ?></h4><hr>
<div class="row">
<div class="col-md-4"><div class="scps-stat"><strong><?= (int) $stats['pending_count']; ?></strong><span><?= _l('scps_pending_installments'); ?></span></div></div>
<div class="col-md-4"><div class="scps-stat"><strong><?= app_format_money($stats['pending_value'], get_base_currency()); ?></strong><span><?= _l('scps_pending_value'); ?></span></div></div>
<div class="col-md-4"><div class="scps-stat"><a class="btn btn-primary" href="<?= admin_url('smart_choice_payment_schedule/import_tasks'); ?>"><i class="fa fa-upload"></i> <?= _l('scps_import_tasks'); ?></a> <a class="btn btn-default" href="<?= admin_url('smart_choice_payment_schedule/sample_tasks'); ?>"><i class="fa fa-download"></i> <?= _l('scps_sample_header'); ?></a></div></div>
</div>
<div class="alert alert-info mtop20"><?= _l('scps_accounting_explanation'); ?></div>
</div></div></div></div></div></div>
<?php init_tail(); ?>

<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-8 col-md-offset-2"><div class="panel_s"><div class="panel-body">
<h4><?= _l('scps_import_tasks'); ?></h4><hr>
<a class="btn btn-default mbot15" href="<?= admin_url('smart_choice_payment_schedule/sample_tasks'); ?>"><i class="fa fa-download"></i> <?= _l('scps_sample_header'); ?></a>
<?= form_open_multipart(admin_url('smart_choice_payment_schedule/import_tasks')); ?>
<div class="form-group"><label><?= _l('scps_csv_file'); ?></label><input type="file" name="task_file" accept=".csv" class="form-control" required></div>
<button class="btn btn-primary" type="submit"><i class="fa fa-upload"></i> <?= _l('scps_import_tasks'); ?></button>
<?= form_close(); ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>

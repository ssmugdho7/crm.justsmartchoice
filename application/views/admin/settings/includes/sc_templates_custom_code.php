<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="tw-font-semibold tw-mt-0"><i class="fa-solid fa-file-code"></i> <?= _l('sc_templates_custom_code'); ?></h4>
<p class="text-muted"><?= _l('sc_templates_custom_code_help'); ?></p>
<div class="row">
  <div class="col-md-4"><a class="btn btn-default btn-block" href="<?= admin_url('emails'); ?>"><i class="fa fa-envelope"></i> <?= _l('email_templates'); ?></a></div>
  <div class="col-md-4"><a class="btn btn-default btn-block" href="<?= admin_url('settings?group=pdf'); ?>"><i class="fa fa-file-pdf"></i> <?= _l('settings_pdf'); ?></a></div>
  <div class="col-md-4"><a class="btn btn-default btn-block" href="<?= admin_url('settings?group=proposals'); ?>"><i class="fa fa-file"></i> <?= _l('proposals'); ?></a></div>
</div>
<hr>
<?= render_textarea('settings[sc_custom_admin_js]','sc_custom_admin_js',get_option('sc_custom_admin_js'),['rows'=>12],[],'',''); ?>
<p class="text-muted"><?= _l('sc_custom_admin_js_help'); ?></p>
<?= render_textarea('settings[sc_custom_client_js]','sc_custom_client_js',get_option('sc_custom_client_js'),['rows'=>12],[],'',''); ?>
<p class="text-muted"><?= _l('sc_custom_client_js_help'); ?></p>
<?= render_input('settings[sc_staff_idle_timeout_minutes]','sc_staff_idle_timeout_minutes',get_option('sc_staff_idle_timeout_minutes') ?: '10','number',['min'=>1,'max'=>480]); ?>
<p class="text-muted"><?= _l('sc_staff_idle_timeout_help'); ?></p>

<?php
$CI = &get_instance();
$CI->load->model('smart_choice_payment_schedule/smart_choice_payment_schedule_model');
$stats = $CI->smart_choice_payment_schedule_model->dashboard_stats();
?>
<div class="widget" id="widget-sc-payment-schedule" data-name="<?= _l('scps_payment_schedule'); ?>">
<div class="panel_s"><div class="panel-body"><div class="widget-dragger"></div>
<h4 class="no-margin"><i class="fa-solid fa-money-check-dollar"></i> <?= _l('scps_payment_schedule'); ?></h4><hr>
<div class="row"><div class="col-xs-6"><strong class="tw-text-2xl"><?= (int) $stats['pending_count']; ?></strong><div class="text-muted"><?= _l('scps_pending_installments'); ?></div></div>
<div class="col-xs-6"><strong class="tw-text-2xl"><?= app_format_money($stats['pending_value'], get_base_currency()); ?></strong><div class="text-muted"><?= _l('scps_pending_value'); ?></div></div></div>
</div></div></div>

<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body ideal-admin-page">
<div class="clearfix"><h4 class="pull-left"><i class="fa-brands fa-stripe"></i> <?php echo _l('ideal_dashboard'); ?></h4><div class="pull-right"><a href="<?php echo admin_url('settings?group=payment_gateways&tab=online_payments_Ideal_gateway_tab'); ?>" class="btn btn-primary btn-sm"><?php echo _l('ideal_gateway_settings'); ?></a> <a href="<?php echo admin_url('ideal_admin/test_connection'); ?>" class="btn btn-info btn-sm"><?php echo _l('ideal_test_connection'); ?></a></div></div><hr>
<div class="row ideal-health-grid">
<?php foreach ([[_l('ideal_api_keys'),$health['keys']],[_l('ideal_stripe_sdk'),$health['stripe_sdk']],[_l('ideal_webhook'),$health['webhook']],[_l('ideal_events_table'),$health['events_table']],[_l('ideal_subscriptions_table'),$health['subscriptions_table']]] as $item): ?>
<div class="col-md-2 col-sm-4"><div class="ideal-card"><b><?php echo $item[0]; ?></b><div class="mtop10"><span class="label label-<?php echo $item[1]?'success':'danger'; ?>"><?php echo $item[1]?_l('ideal_ok'):_l('ideal_attention'); ?></span></div></div></div><?php endforeach; ?>
<div class="col-md-2 col-sm-4"><div class="ideal-card"><b><?php echo _l('ideal_environment'); ?></b><div class="mtop10"><?php echo html_escape($health['environment']); ?></div></div></div>
</div>
<div class="alert alert-info mtop20"><b><?php echo _l('ideal_webhook_url'); ?>:</b> <?php echo html_escape($health['webhook_url']); ?></div>
<div class="panel_s mtop20"><div class="panel-body"><h4><?php echo _l('ideal_currency_and_fee_settings'); ?></h4>
<div class="alert alert-info"><?php echo _l('ideal_crm_currency_detected'); ?>: <strong><?php echo html_escape($crm_currency); ?></strong>. <?php echo _l('ideal_currency_note'); ?></div>
<?php echo form_open(admin_url('ideal_admin/save_module_settings')); ?>
<div class="row">
<div class="col-md-3"><?php echo render_yes_no_option('use_crm_currency','ideal_use_crm_currency', $settings['use_crm_currency'] === '1'); ?></div>
<div class="col-md-3"><?php echo render_input('fallback_currency','ideal_fallback_currency',$settings['fallback_currency'] ?: $crm_currency); ?></div>
<div class="col-md-3"><?php echo render_yes_no_option('fee_enabled','ideal_fee_enabled', $settings['fee_enabled'] === '1'); ?></div>
<div class="col-md-3"><?php echo render_input('fee_percent','ideal_fee_percent',$settings['fee_percent'],'number',['step'=>'0.01','min'=>'0','max'=>'100']); ?></div>
</div><div class="row">
<div class="col-md-3"><?php echo render_input('fee_fixed','ideal_fee_fixed',$settings['fee_fixed'],'number',['step'=>'0.01','min'=>'0']); ?></div>
<div class="col-md-6"><?php echo render_input('fee_label','ideal_fee_label',$settings['fee_label']); ?></div>
<div class="col-md-3 mtop25"><button type="submit" class="btn btn-primary btn-sm"><?php echo _l('save'); ?></button></div>
</div><?php echo form_close(); ?></div></div>
<div class="row mtop20"><div class="col-md-5"><h4><?php echo _l('ideal_create_subscription_link'); ?></h4><?php echo form_open(admin_url('ideal_admin/create_subscription_link')); ?>
<?php echo render_input('name','ideal_subscription_name'); ?><?php echo render_input('stripe_price_id','ideal_stripe_price_id'); ?><?php echo render_input('customer_email','ideal_customer_email','', 'email'); ?>
<button class="btn btn-primary btn-sm" type="submit"><?php echo _l('create'); ?></button><?php echo form_close(); ?></div>
<div class="col-md-7"><h4><?php echo _l('ideal_subscription_links'); ?></h4><div class="table-responsive"><table class="table table-striped"><thead><tr><th><?php echo _l('ideal_subscription_name'); ?></th><th><?php echo _l('ideal_stripe_price_id'); ?></th><th><?php echo _l('ideal_customer_email'); ?></th><th><?php echo _l('date_created'); ?></th><th><?php echo _l('options'); ?></th></tr></thead><tbody><?php foreach($links as $link): ?><tr><td><?php echo html_escape($link['name']); ?></td><td><?php echo html_escape($link['stripe_price_id']); ?></td><td><?php echo html_escape($link['customer_email']); ?></td><td><?php echo _dt($link['created_at']); ?></td><td><?php if($link['checkout_url']): ?><a target="_blank" class="btn btn-default btn-xs" href="<?php echo html_escape($link['checkout_url']); ?>"><?php echo _l('view'); ?></a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<h4 class="mtop30"><?php echo _l('ideal_recent_webhook_events'); ?></h4><div class="table-responsive"><table class="table table-striped"><thead><tr><th><?php echo _l('ideal_event_type'); ?></th><th><?php echo _l('ideal_object_id'); ?></th><th><?php echo _l('ideal_status'); ?></th><th><?php echo _l('date_created'); ?></th></tr></thead><tbody><?php foreach($events as $event): ?><tr><td><?php echo html_escape($event['event_type']); ?></td><td><?php echo html_escape($event['object_id']); ?></td><td><?php echo html_escape($event['status']); ?></td><td><?php echo _dt($event['created_at']); ?></td></tr><?php endforeach; ?></tbody></table></div>
</div></div></div></div></div></div><?php init_tail(); ?>

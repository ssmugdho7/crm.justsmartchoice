<?php $this->load->view('stripe_hub/_header'); ?><div class="panel_s"><div class="panel-body"><h4><?= _l('stripe_hub_settings'); ?></h4><?= form_open(admin_url('stripe_hub/settings')); ?>
<div class="checkbox checkbox-primary"><input type="checkbox" id="use_core_gateway" name="use_core_gateway" value="1" <?= get_option('stripe_hub_use_core_gateway')=='1'?'checked':''; ?>><label for="use_core_gateway"><?= _l('stripe_hub_use_core_gateway'); ?></label></div><p class="text-muted"><?= _l('stripe_hub_use_core_gateway_help'); ?></p><hr>
<?= render_input('publishable_key','stripe_hub_publishable_key',$publishable_key); ?>
<?= render_input('secret_key','stripe_hub_secret_key','', 'password',['autocomplete'=>'new-password','placeholder'=>$secret_masked]); ?>
<?= render_input('webhook_secret','stripe_hub_webhook_secret','', 'password',['autocomplete'=>'new-password','placeholder'=>$webhook_masked]); ?>
<?= render_input('default_currency','stripe_hub_default_currency',get_option('stripe_hub_default_currency')); ?>
<?= render_input('items_per_page','stripe_hub_items_per_page',get_option('stripe_hub_items_per_page'),'number',['min'=>5,'max'=>100]); ?>
<div class="form-group"><label><?= _l('stripe_hub_webhook_url'); ?></label><input class="form-control" readonly value="<?= e(site_url('stripe_hub/stripe_hub_webhook')); ?>"></div>
<button type="submit" class="btn btn-primary"><?= _l('save'); ?></button><?= form_close(); ?></div></div><?php $this->load->view('stripe_hub/_footer'); ?>

<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('telegram_chat/_nav'); ?>
<div class="telegram-page-header"><div><h3><?= _l('telegram_bot_profile'); ?></h3><p><?= _l('telegram_bot_profile_description'); ?></p></div><div><a class="btn btn-success" href="<?= admin_url('telegram_chat/install_webhook'); ?>"><?= _l('telegram_install_webhook'); ?></a> <a class="btn btn-danger" href="<?= admin_url('telegram_chat/delete_webhook'); ?>"><?= _l('telegram_remove_webhook'); ?></a></div></div>
<?= form_open(admin_url('telegram_chat/save_bot_profile')); ?>
<?= render_input('bot_name', _l('telegram_bot_name'), $bot_info['result']['first_name']??''); ?>
<?= render_textarea('bot_short_description', _l('telegram_bot_short_description')); ?>
<?= render_textarea('bot_description', _l('telegram_bot_description')); ?>
<button class="btn btn-primary"><?= _l('save'); ?></button>
<?= form_close(); ?>
<hr><h4><?= _l('telegram_webhook_status'); ?></h4><pre><?= html_escape(json_encode($webhook_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)); ?></pre>
</div></div></div></div><?php init_tail(); ?>

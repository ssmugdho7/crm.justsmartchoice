<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('telegram_chat/_nav'); ?>
<div class="telegram-page-header"><div><h3><?= _l('telegram_help_training'); ?></h3><p><?= _l('telegram_training_description'); ?></p></div></div>
<div class="telegram-training-grid">
<?php foreach ([
[_l('telegram_training_connect_title'),_l('telegram_training_connect_text')],
[_l('telegram_training_appointments_title'),_l('telegram_training_appointments_text')],
[_l('telegram_training_webhook_title'),_l('telegram_training_webhook_text')],
[_l('telegram_training_bot_profile_title'),_l('telegram_training_bot_profile_text')],
[_l('telegram_training_store_title'),_l('telegram_training_store_text')],
[_l('telegram_training_messages_title'),_l('telegram_training_messages_text')],
] as $card): ?><div class="telegram-training-card"><h4><?= html_escape($card[0]); ?></h4><p><?= html_escape($card[1]); ?></p></div><?php endforeach; ?>
</div></div></div></div></div><?php init_tail(); ?>

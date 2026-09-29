<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('telegram_chat/_nav'); ?>
<div class="telegram-page-header"><div><h3><?= _l('telegram_chat_settings'); ?></h3><p><?= _l('telegram_settings_description'); ?></p></div></div>
<?= form_open(admin_url('telegram_chat/save_settings')); ?>
<div class="row">
 <div class="col-md-4"><?= render_input('bot_token', _l('telegram_bot_token'), $userTeleInfo->bot_token ?? '', 'password', ['autocomplete'=>'off']); ?></div>
 <div class="col-md-4"><?= render_input('chat_id', _l('telegram_chat_id'), $userTeleInfo->chat_id ?? '', 'text'); ?></div>
 <div class="col-md-4"><?= render_input('bot_username', _l('telegram_bot_username'), $userTeleInfo->bot_username ?? '', 'text'); ?></div>
</div>
<div class="row">
 <div class="col-md-4"><?= render_input('telegram_chat_default_chat_id', _l('telegram_default_chat_id'), get_option('telegram_chat_default_chat_id')); ?></div>
 <div class="col-md-4"><?= render_input('telegram_chat_store_url', _l('telegram_store_url'), get_option('telegram_chat_store_url'), 'url'); ?></div>
 <div class="col-md-4"><?= render_input('telegram_chat_mini_app_url', _l('telegram_mini_app_url'), get_option('telegram_chat_mini_app_url'), 'url'); ?></div>
 <div class="col-md-4"><?= render_input('telegram_chat_support_url', _l('telegram_support_url'), get_option('telegram_chat_support_url'), 'url'); ?></div>
 <div class="col-md-4"><?= render_input('telegram_chat_privacy_url', _l('telegram_privacy_url'), get_option('telegram_chat_privacy_url'), 'url'); ?></div>
 <div class="col-md-4"><?= render_input('telegram_chat_terms_url', _l('telegram_terms_url'), get_option('telegram_chat_terms_url'), 'url'); ?></div>
</div>
<hr>
<div class="row telegram-option-grid">
<?php foreach ([
 'telegram_chat_enable_message_log'=>_l('telegram_save_message_log'),
 'telegram_chat_enable_client_login_notice'=>_l('telegram_notify_client_login'),
 'telegram_chat_enable_ticket_notice'=>_l('telegram_notify_ticket_activity'),
 'telegram_chat_enable_proposal_notice'=>_l('telegram_notify_proposal_activity'),
 'telegram_chat_enable_file_notice'=>_l('telegram_notify_file_uploads'),
 'telegram_chat_enable_portal_message_notice'=>_l('telegram_notify_portal_messages'),
 'telegram_chat_enable_appointment_monitor'=>_l('telegram_enable_appointment_monitor'),
 'telegram_chat_notify_new_appointments'=>_l('telegram_notify_new_appointments'),
 'telegram_chat_notify_appointment_changes'=>_l('telegram_notify_appointment_changes'),
] as $key=>$label): ?>
<div class="col-md-4"><?php render_yes_no_option($key, $label); ?></div>
<?php endforeach; ?>
</div>
<div class="text-right"><button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= _l('save'); ?></button></div>
<?= form_close(); ?>
</div></div></div></div>
<?php init_tail(); ?>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s telegram-smart-panel"><div class="panel-body">
<?php $this->load->view('telegram_chat/top_navigation'); ?>
<div class="telegram-header"><div><h3><?php echo _l('telegram_online_links'); ?></h3><p><?php echo _l('telegram_links_description'); ?></p></div></div>
<?php $username = trim((string)get_option('telegram_chat_bot_username')); ?>
<div class="row telegram-link-grid">
<div class="col-md-6"><a class="telegram-link-card" target="_blank" rel="noopener" href="https://web.telegram.org/"><i class="fa-brands fa-telegram"></i><strong><?php echo _l('telegram_open_web'); ?></strong></a></div>
<div class="col-md-6"><a class="telegram-link-card" target="_blank" rel="noopener" href="https://telegram.me/BotFather"><i class="fa-solid fa-robot"></i><strong><?php echo _l('telegram_open_botfather'); ?></strong></a></div>
<?php if ($username !== '') { ?><div class="col-md-6"><a class="telegram-link-card" target="_blank" rel="noopener" href="https://t.me/<?php echo html_escape(ltrim($username,'@')); ?>"><i class="fa-solid fa-paper-plane"></i><strong><?php echo _l('telegram_open_bot'); ?></strong></a></div><?php } ?>
<div class="col-md-6"><a class="telegram-link-card" target="_blank" rel="noopener" href="https://core.telegram.org/bots/api"><i class="fa-solid fa-code"></i><strong><?php echo _l('telegram_open_api_docs'); ?></strong></a></div>
<div class="col-md-6"><a class="telegram-link-card" target="_blank" rel="noopener" href="https://core.telegram.org/bots/webhooks"><i class="fa-solid fa-link"></i><strong><?php echo _l('telegram_open_webhook_docs'); ?></strong></a></div>
</div><div class="alert alert-warning mtop20"><?php echo _l('telegram_online_security_note'); ?></div>
</div></div></div></div><?php init_tail(); ?>
<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('telegram_chat/_nav'); ?>
<div class="telegram-page-header"><div><h3><?= _l('telegram_online_links'); ?></h3><p><?= _l('telegram_online_links_description'); ?></p></div></div>
<?php $username = !empty($account->bot_username) ? ltrim($account->bot_username,'@') : ''; ?>
<div class="telegram-link-grid">
<a target="_blank" href="https://web.telegram.org/"><i class="fa-brands fa-telegram"></i><strong>Telegram Web</strong><span><?= _l('telegram_open_web_chat'); ?></span></a>
<a target="_blank" href="https://t.me/BotFather"><i class="fa-solid fa-robot"></i><strong>BotFather</strong><span><?= _l('telegram_manage_bot'); ?></span></a>
<?php if ($username): ?><a target="_blank" href="https://t.me/<?= rawurlencode($username); ?>"><i class="fa-solid fa-comment-dots"></i><strong>@<?= html_escape($username); ?></strong><span><?= _l('telegram_open_your_bot'); ?></span></a><?php endif; ?>
<a target="_blank" href="https://core.telegram.org/bots/api"><i class="fa-solid fa-book"></i><strong>Bot API</strong><span><?= _l('telegram_api_documentation'); ?></span></a>
<a target="_blank" href="https://core.telegram.org/bots/webapps"><i class="fa-solid fa-mobile-screen"></i><strong>Mini Apps</strong><span><?= _l('telegram_mini_apps_documentation'); ?></span></a>
<a target="_blank" href="https://core.telegram.org/bots/payments"><i class="fa-solid fa-credit-card"></i><strong>Payments</strong><span><?= _l('telegram_payments_documentation'); ?></span></a>
</div>
<div class="form-group mtop20"><label><?= _l('telegram_webhook_url'); ?></label><input class="form-control" readonly value="<?= html_escape($webhook_url); ?>"></div>
</div></div></div></div><?php init_tail(); ?>

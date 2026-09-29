<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="telegram-module-nav">
  <a href="<?= admin_url('telegram_chat'); ?>"><i class="fa-brands fa-telegram"></i> <?= _l('telegram_chat_settings'); ?></a>
  <a href="<?= admin_url('telegram_chat/messages'); ?>"><i class="fa-solid fa-comments"></i> <?= _l('telegram_message_center'); ?></a>
  <a href="<?= admin_url('telegram_chat/appointments'); ?>"><i class="fa-solid fa-calendar-check"></i> <?= _l('telegram_appointments_monitor'); ?></a>
  <a href="<?= admin_url('telegram_chat/online_links'); ?>"><i class="fa-solid fa-arrow-up-right-from-square"></i> <?= _l('telegram_online_links'); ?></a>
  <a href="<?= admin_url('telegram_chat/bot_profile'); ?>"><i class="fa-solid fa-robot"></i> <?= _l('telegram_bot_profile'); ?></a>
  <a href="<?= admin_url('telegram_chat/health'); ?>"><i class="fa-solid fa-heart-pulse"></i> <?= _l('telegram_health_check'); ?></a>
  <a href="<?= admin_url('telegram_chat/training'); ?>"><i class="fa-solid fa-graduation-cap"></i> <?= _l('telegram_help_training'); ?></a>
</div>

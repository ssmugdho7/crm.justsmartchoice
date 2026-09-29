<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('telegram_chat/_nav'); ?>
<div class="telegram-page-header"><div><h3><?= _l('telegram_message_center'); ?></h3><p><?= _l('telegram_message_center_description'); ?></p></div><div><a class="btn btn-info" href="<?= admin_url('telegram_chat/sync_updates'); ?>"><i class="fa-solid fa-rotate"></i> <?= _l('telegram_sync_updates'); ?></a></div></div>
<?= form_open(admin_url('telegram_chat/delete_messages')); ?>
<div class="telegram-toolbar"><input class="form-control" name="q" value="<?= html_escape($filters['q']??''); ?>" placeholder="<?= _l('search'); ?>"><a class="btn btn-default" href="<?= admin_url('telegram_chat/messages'); ?>"><?= _l('telegram_reload'); ?></a><button class="btn btn-danger" type="submit"><?= _l('delete'); ?></button></div>
<div class="table-responsive"><table class="table table-striped table-telegram"><thead><tr><th><input type="checkbox" onclick="$('.telegram-message-check').prop('checked',this.checked)"></th><th><?= _l('telegram_direction'); ?></th><th><?= _l('telegram_module'); ?></th><th><?= _l('telegram_action'); ?></th><th><?= _l('telegram_message'); ?></th><th><?= _l('telegram_date'); ?></th></tr></thead><tbody>
<?php foreach($messages as $row): ?><tr><td><input class="telegram-message-check" type="checkbox" name="ids[]" value="<?= (int)$row['id']; ?>"></td><td><?= html_escape($row['message_direction']); ?></td><td><?= html_escape($row['module']); ?></td><td><?= html_escape($row['action']); ?></td><td class="telegram-message-text"><?= nl2br(html_escape($row['message_text'])); ?></td><td><?= html_escape($row['created_at']); ?></td></tr><?php endforeach; ?>
</tbody></table></div><p><?= _l('telegram_total_messages'); ?>: <?= (int)$total; ?></p><?= form_close(); ?>
</div></div></div></div><?php init_tail(); ?>

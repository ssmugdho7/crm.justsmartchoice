<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('telegram_chat/_nav'); ?>
<div class="telegram-page-header"><div><h3><?= _l('telegram_health_check'); ?></h3><p><?= _l('telegram_health_description'); ?></p></div><div><a class="btn btn-warning" href="<?= admin_url('telegram_chat/send_test'); ?>"><?= _l('telegram_send_test'); ?></a></div></div>
<div class="row"><?php foreach($checks as $label=>$ok): ?><div class="col-md-4"><div class="telegram-health-card"><strong><?= html_escape($label); ?></strong><span class="label label-<?= $ok?'success':'danger'; ?>"><?= $ok?_l('telegram_ready'):_l('telegram_missing'); ?></span></div></div><?php endforeach; ?></div>
<div class="alert alert-info mtop20"><?= _l('telegram_saved_messages'); ?>: <?= (int)$messages_count; ?></div>
<pre><?= html_escape(json_encode($bot_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)); ?></pre>
</div></div></div></div><?php init_tail(); ?>

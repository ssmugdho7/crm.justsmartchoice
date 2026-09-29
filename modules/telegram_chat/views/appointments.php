<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('telegram_chat/_nav'); ?>
<div class="telegram-page-header"><div><h3><?= _l('telegram_appointments_monitor'); ?></h3><p><?= _l('telegram_appointments_monitor_description'); ?></p></div><div><a class="btn btn-primary" href="<?= admin_url('telegram_chat/scan_appointments'); ?>"><i class="fa-solid fa-rotate"></i> <?= _l('telegram_scan_now'); ?></a></div></div>
<?php if (!$appointly_available): ?><div class="alert alert-warning"><?= _l('telegram_appointly_not_available'); ?></div><?php else: ?>
<div class="alert alert-info"><?= _l('telegram_last_scan'); ?>: <?= html_escape($last_scan ?: _l('telegram_never')); ?></div>
<div class="table-responsive"><table class="table table-striped table-telegram"><thead><tr><th>ID</th><th><?= _l('telegram_subject'); ?></th><th><?= _l('telegram_customer'); ?></th><th><?= _l('telegram_date_time'); ?></th><th><?= _l('telegram_status'); ?></th><th><?= _l('telegram_contact'); ?></th></tr></thead><tbody>
<?php foreach ($appointments as $row): ?><tr><td><?= (int)($row['id']??0); ?></td><td><?= html_escape($row['subject']??''); ?></td><td><?= html_escape($row['name']??''); ?></td><td><?= html_escape(trim(($row['date']??'').' '.($row['start_hour']??''))); ?></td><td><span class="label label-info"><?= html_escape($row['status']??''); ?></span></td><td><?= html_escape($row['email']??''); ?><br><?= html_escape($row['phone']??''); ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</div></div></div></div><?php init_tail(); ?>

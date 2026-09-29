<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
<div class="tw-flex tw-items-center tw-justify-between tw-flex-wrap tw-gap-3">
    <div>
        <h4 class="tw-mt-0 tw-mb-1"><i class="fa fa-clock"></i> <?= _l('sc_staff_idle_report'); ?></h4>
        <p class="text-muted tw-mb-0"><?= _l('sc_staff_idle_report_help'); ?></p>
    </div>
</div>
<hr>
<form method="get" action="<?= admin_url('utilities/staff_idle_report'); ?>" class="row">
<div class="col-md-3">
    <label><?= _l('sc_idle_report_period'); ?></label>
    <select name="period" class="selectpicker" data-width="100%">
        <option value="daily"<?= $period==='daily'?' selected':''; ?>><?= _l('sc_idle_daily'); ?></option>
        <option value="weekly"<?= $period==='weekly'?' selected':''; ?>><?= _l('sc_idle_weekly'); ?></option>
        <option value="monthly"<?= $period==='monthly'?' selected':''; ?>><?= _l('sc_idle_monthly'); ?></option>
    </select>
</div>
<div class="col-md-3"><label><?= _l('sc_idle_start_date'); ?></label><input type="date" name="start_date" class="form-control" value="<?= e($start_date); ?>"></div>
<div class="col-md-3"><label><?= _l('sc_idle_end_date'); ?></label><input type="date" name="end_date" class="form-control" value="<?= e($end_date); ?>"></div>
<div class="col-md-3" style="padding-top:24px">
    <button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i> <?= _l('filter'); ?></button>
    <a class="btn btn-default" href="<?= admin_url('utilities/staff_idle_report'); ?>"><i class="fa fa-rotate-right"></i> <?= _l('reset'); ?></a>
</div>
</form>
<hr>
<h5 class="tw-font-semibold"><?= _l('sc_idle_summary'); ?> — <?= _l('sc_idle_'.$period); ?></h5>
<div class="table-responsive"><table class="table dt-table"><thead><tr><th><?= _l('staff_member'); ?></th><th><?= _l('staff_email'); ?></th><th><?= _l('sc_idle_period'); ?></th><th><?= _l('sc_idle_events'); ?></th><th><?= _l('sc_idle_minutes_total'); ?></th></tr></thead><tbody>
<?php foreach($summary as $row){ ?><tr><td><?= e(trim(($row['firstname']??'').' '.($row['lastname']??''))); ?></td><td><?= e($row['email']??''); ?></td><td><?= e($row['idle_period']??''); ?></td><td><?= (int)$row['idle_events']; ?></td><td><?= (int)$row['idle_minutes_total']; ?></td></tr><?php } ?>
</tbody></table></div>
<hr><h5 class="tw-font-semibold"><?= _l('sc_idle_event_details'); ?></h5>
<div class="table-responsive"><table class="table dt-table"><thead><tr><th><?= _l('staff_member'); ?></th><th><?= _l('staff_email'); ?></th><th><?= _l('sc_idle_minutes'); ?></th><th><?= _l('date'); ?></th><th><?= _l('sc_ip_address'); ?></th></tr></thead><tbody>
<?php foreach($rows as $row){ ?><tr><td><?= e(trim(($row['firstname']??'').' '.($row['lastname']??''))); ?></td><td><?= e($row['email']??''); ?></td><td><?= (int)$row['idle_minutes']; ?></td><td><?= e(_dt($row['recorded_at'])); ?></td><td><?= e($row['ip_address']??''); ?></td></tr><?php } ?>
</tbody></table></div>
<?php if (is_admin()) { ?>
<hr><div class="panel_s"><div class="panel-body" style="background:#fafafa">
<h5 class="tw-font-semibold tw-mt-0"><i class="fa fa-trash"></i> <?= _l('sc_idle_log_retention'); ?></h5>
<p class="text-muted"><?= _l('sc_idle_log_retention_help'); ?></p>
<?= form_open(admin_url('utilities/delete_staff_idle_logs'), ['onsubmit'=>"return confirm('".e(_l('sc_idle_delete_confirm'))."');"]); ?>
<div class="row">
<div class="col-md-4"><label><?= _l('sc_idle_start_date'); ?></label><input type="date" name="delete_start_date" class="form-control"></div>
<div class="col-md-4"><label><?= _l('sc_idle_end_date'); ?></label><input type="date" name="delete_end_date" class="form-control"></div>
<div class="col-md-4" style="padding-top:24px"><button class="btn btn-danger" type="submit"><i class="fa fa-trash"></i> <?= _l('sc_idle_delete_range'); ?></button></div>
</div>
<?= form_close(); ?>
<?= form_open(admin_url('utilities/delete_staff_idle_logs'), ['class'=>'tw-mt-3','onsubmit'=>"return confirm('".e(_l('sc_idle_clear_all_confirm'))."');"]); ?>
<input type="hidden" name="clear_all" value="1">
<button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-trash-can"></i> <?= _l('sc_idle_clear_all'); ?></button>
<?= form_close(); ?>
</div></div>
<?php } ?>
</div></div></div></div></div></div>
<?php init_tail(); ?>

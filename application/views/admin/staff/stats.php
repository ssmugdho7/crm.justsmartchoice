<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="staff_logged_time">
    <div class="tw-mb-3 tw-flex tw-flex-col">
        <h4 class="tw-font-bold tw-mt-0 tw-mb-1">
            <?= _l('timesheet_summary'); ?> — Task timers
        </h4>
        <?php if (strpos(current_url(), admin_url('staff/timesheets')) === false) { ?>
        <a href="<?= admin_url('staff/timesheets'); ?>"
            class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
            <?= _l('timesheet_detailed_overview'); ?>
            &rarr;
        </a>
        <?php } else { ?>
        <p class="tw-text-neutral-500 tw-mb-0">
            <?= _l('timesheet_detailed_overview'); ?>
        </p>
        <?php } ?>
    </div>
    <dl class="tw-grid tw-grid-cols-2 md:tw-grid-cols-2 lg:tw-grid-cols-5 tw-gap-2">
        <div
            class="tw-bg-white tw-border tw-border-solid tw-border-neutral-300/80 tw-shadow-sm tw-py-2.5 tw-px-3.5 tw-rounded-lg tw-text-sm tw-text-neutral-600">
            <div class="tw-font-normal tw-text-base tw-truncate"
                title="<?= _l('staff_stats_total_logged_time'); ?>">
                <?= _l('staff_stats_total_logged_time'); ?>
            </div>
            <div class="tw-text-neutral-500 tw-font-semibold">
                <?= e(seconds_to_time_format($logged_time['total'])); ?>
            </div>
        </div>

        <div
            class="tw-bg-white tw-border tw-border-solid tw-border-neutral-300/80 tw-shadow-sm tw-py-2.5 tw-px-3.5 tw-rounded-lg tw-text-sm tw-text-neutral-600">
            <div class="tw-font-normal tw-text-base tw-truncate"
                title="<?= _l('staff_stats_last_month_total_logged_time'); ?>">
                <?= _l('staff_stats_last_month_total_logged_time'); ?>
            </div>
            <div class="tw-text-neutral-500 tw-font-semibold">
                <?= e(seconds_to_time_format($logged_time['last_month'])); ?>
            </div>
        </div>

        <div
            class="tw-bg-white tw-border tw-border-solid tw-border-neutral-300/80 tw-shadow-sm tw-py-2.5 tw-px-3.5 tw-rounded-lg tw-text-sm tw-text-neutral-600">
            <div class="tw-font-normal tw-text-base tw-text-primary-600 tw-truncate"
                title="<?= _l('staff_stats_this_month_total_logged_time'); ?>">
                <?= _l('staff_stats_this_month_total_logged_time'); ?>
            </div>
            <div class="tw-text-neutral-500 tw-font-semibold">
                <?= e(seconds_to_time_format($logged_time['this_month'])); ?>
            </div>
        </div>

        <div
            class="tw-bg-white tw-border tw-border-solid tw-border-neutral-300/80 tw-shadow-sm tw-py-2.5 tw-px-3.5 tw-rounded-lg tw-text-sm tw-text-neutral-600">
            <div class="tw-font-normal tw-text-base tw-truncate"
                title="<?= _l('staff_stats_last_week_total_logged_time'); ?>">
                <?= _l('staff_stats_last_week_total_logged_time'); ?>
            </div>
            <div class="tw-text-neutral-500 tw-font-semibold">
                <?= e(seconds_to_time_format($logged_time['last_week'])); ?>
            </div>
        </div>

        <div
            class="tw-bg-white tw-border tw-border-solid tw-border-neutral-300/80 tw-shadow-sm tw-py-2.5 tw-px-3.5 tw-rounded-lg tw-text-sm tw-text-neutral-600 tw-col-span-2 md:tw-col-auto">
            <div class="tw-font-normal tw-text-base tw-truncate"
                title="<?= _l('staff_stats_this_week_total_logged_time'); ?>">
                <?= _l('staff_stats_this_week_total_logged_time'); ?>
            </div>
            <div class="tw-text-neutral-500 tw-font-semibold">
                <?= e(seconds_to_time_format($logged_time['this_week'])); ?>
            </div>
        </div>
    </dl>
</div>
<div class="staff_active_time tw-mt-4">
 <h4 class="tw-font-bold">CRM active time</h4>
 <p class="text-muted">Recorded separately from task timers while a CRM tab is visible and you have interacted within the last 60 seconds. Tracking starts when this feature is enabled; earlier login time cannot be reconstructed.</p>
 <dl class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-5 tw-gap-2">
 <?php foreach(['total'=>'staff_stats_total_logged_time','last_month'=>'staff_stats_last_month_total_logged_time','this_month'=>'staff_stats_this_month_total_logged_time','last_week'=>'staff_stats_last_week_total_logged_time','this_week'=>'staff_stats_this_week_total_logged_time'] as $key=>$label){ ?>
 <div class="tw-bg-white tw-border tw-border-solid tw-border-neutral-300/80 tw-py-2.5 tw-px-3.5 tw-rounded-lg"><dt><?= _l($label); ?></dt><dd><?= e(seconds_to_time_format($logged_time['crm_active'][$key]??0)); ?></dd></div>
 <?php } ?>
 </dl>
</div>

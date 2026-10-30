<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (count($expenses_years) > 1 || isset($currencies)) { ?>
<div
    class="tw-inline-flex tw-w-full tw-gap-8 ltr:tw-pr-2.5 tw-justify-items-end tw-items-end [&_.caret]:!tw-top-[9px] [&_.btn]:tw-py-0 [&_.btn]:tw-mr-0 [&_.btn]:tw-h-[24px] [&_.btn]:tw-font-medium [&_select]:tw-left-auto rtl:[&_.filter-option]:!tw-p-[inherit] rtl:[&_.filter-option]:!tw-text-left [&_.dropdown-menu]:tw-mt-1">
    <?php if (isset($currencies)) { ?>
    <div class="simple-bootstrap-select">
        <select data-width="fit" data-dropdown-align-right="true" class="selectpicker tw-w-full tw-min-w-[79px]"
            name="expenses_total_currency" onchange="init_expenses_total();">
            <?php foreach ($currencies as $currency) {
                $selected = isset($_currency) && $_currency == $currency['id'] ? 'selected' : ''; ?>
            <option
                value="<?= e($currency['id']); ?>"
                <?= e($selected); ?>
                data-subtext="<?= e($currency['name']); ?>"><?= e($currency['symbol']); ?>
            </option>
            <?php
            } ?>
        </select>
    </div>
    <?php } ?>
    <?php if (count($expenses_years) > 1) { ?>
    <div class="simple-bootstrap-select">
        <select
            data-none-selected-text="<?= date('Y'); ?>"
            data-width="fit" data-dropdown-align-right="true" class="selectpicker tw-w-full tw-min-w-[79px]" multiple
            name="expenses_total_years" onchange="init_expenses_total();">
            <?php foreach ($expenses_years as $year) { ?>
            <option
                value="<?= e($year['year']); ?>"
                <?php if (in_array((string) $year['year'], $scct_expense_years, true)) {
                    echo ' selected';
                } ?>>
                <?= e($year['year']); ?>
            </option>
            <?php } ?>
        </select>
    </div>
    <?php } ?>
</div>
<?php } ?>

<?php $labels = scct_expenses_status_labels(); ?>
<div class="exp-kpi-grid" aria-label="<?= e(_l('scct_expenses_summary')); ?>">
<?php foreach ($labels as $key => $label) { $metric = $scct_expense_summary['metrics'][$key]; ?>
    <button type="button" class="exp-kpi-card status-<?= e(str_replace('_', '-', $key)); ?>" data-scct-expense-status="<?= e($key); ?>">
        <span class="exp-kpi-label"><?= e($label); ?></span>
        <span class="exp-kpi-count"><?= e(app_format_money($metric['amount'], $scct_expense_summary['currency'])); ?></span>
        <span class="exp-kpi-subtext"><?= e(_l('scct_expenses_count', $metric['count'])); ?></span>
    </button>
<?php } ?>
</div>
<div class="exp-ribbon" role="region" aria-label="<?= e(_l('scct_expenses_distribution')); ?>">
    <div><span class="exp-ribbon-stat-title"><?= _l('scct_expenses_total_outlay'); ?></span><strong class="exp-ribbon-stat-value"><?= e(app_format_money($scct_expense_summary['metrics']['all']['amount'], $scct_expense_summary['currency'])); ?></strong></div>
    <div><span class="exp-ribbon-stat-title"><?= _l('scct_expenses_billable_ratio'); ?></span><strong class="exp-ribbon-stat-value"><?= e($scct_expense_summary['billable_ratio']); ?>%</strong></div>
    <p><?= _l('scct_expenses_summary_scope'); ?></p>
</div>
<script>
    init_selectpicker();
</script>
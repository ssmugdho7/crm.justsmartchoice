<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="prj-ribbon" aria-label="<?= e(_l('scpr_portfolio')); ?>">
    <div class="scpr-portfolio-primary">
        <span class="prj-ribbon-stat-title"><?= e(_l('scpr_portfolio')); ?></span>
        <strong class="prj-ribbon-stat-value"><?= e(sprintf(_l('scpr_active_count'), $scpr_portfolio['active'])); ?></strong>
        <?php if (staff_can('create', 'projects')) { ?>
        <div class="scpr-portfolio-values">
            <span><?= e(_l('scpr_fixed_value')); ?></span>
            <?php foreach ($scpr_portfolio['values'] as $value) { ?>
            <strong><?= e(app_format_money($value['amount'], get_currency($value['currency_id']))); ?></strong>
            <?php } ?>
            <?php if (!$scpr_portfolio['values']) { ?><span><?= e(_l('scpr_no_fixed')); ?></span><?php } ?>
        </div>
        <?php } ?>
    </div>
    <div><span class="prj-ribbon-stat-title"><?= e(_l('scpr_average')); ?></span><strong class="prj-ribbon-stat-value"><?= e($scpr_portfolio['average']); ?>%</strong></div>
    <div><span class="prj-ribbon-stat-title"><?= e(_l('scpr_crew_hours')); ?></span><strong class="prj-ribbon-stat-value"><?= e(seconds_to_time_format($scpr_portfolio['seconds'])); ?></strong></div>
</section>

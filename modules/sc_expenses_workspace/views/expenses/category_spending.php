<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="panel_s scct-category-spending" aria-labelledby="scct-category-title">
    <div class="panel-body">
        <h4 id="scct-category-title"><?= e(_l('scct_expenses_category_outlay', date('Y'))); ?></h4>
        <p class="text-muted"><?= e(_l('scct_expenses_category_scope')); ?></p>
        <?php if (!$scct_category_spending) { ?>
        <p><?= e(_l('scct_expenses_no_outlay')); ?></p>
        <?php } else { ?>
        <div class="table-responsive"><table class="table table-bordered">
            <thead><tr><th><?= e(_l('expense_category')); ?></th><th><?= e(_l('scct_expenses_entries')); ?></th><th><?= e(_l('scct_expenses_total_outlay')); ?></th></tr></thead>
            <tbody><?php foreach ($scct_category_spending as $category) { ?>
            <tr><td><?= e($category['name']); ?></td><td><?= e($category['expense_count']); ?></td><td><?= e(app_format_money($category['total'], get_currency($category['currency']))); ?></td></tr>
            <?php } ?></tbody>
        </table></div>
        <?php } ?>
    </div>
</section>

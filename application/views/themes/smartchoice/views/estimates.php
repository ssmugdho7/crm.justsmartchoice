<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700 section-heading section-heading-estimates">
    <?= _l('clients_my_estimates'); ?>
</h4>
<?php if (isset($estimates) && count($estimates) === 0) {
    $portalEmptyTitle = 'No estimates yet';
    $portalEmptyText = 'Estimates shared with you will appear here for review.';
    get_template_part('portal_empty_state', compact('portalEmptyTitle', 'portalEmptyText'));
} ?>
<div class="panel_s">
    <div class="panel-body">
        <?php get_template_part('estimates_stats'); ?>
        <hr />
        <?php get_template_part('estimates_table'); ?>
    </div>
</div>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700 section-heading section-heading-proposals">
    <?= _l('proposals'); ?>
</h4>
<?php if (isset($proposals) && count($proposals) === 0) {
    $portalEmptyTitle = 'No proposals yet';
    $portalEmptyText = 'Your proposals will appear here when they are ready to review.';
    get_template_part('portal_empty_state', compact('portalEmptyTitle', 'portalEmptyText'));
} ?>
<div class="panel_s">
    <div class="panel-body">
        <?php get_template_part('proposals_table'); ?>
    </div>
</div>
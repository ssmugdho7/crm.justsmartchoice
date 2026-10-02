<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<section class="sc-support-page sc-account-page" aria-labelledby="sc-support-title">
<header class="sc-account-heading sc-support-heading"><div><span class="sc-account-eyebrow">WE'RE HERE TO HELP</span><h1 id="sc-support-title"><?= _l('clients_tickets_heading'); ?></h1><p>Follow your requests, check replies, and contact the team when you need help.</p></div><a href="<?= site_url('clients/open_ticket'); ?>" class="btn btn-primary new-ticket"><i class="fa-regular fa-plus" aria-hidden="true"></i> <?= _l('clients_ticket_open_subject'); ?></a></header>
<h2 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700 tickets-summary-heading">
    <?= _l('tickets_summary'); ?>
</h2>

<dl class="sc-support-statuses tw-grid tw-grid-cols-1 md:tw-grid-cols-5 tw-gap-2 sm:tw-gap-4 tw-mt-2 tw-mb-10">
    <?php foreach (get_clients_area_tickets_summary($ticket_statuses) as $status) { ?>
    <a href="<?= e($status['url']); ?>"
        class="tw-border tw-border-solid tw-border-neutral-200 tw-rounded-md hover:tw-bg-neutral-100 <?= in_array($status['ticketstatusid'], $list_statuses) ? 'tw-bg-white' : 'tw-bg-neutral-50 '; ?>">
        <div class="tw-px-4 tw-py-5 sm:tw-px-4 sm:tw-py-2">
            <dt class="tw-font-medium"
                style="color:<?= e($status['statuscolor']); ?>">
                <?= e($status['translated_name']); ?>
            </dt>
            <dd class="tw-mt-1 tw-flex tw-items-baseline tw-justify-between md:tw-block lg:tw-flex">
                <div class="tw-flex tw-items-baseline tw-text-base tw-font-semibold tw-text-primary-600">
                    <?= e($status['total_tickets']) ?>
                </div>
            </dd>
        </div>
    </a>
    <?php } ?>
</dl>

<h2 class="sc-account-subheading">Your requests</h2>
<?php if (isset($tickets) && count($tickets) === 0) {
    $portalEmptyTitle = 'No support requests in this view';
    $portalEmptyText = 'Try another status filter, or open a support request if you need help.';
    get_template_part('portal_empty_state', compact('portalEmptyTitle', 'portalEmptyText'));
} ?>
<?php if (!isset($tickets) || count($tickets) > 0) { ?>
<div class="panel_s sc-support-table">
    <div class="panel-body">
        <?php get_template_part('tickets_table'); ?>
    </div>
</div>
<?php } ?>
</section>

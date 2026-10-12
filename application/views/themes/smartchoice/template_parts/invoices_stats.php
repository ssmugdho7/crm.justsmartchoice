<?php defined('BASEPATH') or exit('No direct script access allowed');

$invoiceStatusCounts = $invoiceStatusCounts ?? sc_customer_status_counts('invoices');
$total_invoices = array_sum($invoiceStatusCounts) - ($invoiceStatusCounts[5] ?? 0);
if (get_option('exclude_invoice_from_client_area_with_draft_status') == 1) {
    $total_invoices -= $invoiceStatusCounts[6] ?? 0;
}
$total_open                = $invoiceStatusCounts[1] ?? 0;
$total_paid                = $invoiceStatusCounts[2] ?? 0;
$total_not_paid_completely = $invoiceStatusCounts[3] ?? 0;
$total_overdue             = $invoiceStatusCounts[4] ?? 0;

$percent_open                = ($total_invoices > 0 ? number_format(($total_open * 100) / $total_invoices, 2) : 0);
$percent_paid                = ($total_invoices > 0 ? number_format(($total_paid * 100) / $total_invoices, 2) : 0);
$percent_overdue             = ($total_invoices > 0 ? number_format(($total_overdue * 100) / $total_invoices, 2) : 0);
$percent_not_paid_completely = ($total_invoices > 0 ? number_format(($total_not_paid_completely * 100) / $total_invoices, 2) : 0);

?>
<div class="row text-left invoice-quick-info invoices-stats">
    <div class="col-md-3 invoices-stats-unpaid">
        <div class="row">
            <div class="col-md-8 stats-status">
                <a href="<?= site_url('clients/invoices/1'); ?>"
                    class="tw-text-neutral-600 hover:tw-text-neutral-800 active:tw-text-neutral-800 tw-font-medium">
                    <?= _l('invoice_status_unpaid'); ?>
                </a>
            </div>
            <div class="col-md-4 text-right tw-font-medium stats-numbers">
                <?= e($total_open); ?> /
                <?= e($total_invoices); ?>
            </div>
            <div class="col-md-12 tw-mt-1.5">
                <div class="progress">
                    <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="40"
                        aria-valuemin="0" aria-valuemax="100" style="width: 0%"
                        data-percent="<?= e($percent_open); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 invoices-stats-paid">
        <div class="row">
            <div class="col-md-8 stats-status">
                <a href="<?= site_url('clients/invoices/2'); ?>"
                    class="tw-text-neutral-600 hover:tw-text-neutral-800 active:tw-text-neutral-800 tw-font-medium">
                    <?= _l('invoice_status_paid'); ?>
                </a>
            </div>
            <div class="col-md-4 text-right stats-numbers bold">
                <?= e($total_paid); ?> /
                <?= e($total_invoices); ?>
            </div>
            <div class="col-md-12 tw-mt-1.5">
                <div class="progress">
                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40"
                        aria-valuemin="0" aria-valuemax="100" style="width: 0%"
                        data-percent="<?= e($percent_paid); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 invoices-stats-overdue">
        <div class="row">
            <div class="col-md-8 stats-status">
                <a href="<?= site_url('clients/invoices/4'); ?>"
                    class="tw-text-neutral-600 hover:tw-text-neutral-800 active:tw-text-neutral-800 tw-font-medium">
                    <?= _l('invoice_status_overdue'); ?>
                </a>
            </div>
            <div class="col-md-4 text-right stats-numbers bold">
                <?= e($total_overdue); ?> /
                <?= e($total_invoices); ?>
            </div>
            <div class="col-md-12 tw-mt-1.5">
                <div class="progress">
                    <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="40"
                        aria-valuemin="0" aria-valuemax="100" style="width: 0%"
                        data-percent="<?= e($percent_overdue); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 invoices-stats-partially-paid">
        <div class="row">
            <div class="col-md-8 stats-status">
                <a href="<?= site_url('clients/invoices/3'); ?>"
                    class="tw-text-neutral-600 hover:tw-text-neutral-800 active:tw-text-neutral-800 tw-font-medium">
                    <?= _l('invoice_status_not_paid_completely'); ?>
                </a>
            </div>
            <div class="col-md-4 text-right stats-numbers bold">
                <?= e($total_not_paid_completely); ?> /
                <?= e($total_invoices); ?>
            </div>
            <div class="col-md-12 tw-mt-1.5">
                <div class="progress">
                    <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="40"
                        aria-valuemin="0" aria-valuemax="100" style="width: 0%"
                        data-percent="<?= e($percent_not_paid_completely); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
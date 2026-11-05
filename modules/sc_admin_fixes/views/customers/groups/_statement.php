<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="col-md-5">
    <div class="text-right">
        <h4 class="tw-my-0 tw-font-semibold"><?php echo _l('account_summary'); ?></h4>
        <p class="text-muted"><?php echo e(_l('statement_from_to', [$from, $to])); ?></p>
        <hr />
        <table class="table statement-account-summary native-statement-summary">
            <tbody>
                <tr>
                    <td class="text-left"><?php echo _l('statement_beginning_balance'); ?>:</td>
                    <td><?php echo e(app_format_money($statement['beginning_balance'], $statement['currency'])); ?></td>
                </tr>
                <tr>
                    <td class="text-left"><?php echo _l('invoiced_amount'); ?>:</td>
                    <td><?php echo e(app_format_money($statement['invoiced_amount'], $statement['currency'])); ?></td>
                </tr>
                <tr>
                    <td class="text-left"><?php echo _l('amount_paid'); ?>:</td>
                    <td><?php echo e(app_format_money($statement['amount_paid'], $statement['currency'])); ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-left"><b><?php echo _l('balance_due'); ?></b>:</td>
                    <td><?php echo e(app_format_money($statement['balance_due'], $statement['currency'])); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php if (!empty($aging)) { ?>
<div class="col-md-12 native-aging-panel">
    <h4><?= e(_l('customer_aging_title')); ?></h4>
    <p class="text-muted"><?= e(_l('customer_aging_as_of', _d($aging['as_of']))); ?></p>
    <?php $agingTotal = array_sum(array_map('floatval', $aging['buckets'])); ?>
    <div class="native-aging-bar" role="img" aria-label="<?= e(_l('customer_aging_title')); ?>">
    <?php if ($agingTotal > 0) { foreach ($aging['buckets'] as $key => $amount) { if ((float) $amount <= 0) { continue; } ?>
        <span class="native-aging-<?= e($key); ?>" style="width:<?= e(number_format((float) $amount * 100 / $agingTotal, 4, '.', '')); ?>%" title="<?= e(_l('customer_aging_' . $key) . ': ' . app_format_money($amount, $statement['currency'])); ?>"></span>
    <?php } } ?>
    </div>
    <div class="native-aging-grid">
    <?php foreach ($aging['buckets'] as $key => $amount) { ?>
        <div class="native-aging-card"><span><?= e(_l('customer_aging_' . $key)); ?></span><strong><?= e(app_format_money($amount, $statement['currency'])); ?></strong></div>
    <?php } ?>
    </div>
</div>
<?php } ?>
<div class="col-md-12">
    <div class="text-center bold padding-10">
        <?php echo _l('customer_statement_info', [$from, $to]); ?>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th><b><?php echo _l('statement_heading_date'); ?></b></th>
                    <th><b><?php echo _l('statement_heading_details'); ?></b></th>
                    <th class="text-right"><b><?php echo _l('statement_heading_amount'); ?></b></th>
                    <th class="text-right"><b><?php echo _l('statement_heading_payments'); ?></b></b></th>
                    <th class="text-right"><b><?php echo _l('statement_heading_balance'); ?></b></b></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo e($from); ?></td>
                    <td><?php echo _l('statement_beginning_balance'); ?></td>
                    <td class="text-right">
                        <?php echo e(app_format_money($statement['beginning_balance'], $statement['currency'], true)); ?>
                    </td>
                    <td></td>
                    <td class="text-right">
                        <?php echo e(app_format_money($statement['beginning_balance'], $statement['currency'], true)); ?>
                    </td>
                </tr>
                <?php
       $tmpBeginningBalance = $statement['beginning_balance'];
       foreach ($statement['result'] as $data) { ?>
                <tr class="<?= isset($data['payment_id']) ? 'native-ledger-payment' : ((isset($data['credit_note_id']) || isset($data['credit_id'])) ? 'native-ledger-credit' : 'native-ledger-charge'); ?>">
                    <td><?php echo e(_d($data['date'])); ?></td>
                    <td>
                        <?php
            if (isset($data['invoice_id'])) {
                echo _l('statement_invoice_details', ['<a href="' . admin_url('invoices/list_invoices/' . $data['invoice_id']) . '" target="_blank">' . e(format_invoice_number($data['invoice_id'])) . '</a>', e(_d($data['duedate']))]);
            } elseif (isset($data['payment_id'])) {
                echo _l('statement_payment_details', ['<a href="' . admin_url('payments/payment/' . $data['payment_id']) . '" target="_blank">' . '#' . $data['payment_id'] . '</a>', e(format_invoice_number($data['payment_invoice_id']))]);
            } elseif (isset($data['credit_note_id'])) {
                echo _l('statement_credit_note_details', ['<a href="' . admin_url('credit_notes/list_credit_notes/' . $data['credit_note_id']) . '" target="_blank">' . e(format_credit_note_number($data['credit_note_id'])) . '</a>']);
            } elseif (isset($data['credit_id'])) {
                echo _l(
                    'statement_credits_applied_details',
                    [
              '<a href="' . admin_url('credit_notes/list_credit_notes/' . $data['credit_applied_credit_note_id']) . '" target="_blank">' . e(format_credit_note_number($data['credit_applied_credit_note_id'])) . '</a>',
              e(app_format_money($data['credit_amount'], $statement['currency'], true)),
              e(format_invoice_number($data['credit_invoice_id'])),
            ]
                );
            } elseif (isset($data['credit_note_refund_id'])) {
                echo e(_l('statement_credit_note_refund', format_credit_note_number($data['refund_credit_note_id'])));
            }
          ?>
                    </td>
                    <td class="text-right">
                        <?php
          if (isset($data['invoice_id'])) {
              echo e(app_format_money($data['invoice_amount'], $statement['currency'], true));
          } elseif (isset($data['credit_note_id'])) {
              echo e(app_format_money($data['credit_note_amount'], $statement['currency'], true));
          }
          ?>
                    </td>
                    <td class="text-right">
                        <?php
          if (isset($data['payment_id'])) {
              echo e(app_format_money($data['payment_total'], $statement['currency'], true));
          } elseif (isset($data['credit_note_refund_id'])) {
              echo e(app_format_money($data['refund_amount'], $statement['currency'], true));
          }
          ?>
                    </td>
                    <td class="text-right">
                        <?php
          if (isset($data['invoice_id'])) {
              $tmpBeginningBalance = ($tmpBeginningBalance + $data['invoice_amount']);
          } elseif (isset($data['payment_id'])) {
              $tmpBeginningBalance = ($tmpBeginningBalance - $data['payment_total']);
          } elseif (isset($data['credit_note_id'])) {
              $tmpBeginningBalance = ($tmpBeginningBalance - $data['credit_note_amount']);
          } elseif (isset($data['credit_note_refund_id'])) {
              $tmpBeginningBalance = ($tmpBeginningBalance + $data['refund_amount']);
          }
          if (!isset($data['credit_id'])) {
              echo e(app_format_money($tmpBeginningBalance, $statement['currency'], true));
          }
          ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
            <tfoot class="statement_tfoot">
                <tr>
                    <td colspan="3" class="text-right">
                        <b><?php echo _l('balance_due'); ?></b>
                    </td>
                    <td class="text-right" colspan="2">
                        <b><?php echo e(app_format_money($statement['balance_due'], $statement['currency'])); ?></b>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<style>
.native-statement-summary{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;border:0!important}
.native-statement-summary tbody,.native-statement-summary tfoot{display:contents}
.native-statement-summary tr{display:flex;flex-direction:column;padding:14px;border:1px solid #e2e8f0;border-radius:10px;background:#fff}
.native-statement-summary td{display:block;text-align:left!important;border:0!important;padding:3px!important}
.native-statement-summary td:last-child{font-size:20px;font-weight:700;color:#064e3b;font-variant-numeric:tabular-nums;overflow-wrap:anywhere}
.native-ledger-payment td{background:#ecfdf5!important;color:#047857}.native-ledger-credit td{background:#eff6ff!important;color:#1d4ed8}
.native-ledger-charge td{color:#334155}.native-ledger-payment td:last-child,.native-ledger-credit td:last-child,.native-ledger-charge td:last-child{font-family:ui-monospace,monospace;font-weight:600}
@media print{.native-statement-summary{display:table}.native-statement-summary tbody{display:table-row-group}.native-statement-summary tfoot{display:table-footer-group}.native-statement-summary tr{display:table-row}.native-statement-summary td{display:table-cell}}
</style>

<style>
.native-aging-panel{margin:16px 0}.native-aging-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,170px),1fr));gap:10px}.native-aging-card{border:1px solid #e2e8f0;border-top:3px solid #059669;border-radius:8px;padding:14px;background:#fff}.native-aging-card span,.native-aging-card strong{display:block}.native-aging-card strong{font-size:20px;margin-top:8px;font-variant-numeric:tabular-nums}.native-aging-card:nth-child(3){border-top-color:#d97706}.native-aging-card:nth-child(4){border-top-color:#dc2626}
</style>

<style>.native-aging-bar{display:flex;height:10px;overflow:hidden;background:#e2e8f0;border-radius:6px;margin:12px 0}.native-aging-current{background:#059669}.native-aging-1_30{background:#eab308}.native-aging-31_60{background:#d97706}.native-aging-61_plus{background:#dc2626}</style>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$scPaypalCheckoutAvailable = sc_active_online_gateway_supports_currency('paypal_checkout', $invoice->currency_name)
    && $invoice->status != Invoices_model::STATUS_PAID
    && $invoice->status != Invoices_model::STATUS_CANCELLED
    && (float) $invoice->total_left_to_pay > 0;
$scStripeCheckoutAvailable = sc_active_online_gateway_supports_currency('stripe', $invoice->currency_name)
    && $invoice->status != Invoices_model::STATUS_PAID
    && $invoice->status != Invoices_model::STATUS_CANCELLED
    && (float) $invoice->total_left_to_pay > 0;
?>
<?php $scCustomerAttachments = sc_sales_customer_attachments('invoice', (int) $invoice->id); ?>

<div class="mtop15 preview-top-wrapper">
    <div class="row">
        <div class="col-md-3">
            <div class="mbot30">
                <div class="invoice-html-logo">
                    <?= get_dark_company_logo(); ?>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="top" data-sticky data-sticky-class="preview-sticky-header">
        <div class="container preview-sticky-container">
            <div class="sm:tw-flex tw-justify-between -tw-mx-4">
                <div class="sm:tw-self-end">
                    <h3 class="bold tw-my-0 invoice-html-number">
                        <span class="sticky-visible hide tw-mb-2">
                            <?= html_escape(format_invoice_number($invoice->id)); ?>
                        </span>
                    </h3>
                    <span class="invoice-html-status">
                        <?= format_invoice_status($invoice->status, '', true); ?>
                    </span>
                </div>
                <div class="tw-flex tw-items-end tw-space-x-2 tw-mt-3 sm:tw-mt-0 sc-customer-sales-actions">
                    <?php if (is_client_logged_in() && has_contact_permission('invoices')) { ?>
                    <a href="<?= site_url('clients/invoices/'); ?>"
                        class="btn btn-default action-button go-to-portal">
                        <?= _l('client_go_to_dashboard'); ?>
                    </a>
                    <?php } ?>
                    <a href="#document-attachments" class="btn btn-default action-button smart-choice-attachments-button"><i class="fa fa-paperclip"></i> <?= _l('attachments'); ?></a>
                    <?= form_open($this->uri->uri_string(), ['class'=>'tw-inline-block']); ?>
                    <button type="submit" name="invoicepdf" value="invoicepdf" class="btn btn-default action-button">
                        <i class='fa-regular fa-file-pdf'></i>
                        <?= _l('clients_invoice_html_btn_download'); ?>
                    </button>
                    <?= form_close(); ?>

                    <a href="<?= site_url('appointly/appointments'); ?>" class="btn btn-primary action-button sc-service-appointment-button"><i class="fa fa-calendar-check"></i> <?= _l('sc_make_service_appointment'); ?></a>
                    <?php if (($scPaypalCheckoutAvailable || $scStripeCheckoutAvailable || found_invoice_mode($payment_modes, $invoice->id, false))) { ?>
                    <a href="#online_payment_form" class="btn btn-success action-button invoice-html-pay-now-top pay-now-top sticky-hidden">
                        <i class="fa fa-credit-card"></i> <?= _l('invoice_html_online_payment_button_text'); ?>
                    </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div>

<div class="row sc-sales-document-layout">
<div class="col-md-8 sc-sales-document-main">
<div class="panel_s tw-mt-6">
    <div class="panel-body">
        <?php if (is_invoice_overdue($invoice)) { ?>
        <div class="col-md-10 col-md-offset-1 tw-mb-5">
            <div class="alert alert-danger text-center">
                <p class="tw-font-medium">
                    <?= html_escape(_l('overdue_by_days', get_total_days_overdue($invoice->duedate))); ?>
                </p>
            </div>
        </div>
        <?php } ?>
        <div class="col-md-10 col-md-offset-1">
            <div class="row mtop20">
                <div class="col-md-6 col-sm-6 transaction-html-info-col-left">
                    <h4 class="tw-font-semibold tw-text-neutral-700 invoice-html-number">
                        <?= html_escape(format_invoice_number($invoice->id)); ?>
                    </h4>
                    <address class="invoice-html-company-info tw-text-neutral-500 tw-text-normal">
                        <?= format_organization_info(); ?>
                    </address>
                    <?php hooks()->do_action('after_left_panel_invoicehtml', $invoice); ?>
                </div>
                <div class="col-sm-6 text-right transaction-html-info-col-right">
                    <span class="tw-font-medium tw-text-neutral-700 invoice-html-bill-to">
                        <?= _l('invoice_bill_to'); ?>
                    </span>
                    <address class="invoice-html-customer-billing-info tw-text-neutral-500 tw-text-normal">
                        <?= format_customer_info($invoice, 'invoice', 'billing'); ?>
                    </address>
                    <!-- shipping details -->
                    <?php if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) { ?>
                    <span class="tw-font-medium tw-text-neutral-700 invoice-html-ship-to">
                        <?= _l('ship_to'); ?>
                    </span>
                    <address class="invoice-html-customer-shipping-info tw-text-neutral-500 tw-text-normal">
                        <?= format_customer_info($invoice, 'invoice', 'shipping'); ?>
                    </address>
                    <?php } ?>
                    <p class="invoice-html-date tw-mb-0 tw-text-normal">
                        <span class="tw-font-medium tw-text-neutral-700">
                            <?= _l('invoice_data_date'); ?>
                        </span>
                        <?= html_escape(_d($invoice->date)); ?>
                    </p>
                    <?php if (! empty($invoice->duedate)) { ?>
                    <p class="invoice-html-duedate tw-mb-0 tw-text-normal">
                        <span class="tw-font-medium tw-text-neutral-700">
                            <?= _l('invoice_data_duedate'); ?>
                        </span>
                        <?= html_escape(_d($invoice->duedate)); ?>
                    </p>
                    <?php } ?>
                    <?php if ($invoice->sale_agent && get_option('show_sale_agent_on_invoices') == 1) { ?>
                    <p class="invoice-html-sale-agent tw-mb-0 tw-text-normal">
                        <span
                            class="tw-font-medium tw-text-neutral-700"><?= _l('sale_agent_string'); ?>:</span>
                        <?= html_escape(get_staff_full_name($invoice->sale_agent)); ?>
                    </p>
                    <?php } ?>
                    <?php if ($invoice->project_id && get_option('show_project_on_invoice') == 1) { ?>
                    <p class="invoice-html-project tw-mb-0 tw-text-normal">
                        <span
                            class="tw-font-medium tw-text-neutral-700"><?= _l('project'); ?>:</span>
                        <?= html_escape(get_project_name_by_id($invoice->project_id)); ?>
                    </p>
                    <?php } ?>
                    <?php $pdf_custom_fields = get_custom_fields('invoice', ['show_on_pdf' => 1, 'show_on_client_portal' => 1]);

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
    if ($value == '') {
        continue;
    } ?>
                    <p class="tw-mb-0 tw-text-normal">
                        <span
                            class="tw-font-medium tw-text-neutral-700"><?= html_escape(sc_smart_choice_custom_field_label($field['name'])); ?>:
                        </span>
                        <?= $value; ?>
                    </p>
                    <?php
} ?>
                    <?php hooks()->do_action('after_right_panel_invoicehtml', $invoice); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <?php
             $items = get_items_table_data($invoice, 'invoice');
echo $items->table();
?>
                    </div>
                </div>
                <div class="col-md-6 col-md-offset-6">
                    <table class="table text-right tw-text-normal">
                        <tbody>
                            <tr id="subtotal">
                                <td>
                                    <span
                                        class="bold tw-text-neutral-700"><?= _l('invoice_subtotal'); ?></span>
                                </td>
                                <td class="subtotal">
                                    <?= html_escape(app_format_money($invoice->subtotal, $invoice->currency_name)); ?>
                                </td>
                            </tr>
                            <?php if (is_sale_discount_applied($invoice)) { ?>
                            <tr>
                                <td>
                                    <span
                                        class="bold tw-text-neutral-700"><?= _l('invoice_discount'); ?>
                                        <?php if (is_sale_discount($invoice, 'percent')) { ?>
                                        (<?= html_escape(app_format_number($invoice->discount_percent, true)); ?>%)
                                        <?php } ?></span>
                                </td>
                                <td class="discount">
                                    <?= html_escape('-' . app_format_money($invoice->discount_total, $invoice->currency_name)); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php
        foreach ($items->taxes() as $tax) {
            echo '<tr class="tax-area"><td class="bold !tw-text-neutral-700">' . e($tax['taxname']) . ' (' . e(app_format_number($tax['taxrate'])) . '%)</td><td>' . e(app_format_money($tax['total_tax'], $invoice->currency_name)) . '</td></tr>';
        }
?>
                            <?php if ((int) $invoice->adjustment != 0) { ?>
                            <tr>
                                <td>
                                    <span class="bold tw-text-neutral-700">
                                        <?= _l('invoice_adjustment'); ?>
                                    </span>
                                </td>
                                <td class="adjustment">
                                    <?= html_escape(app_format_money($invoice->adjustment, $invoice->currency_name)); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td>
                                    <span
                                        class="bold tw-text-neutral-700"><?= _l('invoice_total'); ?></span>
                                </td>
                                <td class="total">
                                    <?= html_escape(app_format_money($invoice->total, $invoice->currency_name)); ?>
                                </td>
                            </tr>
                            <?php if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) { ?>
                            <tr>
                                <td>
                                    <span class="bold tw-text-neutral-700">
                                        <?= _l('invoice_total_paid'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?= html_escape('-' . app_format_money(sum_from_table(db_prefix() . 'invoicepaymentrecords', ['field' => 'amount', 'where' => ['invoiceid' => $invoice->id]]), $invoice->currency_name)); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) { ?>
                            <tr>
                                <td>
                                    <span
                                        class="bold tw-text-neutral-700"><?= _l('applied_credits'); ?></span>
                                </td>
                                <td>
                                    <?= html_escape('-' . app_format_money($credits_applied, $invoice->currency_name)); ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != Invoices_model::STATUS_CANCELLED) { ?>
                            <tr>
                                <td>
                                    <span
                                        class="<?= $invoice->total_left_to_pay > 0 ? 'text-danger ' : ''; ?> bold">
                                        <?= _l('invoice_amount_due'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="<?= $invoice->total_left_to_pay > 0 ? 'text-danger' : ''; ?>">
                                        <?= html_escape(app_format_money($invoice->total_left_to_pay, $invoice->currency_name)); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                
<?php
$scPaymentMeta = null;
if (isset($invoice->id) && $this->db->table_exists(db_prefix() . 'sc_sales_meta')) {
    $scPaymentMeta = $this->db
        ->where(['rel_type' => 'invoice', 'rel_id' => (int) $invoice->id])
        ->get(db_prefix() . 'sc_sales_meta')
        ->row();
}
if ($scPaymentMeta) {
    $scDiscountLabel = trim((string)($scPaymentMeta->discount_type ?? ''));
    if (trim((string)($scPaymentMeta->discount_reason ?? '')) !== '') { $scDiscountLabel .= ($scDiscountLabel !== '' ? ' — ' : '') . trim((string)$scPaymentMeta->discount_reason); }
?>
<div class="smart-choice-public-payment-summary">
    <h4><?= _l('sc_payment_schedule'); ?></h4>
    <div class="smart-choice-public-payment-grid">
        <div><span><?= _l('sc_contract_total'); ?></span><strong><?= html_escape(app_format_money($scPaymentMeta->contract_total, $invoice->currency_name)); ?></strong></div>
        <div><span><?= _l('sc_amount_due_now'); ?></span><strong><?= html_escape(app_format_money($scPaymentMeta->due_now, $invoice->currency_name)); ?></strong></div>
        <div><span><?= _l('sc_remaining_balance'); ?></span><strong><?= html_escape(app_format_money($scPaymentMeta->remaining_balance, $invoice->currency_name)); ?></strong></div>
        <?php if ($scDiscountLabel !== '') { ?>
        <div><span>Discount</span><strong><?= html_escape($scDiscountLabel); ?></strong></div>
        <?php } ?>
        <?php if (!empty($scPaymentMeta->payment_link)) { ?>
        <div class="smart-choice-payment-link"><a href="<?= html_escape($scPaymentMeta->payment_link); ?>" target="_blank" rel="noopener">Open Payment Link</a></div>
        <?php } ?>
    </div>
</div>
<?php } ?>

                <?php hooks()->do_action('after_total_summary_invoicehtml', $invoice); ?>
                <?php if (get_option('total_to_words_enabled') == 1) { ?>
                <div class="col-md-12 text-center invoice-html-total-to-words">
                    <p class="tw-font-medium">
                        <?= _l('num_word'); ?>:<span
                            class="tw-text-neutral-500">
                            <?= $this->numberword->convert($invoice->total, $invoice->currency_name); ?>
                        </span>
                    </p>
                </div>
                <?php } ?>
                <?php if (count($scCustomerAttachments) > 0) { ?>
                <div class="clearfix"></div>
                <div id="document-attachments" class="invoice-html-files">
                    <div class="col-md-12">
                        <hr />
                        <p><b><?= _l('invoice_files'); ?></b>
                        </p>
                    </div>
                    <?php foreach ($scCustomerAttachments as $attachment) {
                        // Do not show hidden attachments to customer
                        if ($attachment['visible_to_customer'] == 0) {
                            continue;
                        }
                        if (!sc_sales_attachment_available($attachment)) {
                            echo sc_sales_attachment_unavailable_html($attachment);
                            continue;
                        }
                        $attachment_url = sc_sales_attachment_download_url($attachment);
                            $attachment_preview_url = sc_sales_attachment_preview_url($attachment);
                        if (! empty($attachment['external'])) {
                            $attachment_url = $attachment['external_link'];
                        } ?>
                    <div class="col-md-12 mbot10">
                        <div class="pull-left">
                            <i
                                class="<?= get_mime_class($attachment['filetype']); ?>"></i>
                        </div>
                        <a class="smart-choice-attachment-open" target="_blank" rel="noopener" href="<?= html_escape($attachment_preview_url); ?>"><i class="fa fa-eye"></i> <?= html_escape($attachment['file_name']); ?></a>
                        <?php $scExt = strtolower(pathinfo($attachment['file_name'], PATHINFO_EXTENSION)); ?>
                        <?php if (in_array($scExt, ['jpg','jpeg','png','gif','webp'], true)) { ?>
                        <div class="smart-choice-attachment-preview"><a href="<?= html_escape($attachment_preview_url); ?>" target="_blank" rel="noopener"><img src="<?= html_escape($attachment_preview_url); ?>" alt="<?= html_escape($attachment['file_name']); ?>"></a></div>
                        <?php } elseif ($scExt === 'pdf') { ?>
                        <div class="smart-choice-attachment-preview smart-choice-pdf-preview"><iframe src="<?= html_escape($attachment_preview_url); ?>#toolbar=1&navpanes=0" title="<?= html_escape($attachment['file_name']); ?>" loading="lazy"></iframe></div>
                        <?php } ?>
                    </div>
                    <?php
                    } ?>
                </div>
                <?php } ?>
                <?php if (! empty($invoice->clientnote)) { ?>
                <div class="col-md-12 invoice-html-note">
                    <p>
                        <b><?= _l('invoice_note'); ?></b>
                    </p>
                    <div class="tw-text-neutral-500 tw-mt-2.5">
                        <?= process_text_content_for_display($invoice->clientnote); ?>
                    </div>
                </div>
                <?php } ?>
                <?php if (! empty($invoice->terms)) { ?>
                <div class="col-md-12 invoice-html-terms-and-conditions">
                    <hr />
                    <p>
                        <b>
                            <?= _l('terms_and_conditions'); ?>
                        </b>
                    </p>
                    <div class="tw-text-neutral-500 tw-mt-2.5">
                        <?= process_text_content_for_display($invoice->terms); ?>
                    </div>
                </div>
                <?php } ?>

                <?php if ((!empty($invoice->sc_discount_reason)) || (isset($invoice->sc_down_payment_percent) && (float) $invoice->sc_down_payment_percent < 100)) { ?>
                <div class="col-md-12 sc-invoice-footer-summary mtop20">
                  <div class="panel panel-default"><div class="panel-body">
                    <h4 class="tw-font-semibold tw-mt-0">Payment and Discount Summary</h4>
                    <?php if (!empty($invoice->sc_discount_reason)) { ?><p><strong><?= _l('sc_discount_reason'); ?>:</strong> <?= e($invoice->sc_discount_reason); ?></p><?php } ?>
                    <?php if (isset($invoice->sc_down_payment_percent) && (float) $invoice->sc_down_payment_percent < 100) { ?>
                    <p><strong><?= _l('sc_down_payment'); ?>:</strong> <?= e($invoice->sc_down_payment_percent); ?>%</p>
                    <p><strong>Down Payment Total to Pay:</strong> <?= app_format_money($invoice->total, $invoice->currency_name ?? ''); ?></p>
                    <?php if (isset($invoice->sc_remaining_balance)) { ?><p><strong>Remaining Contract Balance:</strong> <?= app_format_money($invoice->sc_remaining_balance, $invoice->currency_name ?? ''); ?></p><?php } ?>
                    <?php } ?>
                  </div></div>
                </div>
                <?php } ?>
                <div class="col-md-12">
                    <hr />
                </div>
                <div class="col-md-12 invoice-html-payments">
                    <p>
                        <b><?= _l('invoice_received_payments'); ?></b>
                    </p>
                    <?php
               $total_payments = count($invoice->payments);

if ($total_payments > 0) { ?>
                    <table class="table table-hover invoice-payments-table tw-mt-2.5">
                        <thead>
                            <tr>
                                <th><?= _l('invoice_payments_table_number_heading'); ?>
                                </th>
                                <th><?= _l('invoice_payments_table_mode_heading'); ?>
                                </th>
                                <th><?= _l('invoice_payments_table_date_heading'); ?>
                                </th>
                                <th><?= _l('invoice_payments_table_amount_heading'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoice->payments as $payment) { ?>
                            <tr>
                                <td>
                                    <span
                                        class="pull-left"><?= html_escape($payment['paymentid']); ?></span>
                                    <?= form_open($this->uri->uri_string()); ?>
                                    <button type="submit"
                                        value="<?= html_escape($payment['paymentid']); ?>"
                                        class="btn btn-icon btn-default pull-right" name="paymentpdf"><i
                                            class="fa-regular fa-file-pdf"></i></button>
                                    <?= form_close(); ?>
                                </td>
                                <td><?= html_escape($payment['name']); ?>
                                    <?php if (! empty($payment['paymentmethod'])) {
                                        echo ' - ' . $payment['paymentmethod'];
                                    } ?></td>
                                <td><?= html_escape(_d($payment['date'])); ?>
                                </td>
                                <td><?= html_escape(app_format_money($payment['amount'], $invoice->currency_name)); ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <hr />
                    <?php } else { ?>
                    <h5 class="tw-font-medium tw-mt-0 tw-text-neutral-500">
                        <?= _l('invoice_no_payments_found'); ?>
                    </h5>
                    <div class="clearfix"></div>
                    <hr />
                    <?php } ?>
                </div>
                <?php
            // No payments for paid and cancelled
            if (($invoice->status != Invoices_model::STATUS_PAID
                                    && $invoice->status != Invoices_model::STATUS_CANCELLED
                                    && $invoice->total > 0)) { ?>
                <div class="col-md-12">
                    <div class="row">
                        <?php
                                          $found_online_mode = false;
                if (($scPaypalCheckoutAvailable || $scStripeCheckoutAvailable || found_invoice_mode($payment_modes, $invoice->id, false))) {
                    $found_online_mode = true; ?>
                        <div class="col-md-6 text-left">
                            <p class="tw-mb-2.5 tw-font-medium">
                                <?= _l('invoice_html_online_payment'); ?>
                            </p>
                            <?php
                            // Prefer Stripe when it is enabled for this invoice. If Stripe is
                            // unavailable and there is only one online gateway, preselect that
                            // gateway so Pay Now never submits an empty paymentmode value.
                            $sc_online_mode_ids = [];
                            foreach ($payment_modes as $sc_mode) {
                                if (!is_numeric($sc_mode['id']) && !empty($sc_mode['id'])
                                    && is_payment_mode_allowed_for_invoice($sc_mode['id'], $invoice->id)) {
                                    $sc_online_mode_ids[] = (string) $sc_mode['id'];
                                }
                            }
                            $sc_preferred_online_mode = '';
                            foreach ($payment_modes as $sc_mode) {
                                if (!empty($sc_mode['default_selected']) && in_array((string)$sc_mode['id'], $sc_online_mode_ids, true)) {
                                    $sc_preferred_online_mode = (string)$sc_mode['id'];
                                    break;
                                }
                            }
                            if ($sc_preferred_online_mode === '' && count($sc_online_mode_ids) === 1) {
                                $sc_preferred_online_mode = $sc_online_mode_ids[0];
                            }
                            ?>
                            <?= form_open($this->uri->uri_string(), ['id' => 'online_payment_form', 'novalidate' => true]); ?>
                            <?php foreach ($payment_modes as $mode) {
                                if (! is_numeric($mode['id']) && ! empty($mode['id'])) {
                                    if (! is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
                                        continue;
                                    } ?>
                            <div class="radio radio-success online-payment-radio">
                                <input type="radio"
                                    value="<?= html_escape($mode['id']); ?>"
                                    id="pm_<?= html_escape($mode['id']); ?>"
                                    name="paymentmode"<?= $sc_preferred_online_mode === (string) $mode['id'] ? ' checked' : ''; ?>>
                                <label
                                    for="pm_<?= html_escape($mode['id']); ?>"><?= html_escape($mode['name']); ?></label>
                            </div>
                            <?php if (! empty($mode['description'])) { ?>
                            <div class="mbot15">
                                <?= process_text_content_for_display($mode['description']); ?>
                            </div>
                            <?php }
                            }
                            } ?>
                            <div class="form-group mtop25">
                                <?php if (get_option('allow_payment_amount_to_be_modified') == 1) { ?>
                                <label for="amount"
                                    class="control-label"><?= _l('invoice_html_amount'); ?></label>
                                <div class="input-group">
                                    <input type="number" required
                                        max="<?= html_escape($invoice->total_left_to_pay); ?>"
                                        data-total="<?= html_escape($invoice->total_left_to_pay); ?>"
                                        name="amount" class="form-control"
                                        value="<?= html_escape($invoice->total_left_to_pay); ?>">
                                    <span class="input-group-addon">
                                        <?= html_escape($invoice->symbol); ?>
                                    </span>
                                </div>
                                <?php } else {
                                    echo '<h4 class="bold mbot25">' . e(_l('invoice_html_total_pay', app_format_money($invoice->total_left_to_pay, $invoice->currency_name))) . '</h4>';
                                } ?>
                            </div>
                            <div id="pay_button">
                                <button id="pay_now" type="submit" name="make_payment" value="1" class="btn btn-success"
                                    data-original-text="<?= html_escape(_l('invoice_html_online_payment_button_text')); ?>">
                                    <i class="fa fa-credit-card"></i> <?= _l('invoice_html_online_payment_button_text'); ?>
                                </button>
                            </div>
                            <input type="hidden" name="hash"
                                value="<?= html_escape($hash); ?>">
                            <?= form_close(); ?>
                        </div>
                        <?php
                } ?>
                        <?php if (found_invoice_mode($payment_modes, $invoice->id)) { ?>
                        <div class="invoice-html-offline-payments <?php if ($found_online_mode == true) {
                            echo 'col-md-6 text-right';
                        } else {
                            echo 'col-md-12';
                        } ?>">
                            <p class="tw-mb-2.5 tw-font-medium">
                                <?= _l('invoice_html_offline_payment'); ?>
                            </p>
                            <?php foreach ($payment_modes as $mode) {
                                if (is_numeric($mode['id'])) {
                                    if (! is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
                                        continue;
                                    } ?>
                            <p class="bold">
                                <?= html_escape($mode['name']); ?>
                            </p>
                            <?php if (! empty($mode['description'])) { ?>
                            <div class="mbot15">
                                <?= process_text_content_for_display($mode['description']); ?>
                            </div>
                            <?php }
                            }
                            } ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
</div>
<div class="col-md-4 sc-sales-document-sidebar">
<?= sc_sales_public_sidebar('invoice', $invoice, $hash); ?>
</div>
</div>
<script>
    $(function() {
        new Sticky('[data-sticky]');
        var $payNowTop = $('.pay-now-top');
        var $form = $('#online_payment_form');
        if ($payNowTop.length && !$('#pay_now').isInViewport()) {
            $payNowTop.removeClass('hide');
        }
        $payNowTop.off('click.smartChoicePayNow').on('click.smartChoicePayNow', function(e){
            e.preventDefault();
            if ($form.length) { $('html,body').animate({scrollTop: Math.max(0, $form.offset().top - 90)}, 'slow'); }
        });
        $form.appFormValidator();
        var online_payments = $('.online-payment-radio');
        if (!$form.find('input[name="paymentmode"]:checked').length && online_payments.length === 1) {
            online_payments.find('input').prop('checked', true);
        }
    });
</script>
<?php if (isset($invoice->sc_discount_reason) && $invoice->sc_discount_reason !== '') { ?>
<div class="sc-invoice-discount-reason"><strong><?= _l('sc_discount_reason'); ?>:</strong> <?= e($invoice->sc_discount_reason); ?></div>
<?php } ?>
<?php if (isset($invoice->sc_down_payment_percent) && (float)$invoice->sc_down_payment_percent < 100) { ?>
<div class="sc-invoice-payment-stage"><strong><?= _l('sc_down_payment'); ?>:</strong> <?= e($invoice->sc_down_payment_percent); ?>% &nbsp; <strong><?= _l('sc_amount_due_now'); ?>:</strong> <?= app_format_money($invoice->total, $invoice->currency_name ?? ''); ?></div>
<?php } ?>

<script>
$(function(){
    $('.select-item').on('change', function(){
        const $el=$(this);
        $.post(window.location.href,{item_id:$el.closest('tr').data('item-id'),choosen:$el.prop('checked')?1:0,action:'invoice_item_selection_changed'})
          .done(function(){window.location.reload();});
    });
});
</script>
<script>document.addEventListener('click',function(e){var a=e.target.closest('.smart-choice-attachments-button');if(!a)return;var t=document.getElementById('document-attachments');if(t){e.preventDefault();t.scrollIntoView({behavior:'smooth',block:'start'});}});</script>

<style id="sc-portal-sales-402">
.proposal-html-items,.estimate-html-items,.invoice-html-items,.proposal-html-items table,.estimate-html-items table,.invoice-html-items table{border-radius:8px;overflow:hidden}
.proposal-html-items thead th,.estimate-html-items thead th,.invoice-html-items thead th{background:#3598DB!important;color:#fff!important;border-color:#2f86c3!important}
.proposal-html-items tbody tr:nth-child(even),.estimate-html-items tbody tr:nth-child(even),.invoice-html-items tbody tr:nth-child(even){background:#f5faff!important}
.proposal-html-items .optional-item,.estimate-html-items .optional-item,.invoice-html-items .optional-item{background:#fff7ed!important;border-left:4px solid #F28C28!important}
.optional-choose-item-checkbox,.optional-item-checkbox{appearance:auto!important;-webkit-appearance:checkbox!important;visibility:visible!important;opacity:1!important;width:18px!important;height:18px!important;accent-color:#169179!important;position:static!important;margin:2px 7px 2px 0!important}
label[for*="optional"],.optional-item label,.optional-item-checkbox-label{color:#0E6F5B!important;font-weight:700!important;background:#ecfdf5!important;border:1px solid #a7f3d0!important;border-radius:5px!important;padding:5px 8px!important;display:inline-flex!important;align-items:center!important}
.smart-choice-attachment-preview object{width:100%!important;min-height:520px!important;border:1px solid #dbeafe!important;border-radius:8px!important;background:#fff!important}
</style>

<style id="sc-sales-final-415">
.sc-sales-edit-attachment-card{display:block;border:1px solid #e5e7eb;border-radius:8px;padding:8px;background:#fff;min-height:120px;text-align:center;overflow:hidden}
.sc-sales-edit-attachment-card img{width:100%;height:110px;object-fit:cover;border-radius:6px}
.sc-sales-edit-attachment-icon{height:80px;display:flex;align-items:center;justify-content:center;font-size:38px;color:#3598DB}
.sc-sales-edit-attachment-name{font-size:12px;margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.preview-top-wrapper .action-button{margin-bottom:6px}.preview-top-wrapper .tw-flex{flex-wrap:wrap;gap:6px}
.sc-sales-document-sidebar{display:block!important}.sc-sales-summary-discussion{position:relative;z-index:1}
@media(max-width:991px){.sc-sales-document-main,.sc-sales-document-sidebar{width:100%!important;float:none!important}.sc-sales-document-sidebar{margin-top:15px}}
</style>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $scCustomerAttachments = sc_sales_customer_attachments('estimate', (int) $estimate->id); ?>
<div class="mtop15 preview-top-wrapper">
    <div class="row">
        <div class="col-md-3">
            <div class="mbot30">
                <div class="estimate-html-logo">
                    <?= get_dark_company_logo(); ?>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="top" data-sticky data-sticky-class="preview-sticky-header">
        <div class="container preview-sticky-container">
            <div class="sm:tw-flex sm:tw-justify-between -tw-mx-4">
                <div class="sm:tw-self-end">
                    <h3 class="bold tw-my-0 estimate-html-number">
                        <span class="sticky-visible hide tw-mb-2">
                            <?= html_escape(format_estimate_number($estimate->id)); ?>
                        </span>
                    </h3>
                    <span class="estimate-html-status">
                        <?= format_estimate_status($estimate->status, '', true); ?>
                    </span>
                </div>

                <div class="tw-flex tw-items-end tw-space-x-2 tw-mt-3 sm:tw-mt-0 sc-customer-sales-actions">
                    <a href="#document-attachments" class="btn btn-default action-button smart-choice-attachments-button"><i class="fa fa-paperclip"></i> <?= _l('attachments'); ?></a>
                    <?= form_open($this->uri->uri_string(), ['class' => 'tw-inline-block']); ?>
                    <button type="submit" name="estimatepdf" class="btn btn-default action-button download" value="estimatepdf">
                        <i class="fa-regular fa-file-pdf"></i> <?= _l('clients_invoice_html_btn_download'); ?>
                    </button>
                    <?= form_close(); ?>
                    <a href="<?= site_url('appointly/appointments_public/book'); ?>?col=<?= rawurlencode('col-md-8 col-md-offset-2'); ?>" class="btn btn-primary action-button sc-service-appointment-button"><i class="fa fa-calendar-check"></i> <?= _l('sc_make_service_appointment'); ?></a>
                    <?php if (is_client_logged_in() && has_contact_permission('estimates')) { ?>
                    <a href="<?= site_url('clients/estimates/'); ?>"
                        class="btn btn-default action-button go-to-portal">
                        <?= _l('client_go_to_dashboard'); ?>
                    </a>
                    <?php } ?>
                    <?php
                    // Is not accepted, declined and expired
                  if ($estimate->status != 4 && $estimate->status != 3 && $estimate->status != 5) {
                      echo form_open($this->uri->uri_string(), ['class' => 'action-button']);
                      echo form_hidden('estimate_action', 3);
                      echo '<button type="submit" data-loading-text="' . _l('wait_text') . '" autocomplete="off" class="btn btn-default action-button decline"><i class="fa fa-remove"></i> ' . _l('clients_decline_estimate') . '</button>';
                      echo form_close();
                  }
// Is not accepted, declined and expired
if ($estimate->status != 4 && $estimate->status != 3 && $estimate->status != 5) {
    $can_be_accepted = true;
    if ($identity_confirmation_enabled == '0') {
        echo form_open($this->uri->uri_string(), ['class' => 'action-button']);
        echo form_hidden('estimate_action', 4);
        echo '<button type="submit" data-loading-text="' . _l('wait_text') . '" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> ' . _l('clients_accept_estimate') . '</button>';
        echo form_close();
    } else {
        echo '<button type="button" id="accept_action" class="btn btn-success action-button accept"><i class="fa fa-check"></i> ' . _l('clients_accept_estimate') . '</button>';
    }
} elseif ($estimate->status == 3) {
    if (($estimate->expirydate >= date('Y-m-d') || ! $estimate->expirydate) && $estimate->status != 5) {
        $can_be_accepted = true;
        if ($identity_confirmation_enabled == '0') {
            echo form_open($this->uri->uri_string(), ['class' => 'action-button']);
            echo form_hidden('estimate_action', 4);
            echo '<button type="submit" data-loading-text="' . _l('wait_text') . '" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> ' . _l('clients_accept_estimate') . '</button>';
            echo form_close();
        } else {
            echo '<button type="button" id="accept_action" class="btn btn-success action-button accept"><i class="fa fa-check"></i> ' . _l('clients_accept_estimate') . '</button>';
        }
    }
}
?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row sc-sales-document-layout">
    <div class="col-md-8 sc-sales-document-main">
    <div class="panel_s tw-mt-6">
        <div class="panel-body">
            <div class="col-md-10 col-md-offset-1">
                <div class="row mtop20">
                    <div class="col-md-6 col-sm-6 transaction-html-info-col-left">
                        <h4 class="bold estimate-html-number">
                            <?= html_escape(format_estimate_number($estimate->id)); ?>
                        </h4>
                        <address class="estimate-html-company-info tw-text-neutral-500 tw-text-normal">
                            <?= format_organization_info(); ?>
                        </address>
                    </div>
                    <div class="col-sm-6 text-right transaction-html-info-col-right">
                        <span class="tw-font-medium tw-text-neutral-600 estimate_to">
                            <?= _l('estimate_to'); ?>
                        </span>
                        <address class="estimate-html-customer-billing-info tw-text-neutral-500 tw-text-normal">
                            <?= format_customer_info($estimate, 'estimate', 'billing'); ?>
                        </address>
                        <!-- shipping details -->
                        <?php if ($estimate->include_shipping == 1 && $estimate->show_shipping_on_estimate == 1) { ?>
                        <span class="tw-font-medium tw-text-neutral-700 estimate_ship_to">
                            <?= _l('ship_to'); ?>
                        </span>
                        <address class="estimate-html-customer-shipping-info tw-text-neutral-500 tw-text-normal">
                            <?= format_customer_info($estimate, 'estimate', 'shipping'); ?>
                        </address>
                        <?php } ?>
                        <p class="estimate-html-date tw-mb-0 tw-text-normal">
                            <span class="tw-font-medium tw-text-neutral-700">
                                <?= _l('estimate_data_date'); ?>:
                            </span>
                            <?= html_escape(_d($estimate->date)); ?>
                        </p>
                        <?php if (! empty($estimate->expirydate)) { ?>
                        <p class="estimate-html-expiry-date tw-mb-0 tw-text-normal">
                            <span class="tw-font-medium tw-text-neutral-700">
                                <?= _l('estimate_data_expiry_date'); ?>:
                            </span>
                            <?= html_escape(_d($estimate->expirydate)); ?>
                        </p>
                        <?php } ?>
                        <?php if (! empty($estimate->reference_no)) { ?>
                        <p class="estimate-html-reference-no tw-mb-0 tw-text-normal">
                            <span
                                class="tw-font-medium tw-text-neutral-700"><?= _l('reference_no'); ?>:</span>
                            <?= html_escape($estimate->reference_no); ?>
                        </p>
                        <?php } ?>
                        <?php if ($estimate->sale_agent && get_option('show_sale_agent_on_estimates') == 1) { ?>
                        <p class="estimate-html-sale-agent tw-mb-0 tw-text-normal">
                            <span
                                class="tw-font-medium tw-text-neutral-700"><?= _l('sale_agent_string'); ?>:</span>
                            <?= html_escape(get_staff_full_name($estimate->sale_agent)); ?>
                        </p>
                        <?php } ?>
                        <?php if ($estimate->project_id && get_option('show_project_on_estimate') == 1) { ?>
                        <p class="estimate-html-project tw-mb-0 tw-text-normal">
                            <span
                                class="tw-font-medium tw-text-neutral-700"><?= _l('project'); ?>:</span>
                            <?= html_escape(get_project_name_by_id($estimate->project_id)); ?>
                        </p>
                        <?php } ?>
                        <?php $pdf_custom_fields = get_custom_fields('estimate', ['show_on_pdf' => 1, 'show_on_client_portal' => 1]);

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($estimate->id, $field['id'], 'estimate');
    if ($value == '') {
        continue;
    } ?>
                        <p class="tw-mb-0 tw-text-normal">
                            <span class="tw-font-medium tw-text-neutral-700">
                                <?= html_escape(sc_smart_choice_custom_field_label($field['name'])); ?>:
                            </span>
                            <?= $value; ?>
                        </p>
                        <?php
} ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <?php
              $items = get_items_table_data($estimate, 'estimate');
echo $items->table();
?>
                        </div>
                    </div>
                    <div class="col-md-6 col-md-offset-6">
                        <table class="table text-right tw-text-normal">
                            <tbody>
                                <tr id="subtotal">
                                    <td>
                                        <span class="bold tw-text-neutral-700">
                                            <?= _l('estimate_subtotal'); ?>
                                        </span>
                                    </td>
                                    <td class="subtotal">
                                        <?= html_escape(app_format_money($estimate->subtotal, $estimate->currency_name)); ?>
                                    </td>
                                </tr>
                                <?php if (is_sale_discount_applied($estimate)) { ?>
                                <tr>
                                    <td>
                                        <span
                                            class="bold tw-text-neutral-700"><?= _l('estimate_discount'); ?>
                                            <?php if (is_sale_discount($estimate, 'percent')) { ?>
                                            (<?= html_escape(app_format_number($estimate->discount_percent, true)); ?>%)
                                            <?php } ?>
                                        </span>
                                    </td>
                                    <td class="discount">
                                        <?= html_escape('-' . app_format_money($estimate->discount_total, $estimate->currency_name)); ?>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php
        foreach ($items->taxes() as $tax) {
            echo '<tr class="tax-area"><td class="bold !tw-text-neutral-700">' . e($tax['taxname']) . ' (' . e(app_format_number($tax['taxrate'])) . '%)</td><td>' . e(app_format_money($tax['total_tax'], $estimate->currency_name)) . '</td></tr>';
        }
?>
                                <?php if ((int) $estimate->adjustment != 0) { ?>
                                <tr>
                                    <td>
                                        <span class="bold tw-text-neutral-700">
                                            <?= _l('estimate_adjustment'); ?>
                                        </span>
                                    </td>
                                    <td class="adjustment">
                                        <?= html_escape(app_format_money($estimate->adjustment, $estimate->currency_name)); ?>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td>
                                        <span class="bold tw-text-neutral-700">
                                            <?= _l('estimate_total'); ?>
                                        </span>
                                    </td>
                                    <td class="total">
                                        <?= html_escape(app_format_money($estimate->total, $estimate->currency_name)); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
<?php
$scPaymentMeta = null;
if (isset($estimate->id) && $this->db->table_exists(db_prefix() . 'sc_sales_meta')) {
    $scPaymentMeta = $this->db
        ->where(['rel_type' => 'estimate', 'rel_id' => (int) $estimate->id])
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
        <div><span><?= _l('sc_contract_total'); ?></span><strong><?= html_escape(app_format_money($scPaymentMeta->contract_total, $estimate->currency_name)); ?></strong></div>
        <div><span><?= _l('sc_amount_due_now'); ?></span><strong><?= html_escape(app_format_money($scPaymentMeta->due_now, $estimate->currency_name)); ?></strong></div>
        <div><span><?= _l('sc_remaining_balance'); ?></span><strong><?= html_escape(app_format_money($scPaymentMeta->remaining_balance, $estimate->currency_name)); ?></strong></div>
        <?php if ($scDiscountLabel !== '') { ?>
        <div><span>Discount</span><strong><?= html_escape($scDiscountLabel); ?></strong></div>
        <?php } ?>
        <?php if (!empty($scPaymentMeta->payment_link)) { ?>
        <div class="smart-choice-payment-link"><a href="<?= html_escape($scPaymentMeta->payment_link); ?>" target="_blank" rel="noopener">Open Payment Link</a></div>
        <?php } ?>
    </div>
</div>
<?php } ?>

                <?php hooks()->do_action('after_total_summary_estimatehtml', $estimate); ?>
                    <?php
               if (get_option('total_to_words_enabled') == 1) { ?>
                    <div class="col-md-12 text-center estimate-html-total-to-words">
                        <p class="tw-font-medium">
                            <?= _l('num_word'); ?>:<span
                                class="tw-text-neutral-500">
                                <?= $this->numberword->convert($estimate->total, $estimate->currency_name); ?>
                            </span>
                        </p>
                    </div>
                    <?php } ?>
                    <?php if (count($scCustomerAttachments) > 0) { ?>
                    <div class="clearfix"></div>
                    <div id="document-attachments" class="estimate-html-files">
                        <div class="col-md-12">
                            <hr />
                            <p class="bold mbot15 font-medium">
                                <?= _l('estimate_files'); ?>
                            </p>
                        </div>
                        <?php foreach ($scCustomerAttachments as $attachment) {
                            // Do not show hidden attachments to customer
                            if ($attachment['visible_to_customer'] == 0) {
                                continue;
                            }
                            $attachment_url = sc_sales_attachment_download_url($attachment);
                            $attachment_preview_url = sc_sales_attachment_preview_url($attachment);
                            if (! empty($attachment['external'])) {
                                $attachment_url = $attachment['external_link'];
                            } ?>
                        <div class="col-md-12 mbot15">
                            <div class="pull-left"><i
                                    class="<?= get_mime_class($attachment['filetype']); ?>"></i>
                            </div>
                            <a class="smart-choice-attachment-open" target="_blank" rel="noopener" href="<?= html_escape($attachment_url); ?>"><i class="fa fa-eye"></i> <?= html_escape($attachment['file_name']); ?></a>
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
                    <?php if (! empty($estimate->clientnote)) { ?>
                    <div class="col-md-12 estimate-html-note">
                        <p class="tw-mb-2.5 tw-font-medium">
                            <b><?= _l('estimate_note'); ?></b>
                        </p>
                        <div class="tw-text-neutral-500">
                            <?= process_text_content_for_display($estimate->clientnote); ?>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if (! empty($estimate->terms)) { ?>
                    <div class="col-md-12 estimate-html-terms-and-conditions">
                        <hr />
                        <p class="tw-mb-2.5 tw-font-medium">
                            <b><?= _l('terms_and_conditions'); ?></b>
                        </p>
                        <div class="tw-text-neutral-500">
                            <?= process_text_content_for_display($estimate->terms); ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="col-md-4 sc-sales-document-sidebar">
        <?= sc_sales_public_sidebar('estimate', $estimate, $hash); ?>
    </div>
    </div>
    <?php
   if ($identity_confirmation_enabled == '1' && $can_be_accepted) {
       get_template_part('identity_confirmation_form', ['formData' => form_hidden('estimate_action', 4)]);
   }
?>
    <script>
        $(function() {
            new Sticky('[data-sticky]');
            $('.select-item').on('change', function() {
                const $el = $(this);
                $.post(window.location.href, {
                    item_id: $el.closest('tr').data('item-id'),
                    choosen: $el.prop('checked') ? 1 : 0,
                    action: 'estimate_item_selection_changed'
                }).done(function() { window.location.reload(); });
            });
        })
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

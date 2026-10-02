<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $scCustomerAttachments = sc_sales_customer_attachments('proposal', (int) $proposal->id); ?>
<div id="proposal-wrapper">
    <?php
      ob_start();
$items = get_items_table_data($proposal, 'proposal')
    ->add_table_class('no-margin')
    ->set_headings('estimate');

echo $items->table();
?>
    <div class="row mtop15">
        <div class="col-md-6 col-md-offset-6">
            <table class="table text-right">
                <tbody>
                    <tr id="subtotal">
                        <td>
                            <span class="bold tw-text-neutral-700">
                                <?= _l('estimate_subtotal'); ?>
                            </span>
                        </td>
                        <td class="subtotal">
                            <?= html_escape(app_format_money($proposal->subtotal, $proposal->currency_name)); ?>
                        </td>
                    </tr>
                    <?php if (is_sale_discount_applied($proposal)) { ?>
                    <tr>
                        <td>
                            <span
                                class="bold tw-text-neutral-700"><?= _l('estimate_discount'); ?>
                                <?php if (is_sale_discount($proposal, 'percent')) { ?>
                                (<?= html_escape(app_format_number($proposal->discount_percent, true)); ?>%)
                                <?php } ?>
                            </span>
                        </td>
                        <td class="discount">
                            <?= html_escape('-' . app_format_money($proposal->discount_total, $proposal->currency_name)); ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php
                  foreach ($items->taxes() as $tax) {
                      echo '<tr class="tax-area"><td class="bold !tw-text-neutral-700">' . e($tax['taxname']) . ' (' . e(app_format_number($tax['taxrate'])) . '%)</td><td>' . e(app_format_money($tax['total_tax'], $proposal->currency_name)) . '</td></tr>';
                  }
?>
                    <?php if ((int) $proposal->adjustment != 0) { ?>
                    <tr>
                        <td>
                            <span class="bold tw-text-neutral-700">
                                <?= _l('estimate_adjustment'); ?>
                            </span>
                        </td>
                        <td class="adjustment">
                            <?= html_escape(app_format_money($proposal->adjustment, $proposal->currency_name)); ?>
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
                            <?= html_escape(app_format_money($proposal->total, $proposal->currency_name)); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
<?php
$scPaymentMeta = null;
if (isset($proposal->id) && $this->db->table_exists(db_prefix() . 'sc_sales_meta')) {
    $scPaymentMeta = $this->db
        ->where(['rel_type' => 'proposal', 'rel_id' => (int) $proposal->id])
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
        <div><span><?= _l('sc_contract_total'); ?></span><strong><?= html_escape(app_format_money($scPaymentMeta->contract_total, $proposal->currency_name)); ?></strong></div>
        <div><span><?= _l('sc_amount_due_now'); ?></span><strong><?= html_escape(app_format_money($scPaymentMeta->due_now, $proposal->currency_name)); ?></strong></div>
        <div><span><?= _l('sc_remaining_balance'); ?></span><strong><?= html_escape(app_format_money($scPaymentMeta->remaining_balance, $proposal->currency_name)); ?></strong></div>
        <?php if ($scDiscountLabel !== '') { ?>
        <div><span>Discount</span><strong><?= html_escape($scDiscountLabel); ?></strong></div>
        <?php } ?>
        <?php if (!empty($scPaymentMeta->payment_link)) { ?>
        <div class="smart-choice-payment-link"><a href="<?= html_escape($scPaymentMeta->payment_link); ?>" target="_blank" rel="noopener">Open Payment Link</a></div>
        <?php } ?>
    </div>
</div>
<?php } ?>

<?php
      if (get_option('total_to_words_enabled') == 1) { ?>
    <div class="col-md-12 text-center proposal-html-total-to-words">
        <p class="tw-font-medium">
            <?= _l('num_word'); ?>:<span
                class="tw-text-neutral-500">
                <?= $this->numberword->convert($proposal->total, $proposal->currency_name); ?>
            </span>
        </p>
    </div>
    <?php }
      $items = ob_get_contents();
ob_end_clean();
$scProposalHadItemsToken = strpos((string) $proposal->content, '{proposal_items}') !== false;
$proposal->content = str_replace('{proposal_items}', $items, $proposal->content);
if (! $scProposalHadItemsToken) {
    $proposal->content .= '<div class="sc-proposal-public-items mtop20">' . $items . '</div>';
}
?>
    <div class="mtop15 preview-top-wrapper">
        <div class="row">
            <div class="col-md-3">
                <div class="mbot30">
                    <div class="proposal-html-logo">
                        <?= get_dark_company_logo(); ?>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="top" data-sticky data-sticky-class="preview-sticky-header">
            <div class="container preview-sticky-container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-left">
                            <h4 class="tw-font-semibold tw-my-0 proposal-html-number">#
                                <?= html_escape(format_proposal_number($proposal->id)); ?><br />
                                <small
                                    class="proposal-html-subject"><?= html_escape($proposal->subject); ?></small>
                            </h4>
                        </div>
                        <div class="visible-xs">
                            <div class="clearfix"></div>
                        </div>
                        <div class="sc-proposal-action-row">
                            <?php if (count($scCustomerAttachments) > 0) { ?>
                            <a href="#document-attachments" class="btn btn-default action-button smart-choice-attachments-button"><i class="fa fa-paperclip"></i> <?= _l('attachments'); ?></a>
                            <?php } ?>
                            <?= form_open($this->uri->uri_string(), ['class'=>'tw-inline-block']); ?>
                            <button type="submit" class="btn btn-default action-button"><i class="fa-regular fa-file-pdf"></i> <?= _l('clients_invoice_html_btn_download'); ?></button>
                            <?= form_hidden('action', 'proposal_pdf'); ?>
                            <?= form_close(); ?>
                            <?php if (($proposal->status != 2 && $proposal->status != 3)) {
                                if (!empty($proposal->open_till) && date('Y-m-d', strtotime($proposal->open_till)) < date('Y-m-d')) {
                                    echo '<span class="label label-warning">' . _l('proposal_expired') . '</span>';
                                } else { ?>
                                    <?= form_open($this->uri->uri_string(), ['class'=>'tw-inline-block']); ?>
                                    <button type="submit" data-loading-text="<?= _l('wait_text'); ?>" autocomplete="off" class="btn btn-default action-button"><i class="fa fa-remove"></i> <?= _l('proposal_decline_info'); ?></button>
                                    <?= form_hidden('action', 'decline_proposal'); ?>
                                    <?= form_close(); ?>
                                    <?php if ($identity_confirmation_enabled == '1') { ?>
                                    <button type="button" id="accept_action" class="btn btn-success action-button"><i class="fa fa-check"></i> <?= _l('proposal_accept_info'); ?></button>
                                    <?php } else { ?>
                                    <?= form_open($this->uri->uri_string(), ['class'=>'tw-inline-block']); ?>
                                    <button type="submit" data-loading-text="<?= _l('wait_text'); ?>" autocomplete="off" class="btn btn-success action-button"><i class="fa fa-check"></i> <?= _l('proposal_accept_info'); ?></button>
                                    <?= form_hidden('action', 'accept_proposal'); ?>
                                    <?= form_close(); ?>
                                    <?php } ?>
                                <?php }
                            } else {
                                if ($proposal->status == 2) {
                                    echo '<span class="label label-danger">' . _l('proposal_status_declined') . '</span>';
                                } elseif ($proposal->status == 3) {
                                    echo '<span class="label label-success">' . _l('proposal_status_accepted') . '</span>';
                                }
                            } ?>
                            <a href="<?= site_url('appointly/appointments_public/book'); ?>?col=<?= rawurlencode('col-md-8 col-md-offset-2'); ?>" class="btn btn-primary action-button sc-service-appointment-button"><i class="fa fa-calendar-check"></i> <?= _l('sc_make_service_appointment'); ?></a>
                            <?php if (is_client_logged_in() && has_contact_permission('proposals')) { ?>
                            <a href="<?= site_url('clients/proposals/'); ?>" class="btn btn-default action-button go-to-portal"><?= _l('client_go_to_dashboard'); ?></a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 proposal-left">
            <div class="panel_s mtop20">
                <div class="panel-body proposal-content tc-content">
                    <?= $proposal->content; ?>
                    <?php if (!empty($proposal->clientnote)) { ?>
                    <div class="sc-proposal-public-client-note mtop25">
                        <h4 class="tw-font-semibold"><?= _l('client_note'); ?></h4>
                        <div class="tw-text-neutral-700"><?= $proposal->clientnote; ?></div>
                    </div>
                    <?php } ?>
                    <?php if (!empty($proposal->terms)) { ?>
                    <div class="sc-proposal-public-terms mtop25">
                        <h4 class="tw-font-semibold"><?= _l('terms_and_conditions'); ?></h4>
                        <div class="tw-text-neutral-700"><?= $proposal->terms; ?></div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 proposal-right">
            <div class="inner mtop20 proposal-html-tabs">
                <ul class="nav nav-tabs nav-tabs-flat mbot15" role="tablist">
                    <li role="presentation" class="<?php if (! $this->input->get('tab') || $this->input->get('tab') === 'summary') {
                        echo 'active';
                    } ?>">
                        <a href="#summary" aria-controls="summary" role="tab" data-toggle="tab"
                            class="tw-flex tw-justify-center tw-space-x-1">
                            <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
                            <span><?= _l('summary'); ?></span>
                        </a>
                    </li>
                    <?php if ($proposal->allow_comments == 1) { ?>
                    <li role="presentation" class="<?php if ($this->input->get('tab') === 'discussion') {
                        echo 'active';
                    } ?>">
                        <a href="#discussion" aria-controls="discussion" role="tab" data-toggle="tab"
                            class="tw-flex tw-justify-center tw-space-x-1">
                            <i class="fa-regular fa-comment" aria-hidden="true"></i>
                            <span><?= _l('discussion'); ?></span>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane<?php if (! $this->input->get('tab') || $this->input->get('tab') === 'summary') {
                        echo ' active';
                    } ?>" id="summary">
                        <address class="proposal-html-company-info tw-text-neutral-500 tw-text-normal">
                            <?= format_organization_info(); ?>
                        </address>
                        <hr />
                        <p class="bold proposal-html-information tw-text-neutral-700">
                            <?= _l('proposal_information'); ?>
                        </p>
                        <address class="tw-mb-0 proposal-html-info tw-text-neutral-500 tw-text-normal">
                            <?= format_proposal_info($proposal, 'html'); ?>
                        </address>
                        <div class="row mtop20">
                            <?php if ($proposal->total != 0) { ?>
                            <div class="tw-text-normal col-md-12 proposal-html-total">
                                <h4 class="bold tw-mb-3">
                                    <?= html_escape(_l('proposal_total_info', app_format_money($proposal->total, $proposal->currency_name))); ?>
                                </h4>
                            </div>
                            <?php } ?>
                            <div class="tw-text-normal col-md-4 text-muted proposal-status">
                                <?= _l('proposal_status'); ?>
                            </div>
                            <div class="tw-text-normal col-md-8 proposal-status tw-text-neutral-700">
                                <?= html_escape(format_proposal_status($proposal->status, '', false)); ?>
                            </div>
                            <div class="tw-text-normal col-md-4 text-muted proposal-date">
                                <?= _l('proposal_date'); ?>
                            </div>
                            <div class="tw-text-normal col-md-8 proposal-date tw-text-neutral-700">
                                <?= html_escape(_d($proposal->date)); ?>
                            </div>
                            <?php if (! empty($proposal->open_till)) { ?>
                            <div class="tw-text-normal col-md-4 text-muted proposal-open-till">
                                <?= _l('proposal_open_till'); ?>
                            </div>
                            <div class="tw-text-normal col-md-8 proposal-open-till tw-text-neutral-700">
                                <?= html_escape(_d($proposal->open_till)); ?>
                            </div>
                            <?php } ?>
                            <?php if ($proposal->project_id != '' && get_option('show_project_on_proposal') == 1) { ?>
                            <div class="tw-text-normal col-md-4 text-muted proposal-html-project">
                                <?= _l('project'); ?>
                            </div>
                            <div class="tw-text-normal col-md-8 proposal-html-project tw-text-neutral-700">
                                <?= html_escape(get_project_name_by_id($proposal->project_id)); ?>
                            </div>
                            <?php } ?>
                        </div>
                        <?php if (count($scCustomerAttachments) > 0) { ?>
                        <div id="document-attachments" class="proposal-attachments">
                            <hr />
                            <p class="bold mbot15">
                                <?= _l('proposal_files'); ?>
                            </p>
                            <?php foreach ($scCustomerAttachments as $attachment) {
                                if ($attachment['visible_to_customer'] == 0) {
                                    continue;
                                }
                                $attachment_url = sc_sales_attachment_download_url($attachment);
                            $attachment_preview_url = sc_sales_attachment_preview_url($attachment);
                                if (! empty($attachment['external'])) {
                                    $attachment_url = $attachment['external_link'];
                                } ?>
                            <div class="col-md-12 row mbot15">
                                <div class="pull-left"><i
                                        class="<?= get_mime_class($attachment['filetype']); ?>"></i>
                                </div>
                                <a class="smart-choice-attachment-open" target="_blank" rel="noopener" href="<?= html_escape($attachment_preview_url); ?>"><i class="fa fa-eye"></i> <?= html_escape($attachment['file_name']); ?></a>
                        <?php $scExt = strtolower(pathinfo($attachment['file_name'], PATHINFO_EXTENSION)); ?>
                        <?php if (in_array($scExt, ['jpg','jpeg','png','gif','webp'], true)) { ?>
                        <div class="smart-choice-attachment-preview"><a href="<?= html_escape($attachment_preview_url); ?>" target="_blank"><img src="<?= html_escape($attachment_preview_url); ?>" alt="<?= html_escape($attachment['file_name']); ?>"></a></div>
                        <?php } elseif ($scExt === 'pdf') { ?>
                        <div class="smart-choice-attachment-preview smart-choice-pdf-preview"><div class="mbot8"><a class="btn btn-default btn-xs" target="_blank" rel="noopener" href="<?= html_escape($attachment_preview_url); ?>"><i class="fa fa-external-link"></i> <?= _l('sc_open_attachment_new_tab'); ?></a></div><iframe src="<?= html_escape($attachment_preview_url); ?>#toolbar=1&navpanes=0" title="<?= html_escape($attachment['file_name']); ?>" loading="lazy"></iframe></div>
                        <?php } ?>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <?= sc_sales_links_public_html('proposal', $proposal->id); ?>
                    </div>
                    <?php if ($proposal->allow_comments == 1) { ?>
                    <div role="tabpanel" class="tab-pane<?php if ($this->input->get('tab') === 'discussion') {
                        echo ' active';
                    } ?>" id="discussion">
                        <?= form_open($this->uri->uri_string()); ?>
                        <div class="proposal-comment">
                            <textarea name="content" rows="4" class="form-control"></textarea>
                            <button type="submit" class="btn btn-primary mtop10 pull-right"
                                data-loading-text="<?= _l('wait_text'); ?>"><?= _l('proposal_add_comment'); ?></button>
                            <?= form_hidden('action', 'proposal_comment'); ?>
                        </div>
                        <?= form_close(); ?>
                        <div class="clearfix"></div>
                        <?php
                     $proposal_comments = '';

                        foreach ($comments as $comment) {
                            $proposal_comments .= '<div class="proposal_comment mtop10 mbot20" data-commentid="' . $comment['id'] . '">';
                            if ($comment['staffid'] != 0) {
                                $proposal_comments .= staff_profile_image($comment['staffid'], [
                                    'staff-profile-image-small',
                                    'media-object img-circle pull-left mright10',
                                ]);
                            }
                            $proposal_comments .= '<div class="media-body valign-middle">';
                            $proposal_comments .= '<div class="mtop5 tw-text-neutral-600">';
                            $proposal_comments .= '<b>';
                            if ($comment['staffid'] != 0) {
                                $proposal_comments .= e(get_staff_full_name($comment['staffid']));
                            } else {
                                $proposal_comments .= _l('is_customer_indicator');
                            }
                            $proposal_comments .= '</b>';
                            $proposal_comments .= ' - <small class="mtop10 text-muted">' . e(time_ago($comment['dateadded'])) . '</small>';
                            $proposal_comments .= '</div>';
                            $proposal_comments .= '<div class="tw-text-neutral-500">';
                            $proposal_comments .= process_text_content_for_display($comment['content']);
                            $proposal_comments .= '</div>';
                            $proposal_comments .= '</div>';
                            $proposal_comments .= '</div>';
                        }
                        echo $proposal_comments; ?>
                    </div>
                    <?php } ?>
                </div>

                <?php if (! empty($proposal->signature)) { ?>
                <div class="row mtop20">
                    <div class="col-md-12 proposal-value">
                        <h4 class="bold mbot10">
                            <?= _l('signature'); ?>
                        </h4>
                    </div>
                    <div class="col-md-5 text-muted proposal-signed-by">
                        <?= _l('proposal_signed_by'); ?>
                    </div>
                    <div class="col-md-7 proposal-proposal-signed-by">
                        <?= html_escape("{$proposal->acceptance_firstname} {$proposal->acceptance_lastname}"); ?>
                    </div>

                    <div class="col-md-5 text-muted proposal-signed-by">
                        <?= _l('proposal_signed_date'); ?>
                    </div>
                    <div class="col-md-7 proposal-proposal-signed-by">
                        <?= html_escape(_d(explode(' ', $proposal->acceptance_date)[0])); ?>
                    </div>

                    <div class="col-md-5 text-muted proposal-signed-by">
                        <?= _l('proposal_signed_ip'); ?>
                    </div>
                    <div class="col-md-7 proposal-signed-by">
                        <?= html_escape($proposal->acceptance_ip); ?>
                    </div>
                </div>
                <?php } ?>
                <div style="margin-top:80px;"></div>
            </div>
        </div>
    </div>
</div>
<?php
   if ($identity_confirmation_enabled == '1') {
       get_template_part('identity_confirmation_form', ['formData' => form_hidden('action', 'accept_proposal')]);
   }
?>
<script>
    $(function() {
        new Sticky('[data-sticky]');
        $(".proposal-left table").wrap("<div class='table-responsive'></div>");
        // Create lightbox for proposal content images
        $('.proposal-content img').wrap(function() {
            return '<a href="' + $(this).attr('src') + '" data-lightbox="proposal"></a>';
        });
        $('.select-item').on('change', function() {
            const $el = $(this);
            $.post(window.location.href, {
                item_id: $el.closest('tr').data('item-id'),
                choosen: $el.prop('checked') ? 1 : 0,
                action: 'item_selection_changed'
            }).done(function() { window.location.reload(); });
        });
    });
</script>
</div>
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

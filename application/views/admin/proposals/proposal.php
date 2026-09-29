<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content accounting-template proposal">
        <div class="row">
            <?php
         if (isset($proposal)) {
             echo form_hidden('isedit', $proposal->id);
         }
$rel_type = '';
$rel_id   = '';
if (isset($proposal) || ($this->input->get('rel_id') && $this->input->get('rel_type'))) {
    if ($this->input->get('rel_id')) {
        $rel_id   = $this->input->get('rel_id');
        $rel_type = $this->input->get('rel_type');
    } else {
        $rel_id   = $proposal->rel_id;
        $rel_type = $proposal->rel_type;
    }
}
?>
            <?= form_open($this->uri->uri_string(), ['id' => 'proposal-form', 'class' => '_transaction_form proposal-form', 'enctype' => 'multipart/form-data']);

if ($this->input->get('estimate_request_id')) {
    echo form_hidden('estimate_request_id', $this->input->get('estimate_request_id'));
}
?>

            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700 tw-flex tw-items-center tw-space-x-2">
                    <span>
                        <?= e(isset($proposal) ? format_proposal_number($proposal->id) : _l('new_proposal')); ?>
                    </span>
                    <?= isset($proposal) ? format_proposal_status($proposal->status) : ''; ?>
                </h4>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <?php $value = (isset($proposal) ? $proposal->subject : ''); ?>
                                <?php $attrs = (isset($proposal) ? [] : ['autofocus' => true]); ?>
                                <?= render_input('subject', 'proposal_subject', $value, 'text', $attrs); ?>
                                <div class="form-group select-placeholder">
                                    <label for="rel_type"
                                        class="control-label"><?= _l('proposal_related'); ?></label>
                                    <select name="rel_type" id="rel_type" class="selectpicker" data-width="100%"
                                        data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                        <option value="lead" <?php if ((isset($proposal) && $proposal->rel_type == 'lead') || $this->input->get('rel_type')) {
                                            if ($rel_type == 'lead') {
                                                echo 'selected';
                                            }
                                        } ?>><?= _l('proposal_for_lead'); ?>
                                        </option>
                                        <option value="customer" <?php if ((isset($proposal) && $proposal->rel_type == 'customer') || $this->input->get('rel_type')) {
                                            if ($rel_type == 'customer') {
                                                echo 'selected';
                                            }
                                        } ?>><?= _l('proposal_for_customer'); ?>
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group select-placeholder<?php if ($rel_id == '') {
                                    echo ' hide';
                                } ?> " id="rel_id_wrapper">
                                    <label for="rel_id"><span class="rel_id_label"></span></label>
                                    <div id="rel_id_select">
                                        <select name="rel_id" id="rel_id" class="ajax-search" data-width="100%"
                                            data-live-search="true"
                                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                            <?php if ($rel_id != '' && $rel_type != '') {
                                                $rel_data = get_relation_data($rel_type, $rel_id);
                                                $rel_val  = get_relation_values($rel_data, $rel_type);
                                                echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                                <div
                                    class="form-group select-placeholder projects-wrapper <?= ((! isset($proposal)) || (isset($proposal) && $proposal->rel_type !== 'customer')) ? 'hide' : '' ?>">
                                    <label
                                        for="project_id"><?= _l('project'); ?></label>
                                    <div id="project_ajax_search_wrapper">
                                        <select name="project_id" id="project_id" class="projects ajax-search"
                                            data-live-search="true" data-width="100%"
                                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                            <?php

                                                                        if (isset($proposal) && $proposal->project_id) {
                                                                            echo '<option value="' . $proposal->project_id . '" selected>' . e(get_project_name_by_id($proposal->project_id)) . '</option>';
                                                                        }
?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?= sc_google_map_field('proposal', isset($proposal) ? $proposal : null); ?>
                                        <?php $value = (isset($proposal) ? _d($proposal->date) : _d(date('Y-m-d'))) ?>
                                        <?= render_date_input('date', 'proposal_date', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php
                        $value = '';
if (isset($proposal)) {
    $value = _d($proposal->open_till);
} else {
    if (get_option('proposal_due_after') != 0) {
        $value = _d(date('Y-m-d', strtotime('+' . get_option('proposal_due_after') . ' DAY', strtotime(date('Y-m-d')))));
    }
}
echo render_date_input('open_till', 'proposal_open_till', $value); ?>
                                    </div>
                                </div>
                                <?php
   $selected   = '';
$currency_attr = ['data-show-subtext' => true];

foreach ($currencies as $currency) {
    if ($currency['isdefault'] == 1) {
        $currency_attr['data-base'] = $currency['id'];
    }
    if (isset($proposal)) {
        if ($currency['id'] == $proposal->currency) {
            $selected = $currency['id'];
        }
        if ($proposal->rel_type == 'customer') {
            $currency_attr['disabled'] = true;
        }
    } else {
        if ($rel_type == 'customer') {
            $customer_currency = $this->clients_model->get_customer_default_currency($rel_id);
            if ($customer_currency != 0) {
                $selected = $customer_currency;
            } else {
                if ($currency['isdefault'] == 1) {
                    $selected = $currency['id'];
                }
            }
            $currency_attr['disabled'] = true;
        } else {
            if ($currency['isdefault'] == 1) {
                $selected = $currency['id'];
            }
        }
    }
}
$currency_attr = apply_filters_deprecated('proposal_currency_disabled', [$currency_attr], '2.3.0', 'proposal_currency_attributes');
$currency_attr = hooks()->apply_filters('proposal_currency_attributes', $currency_attr);
?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?= render_select('currency', $currencies, ['id', 'name', 'symbol'], 'proposal_currency', $selected, $currency_attr);
?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label for="discount_type"
                                                class="control-label"><?= _l('discount_type'); ?></label>
                                            <select name="discount_type" class="selectpicker" data-width="100%"
                                                data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                                <option value="" selected>
                                                    <?= _l('no_discount'); ?>
                                                </option>
                                                <option value="before_tax" <?php
    if (isset($proposal)) {
        if ($proposal->discount_type == 'before_tax') {
            echo 'selected';
        }
    }?>><?= _l('discount_type_before_tax'); ?>
                                                </option>
                                                <option value="after_tax" <?php if (isset($proposal)) {
                                                    if ($proposal->discount_type == 'after_tax') {
                                                        echo 'selected';
                                                    }
                                                } ?>><?= _l('discount_type_after_tax'); ?>
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
</div>
                                <?php $fc_rel_id = (isset($proposal) ? $proposal->id : false); ?>
                                <!-- SMART CHOICE PAYMENT SCHEDULE PROPOSAL -->
                                <?php if (function_exists('module_active') && module_active('smart_choice_payment_schedule') && is_file(module_dir_path('smart_choice_payment_schedule', 'views/partials/payment_fields.php'))) {
                                    $scpsDocumentType = 'proposal';
                                    $scpsDocumentId = isset($proposal) ? (int) $proposal->id : 0;
                                    include module_dir_path('smart_choice_payment_schedule', 'views/partials/payment_fields.php');
                                } ?>
                                <div class="form-group no-mbot">
                                    <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
                                        <?= _l('tags'); ?></label>
                                    <input type="text" class="tagsinput" id="tags" name="tags"
                                        value="<?= isset($proposal) ? prep_tags_input(get_tags_in($proposal->id, 'proposal')) : ''; ?>"
                                        data-role="tagsinput">
                                </div>
                                <div class="form-group mtop10 no-mbot">
                                    <p><?= _l('proposal_allow_comments'); ?>
                                    </p>
                                    <div class="onoffswitch">
                                        <input type="checkbox" id="allow_comments" class="onoffswitch-checkbox" <?php if ((isset($proposal) && $proposal->allow_comments == 1) || ! isset($proposal)) {
                                            echo 'checked';
                                        } ?> value="on" name="allow_comments">
                                        <label class="onoffswitch-label" for="allow_comments" data-toggle="tooltip"
                                            title="<?= _l('proposal_allow_comments_help'); ?>"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel_s sc-proposal-customer-edit-card">
                                    <div class="panel-body tw-bg-neutral-50">
                                        <h4 class="tw-font-semibold tw-mt-0 tw-mb-3"><i class="fa-regular fa-address-card tw-mr-1"></i> <?= _l('sc_customer_information'); ?></h4>
                                <?php $value = (isset($proposal) ? $proposal->proposal_to : ''); ?>
                                <?= render_input('proposal_to', 'proposal_to', $value); ?>
                                <?php $value = (isset($proposal) ? $proposal->address : ''); ?>
                                <?= render_input('address', 'proposal_address', strip_tags($value)); ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->city : ''); ?>
                                        <?= render_input('city', 'billing_city', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->state : ''); ?>
                                        <?= render_input('state', 'billing_state', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $countries = get_all_countries(); ?>
                                        <?php $selected  = (isset($proposal) ? $proposal->country : ''); ?>
                                        <?= render_select('country', $countries, ['country_id', ['short_name'], 'iso2'], 'billing_country', $selected); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->zip : ''); ?>
                                        <?= render_input('zip', 'billing_zip', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->email : ''); ?>
                                        <?= render_input('email', 'proposal_email', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->phone : ''); ?>
                                        <?= render_input('phone', 'proposal_phone', $value); ?>
                                    </div>
                                </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label for="status"
                                                class="control-label"><?= _l('proposal_status'); ?></label>
                                            <?php
                                          $disabled = '';
if (isset($proposal)) {
    if ($proposal->estimate_id != null || $proposal->invoice_id != null) {
        $disabled = 'disabled';
    }
}
?>
                                            
                                            <div class="sc-native-status-wrap">
                                            <select name="status" id="proposal_status" class="selectpicker" data-width="100%"
                                                <?= e($disabled); ?>
                                                data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                                <?php foreach ($statuses as $status) { ?>
                                                <option
                                                    value="<?= e($status); ?>"
                                                    <?php if ((isset($proposal) && $proposal->status == $status) || (! isset($proposal) && $status == 0)) {
                                                        echo 'selected';
                                                    } ?>><?= format_proposal_status($status, '', false); ?>
                                                </option>
                                                <?php } ?>
                                            </select>
                                            <button type="button" class="btn btn-success sc-native-status-add" data-type="proposal" title="<?= _l('sc_add_status'); ?>"><i class="fa fa-plus"></i></button>
                                            </div>
                                            <?php $scCustomStatusValue = isset($proposal) ? (string)($proposal->sc_custom_status_value ?? '') : ''; ?>
<?= form_hidden('sc_custom_status_value', $scCustomStatusValue); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <?php
                                                            $selected = ! isset($proposal) && get_option('automatically_set_logged_in_staff_sales_agent') == '1' ? get_staff_user_id() : '';

foreach ($staff as $member) {
    if (isset($proposal)) {
        if ($proposal->assigned == $member['staffid']) {
            $selected = $member['staffid'];
        }
    }
}
echo render_select('assigned', $staff, ['staffid', ['firstname', 'lastname']], 'proposal_assigned', $selected);
?>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="sc-proposal-custom-fields-fullwidth">
                            <?php $scProposalCustomFields = render_custom_fields('proposal', $fc_rel_id); echo preg_replace('/col-md-\d+/', 'col-md-6', $scProposalCustomFields); ?>
                        </div>
                        <div class="panel-body sc-proposal-content-editor">
                            <?php
                            $scProposalDefaultContent = '';
                            $scProposalContent = isset($proposal) && trim((string)$proposal->content) !== '' ? $proposal->content : $scProposalDefaultContent;
                            ?>
                            <?= render_textarea('content', 'proposal_content', $scProposalContent, ['rows'=>12], [], '', 'tinymce'); ?>
                            <div class="sc-proposal-merge-fields-box mtop10">
                                <strong><?= _l('sc_proposal_merge_fields_help'); ?></strong>
                                <div class="mtop5">
                                    <?php foreach ([
                                        '{proposal_proposal_to}' => _l('sc_customer_name'),
                                        '{proposal_email}' => _l('sc_customer_email'),
                                        '{proposal_phone}' => _l('sc_customer_phone'),
                                        '{proposal_address}' => _l('sc_project_address'),
                                        '{proposal_items}' => _l('items'),
                                    ] as $mergeCode => $mergeLabel) { ?>
                                    <button type="button" class="btn btn-default btn-xs sc-insert-proposal-merge-field" data-merge="<?= e($mergeCode); ?>">
                                        <i class="fa fa-code"></i> <?= e($mergeLabel); ?>
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div
                            class="btn-bottom-toolbar bottom-transaction text-right sm:tw-flex sm:tw-items-center sm:tw-justify-between">
<span></span>
                            <div>
                                <button type="button"
                                    class="btn btn-default mleft10 proposal-form-submit save-and-send transaction-submit">
                                    <?= _l('save_and_send'); ?>
                                </button>
                                <button class="btn btn-primary mleft5 proposal-form-submit transaction-submit"
                                    type="button">
                                    <?= _l('submit'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <hr class="hr-panel-separator" />
                    <?php $estimate = isset($proposal) ? $proposal : null; $is_proposal = true; $this->load->view('admin/estimates/_add_edit_items'); ?>
                    <?php $this->load->view('admin/includes/smart_choice_sales_attachments', [
                        'sc_rel_type' => 'proposal',
                        'sc_rel_id'   => isset($proposal) ? (int) $proposal->id : 0,
                    ]); ?>
                    <div class="panel-body sc-proposal-notes-terms">
                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = isset($proposal) && isset($proposal->clientnote) ? $proposal->clientnote : 'Thank you for considering Smart Choice Contractors USA for your construction needs. This proposal is based on the details discussed during our consultation and is valid for 30 days from the date issued.'; ?>
                                <label for="clientnote"><?= _l('client_note'); ?></label>
                                <textarea name="clientnote" id="clientnote" rows="8" class="form-control sc-proposal-rich-editor"><?= html_escape(clear_textarea_breaks($value)); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <?php $value = isset($proposal) && isset($proposal->terms) ? $proposal->terms : 'This proposal is valid for 30 days from the issue date. Pricing is subject to change after this period. Any additional work requires a written change order. Payment terms, project schedule, warranty, cancellation, and dispute-resolution requirements are governed by the accepted agreement and applicable Florida law.'; ?>
                                <label for="terms"><?= _l('terms_and_conditions'); ?></label>
                                <textarea name="terms" id="terms" rows="8" class="form-control sc-proposal-rich-editor"><?= html_escape(clear_textarea_breaks($value)); ?></textarea>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php $this->load->view('admin/includes/smart_choice_sales_links', ['sc_rel_type'=>'proposal','sc_rel_id'=>isset($proposal)?$proposal->id:0]); ?>
            <?= form_close(); ?>
            <?php $this->load->view('admin/invoice_items/item'); ?>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    var _rel_id = $('#rel_id'),
        _rel_type = $('#rel_type'),
        _rel_id_wrapper = $('#rel_id_wrapper'),
        _project_wrapper = $('.projects-wrapper'),
        data = {};

    $(function() {
        // Client note and terms are always rich-text editors. No duplicate enable/disable controls.
        init_editor('#content,#clientnote,#terms', { height: 220 });

        // Some legacy hooks can render the proposal optional checkbox twice. Keep exactly one per item row.
        function scNormalizeProposalOptionalCheckboxes() {
            $('.accounting-template.proposal table.items tr').each(function() {
                var $optional = $(this).find('.optional-item-checkbox');
                if ($optional.length > 1) {
                    $optional.slice(1).each(function() {
                        var $wrap = $(this).closest('.checkbox');
                        if ($wrap.length) { $wrap.remove(); } else { $(this).remove(); }
                    });
                }
            });
        }
        scNormalizeProposalOptionalCheckboxes();
        $(document).on('item-added-to-table', function() { window.setTimeout(scNormalizeProposalOptionalCheckboxes, 0); });

        <?php if (isset($proposal) && $proposal->rel_type === 'customer') { ?>
        init_proposal_project_select('select#project_id')
        <?php } ?>
        $('body').on('change', '#rel_type', function() {
            if (_rel_type.val() != 'customer') {
                _project_wrapper.addClass('hide')
            }
        });

        $('body').on('change', '#rel_id', function() {
            if (_rel_type.val() == 'customer') {
                console.log('working')
                var projectAjax = $('select#project_id');
                var clonedProjectsAjaxSearchSelect = projectAjax.html('').clone();
                projectAjax.selectpicker('destroy').remove();
                projectAjax = clonedProjectsAjaxSearchSelect;
                $('#project_ajax_search_wrapper').append(clonedProjectsAjaxSearchSelect);
                init_proposal_project_select(projectAjax);
                _project_wrapper.removeClass('hide')
            }
        });

        init_currency();

        // Maybe items ajax search
        validate_proposal_form();
        $('body').on('change', '#rel_id', function() {
            if ($(this).val() != '') {
                $.get(admin_url + 'proposals/get_relation_data_values/' + $(this).val() + '/' +
                    _rel_type
                    .val(),
                    function(response) {
                        $('input[name="proposal_to"]').val(response.to);
                        $('textarea[name="address"]').val(response.address);
                        $('input[name="email"]').val(response.email);
                        $('input[name="phone"]').val(response.phone);
                        $('input[name="city"]').val(response.city);
                        $('input[name="state"]').val(response.state);
                        $('input[name="zip"]').val(response.zip);
                        $('select[name="country"]').selectpicker('val', response.country);
                        var currency_selector = $('#currency');
                        if (_rel_type.val() == 'customer') {
                            if (typeof(currency_selector.attr('multi-currency')) == 'undefined') {
                                currency_selector.attr('disabled', true);
                            }

                        } else {
                            currency_selector.attr('disabled', false);
                        }
                        var proposal_to_wrapper = $('[app-field-wrapper="proposal_to"]');
                        if (response.is_using_company == false && !empty(response.company)) {
                            proposal_to_wrapper.find('#use_company_name').remove();
                            proposal_to_wrapper.find('#use_company_help').remove();
                            proposal_to_wrapper.append('<div id="use_company_help" class="hide">' +
                                response.company + '</div>');
                            proposal_to_wrapper.find('label')
                                .prepend(
                                    "<a href=\"#\" id=\"use_company_name\" data-toggle=\"tooltip\" data-title=\"<?= _l('use_company_name_instead'); ?>\" onclick='document.getElementById(\"proposal_to\").value = document.getElementById(\"use_company_help\").innerHTML.trim(); this.remove();'><i class=\"fa fa-building-o\"></i></a> "
                                );
                        } else {
                            proposal_to_wrapper.find('label #use_company_name').remove();
                            proposal_to_wrapper.find('label #use_company_help').remove();
                        }
                        /* Check if customer default currency is passed */
                        if (response.currency) {
                            currency_selector.selectpicker('val', response.currency);
                        } else {
                            /* Revert back to base currency */
                            currency_selector.selectpicker('val', currency_selector.data('base'));
                        }
                        currency_selector.selectpicker('refresh');
                        currency_selector.change();
                    }, 'json');
            }
        });
        $('.rel_id_label').html(_rel_type.find('option:selected').text());
        _rel_type.on('change', function() {
            var clonedSelect = _rel_id.html('').clone();
            _rel_id.selectpicker('destroy').remove();
            _rel_id = clonedSelect;
            $('#rel_id_select').append(clonedSelect);
            proposal_rel_id_select();
            if ($(this).val() != '') {
                _rel_id_wrapper.removeClass('hide');
            } else {
                _rel_id_wrapper.addClass('hide');
            }
            $('.rel_id_label').html(_rel_type.find('option:selected').text());
        });
        proposal_rel_id_select();
        if ($('#item_select.ajax-search').length) {
            init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
        }
        <?php if (! isset($proposal) && $rel_id != '') { ?>
        _rel_id.change();
        <?php } ?>
    });

    function init_proposal_project_select(selector) {
        init_ajax_search('project', selector, {
            customer_id: function() {
                return $('#rel_id').val();
            }
        })
    }

    function proposal_rel_id_select() {
        var serverData = {};
        serverData.rel_id = _rel_id.val();
        data.type = _rel_type.val();
        init_ajax_search(_rel_type.val(), _rel_id, serverData);
    }

    function validate_proposal_form() {
        appValidateForm($('#proposal-form'), {
            subject: 'required',
            proposal_to: 'required',
            rel_type: 'required',
            rel_id: 'required',
            date: 'required',
            email: {
                email: true,
                required: true
            },
            currency: 'required',
        });
    }
</script>
<style>
.sc-proposal-status-group{display:flex}.sc-proposal-status-group>.bootstrap-select{flex:1}.sc-proposal-status-group .input-group-btn{width:auto}.proposal-form .form-group textarea[name="content"]{min-height:260px}.proposal-form .table.items td{vertical-align:top!important}
</style>
</body>

</html>

<script id="sc-proposal-sortable-fix">
$(function(){
  var $body = $('#proposal-form table.items tbody');
  if (!$body.length) $body = $('#proposal-form .table.items tbody');
  if ($body.length && $.fn.sortable) {
    try { $body.sortable('destroy'); } catch(e) {}
    $body.sortable({
      items: 'tr.item', handle: '.dragger, .fa-grip-vertical, .fa-bars', axis: 'y',
      helper: function(e,ui){ ui.children().each(function(){ $(this).width($(this).width()); }); return ui; },
      update: function(){ if (typeof calculate_total === 'function') calculate_total(); }
    });
    }
});
</script>

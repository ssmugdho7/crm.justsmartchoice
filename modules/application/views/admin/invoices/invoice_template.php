<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="<?= (! isset($invoice) || (isset($invoice) && count($invoices_to_merge) == 0 && (! isset($invoice_from_project) && count($expenses_to_bill) == 0 || $invoice->status == Invoices_model::STATUS_CANCELLED))) ? 'hide' : ''; ?>"
    id="invoice_top_info">
    <div class="panel_s">
        <div class="panel-body tw-bg-gradient-to-l tw-from-transparent tw-to-neutral-50">
            <div class="row">
                <div id="merge" class="col-md-6">
                    <?php if (isset($invoice)) {
                        $this->load->view('admin/invoices/merge_invoice', ['invoices_to_merge' => $invoices_to_merge]);
                    } ?>
                </div>
                <!--  When invoicing from project area the expenses are not visible here because you can select to bill expenses while trying to invoice project -->
                <?php if (! isset($invoice_from_project)) { ?>
                <div id="expenses_to_bill" class="col-md-6">
                    <?php if (isset($invoice) && $invoice->status != Invoices_model::STATUS_CANCELLED) {
                        $this->load->view('admin/invoices/bill_expenses', ['expenses_to_bill' => $expenses_to_bill]);
                    } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<div class="panel_s invoice accounting-template">
    <div class="additional"></div>
    <div class="panel-body">
        <?php hooks()->do_action('before_render_invoice_template', $invoice ?? null); ?>
        <?php if (isset($invoice)) {
            echo form_hidden('merge_current_invoice', $invoice->id);
        } ?>
        <div class="row">
            <div class="col-md-6">
                <div class="f_client_id">
                    <div class="form-group select-placeholder">
                        <label for="clientid"
                            class="control-label"><?= _l('invoice_select_customer'); ?></label>
                        <select id="clientid" name="clientid" data-live-search="true" data-width="100%"
                            class="ajax-search<?= isset($invoice) && empty($invoice->clientid) ? ' customer-removed' : ''; ?>"
                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                            <?php $selected = isset($invoice) ? $invoice->clientid : ($customer_id ?? ''); ?>
                            <?php if ($selected != '') {
                                $rel_data = get_relation_data('customer', $selected);
                                $rel_val  = get_relation_values($rel_data, 'customer');
                                echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
                <?php
                                        if (! isset($invoice_from_project)) { ?>
                <div class="form-group select-placeholder projects-wrapper<?php if ((! isset($invoice)) || (isset($invoice) && ! customer_has_projects($invoice->clientid))) {
                    echo (isset($customer_id) && (! isset($project_id) || ! $project_id)) ? ' hide' : '';
                } ?>">
                    <label
                        for="project_id"><?= _l('project'); ?></label>
                    <div id="project_ajax_search_wrapper">
                        <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true"
                            data-width="100%"
                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                            <?php $project_id = isset($invoice) && $invoice->project_id ?
                                $invoice->project_id :
                                ($project_id ?? ''); ?>

                            <?php if ($project_id) {
                                echo '<option value="' . $project_id . '" selected>' . e(get_project_name_by_id($project_id)) . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
                <?php } ?>
                <div class="row">
                    <div class="col-md-12">
                        <hr class="hr-10" />
                        <a href="#" class="edit_shipping_billing_info" data-toggle="modal"
                            data-target="#billing_and_shipping_details"><i class="fa-regular fa-pen-to-square"></i></a>
                        <?php include_once APPPATH . 'views/admin/invoices/billing_and_shipping_template.php'; ?>
                    </div>
                    <div class="col-md-6">
                        <p class="bold">
                            <?= _l('invoice_bill_to'); ?>
                        </p>
                        <address>
                            <span class="billing_street">
                                <?php $billing_street = (isset($invoice) ? $invoice->billing_street : '--'); ?>
                                <?php $billing_street = ($billing_street == '' ? '--' : $billing_street); ?>
                                <?= process_text_content_for_display($billing_street); ?></span><br>
                            <span class="billing_city">
                                <?php $billing_city = (isset($invoice) ? $invoice->billing_city : '--'); ?>
                                <?php $billing_city = ($billing_city == '' ? '--' : $billing_city); ?>
                                <?= e($billing_city); ?></span>,
                            <span class="billing_state">
                                <?php $billing_state = (isset($invoice) ? $invoice->billing_state : '--'); ?>
                                <?php $billing_state = ($billing_state == '' ? '--' : $billing_state); ?>
                                <?= e($billing_state); ?></span>
                            <br />
                            <span class="billing_country">
                                <?php $billing_country = (isset($invoice) ? get_country_short_name($invoice->billing_country) : '--'); ?>
                                <?php $billing_country = ($billing_country == '' ? '--' : $billing_country); ?>
                                <?= e($billing_country); ?></span>,
                            <span class="billing_zip">
                                <?php $billing_zip = (isset($invoice) ? $invoice->billing_zip : '--'); ?>
                                <?php $billing_zip = ($billing_zip == '' ? '--' : $billing_zip); ?>
                                <?= e($billing_zip); ?></span>
                        </address>
                    </div>
                    <div class="col-md-6">
                        <p class="bold">
                            <?= _l('ship_to'); ?>
                        </p>
                        <address>
                            <span class="shipping_street">
                                <?php $shipping_street = (isset($invoice) ? $invoice->shipping_street : '--'); ?>
                                <?php $shipping_street = ($shipping_street == '' ? '--' : $shipping_street); ?>
                                <?= process_text_content_for_display($shipping_street); ?></span><br>
                            <span class="shipping_city">
                                <?php $shipping_city = (isset($invoice) ? $invoice->shipping_city : '--'); ?>
                                <?php $shipping_city = ($shipping_city == '' ? '--' : $shipping_city); ?>
                                <?= e($shipping_city); ?></span>,
                            <span class="shipping_state">
                                <?php $shipping_state = (isset($invoice) ? $invoice->shipping_state : '--'); ?>
                                <?php $shipping_state = ($shipping_state == '' ? '--' : $shipping_state); ?>
                                <?= e($shipping_state); ?></span>
                            <br />
                            <span class="shipping_country">
                                <?php $shipping_country = (isset($invoice) ? get_country_short_name($invoice->shipping_country) : '--'); ?>
                                <?php $shipping_country = ($shipping_country == '' ? '--' : $shipping_country); ?>
                                <?= e($shipping_country); ?></span>,
                            <span class="shipping_zip">
                                <?php $shipping_zip = (isset($invoice) ? $invoice->shipping_zip : '--'); ?>
                                <?php $shipping_zip = ($shipping_zip == '' ? '--' : $shipping_zip); ?>
                                <?= e($shipping_zip); ?></span>
                        </address>
                    </div>
                </div>
                <?php $next_invoice_number = get_option('next_invoice_number'); ?>
                <?php $format              = get_option('invoice_number_format'); ?>
                <?php
if (isset($invoice)) {
    $format = $invoice->number_format;
}

$prefix = get_option('invoice_prefix');

if ($format == 1) {
    $__number = $next_invoice_number;
    if (isset($invoice)) {
        $__number = $invoice->number;
        $prefix   = '<span id="prefix">' . $invoice->prefix . '</span>';
    }
} elseif ($format == 2) {
    if (isset($invoice)) {
        $__number = $invoice->number;
        $prefix   = $invoice->prefix;
        $prefix   = '<span id="prefix">' . $prefix . '</span><span id="prefix_year">' . date('Y', strtotime($invoice->date)) . '</span>/';
    } else {
        $__number = $next_invoice_number;
        $prefix   = $prefix . '<span id="prefix_year">' . date('Y') . '</span>/';
    }
} elseif ($format == 3) {
    if (isset($invoice)) {
        $yy       = date('y', strtotime($invoice->date));
        $__number = $invoice->number;
        $prefix   = '<span id="prefix">' . $invoice->prefix . '</span>';
    } else {
        $yy       = date('y');
        $__number = $next_invoice_number;
    }
} elseif ($format == 4) {
    if (isset($invoice)) {
        $yyyy     = date('Y', strtotime($invoice->date));
        $mm       = date('m', strtotime($invoice->date));
        $__number = $invoice->number;
        $prefix   = '<span id="prefix">' . $invoice->prefix . '</span>';
    } else {
        $yyyy     = date('Y');
        $mm       = date('m');
        $__number = $next_invoice_number;
    }
}

$_is_draft            = (isset($invoice) && $invoice->status == Invoices_model::STATUS_DRAFT) ? true : false;
$_invoice_number      = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
$isedit               = isset($invoice) ? 'true' : 'false';
$data_original_number = isset($invoice) ? $invoice->number : 'false';

?>
                <div class="form-group">
                    <label for="number">
                        <?= _l('invoice_add_edit_number'); ?>
                        <i class="fa-regular fa-circle-question" data-toggle="tooltip"
                            data-title="<?= _l('invoice_number_not_applied_on_draft') ?>"
                            data-placement="top"></i>
                    </label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <?php if (isset($invoice)) { ?>
                            <a href="#" onclick="return false;" data-toggle="popover"
                                data-container='._transaction_form' data-html="true"
                                data-content="<label class='control-label'><?= _l('settings_sales_invoice_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?= e($invoice->prefix); ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?= admin_url('invoices/update_number_settings/' . $invoice->id); ?>' class='btn btn-primary btn-block mtop15'><?= _l('submit'); ?></button>">
                                <i class="fa fa-cog"></i>
                            </a>
                            <?php } ?>
                            <?= $prefix; ?>
                        </span>
                        <input type="text" name="number" class="form-control"
                            value="<?= ($_is_draft) ? 'DRAFT' : $_invoice_number; ?>"
                            data-isedit="<?= e($isedit); ?>"
                            data-original-number="<?= e($data_original_number); ?>"
                            <?= ($_is_draft) ? 'disabled' : '' ?>>
                        <?php if ($format == 3) { ?>
                        <span class="input-group-addon">
                            <span id="prefix_year"
                                class="format-n-yy"><?= e($yy); ?></span>
                        </span>
                        <?php } elseif ($format == 4) { ?>
                        <span class="input-group-addon">
                            <span id="prefix_month"
                                class="format-mm-yyyy"><?= e($mm); ?></span>
                            /
                            <span id="prefix_year"
                                class="format-mm-yyyy"><?= e($yyyy); ?></span>
                        </span>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?php $value      = isset($invoice) ? _d($invoice->date) : _d(date('Y-m-d')); ?>
                        <?php $date_attrs = (isset($invoice) && $invoice->recurring > 0 && $invoice->last_recurring_date != null) ? ['disabled' => true] : []; ?>
                        <?= render_date_input('date', 'invoice_add_edit_date', $value, $date_attrs); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $value = isset($invoice) ? _d($invoice->duedate) : (get_option('invoice_due_after') != 0 ? _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY'))) : ''); ?>
                        <?= render_date_input('duedate', 'invoice_add_edit_duedate', $value); ?>
                    </div>
                </div>
                <?php if (is_invoices_overdue_reminders_enabled()) { ?>
                <div class="form-group">
                    <div class="checkbox checkbox-danger">
                        <input type="checkbox"
                            <?= isset($invoice) && $invoice->cancel_overdue_reminders == 1 ? 'checked' : ''; ?>
                        id="cancel_overdue_reminders" name="cancel_overdue_reminders">
                        <label
                            for="cancel_overdue_reminders"><?= _l('cancel_overdue_reminders_invoice') ?></label>
                    </div>
                </div>
                <?php } ?>
                <?php $rel_id = (isset($invoice) ? $invoice->id : false); ?>
                <?php if (isset($custom_fields_rel_transfer)) {
                    $rel_id = $custom_fields_rel_transfer;
                } ?>

<!-- SMART CHOICE PAYMENT SCHEDULE INVOICE -->
<?php if (function_exists('module_active') && module_active('smart_choice_payment_schedule')) {
    $scpsDocumentType = 'invoice';
    $scpsDocumentId = isset($invoice) ? (int) ($invoice->id) : 0;
    include module_dir_path('smart_choice_payment_schedule', 'views/partials/payment_fields.php');
} ?>
                <?= render_custom_fields('invoice', $rel_id); ?>
            </div>
            <div class="col-md-6">
                <div class="tw-ml-3">
                    <div class="form-group">
                        <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>
                            <?= _l('tags'); ?></label>
                        <input type="text" class="tagsinput" id="tags" name="tags"
                            value="<?= isset($invoice) ? prep_tags_input(get_tags_in($invoice->id, 'invoice')) : ''; ?>"
                            data-role="tagsinput">
                    </div>
                    <div
                        class="form-group mbot15<?= count($payment_modes) > 0 ? ' select-placeholder' : ''; ?>">
                        <label for="allowed_payment_modes"
                            class="control-label"><?= _l('invoice_add_edit_allowed_payment_modes'); ?></label>
                        <br />
                        <?php if (count($payment_modes) > 0) { ?>
                        <select class="selectpicker"
                            data-toggle="<?= $this->input->get('allowed_payment_modes'); ?>"
                            name="allowed_payment_modes[]" data-actions-box="true" multiple="true" data-width="100%"
                            data-title="<?= _l('dropdown_non_selected_tex'); ?>">
                            <?php foreach ($payment_modes as $mode) {
                                $selected = '';
                                if (isset($invoice)) {
                                    if ($invoice->allowed_payment_modes) {
                                        $inv_modes = unserialize($invoice->allowed_payment_modes);
                                        if (is_array($inv_modes)) {
                                            foreach ($inv_modes as $_allowed_payment_mode) {
                                                if ($_allowed_payment_mode == $mode['id']) {
                                                    $selected = ' selected';
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    if ($mode['selected_by_default'] == 1) {
                                        $selected = ' selected';
                                    }
                                } ?>
                            <option
                                value="<?= e($mode['id']); ?>"
                                <?= e($selected); ?>>
                                <?= e($mode['name']); ?>
                            </option>
                            <?php
                            } ?>
                        </select>
                        <?php } else { ?>
                        <p class="tw-text-neutral-500">
                            <?= _l('invoice_add_edit_no_payment_modes_found'); ?>
                        </p>
                        <a class="btn btn-primary btn-sm"
                            href="<?= admin_url('paymentmodes'); ?>">
                            <?= _l('new_payment_mode'); ?>
                        </a>
                        <?php } ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?php
                                     $currency_attr = ['disabled' => true, 'data-show-subtext' => true];
$currency_attr                                      = apply_filters_deprecated('invoice_currency_disabled', [$currency_attr], '2.3.0', 'invoice_currency_attributes');

foreach ($currencies as $currency) {
    if ($currency['isdefault'] == 1) {
        $currency_attr['data-base'] = $currency['id'];
    }
    if (isset($invoice)) {
        if ($currency['id'] == $invoice->currency) {
            $selected = $currency['id'];
        }
    } else {
        if ($currency['isdefault'] == 1) {
            $selected = $currency['id'];
        }
    }
}
$currency_attr = hooks()->apply_filters('invoice_currency_attributes', $currency_attr);
?>
                            <?= render_select('currency', $currencies, ['id', 'name', 'symbol'], 'invoice_add_edit_currency', $selected, $currency_attr); ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                $selected = isset($invoice) ? $invoice->sale_agent : (get_option('automatically_set_logged_in_staff_sales_agent') == '1' ? get_staff_user_id() : '');

foreach ($staff as $member) {
    if (isset($invoice) && $invoice->sale_agent == $member['staffid']) {
        $selected = $member['staffid'];
        break;
    }
}

echo render_select('sale_agent', $staff, ['staffid', ['firstname', 'lastname']], 'sale_agent_string', $selected);
?>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group select-placeholder" <?php if (isset($invoice) && ! empty($invoice->is_recurring_from)) { ?>
                                data-toggle="tooltip"
                                data-title="<?= _l('create_recurring_from_child_error_message', [_l('invoice_lowercase'), _l('invoice_lowercase'), _l('invoice_lowercase')]); ?>"
                                <?php } ?>>
                                <label for="recurring" class="control-label">
                                    <?= _l('invoice_add_edit_recurring'); ?>
                                </label>
                                <select class="selectpicker" data-width="100%" name="recurring"
                                    data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>"
                                    <?php
                        // The problem is that this invoice was generated from previous recurring invoice
                        // Then this new invoice you set it as recurring but the next invoice date was still taken from the previous invoice.
                        if (isset($invoice) && ! empty($invoice->is_recurring_from)) {
                            echo 'disabled';
                        } ?>>
                                    <?php for ($i = 0; $i <= 12; $i++) { ?>
                                    <?php
                              $selected = '';
                                        if (isset($invoice)) {
                                            if ($invoice->custom_recurring == 0) {
                                                if ($invoice->recurring == $i) {
                                                    $selected = 'selected';
                                                }
                                            }
                                        }
                                        if ($i == 0) {
                                            $reccuring_string = _l('invoice_add_edit_recurring_no');
                                        } elseif ($i == 1) {
                                            $reccuring_string = _l('invoice_add_edit_recurring_month', $i);
                                        } else {
                                            $reccuring_string = _l('invoice_add_edit_recurring_months', $i);
                                        }
                                        ?>
                                    <option value="<?= e($i); ?>" <?= e($selected); ?>>
                                        <?= e($reccuring_string); ?>
                                    </option>
                                    <?php } ?>
                                    <option value="custom" <?= isset($invoice) && $invoice->recurring != 0 && $invoice->custom_recurring == 1 ? 'selected' : ''; ?>>
                                        <?= _l('recurring_custom'); ?>
                                    </option>
                                </select>
                            </div>
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
                                    <option value="before_tax" <?= isset($invoice) && $invoice->discount_type == 'before_tax' ? 'selected' : ''; ?>>
                                        <?= _l('discount_type_before_tax'); ?>
                                    </option>
                                    <option value="after_tax" <?= isset($invoice) && $invoice->discount_type == 'after_tax' ? 'selected' : ''; ?>>
                                        <?= _l('discount_type_after_tax'); ?>
                                    </option>

                                </select>
                            </div>
                        </div>
<div
                            class="recurring_custom<?= (isset($invoice) && $invoice->custom_recurring != 1) || (! isset($invoice)) ? ' hide' : ''; ?>">
                            <div class="col-md-6">
                                <?php $value = (isset($invoice) && $invoice->custom_recurring == 1 ? $invoice->recurring : 1); ?>
                                <?= render_input('repeat_every_custom', '', $value, 'number', ['min' => 1]); ?>
                            </div>
                            <div class="col-md-6">
                                <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker"
                                    data-width="100%"
                                    data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                    <?php $selectedType = isset($invoice) && $invoice->custom_recurring == 1 ? $invoice->recurring_type : ''; ?>
                                    <option value="day" <?= $selectedType == 'day' ? 'selected' : ''; ?>><?= _l('invoice_recurring_days'); ?>
                                    </option>
                                    <option value="week" <?= $selectedType == 'week' ? 'selected' : ''; ?>><?= _l('invoice_recurring_weeks'); ?>
                                    </option>
                                    <option value="month" <?= $selectedType == 'month' ? 'selected' : ''; ?>><?= _l('invoice_recurring_months'); ?>
                                    </option>
                                    <option value="year" <?= $selectedType == 'year' ? 'selected' : ''; ?>><?= _l('invoice_recurring_years'); ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div id="cycles_wrapper"
                            class="<?= ! isset($invoice) || (isset($invoice) && $invoice->recurring == 0) ? 'hide' : ''; ?>">
                            <div class="col-md-12">
                                <?php $value = (isset($invoice) ? $invoice->cycles : 0); ?>
                                <div class="form-group recurring-cycles">
                                    <label
                                        for="cycles"><?= _l('recurring_total_cycles'); ?>
                                        <?php if (isset($invoice) && $invoice->total_cycles > 0) {
                                            echo '<small>' . e(_l('cycles_passed', $invoice->total_cycles)) . '</small>';
                                        } ?>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"
                                            <?= $value == 0 ? 'disabled' : ''; ?>
                                        name="cycles" id="cycles"
                                        value="<?= e($value); ?>"
                                        <?php if (isset($invoice) && $invoice->total_cycles > 0) {
                                            echo 'min="' . e($invoice->total_cycles) . '"';
                                        } ?>>
                                        <div class="input-group-addon">
                                            <div class="checkbox">
                                                <input type="checkbox"
                                                    <?= $value == 0 ? 'checked' : ''; ?>
                                                id="unlimited_cycles">
                                                <label
                                                    for="unlimited_cycles"><?= _l('cycles_infinity'); ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $value = (isset($invoice) ? $invoice->adminnote : ''); ?>
                    <?= render_textarea('adminnote', 'invoice_add_edit_admin_note', $value); ?>

                </div>
            </div>
        </div>
    </div>

    <hr class="hr-panel-separator" />

    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <?php $this->load->view('admin/invoice_items/item_select'); ?>
            </div>
            <?php if (! isset($invoice_from_project) && isset($billable_tasks)) { ?>
            <div class="col-md-3">
                <div class="form-group select-placeholder input-group-select form-group-select-task_select popover-250">
                    <div class="input-group input-group-select">
                        <select name="task_select" data-live-search="true" id="task_select"
                            class="selectpicker no-margin _select_input_group" data-width="100%"
                            data-none-selected-text="<?= _l('bill_tasks'); ?>">
                            <option value=""></option>
                            <?php foreach ($billable_tasks as $task_billable) { ?>
                            <option
                                value="<?= e($task_billable['id']); ?>"
                                <?php if ($task_billable['started_timers'] == true) { ?>disabled
                                class="text-danger"
                                data-subtext="<?= _l('invoice_task_billable_timers_found'); ?>"
                                <?php } else {
                                    $task_rel_data  = get_relation_data($task_billable['rel_type'], $task_billable['rel_id']);
                                    $task_rel_value = get_relation_values($task_rel_data, $task_billable['rel_type']); ?>
                                data-subtext="<?= $task_billable['rel_type'] == 'project' ? '' : $task_rel_value['name']; ?>"
                                <?php
                                } ?>><?= e($task_billable['name']); ?>
                            </option>
                            <?php } ?>
                        </select>
                        <div class="input-group-addon input-group-addon-bill-tasks-help">
                            <?php
                    if (isset($invoice) && ! empty($invoice->project_id)) {
                        $help_text = _l('showing_billable_tasks_from_project') . ' ' . get_project_name_by_id($invoice->project_id);
                    } else {
                        $help_text = _l('invoice_task_item_project_tasks_not_included');
                    }
                echo '<span class="pointer popover-invoker" data-container=".form-group-select-task_select"
                      data-trigger="click" data-placement="top" data-toggle="popover" data-content="' . $help_text . '">
                      <i class="fa-regular fa-circle-question"></i></span>'; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            } ?>
            <div
                class="col-md-<?= ! isset($invoice_from_project) ? '5' : '8'; ?> text-right show_quantity_as_wrapper">
                <div class="mtop10">
                    <span><?= _l('show_quantity_as'); ?>
                    </span>
                    <div class="radio radio-primary radio-inline">
                        <input type="radio" value="1" id="sq_1" name="show_quantity_as"
                            data-text="<?= _l('invoice_table_quantity_heading'); ?>"
                            <?= (isset($invoice) && $invoice->show_quantity_as == 1) || (! isset($hours_quantity) && ! isset($qty_hrs_quantity)) ? 'checked' : ''; ?>>
                        <label
                            for="sq_1"><?= _l('quantity_as_qty'); ?></label>
                    </div>

                    <div class="radio radio-primary radio-inline">
                        <input type="radio" value="2" id="sq_2" name="show_quantity_as"
                            data-text="<?= _l('invoice_table_hours_heading'); ?>"
                            <?= (isset($invoice) && $invoice->show_quantity_as == 2) || isset($hours_quantity) ? 'checked' : ''; ?>>
                        <label
                            for="sq_2"><?= _l('quantity_as_hours'); ?></label>
                    </div>

                    <div class="radio radio-primary radio-inline">
                        <input type="radio" value="3" id="sq_3" name="show_quantity_as"
                            data-text="<?= _l('invoice_table_quantity_heading'); ?>/<?= _l('invoice_table_hours_heading'); ?>"
                            <?= (isset($invoice) && $invoice->show_quantity_as == 3) || isset($qty_hrs_quantity) ? 'checked' : ''; ?>>
                        <label
                            for="sq_3"><?= _l('invoice_table_quantity_heading'); ?>/<?= _l('invoice_table_hours_heading'); ?></label>
                    </div>

                </div>
            </div>
        </div>
        <?php if (isset($invoice_from_project)) {
            echo '<hr class="no-mtop" />';
        } ?>
        <div class="table-responsive s_table">
            <table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
                <thead>
<tr id="discount_area">
                        <td>
                            <div class="row">
                                <div class="col-md-7">
                                    <span class="bold tw-text-neutral-700">
                                        <?= _l('invoice_discount'); ?>
                                    </span>
                                </div>
                                <div class="col-md-5">
                                    <div class="input-group" id="discount-total">

                                        <input type="number"
                                            value="<?= isset($invoice) ? $invoice->discount_percent : 0; ?>"
                                            class="form-control pull-left input-discount-percent<?= isset($invoice) && ! is_sale_discount($invoice, 'percent') && is_sale_discount_applied($invoice) ? ' hide' : ''; ?>"
                                            min="0" max="100" name="discount_percent">

                                        <input type="number" data-toggle="tooltip"
                                            data-title="<?= _l('numbers_not_formatted_while_editing'); ?>"
                                            value="<?= isset($invoice) ? $invoice->discount_total : 0; ?>"
                                            class="form-control pull-left input-discount-fixed<?= ! isset($invoice) || (isset($invoice) && ! is_sale_discount($invoice, 'fixed')) ? ' hide' : ''; ?>"
                                            min="0" name="discount_total">

                                        <div class="input-group-addon">
                                            <div class="dropdown">
                                                <a class="dropdown-toggle" href="#" id="dropdown_menu_tax_total_type"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                    <span class="discount-total-type-selected">
                                                        <?php if (! isset($invoice) || isset($invoice) && (is_sale_discount($invoice, 'percent') || ! is_sale_discount_applied($invoice))) {
                                                            echo '%';
                                                        } else {
                                                            echo _l('discount_fixed_amount');
                                                        } ?>
                                                    </span>
                                                    <span class="caret"></span>
                                                </a>
                                                <ul class="dropdown-menu" id="discount-total-type-dropdown"
                                                    aria-labelledby="dropdown_menu_tax_total_type">
                                                    <li>
                                                        <a href="#"
                                                            class="discount-total-type discount-type-percent<?= (! isset($invoice) || (isset($invoice) && is_sale_discount($invoice, 'percent')) || (isset($invoice) && ! is_sale_discount_applied($invoice))) ? ' selected' : ''; ?>">%</a>
                                                    </li>
                                                    <li>
                                                        <a href="#"
                                                            class="discount-total-type discount-type-fixed<?= (isset($invoice) && is_sale_discount($invoice, 'fixed')) ? ' selected' : ''; ?>">
                                                            <?= _l('discount_fixed_amount'); ?>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="discount-total"></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="row">
                                <div class="col-md-7">
                                    <span
                                        class="bold tw-text-neutral-700"><?= _l('invoice_adjustment'); ?></span>
                                </div>
                                <div class="col-md-5">
                                    <input type="number" data-toggle="tooltip"
                                        data-title="<?= _l('numbers_not_formatted_while_editing'); ?>"
                                        value="<?= isset($invoice) ? $invoice->adjustment : 0; ?>"
                                        class="form-control pull-left" name="adjustment">
                                </div>
                            </div>
                        </td>
                        <td class="adjustment"></td>
                    </tr>
                    <tr>
                        <td><span
                                class="bold tw-text-neutral-700"><?= _l('invoice_total'); ?>
                                :</span>
                        </td>
                        <td class="total">
                        </td>
                    </tr>
                    <?php hooks()->do_action('after_admin_invoice_form_total_field', $invoice ?? null); ?>
                </tbody>
            </table>
        </div>
        <div id="removed-items"></div>
        <div id="billed-tasks"></div>
        <div id="billed-expenses"></div>
        <?= form_hidden('task_id'); ?>
        <?= form_hidden('expense_id'); ?>

    </div>

    <hr class="hr-panel-separator" />
</div>

    <div class="panel-body">
        <?php $value = (isset($invoice) ? $invoice->clientnote : get_option('predefined_clientnote_invoice')); ?>
        <?= render_textarea('clientnote', 'invoice_add_edit_client_note', $value); ?>
        <?php $value = (isset($invoice) ? $invoice->terms : get_option('predefined_terms_invoice')); ?>
        <?= render_textarea('terms', 'terms_and_conditions', $value, [], [], 'mtop15'); ?>
    </div>

    <?php hooks()->do_action('after_render_invoice_template', $invoice ?? false); ?>
</div>

<div class="btn-bottom-pusher"></div>
<div class="btn-bottom-toolbar text-right">
    <?php if (! isset($invoice)) { ?>
    <button class="btn-tr btn btn-default mright5 text-right invoice-form-submit save-as-draft transaction-submit">
        <?= _l('save_as_draft'); ?>
    </button>
    <?php } ?>
    <div class="btn-group dropup">
        <button type="button"
            class="btn-tr btn btn-primary invoice-form-submit transaction-submit"><?= _l('submit'); ?></button>
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right width200">
            <li>
                <a href="#" class="invoice-form-submit save-and-send transaction-submit">
                    <?= _l('save_and_send'); ?>
                </a>
            </li>
            <?php if (! isset($invoice)) { ?>
            <li>
                <a href="#" class="invoice-form-submit save-and-send-later transaction-submit">
                    <?= _l('save_and_send_later'); ?>
                </a>
            </li>
            <li>
                <a href="#" class="invoice-form-submit save-and-record-payment transaction-submit">
                    <?= _l('save_and_record_payment'); ?>
                </a>
            </li>
            <?php } ?>
        </ul>
    </div>
</div>


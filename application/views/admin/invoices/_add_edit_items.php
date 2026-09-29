<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel-body">
    <div class="row">
        <div class="col-md-4">
            <?php $this->load->view('admin/invoice_items/item_select'); ?>
        </div>
        <div class="col-md-8 text-right show_quantity_as_wrapper">
            <div class="mtop10">
                <span><?= _l('show_quantity_as'); ?></span>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" value="1" id="1" name="show_quantity_as"
                        data-text="<?= _l('invoice_table_quantity_heading'); ?>"
                        <?= isset($invoice) && $invoice->show_quantity_as == 1 ? 'checked' : 'checked'; ?>>
                    <label
                        for="1"><?= _l('quantity_as_qty'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" value="2" id="2" name="show_quantity_as"
                        data-text="<?= _l('invoice_table_hours_heading'); ?>"
                        <?= isset($invoice) && $invoice->show_quantity_as == 2 ? 'checked' : ''; ?>>
                    <label
                        for="2"><?= _l('quantity_as_hours'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="3" value="3" name="show_quantity_as"
                        data-text="<?= _l('invoice_table_quantity_heading'); ?>/<?= _l('invoice_table_hours_heading'); ?>"
                        <?= isset($invoice) && $invoice->show_quantity_as == 3 ? 'checked' : ''; ?>>
                    <label for="3">
                        <?= _l('invoice_table_quantity_heading'); ?>/<?= _l('invoice_table_hours_heading'); ?>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive s_table">
        <table class="table invoice-items-table items table-main-invoice-edit has-calculations no-mtop">
            <thead>
                <tr>
                    <th class="drag-column"></th>
                    <th class="description" align="left"><i class="fa-solid fa-circle-exclamation tw-mr-1" aria-hidden="true"
                            data-toggle="tooltip"
                            data-title="<?= _l('item_description_new_lines_notice'); ?>"></i>
                        <?= _l('invoice_table_item_heading'); ?>
                    </th>
                    <th class="long_description" align="left">
                        <?= _l('invoice_table_item_description'); ?>
                    </th>
                    <?php
                  $custom_fields = get_custom_fields('items');

foreach ($custom_fields as $cf) {
    echo '<th width="15%" align="left" class="custom_field">' . e($cf['name']) . '</th>';
}

$qty_heading = _l('invoice_table_quantity_heading');
if (isset($invoice) && $invoice->show_quantity_as == 2) {
    $qty_heading = _l('invoice_table_hours_heading');
} elseif (isset($invoice) && $invoice->show_quantity_as == 3) {
    $qty_heading = _l('invoice_table_quantity_heading') . '/' . _l('invoice_table_hours_heading');
}
?>
                    <th width="10%" class="qty" align="right">
                        <?= e($qty_heading); ?>
                    </th>
                    <th class="rate" align="right">
                        <?= _l('invoice_table_rate_heading'); ?>
                    </th>
                    <th class="taxrate" align="right">
                        <?= _l('invoice_table_tax_heading'); ?>
                    </th>
                    <th class="amount" align="right">
                        <?= _l('invoice_table_amount_heading'); ?>
                    </th>
                    <th align="center"><i class="fa fa-cog"></i></th>
                </tr>
            </thead>
            <tbody>
                <tr class="main">
                    <td class="dragger"></td>
                    <td class="description">
                        <textarea name="description" rows="4" class="form-control"
                            placeholder="<?= _l('item_description_placeholder'); ?>"></textarea>
                        <div class="tw-mt-1.5">
                            <div class="checkbox checkbox-info">
                                <input value="1" id="main-optional" type="checkbox" />
                                <label
                                    for="main-optional"><?= _l('item_is_optional'); ?></label>
                            </div>
                            <div class="checkbox" style="display: none;">
                                <input value="1" id="main-optional-choosen" type="checkbox" />
                                <label
                                    for="main-optional-choosen"><?= _l('item_is_selected'); ?></label>
                            </div>
                        </div>
                    </td>
                    <td class="long_description">
                        <textarea name="long_description" rows="4" class="form-control"
                            placeholder="<?= _l('item_long_description_placeholder'); ?>"></textarea>
                    </td>
                    <?= render_custom_fields_items_table_add_edit_preview(); ?>
                    <td class="qty">
                        <div class="sc-qty-unit-inline">
                        <input type="text" inputmode="decimal" data-quantity name="quantity" value="1" class="form-control"
                            placeholder="<?= _l('item_quantity_placeholder'); ?>">
                        <input type="text"
                            placeholder="<?= _l('unit'); ?>"
                            data-toggle="tooltip" 612 data-title="e.q kg, lots, packs" name="unit" list="sc-unit-options"
                            class="form-control text-right sc-unit-input">
                    
                        </div></td>
                    <td class="rate">
                        <input type="text" inputmode="decimal" name="rate" class="form-control sc-money-input"
                            placeholder="<?= _l('item_rate_placeholder'); ?>">
                    </td>
                    <td class="taxrate">
                        <?php
   $default_tax = unserialize(get_option('default_tax'));
$select         = '<select class="selectpicker display-block tax main-tax" data-width="100%" name="taxname" multiple data-none-selected-text="' . _l('no_tax') . '">';

foreach ($taxes as $tax) {
    $selected = '';
    if (is_array($default_tax)) {
        if (in_array($tax['name'] . '|' . $tax['taxrate'], $default_tax)) {
            $selected = ' selected ';
        }
    }
    $select .= '<option value="' . $tax['name'] . '|' . $tax['taxrate'] . '"' . $selected . 'data-taxrate="' . $tax['taxrate'] . '" data-taxname="' . $tax['name'] . '" data-subtext="' . $tax['name'] . '">' . $tax['taxrate'] . '%</option>';
}
$select .= '</select>';
echo $select;
?>
                    </td>
                    <td class="amount"></td>
                    <td class="item-actions">
                        <?php
$new_item = 'undefined';
if (isset($invoice)) {
    $new_item = true;
}
?>
                        <button type="button"
                            onclick="add_item_to_table('undefined','undefined',<?= e($new_item); ?>); return false;"
                            class="btn pull-right btn-primary"><i class="fa fa-check"></i></button>
                    </td>
                </tr>
                <?php if (isset($invoice) || isset($add_items)) {
                    $i               = 1;
                    $items_indicator = 'newitems';
                    if (isset($invoice)) {
                        $add_items       = $invoice->items;
                        $items_indicator = 'items';
                    }

                    foreach ($add_items as $item) {
                        $manual    = false;
                        $table_row = '<tr class="sortable item">';
                        $table_row .= '<td class="dragger">';
                        if ($item['qty'] == '' || $item['qty'] == 0) {
                            $item['qty'] = 1;
                        }
                        if (! isset($is_proposal)) {
                            $invoice_item_taxes = get_invoice_item_taxes($item['id']);
                        } else {
                            $invoice_item_taxes = get_proposal_item_taxes($item['id']);
                        }
                        if ($item['id'] == 0) {
                            $invoice_item_taxes = $item['taxname'];
                            $manual              = true;
                        }
                        $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][itemid]', $item['id']);
                        $amount = $item['rate'] * $item['qty'];
                        $amount = app_format_number($amount);
                        // order input
                        $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]">';
                        $table_row .= '</td>';
                        $table_row .= '<td class="bold description"><textarea name="' . $items_indicator . '[' . $i . '][description]" class="form-control" rows="5">' . clear_textarea_breaks($item['description']) . '</textarea>';

                        $table_row .= '<div class="tw-mt-1.5">';
                        $table_row .= '<div class="checkbox checkbox-info">';
                        $table_row .= '<input class="optional-item-checkbox" ' . ($item['is_optional'] ? ' checked' : '') . ' value="1" id="' . $i . '-optional" type="checkbox" name="' . $items_indicator . '[' . $i . '][is_optional]" data-index="' . $i . '" />';

                        $table_row .= '<label for="' . $i . '-optional">' . _l('item_is_optional') . '</label>';
                        $table_row .= '</div>';

                        $table_row .= '<div class="checkbox" style="' . (! ($item['is_optional'] ?? false) ? 'display: none; ' : '') . '">';
                        $table_row .= '<input class="optional-choose-item-checkbox" ' . ($item['is_selected'] ? ' checked' : '') . ' value="1" id="' . $i . '-optional-choosen" type="checkbox" name="' . $items_indicator . '[' . $i . '][is_selected]" />';

                        $table_row .= '<label for="' . $i . '-optional-choosen">' . _l('item_is_selected') . '</label>';
                        $table_row .= '</div>';
                        $table_row .= '</div>';

                        $table_row .= '</td>';
                        $table_row .= '<td class="long_description"><textarea name="' . $items_indicator . '[' . $i . '][long_description]" class="form-control" rows="5">' . clear_textarea_breaks($item['long_description']) . '</textarea></td>';
                        $table_row .= render_custom_fields_items_table_in($item, $items_indicator . '[' . $i . ']');
                        $table_row .= '<td class="qty"><div class="sc-qty-unit-inline"><input type="text" inputmode="decimal" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="' . $items_indicator . '[' . $i . '][qty]" value="' . $item['qty'] . '" class="form-control">';
                        $unit_placeholder = '';
                        if (! $item['unit']) {
                            $unit_placeholder = _l('unit');
                            $item['unit']     = '';
                        }
                        $table_row .= '<input type="text" placeholder="' . $unit_placeholder . '" name="' . $items_indicator . '[' . $i . '][unit]" class="form-control text-right sc-unit-input" list="sc-unit-options" value="' . $item['unit'] . '">';
                        $table_row .= '</div>';
                        $table_row .= '</td>';
                        $table_row .= '<td class="rate"><input type="text" inputmode="decimal" data-toggle="tooltip" title="' . _l('numbers_not_formatted_while_editing') . '" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][rate]" value="' . $item['rate'] . '" class="form-control"></td>';
                        $table_row .= '<td class="taxrate">' . $this->misc_model->get_taxes_dropdown_template('' . $items_indicator . '[' . $i . '][taxname][]', $invoice_item_taxes, (isset($is_proposal) ? 'proposal' : 'invoice'), $item['id'], true, $manual) . '</td>';
                        $table_row .= '<td class="amount" align="right">' . $amount . '</td>';
                        $table_row .= '<td><a href="#" class="btn btn-danger pull-left !tw-px-3" onclick="delete_item(this,' . $item['id'] . '); return false;"><i class="fa fa-times"></i></a></td>';
                        $table_row .= '</tr>';
                        echo $table_row;
                        $i++;
                    }
                }
?>
            </tbody>
        </table>
    </div>
    <div class="col-md-8 col-md-offset-4">
        <table class="table text-right">
            <tbody>
                <tr id="subtotal">
                    <td><span
                            class="bold tw-text-neutral-700"><?= _l('estimate_subtotal'); ?>
                            :</span>
                    </td>
                    <td class="subtotal">
                    </td>
                </tr>
                <tr id="discount_area">
                    <td>
                        <div class="row">
                            <div class="col-md-7">
                                <span
                                    class="bold tw-text-neutral-700"><?= _l('estimate_discount'); ?></span>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group" id="discount-total">

                                    <input type="number"
                                        value="<?= isset($invoice) ? $invoice->discount_percent : 0; ?>"
                                        class="form-control pull-left input-discount-percent<?php if (isset($invoice) && ! is_sale_discount($invoice, 'percent') && is_sale_discount_applied($invoice)) {
                                            echo ' hide';
                                        } ?>" min="0" max="100" name="discount_percent">

                                    <input type="text" inputmode="decimal" data-toggle="tooltip"
                                        data-title="<?= _l('numbers_not_formatted_while_editing'); ?>"
                                        value="<?= isset($invoice) ? $invoice->discount_total : 0; ?>"
                                        class="form-control pull-left input-discount-fixed<?php if (! isset($invoice) || (isset($invoice) && ! is_sale_discount($invoice, 'fixed'))) {
                                            echo ' hide';
                                        } ?>" min="0" name="discount_total">

                                    <div class="input-group-addon">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" href="#" id="dropdown_menu_tax_total_type"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <span class="discount-total-type-selected">
                                                    <?php if (! isset($invoice) || isset($invoice) && (is_sale_discount($invoice, 'percent') || ! is_sale_discount_applied($invoice))) {
                                                        echo '%';
                                                    } else {
                                                        echo _l('discount_fixed_amount');
                                                    }
?>
                                                </span>
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu" id="discount-total-type-dropdown"
                                                aria-labelledby="dropdown_menu_tax_total_type">
                                                <li>
                                                    <a href="#" class="discount-total-type discount-type-percent<?php if (! isset($invoice) || (isset($invoice) && is_sale_discount($invoice, 'percent')) || (isset($invoice) && ! is_sale_discount_applied($invoice))) {
                                                        echo ' selected';
                                                    } ?>">%</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="discount-total-type discount-type-fixed<?php if (isset($invoice) && is_sale_discount($invoice, 'fixed')) {
                                                        echo ' selected';
                                                    } ?>">
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
                                    class="bold tw-text-neutral-700"><?= _l('estimate_adjustment'); ?></span>
                            </div>
                            <div class="col-md-5">
                                <input type="text" inputmode="decimal" data-toggle="tooltip"
                                    data-title="<?= _l('numbers_not_formatted_while_editing'); ?>"
                                    value="<?php if (isset($invoice)) {
                                        echo $invoice->adjustment;
                                    } else {
                                        echo 0;
                                    } ?>" class="form-control pull-left" name="adjustment">
                            </div>
                        </div>
                    </td>
                    <td class="adjustment"></td>
                </tr>
                <tr>
                    <td><span
                            class="bold tw-text-neutral-700"><?= _l('estimate_total'); ?>
                            :</span>
                    </td>
                    <td class="total">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="removed-items"></div>
</div>
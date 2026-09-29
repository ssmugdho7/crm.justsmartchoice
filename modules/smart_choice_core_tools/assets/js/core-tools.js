(function ($) {
    'use strict';

    function money(value) {
        var number = parseFloat(value || 0);
        if (isNaN(number)) number = 0;
        return number.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function currentDocumentTotal() {
        var selectors = [
            'table.invoice-items-table td.total',
            'table.estimate-items-table td.total',
            '.proposal-items-table td.total',
            'tr#total td.total',
            'td.total',
            '.total-money',
            '.subtotal'
        ];
        var value = 0;
        $.each(selectors, function(_, selector) {
            if (value > 0) return false;
            $(selector).each(function() {
                var normalized = String($(this).text() || '').replace(/[^0-9.-]/g, '');
                var parsed = parseFloat(normalized);
                if (!isNaN(parsed) && parsed > 0) {
                    value = parsed;
                    return false;
                }
            });
        });
        return value;
    }

    function buildPanel() {
        if ($('.sc-sales-meta-panel, .smart-choice-payment-schedule-panel').length || !$('form').length) return;
        var meta = window.SmartChoiceSalesMeta || {};
        var categories = [
            '', 'Senior Citizen', 'Veteran', 'Disability', 'Retiree', 'Loyalty Customer',
            'Promotional', 'Employee', 'Referral', 'Contractor Courtesy', 'Other'
        ];
        var options = categories.map(function (value) {
            var selected = (meta.discount_category || '') === value ? ' selected' : '';
            return '<option value="' + value + '"' + selected + '>' + (value || 'Select Discount Type') + '</option>';
        }).join('');
        var html = '' +
            '<section class="sc-sales-meta-panel">' +
                '<div class="sc-meta-heading">' +
                    '<div><strong>Payment Schedule and Discount Details</strong><small>Controls the amount due now and preserves the remaining project balance.</small></div>' +
                '</div>' +
                '<div class="sc-meta-grid">' +
                    '<div class="form-group"><label>Down Payment Percentage</label><div class="input-group"><input type="number" min="0" max="100" step="0.01" name="sc_down_payment_percent" class="form-control" value="' + (meta.down_payment_percent || 0) + '"><span class="input-group-addon">%</span></div></div>' +
                    '<div class="form-group"><label>Payment Stage</label><select name="sc_payment_stage" class="form-control"><option value="deposit">Deposit</option><option value="progress">Progress Payment</option><option value="final">Final Payment</option></select></div>' +
                    '<div class="form-group"><label>Installment Label</label><input type="text" name="sc_installment_label" class="form-control" value="' + (meta.installment_label || '') + '" placeholder="Example: Initial Deposit"></div>' +
                    '<div class="form-group"><label>Discount Type</label><select name="sc_discount_category" class="form-control">' + options + '</select></div>' +
                    '<div class="form-group"><label>Discount Reason</label><input type="text" name="sc_discount_reason" class="form-control" value="' + (meta.discount_reason || '') + '" placeholder="Explain the discount"></div>' +
                    '<div class="form-group"><label>Payment Link</label><input type="url" name="sc_payment_link" class="form-control" value="' + (meta.payment_link || '') + '" placeholder="Cash App, Zelle, or payment URL"></div>' +
                '</div>' +
                '<div class="sc-payment-summary">' +
                    '<div><span>Contract Total</span><strong class="sc-contract-total">0.00</strong></div>' +
                    '<div><span>Amount Due Now</span><strong class="sc-due-now">0.00</strong></div>' +
                    '<div><span>Remaining Balance</span><strong class="sc-remaining">0.00</strong></div>' +
                '</div>' +
            '</section>';

        var $items = $('.items').first().closest('.panel-body, .panel, .row');
        if ($items.length) {
            $items.before(html);
        } else {
            $('form').first().prepend(html);
        }

        if (meta.payment_stage) $('[name="sc_payment_stage"]').val(meta.payment_stage);
        updatePaymentSummary();
    }

    function updatePaymentSummary() {
        var meta = window.SmartChoiceSalesMeta || {};
        var total = parseFloat(meta.contract_total || 0) || currentDocumentTotal();
        var percent = parseFloat($('[name="sc_down_payment_percent"]').val() || 0) || 0;
        var due = percent > 0 ? total * percent / 100 : total;
        var remaining = Math.max(0, total - due);
        $('.sc-contract-total').text(money(total));
        $('.sc-due-now').text(money(due));
        $('.sc-remaining').text(money(remaining));
    }

    function alignSalesItems() {
        var selectors = [
            '.items.table-main-estimate-edit',
            '.items.table-main-invoice-edit',
            '.estimate-items-table',
            '.invoice-items-table',
            '.proposal-items-table'
        ];
        $(selectors.join(',')).each(function () {
            var $table = $(this);
            $table.addClass('sc-aligned-sales-table');
            $table.find('tbody tr.main, tbody tr.item').addClass('sc-sales-item-row');
            $table.find('textarea').attr('rows', 4);
            $table.find('input.form-control, select.form-control').addClass('sc-sales-control');
        });
    }

    function appendTotalsLabels() {
        var category = $('[name="sc_discount_category"]').val();
        var reason = $('[name="sc_discount_reason"]').val();
        var label = [category, reason].filter(Boolean).join(' — ');
        if (label) {
            var $discountLabel = $('#discount_area td:first .bold, tr#discount_area td:first').first();
            if ($discountLabel.length && !$discountLabel.find('.sc-discount-label').length) {
                $discountLabel.append('<small class="sc-discount-label">' + $('<div>').text(label).html() + '</small>');
            }
        }
    }

    $(function () {
        buildPanel();
        alignSalesItems();
        appendTotalsLabels();
        $(document).on('input change', '[name="sc_down_payment_percent"], [name="sc_discount_category"], [name="sc_discount_reason"]', function () {
            updatePaymentSummary();
            appendTotalsLabels();
        });
        $(document).on('item-added-to-table item-added-to-invoice sales-total-calculated', function(){ alignSalesItems(); updatePaymentSummary(); });
        setTimeout(function(){ buildPanel(); alignSalesItems(); updatePaymentSummary(); }, 500);
        setTimeout(function(){ buildPanel(); alignSalesItems(); updatePaymentSummary(); }, 1500);
        if (window.MutationObserver) {
            var observer = new MutationObserver(function(){ buildPanel(); alignSalesItems(); });
            observer.observe(document.body, {childList:true, subtree:true});
        }
    });
})(jQuery);

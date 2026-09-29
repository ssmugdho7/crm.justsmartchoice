(function ($) {
  'use strict';

  var recalcTimer = null;
  var unitOptions = [
    'Each', 'Linear Foot', 'Square Foot', 'Square Yard', 'Cubic Foot', 'Cubic Yard',
    'Foot', 'Inch', 'Pound', 'Ounce', 'Ton', 'Gallon', 'Quart', 'Liter', 'Hour',
    'Day', 'Week', 'Month', 'Lot', 'Sheet', 'Board', 'Bundle', 'Box', 'Bag', 'Roll',
    'Tube', 'Pair', 'Set', 'Roofing Square'
  ];

  function numberValue(value) {
    var text = String(value == null ? '' : value).trim().replace(/[^0-9,\.\-]/g, '');
    if (text.indexOf(',') > -1 && text.indexOf('.') > -1) {
      text = text.lastIndexOf('.') > text.lastIndexOf(',')
        ? text.replace(/,/g, '')
        : text.replace(/\./g, '').replace(',', '.');
    } else {
      text = text.replace(/,/g, '');
    }
    var parsed = parseFloat(text);
    return isFinite(parsed) ? parsed : 0;
  }

  window.scSalesNumber = numberValue;

  function roundMoney(value) {
    return Math.round((numberValue(value) + Number.EPSILON) * 100) / 100;
  }

  function formatMoney(value, withoutCurrency) {
    var amount = roundMoney(value);
    if (typeof window.format_money === 'function') {
      return window.format_money(amount, withoutCurrency === true);
    }
    return amount.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function activeForm() {
    return $('form._transaction_form:visible').first();
  }

  function selectedDiscountMode($form) {
    var $selected = $form.find('#discount-total .discount-total-type.selected').first();
    if ($selected.length) {
      return $selected.hasClass('discount-type-fixed') ? 'fixed' : 'percent';
    }

    var label = $.trim($form.find('#discount-total .discount-total-type-selected').first().text()).toLowerCase();
    if (label.indexOf('fixed') !== -1 || label.indexOf('amount') !== -1) {
      return 'fixed';
    }

    var $fixed = $form.find('input[name="discount_total"]').first();
    var $percent = $form.find('input[name="discount_percent"]').first();
    return $fixed.is(':visible') && !$percent.is(':visible') ? 'fixed' : 'percent';
  }

  function setDiscountMode($form, mode) {
    var fixed = mode === 'fixed';
    var $percent = $form.find('input[name="discount_percent"]').first();
    var $fixed = $form.find('input[name="discount_total"]').first();
    var $choices = $form.find('#discount-total .discount-total-type');

    $choices.removeClass('selected');
    $choices.filter(fixed ? '.discount-type-fixed' : '.discount-type-percent').addClass('selected');
    $form.find('#discount-total .discount-total-type-selected').text(fixed ? 'Fixed amount' : '%');

    $percent.attr({ type: 'text', inputmode: 'decimal' }).removeAttr('min max step').prop('disabled', false);
    $fixed.attr({ type: 'text', inputmode: 'decimal' }).removeAttr('min max step').prop('disabled', false);

    if (fixed) {
      $percent.addClass('hide').hide();
      $fixed.removeClass('hide').show();
    } else {
      $fixed.addClass('hide').hide();
      $percent.removeClass('hide').show();
    }
  }

  function setupDiscount($form) {
    setDiscountMode($form, selectedDiscountMode($form));
  }

  function paymentElements($form) {
    var $row = $form.find('.smart-choice-down-payment-row, #smart-choice-partial-payment-row').first();
    return {
      row: $row,
      mode: $row.find('select[name="sc_down_payment_mode"]').first(),
      visible: $row.find('.sc-down-payment-value').first(),
      percent: $row.find('input[name="sc_down_payment_percent"]').first(),
      amount: $row.find('input[name="sc_down_payment_amount"]').first(),
      contract: $row.find('input[name="sc_contract_total"]').first(),
      remaining: $row.find('input[name="sc_remaining_balance"]').first()
    };
  }

  function setupPayment($form) {
    var payment = paymentElements($form);
    if (!payment.row.length) {
      return;
    }

    var mode = payment.mode.val() === 'fixed' ? 'fixed' : 'percent';
    if (!payment.visible.length) {
      var initialValue = mode === 'fixed' ? numberValue(payment.amount.val()) : numberValue(payment.percent.val());
      var $group = $('<div class="input-group sc-payment-input-group"></div>');
      var $value = $('<input type="text" inputmode="decimal" class="form-control sc-down-payment-value sc-money-input" aria-label="Partial payment or down payment">').val(initialValue);

      if (!payment.mode.length) {
        payment.mode = $('<select name="sc_down_payment_mode" class="form-control sc-down-payment-mode"><option value="percent">%</option><option value="fixed">Fixed amount</option></select>').val(mode);
      }

      payment.mode.addClass('sc-down-payment-mode');
      $group.append($value).append($('<span class="input-group-btn sc-payment-mode-wrap"></span>').append(payment.mode));

      var $host = payment.row.find('.col-md-5').first();
      if (!$host.length) {
        $host = payment.row.find('td').first();
      }
      $host.prepend($group);
      payment.visible = $value;
    }

    payment.mode.val(mode);
    payment.percent.attr('type', 'hidden').hide();
    payment.amount.attr('type', 'hidden').hide();
  }

  function itemRows($form) {
    return $form.find('.table.has-calculations tbody tr.item');
  }

  function itemIsIncluded($row) {
    var $optional = $row.find('.optional-item-checkbox').first();
    if (!$optional.length || !$optional.prop('checked')) {
      return true;
    }
    var $selected = $row.find('.optional-choose-item-checkbox').first();
    return $selected.length ? $selected.prop('checked') : false;
  }

  function calculateTaxesForRow($row, amount, taxes) {
    var values = $row.find('select.tax').val();
    if (!values) {
      return;
    }
    if (!Array.isArray(values)) {
      values = [values];
    }

    $.each(values, function (_, taxName) {
      var $option = $row.find('select.tax option[value="' + String(taxName).replace(/"/g, '\\"') + '"]').first();
      var rate = numberValue($option.data('taxrate'));
      if (!rate) {
        var parts = String(taxName).split('|');
        rate = numberValue(parts.length > 1 ? parts[1] : 0);
      }
      if (!rate) {
        return;
      }
      if (!taxes[taxName]) {
        taxes[taxName] = { rate: rate, total: 0 };
      }
      taxes[taxName].total += (amount * rate) / 100;
    });
  }

  function calculateDocument() {
    var $form = activeForm();
    if (!$form.length || $('body').hasClass('no-calculate-total')) {
      return false;
    }

    setupDiscount($form);
    setupPayment($form);

    var subtotal = 0;
    var taxes = {};
    var $rows = itemRows($form);

    $rows.each(function () {
      var $row = $(this);
      var quantity = numberValue($row.find('[data-quantity]').first().val());
      if (quantity === 0 && $row.find('[data-quantity]').first().val() === '') {
        quantity = 1;
        $row.find('[data-quantity]').first().val('1');
      }
      var rate = numberValue($row.find('td.rate input').first().val());
      var amount = roundMoney(quantity * rate);
      $row.find('td.amount').html(formatMoney(amount, true));

      if (!itemIsIncluded($row)) {
        return;
      }
      subtotal += amount;
      calculateTaxesForRow($row, amount, taxes);
    });

    subtotal = roundMoney(subtotal);
    var discountType = $form.find('select[name="discount_type"]').val() || 'before_tax';
    var discountMode = selectedDiscountMode($form);
    var discountPercent = Math.max(0, Math.min(100, numberValue($form.find('input[name="discount_percent"]').first().val())));
    var fixedDiscountInput = Math.max(0, numberValue($form.find('input[name="discount_total"]').first().val()));

    var preTaxDiscount = 0;
    if (discountType === 'before_tax') {
      preTaxDiscount = discountMode === 'fixed' ? fixedDiscountInput : subtotal * discountPercent / 100;
      preTaxDiscount = Math.min(subtotal, preTaxDiscount);
    }

    var taxTotal = 0;
    $form.find('.tax-area').remove();
    $.each(taxes, function (taxName, taxData) {
      var value = taxData.total;
      if (discountType === 'before_tax' && subtotal > 0 && preTaxDiscount > 0) {
        value -= value * (preTaxDiscount / subtotal);
      }
      value = Math.max(0, roundMoney(value));
      taxTotal += value;
      var label = String(taxName).split('|')[0] + ' (' + taxData.rate + '%)';
      var id = typeof window.slugify === 'function' ? window.slugify(taxName) : String(taxName).replace(/[^a-z0-9]/gi, '_');
      $('<tr class="tax-area"><td class="bold tw-text-neutral-700"></td><td></td></tr>')
        .find('td:first').text(label).end()
        .find('td:last').attr('id', 'tax_id_' + id).html(formatMoney(value)).end()
        .insertAfter($form.find('#discount_area'));
    });
    taxTotal = roundMoney(taxTotal);

    var beforeAfterTotal = subtotal + taxTotal;
    var discountCalculated = preTaxDiscount;
    if (discountType === 'after_tax') {
      discountCalculated = discountMode === 'fixed' ? fixedDiscountInput : beforeAfterTotal * discountPercent / 100;
      discountCalculated = Math.min(beforeAfterTotal, Math.max(0, discountCalculated));
    }
    discountCalculated = roundMoney(discountCalculated);

    var adjustment = roundMoney(numberValue($form.find('input[name="adjustment"]').first().val()));
    var contractTotal = roundMoney(Math.max(0, beforeAfterTotal - discountCalculated + adjustment));

    var payment = paymentElements($form);
    var paymentMode = payment.mode.val() === 'fixed' ? 'fixed' : 'percent';
    var paymentValue = Math.max(0, numberValue(payment.visible.val()));
    var dueNow = paymentMode === 'fixed'
      ? paymentValue
      : contractTotal * Math.min(100, paymentValue) / 100;
    dueNow = roundMoney(Math.max(0, Math.min(contractTotal, dueNow)));
    var remaining = roundMoney(Math.max(0, contractTotal - dueNow));
    var derivedPercent = contractTotal > 0 ? dueNow / contractTotal * 100 : 0;

    var $discountPercentInput = $form.find('input[name="discount_percent"]').first();
    var $discountFixedInput = $form.find('input[name="discount_total"]').first();
    var activeElement = document.activeElement;

    // Never rewrite the field while the user is typing. Replacing `1` with `1.00`
    // on every input event prevents entry of multi-digit fixed discounts.
    if (discountMode === 'percent') {
      if (activeElement !== $discountPercentInput[0]) {
        $discountPercentInput.val(discountPercent);
      }
      if (activeElement !== $discountFixedInput[0]) {
        $discountFixedInput.val(discountCalculated.toFixed(2));
      }
    } else {
      if (activeElement !== $discountFixedInput[0]) {
        $discountFixedInput.val(fixedDiscountInput.toFixed(2));
      }
      if (activeElement !== $discountPercentInput[0]) {
        $discountPercentInput.val('0');
      }
    }

    payment.percent.val(derivedPercent.toFixed(4));
    payment.amount.val(dueNow.toFixed(2));
    payment.contract.val(contractTotal.toFixed(2));
    payment.remaining.val(remaining.toFixed(2));

    $form.find('.subtotal').html(formatMoney(subtotal) + window.hidden_input('subtotal', subtotal.toFixed(2)));
    $form.find('.discount-total').html('-' + formatMoney(discountCalculated));
    $form.find('.adjustment').html(formatMoney(adjustment));
    $form.find('.smart-choice-contract-total, .scps-contract').html(formatMoney(contractTotal));
    $form.find('.smart-choice-down-payment-total, .scps-due').html(formatMoney(dueNow));
    $form.find('.smart-choice-remaining-balance, .scps-remaining').html(formatMoney(remaining));

    var $totalCell = $form.find('td.total, .total').last();
    if ($totalCell.length) {
      $totalCell.html(formatMoney(dueNow) + window.hidden_input('total', dueNow.toFixed(2)));
      $totalCell.closest('tr').find('td:first .bold, td:first strong').first().text('Amount Due Now:');
    }

    $(document).trigger('sales-total-calculated', [{
      subtotal: subtotal,
      tax: taxTotal,
      discount: discountCalculated,
      adjustment: adjustment,
      contractTotal: contractTotal,
      dueNow: dueNow,
      remaining: remaining
    }]);
    return true;
  }

  window.calculate_total = calculateDocument;
  window.scRecalculateSalesDocument = calculateDocument;
  window.smartChoiceCalculateDownPayment = calculateDocument;
  window.smartChoiceSalesPartialPayment = calculateDocument;

  function queueCalculation() {
    window.clearTimeout(recalcTimer);
    recalcTimer = window.setTimeout(calculateDocument, 25);
  }

  $('body').off('click.scSalesDiscount', '.discount-total-type').on('click.scSalesDiscount', '.discount-total-type', function (event) {
    event.preventDefault();
    var $form = activeForm();
    setDiscountMode($form, $(this).hasClass('discount-type-fixed') ? 'fixed' : 'percent');
    var $field = $(this).hasClass('discount-type-fixed')
      ? $form.find('input[name="discount_total"]').first()
      : $form.find('input[name="discount_percent"]').first();
    $field.focus().select();
    queueCalculation();
  });

  $(document).on('input change changed.bs.select', [
    'form._transaction_form table.items td.rate input',
    'form._transaction_form table.items [data-quantity]',
    'form._transaction_form input[name="discount_percent"]',
    'form._transaction_form input[name="discount_total"]',
    'form._transaction_form input[name="adjustment"]',
    'form._transaction_form select[name="discount_type"]',
    'form._transaction_form select.tax',
    'form._transaction_form .optional-item-checkbox',
    'form._transaction_form .optional-choose-item-checkbox',
    'form._transaction_form .sc-down-payment-value',
    'form._transaction_form select[name="sc_down_payment_mode"]'
  ].join(','), queueCalculation);

  $(document).on('item-added-to-table item-removed-from-table optional-item-added optional-item-removed', function () {
    window.setTimeout(function () {
      enhanceUnits();
      calculateDocument();
    }, 0);
  });

  $(document).on('focus', 'form._transaction_form td.rate input, form._transaction_form input[name="discount_total"], form._transaction_form input[name="discount_percent"], form._transaction_form input[name="adjustment"], .sc-down-payment-value', function () {
    if (this.value !== '') {
      this.value = String(numberValue(this.value));
      this.select();
    }
  });

  $(document).on('blur', 'form._transaction_form td.rate input, form._transaction_form input[name="discount_total"], form._transaction_form input[name="discount_percent"], form._transaction_form input[name="adjustment"], .sc-down-payment-value', function () {
    if (this.value !== '') {
      this.value = numberValue(this.value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }
    queueCalculation();
  });

  $(document).on('submit', 'form._transaction_form', function () {
    var $form = $(this);
    calculateDocument();
    $form.find('td.rate input, input[name="discount_total"], input[name="discount_percent"], input[name="adjustment"], .sc-down-payment-value').each(function () {
      if (this.value !== '') {
        this.value = String(numberValue(this.value));
      }
    });
  });

  function makeUnitSelect($input) {
    if (!$input.length || $input.data('sc-unit-ready')) {
      return;
    }
    $input.data('sc-unit-ready', 1);
    var name = $input.attr('name');
    var current = $input.val() || '';
    var $select = $('<select class="form-control sc-unit-select"></select>').attr('name', name);
    $select.append('<option value="">Unit</option>');
    var choices = unitOptions.slice();
    if (current && choices.indexOf(current) === -1) {
      choices.unshift(current);
    }
    $.each(choices, function (_, unit) {
      $select.append($('<option></option>').val(unit).text(unit));
    });
    $select.append('<option value="__custom__">+ Custom unit</option>');
    $select.val(current);
    $select.on('change', function () {
      if ($(this).val() === '__custom__') {
        var custom = window.prompt('Enter unit name', current) || current;
        if (custom && !$(this).find('option').filter(function () { return this.value === custom; }).length) {
          $(this).find('option[value="__custom__"]').before($('<option></option>').val(custom).text(custom));
        }
        $(this).val(custom);
      }
    });
    $input.replaceWith($select);
  }

  function enhanceUnits() {
    var $form = activeForm();
    if (!$form.length) {
      return;
    }
    $form.find('table.items input[name="unit"], table.items input[name$="[unit]"]').each(function () {
      makeUnitSelect($(this));
    });
  }

  function refreshItemSelector() {
    var $select = $('#item_select');
    if (!$select.length) {
      return;
    }
    if (!$select.find('option[value=""]').length) {
      $select.prepend('<option value=""></option>');
    }
    if ($select.data('selectpicker')) {
      $select.selectpicker('refresh');
    }
  }

  $(document).on('shown.bs.select', '#item_select', function () {
    $(this).parent().find('.dropdown-menu li').show();
  });


  function setupMenuRepair() {
    // Keep exactly one Setup search box directly below the Setup profile card.
    $('#setup-menu .nav-second-level input[type="search"], #setup-menu .nav-second-level .sc-primary-setup-search, #setup-menu .nav-second-level [id*="setup-menu-search"]').remove();
    $('#setup-menu #sc-setup-menu-search').not(':first').closest('li,div').remove();
    var $search = $('#sc-setup-menu-search').first();
    if ($search.length && !$search.data('scBound')) {
      $search.data('scBound', true).on('input', function () {
        var term = $.trim($(this).val()).toLowerCase();
        $('#setup-menu > li').not('.sc-primary-setup-search,.sc-setup-profile-card').each(function () {
          var $li = $(this);
          var own = $li.children('a').text().toLowerCase();
          var childMatch = false;
          $li.find('.nav-second-level > li').each(function () {
            var match = !term || $(this).text().toLowerCase().indexOf(term) !== -1;
            $(this).toggle(match);
            childMatch = childMatch || match;
          });
          $li.toggle(!term || own.indexOf(term) !== -1 || childMatch);
          if (term && childMatch) { $li.addClass('active').children('ul').addClass('in').show(); }
        });
      });
    }
    $('.quick-create a,#setup-menu a,#side-menu a').each(function () {
      var $a=$(this), text=$.trim($a.text()).toLowerCase(), $i=$a.children('i').first();
      if (!$i.length) { $i=$('<i class="fa fa-circle-o menu-icon" aria-hidden="true"></i>').prependTo($a); }
      if (text.indexOf('telegram')!==-1) $i.attr('class','fa fa-paper-plane menu-icon');
      if (text.indexOf('supplier')!==-1 || text.indexOf('vendor')!==-1) $i.attr('class','fa fa-truck menu-icon');
    });
  }

  $(function () {
    setupMenuRepair();
    var $form = activeForm();
    if (!$form.length) {
      return;
    }
    setupDiscount($form);
    setupPayment($form);
    enhanceUnits();
    refreshItemSelector();
    window.setTimeout(calculateDocument, 120);
  });
})(jQuery);

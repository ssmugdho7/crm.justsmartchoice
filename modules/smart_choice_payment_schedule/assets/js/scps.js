(function () {
  function start($) {
  'use strict';

  function number(value) {
    var parsed = parseFloat(String(value == null ? '' : value).replace(/[^0-9.\-]/g, ''));
    return isNaN(parsed) ? 0 : parsed;
  }

  function currency(value) {
    if (typeof format_money === 'function') {
      return format_money(value, true);
    }
    return '$' + number(value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }

  function getContractTotal() {
    var hidden = $('input[name="total"]').last();
    if (hidden.length) return number(hidden.val());
    return number($('.total').last().text());
  }

  function calculateDue(contractTotal) {
    var mode = $('select[name="scps_mode"]').val() || 'percentage';
    var due = mode === 'fixed'
      ? number($('input[name="scps_down_payment_amount"]').val())
      : contractTotal * (number($('input[name="scps_down_payment_percent"]').val()) / 100);
    if (due < 0) due = 0;
    if (due > contractTotal) due = contractTotal;
    return Math.round((due + Number.EPSILON) * 100) / 100;
  }

  function enabled() {
    return $('input[name="scps_enabled"]').is(':checked');
  }

  function ensureRows() {
    var totalRow = $('tr').filter(function () {
      return $(this).find('td.total').length > 0;
    }).last();
    if (!totalRow.length) return null;

    if (!$('#scps-contract-total-row').length) {
      $('<tr id="scps-contract-total-row" class="scps-summary-row"><td><span class="bold tw-text-neutral-700">Contract Total:</span></td><td class="scps-contract-total text-right"></td></tr>').insertBefore(totalRow);
      $('<tr id="scps-down-payment-row" class="scps-summary-row"><td><span class="bold tw-text-neutral-700">Down Payment Due Now:</span></td><td class="scps-down-payment-total text-right"></td></tr>').insertBefore(totalRow);
      $('<tr id="scps-balance-row" class="scps-summary-row"><td><span class="bold tw-text-neutral-700">Remaining Balance:</span></td><td class="scps-balance-total text-right"></td></tr>').insertBefore(totalRow);
    }
    return totalRow;
  }

  function refresh() {
    var mode = $('select[name="scps_mode"]').val() || 'percentage';
    $('.scps-fixed-wrap').toggle(mode === 'fixed');
    $('.scps-percent-wrap').toggle(mode !== 'fixed');

    var totalRow = ensureRows();
    if (!totalRow) return;

    var contractTotal = getContractTotal();
    var due = enabled() ? calculateDue(contractTotal) : contractTotal;
    var balance = Math.max(0, contractTotal - due);

    $('.scps-live-due').text(currency(due));
    $('.scps-contract-total').text(currency(contractTotal));
    $('.scps-down-payment-total').text(currency(due));
    $('.scps-balance-total').text(currency(balance));

    $('#scps-contract-total-row, #scps-down-payment-row, #scps-balance-row').toggle(enabled());

    var totalLabel = totalRow.find('td').first().find('span.bold');
    if (enabled()) {
      totalLabel.text('Amount Customer Pays Now:');
      totalRow.find('td.total').contents().filter(function(){ return this.nodeType === 3; }).remove();
      totalRow.find('td.total').prepend(document.createTextNode(currency(due)));
      if (!$('input[name="scps_amount_due_now"]').length) {
        $('<input>', {type:'hidden', name:'scps_amount_due_now'}).appendTo(totalRow.find('td.total'));
      }
      $('input[name="scps_amount_due_now"]').val(due.toFixed(2));
    } else {
      totalLabel.text('Total:');
      totalRow.find('td.total').contents().filter(function(){ return this.nodeType === 3; }).remove();
      totalRow.find('td.total').prepend(document.createTextNode(currency(contractTotal)));
      $('input[name="scps_amount_due_now"]').remove();
    }
  }

  $(document).on('sales-total-calculated', refresh);
  window.scpsRefreshTotals = refresh;
  $(document).on('change keyup', 'input[name="scps_enabled"], select[name="scps_mode"], input[name="scps_down_payment_percent"], input[name="scps_down_payment_amount"], input[name="discount_percent"], input[name="discount_total"], input[name="adjustment"]', function(){ refresh(); setTimeout(refresh, 80); });
  $(document).ajaxComplete(function(){ setTimeout(refresh, 50); });
  $(function(){ setTimeout(refresh, 150); });
  }
  if(window.jQuery){start(window.jQuery);}else{document.addEventListener('DOMContentLoaded',function(){if(window.jQuery){start(window.jQuery);}});}
})();

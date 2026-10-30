/* Native expense actions, uploads and DataTables remain owned by Perfex. */
(function ($) {
    'use strict';
    if (!$('body').hasClass('scct-expenses')) return;
    $.ajaxPrefilter(function (options) {
        if ($('#expenses_total').length && options.url.split('?')[0] === admin_url + 'expenses/get_expenses_total') {
            options.data = (options.data ? options.data + '&' : '') + 'scct_expenses_workspace=1';
        }
    });
    function syncStatus() {
        var value = $('#scct-expense-status').val() || 'all';
        $('[data-scct-expense-status]').each(function () {
            var active = $(this).attr('data-scct-expense-status') === value;
            $(this).toggleClass('is-active', active).attr('aria-pressed', String(active));
        });
    }
    $(document).on('click.scctExpenses', '[data-scct-expense-status]', function () {
        var $filter = $('#scct-expense-status');
        if (!$filter.length) return;
        $filter.val($(this).attr('data-scct-expense-status'));
        if ($.fn.selectpicker) $filter.selectpicker('refresh');
        $filter.trigger('change');
        syncStatus();
    }).on('change.scctExpenses', '#scct-expense-status', syncStatus);
    $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings.url && settings.url.indexOf('expenses/get_expenses_total') !== -1) syncStatus();
    });
    $(syncStatus);
})(jQuery);

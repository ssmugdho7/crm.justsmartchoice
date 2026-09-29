(function($){
'use strict';
if (!$) { return; }

function init_supplier_modal(id) {
    if (typeof id === 'undefined') { id = 0; }
    $('#supplier-modal').modal('show');
    $('#supplier-modal-content').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
    $.get(admin_url + 'supplier/supplier_form/' + id, function(response) {
        $('#supplier-modal-content').html(response);
        if ($.fn.selectpicker) { $('#supplier-modal-content .selectpicker').selectpicker('refresh'); }
        if ($.fn.tagsinput) { $('#supplier-modal-content .tagsinput').tagsinput(); }
    });
}
function supplierBulkAction(btn) {
    var ids = [];
    $('.table-suppliers tbody input[type="checkbox"]:checked').each(function(){ ids.push($(this).val()); });
    if (!ids.length) { alert_float('warning', 'Please select at least one supplier.'); return false; }
    if (!confirm_delete()) { return false; }
    $(btn).addClass('disabled');
    $.post(admin_url + 'supplier/bulk_action', {ids: ids, mass_delete: $('#mass_delete').is(':checked') ? 1 : 0}).done(function(){ window.location.reload(); }).fail(function(xhr){ alert_float('danger', xhr.responseText || 'Action failed'); $(btn).removeClass('disabled'); });
}
function supplierApplyFilters() {
    var table = $('.table-suppliers').DataTable();
    var keyword = $('#supplier_filter_keyword').val();
    var trade = $('#supplier_filter_trade').val();
    table.search(keyword || '').draw();
    if (trade) { table.column(5).search(trade).draw(); } else { table.column(5).search('').draw(); }
}
function supplierClearFilters() {
    $('#supplier_filter_keyword').val('');
    $('#supplier_filter_trade').val('');
    $('#supplier_filter_location').val('');
    $('#supplier_filter_visibility').val('');
    var table = $('.table-suppliers').DataTable();
    table.search('').columns().search('').draw();
}
$(document).on('mouseenter', '.supplier-actions-cell', function(){ $(this).find('.supplier-hidden-meta').stop(true,true).fadeIn(120); });
$(document).on('mouseleave', '.supplier-actions-cell', function(){ $(this).find('.supplier-hidden-meta').stop(true,true).fadeOut(120); });


function supplierSelectedIds() {
    var ids = [];
    $('.table-suppliers tbody input[type="checkbox"]:checked').each(function(){ ids.push($(this).val()); });
    return ids;
}
function supplierExport(type) {
    var ids = supplierSelectedIds();
    var url = admin_url + 'supplier/export/' + encodeURIComponent(type || 'csv');
    if (ids.length) { url += '?ids=' + encodeURIComponent(ids.join(',')); }
    $('.supplier-main-export').addClass('supplier-export-active');
    window.location.href = url;
    setTimeout(function(){ $('.supplier-main-export').removeClass('supplier-export-active'); }, 1600);
}
function supplierEmailModal(id) {
    $('#supplier-email-form').attr('enctype','multipart/form-data');
    $('#supplier_email_modal').modal('show');
    $('#supplier-email-modal-content').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
    $.get(admin_url + 'supplier/email_form/' + id, function(response) {
        $('#supplier-email-modal-content').html(response);
    }).fail(function(xhr){
        $('#supplier-email-modal-content').html('<div class="alert alert-danger">' + (xhr.responseText || 'Email form failed to load.') + '</div>');
    });
}
function supplierMoveToolbar() {
    var $wrapper = $('.table-suppliers_wrapper');
    if (!$wrapper.length) { return; }
    var $filter = $wrapper.find('.dataTables_filter');
    var $static = $('#supplier-static-toolbar');
    if ($filter.length) {
        $filter.addClass('supplier-search-toolbar');
        if (!$filter.find('.supplier-dt-toolbar-live').length) {
            var $toolbar = $('#supplier-toolbar-template').children().clone();
            var $live = $('<div class="supplier-dt-toolbar-live"></div>').append($toolbar);
            $filter.prepend($live);
        }
        if ($filter.find('.supplier-dt-toolbar-live').length) {
            $static.hide();
        }
    } else {
        $static.show();
    }
}
$(document).on('init.dt draw.dt', function(){ setTimeout(supplierMoveToolbar, 50); });
$(function(){ setTimeout(supplierMoveToolbar, 250); setTimeout(supplierMoveToolbar, 800); });
function supplierPrint() {
    supplierExport('pdf');
}

$(document).on('draw.dt init.dt', function () {
    setTimeout(function () {
        var $table = $('.table-suppliers');
        var $wrap = $table.closest('.dataTables_wrapper');
        $wrap.find('.dataTables_length select').addClass('input-sm');
        $table.css({width: '100%'});
        $table.closest('.table-responsive').css({overflowX: 'hidden'});
        $table.closest('.dataTables_wrapper').css({overflowX: 'hidden'});
    }, 80);
});

/* Smart Choice v1.0.8: force supplier list to stay inside one visible page without double horizontal bars. */
function supplierRemoveHorizontalScrollbars() {
    var $table = $('.table-suppliers');
    if (!$table.length) { return; }
    var $wrapper = $table.closest('.dataTables_wrapper');
    $table.css({ width: '100%', maxWidth: '100%', minWidth: '0', tableLayout: 'fixed' });
    $wrapper.find('.dataTables_scroll, .dataTables_scrollHead, .dataTables_scrollHeadInner, .dataTables_scrollBody, .dataTables_scrollFoot').css({
        overflowX: 'hidden',
        width: '100%',
        maxWidth: '100%'
    });
    $wrapper.find('.table-responsive').css({ overflowX: 'hidden', maxWidth: '100%' });
    $table.closest('.table-responsive').css({ overflowX: 'hidden', maxWidth: '100%' });
    $wrapper.css({ overflowX: 'hidden', maxWidth: '100%' });
}
$(document).on('init.dt draw.dt page.dt length.dt search.dt order.dt', function () {
    setTimeout(supplierRemoveHorizontalScrollbars, 40);
    setTimeout(supplierRemoveHorizontalScrollbars, 180);
});
$(function () {
    setTimeout(supplierRemoveHorizontalScrollbars, 300);
    setTimeout(supplierRemoveHorizontalScrollbars, 1000);
});

/* Smart Choice v1.0.9: hard reset remaining supplier horizontal scroll after DataTables redraws. */
function supplierFinalFitTable() {
    var $table = $('.table-suppliers');
    if (!$table.length) { return; }
    var $wrapper = $table.closest('.dataTables_wrapper');
    $table.css({
        width: '100%',
        maxWidth: '100%',
        minWidth: '0',
        tableLayout: 'fixed'
    });
    $wrapper.css({ overflowX: 'hidden', maxWidth: '100%', width: '100%' });
    $wrapper.find('.dataTables_scroll, .dataTables_scrollHead, .dataTables_scrollHeadInner, .dataTables_scrollBody, .dataTables_scrollFoot, .table-responsive').css({
        overflowX: 'hidden',
        maxWidth: '100%',
        width: '100%'
    });
    $table.closest('.table-responsive').css({ overflowX: 'hidden', maxWidth: '100%', width: '100%' });
}
$(document).on('init.dt draw.dt page.dt length.dt search.dt order.dt column-sizing.dt', function () {
    setTimeout(supplierFinalFitTable, 25);
    setTimeout(supplierFinalFitTable, 150);
    setTimeout(supplierFinalFitTable, 500);
});
$(window).on('resize', function () { setTimeout(supplierFinalFitTable, 80); });
$(function () {
    setTimeout(supplierFinalFitTable, 250);
    setTimeout(supplierFinalFitTable, 900);
});


})(window.jQuery || window.$);

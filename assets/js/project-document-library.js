(function () {
    'use strict';
    function start() {
        var root = document.getElementById('project-document-library');
        if (!root || root.dataset.documentsReady === '1') return;
        root.dataset.documentsReady = '1';
        var table = root.querySelector('.table-project-files');
        var input = root.querySelector('#project-document-search');
        var count = root.querySelector('#project-document-count');
        if (!table || !input) return;
        var labels = Array.prototype.map.call(table.querySelectorAll('thead th'), function (cell) { return cell.textContent.trim(); });
        function dataTable() {
            var jq = window.jQuery;
            return jq && jq.fn.DataTable && jq.fn.DataTable.isDataTable(table) ? jq(table).DataTable() : null;
        }
        function decorate() {
            Array.prototype.forEach.call(table.querySelectorAll('tbody tr.project-document-row'), function (row) {
                Array.prototype.forEach.call(row.cells, function (cell, i) {
                    // DataTables can hide cells; preserve each column's original index.
                    var index = cell._DT_CellIndex ? cell._DT_CellIndex.column : i;
                    cell.setAttribute('data-document-label', labels[index] || 'Select document');
                    cell.classList.add('project-document-column-' + index);
                });
            });
            var api = dataTable();
            var shown = api ? api.page.info().recordsDisplay : table.querySelectorAll('tbody tr.project-document-row:not([hidden])').length;
            var total = api ? api.page.info().recordsTotal : table.querySelectorAll('tbody tr.project-document-row').length;
            count.textContent = shown + ' of ' + total + ' documents' + (shown === 0 ? ' — no matching documents' : '');
        }
        function search() {
            var api = dataTable();
            if (api) {
                api.search(input.value).draw();
            } else {
                var query = input.value.trim().toLocaleLowerCase();
                Array.prototype.forEach.call(table.querySelectorAll('tbody tr.project-document-row'), function (row) {
                    row.hidden = !!query && row.textContent.toLocaleLowerCase().indexOf(query) === -1;
                });
            }
            decorate();
        }
        input.addEventListener('input', search);
        root.querySelector('.project-documents-search').hidden = false;
        root.classList.add('project-documents-enhanced');
        if (window.jQuery) {
            window.jQuery(table).on('init.dt.projectDocuments', function () {
                // Remove fallback visibility before DataTables takes over filtering.
                Array.prototype.forEach.call(table.querySelectorAll('tbody tr[hidden]'), function (row) { row.hidden = false; });
                var api = dataTable();
                if (api) {
                    if (input.value) api.search(input.value).draw();
                    else input.value = api.search();
                }
                decorate();
            }).on('draw.dt.projectDocuments column-visibility.dt.projectDocuments', decorate);
        }
        decorate();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
}());

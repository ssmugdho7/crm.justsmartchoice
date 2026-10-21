<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
(function () {
    function initModuleFilters() {
        var table = document.getElementById('sc_modules_table');
        if (!table) return;
        var root = document.getElementById('wrapper'), input = document.getElementById('sc_module_search');
        var empty = document.getElementById('sc_module_empty');
        var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr'));
        var buttons = Array.prototype.slice.call(root.querySelectorAll('[data-sc-filter]')), current = 'all';
        function matchesFilter(row, filter) {
            if (filter === 'active') return row.dataset.active === '1';
            if (filter === 'inactive') return row.dataset.active === '0';
            if (filter === 'update') return row.dataset.update === '1';
            if (filter === 'error') return row.dataset.error === '1';
            return true;
        }
        function apply() {
            var query = input ? input.value.trim().toLowerCase() : '', visible = 0;
            rows.forEach(function (row) {
                var show = matchesFilter(row, current) && (!query || row.textContent.toLowerCase().indexOf(query) !== -1);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (empty) {
                empty.textContent = query ? 'No modules match your search in this filter.' :
                    (current === 'error' ? 'No module compatibility or database upgrade errors were found.' :
                    (current === 'update' ? 'No module database upgrades are pending.' : 'No modules match this filter.'));
                empty.style.display = visible ? 'none' : '';
            }
        }
        ['all', 'active', 'inactive', 'update', 'error'].forEach(function (filter) {
            var count = document.getElementById('sc_count_' + filter);
            if (count) count.textContent = rows.filter(function (row) { return matchesFilter(row, filter); }).length;
        });
        buttons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                current = button.dataset.scFilter;
                buttons.forEach(function (other) {
                    var selected = other.dataset.scFilter === current;
                    other.classList.toggle('active', selected);
                    other.setAttribute('aria-pressed', selected ? 'true' : 'false');
                });
                apply();
            });
        });
        if (input) input.addEventListener('input', apply);
        apply();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initModuleFilters);
    else initModuleFilters();
})();
</script>

(function ($) {
    'use strict';
    if (!$ || !document.body.classList.contains('scpr-projects')) return;

    $(document).on('click', '.prj-kpi-card', function () {
        $('.prj-kpi-card').removeClass('is-active').attr('aria-pressed', 'false');
        $(this).addClass('is-active').attr('aria-pressed', 'true');
    });

    $(document).on('change', '.scpr-task-toggle', function () {
        var checkbox = this;
        var id = checkbox.getAttribute('data-task-id');
        var checked = checkbox.checked;
        if (!/^\d+$/.test(id)) return;
        checkbox.disabled = true;
        // Use the same authorized native task endpoints as Perfex's completion controls.
        var route = checked ? 'tasks/mark_as/5/' + id : 'tasks/unmark_complete/' + id;
        function failed() {
            checkbox.checked = !checked;
            checkbox.disabled = false;
            alert_float('danger', $(checkbox).closest('.scpr-punchlist').attr('data-error'));
        }
        requestGetJSON(route + '?single_task=false').done(function (response) {
            if (response.success === true || response.success === 'true') {
                window.location.reload();
            } else {
                failed();
            }
        }).fail(failed);
    });
})(window.jQuery);

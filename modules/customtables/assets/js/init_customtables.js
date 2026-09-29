document.addEventListener(
    "DOMContentLoaded",
    function () {
        "use strict";
        $(".table-proposals").attr(
            "data-default-order",
            JSON.stringify([[0, "asc"]])
        );
        $(".table-projects").attr(
            "data-default-order",
            JSON.stringify([[0, "asc"]])
        );
        $(".table-contracts").attr(
            "data-default-order",
            JSON.stringify([[0, "asc"]])
        );
        $(".table-expenses").attr(
            "data-default-order",
            JSON.stringify([[0, "asc"]])
        );
        $(".table-projects-single-client").attr(
            "data-default-order",
            JSON.stringify([[0, "asc"]])
        );
        $(".table-expenses-single-client").attr(
            "data-default-order",
            JSON.stringify([[0, "asc"]])
        );
        $(".table-contracts-single-client").attr(
            "data-default-order",
            JSON.stringify([[0, "asc"]])
        );

        var column_tables =
            ".table-leads, .table-clients, .table-proposals, .table-estimates, .table-projects, .table-tasks, .table-invoices, .table-contracts, .table-expenses, .table-invoice-items";

        $(document).on("init.dt", function (e, settings) {
            var table = new $.fn.dataTable.Api(settings);
            /* New safer implementation */
            var $node = $(table.table().node());
            var classAttr = ($node.attr("class") || "").trim();
            var tableClass = classAttr
                .split(/\s+/)
                .find(c => c.indexOf("table-") === 0);
            var dataTableType = tableClass ? tableClass.replace("table-", "") : null;
            if (dataTableType && table.button && $node.is(column_tables)) {
                table
                    .buttons()
                    .container()
                    .appendTo($(table.table().container()).find(".dt-buttons"));
                table.button().add(null, {
                    text: '<i class="fa fa-list"></i>',
                    action: function (e, dt, node, config) {
                        requestGet(
                            `${admin_url}/customtables/column_popup/${dataTableType}`
                        ).done(function (response) {
                            $(response).insertAfter("#lead_reminder_modal");
                            $("body").find("#_custom_tables_popup").modal({
                                show: true,
                                backdrop: "static",
                            });
                            initSorting();
                        });
                    },
                    className: "btn-sm",
                });
            }
        });
    },
    false
);

// Initialize sortable lists
function initSorting() {
    // Make sure we're targeting the correct elements
    var allowColumns = $("#allow_columns");
    var displayColumns = $("#display_columns");

    if (allowColumns.length && displayColumns.length) {
        allowColumns
            .sortable({
                connectWith: ".connectedSortable",
                placeholder: "ui-state-highlight",
                receive: function (event, ui) {
                    if ($(ui.item).hasClass("disabled")) {
                        $(ui.sender).sortable("cancel");
                    }
                },
            })
            .disableSelection();

        displayColumns
            .sortable({
                connectWith: ".connectedSortable",
                placeholder: "ui-state-highlight",
            })
            .disableSelection();

        $(".connectedSortable").on("sortstop", function (event, ui) {
            saveColumns();
        });
    }
}
var currentRequest = null;

const saveColumns = () => {
    let columns = [];
    let table = $("#display_columns").data("table-name");
    $("#display_columns li").each(function () {
        columns.push($(this).data("column-id"));
    });
    var data = {};
    data[table] = columns;
    currentRequest = $.ajax({
        url: `${admin_url}customtables/storeColumns/`,
        type: "post",
        data: data,
        beforeSend: function () {
            if (currentRequest != null) {
                currentRequest.abort();
            }
        },
        success: function (data) { },
    });
};

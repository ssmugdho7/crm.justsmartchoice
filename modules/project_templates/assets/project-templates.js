var _table_api;
$(document).ready(function(){
    "use strict";

    var table_project_templates = $('.table-project-templates');
    if (table_project_templates.length) {

        // Tasks not sortable
        var projectsTableNotSortable = [0]; // bulk actions
        var projectsTableURL = admin_url + 'project_templates/table';

        if ($("body").hasClass('projects-page')) {
            projectsTableURL += '?bulk_actions=true';
        }

        _table_api = initDataTable(table_project_templates, projectsTableURL, projectsTableNotSortable, projectsTableNotSortable, {}, [1, 'asc']);
    }


    if ($("#project-template-files-upload").length > 0) {
        new Dropzone(
            "#project-template-files-upload",
            appCreateDropzoneOptions({
                paramName: "file",
                uploadMultiple: true,
                parallelUploads: 20,
                maxFiles: 20,
                accept: function (file, done) {
                    done();
                },
                success: function (file, response) {
                    if (
                        this.getUploadingFiles().length === 0 &&
                        this.getQueuedFiles().length === 0
                    ) {
                        window.location.href =
                            admin_url +
                            "project_templates/view/" +
                            project_id +
                            "?group=project_files";
                    }
                },
                sending: function (file, xhr, formData) {
                    formData.append(
                        "visible_to_customer",
                        $('input[name="visible_to_customer"]').prop("checked")
                    );
                },
            })
        );
    }

    milestones_template_kanban();

    $("#milestone_modal").on("hidden.bs.modal", function (event) {
        $("#additional_milestone").html("");
        $('#milestone_modal input[name="start_date"]').val("");
        $('#milestone_modal input[name="due_date"]').val("");
        $('#milestone_modal input[name="name"]').val("");
        $('#milestone_modal input[name="milestone_order"]').val(
            $(".table-milestones tbody tr").length + 1
        );
        $('#milestone_modal textarea[name="description"]').val("");
        $('#milestone_modal input[name="description_visible_to_customer"]').prop(
            "checked",
            false
        );
        $('#milestone_modal input[name="hide_from_customer"]').prop("checked", false);
        $("#milestone_modal .add-title").removeClass("hide");
        $("#milestone_modal .edit-title").removeClass("hide");
    });

    appValidateForm($("#project_template_milestone_form"), {
        name: "required",
        start_date: "required",
        due_date: "required",
    });

    var milestone_form = $("#project_template_milestone_form");
    var milestone_start_date = milestone_form.find("#start_date");
    milestone_start_date.on("changed.bs.select", function (e) {
        milestone_form
            .find("#due_date")
            .data("data-date-min-date", milestone_start_date.val());
    });

    $("body").on("click", ".new-task-template-to-milestone", function(){
        var milestone_id = $(this)
            .parents(".milestone-column")
            .data("col-status-id");
        var project_template_id = $("[name=project_id]").val();
        var url = admin_url + 'task_templates/task?rel_type=project&rel_id='+project_template_id;
        if(milestone_id != null){
            url = url + '&milestone='+milestone_id
        }
        new_task_template(url);
        $('body [data-toggle="popover"]').popover("hide");
    });

    $("body").on(
        "click",
        ".milestone-column .cpicker,.milestone-column .reset_milestone_color",
        function (e) {
            e.preventDefault();
            var color = $(this).data("color");
            var invoker = $(this);
            var milestone_id = invoker
                .parents(".milestone-column")
                .data("col-status-id");
            $.post(admin_url + "project_templates/change_milestone_color", {
                color: color,
                milestone_id: milestone_id,
            }).done(function () {
                // Reset color needs reload
                if (color == "") {
                    window.location.reload();
                } else {
                    var $parent = invoker.parents(".milestone-column");
                    $parent.find(".reset_milestone_color").removeClass("hide");
                    $parent
                        .find(".panel-heading")
                        .addClass("color-white")
                        .removeClass("task-phase");
                    $parent.find(".edit-milestone-phase").addClass("color-white");
                }
            });
        }
    );

    $("body").on("shown.bs.modal", "._project_file", function () {
        var content_height =
            $("body").find("._project_file .modal-content").height() - 165;
        var projectFilePreviewIframe = $(".project_file_area iframe");

        if (projectFilePreviewIframe.length > 0) {
            projectFilePreviewIframe.css("height", content_height);
        }

        if (!is_mobile()) {
            $(".project_file_area").css(
                "height",
                content_height
            );
        }
    });
});

function init_new_project_template_form(){
    var inner_popover_template = '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"></div></div></div>';

    appValidateForm($('#project-form'), {
        name: 'required',
        duration_value: {
            number: true,
            min: 0
        },
        repeat_every_custom: {min: 1},
    }, project_template_form_handler);

}

// Go to edit view
function edit_project_template(template_id) {
    requestGet('project_templates/project/' + template_id).done(function (response) {
        $('#_project').html(response);
        $('#project-modal').modal('hide');
        $("body").find('#_project_modal').modal({
            show: true,
            backdrop: 'static'
        });
    });
}

// Handles project add/edit form modal.
function project_template_form_handler(form) {

    // Disable the save button in cases od duplicate clicks
    $('#_project_modal').find('button[type="submit"]').prop('disabled', true);

    $("#_project_modal input[type=file]").each(function () {
        if ($(this).val() === "") {
            $(this).prop('disabled', true);
        }
    });

    var formURL = form.action;
    var formData = new FormData($(form)[0]);

    $.ajax({
        type: $(form).attr('method'),
        data: formData,
        mimeType: $(form).attr('enctype'),
        contentType: false,
        cache: false,
        processData: false,
        url: formURL
    }).done(function (response) {
        response = JSON.parse(response);
        if (response.success === true || response.success == 'true') {
            alert_float('success', response.message);
        }
        $('#_project_modal').modal('hide');
        _table_api.ajax.reload();
    }).fail(function (error) {
        alert_float('danger', JSON.parse(error.responseText));
    });

    return false;
}

// Removes project single attachment
function remove_project_template_attachment(link, id) {
    if (confirm_delete()) {
        requestGetJSON('project_templates/remove_project_attachment/' + id).done(function (response) {
            if (response.success === true || response.success == 'true') {
                $('[data-project-attachment-id="' + id + '"]').remove();
            }
            _project_attachments_more_and_less_checks();
            if (response.comment_removed) {
                $('#comment_' + response.comment_removed).remove();
            }
        });
    }
}


function project_template_files_bulk_action(e) {
    if (confirm_delete()) {
        var mass_delete = $("#mass_delete").prop("checked");
        var ids = [];
        var data = {};
        if (mass_delete == false || typeof mass_delete == "undefined") {
            data.visible_to_customer = $("#bulk_pf_visible_to_customer").prop(
                "checked"
            );
        } else {
            data.mass_delete = true;
        }

        var rows = $(".table-project-files").find("tbody tr");
        $.each(rows, function () {
            var checkbox = $($(this).find("td").eq(0)).find("input");
            if (checkbox.prop("checked") == true) {
                ids.push(checkbox.val());
            }
        });

        data.ids = ids;
        $(e).addClass("disabled");

        setTimeout(function () {
            $.post(admin_url + "project_templates/bulk_action_files", data).done(function () {
                window.location.reload();
            });
        }, 200);
    }
}


function new_milestone_template() {
    $("#milestone_modal").modal("show");
    $("#milestone_modal .edit-title").addClass("hide");
}

function edit_milestone_template(invoker, id) {
    var description_visible_to_customer = $(invoker).data(
        "description-visible-to-customer"
        ),
        hide_from_customer = $(invoker).data("hide-from-customer");
    if (description_visible_to_customer == 1) {
        $('input[name="description_visible_to_customer"]').prop("checked", true);
    } else {
        $('input[name="description_visible_to_customer"]').prop("checked", false);
    }

    $('input[name="hide_from_customer"]').prop(
        "checked",
        hide_from_customer == 1
    );

    $("#additional_milestone").append(hidden_input("id", id));
    $('#milestone_modal input[name="name"]').val($(invoker).data("name"));
    $('#milestone_modal input[name="start_date"]').val($(invoker).data("start_date"));
    $('#milestone_modal input[name="due_date"]').val($(invoker).data("due_date"));
    $('#milestone_modal input[name="milestone_order"]').val($(invoker).data("order"));
    $('#milestone_modal textarea[name="description"]').val(
        $(invoker).data("description")
    );
    $("#milestone_modal").modal("show");
    $("#milestone_modal .add-title").addClass("hide");
}

function milestones_template_switch_view() {
    $("#milestones-table").toggleClass("hide");
    $(".project-milestones-kanban").toggleClass("hide");
    if (!$.fn.DataTable.isDataTable(".table-milestones")) {
        initDataTable(
            ".table-milestones",
            admin_url + "project_templates/milestones/" + project_id
        );
    }
}

function milestones_template_kanban() {
    init_kanban(
        "project_templates/milestones_kanban",
        milestones_template_kanban_update,
        ".project-milestone",
        445,
        360,
        after_milestones_template_kanban
    );
}

function milestones_template_kanban_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        data = {};
        data.order = [];
        data.milestone_id = $(ui.item.parent()[0])
            .parents(".milestone-column")
            .data("col-status-id");
        data.task_id = $(ui.item).data("task-id");
        var tasks = $(ui.item.parent()[0])
            .parents(".milestone-column")
            .find(".task");

        var i = 0;
        $.each(tasks, function () {
            data.order.push([$(this).data("task-id"), i]);
            i++;
        });
        check_kanban_empty_col("[data-task-id]");

        setTimeout(function () {
            $.post(admin_url + "project_templates/update_task_milestone", data);
        }, 50);
    }
}

function after_milestones_template_kanban() {
    $("#kan-ban").sortable({
        helper: "clone",
        item: ".kan-ban-col",
        cancel: ".milestone-not-sortable",
        update: function (event, ui) {
            var uncategorized_is_after = $(ui.item).next(
                'ul.kan-ban-col[data-col-status-id="0"]'
            );

            if (uncategorized_is_after.length) {
                $(this).sortable("cancel");
                return false;
            }

            var data = {};
            data.order = [];
            var status = $(".kan-ban-col");
            var i = 0;

            $.each(status, function () {
                data.order.push([$(this).data("col-status-id"), i]);
                i++;
            });

            $.post(admin_url + "project_templates/update_milestones_order", data);
        },
    });

    for (
        var i = -10;
        i < $(".task-phase").not(".color-not-auto-adjusted").length / 2;
        i++
    ) {
        var r = 120;
        var g = 169;
        var b = 56;
        $(".task-phase:eq(" + (i + 10) + ")")
            .not(".color-not-auto-adjusted")
            .css("background", color(r - i * 13, g - i * 13, b - i * 13))
            .css("border", "1px solid " + color(r - i * 12, g - i * 12, b - i * 12));
    }
}

function view_project_template_file(id, $project_id) {
    $("#project_file_data").empty();
    $("#project_file_data").load(
        admin_url + "project_templates/file/" + id + "/" + project_id,
        function (response, status, xhr) {
            if (status == "error") {
                alert_float("danger", xhr.statusText);
            }
        }
    );
}

function update_template_file_data(id) {
    var data = {};
    data.id = id;
    data.subject = $('body input[name="file_subject"]').val();
    data.description = $('body textarea[name="file_description"]').val();
    $.post(admin_url + "project_templates/update_file_data/", data);
}
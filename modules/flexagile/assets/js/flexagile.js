"use strict";
function flexagile_cta() {
    $('#flexsprints-dropdown-container').toggle();
    return false;
}

$(document).on('change', '#flexsprints-dropdown-container select', function() {
    const sprint_id = $(this).val();
    const container = $('#flexsprints-dropdown-container');
    const url = $(container).data('url');
    const label = $(this).find('option:selected').text();
    const message = $(container).data('message');
    const data = {
        action: 'update_sprint_data',
        sprint_id: sprint_id,
        task_id: $(container).data('id'),
        task_status: $(container).data('status'),
    }
    //make post request
    $.post(url, data, function(response) {
        $(container).hide();
        $('#flexagile-task-selected span').html(label);
        alert_float('success', message);

    });
    return false;
});

$(document).on('click', '.flexagile-edit-sprint', function (){
    const obj = $(this);
    const container = $('#flexagile-create-edit-sprint-modal');
    $(container).find('.modal-title').html(obj.data('title'));
    $(container).find('input[name="name"]').val(obj.data('name'));
    $(container).find('input[name="id"]').val(obj.data('id'));
    $(container).find('input[name="start_date"]').val(obj.data('start-date'));
    $(container).find('input[name="end_date"]').val(obj.data('end-date'));
    $(container).find('textarea[name="description"]').val(obj.data('description'));
    $(container).find('select[name="project_id"]').val(obj.data('project-id'));
    $(container).find('select[name="project_id"]').prop('disabled', true);
    $(container).find('select[name="project_id"]').selectpicker('refresh');
    $(container).find('.modal-footer .btn-primary').html(obj.data('button-text'));
    //button text
    $(container).modal('show');
    return false;
});

$(document).on('click', '#flexagile-create-sprint', function (){
    const container = $('#flexagile-create-edit-sprint-modal');
    $(container).find('.modal-title').html($(this).data('title'));
    $(container).find('input[name="name"]').val('');
    $(container).find('input[name="id"]').val('');
    $(container).find('input[name="start_date"]').val('');
    $(container).find('input[name="end_date"]').val('');
    $(container).find('textarea[name="description"]').val('');
    $(container).find('select[name="project_id"]').val('');
    $(container).find('select[name="project_id"]').prop('disabled', false);
    $(container).find('select[name="project_id"]').selectpicker('refresh');
    $(container).find('.modal-footer .btn-primary').html($(this).data('button-text'));
    $(container).modal('show');
    return false;
});

function flexagile_select_move_to_toggle(id) {
    $('#flexiselect-moveto-'+id).toggle();
    return false;
}

function flexagile_select_move_to(obj, task_id, task_status) {
    const container = $('#flexiselect-moveto-'+task_id);
    const url = $(container).data('url');
    const message = $(container).data('message');
    const data = {
        action: 'update_sprint_data',
        sprint_id: $(obj).val(),
        task_id: task_id,
        task_status: task_status,
    }
    //make post request
    $.post(url, data, function(response) {
        $(container).hide();
        alert_float('success', message);
        location.reload();
    });
    return false;
}
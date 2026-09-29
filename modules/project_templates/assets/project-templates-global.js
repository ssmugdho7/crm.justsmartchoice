
// New project template function, various actions performed
function new_project_from_template(id) {
    var url = admin_url + 'project_templates/create_project';
    if(typeof (id) != 'undefined'){
        url += '?id=' + id;
    }

    var $projectSingleModal = $('#project-modal');
    if ($projectSingleModal.is(':visible')) {
        $projectSingleModal.modal('hide');
    }

    var $projectEditModal = $('#_project_modal');
    if ($projectEditModal.is(':visible')) {
        $projectEditModal.modal('hide');
    }

    requestGet(url).done(function (response) {
        $('#_task').html(response);
        $("body").find('#_project_modal').modal({
            show: true,
            backdrop: 'static'
        });
    }).fail(function (error) {
        alert_float('danger', error.responseText);
    })
}
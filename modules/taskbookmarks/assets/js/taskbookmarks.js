// Change task milestone from single modal
function task_change_taskbookmarks(taskbookmarks_id, task_id) {
    url = 'taskbookmarks/change_taskbookmarks/' + taskbookmarks_id + '/' + task_id;
    var taskModalVisible = $('#task-modal').is(':visible');
    url += '?single_task=' + taskModalVisible;
    requestGetJSON(url).done(function(response) {
        if (response.success === true || response.success == 'true') {
            reload_tasks_tables();
            if (taskModalVisible) { _task_append_html(response.taskHtml); }
        }
    });
}

// Change task milestone from single modal
function task_add_taskbookmarks(taskbookmarks_id, task_id) {
    url = 'taskbookmarks/add_taskbookmarks/' + taskbookmarks_id + '/' + task_id;
    var taskModalVisible = $('#task-modal').is(':visible');
    url += '?single_task=' + taskModalVisible;
    requestGetJSON(url).done(function(response) {
        if (response.success === true || response.success == 'true') {
            reload_tasks_tables();
            if (taskModalVisible) { _task_append_html(response.taskHtml); }
        }
    });
}

// Change task milestone from single modal
function remove_taskbookmarks(taskbookmarks_id, task_id, append = true) {
    url = 'taskbookmarks/remove_taskbookmarks/' + taskbookmarks_id + '/' + task_id;
    var taskModalVisible = $('#task-modal').is(':visible');
    url += '?single_task=' + taskModalVisible;
    requestGetJSON(url).done(function(response) {
        if (response.success === true || response.success == 'true') {
            reload_tasks_tables();
            if(append == true){
                if (taskModalVisible) { _task_append_html(response.taskHtml); }
            }
        }
    });
}

// Change task milestone from single modal
function add_taskbookmarks(taskbookmarks_id, task_id, append = true) {
    url = 'taskbookmarks/add_taskbookmarks/' + taskbookmarks_id + '/' + task_id;
    var taskModalVisible = $('#task-modal').is(':visible');
    url += '?single_task=' + taskModalVisible;
    requestGetJSON(url).done(function(response) {
        if (response.success === true || response.success == 'true') {
            reload_tasks_tables();
            if(append == true){
                if (taskModalVisible) { _task_append_html(response.taskHtml); }
            }
        }
    });
}
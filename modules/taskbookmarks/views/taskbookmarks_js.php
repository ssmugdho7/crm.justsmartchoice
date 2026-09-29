<script>
  _validate_form($('#taskbookmarks-form'),{taskbookmarks_name:'required'});

  initDataTable('.table-taskbookmarks', window.location.href);

  function new_taskbookmarks(){
    $('#taskbookmarks input[name="taskbookmarks_name"]').val('');
    $('.colorpicker-input').colorpicker('setValue', '#f0f509');
    $('#taskbookmarks i[id="icon"]').attr("class",'');
    $('#taskbookmarks input[name="icon"]').val('');
    $('#additional').find('input[name="id"]').remove();
     
    $('#taskbookmarks').modal('show');
    $('.edit-title').addClass('hide');
    $('.add-title').removeClass('hide');
  }
  function edit_taskbookmarks(invoker,id){
    $('#additional').find('input[name="id"]').remove();
    $('#additional').append(hidden_input('id',id));

    $('#taskbookmarks input[name="taskbookmarks_name"]').val($(invoker).data('name'));
    $('#taskbookmarks input[name="icon"]').val($(invoker).data('icon'));
    $('#taskbookmarks i[id="icon"]').attr("class",'fa '+$(invoker).data('icon'));
    $('.colorpicker-input').colorpicker('setValue', $(invoker).data('color'));
    $('#taskbookmarks').modal('show');
    $('.add-title').addClass('hide');
    $('.edit-title').removeClass('hide');
    
}
var fnServerParams = {
   "rel_id": 'select[name="rel_id"]',
   "rel_type": 'select[name="rel_type"]',
 }
 $('#rel_id_wrapper').on('change', function() {
       taskbookmarks_list_task_filter();
     });
  function taskbookmarks_list_task_filter() {
     if ($.fn.DataTable.isDataTable('.table-taskbookmarks_list_task_filter')) {
       $('.table-taskbookmarks_list_task_filter').DataTable().destroy();
     }
     initDataTable('.table-taskbookmarks_list_task_filter', admin_url + 'taskbookmarks/taskbookmarks_list_task_filter', false, false, fnServerParams);
   }

function remove_list_taskbookmarks(taskbookmarks_id,taskid) {

    $.post(admin_url+'taskbookmarks/remove_list_taskbookmarks/'+taskbookmarks_id+'/'+id).done(function(response){
      $('#additional').find('input[value="'+taskid+'"]').remove();
      var list_tasks = $('input[name="list_tasks[]"]').map(function(){
      return $(this).val()
    }).get()
      $('input[name="list_tasks_<?php echo htmlspecialchars($id); ?>"]').remove();
      $('#additional').append(hidden_input('list_tasks_<?php echo htmlspecialchars($id); ?>',list_tasks));
      var list_tasks = {
     "list_tasks": 'input[name="list_tasks_<?php echo htmlspecialchars($id); ?>"]',
    }
    
     if ($.fn.DataTable.isDataTable('.table-taskbookmarks_list_task_add_<?php echo htmlspecialchars($id); ?>')) {
       $('.table-taskbookmarks_list_task_add_<?php echo htmlspecialchars($id); ?>').DataTable().destroy();
     }
     initDataTable('.table-taskbookmarks_list_task_add_<?php echo htmlspecialchars($id); ?>', admin_url + 'taskbookmarks/taskbookmarks_list_task_add', false, false, list_tasks);
    }); 
}
function add_dashboard(id){
    $.post(admin_url + 'taskbookmarks/add_taskbookmarks_widget/'+id).done(function(response) {
        response = JSON.parse(response);
            alert_float('success', response.message);
            window.location.reload();
     });
 }
 function remove_taskbookmarks_dashboard(id){
    $.post(admin_url + 'taskbookmarks/remove_taskbookmarks_widget/'+id).done(function(response) {
        response = JSON.parse(response);
        if (response.success === true || response.success == 'true') {
            alert_float('success', response.message);
            window.location.reload();
        }
     });
 }
</script>
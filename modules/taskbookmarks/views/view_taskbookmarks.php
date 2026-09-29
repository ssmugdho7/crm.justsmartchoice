<?php init_head(); ?>
<div id="wrapper">
    
  <div class="content">
    <div class="clearfix"></div>
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <div class="row">
             <div class="col-md-12 border-right">
              <h4 class="no-margin font-bold"><i class="fa fa-eye" aria-hidden="true"></i> <?php echo htmlspecialchars(_l('taskbookmarks')).': '. _l($title); ?></h4>
              <hr />
            </div>
          </div>
            <?php $this->load->view('taskbookmarks/view_taskbookmarks_dashboard'); ?>
            </div>
          </div>
        </div>
      </div>
</div>
</div>

<?php init_tail(); ?>
<?php $this->load->view('taskbookmarks/taskbookmarks_js'); ?>
</body>
</html>
<script>
      
  var list_tasks = {
            "list_tasks": 'input[name="list_tasks_'+<?php echo htmlspecialchars($id); ?>+'"]',
        }
  $(function(){
      taskbookmarks();
    });
function taskbookmarks() {
    if ($.fn.DataTable.isDataTable('.table-taskbookmarks_list_task_add_'+<?php echo htmlspecialchars($id); ?>)) {
       $('.table-taskbookmarks_list_task_add_'+<?php echo htmlspecialchars($id); ?>).DataTable().destroy();
     }
      initDataTable('.table-taskbookmarks_list_task_add_'+<?php echo htmlspecialchars($id); ?>, admin_url + 'taskbookmarks/taskbookmarks_list_task_add', false, false, list_tasks);
   }
</script>
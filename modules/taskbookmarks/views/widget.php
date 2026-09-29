<?php $this->load->model('taskbookmarks/taskbookmarks_model');
$taskbookmarks_list = $this->taskbookmarks_model->get_filter_widget(get_staff_user_id(),'taskbookmarks'); ?>

<?php foreach ($taskbookmarks_list as $taskbookmarks) { ?>
<div class="widget" id="widget-<?php echo basename(__FILE__,".php"); ?>" data-name="<?php echo htmlspecialchars(_l('project_roadmap')); ?>">
<div class="panel_s user-data">
  <div class="panel-body">
    <div class="widget-dragger"></div>
     <?php
      $data = $this->taskbookmarks_model->view_taskbookmarks_helper($taskbookmarks['rel_id']); 
     ?>
    <div class="row">
      <div class="col-md-12">
        <div class="col-md-6">
         <p class="text-dark text-uppercase bold"><?php echo htmlspecialchars(_l('taskbookmarks')).': '.htmlspecialchars($data['taskbookmarks']['name']); ?></p>
      </div>
         <div class="col-md-3 pull-right">
          <a href="#" class="pull-right btn btn-danger btn-icon" data-toggle="tooltip" title="" onclick="remove_dashboard(<?php echo htmlspecialchars($taskbookmarks['id']); ?>)" data-original-title="remove_dashboard"><i class="fa fa-compress"></i></a> 
         </div>
         <br>
         <hr class="mtop15" />
      </div>
     <?php $this->load->view('taskbookmarks/view_taskbookmarks_dashboard', $data); ?>
     </div>
    </div>
  </div>
</div>
<?php } ?> 
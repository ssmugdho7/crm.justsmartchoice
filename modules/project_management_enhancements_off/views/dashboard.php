<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="no-margin">Project Management Enhancements</h4>
        <hr class="hr-panel-heading" />
        <p>Smart Choice project management compatibility and task workflow repair layer.</p>
        <p><strong>Version:</strong> <?php echo html_escape($version); ?></p>
        <a href="<?php echo admin_url('project_management_enhancements/health'); ?>" class="btn btn-info"><i class="fa fa-heart-pulse"></i> Health</a>
        <a href="<?php echo admin_url('tasks'); ?>" class="btn btn-default"><i class="fa fa-check-square"></i> Tasks</a>
        <a href="<?php echo admin_url('projects'); ?>" class="btn btn-default"><i class="fa fa-diagram-project"></i> Projects</a>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>

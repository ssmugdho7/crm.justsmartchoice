<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="panel_s">
      <div class="panel-body">
        <h4 class="no-margin">Project Management Health</h4>
        <hr class="hr-panel-heading" />
        <table class="table table-bordered table-striped">
          <thead><tr><th>Check</th><th>Status</th><th>Message</th></tr></thead>
          <tbody>
          <?php foreach ($checks as $row) { ?>
            <tr><td><?php echo html_escape($row['check']); ?></td><td><?php echo html_escape($row['status']); ?></td><td><?php echo html_escape($row['message']); ?></td></tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>

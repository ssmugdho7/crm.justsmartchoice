<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content scfc-wrap">
  <div class="panel_s scfc-panel">
    <div class="panel-heading scfc-flex-head"><h4><i class="fa fa-heartbeat"></i> <?php echo _l('scfc_health_check'); ?></h4><a href="<?php echo admin_url('smart_choice_field_connector'); ?>" class="btn btn-default btn-sm">Back</a></div>
    <div class="panel-body">
      <table class="table scfc-table"><thead><tr><th>Check</th><th>Status</th></tr></thead><tbody>
      <?php foreach ($report as $row) { ?>
        <tr><td><?php echo html_escape($row['label']); ?></td><td><strong><?php echo html_escape($row['status']); ?></strong></td></tr>
      <?php } ?>
      </tbody></table>
      <div class="alert alert-info">This checker is read-only. It does not delete CRM data.</div>
    </div>
  </div>
</div></div>
<?php init_tail(); ?>

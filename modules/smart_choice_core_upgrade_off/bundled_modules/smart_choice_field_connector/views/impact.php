<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content scfc-wrap">
    <div class="panel_s scfc-panel">
      <div class="panel-heading scfc-flex-head">
        <h4><i class="fa fa-warning"></i> <?php echo _l('scfc_impact_report'); ?></h4>
        <a href="<?php echo admin_url('smart_choice_field_connector'); ?>" class="btn btn-default btn-sm"><?php echo _l('back'); ?></a>
      </div>
      <div class="panel-body">
        <div class="alert alert-warning"><?php echo _l('scfc_impact_warning'); ?></div>
        <h4><?php echo _l('scfc_total_detected_references'); ?>: <?php echo (int) ($report['total'] ?? 0); ?></h4>
        <table class="table scfc-table">
          <thead><tr><th><?php echo _l('scfc_table'); ?></th><th><?php echo _l('scfc_column'); ?></th><th><?php echo _l('scfc_matches'); ?></th></tr></thead>
          <tbody>
          <?php if (empty($report['items'])) { ?><tr><td colspan="3" class="text-center text-muted"><?php echo _l('scfc_no_references'); ?></td></tr><?php } ?>
          <?php foreach (($report['items'] ?? []) as $item) { ?>
            <tr><td><?php echo html_escape($item['table']); ?></td><td><?php echo html_escape($item['column']); ?></td><td><?php echo (int) $item['count']; ?></td></tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>

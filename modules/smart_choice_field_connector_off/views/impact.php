<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content scfc-wrap">
  <div class="panel_s scfc-panel">
    <div class="panel-heading scfc-flex-head"><h4><i class="fa fa-warning"></i> <?php echo _l('scfc_impact_report'); ?></h4><a href="<?php echo admin_url('smart_choice_field_connector'); ?>" class="btn btn-default btn-sm">Back</a></div>
    <div class="panel-body">
      <div class="alert alert-warning">Review this list before deleting or changing a merge token. If there are matches, that module may be affected.</div>
      <h4>Total detected references: <?php echo (int)($report['total'] ?? 0); ?></h4>
      <table class="table scfc-table"><thead><tr><th>Table</th><th>Column</th><th>Matches</th></tr></thead><tbody>
      <?php if (empty($report['items'])) { ?><tr><td colspan="3" class="text-center text-muted">No references found.</td></tr><?php } ?>
      <?php foreach (($report['items'] ?? []) as $item) { ?>
        <tr><td><?php echo html_escape($item['table']); ?></td><td><?php echo html_escape($item['column']); ?></td><td><?php echo (int)$item['count']; ?></td></tr>
      <?php } ?>
      </tbody></table>
    </div>
  </div>
</div></div>
<?php init_tail(); ?>

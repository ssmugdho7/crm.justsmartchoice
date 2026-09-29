<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="smart-choice-core-page">
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body"><h3 class="tw-mt-0"><i class="fa fa-file-lines"></i> CRM File Inventory</h3><a href="<?php echo admin_url('smart_choice_core_upgrade'); ?>" class="btn btn-default btn-sm">Dashboard</a><a href="<?php echo admin_url('smart_choice_core_upgrade/export_file_inventory'); ?>" class="btn btn-info btn-sm">Export CSV</a><hr><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Path</th><th>Size</th><th>Modified</th></tr></thead><tbody><?php if (!empty($files)) { foreach ($files as $file) { ?><tr><td><?php echo html_escape($file['path'] ?? ''); ?></td><td><?php echo html_escape($file['size'] ?? ''); ?></td><td><?php echo html_escape($file['modified'] ?? ''); ?></td></tr><?php }} else { ?><tr><td colspan="3" class="text-center text-muted">No file data returned.</td></tr><?php } ?></tbody></table></div></div></div></div></div><?php init_tail(); ?>

</div>

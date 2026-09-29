<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-choice-core-page"><div class="panel_s"><div class="panel-body">
<h3><i class="fa fa-folder-tree"></i> CRM File Inventory</h3>
<p>This report lists CRM files so the structure can be reviewed, exported, or shared for troubleshooting.</p>
<div class="smart-choice-table-toolbar"><a href="<?= admin_url('smart_choice_core_upgrade/export_file_inventory'); ?>" class="btn btn-default btn-xs"><i class="fa fa-download"></i> Export</a><a href="<?= admin_url('smart_choice_core_upgrade/file_inventory'); ?>" class="btn btn-default btn-xs"><i class="fa fa-rotate"></i> Reload</a></div>
<div class="table-responsive"><table class="table table-striped table-smart-choice"><thead><tr><th>Path</th><th>Size</th><th>Modified</th></tr></thead><tbody>
<?php foreach($files as $file){ ?><tr><td><?= html_escape($file['path']); ?></td><td><?= number_format((float)$file['size']); ?></td><td><?= html_escape($file['modified']); ?></td></tr><?php } ?>
</tbody></table></div></div></div></div></div><?php init_tail(); ?>

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content smart-choice-core-page"><div class="panel_s"><div class="panel-body">
<h3><i class="fa fa-bug"></i> System Error Report</h3>
<div class="smart-choice-table-toolbar">
<a href="<?= admin_url('smart_choice_core_upgrade/export_error_report'); ?>" class="btn btn-default btn-xs"><i class="fa fa-download"></i> Export</a>
<a href="<?= admin_url('smart_choice_core_upgrade/error_report'); ?>" class="btn btn-default btn-xs"><i class="fa fa-rotate"></i> Reload</a>
<a href="<?= admin_url('smart_choice_core_upgrade/clear_error_report'); ?>" class="btn btn-danger btn-xs _delete"><i class="fa fa-trash"></i> Clear</a>
<button type="button" onclick="navigator.clipboard.writeText(document.getElementById('sc-error-table').innerText);alert('Error report copied.');" class="btn btn-info btn-xs"><i class="fa fa-copy"></i> Copy</button>
</div>
<div class="table-responsive"><table id="sc-error-table" class="table table-striped table-smart-choice"><thead><tr><th>Type</th><th>File</th><th>Line</th><th>Message</th><th>Date</th></tr></thead><tbody>
<?php foreach($errors as $error){ ?><tr><td><?= html_escape($error['type']); ?></td><td><?= html_escape($error['file']); ?></td><td><?= html_escape($error['line']); ?></td><td><?= html_escape($error['message']); ?></td><td><?= html_escape($error['date']); ?></td></tr><?php } ?>
<?php if(empty($errors)){ ?><tr><td colspan="5">No system errors found in recent logs.</td></tr><?php } ?>
</tbody></table></div></div></div></div></div><?php init_tail(); ?>

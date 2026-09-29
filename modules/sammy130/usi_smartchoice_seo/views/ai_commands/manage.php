<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<?php $this->load->view('usi_smartchoice_seo/partials/top'); ?>
<div class="sc-toolbar"><h4><i class="fa fa-microphone"></i> <?php echo html_escape($title); ?></h4><div>
<a class="btn btn-primary btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_command'); ?>">New Command</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/sample_header/ai_commands'); ?>">Sample Header</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/export/ai_commands'); ?>">Export</a>
<a class="btn btn-default btn-sm" href="<?php echo admin_url('usi_smartchoice_seo/ai_commands'); ?>">Reload</a></div></div>
<form method="get" class="sc-filter"><input type="text" name="search" class="form-control" placeholder="Filter commands, intent, module, or status" value="<?php echo html_escape($filters['search'] ?? ''); ?>"></form>
<?php echo form_open(admin_url('usi_smartchoice_seo/mass_delete_ai_commands')); ?><div class="table-responsive"><table class="table table-striped table-condensed sc-table"><thead><tr><th><input type="checkbox" class="sc-check-all"></th><th>Command</th><th>Source</th><th>Intent</th><th>Target Module</th><th>Status</th><th>Created</th><th class="text-center">Actions</th></tr></thead><tbody>
<?php foreach ($commands as $row) { ?><tr><td><input type="checkbox" name="ids[]" value="<?php echo (int)$row['id']; ?>"></td><td><?php echo html_escape($row['command_text']); ?></td><td><?php echo html_escape($row['command_source']); ?></td><td><?php echo html_escape($row['intent']); ?></td><td><?php echo html_escape($row['target_module']); ?></td><td><?php echo html_escape($row['action_status']); ?></td><td><?php echo html_escape($row['created_at']); ?></td><td class="text-center"><a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/view_ai_command/' . (int)$row['id']); ?>">View</a> <a class="btn btn-default btn-xs" href="<?php echo admin_url('usi_smartchoice_seo/ai_command/' . (int)$row['id']); ?>">Edit</a> <a class="btn btn-danger btn-xs _delete" href="<?php echo admin_url('usi_smartchoice_seo/delete_ai_command/' . (int)$row['id']); ?>">Delete</a></td></tr><?php } ?>
<?php if (empty($commands)) { ?><tr><td colspan="8" class="text-center text-muted">No AI commands found.</td></tr><?php } ?>
</tbody></table></div><button type="submit" class="btn btn-danger btn-sm">Mass Delete</button><?php echo form_close(); ?>
</div></div></div></div><?php init_tail(); ?>

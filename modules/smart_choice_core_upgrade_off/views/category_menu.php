<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="smart-choice-core-page">
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body"><h3 class="tw-mt-0"><i class="fa fa-folder-tree"></i> Category Menu</h3><p class="text-muted">Organize Smart Choice menu shortcuts.</p><a href="<?php echo admin_url('smart_choice_core_upgrade'); ?>" class="btn btn-default btn-sm">Dashboard</a><hr><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Name</th><th>Icon</th><th>Status</th></tr></thead><tbody><?php if (!empty($categories)) { foreach ($categories as $cat) { ?><tr><td><?php echo html_escape($cat->name ?? 'Category'); ?></td><td><i class="<?php echo html_escape($cat->icon ?? 'fa fa-folder'); ?>"></i></td><td><?php echo !empty($cat->is_active) ? 'Active' : 'Inactive'; ?></td></tr><?php }} else { ?><tr><td colspan="3" class="text-center text-muted">No categories found.</td></tr><?php } ?></tbody></table></div></div></div></div></div><?php init_tail(); ?>

</div>

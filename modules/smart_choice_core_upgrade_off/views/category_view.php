<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="smart-choice-core-page">
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body"><h3 class="tw-mt-0"><?php echo html_escape($category->name ?? 'Category'); ?></h3><a href="<?php echo admin_url('smart_choice_core_upgrade/category_menu'); ?>" class="btn btn-default btn-sm">Back</a><hr><div class="table-responsive"><table class="table table-striped"><thead><tr><th>Name</th><th>URL</th></tr></thead><tbody><?php if (!empty($items)) { foreach ($items as $item) { ?><tr><td><?php echo html_escape($item->name ?? 'Item'); ?></td><td><?php echo html_escape($item->url ?? $item->href ?? ''); ?></td></tr><?php }} else { ?><tr><td colspan="2" class="text-center text-muted">No items found.</td></tr><?php } ?></tbody></table></div></div></div></div></div><?php init_tail(); ?>

</div>

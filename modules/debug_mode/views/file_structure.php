<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
function debug_mode_render_tree($items) {
    if (empty($items)) { return; }
    echo '<ul class="debug-folder-tree">';
    foreach ($items as $item) {
        echo '<li><span><i class="fa fa-folder"></i> ' . html_escape($item['name']) . '</span>';
        if (!empty($item['children'])) { debug_mode_render_tree($item['children']); }
        echo '</li>';
    }
    echo '</ul>';
}
?>
<div id="wrapper" class="debug-mode-page"><div class="content"><div class="panel_s debug-mode-card"><div class="panel-body">
    <div class="debug-mode-hero debug-mode-hero-compact"><div><h1><i class="fa fa-sitemap"></i> CRM Folder Structure</h1><p>Folder-only map of the CRM installation. Files are intentionally hidden.</p></div><div class="debug-mode-inline-actions"><a href="<?php echo admin_url('debug_mode/export_structure_html'); ?>" class="btn btn-success btn-sm"><i class="fa fa-file-code-o"></i> Create HTML File</a><a href="<?php echo admin_url('debug_mode'); ?>" class="btn btn-default btn-xs">Back</a></div></div>
    <div class="debug-mode-bottom-nav debug-mode-top-nav"><a href="<?php echo admin_url('debug_mode'); ?>">Utilities</a><a href="<?php echo admin_url('debug_mode/health'); ?>">Health</a><a  href="<?php echo admin_url('debug_mode/crm_errors'); ?>">Errors</a><a class="active" href="<?php echo admin_url('debug_mode/file_structure'); ?>">Structure</a><a href="<?php echo admin_url('debug_mode/network_tools'); ?>">Network</a></div>
    <div class="alert alert-info">Base path: <strong><?php echo html_escape($base_path); ?></strong></div>
    <div class="debug-folder-wrapper"><?php debug_mode_render_tree($tree); ?></div>
</div></div></div></div>
<?php init_tail(); ?>

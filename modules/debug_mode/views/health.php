<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s debug-mode-card"><div class="panel-body">
<h3>CRM Utilities Health Check</h3>
<p><strong>Status:</strong> <?php echo debug_mode_is_enabled() ? 'Debug Mode Active' : 'Production Mode'; ?></p>
<p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
<p><strong>Environment:</strong> <?php echo ENVIRONMENT; ?></p>
<p><strong>Cache Last Cleared:</strong> <?php echo html_escape(get_option('debug_mode_cache_last_cleared')); ?></p>
<a href="<?php echo admin_url('debug_mode'); ?>" class="btn btn-primary">Back To CRM Utilities</a>
</div></div></div></div>
<?php init_tail(); ?>

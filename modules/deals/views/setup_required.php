<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h2>Deals setup required</h2><p>The Deals module has not been initialized, or has no configured pipeline. An administrator must activate or upgrade the module and configure a pipeline before deals can be managed.</p>
<?php if (is_admin()) { ?><a class="btn btn-primary" href="<?php echo admin_url('mods'); ?>">Open Modules</a><?php } ?>
</div></div></div></div>
<?php init_tail(); ?>

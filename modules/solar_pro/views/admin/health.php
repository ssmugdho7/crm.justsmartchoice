<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content solar-pro-shell"><?php $this->load->view('admin/_module_nav'); ?>
<div class="solar-hero compact"><div><h1><?php echo _l('solar_pro_health'); ?></h1><p><?php echo _l('solar_pro_health_subtitle'); ?></p></div><a class="btn btn-default" href="<?php echo admin_url('solar_pro'); ?>"><i class="fa fa-arrow-left"></i> <?php echo _l('solar_pro_dashboard'); ?></a></div>
<div class="panel_s solar-card"><div class="panel-body">
<table class="table table-striped"><tbody>
<tr><th><?php echo _l('solar_pro_file_version'); ?></th><td><?php echo html_escape($health['file_version']); ?></td></tr>
<tr><th><?php echo _l('solar_pro_installed_version'); ?></th><td><?php echo html_escape($health['installed_version']); ?></td></tr>
<tr><th><?php echo _l('solar_pro_module_active'); ?></th><td><?php echo $health['active'] ? _l('solar_pro_yes') : _l('solar_pro_no'); ?></td></tr>
<tr><th><?php echo _l('solar_pro_upgrade_required'); ?></th><td><?php echo $health['upgrade_required'] ? _l('solar_pro_yes') : _l('solar_pro_no'); ?></td></tr>
<tr><th><?php echo _l('solar_pro_settings_registered'); ?></th><td><?php echo $health['settings_registered'] ? _l('solar_pro_yes') : _l('solar_pro_no'); ?></td></tr>
<tr><th><?php echo _l('solar_pro_admin_url'); ?></th><td><code><?php echo html_escape($health['admin_url']); ?></code></td></tr>
<tr><th><?php echo _l('solar_pro_public_url'); ?></th><td><code><?php echo html_escape($health['public_url']); ?></code></td></tr>
</tbody></table>
<h4><?php echo _l('solar_pro_database_tables'); ?></h4>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th><?php echo _l('solar_pro_table'); ?></th><th><?php echo _l('solar_pro_status'); ?></th></tr></thead><tbody><?php foreach($health['tables'] as $table=>$ok): ?><tr><td><code><?php echo html_escape(db_prefix().$table); ?></code></td><td><?php if($ok): ?><span class="label label-success"><?php echo _l('solar_pro_ok'); ?></span><?php else: ?><span class="label label-danger"><?php echo _l('solar_pro_missing'); ?></span><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
</div></div></div></div>
<?php init_tail(); ?>

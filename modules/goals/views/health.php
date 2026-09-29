<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><?php init_head(); ?>
<div id="wrapper"><div class="content smart-choice-goals">
<?php $this->load->view('goals/_navigation'); ?><div class="panel_s"><div class="panel-body">
<h4 class="tw-font-bold tw-text-lg"><?php echo _l('goals_health_check'); ?></h4>
<table class="table table-bordered sc-health-table"><tbody>
<tr><th><?php echo _l('goals_module_folder'); ?></th><td><code><?php echo html_escape($checks['module_folder']); ?></code></td></tr>
<tr><th><?php echo _l('goals_main_file'); ?></th><td><?php echo is_file($checks['main_file'])?'OK':'Missing'; ?></td></tr>
<tr><th><?php echo _l('goals_database_table'); ?></th><td><?php echo $checks['table_exists']?'OK':'Missing'; ?></td></tr>
<tr><th><?php echo _l('goals_record_count'); ?></th><td><?php echo (int)$checks['record_count']; ?></td></tr>
<tr><th><?php echo _l('goals_missing_fields'); ?></th><td><?php echo empty($checks['missing_fields'])?'None':html_escape(implode(', ',$checks['missing_fields'])); ?></td></tr>
<tr><th>English</th><td><?php echo $checks['english_language']?'OK':'Missing'; ?></td></tr>
<tr><th>Spanish</th><td><?php echo $checks['spanish_language']?'OK':'Missing'; ?></td></tr>
<tr><th>Migration 250</th><td><?php echo $checks['migration_250']?'OK':'Missing'; ?></td></tr>
</tbody></table>
<?php if(is_admin()){ ?><a href="<?php echo admin_url('goals/repair_database'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-wrench"></i> <?php echo _l('goals_repair_database'); ?></a><?php } ?>
</div></div></div></div><?php init_tail(); ?></body></html>

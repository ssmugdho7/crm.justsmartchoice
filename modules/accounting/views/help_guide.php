<?php defined('BASEPATH') or exit('No direct script access allowed'); init_head(); ?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<h2><i class="fa fa-graduation-cap"></i> <?php echo _l('accounting_help_guide'); ?></h2>
<p class="text-muted"><?php echo _l('accounting_help_intro'); ?></p>
<div class="row"><div class="col-md-8">
<?php foreach (['dashboard','banking','transactions','bills_checks','journal','reconciliation','budget','quickbooks','backup_safe_mode'] as $section) { ?>
<div class="panel panel-default"><div class="panel-heading"><strong><?php echo _l('acc_help_'.$section.'_title'); ?></strong></div><div class="panel-body"><?php echo _l('acc_help_'.$section.'_steps'); ?></div></div>
<?php } ?>
</div><div class="col-md-4"><div class="panel panel-info"><div class="panel-heading"><?php echo _l('accounting_training_video'); ?></div><div class="panel-body">
<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="<?php echo html_escape(get_option('acc_training_video_url')); ?>" allowfullscreen></iframe></div>
<p class="mtop10 text-muted"><?php echo _l('accounting_video_settings_note'); ?></p>
</div></div></div></div>
</div></div></div><?php init_tail(); ?>

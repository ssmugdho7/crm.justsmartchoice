<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="sc-top-nav">
  <a class="sc-nav-btn" href="<?php echo admin_url('crm_file_explorer_manager'); ?>"><i class="fa fa-dashboard"></i> <?php echo _l('file_dashboard'); ?></a>
  <a class="sc-nav-btn" href="<?php echo admin_url('crm_file_explorer_manager/media'); ?>"><i class="fa fa-picture-o"></i> <?php echo _l('media_viewer'); ?></a>
  <a class="sc-nav-btn" href="<?php echo admin_url('crm_file_explorer_manager/backups'); ?>"><i class="fa fa-archive"></i> <?php echo _l('safe_backups'); ?></a>
  <a class="sc-nav-btn" href="<?php echo admin_url('crm_file_explorer_manager/health'); ?>"><i class="fa fa-heartbeat"></i> <?php echo _l('health_checker'); ?></a>
  <a class="sc-nav-btn" href="<?php echo admin_url('crm_file_explorer_manager/help'); ?>"><i class="fa fa-question-circle"></i> <?php echo _l('help_guide'); ?></a>
</div>
